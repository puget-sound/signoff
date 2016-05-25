<?php
require_once('connect.php');
$conn = db_connect();
$query = $conn->query("SELECT * FROM signoff_project_owners ORDER BY ownerName ASC");
$push = array();
while ($result = $query->fetch_array(MYSQLI_ASSOC)) {
	 $result['ownerName'] = urldecode($result['ownerName']);
	 array_push($push, $result);
}
echo(json_encode($push));

?>
