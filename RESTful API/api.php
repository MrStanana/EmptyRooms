<?php
header('Content-Type: text/html; charset=utf-8');
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
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
$link = new mysqli($host, $user, $password, $db, 3306);
if ($link->connect_error) die("Connection to database failed.");
$link->set_charset('utf8');
$key = preg_replace('/[^a-z0-9_\.]+/i', '', array_shift($request));
$stmt;
if ($key == 'salas') {
	$sql = "SELECT id FROM salas;";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$stmt->bind_result($result);
} else {
	$data = "id, nome, time";
	if (isset($_GET['data']))
		$data = $_GET['data'];
	//$sql = "SELECT ".$data." FROM salas WHERE id = '".$key."';";
	$sql = "SELECT ? FROM salas WHERE id = ?";
	$stmt = $db->prepare($sql);
	$stmt->bind_param('ss', $data, $key);
	$stmt->execute();
	$stmt->bind_result($result);
}
if ($result->num_rows == 0) die("Information requested not found.");
if ($result->num_rows > 1) echo '[';
for ($i = 0; $i < $result->num_rows; $i++)
	echo ($i > 0 ? ',' : '').json_encode($result->fetch_object());
if ($result->num_rows > 1) echo ']';
$stmt->close();
$link->close();
?>
