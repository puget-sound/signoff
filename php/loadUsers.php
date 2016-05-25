<?php
require_once('connect.php');
$conn = db_connect();
$query = $conn->query("SELECT * FROM signoff_project_admins ORDER BY activeDate DESC");
$push = array();
while ($result = $query->fetch_array(MYSQLI_ASSOC)) {
	 $result['username'] = urldecode($result['username']);
	 $result['fullName'] = urldecode($result['fullName']);
	 array_push($push, $result);
}
echo(json_encode($push));

?>
