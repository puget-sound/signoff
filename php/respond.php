<?php
//Logging Set up
require_once 'php/KLogger.php';
$log = KLogger::instance('log/');
$myname = basename(__FILE__, '.php') . ".php";
// Load the settings from the central config file
require_once 'cas/config.php';
// Load the CAS lib
require_once $phpcas_path . '/CAS.php';
// Initialize phpCAS
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

// For production use set the CA certificate that is the issuer of the cert
// on the CAS server and uncomment the line below
// phpCAS::setCasServerCACert($cas_server_ca_cert_path);

// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
phpCAS::setNoCasServerValidation();

// force CAS authentication
phpCAS::forceAuthentication();
$username = phpCAS::getUser();
//setcookie('requestResponseUser', $username, time() + 60*60*24*30, '/');

$requestId = urlencode($_GET['requestId']);
require_once('connect.php');
$conn = db_connect();
$query = $conn->query("SELECT * FROM signoff_project_requests WHERE requestId = $requestId");
if ($query->num_rows > 0) {
  $log->logInfo("$myname | $username is Accessing Request $requestId...");
  $result = $query->fetch_array(MYSQLI_ASSOC);
  if ($username != $result['requestTo']) {
    $log->logWarn("$myname | $username tried to access Request $requestId. $username is not recipient. Access Denied.");
    header("Location: error.php?request=$requestId&error=wp");
  } else if ($result['status'] == "Received" || $result['status'] == "Declined") {
    $log->logInfo("$myname | $username Accessed Request $requestId. Request has ALREADY been signed-off. Redirect to overview.php");
    header("Location: overview.php?requestId=$requestId&action=locked");
  }
} else {
  $log->logWarn("$myname | $username tried to access Request $requestId. Cannot locate request number in the Database.");
  header("Location: error.php?error=na");
}
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

    <title>Respond | Project Sign-Off</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
    <!-- Bootstrap select theme -->
    <link href="css/bootstrap-select.min.css" rel="stylesheet">

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
        <div class="panel-heading"><h3 class="panel-title">Request for Sign-off (#<?php echo(urldecode($result["requestId"])); ?>)</h3></div>
        <div class="panel-body">
          <div style="display: none;" class="alert alert-danger" id="testingError" role="alert"></div>
          <table>
            <tr>
              <td style='padding: 3px;'><strong>Type:</strong></td>
              <td style='padding: 3px;'>  <?php

                if ($result['typeOfWork'] == 'project') {
                  echo ("Project");
                }
                if ($result['typeOfWork'] == 'ticket') {
                  echo ("Ticket (KACE)");
                }
                if ($result['typeOfWork'] == "req") {
                  echo ("Requirements or Documentation");
                }
                ?>
                </td>
            <tr>
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
              if ($result['typeOfWork'] == "project") {
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
          </table>
        </div>
      </div>
    </div>
     <div class='row' <?php if ($result['typeOfWork'] == 'req') {echo("style='display: none;'");} ?>>
      <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Summary of Work Completed</h3></div>
        <div class="panel-body">
          <p style="padding: 15px;">
            <?php echo(urldecode($result['sumWorkCompleted'])); ?>
          </p>
        </div>
      </div>
    </div>
    <div class='row'>
      <form method='get' action='php/saveResponse.php'>
        <input type="hidden" name="requestId" value=<?php echo("'" . $result['requestId'] . "'") ?>>
        <input type="hidden" id="typeOfWorkField" name="typeOfWork" value=<?php echo("'" . $result['typeOfWork'] . "'") ?>>
      <div class="panel panel-default" <?php if ($result['typeOfWork'] == 'req') {echo("style='display: none;'");} ?>>
        <div class="panel-heading"><h3 class="panel-title">Testing Information</h3></div>
        <div class="panel-body">
          <?php
          if ($result['testingType'] == "link") {
              echo("<input type='text' name='link' id='testLink' class='form-control' placeholder='Paste link to Testing Documentation here.'/>");
          }
          if ($result['testingType'] == "text") {
              echo("<textarea class='form-control' name='text' id='testText' rows=4 placeholder='Please briefly describe your testing process and procedure.'></textarea>");
          }
          ?>
          <p><br><strong>Please note:</strong> If an untested issue is found after installation to Production, a period of reasonable time maybe required for Technology Services to identify and implement a fix for the issue.</p>
        </div>
      </div>
    </div>
     <div class='row'>
      <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Additional Comments</h3></div>
        <div class="panel-body">
          <textarea class="form-control" name='comments' rows=4 placeholder='Please add any additonal comments in this section. (optional)'></textarea>
        </div>
      </div>
    </div>
    <div class='row'>
      <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Sign-off (<?php echo(urldecode($result['reqFullName'])); ?>)</h3></div>
        <div class="panel-body">
          <div style="display: none;" class="alert alert-danger" id="signOffError" role="alert"></div>
          <p><strong>I have tested and verified this project is working as expected.</strong></p>
          <div class="radio">
            <label>
            <input type="radio" name="optionsRadios" id="agree" value="agree">
            <?php
              echo ("I approve the documents to move forward with this project");


            ?>
            </label>
          </div>
          <div class="radio">
            <label>
            <input type="radio" name="optionsRadios" id="disagree" value="disagree" data-target='#confirm-delete' data-toggle='modal'>
            I do NOT approve the installation of this project to Production.
            </label>
          </div>
          <input type='submit' value='Submit' onclick='return validateSubmission();' style='width: 100px;'class='btn btn-default'/?
        </div>
      </div>
    </div>
    </div>


    <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="ConfirmDelete" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Please Confirm</h4>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to DECLINE the installation of this project to Production?</p>
                    <p class="debug-url"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Confirm</button>
                </div>
            </div>
        </div>
    </div>



   <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script>
      function validateSubmission() {
        $("#signOffError").hide();
        $("#testingError").hide();
        if ($("#testLink").val() == "" || $("#testText").val() == "" && $("#typeOfWorkField").val() != "req" ) {
          $("#testingError").html("Oops! <strong>Testing</strong> verification is missing. Please complete.");
          $("#testingError").show();
          return false;
        }
        if (!$("input[name='optionsRadios']:checked").val()){
          $("#signOffError").html("Oops! <strong>Sign-off</strong> verification is missing.");
          $("#signOffError").show();
          return false;
        }
      }
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <!--<script src="js/docs.min.js"></script> -->
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--<script src="js/ie10-viewport-bug-workaround.js"></script>-->
  </body>
</html>
