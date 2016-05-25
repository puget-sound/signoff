<?php
require_once('connect.php');
$conn = db_connect();

$originalRequestId = urlencode($_GET['requestId']);

$requestQueryResult = $conn->query("SELECT * FROM signoff_project_requests WHERE requestId=$originalRequestId");//get the information from the original request

$row = $requestQueryResult->fetch_array(MYSQLI_ASSOC);

//url_encode not used on values that are coming directly from the database
$typeOfWork = $row["typeOfWork"];
$ticketNumber = $row["ticketNumber"];
$projectId = $row["projectId"];
$soundNetLink = $row["soundNetLink"];
$liquidPlannerLink = $row["liquidPlannerLink"];
$sprint = $row["sprint"];
$projectName = $row["projectName"];
$appDesignerProjs = $row["appDesignerProjs"];
$plsqlObjects = $row["plsqlObjects"];
$otherObjects = $row["otherObjects"];
$projectOwner = $row["projectOwner"];
$sumWorkCompleted = $row["sumWorkCompleted"];
$testingType = $row["testingType"];
$proofTesting = $row["proofTesting"];
$requestTo = $_GET['newRepresentative'];
$newRepresentativeUserName = $_GET['newRepresentative'];//newRepresentative
//$author = $row["author"];
//$authorFullName = $row["authorFullName"];
$author = $_COOKIE['SignOffAdminUser'];
$authorFullName = ldapGetFullName($author);
$requestDate = date("Y-m-d H:i:s", time());
$submitDate = $row["submitDate"];
$additionalComments = $row["additionalComments"];

$repFullName = ldapGetFullName($newRepresentativeUserName);

if($repFullName == "err"){
	echo(json_encode(array("error" => "One or more users does not exist in Active Directory. Please check and try again.")));
	$log->LogError("$myname | $whoami tried to Create Request. Error finding recipient in Active Directory.");
	exit;
}

if($authorFullName == "err"){
	echo(json_encode(array("error" => "Your user account does not exist in the Active Directory. Please check and try again.")));
	$log->LogError("$myname | $whoami tried to Create Request. Error finding recipient in Active Directory.");
	exit;
}

$repName = urlencode($repFullName);
$authorFullNameEncoded = urlencode($authorFullName);

$resultOfInsert = $conn->query("INSERT INTO signoff_project_requests
  ( typeOfWork,
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
    proofTesting,
    requestTo,
    reqFullName,
    author,
    authorFullName,
    requestDate,
    submitDate,
    status,
    additionalComments) VALUES
(	'$typeOfWork',
		'$ticketNumber',
		'$projectId',
		'$soundNetLink',
		'$liquidPlannerLink',
		'$sprint',
		'$projectName',
		'$appDesignerProjs',
		'$plsqlObjects',
		'$otherObjects',
		'$projectOwner',
		'$sumWorkCompleted',
		'$testingType',
    '$proofTesting',
    '$requestTo',
		'$repName',
		'$author',
		'$authorFullNameEncoded',
    '$requestDate',
    '$submitDate',
    'Pending',
    '$additionalComments')"); //put in the variables


if($resultOfInsert){
echo(json_encode(array("success" => "true")));
}else{
	echo(json_encode(array("error" => "Error! MySQL Err: " . mysql_error($connection))));
}

?>
