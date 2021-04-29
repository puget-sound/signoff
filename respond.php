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
require_once('php/connect.php');
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

    <title>Sign-off - Respond</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap select theme -->
    <link href="css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
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
    <div class="container" style='max-width: 860px;'>
      <div class='row'>
       <p style='text-align: center;'><img src='images/PMOLogo.jpg' height=100/></p>
      </div>
      <div class='row'>
        <div class='col-md-6'>
        <h4>Request for Sign-off</h4>
        <p class="text-muted" style="margin-top:-6px;margin-bottom:8px;">Request #<?php echo(urldecode($result["requestId"])); ?></p>
      </div>
      <div class='col-md-6'>
        <p class="text-muted up-right-desktop" style="padding-top:6px;font-size:13px;">Requested on <strong><?php echo date('M j Y g:i A', strtotime($result['requestDate'])); ?></strong></p>
      </div>
      </div>
     <div class='row'>
       <div class='col-md-12'>
       <div class="panel panel-default">
  <div class="panel-body">
       <h3 style="margin-top:4px;"><?php echo(urldecode($result['projectName'])); ?></h3>

       <div class='row'>
         <div class='col-md-7'>
           <p><strong style="width:110px;display:inline-block;">Project Owner</strong><?php echo(urldecode($result['projectOwner'])); ?><p>
           <p><strong style="width:110px;display:inline-block;">Type</strong><?php

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
             <p>
               <p><strong style="width:110px;display:inline-block;">
           <?php
             if ($result['typeOfWork'] == "project") {
               echo("Project ID</strong>");
               echo(urldecode($result['projectId']) . "</p>");
             }
             if ($result['typeOfWork'] == "ticket") {
               echo("Ticket</strong>");
               echo("TICK:" . urldecode($result['ticketNumber']) . "</p>");
             }
           ?></p>
           <p <?php if ($result['sprint'] === '') {echo("style='display: none;'");} ?>>
             <strong style="width:110px;display:inline-block;">Sprint</strong><?php echo(urldecode($result['sprint'])); ?></p>

         </div>
         <div class='col-md-5'>
           <p><strong style="width:110px;display:inline-block;">Sent To</strong><?php echo(urldecode($result['reqFullName'])); ?></p>

           <p><strong style="width:110px;display:inline-block;">Requested By</strong><?php echo(urldecode($result['authorFullName'])); ?></p>


</div>
</div>
          <div <?php if ($result['typeOfWork'] == 'req') {echo("style='display: none;'");} ?>>
            <hr>
          <h4 style="margin-top:14px;">Summary of Work Completed</h4>
            <p>
              <?php echo(urldecode($result['sumWorkCompleted'])); ?>
            </p>
          </div>
        </div>
      </div>
    </div>
    </div>
    <div class='row'>
      <div class='col-md-12'>
      <form method='get' action='php/saveResponse.php'>
        <input type="hidden" name="requestId" value=<?php echo("'" . $result['requestId'] . "'") ?>>
        <input type="hidden" id="typeOfWorkField" name="typeOfWork" value=<?php echo("'" . $result['typeOfWork'] . "'") ?>>
      <div id="testing-info" class="panel panel-info" <?php if ($result['typeOfWork'] == 'req') {echo("style='display: none;'");} ?>>
        <div class="panel-heading"><h3 class="panel-title">Testing Information *</h3></div>
        <div class="panel-body">
          <?php
          if ($result['testingType'] == "link") {
              echo("<input type='text' name='link' id='testLink' class='form-control' placeholder='Paste link to Testing Documentation here.'/>");
          }
          if ($result['testingType'] == "text" || $result['testingType'] == "") {
              echo("<textarea class='form-control' name='text' id='testText' rows=4 placeholder='Please briefly describe your testing process and procedure.'></textarea>");
          }
          ?>
          <p><br><strong>Please note:</strong> If an untested issue is found after installation to Production, a period of reasonable time maybe required for Technology Services to identify and implement a fix for the issue.</p>
        </div>
      </div>
    </div>
    </div>
     <div class='row'>
       <div class='col-md-12'>
      <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Additional Comments</h3></div>
        <div class="panel-body">
          <textarea class="form-control" name='comments' rows=4 placeholder='Please add any additonal comments in this section. (optional)'></textarea>
        </div>
      </div>
    </div>
  </div>
    <div class='row'>
      <div class='col-md-12'>
      <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Sign-off from <strong><?php echo(urldecode($result['reqFullName'])); ?> *</strong></h3></div>
        <div class="panel-body">
          <div style="display: none;" class="alert alert-danger" id="signOffError" role="alert"></div>
          <div style="display: none;" class="alert alert-danger" id="testingError" role="alert"></div>
          <p><strong>I have tested and verified this project is working as expected.</strong></p>
          <div class="radio">
            <label>
            <input type="radio" name="optionsRadios" id="agree" value="agree">
            <?php
            if ($result['typeOfWork'] != "req") {
              echo("I approve the installation of this project to Production.");

            }else{
              echo ("I approve the documents for this project.");
            }

            ?>
            </label>
          </div>
          <div class="radio">
            <label>
            <input type="radio" name="optionsRadios" id="disagree" value="disagree" data-target='#confirm-delete' data-toggle='modal'>
            <?php
            if ($result['typeOfWork'] != "req") {
              echo("I do not approve the installation of this project to Production.");

            }else{
              echo ("I do not approve the documents for this project.");
            }

            ?>
            </label>
          </div>
          <input type='submit' value='Submit' onclick='return validateSubmission();' style='width: 100px;margin-top:8px;'class='btn btn-primary'/?
        </div>
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
                    <button type="button" class="btn btn-default" data-dismiss="modal" onclick='return deselectVerification();'>Cancel</button>
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
          $("#testingError").html("The <strong>Testing Information</strong> section is required. Please complete the blue section above.");
          $("#testingError").show();
          return false;
        }
        if (!$("input[name='optionsRadios']:checked").val()){
          $("#signOffError").html("<strong>Sign-off</strong> verification is missing. Please choose an option below.");
          $("#signOffError").show();
          return false;
        }
      }
      function deselectVerification(){
        $('#disagree').attr('checked',false);
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
