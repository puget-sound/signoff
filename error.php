<?php
setcookie("SignOffAdminUser", "", 1);
setcookie("requestResponseUser", "", 1);
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

    <title>Error | Project Sign-Off</title>

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
  <body role="document" onload="checkErrors();">
    <style>
    body {
      padding-top: 150px;
      padding-bottom: 50px;
    }
    </style>
    <div class="container" style="max-width: 600px;">
      <p><img src='images/PMOLogo.jpg' height=100/></p>
     <div class='row' id='login'>
      <div class="panel panel-default" style="max-width: 500px;">
        <div class="panel-heading"><h3 class="panel-title">Error</h3></div>
        <div class="panel-body">
          <div style="display: none;" class="alert alert-danger" id="loginError" role="alert"></div>
        </div>
      </div>
    </div>
    </div>

   <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script>
    function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
    }

    function checkErrors() {
     var error = getUrlVars()["error"];
     if (error == "wuwp") {
        $("#loginError").html("Incorrect <strong>username or password</strong>. Please try again.");
        $("#loginError").show();
     } else if (error == "se") {
        $("#loginError").html("Your current session <strong>expired</strong>. Please sign in again.");
        $("#loginError").show();
     } else if (error == "nauth") {
        $("#loginError").html("You are not authorized to access this page.");
        $("#loginError").show();
     } else if (error == "pl") {
        $("#loginError").html("Please sign-in to continue.");
        $("#loginError").show();
     } else if (error == "na") {
        $("#loginError").html("Invalid Request. This Request may have been deleted. Please check with your Technology Services project representative.");
        $("#loginError").show();
     } else if (error == "wp") {
        $("#loginError").html("You do not have permission to view this request. If this is an error, please contact Technology Services.");
        $("#loginError").show();
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