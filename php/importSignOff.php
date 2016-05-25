<?php

if (isset($_POST['submit'])) {

	require_once('connect.php');
	$conn = db_connect();
	$mysqli = $conn;

	if ($mysqli->connect_errno) {
		echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}else{
		importSignoff($mysqli);	//import the signoff to the connected database
		importFile($mysqli);
	}

	header('Location: ../addOldRequest.html');
}

function importSignoff($mysqli){

	//Prepare
	if (!($stmt = $mysqli->prepare("INSERT INTO signoff_project_requests(requestId, typeOfWork, ticketNumber, projectId, soundNetLink,
	liquidPlannerLink, sprint, projectName, appDesignerProjs, plsqlObjects, otherObjects, projectOwner,
	sumWorkCompleted, testingType, proofTesting, requestTo, reqFullName, author, authorFullName,
	requestDate, submitDate, status, additionalComments)
	VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"))){

		echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;

	}else{	//Bind
		$requestDate = $_POST['requestDate'];
		$requestDateFormatted = date("Y-m-d H:i:s", strtotime($requestDate));

		$submitDate = $_POST['submitDate'];
		$submitDateFormatted = date("Y-m-d H:i:s", strtotime($submitDate));


		echo $_POST['typeOfWork']."\r\n";
		echo $_POST['ticketNumber']."\r\n";
		echo $_POST['projectID']."\r\n";
		echo $_POST['soundNetLink']."\r\n";
		echo $_POST['liquidPlannerLink']."\r\n";
		echo $_POST['sprint']."\r\n";
		echo $_POST['projectName']."\r\n";
		echo $_POST['appDesingerProjs'];
		echo $_POST['plsqlObjects']."\r\n";
		echo $_POST['otherObjects']."\r\n";
		echo $_POST['projectOwner']."\r\n";
		echo $_POST['sumWorkCompleted']."\r\n";
		echo $_POST['testingType']."\r\n";
		echo $_POST['proofTesting']."\r\n";
		echo $_POST['requestTo']."\r\n";
		echo $_POST['reqFullName']."\r\n";
		echo $_POST['author']."\r\n";
		echo $_POST['authorFullName']."\r\n";
		echo $requestDateFormatted."\r\n";
		echo $submitDateFormatted."\r\n";
		echo $_POST['status']."\r\n";
		echo $_POST['additionalComments']."\r\n";

		$requestID = null;
		$typeOfWork = $_POST['typeOfWork'];
		$ticketNum =  $_POST['ticketNumber'];
		$projectID =  $_POST['projectID'];
		$soundNetLink = $_POST['soundNetLink'];
		$liquidPlannerLink = $_POST['liquidPlannerLink'];
		$sprint = $_POST['sprint'];
		$projectName = $_POST['projectName'];
		$appDesignerProjs = $_POST['appDesingerProjs'];
		$plsqlObjects = $_POST['plsqlObjects'];
		$otherObjects = $_POST['otherObjects'];
		$projectOwner = $_POST['projectOwner'];
		$sumWorkCompleted = $_POST['sumWorkCompleted'];
		$testingType = $_POST['testingType'];
		$proofTesting = $_POST['proofTesting'];
		$requestTo = $_POST['requestTo'];
		$reqFullName = $_POST['reqFullName'];
		$author = $_POST['author'];
		$authorFullName= $_POST['authorFullName'];
		$status= $_POST['status'];
		$additionalComments = $_POST['additionalComments'];

		echo "binding";
		$bindResult = $stmt->bind_param("issssssssssssssssssssss" ,$requestID,$_POST['typeOfWork'],$_POST['ticketNumber'],$_POST['projectID'],$_POST['soundNetLink'],$_POST['liquidPlannerLink'],
		$_POST['sprint'],$_POST['projectName'],$appDesignerProjs,$_POST['plsqlObjects'],$_POST['otherObjects'],$projectOwner,
		$_POST['sumWorkCompleted'],$_POST['testingType'],$_POST['proofTesting'],$_POST['requestTo'],$_POST['reqFullName'],$_POST['author'],
		$_POST['authorFullName'],$requestDateFormatted,$submitDateFormatted,$_POST['status'],$_POST['additionalComments']);

		if(!$bindResult){
			echo "Bind failed";
		}
		//Execute
		if (!$stmt->execute()) {
			echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}else {
			echo "Form Save completed!";
		}


	}
}

function importFile($mysqli){

	$target_dir = "../signoff_files/"; //get out of the php directory and go into signoff_files
	$file =  $_FILES["fileToUpload"]["name"];
	$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
	$uploadOk = 1;
	$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
	echo $target_file;

	// Check file size
	if ($_FILES["fileToUpload"]["size"] > 500000) {
		echo "Sorry, your file is too large.";
		$uploadOk = 0;
	}

	// Check if file already exists
	if (file_exists($target_file)) {
		echo "Sorry, file already exists.";
	}

	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
		echo "Sorry, your file was not uploaded.";

	} else {// if everything is ok, try to upload file

		if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
			echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";

			if (!($stmt = $mysqli->prepare("INSERT INTO signoff_files(id, requestId, filepath) VALUES (?, ?, ?);"))){
				echo "Prepare file query failed: (" . $mysqli->errno . ") " . $mysqli->error;
			} else {
				echo "prepare success";
				$lastRequestId = mysqli_insert_id($mysqli);//get previous inserted id
				echo "last inserted ID ".$lastRequestId;
				$stmt->bind_param("iis", $id, $lastRequestId, $target_file);

				if (!$stmt->execute()) {
					echo "Execute database file upload failed: (" . $stmt->errno . ") " . $stmt->error;
				}else {
					echo "Save file to database completed!";
				}
			}

		} else {
			echo "Sorry, there was an error uploading your file.";
		}
}
}



?>
