<?php
require_once('connect.php');
$conn = db_connect();
$deleteId = $_GET['deleteId'];

$query = $conn->query("DELETE FROM signoff_project_owners WHERE ownerId = $deleteId");

if ($query) {
	echo(json_encode(array("success" => "true")));
} else {
	echo(json_encode(array("error" => mysql_error())));
}
?>
