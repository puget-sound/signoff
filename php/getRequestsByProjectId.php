<?php

$projectId = $_GET['projectId'];

require_once('config.php');
global $API_TOKEN;
$jsonp_callback = isset($_GET['callback']) ? $_GET['callback'] : null;
if($jsonp_callback) {
	// verify API token
	if (!isset($_GET['apiToken']) || $_GET['apiToken'] != $API_TOKEN) {
		echo(json_encode(array("error" => "API Token is not authorized")));
		exit;
	}
}

if($projectId == "") {
	$json = json_encode(array("error" => "No project ID specified."));
	print $jsonp_callback ? "$jsonp_callback($json)" : $json;
	exit;
}

require_once('connect.php');
$conn = db_connect();
if (!$conn) {
	$log->logError("$myname | Database connection failed. Please check $myname for errors.");
}
$query = $conn->query("SELECT * FROM signoff_project_requests
 WHERE (projectId = '$projectId')
 ORDER BY requestDate DESC");
$push = array();
while ($result = $query->fetch_array(MYSQLI_ASSOC)) {
	 array_push($push, $result);
}

$json = json_encode($push);
print $jsonp_callback ? "$jsonp_callback($json)" : $json;

?>
