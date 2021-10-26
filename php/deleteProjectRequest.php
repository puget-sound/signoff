<?php

require_once('connect.php');
$conn = db_connect();
$requestId = $_GET['requestId'];

$query = $conn->query("DELETE FROM signoff_project_requests WHERE requestId = $requestId");

if ($query) {
	echo(json_encode(array("success" => "true")));
} else {
	echo(json_encode(array("error" => mysql_error())));
}
?>
