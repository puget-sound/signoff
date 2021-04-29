<?php

$requestId = $_GET['requestId'];
require_once('php/connect.php');
$conn = db_connect();
$query = $conn->query("SELECT * FROM signoff_project_requests WHERE requestId = $requestId");
$result = $query->fetch_array(MYSQLI_ASSOC);

$fileQuery = $conn->query("SELECT filepath FROM signoff_files WHERE requestId = $requestId");
$fileQueryResult = $fileQuery->fetch_array(MYSQLI_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title>Sign-off - View Request</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
    <!-- Bootstrap select theme -->
    <link href="css/bootstrap-select.min.css" rel="stylesheet">
    <link rel="icon" href="images/grey-favicon.png" type="image/png">

    <!-- Custom styles for this template -->
    <!--<link href="theme.css" rel="stylesheet">-->


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body role="document">
    <style>
    body {
      padding-top: 10px;
      padding-bottom: 10px;
    }
    </style>
    <div class="container" style='max-width: 800px;'>
     <div class='row'>
      <p style='text-align: center;'><img src='images/PMOLogo.jpg' height=100/></p>
     </div>
     <div class='row'>
      <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Request Information (#<?php echo(urldecode($result["requestId"])); ?>)</h3></div>
        <div class="panel-body">
          <?php

          if ($result["status"] == "Pending") {
            echo("<div class='alert alert-info' role='alert'>This request is currently <strong>pending.</strong></div>");
          }

          if ($result["status"] == "Received") {
            echo("<div class='alert alert-success' role='alert'>Sign-off was <strong>received</strong> by <strong>".urldecode($result['reqFullName'])."</strong> on <strong>".date('M j Y g:i A', strtotime($result['submitDate']))."</strong></div>");
          }

          if ($result["status"] == "Declined") {
            echo("<div class='alert alert-danger' role='alert'>Sign-off was <strong>declined</strong> by <strong>".urldecode($result['reqFullName'])."</strong> on <strong>".date('M j Y g:i A', strtotime($result['submitDate']))."</strong></div>");
          }

          ?>

          <table style="margin-bottom:14px;">
            <tr>
              <td style='padding: 3px;'><strong>Project Name:</strong></td>
              <td style='padding: 3px;'><?php echo(urldecode($result['projectName'])); ?></td>
            </tr>
            <tr>
              <td style='padding: 3px;'><strong>Project Owner:</strong></td>
              <td style='padding: 3px;'><?php echo(urldecode($result['projectOwner'])); ?></td>
            </tr>
            <tr>
            <?php
              if ($result['typeOfWork'] == "project" || $result['typeOfWork'] == "req") {
                echo("<td style='padding: 3px;'><strong>Project ID:</strong></td>");
                echo("<td style='padding: 3px;'>" . urldecode($result['projectId']) . "</td>");
              }
              if ($result['typeOfWork'] == "ticket") {
                echo("<td style='padding: 3px;'><strong>Ticket:</strong></td>");
                echo("<td style='padding: 3px;'>TICK:" . urldecode($result['ticketNumber']) . "</td>");
              }
            ?>
            </tr>
            <tr>
              <td style='padding: 3px;'><strong>Sprint:</strong></td>
              <td style='padding: 3px;'><?php echo(urldecode($result['sprint'])); ?></td>
            </tr>
            <tr>
              <td style='padding: 3px;'><strong>Requested By:</strong></td>
              <td style='padding: 3px;'><?php echo(urldecode($result['authorFullName'])); ?></td>
            </tr>
            <tr>
              <td style='padding: 3px;'><strong>Sent To:</strong></td>
              <td style='padding: 3px;'><?php echo(urldecode($result['reqFullName'])); ?></td>
            </tr>
            <tr>
              <td style='padding: 3px;'><strong>Request Date:</strong></td>
              <td style='padding: 3px;'><?php echo date('M j Y g:i A', strtotime($result['requestDate'])); ?></td>
            </tr>
            <?php if ($result["status"] == "Pending") { ?>
            <tr>
              <td style='padding: 3px;'><strong>Get link:</strong></td>
              <td style='padding: 3px;'>
                <input id="get-signoff-link" class="form-control input-sm" type="text" value="http://signoff.pugetsound.edu/respond.php?requestId=<?php echo $result['requestId']?>" style="width:450px;" selected="selected">
                </td>
            <tr>
              <?php }?>
          </table>
          <?php
          if ($result['ticketNumber'] != "") {
            echo("<a class='btn btn-info' target='_blank' href='https://kbox.pugetsound.edu/adminui/ticket?ID=" . urldecode($result['ticketNumber']) . "'>KBOX TICK:" . urldecode($result['ticketNumber']) ."</a>");
          }
          if ($result['soundNetLink'] != "") {
            echo("<a class='btn btn-info' href='" . urldecode($result['soundNetLink']) . "' target='_blank'>Google Drive Project Folder</a>");
          }
          if ($result['liquidPlannerLink'] != "") {
            echo("<a style='margin: 3px;' class='btn btn-info' href='" . urldecode($result['liquidPlannerLink']) . "' target='_blank'>LiquidPlanner Project</a>");
          }
		      if ($fileQueryResult['filepath'] != ""){
			         $filePathFound = $fileQueryResult['filepath'];
			        echo("<a style='margin: 3px;' class='btn btn-primary' target='_blank' href='php/downloadFile.php?filepath=$filePathFound'>File:" . basename($filePathFound) ."</a>");
		      }
          ?>
      </div>
    </div>
  </div>
     <div class='row' <?php if ($result['typeOfWork'] != 'req') {echo("style='display: none';");} ?>>
        <div class="panel panel-default">
          <div class="panel-heading"><h3 class='panel-title'>Review Requirements or Documentation</h3></div>
          <div class='panel-body'>
            <p>Please review these documents or requirements by following this link: <?php echo("<a href='" . urldecode($result['soundNetLink']) . "' target='_blank'>" . urldecode($result['projectName']) . "</a>")  ?></p>
          </div>
     </div>
   </div>
     <div class='row' <?php if ($result['typeOfWork'] == 'req') {echo("style='display: none';");} ?>>
      <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Summary of Work Completed</h3></div>
        <div class="panel-body">
          <table>
          <tr>
            <td style='padding: 3px;'><strong>App Designer Projects:</strong></td>
            <td style='padding: 3px;'><?php echo(urldecode($result['appDesignerProjs'])); ?></td>
          </tr>
          <tr>
            <td style='padding: 3px;'><strong>PL/SQL Objects:</strong></td>
            <td style='padding: 3px;'><?php echo(urldecode($result['plsqlObjects'])); ?></td>
          </tr>
          <tr>
            <td style='padding: 3px;'><strong>Other Objects:</strong></td>
            <td style='padding: 3px;'><?php echo(urldecode($result['otherObjects'])); ?></td>
          </tr>
          <tr>
            <td style='padding: 3px;'><strong>Work Summary:</strong></td>
            <td style='padding: 3px;'><?php echo(nl2br(urldecode($result['sumWorkCompleted']))); ?></td>
          </tr>
        </table>
        </div>
      </div>
    </div>
    <div class='row' <?php if ($result['typeOfWork'] == 'req') {echo("style='display: none';");} ?>>
      <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Testing Information</h3></div>
        <div class="panel-body">
          <p style="padding: 15px;">
          <?php
          if ($result['proofTesting'] == "") {
            echo("<em>Sign-off is still pending or testing is incomplete.</em>");
          } else {
            echo(nl2br(urldecode($result['proofTesting'])));
          }
          ?>
          </p>
        </div>
      </div>
    </div>
     <div class='row'>
      <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Additional Comments</h3></div>
        <div class="panel-body">
          <p style="padding: 15px;">
          <?php
          if ($result['additionalComments'] == "") {
            echo("<em>None provided.</em>");
          } else {
            echo(nl2br(urldecode($result['additionalComments'])));
          }
          ?>
          </p>
        </div>
      </div>
    </div>
    </div>

   <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script>
    $(document).ready(function() {
        $("#get-signoff-link").select();
        $("#get-signoff-link").on('click', function() {
          $(this).select();
        });
    });
    </script>
    <!--<script src="js/docs.min.js"></script> -->
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--<script src="js/ie10-viewport-bug-workaround.js"></script>-->
  </body>
</html>
