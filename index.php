<?php
// Begin the PHP session so we have a place to store the username
session_start();

require_once('php/connect.php');

$client_id = $OKTA_CLIENT_ID;
$client_secret = $OKTA_CLIENT_SECRET;
$redirect_uri = $OKTA_REDIRECT_URL;

$metadata_url = $OKTA_METADATA_URL;

// Fetch the authorization server metadata which contains a few URLs
// that we need later, such as the authorization and token endpoints
$metadata = http($metadata_url);

if(isset($_GET['code'])) {

  if($_SESSION['state'] != $_GET['state']) {
    die('Authorization server returned an invalid state parameter');
  }

  if(isset($_GET['error'])) {
    die('Authorization server returned an error: '.htmlspecialchars($_GET['error']));
  }

  $response = http($metadata->token_endpoint, [
    'grant_type' => 'authorization_code',
    'code' => $_GET['code'],
    'redirect_uri' => $redirect_uri,
    'client_id' => $client_id,
    'client_secret' => $client_secret,
  ]);

  if(!isset($response->access_token)) {
    die('Error fetching access token');
  }

  $token = http($metadata->introspection_endpoint, [
    'token' => $response->access_token,
    'client_id' => $client_id,
    'client_secret' => $client_secret,
  ]);

  if($token->active == 1) {
    $_SESSION['username'] = $token->username;
    //header('Location: /');
    header('Location: ' . $redirect_uri);
    die();
  }

}

// If there is a username, they are logged in, and we'll show the logged-in view
if(isset($_SESSION['username'])) {
  $username_parts = explode("@", $_SESSION['username']);
  $username_short = $username_parts[0];
  $conn = db_connect();
  $query = $conn->query("SELECT * FROM signoff_project_admins WHERE username = '$username_short'");
    if ($query->num_rows > 0) {
      setcookie('SignOffAdminUser', $username_short, time() + 60*60*24*30, '/');
      header("Location: admin.php");
    }
}

// If there is no username, they are logged out, so show them the login link
if(!isset($_SESSION['username'])) {
  // Generate a random state parameter for CSRF security
//$_SESSION['state'] = bin2hex(random_bytes(5));
$_SESSION['state'] = bin2hex(openssl_random_pseudo_bytes(5));

// Build the authorization URL by starting with the authorization endpoint
// and adding a few query string parameters identifying this application
$authorize_url = $metadata->authorization_endpoint.'?'.http_build_query([
  'response_type' => 'code',
  'client_id' => $client_id,
  'redirect_uri' => $redirect_uri,
  'state' => $_SESSION['state'],
  'scope' => 'openid',
]);
  //echo '<p>Not logged in</p>';
  //echo '<p><a href="'.$authorize_url.'">Log In</a></p>';
  header('Location: ' . $authorize_url);
  die();
}

function http($url, $params=false) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  if($params)
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
  return json_decode(curl_exec($ch));
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

    <title>Sign-off</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <!--<link href="css/bootstrap-theme.min.css" rel="stylesheet">-->
    <!-- Bootstrap select theme -->
    <link href="css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/bootstrap-table.min.css" rel="stylesheet">
    <link href="css/bootstrap.icon-large.min.css" rel="stylesheet">
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
      padding-bottom: 30px;
    }
  </style>
  <p><a href="index.php" style='position:absolute;margin-left:12px;display:block;'>Sign-off Requests</a></p>
  <div class="container" style='max-width: 980px;'>
    <div class='row'>
     <p style='text-align: center;'><img src='images/PMOLogo.jpg' height=100/></p>
    </div>
    <div class='row'>
      <div class='col-md-6'>
      <h3>Your Pending Sign-off Requests</h3>
      <div id='noRequests' style="display: none;">
        <p>You do not have any Sign-off requests.</p>
      </div>
    </div>
    </div>
    <!-- Begin Requests Panel -->
     <div class='row' id="pendingRequestsRow">
      <div class="panel panel-default">
        <!--<div class="panel-heading"><h3 class='panel-title'>Requests</h3></div>-->
        <!-- Table -->
        <!--<div class='input-group' style='margin: 5px;'>
          <span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></span>
          <input class="form-control" type='text' id='filter' placeholder='Type here to filter these results.'></input>
        </div>-->
        <div id='tableLoading' style="display: none;">
          <p style='text-align: center;'>Loading&nbsp;&nbsp;&nbsp;<img width=30 height=30 src="images/loading.gif"/></p>
        </div>
        <table class="table table-striped" id='pendingProjectRequestTable'>
          <thead>
            <th>Project</th>
            <th>Status</th>
            <th class='hidden-xs'>Date</th>
            <th class='hidden-xs hidden-sm hidden-md'>Identifier</th>
            <th class='hidden'>Sprint</th>
            <th class='hidden'>App Designer Projects</th>
            <th class='hidden'>PL/SQL Objects</th>
            <th class='hidden'>Other Objects</th>
            <th class='hidden-xs hidden-sm hidden-md'>Requested By</th>
          </thead>
          <tbody class="searchable" id="pendingProjectRequestTableBody">
            <!--populated by loadProjectRequests(); -->
          <tbody>
        </table>
      </div>
    </div>
    <div class='row' style="margin-top:16px;">
      <div class='col-md-6'>
      <h4>Your Completed Sign-off Requests <small><em>past 12 months</em></small></h4>
      <div id='receivedNoRequests' style="display: none;">
        <p>You do not have any Sign-off requests.</p>
      </div>
    </div>
    </div>
    <div class='row' id="receivedRequestsRow">
     <div class="panel panel-default">
        <table class="table table-striped" id='receivedProjectRequestTable'>
          <thead>
            <th>Project</th>
            <th>Status</th>
            <th class='hidden-xs'>Date</th>
            <th class='hidden-xs hidden-sm hidden-md'>Identifier</th>
            <th class='hidden'>Sprint</th>
            <th class='hidden'>App Designer Projects</th>
            <th class='hidden'>PL/SQL Objects</th>
            <th class='hidden'>Other Objects</th>
            <th class='hidden-xs hidden-sm hidden-md'>Requested By</th>
          </thead>
          <tbody class="searchable" id="receivedProjectRequestTableBody">
            <!--populated by loadProjectRequests(); -->
          <tbody>
        </table>
      </div>
    </div>
    <!-- End Requests Panel -->
  </div>
</div>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/bootstrap-table.min.js"></script>
    <script>
    var userId = "<?php echo $username_short;?>";
    function loadProjectRequestsByUser(type) {
    	$('#' + type + "ProjectRequestTable").hide();
    	$("#tableLoading").show();
    	$.ajaxSetup({ cache: false });
    	$.getJSON("php/loadProjectRequestsByUser.php", {
    		userId: userId,
    	}, function(data) {
    		$('#' + type + 'ProjectRequestTableBody').html("");
    		var html = "";
    		var len = data[type].length;
        if(len === 0) {
          $("#tableLoading").hide();
          $("#" + type + "requestsRow").hide();
      		$("#noRequests").show();
        }
        else {
    		for (var i = 0; i< len; i++) {
          html+= "<tr>";
    			var t = data[type][i].requestDate.split(/[- :]/);
    			var d = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
          if (data[type][i].projectName.length > 72) {
    				cutStr = "";
    				cutStr = data[type][i].projectName;
    				cutStr = cutStr.substring(0,72) + "...";
    				html += "<td style='font-size:1.2em;'><a href='respond.php?requestId=" + data[type][i].requestId + "'>" + cutStr + "</a></td>";
    			} else {
    	    		html += "<td style='font-size:1.2em;'><a href='respond.php?requestId=" + data[type][i].requestId + "'>" + data[type][i].projectName + "</a></td>";
    	    	}
            if (data[type][i].status == "Pending") {
              html += "<td><span class='label label-primary hidden-xs hidden-md hidden-sm'>" + data[type][i].status + "</span><span class='label label-primary hidden-lg'>P</span></td>";
            }
            if (data[type][i].status == "Received") {
              html += "<td><span class='label label-success hidden-xs hidden-md hidden-sm'>Completed</span><span class='label label-success hidden-lg'>C</span></td>";
            }
            if (data[type][i].status == "Declined") {
              html += "<td><span class='label label-danger hidden-xs hidden-md hidden-sm'>" + data[type][i].status + "</span><span class='label label-danger hidden-lg'>D</span></td>";
            }
    			html += "<td class='hidden-xs'>" + (d.getMonth() + 1)  + "/" + d.getDate() + "/" + d.getFullYear() + "</td>";
    			if (data[type][i].typeOfWork == "project") {
    				html += "<td class='hidden-xs hidden-sm hidden-md'>" + data[type][i].projectId + "</td>";
    			} else if (data[type][i].typeOfWork == "ticket") {
    				html += "<td class='hidden-xs hidden-sm hidden-md'>TICK:" + data[type][i].ticketNumber + "</td>";
    			} else if (data[type][i].typeOfWork == "req") {
    				html += "<td class='hidden-xs hidden-sm hidden-md'>Doc: " + data[type][i].projectId + "</td>";
    			} else {
    				html += "<td class='hidden-xs hidden-sm hidden-md'>ERROR</td>";
    			}
    	    	html += "<td class='hidden'>sprint:" + data[type][i].sprint + "</td>";
    	    	html += "<td class='hidden'>" + data[type][i].appDesignerProjs + "</td>";
    	    	html += "<td class='hidden'>" + data[type][i].plsqlObjects + "</td>";
    	    	html += "<td class='hidden'>" + data[type][i].otherObjects + "</td>";
    	    	html += "<td class='hidden-xs hidden-sm hidden-md'>" + data[type][i].authorFullName + "</td>";

    	    	html += "</tr>";
    		}
    		$('#' + type + 'ProjectRequestTableBody').append(html);
    		$("#tableLoading").hide();
    		$('#' + type + "ProjectRequestTable").show();
      }
    		$.ajaxSetup({ cache: true });
    	});
    }
    $(document).ready(function() {
        var pending = 'pending';
        var received = 'received';
    		loadProjectRequestsByUser(pending);
        loadProjectRequestsByUser(received);
    });
    </script>
    <!--<script src="js/docs.min.js"></script> -->
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--<script src="js/ie10-viewport-bug-workaround.js"></script>-->
  </body>
</html>
