<?php
require_once('connect.php');
$conn = db_connect();
$newOwner = urlencode($_GET['newOwner']);

$query = $conn->query("INSERT INTO signoff_project_owners (ownerName) VALUES ('$newOwner')");

if ($query) {
	echo(json_encode(array("success" => "true")));
} else {
	echo(json_encode(array("error" => mysql_error())));
}
?>
