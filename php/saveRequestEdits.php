<?php
require_once('connect.php');
$conn = db_connect();

$originalRequestId = urlencode($_GET['requestId']);
$typeOfWork = urlencode($_GET['typeOfWork']);
$ticketNumber = urlencode($_GET['ticketNumber']);
$projectId = urlencode($_GET['projectId']);
$soundNetLink = urlencode($_GET['soundNetLink']);
$liquidPlannerLink = urlencode($_GET['liquidPlannerLink']);
$sprint = urlencode($_GET['sprint']);
$projectName = urlencode($_GET['projectName']);
$appDesignerProjs = urlencode($_GET['appDesignerProjs']);
$plsqlObjects = urlencode($_GET['plsqlObjects']);
$otherObjects = urlencode($_GET['otherObjects']);
$projectOwner = urlencode($_GET['projectOwner']);
$sumWorkCompleted = urlencode($_GET['sumWorkCompleted']);
$testingType = urlencode($_GET['testingType']);
$author = urlencode($_GET['author']);
//$authorFullName = urlencode($_GET['authorFullName']);

$authorFullName = ldapGetFullName($author);

if($authorFullName == "err"){
	echo(json_encode(array("error" => "The input author name does not exist in the Active Directory. Please check and try again.")));
	$log->LogError("$myname | $whoami tried to Create Request. Error finding recipient in Active Directory.");
	exit;
}

$authorFullNameEncoded = urlencode($authorFullName);

$updateResult = $conn->query("UPDATE signoff_project_requests
  SET typeOfWork='$typeOfWork',
      ticketNumber='$ticketNumber',
      projectId='$projectId',
      soundNetLink='$soundNetLink',
      liquidPlannerLink='$liquidPlannerLink',
      sprint='$sprint',
      projectName='$projectName',
      appDesignerProjs='$appDesignerProjs',
      plsqlObjects='$plsqlObjects',
      otherObjects='$otherObjects',
      projectOwner='$projectOwner',
      sumWorkCompleted='$sumWorkCompleted',
      testingType='$testingType',
      author='$author',
      authorFullName='$authorFullNameEncoded'
      WHERE requestId='$originalRequestId'");

      if($updateResult){
      echo(json_encode(array("success" => "true")));
      }else{
      	echo(json_encode(array("error" => "Error! MySQL Err: " . mysql_error($connection))));
      }
?>
