<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="refresh" content="300">
		<title>Alguma Sala Disponível?</title>
		<link rel="stylesheet" type="text/css" href="style.1.css">
		<link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
	</head>
	<body>
		<div class="tableDiv">
			<?php
				try {
					$string = file_get_contents(".config-private");
					$json = json_decode($string, true);
					$host = json['host'];
					$user = json['user'];
					$password = json['password'];
					$db = json['db'];
				} catch (Exception $e) {
					$host = json[''];
					$user = json[''];
					$password = json[''];
					$db = json[''];
				}
				$conn = new mysqli($host, $user, $password, $db, 3306);
				if ($conn->connect_error)
					die("Connection failed: ".$conn->connect_error);
				$conn->set_charset('utf8');
				$day = intval(date('N')) - 1;
				$hours = date('H');
				$minutes = (intval(date('i')) < 30 ? '00' : '30');
				$col = $hours.'h'.$minutes;
				$tables = array('ocup_segunda', 'ocup_terca', 'ocup_quarta', 'ocup_quinta', 'ocup_sexta', 'ocup_sabado', 'ocup_domingo');
				$cols = array('08h00', '08h30', '09h00', '09h30', '10h00', '10h30', '11h00', '11h30', '12h00', '12h30', '13h00', '13h30', '14h00', '14h30', '15h00', '15h30',
					'16h00', '16h30', '17h00', '17h30', '18h00', '18h30', '19h00', '19h30', '20h00', '20h30', '21h00', '21h30', '22h00', '22h30', '23h00');
				echo '<table class="table">'.PHP_EOL.'<thead><th class="cellHead">Salas</th><th class="cellHead">Horas</th></thead>'.PHP_EOL;
				$sql = "SELECT id, nome, DATEDIFF(NOW(), time) AS time FROM salas;";
				$result = $conn->query($sql);
				for ($i = 0; $i < $result->num_rows; $i++) {
					$row = $result->fetch_assoc();
					echo '<tr class="row">'.PHP_EOL.'<td';
					if (intval($row['time']) > 30)
						echo ' class="cellDated"';
					echo '>'.$row['nome'].'</td>'.PHP_EOL;
					$sql = "SELECT * FROM ".$tables[$day]." WHERE id = '".$row['id']."';";
					$result2 = $conn->query($sql);
					if ($result2->num_rows == 1) {
						$row2 = $result2->fetch_assoc();
						if (!array_key_exists($col , $row2) or $row2[$col] == '0') {
							echo '<td class="cellEmpty">';
							$index = array_search($col , $cols);
							if ($index == FALSE) {
								echo '--';
							} else {
								for ($j = $index + 1; $j < count($cols); $j++)
									if ($row2[$cols[$j]] == '1')
										break;
								if ($j < count($cols))
									echo $cols[$j];
								else
									echo '--';
							}
							echo '</td>'.PHP_EOL;
						} else {
							echo '<td class="cellFull">';
							$index = array_search($col , $cols);
							if ($index == FALSE) {
								echo '--';
							} else {
								for ($j = $index + 1; $j < count($cols); $j++)
									if ($row2[$cols[$j]] == '0')
										break;
								if ($j < count($cols))
									echo $cols[$j];
								else
									echo '--';
							}
							echo '</td>'.PHP_EOL;
						}
					}
					echo '</tr>';
				}
				$conn->close();
				echo '</table>';
			?>
		</div>
	</body>
</html>
