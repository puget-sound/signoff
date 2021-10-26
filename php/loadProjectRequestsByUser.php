<?php
$userId = $_GET['userId'];


require_once('connect.php');
$conn = db_connect();

$query = $conn->query("SELECT * FROM signoff_project_requests
 WHERE (requestTo = '$userId') AND (status = 'Pending')
 ORDER BY requestDate DESC");
$json = array();
while ($result = $query->fetch_array(MYSQLI_ASSOC)) {
	 $result['projectId'] = urldecode($result['projectId']);
	 $result['projectName'] = urldecode($result['projectName']);
	 $result['ticketNumber'] = urldecode($result['ticketNumber']);
	 $result['requestTo'] = urldecode($result['requestTo']);
	 $result['reqFullName'] = urldecode($result['reqFullName']);
	 $result['authorFullName'] = urldecode($result['authorFullName']);
	 array_push($json, $result);
}

$query_completed = $conn->query("SELECT * FROM signoff_project_requests
 WHERE (requestTo = '$userId') AND (status = 'Received') AND (submitDate > DATE_SUB(now(), INTERVAL 12 MONTH))
 ORDER BY requestDate DESC");
$json_completed = array();
while ($result_completed = $query_completed->fetch_array(MYSQLI_ASSOC)) {
	 $result_completed['projectId'] = urldecode($result_completed['projectId']);
	 $result_completed['projectName'] = urldecode($result_completed['projectName']);
	 $result_completed['ticketNumber'] = urldecode($result_completed['ticketNumber']);
	 $result_completed['requestTo'] = urldecode($result_completed['requestTo']);
	 $result_completed['reqFullName'] = urldecode($result_completed['reqFullName']);
	 $result_completed['authorFullName'] = urldecode($result_completed['authorFullName']);
	 array_push($json_completed, $result_completed);
}

echo '{"received": ' . json_encode($json_completed) . ', "pending": ' . json_encode($json) . '}';
//echo(json_encode($push));

?>
