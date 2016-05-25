<?php
//Logging Info
require_once 'KLogger.php';
$log = KLogger::instance('../log/');
$myname = basename(__FILE__, '.php') . ".php";
$whoami = $_COOKIE['SignOffAdminUser'];

require_once('connect.php');
$conn = db_connect();

$userId = $_GET['userId'];

$query = $conn->query("DELETE FROM signoff_project_admins WHERE userId = $userId");

if ($query) {
	echo(json_encode(array("success" => "true")));
	$log->logInfo("$myname | $whoami Deleted user with ID: $userId from the Administration Access List.");
} else {
	echo(json_encode(array("error" => mysql_error())));
	$log->logError("$myname | Error in Deleting user with ID: $userId from the Administration Access List. Please check $myname for errors.");
}
?>
