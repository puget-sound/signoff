<?php

require_once('connect.php');
$conn = db_connect();

$userId = $_GET['userId'];

$query = $conn->query("DELETE FROM signoff_project_admins WHERE userId = $userId");

if ($query) {
	echo(json_encode(array("success" => "true")));
} else {
	echo(json_encode(array("error" => mysql_error())));
}
?>
