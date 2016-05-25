<?php
require_once('connect.php');
$conn = db_connect();
$newUser = urlencode($_GET['newUser']);
$author = urlencode($_COOKIE['SignOffAdminUser']);

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
