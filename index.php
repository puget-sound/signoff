<?php
//Logging Setup for Page
require_once 'php/KLogger.php';
$log = KLogger::instance('log/');
$myname = basename(__FILE__, '.php') . ".php";

// Load the settings from the central config file
require_once 'cas/config.php';
// Load the CAS lib
require_once $phpcas_path . 'CAS.php';
// Initialize phpCAS
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

// For production use set the CA certificate that is the issuer of the cert
// on the CAS server and uncomment the line below
// phpCAS::setCasServerCACert($cas_server_ca_cert_path);

// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
phpCAS::setNoCasServerValidation();
//phpCAS::setCasServerCACert($cas_server_ca_cert_path);
// force CAS authentication
phpCAS::forceAuthentication();

// logout if desired
if (isset($_REQUEST['logout'])) {
    phpCAS::logout();
}


// at this step, the user has been authenticated by the CAS server
// and the user's login name can be read with phpCAS::getUser().
$username = phpCAS::getUser();
require_once('php/connect.php');
$conn = db_connect();
$query = $conn->query("SELECT * FROM signoff_project_admins WHERE username = '$username'");
  if ($query->num_rows > 0) {
    setcookie('SignOffAdminUser', $username, time() + 60*60*24*30, '/');
    $log->logInfo("$myname | $username Authenticated Successfully. User is on Access List. ACCESS GRANTED.");
  } else {
    $log->logWarn("$myname | $username Authenticated Successfuly. User is NOT on Access List. ACCESS DENIED.");
    header("Location: error.php?error=nauth");

  }

//
//  if (!isset($_COOKIE['SignOffAdminUser'])) {
//    header("Location: index.php?error=se");
//  }
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

    <title>Project Sign-Off</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <!--<link href="css/bootstrap-theme.min.css" rel="stylesheet">-->
    <!-- Bootstrap select theme -->
    <link href="css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/bootstrap-table.min.css" rel="stylesheet">
    <link href="css/bootstrap.icon-large.min.css" rel="stylesheet">

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
                <li><a href="#" data-target='#newRequestModal' data-toggle='modal'><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;&nbsp;New Sign-off Request</a></li>
                <li class="divider"></li>
                <li class="dropdown-header">Management</li>
                <li><a href="#" data-target='#manageProjectOwners' data-toggle='modal'><span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>&nbsp;&nbsp;Project Owners</a></li>
                <li><a href="#" data-target='#userManagement' data-toggle='modal'><span class="glyphicon glyphicon-user" aria-hidden="true"></span>&nbsp;&nbsp;User Administration</a></li>
                <li><a href="chartjsExample.html"><span class="glyphicon glyphicon-stats" aria-hidden="true"></span>&nbsp;&nbsp;Analytics Dashboard</a></li>
                <li class="divider"></li>
                <li class="dropdown-header">Account</li>
                <li><a href="?logout"><span class="glyphicon glyphicon-remove-circle" aria-hidden="true"></span>&nbsp;&nbsp;Sign Out</a></li>
              </ul>
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
              </select>
            </div>
            <button type='button' onclick='loadProjectRequests();' class='btn btn-primary'>Find</button>
            <button type='button' onclick='resetProjectRequests();' class='btn btn-warning'>Reset</button>
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
    <div class='modal fade' role='dialog' id='newRequestModal'>
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
                  <option value='project'>Project (SoundNet, LP)</option>
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
                  <label for="projectNumber" class="col-sm-4 control-label">Project ID</label>
                  <div class="col-sm-8">
                    <input style="width: 100px;"type="text" class="form-control" id="projectNumber" placeholder="P0100">
                  </div>
                </div>
                <div class="form-group">
                  <label for="projectNumber" class="col-sm-4 control-label">SoundNet Folder Link</label>
                  <div class="col-sm-8">
                    <input style="width: 300px;"type="text" class="form-control" id="soundNetLink" placeholder="Paste SoundNet Link here">
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
                <input style="width: 250px;"type="text" class="form-control" id="projectName" placeholder="SoundNet Project or Ticket Name">
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
             <button type="button" onclick="refreshView();" class="btn btn-default" data-dismiss="modal">Cancel</button><button style='margin-left: 2px' type="button" class="btn btn-default" onclick="submitNewRequest();">Submit</button>
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
            <h4 class="modal-title" id="myModalLabel">Delete Project Request</h4>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to <strong>delete</strong> this project request?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
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
            <h4 class="modal-title" id="myModalLabel">Forward Project</h4>
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
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button class="btn btn-default" type="submit" id="addRepresentativeBtn" >Add</button>
          </div>
        </div>
      </div>
    </div>
    <!-- End new representative modal -->


    <!-- Begin Edit Request Modal Request -->
    <div class='modal fade' role='dialog' id='editRequestModal'>
      <div style="display: none;" id='editRequestId'></div>
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">Edit Request</h4>
          </div>
          <div class="modal-body">
          <div style="display: none;" class="alert alert-danger" id="editRequestErrorValidate" role="alert"></div>
          <form class='form-horizontal'>
            <div class="form-group">
              <label for="projectOwnerSelect" class="col-sm-4 control-label">Type</label>
              <div class="col-sm-8">
                <select id='typeOfWorkEdit' class="selectpicker" title="Please Select One...">
                  <option value='1' data-hidden="true"></option>
                  <option value='project'>Project (SoundNet, LP)</option>
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
                  <label for="projectNumber" class="col-sm-4 control-label">Project ID</label>
                  <div class="col-sm-8">
                    <input style="width: 100px;"type="text" class="form-control" id="projectNumberEdit" placeholder="P0100">
                  </div>
                </div>
                <div class="form-group">
                  <label for="projectNumber" class="col-sm-4 control-label">SoundNet Folder Link</label>
                  <div class="col-sm-8">
                    <input style="width: 300px;"type="text" class="form-control" id="soundNetLinkEdit" placeholder="Paste SoundNet Link here">
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
                <input style="width: 250px;"type="text" class="form-control" id="projectNameEdit" placeholder="SoundNet Project or Ticket Name">
              </div>
            </div>

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
                    <input type="text" class="form-control" id="authorFullNameEdit" placeholder="username">
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
             <button type="button" onclick="refreshView();" class="btn btn-default" data-dismiss="modal">Cancel</button>
             <button style='margin-left: 2px' type="submit" id="saveRequestEditsBtn" class="btn btn-default">Save</button>
          </div>
        </div>
      </div>
    </div>
    <!-- End New Request Form -->


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