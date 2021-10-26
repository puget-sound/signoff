<?php
session_start();
require_once('connect.php');
$conn = db_connect();
$newUser = urlencode($_GET['newUser']);
$username_parts = explode("@", $_SESSION['username']);
$author = $username_parts[0];

$newUserFull = ldapGetFullName($newUser);
if ($newUserFull == "err") {
	echo(json_encode(array("error" => "Username does not exist in Active Directory")));
} else {
	$query = $conn->query("INSERT INTO signoff_project_admins (username, fullName, auditUsername) VALUES ('$newUser', '$newUserFull', '$author')");
	if ($query) {
		echo(json_encode(array("success" => "true")));
	} else {
		echo(json_encode(array("error" => mysql_error())));
	}
}


?>
