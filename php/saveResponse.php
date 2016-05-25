<?php
require_once 'KLogger.php';
$log = KLogger::instance('../log/');
$myname = basename(__FILE__, '.php') . ".php";

$requestId = $_GET['requestId'];
$proofTesting = "";
if (isset($_GET['text'])) {
	$proofTesting = urlencode($_GET['text']);
}
if (isset($_GET['link'])) {
	$proofTesting = urlencode($_GET['link']);
}
$clientComments = "";
$clientComments = urlencode($_GET['comments']);
$clientSelect = $_GET['optionsRadios'];
$clientChoice = "";
if ($clientSelect == "agree") {
	$clientChoice = "Received";
}
if ($clientSelect == "disagree") {
	$clientChoice = "Declined";
}
require_once('connect.php');
$conn = db_connect();
$query = $conn->query("UPDATE signoff_project_requests
	SET proofTesting = '$proofTesting',
		status = '$clientChoice',
		additionalComments = '$clientComments',
		submitDate = CURRENT_TIMESTAMP
	WHERE requestId = $requestId");
$log->logInfo("$myname | Response received for Request $requestId");
if ($query) {
	//Email the sender that there is a response waiting for them.
	//...To do that, query the table to get the person to send the mail to
	$emailQuery = $conn->query("SELECT * FROM signoff_project_requests WHERE requestId = $requestId")->fetch_array(MYSQLI_ASSOC);
	$to = $emailQuery['author'] . "@pugetsound.edu";
	$subject = "Update to Sign-off Request: #" . $emailQuery['requestId'];
	$message = "<div style='font-family: Arial, Helvetica, sans-serif; font-size: 11pt;'>";
	$message .= "Hello, <br><br>";
	$message .= "This message is informing you that <strong>" . urldecode($emailQuery['reqFullName']) . "</strong> has submitted a response to a Sign-Off Request that you created.";
	$message .= "<br><br>Basic Information:<br>";
	$message .= "<strong>Project or Ticket Title: </strong>" . urldecode($emailQuery['projectName']);
	$message .= "<br><strong>Sent to: </strong>" . urldecode($emailQuery['reqFullName']);
	$message .= "<br><strong>Last Updated:</strong> " . $emailQuery['submitDate'] . "<br>";
	$message .= "<strong>Request status: " . $emailQuery['status'] . "</strong><br><br>";
	if (urldecode($emailQuery['additionalComments']) != "") {
		$message .= urldecode($emailQuery['reqFullName']) . " also left the following comments:<br>" . urldecode($emailQuery['additionalComments']);
	}
	$message .= "<br><br>To view the full request, follow this link: <a href='http://signoff.pugetsound.edu/view.php?requestId=" . $emailQuery['requestId'] . "'>http://signoff.pugetsound.edu/view.php?requestId=" . $emailQuery['requestId'] . "</a><br><br>";
	$message .= "</div>";
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: TS Project Management Office <tsprojects@pugetsound.edu>' . "\r\n" .
	'Reply-To: tsprojects@pugetsound.edu' . "\r\n" .
	'X-Mailer: PHP/' . phpversion();
	mail($to, $subject, $message, $headers);
	$log->logInfo("$myname | E-mail sent to " . $emailQuery['author'] . "@pugetsound.edu with project request update.");
	header("Location: ../overview.php?requestId=$requestId&action=submit");
}
?>
