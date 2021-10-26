<?php
session_start();

require_once('config.php');
$internal_api_token = $API_TOKEN;
$jsonp_callback = isset($_GET['callback']) ? $_GET['callback'] : null;

if($jsonp_callback) {
	$author = $_GET['author'];
	// verify API token
	if (!isset($_GET['apiToken']) || $_GET['apiToken'] != $internal_api_token) {
		echo(json_encode(array("error" => "API Token is not authorized")));
		exit;
	}
}
else {
	//$author = $_SESSION['username'];
	$username_parts = explode("@", $_SESSION['username']);
  $author = $username_parts[0];
}

//make database connection
require_once('connect.php');
$conn = db_connect();

$authorFullName = urlencode(ldapGetFullName($author));
if ($authorFullName == "err") {
	echo(json_encode(array("error" => "Sync with Active Directory failed. This is most likely caused by an issue with Okta. Please try logging in again to continue.")));
	exit;
}


//Get All Variables
$typeOfWork = urlencode($_GET['typeOfWork']);
$ticketNumber = urlencode($_GET['ticketNumber']);
$projectId = urlencode($_GET['projectId']);
$soundNetLink = urlencode($_GET['soundNetLink']);
$lpProjectLink = urlencode($_GET['lpProjectLink']);
$sprint = urlencode($_GET['sprint']);
$projectName = urlencode($_GET['projectName']);
$appDesignerProjects = urlencode($_GET['appDesignerProjects']);
$plsqlObjects = urlencode($_GET['plsqlObjects']);
$otherObjects = urlencode($_GET['otherObjects']);
$projectOwner = urlencode($_GET['projectOwner']);
$summaryWorkCompleted = urlencode($_GET['summaryWorkCompleted']);
$testingType = urlencode($_GET['testingType']);
$requestUsers = $_GET['requestUsers'];
//get users in an array
$userArray = explode(",", $requestUsers);

foreach ($userArray as $checkvalue) {
	$checkuser = $checkvalue;
	$adname = ldapGetFullName($checkuser);
	if ($adname == "err") {
		echo(json_encode(array("error" => "One or more users does not exist in Active Directory. Please check and try again.")));
		exit;
	}
}

foreach ($userArray as $value) {
	$user = strtolower($value);
	$fullName = urlencode(ldapGetFullName($user));
	$userEmail = $user . "@pugetsound.edu";
	$query = $conn->query("INSERT INTO signoff_project_requests
		(typeOfWork,
		 ticketNumber,
		 projectId,
		 soundNetLink,
		 liquidPlannerLink,
		 sprint,
		 projectName,
		 appDesignerProjs,
		 plsqlObjects,
		 otherObjects,
		 projectOwner,
		 sumWorkCompleted,
		 testingType,
		 requestTo,
		 reqFullName,
		 author,
		 authorFullName,
		 status,
	 	 proofTesting,
	 	 additionalComments)
	VALUES ('$typeOfWork',
		'$ticketNumber',
		'$projectId',
		'$soundNetLink',
		'$lpProjectLink',
		'$sprint',
		'$projectName',
		'$appDesignerProjects',
		'$plsqlObjects',
		'$otherObjects',
		'$projectOwner',
		'$summaryWorkCompleted',
		'$testingType',
		'$user',
		'$fullName',
		'$author',
		'$authorFullName',
		'Pending', '', '')");
}
$json = json_encode(array("success" => "true", "requestId" => $conn->insert_id));
print $jsonp_callback ? "$jsonp_callback($json)" : $json;


//close database connection

?>
