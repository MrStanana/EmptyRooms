# -*- coding: utf-8 -*-
import urllib
from bs4 import BeautifulSoup
from time import strftime
from collections import defaultdict
import pymysql.cursors
import hashlib
import json

def table_to_list(table):
	dct = table_to_2d_dict(table)
	return list(iter_2d_dict(dct))

def table_to_2d_dict(table):
	result = defaultdict(lambda : defaultdict(unicode))
	for row_i, row in enumerate(table.find_all('tr')):
		for col_i, col in enumerate(row.find_all('td')):
			colspan = int(col.get('colspan', 1))
			rowspan = int(col.get('rowspan', 1))
			if col.get_text() == u'\xa0':
				col_data = 0
			else:
				col_data = 1
			while row_i in result and col_i in result[row_i]:
				col_i += 1
			for i in range(row_i, row_i + rowspan):
				for j in range(col_i, col_i + colspan):
					result[i][j] = col_data
	return result

def iter_2d_dict(dct):
	for i, row in sorted(dct.items()):
		cols = []
		for j, col in sorted(row.items()):
			cols.append(col)
		yield cols

def IF(expr, iftrue, iffalse):
	if expr:
		return iftrue
	else:
		return iffalse

def log_write(log, str):
	log.write(str + '\n')
	print(str)

semestre = IF((int(strftime("%j")) < 40 or int(strftime("%j")) > 190), 1, 2)
log = open('log', 'a')
log_write(log, strftime("%d-%m-%Y %H:%M:%S"))
log_write(log, 'Version: 1.0.3')
log_write(log, 'Script initiated.')
try:
	with open('.config-private') as data_file:
	    data = json.load(data_file)
	host = data['host']
	user = data['user']
	password = data['password']
	db = data['db']
except:
	host = ''
	user = ''
	password = ''
	db = ''
connection = pymysql.connect(host=host, user=user, password=password, db=db, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
log_write(log, 'Connection to database established.')
salas = []
try:
	with connection.cursor() as cursor:
		cursor.execute("SELECT id, url, hashv FROM salas")
		salas = cursor.fetchall()
	log_write(log, 'Room information retrieved.')
except:
	log_write(log, 'Error retrieving room information.')
finally:
	pass

for sala in salas:
	log_write(log, 'Room: ' + sala['id'])
	url = 'https://ciencias.ulisboa.pt/servicosCake/servicoHorarios/horarios/fetcher/' + str(semestre) + '/' + sala['url']
	page = urllib.urlopen(url).read()
	hashv = hashlib.md5(page).hexdigest()
	log_write(log, 'Hash: ' + hashv)
	if hashv == sala['hashv']:
		log_write(log, 'Room is up to date.')
	else:
		if '<td class="td_cabecalho">Horas' not in page:
			log_write(log, 'There was a problem parsing the page.')
		else:
			html = '<tr>\n<td>Horas' + page.split('<td class="td_cabecalho">Horas')[1].split('</table>')[0]
			soup = BeautifulSoup(html, 'html.parser')
			days = list(map(lambda x: int(x.get('colspan', 1)), soup.find_all('tr')[0].find_all('td')[1:]))
			table = table_to_list(soup)[1:]
			[l.pop(0) for l in table]
			tables = ['ocup_segunda', 'ocup_terca', 'ocup_quarta', 'ocup_quinta', 'ocup_sexta', 'ocup_sabado']
			i = 0
			while len(table[0]) != 0:
				ocup = list(map(lambda x: max(x), list(map(lambda x: x[0:(days[i])], table))))
				try:
					with connection.cursor() as cursor:
						sql = "DELETE FROM " + tables[i] + " WHERE id = '" + sala['id'] + "';"
						cursor.execute(sql)
						sql = "INSERT INTO " + tables[i] + " VALUES ('" + sala['id'] + "', " + ','.join(list(map(lambda x: str(x), ocup))) + ");"
						cursor.execute(sql)
					connection.commit()
				finally:
					pass
				for j in range(days[i]):
					[l.pop(0) for l in table]
				i += 1
			try:
				with connection.cursor() as cursor:
					cursor.execute("UPDATE salas SET hashv = '" + hashv + "', time = NOW() WHERE salas.id = '" + sala['id'] + "';")
				connection.commit()
			finally:
				pass
			log_write(log, 'Room was updated.')

connection.close()
log_write(log, 'Script finished.')
log.write('\n')
log.close()
