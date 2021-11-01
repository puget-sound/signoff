<?php
// Begin the PHP session so we have a place to store the username
session_start();

require_once('php/connect.php');

$client_id = $OKTA_CLIENT_ID;
$client_secret = $OKTA_CLIENT_SECRET;
$redirect_uri = $OKTA_REDIRECT_URL;

$metadata_url = $OKTA_METADATA_URL;
$authorization_endpoint = $OKTA_AUTHORIZATION_ENDPOINT;

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
    } else {
      header("Location: error.php?error=nauth");
    }
  /*echo '<p>Logged in as</p>';
  echo '<p>' . $username_short . '</p>';
  echo '<p><a href="/?logout">Log Out</a></p>';*/
  //die();
}

// If there is no username, they are logged out, so show them the login link
if(!isset($_SESSION['username'])) {
  // Generate a random state parameter for CSRF security
//$_SESSION['state'] = bin2hex(random_bytes(5));
$_SESSION['state'] = bin2hex(openssl_random_pseudo_bytes(5));

// Build the authorization URL by starting with the authorization endpoint
// and adding a few query string parameters identifying this application
$authorize_url = $authorization_endpoint.'?'.http_build_query([
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
      padding-top: 70px;
      padding-bottom: 30px;
    }
  </style>
    <!-- Fixed navbar -->
    <nav class="navbar navbar-inverse navbar-fixed-top" id="topnavbar" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <div class="navbar-brand" style="color: white;">Sign-off Administration</div>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <!--<li class="active"><a href="#">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>-->
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Menu <span class="caret"></span></a>
              <ul class="dropdown-menu" role="menu">
                <li class="dropdown-header">Actions</li>
                <li><a href="#" onclick="refreshView();"><span class="glyphicon glyphicon-dashboard" aria-hidden="true"></span>&nbsp;&nbsp;Request Dashboard</a></li>
                <li><a href="#" data-target='#requestModal' data-toggle='modal'><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;&nbsp;New Sign-off Request</a></li>
                <li class="divider"></li>
                <li class="dropdown-header">Management</li>
                <li><a href="#" data-target='#manageProjectOwners' data-toggle='modal'><span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>&nbsp;&nbsp;Project Owners</a></li>
                <li><a href="#" data-target='#userManagement' data-toggle='modal'><span class="glyphicon glyphicon-user" aria-hidden="true"></span>&nbsp;&nbsp;User Administration</a></li>
                <li><a href="chartjsExample.html"><span class="glyphicon glyphicon-stats" aria-hidden="true"></span>&nbsp;&nbsp;Analytics Dashboard</a></li>
                <li class="divider"></li>
                <li class="dropdown-header">Tools</li>
                <li><a href='#' data-toggle='modal' data-target='#KACEBookmarkletModal'><span class='glyphicon glyphicon-star-empty'></span>&nbsp;&nbsp;KACE Bookmarklet</a></li>
              </ul>
            </li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
              <li>
                <a href="#" data-target='#requestModal' data-toggle='modal'><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;&nbsp;New Sign-off Request</a>
              </li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container theme-showcase" role="main">
    <div style="display: none;" class="alert alert-success" id="mainSuccessMessage" role="alert"></div>

    <!--Begin Search and Filter -->
    <div class='row' id='searchAndFilterRow'>
      <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Search</h3></div>
        <div class="panel-body">
          <form class='form-inline'>
            <div class="form-group">
              <select class='selectpicker' id='filterRType'>
                <option value='all'>View All Items</option>
                <option value='projects'>View Projects Only</option>
                <option value='ticket'>View Tickets Only</option>
              </select>
            </div>
            <div class="form-group">
              <label for="exampleInputEmail1">created in the</label>
              <select class='selectpicker' id='filterDateRange'>
                <option value='30'>Last 30 Days</option>
                <option value='60'>Last 60 Days</option>
                <option value='90'>Last 90 Days</option>
                <option value='all'>All Time</option>
              </select>
            </div>
            <div class="form-group">
              <label for="exampleInputEmail1">and</label>
              <select class='selectpicker' id='filterRec'>
                <option value='showrec'>Show All Requests</option>
                <option value='hiderec'>Hide Received Requests</option>
                <option value='hidepend'>Hide Pending Requests</option>
              </select>
            </div>
            <div class="checkbox">
                <label>
                  <input type="checkbox" id="filterAuthor"> Show my requests only
                </label>
              </div>
            <button type='button' onclick='resetProjectRequests();' class='btn btn-warning btn-sm pull-right'>Reset</button>
          </form>
        </div>
      </div>
    </div>
   <!-- End Serach and Filter -->
    <!-- Begin Requests Panel -->
     <div class='row' id="requestsRow">
      <div class="panel panel-default">
        <div class="panel-heading"><h3 class='panel-title'>Requests</h3></div>
        <!-- Table -->
        <div class='input-group' style='margin: 5px;'>
          <span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></span>
          <input class="form-control" type='text' id='filter' placeholder='Type here to filter these results.'></input>
        </div>
        <div id='tableLoading' style="display: none;">
          <p style='text-align: center;'>Loading&nbsp;&nbsp;&nbsp;<img width=30 height=30 src="images/loading.gif"/></p>
        </div>
        <table class="table table-striped" id='projectRequestTable'>
          <thead>
            <th class='hidden-xs'>Date</th>
            <th class='hidden-xs hidden-sm hidden-md'>Identifier</th>
            <th>Project Name</th>
            <th class='hidden'>Sprint</th>
            <th class='hidden'>App Designer Projects</th>
            <th class='hidden'>PL/SQL Objects</th>
            <th class='hidden'>Other Objects</th>
            <th class='hidden-xs hidden-sm hidden-md'>Author</th>
            <th class='hidden-xs'>Representative</th>
            <th>Status</th>
            <th>Actions</th>
          </thead>
          <tbody class="searchable" id="projectRequestTableBody">
            <!--populated by loadProjectRequests(); -->
          <tbody>
        </table>
      </div>
    </div>
    <!-- End Requests Panel -->
    <!-- Begin New Request Form -->
    <div class='modal fade' role='dialog' id='requestModal'>
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">New Sign-off Request</h4>
          </div>
          <div class="modal-body">
          <div style="display: none;" class="alert alert-danger" id="errorValidate" role="alert"></div>
          <form class='form-horizontal'>
            <div class="form-group">
              <label for="projectOwnerSelect" class="col-sm-4 control-label">Type</label>
              <div class="col-sm-8">
                <select id='typeOfWork' class="selectpicker" title="Please Select One...">
                  <option value='1' data-hidden="true"></option>
                  <option value='project'>Project</option>
                  <option value='ticket'>KACE Ticket</option>
                  <option value='req'>Requirements &amp; Docs</option>
                </select>
              </div>
            </div>
            <div class="form-group" id='ticketNumberGroup'>
              <label for="projectNumber" class="col-sm-4 control-label">Ticket Number</label>
              <div class="col-sm-8">
                <div class='input-group'>
                <span class="input-group-addon">
                    TICK:
                  </span>
                <input style="width: 100px;"type="text" class="form-control" id="ticketNumber" placeholder="34833">
                </div>
              </div>
            </div>
            <div id='soundNetGroup'>
                <div class="form-group">
                  <label for="projectNumber" class="col-sm-4 control-label">Project ID *</label>
                  <div class="col-sm-8">
                    <input style="width: 100px;"type="text" class="form-control" id="projectNumber" placeholder="P0100">
                  </div>
                </div>
                <div class="form-group">
                  <label for="projectNumber" class="col-sm-4 control-label">Google Drive Folder Link</label>
                  <div class="col-sm-8">
                    <input style="width: 300px;"type="text" class="form-control" id="soundNetLink" placeholder="Paste Google Drive Link here">
                  </div>
                </div>
                <div class="form-group">
                  <label for="projectNumber" class="col-sm-4 control-label">LP Task Link</label>
                  <div class="col-sm-8">
                    <input style="width: 300px;"type="text" class="form-control" id="lpProjectLink" placeholder="Paste LiquidPlanner Link here">
                  </div>
                </div>
          </div>
            <div class="form-group">
              <label for="sprintNumber" class="col-sm-4 control-label">Sprint</label>
              <div class="col-sm-8">
                <input style="width: 75px;"type="text" class="form-control" id="sprintNumber" placeholder="10">
              </div>
            </div>
           <div class="form-group">
              <label for="projectName" class="col-sm-4 control-label">Project Name *</label>
              <div class="col-sm-8">
                <input style="width: 250px;"type="text" class="form-control" id="projectName" placeholder="Project or Ticket Name">
              </div>
            </div>
            <div id='hidedetailfields'>
            <div class="form-group">
              <label for="appDesignerProjects" class="col-sm-4 control-label">App Designer Projects</label>
              <div class="col-sm-8">
                <input style="width: 250px;"type="text" class="form-control" id="appDesignerProjects" placeholder="If applicable, separate by commas.">
              </div>
            </div>
            <div class="form-group">
              <label for="appDesignerProjects" class="col-sm-4 control-label">PL/SQL Objects</label>
              <div class="col-sm-8">
                <input style="width: 250px;"type="text" class="form-control" id="plsqlObjects" placeholder="If applicable, separate by commas.">
              </div>
            </div>
            <div class="form-group">
              <label for="appDesignerProjects" class="col-sm-4 control-label">Other</label>
              <div class="col-sm-8">
                <input style="width: 250px;"type="text" class="form-control" id="otherObjects" placeholder="If applicable, separate by commas.">
              </div>
            </div>
          </div>
            <div class="form-group">
              <label for="projectOwnerSelect" class="col-sm-4 control-label">Project Owner *</label>
              <div class="col-sm-8">
                <select id='projectOwnerSelect' class="selectpicker" title="Please Select One..." data-live-search="true">
                </select>
              </div>
            </div>
            <div class="form-group" id='sumWorkGroup'>
              <label for="summaryWorkCompleted" class="col-sm-4 control-label">Summary of Work Completed *</label>
              <div class="col-sm-8">
                <textarea style="width: 300px;" id="summaryWorkCompleted" class="form-control" rows=10 placeholder="Summarize Work Completed for this Project. Including and phasing / caveats"></textarea>
              </div>
            </div>
            <div class="form-group" id='testinggroup'>
              <label for="testingTypeSelect" class="col-sm-4 control-label">Proof of Testing *</label>
              <div class="col-sm-8">
              <select id='testingTypeSelect' class="selectpicker" title="Testing provided by:">
                  <option data-hidden="true" value='1'></option>
                  <option value='link'>Excel Spreadsheet / Link</option>
                  <option value='text'>Testing Summary (Text)</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label for="addAdditionalUser" class="col-sm-4 control-label">Request Sign-off From *</label>
              <div class="col-sm-8" id='input_fields_wrap'>
                <div style='width: 325px; margin-top: 2px;' class="input-group request-representative-div">
                  <span class="input-group-addon">
                    <a href="#" id="remove_field">Remove</a>
                  </span>
                  <input type="text" class="form-control request-representative" id="requestUsers[]" placeholder="username" aria-describedby="email-addon">
                  <span class="input-group-addon" id="email-addon">@pugetsound.edu</span>
                </div>
              </div>
              <div class="col-sm-4 col-sm-offset-4" style="margin-top:5px;">
                <button type="button" class="btn btn-default btn-sm" id="addUsers">Add additional user</button>
              </div>
            </div>
          </form>
    <!-- end modal -->
    </div>
          <div class="modal-footer">
             <button type="button" onclick="refreshView();" class="btn btn-link" data-dismiss="modal">Cancel</button><button style='margin-left: 2px' type="button" class="btn btn-default" onclick="submitNewRequest();">Submit</button>
          </div>
        </div>
      </div>
    </div>
    <!-- End New Request Form -->
    <!-- Copy and paste link modal-->
    <div class='modal fade' role='dialog' id='copyLinkModal'>
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">Get Request Link</h4>
          </div>
          <div class="modal-body">
            <p>Copy the following link in an e-mail to send to the respective sign-off representatives.</p>
            <input id='copyPasteModalLink' style="width:95%;">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Done</button>
          </div>
        </div>
      </div>
    </div>
    <!-- End link modal -->

    <!-- delete request modal-->
    <div class='modal fade' role='dialog' id='deleteRequestModal'>
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">Delete Sign-off Request</h4>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to <strong>delete</strong> this sign-off request?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-link" data-dismiss="modal">Cancel</button>
            <a id="deleteRequestBtn" class="btn btn-danger">Delete</a>
          </div>
        </div>
      </div>
    </div>
    <!-- End delete request modal -->
    <!-- Start User Management Modal -->
    <div class='modal fade' role='dialog' id='userManagement'>
      <div class='modal-dialog'>
        <div class='modal-content'>
          <div class='modal-header'>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">User Management</h4>
          </div>
          <div class="modal-body">
          <div style="display: none;" class="alert alert-danger" id="userManageValidate" role="alert"></div>
          <div style="display: none;" class="alert alert-success" id="userManageSuccess" role="alert"></div>
          <form>
            <div class="form-group">
              <label for="addProjectOwner">Add User</label>
                <div class="form-inline">
                <input style="width: 200px;"type="text" class="form-control" id="addUser" placeholder="jlogger">
                <button class="btn btn-default" type="button" onclick='addUserFromField();'>Add</button>
                </div>
            </div>
          </form>
          <table class="table table-striped">
            <thead>
                <th>Username</th>
                <th>Full Name</th>
                <th>Delete</th>
            </thead>
            <tbody id='userManagementTable'>
            </tbody>
          </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Done</button>
          </div>
        </div>
    </div>
  </div>
    <!-- End User Management Modal -->
    <!-- Start Add/Remove Project Owners -->
    <div class='modal fade' role='dialog' id='manageProjectOwners'>
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">Manage Project Owners</h4>
          </div>
          <div class="modal-body">
          <div style="display: none;" class="alert alert-danger" id="projectOwnerValidate" role="alert"></div>
          <div style="display: none;" class="alert alert-success" id="addOwnerSuccess" role="alert"></div>
          <form>
            <div class="form-group">
              <label for="addProjectOwner">Add Project Owner</label>
                <div class="form-inline">
                <input style="width: 200px;"type="text" class="form-control" id="addProjectOwner" placeholder="Academic Advising">
                <button class="btn btn-default" type="button" onclick='addProjectOwnerFromField();'>Add</button>
                </div>
            </div>
          </form>
            <div class="form-group">
                <div class="input-group">
                <span class="input-group-addon">Filter</span>
                <input style="max-width: 300px;"type="text" class="form-control" id="filterProjectOwners" placeholder="Search...">
                </div>
            </div>
          <table class="table table-striped">
            <thead>
                <th>Project Owner Name</th>
                <th>Delete</th>
            </thead>
            <tbody id='projectOwnerTable' class='ownersearchable'>
            </tbody>
          </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Done</button>
          </div>
        </div>
      </div>
    </div>
    </div> <!-- /container -->

    <!-- Add new Representative modal-->
    <div class='modal fade' role='dialog' id='addRepresentativeModal'>
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">Add Approver</h4>
          </div>
          <div class="modal-body">
            <div style="display: none;" class="alert alert-danger" id="addRepresentativeValidate" role="alert"></div>
            <div style="display: none;" class="alert alert-success" id="addRepresentativeSuccess" role="alert"></div>
            <form>
              <div class="form-group">
                  <div class="form-inline">
                      <label class="control-label">User Name</label>
                      <input type="text" class="form-control" id="addRepresentative" placeholder="username">
                  </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-link" data-dismiss="modal">Cancel</button>
            <button class="btn btn-default" type="submit" id="addRepresentativeBtn" >Add</button>
          </div>
        </div>
      </div>
    </div>
    <!-- End new representative modal -->


    <!-- Begin Edit Request Modal Request -->
    <div class='modal fade' role='dialog' id='requestModalEdit'>
      <div style="display: none;" id='editRequestId'></div>
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">Edit Request</h4>
          </div>
          <div class="modal-body">
          <div style="display: none;" class="alert alert-danger" id="errorValidateEdit" role="alert"></div>
          <form class='form-horizontal'>
            <div class="form-group">
              <label for="projectOwnerSelect" class="col-sm-4 control-label">Type</label>
              <div class="col-sm-8">
                <select id='typeOfWorkEdit' class="selectpicker" title="Please Select One...">
                  <option value='1' data-hidden="true"></option>
                  <option value='project'>Project</option>
                  <option value='ticket'>KACE Ticket</option>
                  <option value='req'>Requirements &amp; Docs</option>
                </select>
              </div>
            </div>
            <div class="form-group" id='ticketNumberGroupEdit'>
              <label for="projectNumber" class="col-sm-4 control-label">Ticket Number</label>
              <div class="col-sm-8">
                <div class='input-group'>
                <span class="input-group-addon">
                    TICK:
                  </span>
                <input style="width: 100px;"type="text" class="form-control" id="ticketNumberEdit" placeholder="34833">
                </div>
              </div>
            </div>
            <div id='soundNetGroupEdit'>
                <div class="form-group">
                  <label for="projectNumber" class="col-sm-4 control-label">Project ID *</label>
                  <div class="col-sm-8">
                    <input style="width: 100px;"type="text" class="form-control" id="projectNumberEdit" placeholder="P0100">
                  </div>
                </div>
                <div class="form-group">
                  <label for="projectNumber" class="col-sm-4 control-label">Google Drive Folder Link</label>
                  <div class="col-sm-8">
                    <input style="width: 300px;"type="text" class="form-control" id="soundNetLinkEdit" placeholder="Paste Google Drive Link here">
                  </div>
                </div>
                <div class="form-group">
                  <label for="projectNumber" class="col-sm-4 control-label">LP Task Link</label>
                  <div class="col-sm-8">
                    <input style="width: 300px;"type="text" class="form-control" id="lpProjectLinkEdit" placeholder="Paste LiquidPlanner Link here">
                  </div>
                </div>
          </div>
            <div class="form-group">
              <label for="sprintNumber" class="col-sm-4 control-label">Sprint</label>
              <div class="col-sm-8">
                <input style="width: 75px;"type="text" class="form-control" id="sprintNumberEdit" placeholder="10">
              </div>
            </div>
           <div class="form-group">
              <label for="projectName" class="col-sm-4 control-label">Project Name *</label>
              <div class="col-sm-8">
                <input style="width: 250px;"type="text" class="form-control" id="projectNameEdit" placeholder="Project or Ticket Name">
              </div>
            </div>
            <div id='hidedetailfieldsEdit'>
            <div class="form-group">
              <label for="appDesignerProjects" class="col-sm-4 control-label">App Designer Projects</label>
              <div class="col-sm-8">
                <input style="width: 250px;"type="text" class="form-control" id="appDesignerProjsEdit" placeholder="If applicable, separate by commas.">
              </div>
            </div>
            <div class="form-group">
              <label for="appDesignerProjects" class="col-sm-4 control-label">PL/SQL Objects</label>
              <div class="col-sm-8">
                <input style="width: 250px;"type="text" class="form-control" id="plsqlObjectsEdit" placeholder="If applicable, separate by commas.">
              </div>
            </div>
            <div class="form-group">
              <label for="appDesignerProjects" class="col-sm-4 control-label">Other</label>
              <div class="col-sm-8">
                <input style="width: 250px;"type="text" class="form-control" id="otherObjectsEdit" placeholder="If applicable, separate by commas.">
              </div>
            </div>
          </div>
            <div class="form-group">
              <label for="projectOwnerSelect" class="col-sm-4 control-label">Project Owner *</label>
              <div class="col-sm-8">
                <select id='projectOwnerSelectEdit' class="selectpicker" title="Please Select One..." data-live-search="true">
                </select>
              </div>
            </div>
            <div class="form-group" id='sumWorkGroupEdit'>
              <label for="summaryWorkCompleted" class="col-sm-4 control-label">Summary of Work Completed *</label>
              <div class="col-sm-8">
                <textarea style="width: 300px;" id="summaryWorkCompletedEdit" class="form-control" rows=10 placeholder="Summarize Work Completed for this Project. Including and phasing / caveats"></textarea>
              </div>
            </div>
            <div class="form-group" id='testinggroupEdit'>
              <label for="testingTypeSelect" class="col-sm-4 control-label">Proof of Testing *</label>
              <div class="col-sm-8">
              <select id='testingTypeSelectEdit' class="selectpicker" title="Testing provided by:">
                  <option data-hidden="true" value='1'></option>
                  <option value='link'>Excel Spreadsheet / Link</option>
                  <option value='text'>Testing Summary (Text)</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="appDesignerProjects" class="col-sm-4 control-label">Request To</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="requestToEdit" placeholder="username" readonly>
                </div>
            </div>

            <div class="form-group">
              <label for="appDesignerProjects" class="col-sm-4 control-label" >Requester Full Name</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="requesterFullNameEdit" placeholder="username" readonly>
                </div>
            </div>

            <div class="form-group">
              <label for="appDesignerProjects" class="col-sm-4 control-label">Author</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="authorEdit" placeholder="username">
                </div>
            </div>

            <div class="form-group">
              <label for="appDesignerProjects" class="col-sm-4 control-label">Author Full Name</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="authorFullNameEdit" placeholder="username" readonly/>
                </div>
            </div>

            <div class="form-group">
              <label for="appDesignerProjects" class="col-sm-4 control-label">Request Date</label>
                <div class="col-sm-8">
                    <input type="date" class="form-control" name="requestDate" id="requestDateEdit" aria-describedby="basic-addon1" placeholder="01/31/2015" readonly/>
                </div>
            </div>

          </form>
    <!-- end modal -->
    </div>
          <div class="modal-footer">
             <button type="button" onclick="refreshView();" class="btn btn-link" data-dismiss="modal">Cancel</button>
             <button style='margin-left: 2px' type="submit" id="saveRequestEditsBtn" class="btn btn-default">Save</button>
          </div>
        </div>
      </div>
    </div>
    <!-- End New Request Form -->
    <!-- KACE Bookmarklet modal-->
    <div class='modal fade' role='dialog' id='KACEBookmarkletModal'>
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">KACE Bookmarklet</h4>
          </div>
          <div class="modal-body">
            <div><?php $bkmrklt_author =  $username_short; ?>
            <a id="bookmarklet" class="btn btn-success btn-lg" href="javascript:(function()%7B!function()%7Bfunction%20e()%7B(window.myBookmarklet%3Dfunction()%7Bfunction%20e(e)%7B%22https%3A%2F%2F<?php echo $BOOKMARKLET_SERVER;?>.pugetsound.edu%22%3D%3D%3De.origin%26%26(jQuery(%22%23trayframe_veil%22).fadeOut(750)%2CjQuery(%22%23trayframe%20iframe%22).slideUp(500)%2CsetTimeout(%22jQuery('%23trayframe').remove()%22%2C750))%7D0%3D%3DjQuery(%22%23trayframe%22).length%26%26(top.frames.kbox%3FjQuery(%22html%22).append(r)%3AjQuery(%22body%22).append(r)%2CjQuery(%22%23trayframe_veil%22).fadeIn(750))%2CjQuery(%22%23trayframe_veil%22).click(function(e)%7BjQuery(%22%23trayframe_veil%22).fadeOut(750)%2CjQuery(%22%23trayframe%20iframe%22).slideUp(500)%2CsetTimeout(%22jQuery('%23trayframe').remove()%22%2C750)%7D)%2Cwindow.addEventListener(%22message%22%2Ce%2C!1)%7D)()%7Dvar%20t%3D%22%22%2Ca%3D%22%22%3Btop.frames.kbox%3F(t%3Dtop.frames.kbox.document.getElementsByClassName(%22k-main%22)%5B0%5D.getElementsByTagName(%22h1%22)%5B0%5D.innerHTML%2Ca%3Dtop.frames.kbox.document.getElementById(%22edit-title%22).innerText)%3A(t%3Ddocument.getElementsByClassName(%22k-main%22)%5B0%5D.getElementsByTagName(%22h1%22)%5B0%5D.innerHTML%2Ca%3Ddocument.getElementById(%22edit-title%22).innerText)%3Bvar%20r%3D%22%3Cdiv%20id%3D'trayframe'%20style%3D'position%3Afixed%3Bz-index%3A1050%3B'%3E%09%3Cdiv%20id%3D'trayframe_veil'%3E%3C%2Fdiv%3E%09%3Ciframe%20src%3D'https%3A%2F%2F<?php echo $BOOKMARKLET_SERVER;?>.pugetsound.edu%2Fsignoff%2Fcreate.php%3FticketNumber%3D%22%2Bt%2B%22%26author=<?php echo $bkmrklt_author;?>%26projectTitle%3D%22%2Ba%2B%22'%20onload%3D%5C%22jQuery('%23trayframe%20iframe').slideDown(500)%3B%5C%22%3EEnable%20iFrames.%3C%2Fiframe%3E%09%3Cstyle%20type%3D'text%2Fcss'%3E%09%09%23trayframe_veil%20%7B%20display%3A%20none%3B%20position%3A%20fixed%3B%20width%3A%20100%25%3B%20height%3A%20100%25%3B%20top%3A%200%3B%20left%3A%200%3B%20background-color%3A%20rgba(255%2C255%2C255%2C.25)%3B%20cursor%3A%20pointer%3B%20z-index%3A%20900%3B%20%7D%09%09%23trayframe%20iframe%20%7B%20display%3A%20none%3B%20position%3A%20fixed%3B%20top%3A%200%3B%20left%3A%200%3B%20width%3A%20100%25%3B%20height%3A417px%3B%20z-index%3A%20999%3B%20border%3Anone%3B%20margin%3A%200%3B%20%7D%09%3C%2Fstyle%3E%3C%2Fdiv%3E%22%3Bif(top.frames.kbox%26%26document.getElementsByTagName(%22html%22)%5B0%5D.appendChild(document.createElement(%22body%22))%2Cvoid%200%3D%3D%3Dwindow.jQuery)%7Bvar%20i%3D!1%2Cn%3Ddocument.createElement(%22script%22)%3Bn.src%3D%22https%3A%2F%2Fajax.googleapis.com%2Fajax%2Flibs%2Fjquery%2F1.3.2%2Fjquery.min.js%22%2Cn.onload%3Dn.onreadystatechange%3Dfunction()%7Bi%7C%7Cthis.readyState%26%26%22loaded%22!%3Dthis.readyState%26%26%22complete%22!%3Dthis.readyState%7C%7C(i%3D!0%2Ce())%7D%2Cdocument.getElementsByTagName(%22head%22)%5B0%5D.appendChild(n)%7Delse%20e()%7D()%7D)()">Create Signoff</a> <span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> Drag this button into your browser toolbar</div>
            <br><br>
            <p>Use this bookmarklet to create a sign-off request from a KACE ticket.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Done</button>
          </div>
        </div>
      </div>
    </div>
    <!-- End link modal -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/bootstrap-table.min.js"></script>
    <script src="js/functions.js"></script>
    <!--<script src="js/docs.min.js"></script> -->
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--<script src="js/ie10-viewport-bug-workaround.js"></script>-->
  </body>
</html>
