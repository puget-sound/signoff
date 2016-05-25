<?php

require_once('connect.php');
$conn = db_connect();

$originalRequestId = urlencode($_GET['requestId']);

$requestQueryResult = $conn->query("SELECT * FROM signoff_project_requests WHERE requestId=$originalRequestId");//get the information from the original request

$row = $requestQueryResult->fetch_array(MYSQLI_ASSOC);

$jsonArray = array(); //the json array being passed back

//url_encode not used on values that are coming directly from the database
$array["typeOfWork"] = urldecode($row["typeOfWork"]);
$array["ticketNumber"] = urldecode($row["ticketNumber"]);
$array["projectId"] = urldecode($row["projectId"]);
$array["soundNetLink"] = urldecode($row["soundNetLink"]);
$array["liquidPlannerLink"] = urldecode($row["liquidPlannerLink"]);
$array["sprint"] = urldecode($row["sprint"]);
$array["projectName"] = urldecode($row["projectName"]);
$array["appDesignerProjs"] = urldecode($row["appDesignerProjs"]);
$array["plsqlObjects"] = urldecode($row["plsqlObjects"]);
$array["otherObjects"] = urldecode($row["otherObjects"]);
$array["projectOwner"] = urldecode($row["projectOwner"]);
$array["sumWorkCompleted"] = urldecode($row["sumWorkCompleted"]);
$array["testingType"] = urldecode($row["testingType"]);
$array["proofTesting"] = urldecode($row["proofTesting"]);
$array["requestTo"] = urldecode($row["requestTo"]);
$array["reqFullName"] = urldecode($row["reqFullName"]);//newRepresentative
$array["author"] = urldecode($row["author"]);
$array["authorFullName"] = urldecode($row["authorFullName"]);
$array["requestDate"] = urldecode($row["requestDate"]);
//$requestDate = date("Y-m-d H:i:s", time());
array_push($jsonArray, $array);
echo(json_encode($jsonArray));


?>
