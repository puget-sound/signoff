<?php

require_once('connect.php');
$conn = db_connect();

if(isset($_GET["findYears"])){
  $query = $conn->query("SELECT DISTINCT YEAR(requestDate) as year FROM signoff_project_requests");
  $jsonArray = array();
  $yearsArray = array();
  while($row = $query->fetch_array(MYSQLI_ASSOC)){
    array_push($yearsArray, $row["year"]);
  }
  array_push($jsonArray, $yearsArray);
  $jsonArray = json_encode($jsonArray);
  if($query){
    echo $jsonArray;
  }else{
    echo "Error! MySQL Err: " . mysql_error($conn);
  }
}

if(isset($_GET["loadNumberOfProjectRequests"])){

  $year1 = $_GET["selectYear1"];
  $year2 = $_GET["selectYear2"];

  $query = $conn->query("SELECT YEAR(requestDate) AS year, MONTH(requestDate) AS month, COUNT(DISTINCT projectName) AS numberOfRequests
  FROM signoff_project_requests
  WHERE YEAR(requestDate)=$year1 OR YEAR(requestDate)=$year2 GROUP BY YEAR(requestDate), MONTH(requestDate)");
  $jsonArray = array();

  $firstYearCount = array_fill(0,12,0);
  $secondYearCount = array_fill(0,12,0);
  $months = array();
  while ($row = $query->fetch_array(MYSQLI_ASSOC)){
    if($row["year"] == $year1){
      $firstYearCount[$row["month"]-1] = $row["numberOfRequests"];
    }else{
      $secondYearCount[$row["month"]-1] = $row["numberOfRequests"];
    }
  }
  array_push($jsonArray, $firstYearCount);
  array_push($jsonArray, $secondYearCount);
  array_push($jsonArray, $months);

$jsonArray = json_encode($jsonArray);
  if($query){
    echo $jsonArray;
  }else{
    echo "Error! MySQL Err: " . mysql_error($conn);
  }

}

if(isset($_GET["typeOfRequestsYear"])){

  $requestsYear = $_GET["typeOfRequestsYear"];

  $query = $conn->query("SELECT typeOfWork, YEAR(requestDate), Count(DISTINCT projectName) AS countOfWorkType
  FROM signoff_project_requests
  WHERE YEAR(requestDate)=$requestsYear GROUP BY typeOfWork, YEAR(requestDate)");

  $projectsArray = array_fill(0,3,0);

  while ($row = $query->fetch_assoc()){
    if($row["typeOfWork"] == "project"){
      $projectsArray[0] = $row["countOfWorkType"];
    }
    if($row["typeOfWork"] == "ticket"){
      $projectsArray[1] = $row["countOfWorkType"];
    }
    if($row["typeOfWork"] == "req"){
      $projectsArray[2] = $row["countOfWorkType"];
    }
  }

  $projectsArray = json_encode($projectsArray);
    if($query){
      echo $projectsArray;
    }else{
      echo "Error! MySQL Err: " . mysql_error($conn);
    }
}


if(isset($_GET["projectsPerAuthorYear"])){


  $jsonArray = array();
  $requestsYear = $_GET["projectsPerAuthorYear"];
  $query = $conn->query("SELECT author, YEAR(requestDate), Count(DISTINCT projectName) AS numberOfRequests
  FROM signoff_project_requests
  WHERE YEAR(requestDate)=$requestsYear GROUP BY author, YEAR(requestDate)");
  $authorsArray = array();
  $projectCountArray = array();
  while ($row = $query->fetch_array(MYSQLI_ASSOC)){
    array_push($authorsArray, $row["author"]);
    array_push($projectCountArray, $row["numberOfRequests"]);
  }
  array_push($jsonArray, $authorsArray);
  array_push($jsonArray, $projectCountArray);

  $jsonArray = json_encode($jsonArray);
    if($query){
      echo $jsonArray;
    }else{
      echo "Error! MySQL Err: " . mysql_error($conn);
    }
}

if(isset($_GET["projectsPerOfficeYear"])){

  $jsonArray = array();
  $requestsYear = $_GET["projectsPerOfficeYear"];

  //Number Of Projects Requested 2015
  $query = $conn->query("SELECT projectOwner, YEAR(requestDate), Count(DISTINCT projectName) AS numberOfRequests
  FROM signoff_project_requests
  WHERE YEAR(requestDate)=$requestsYear AND projectOwner != '1' GROUP BY projectOwner, YEAR(requestDate)");
  $requestorsArray = array();
  $requestCountArray = array();
  while ($row = $query->fetch_array(MYSQLI_ASSOC)){

    //if the office was already in the array, add the found value to what was already there
    $office = trim(urldecode($row["projectOwner"]));

    if(array_key_exists($office, $requestorsArray)){
      $previousCount = $requestorsArray[$office];
      $requestorsArray[$office] = $previousCount + $row["numberOfRequests"];
    }else{
      $requestorsArray[$office] = $row["numberOfRequests"];
    }
  }
  array_push($jsonArray, array_keys($requestorsArray));
  array_push($jsonArray, $requestorsArray);
  $jsonArray = json_encode($jsonArray);
  if($query){
    echo $jsonArray;
  }else{
    echo "Error! MySQL Err: " . mysql_error($conn);
  }
}
?>
