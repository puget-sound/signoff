<?php
require_once 'KLogger.php';
$log = KLogger::instance('../log/');
$myname = basename(__FILE__, '.php') . ".php";
$whoami = $_COOKIE['SignOffAdminUser'];

require_once('connect.php');
$conn = db_connect();
$requestId = $_GET['requestId'];

$query = $conn->query("DELETE FROM signoff_project_requests WHERE requestId = $requestId");

if ($query) {
	$log->logInfo("$myname | $whoami deleted Project Request $requestId from the Project Request System");
	echo(json_encode(array("success" => "true")));
} else {
	$log->logError("$myname | An error occured when $whoami tried to delete Project Request $requestId from the Project Request System.");
	echo(json_encode(array("error" => mysql_error())));
}
?>
