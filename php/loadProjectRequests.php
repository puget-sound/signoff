<?php
session_start();

$username_parts = explode("@", $_SESSION['username']);
$whoami = $username_parts[0];

$filterRType = $_GET['filterRType'];
$filterDateRange = $_GET['filterDateRange'];
$filterRec = $_GET['filterRec'];
$filterAuthor = $_GET['filterAuthor'];

//Translate input values into SQL
if ($filterRType == "all") {
	$filterRType = "%";
} else if ($filterRType == "projects") {
	$filterRType = "project";
} else if ($filterRType == "tickets") {
	$filterRType = "ticket";
}

if ($filterDateRange == "all") {
	$filterDateRange = "LIKE '%'";
} else if ($filterDateRange == "30") {
	$filterDateRange = ">= NOW() - INTERVAL 30 DAY";
} else if ($filterDateRange == "60") {
	$filterDateRange = ">= NOW() - INTERVAL 60 DAY";
} else if ($filterDateRange == "90") {
	$filterDateRange = ">= NOW() - INTERVAL 90 DAY";
}

if ($filterRec == "showrec") {
	$filterRec = "LIKE '%'";
} else if ($filterRec == "hiderec") {
	$filterRec = "LIKE 'Pending'";
} else if ($filterRec == "hidepend") {
	$filterRec = "LIKE 'Received'";
}

if($filterAuthor == "true") {
	$filterAuthor = "LIKE '$whoami'";
}
else {
	$filterAuthor = "LIKE '%'";
}

require_once('connect.php');
$conn = db_connect();
if (!$conn) {
	exit;
}
$query = $conn->query("SELECT * FROM signoff_project_requests
 WHERE (typeOfWork LIKE '$filterRType')
 AND (requestDate " . $filterDateRange . ")
 AND (status " . $filterRec . ")
 AND (author " . $filterAuthor . ")
 ORDER BY requestDate DESC");
$push = array();
while ($result = $query->fetch_array(MYSQLI_ASSOC)) {
	 $result['projectId'] = urldecode($result['projectId']);
	 $result['projectName'] = urldecode($result['projectName']);
	 $result['ticketNumber'] = urldecode($result['ticketNumber']);
	 $result['requestTo'] = urldecode($result['requestTo']);
	 $result['reqFullName'] = urldecode($result['reqFullName']);
	 $result['authorFullName'] = urldecode($result['authorFullName']);
	 array_push($push, $result);
}

echo(json_encode($push));

?>
