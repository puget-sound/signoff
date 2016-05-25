<?php

require_once('connect.php');
$conn = db_connect();

$query = "SELECT * from signoff_project_requests";
$result = $conn->query($query);

$count = $result->num_rows;

echo("Number of requests: " . $count);

?>
