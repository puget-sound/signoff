<?php
//Logging Info
require_once 'KLogger.php';
$log = KLogger::instance('../log/');
$myname = basename(__FILE__, '.php') . ".php";
$whoami = $_COOKIE['SignOffAdminUser'];

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

//make database connection
require_once('connect.php');
$conn = db_connect();

foreach ($userArray as $checkvalue) {
	$checkuser = $checkvalue;
	$adname = ldapGetFullName($checkuser);
	if ($adname == "err") {
		echo(json_encode(array("error" => "One or more users does not exist in Active Directory. Please check and try again.")));
		$log->LogError("$myname | $whoami tried to Create Request. Error finding recipient in Active Directory.");
		exit;
	}
}

foreach ($userArray as $value) {
	$author = $_COOKIE['SignOffAdminUser'];
	$authorFullName = urlencode(ldapGetFullName($author));
	if ($authorFullName == "err") {
		echo(json_encode(array("error" => "Sync with Active Directory failed. This is most likely caused by an issue with CAS timing out. Please try logging out and logging in again to continue.")));
		$log->LogError("$myname | CAS Timeout issue.");
		exit;
	}
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
	$log->logInfo("$myname | $whoami Created Sign-off Request #" . $conn->insert_id .". Intended for: ". urldecode($fullName). " ($user).");
}
echo(json_encode(array("success" => "true",)));


//close database connection

?>
