function clearRequestForm() {
	$("#errorValidate").hide();
	$("#ticketNumberGroup").hide();
	$("#soundNetGroup").hide();
	$("#typeOfWork").val(1);
	$("#typeOfWork").selectpicker("refresh");
	$("#ticketNumber").val("");
	$("#projectNumber").val("");
	$("#soundNetLink").val("");
	$("#lpProjectLink").val("");
	$("#sprintNumber").val("");
	$("#projectName").val("");
	$("#appDesignerProjects").val("");
	$("#plsqlObjects").val("");
	$("#otherObjects").val("");
	$(".request-representative").val("");
	$("#projectOwnerSelect").val(1);
	$("#projectOwnerSelect").selectpicker("refresh");
	$("#summaryWorkCompleted").val("");
	$("#testingTypeSelect").val(1);
	$("#testingTypeSelect").selectpicker("refresh");
	var requestLength = $('.request-representative-div').length;
	for (var i = requestLength; i > 1;  i--) {
			$('.request-representative-div:last').remove();
	}
}

function refreshView() {
	//hide search and filter
	$("#soundNetGroup").hide();
	$("#ticketNumberGroup").hide();
	$("#searchAndFilterRow").show();
	//hide requests
	$("#requestsRow").show();
	//show sign-off reqeust
	loadProjectRequests();
}

function loadProjectOwners() {
	$.getJSON("php/loadProjectOwners.php", function(data) {
		$('#projectOwnerSelect').html("");
		$('#projectOwnerSelectEdit').html("");
		var html = "<option data-hidden='true' value='1'></option>";
		var len = data.length;
    	for (var i = 0; i< len; i++) {
        	html += '<option value="' + data[i].ownerName + '">' + data[i].ownerName + '</option>';
    	}
    	$('#projectOwnerSelect').append(html);
    	$('#projectOwnerSelect').selectpicker("refresh");
    	$('#projectOwnerSelect').selectpicker("deselectAll");
    	$('#projectOwnerSelect').selectpicker("refresh");

			$('#projectOwnerSelectEdit').append(html);
    	$('#projectOwnerSelectEdit').selectpicker("refresh");
    	$('#projectOwnerSelectEdit').selectpicker("deselectAll");
    	$('#projectOwnerSelectEdit').selectpicker("refresh");

	});
}

function loadUsers() {
	$.getJSON("php/loadUsers.php", function(data) {
		$("#userManagementTable").html("");
		var html = "";
		var len = data.length;
		for (var i = 0; i < len; i++) {
			html += "<tr>";
			html += "<td>" + data[i].username + "</td>";
			html += "<td>" + data[i].fullName + "</td>";
			html += "<td><button type='button' class='btn btn-default btn-xs' aria-label='Delete' onclick='removeUser(" + data[i].userId + ");'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button></td>"
			html += "</tr>";
		}
		$('#userManagementTable').append(html);
	});
}

function viewProjectOwners() {
	$.getJSON("php/loadProjectOwners.php", function(data) {
		$('#projectOwnerTable').html("");
		var html = "";
		var len = data.length;
    	for (var i = 0; i< len; i++) {
        	html += '<tr><td>' + data[i].ownerName + '</td>';
        	html += "<td><button type='button' class='btn btn-default btn-xs' aria-label='Delete' onclick='removeProjectOwner(" + data[i].ownerId + ");''><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button></td></tr>";
    	}
    	$('#projectOwnerTable').append(html);
	});
}

function removeUser(userId) {
	$.getJSON("php/deleteUser.php", {userId: userId}, function(data) {
		$("#userManageValidate").hide();
		if (data.hasOwnProperty("success")) {
			$("#userManageSuccess").html("Succesfully <strong>deleted</strong> user.");
			$("#userManageSuccess").show().delay(2000).fadeOut();
			loadUsers();
		} else {
			var sqlerror = data.error;
			$("#userManageValidate").html("<strong>SQL Error: </strong>" + sqlerror);
			$("#userManageValidate").show();
		}
	});
}

function removeProjectOwner(deleteId) {
	$.getJSON("php/deleteProjectOwner.php", {deleteId: deleteId}, function(data) {
		$("#projectOwnerValidate").hide();
		if (data.hasOwnProperty("success")) {
			$("#addOwnerSuccess").html("Succesfully <strong>deleted</strong> Project Owner.");
			$("#addOwnerSuccess").show().delay(2000).fadeOut();
			viewProjectOwners();
		} else {
			var sqlerror = data.error;
			$("#projectOwnerValidate").html("<strong>SQL Error: </strong>" + sqlerror);
			$("#projectOwnerValidate").show();
		}
	});
}

function deleteRequest(requestId) {
	$.getJSON("php/deleteProjectRequest.php", {requestId: requestId}, function(data) {
		if (data.hasOwnProperty("success")) {
			refreshView();
			$('#deleteRequestModal').modal('hide');
			$("#mainSuccessMessage").html("Successfully deleted request.");
			$("#mainSuccessMessage").show().delay(3000).fadeOut();

		} else {
			var sqlerror = data.error;
			$('#deleteRequestModal').modal('hide');
			$("#mainSuccessMessage").html("<strong>SQL Error: </strong>" + sqlerror);
			$("#mainSuccessMessage").show();

		}
	});
}

function addUserFromField() {
	if ($("#addUser").val() != "") {
		$("#userManageValidate").hide();
		$.getJSON("php/addUser.php", {newUser: $('#addUser').val()}, function(data) {
			if (data.hasOwnProperty("success")) {
				$("#userManageSuccess").html("Successfully added <strong>new</strong> user.");
				$("#userManageSuccess").show().delay(2000).fadeOut();
				$("#addUser").val("");
				loadUsers();
			} else {
				var sqlerror = data.error;
				$('#userManageValidate').html("<strong>SQL Error: </strong>" + sqlerror);
				$("#userManageValidate").show();
			}
		})

	} else {
		$("#userManageValidate").html("You have not entered a new <strong>user</strong> to add.");
		$("#userManageValidate").show();
	}
}

function addRepresentativeFromField(requestId){
		if ($("#addRepresentative").val() != "") {
			$("#addRepresentativeValidate").hide();
			$.getJSON("php/addRepresentative.php", {newRepresentative: $("#addRepresentative").val(), requestId: requestId}, function(data) {
				if (data.hasOwnProperty("success")) {//close the view and display the success on the screen
					refreshView();
					$("#addRepresentativeModal").modal('hide');
					$("#mainSuccessMessage").html("Successfully duplicated request with <strong>new</strong> representative.");
					$("#mainSuccessMessage").show().delay(3000).fadeOut();
					$("#addRepresentative").val("");
				} else {													//display the error on the modal view
					var addRepErr = data.error;
					$("#addRepresentativeValidate").html("<strong>Add Representative Error: </strong>" + addRepErr);
					$("#addRepresentativeValidate").show();
				 }
			});
		} else {
			$("#addRepresentativeValidate").html("You have not entered a new <strong>representative</strong> to add.");
			$("#addRepresentativeValidate").show();
		}
}

function saveRequestEdits(requestId){
	//get all of the information we need and pass it to a php function to update the values
	if (validateRequestEdits()) {
	$.getJSON("php/saveRequestEdits.php", {requestId: requestId,
		typeOfWork : $("#typeOfWorkEdit").val(),
		ticketNumber : $("#ticketNumberEdit").val(),
		projectId : $("#projectNumberEdit").val(),
		soundNetLink : $("#soundNetLinkEdit").val(),
		liquidPlannerLink : $("#lpProjectLinkEdit").val(),
		sprint : $("#sprintNumberEdit").val(),
		projectName : $("#projectNameEdit").val(),
		appDesignerProjs : $("#appDesignerProjsEdit").val(),
		plsqlObjects : $("#plsqlObjectsEdit").val(),
		otherObjects : $("#otherObjectsEdit").val(),
		projectOwner : $("#projectOwnerSelectEdit").val(),
		sumWorkCompleted : $("#summaryWorkCompletedEdit").val(),
		testingType : $("#testingTypeSelectEdit").val(),
		author : $("#authorEdit").val(),
		authorFullName : $("#authorFullNameEdit").val(),
		}, function(data) {

			if (data.hasOwnProperty("success")) {//close the view and display the success on the screen
				refreshView();
				$("#editRequestModal").modal('hide');
				$("#mainSuccessMessage").html("Successfully updated request.");
				$("#mainSuccessMessage").show().delay(3000).fadeOut();
			} else {													//display the error on the modal view
				var addRepErr = data.error;
				$("#editRequestErrorValidate").html("<strong>Edit Request Error: </strong>" + addRepErr);
				$("#editRequestErrorValidate").show();
				$('#editRequestModal').animate({ scrollTop: 0 }, 'slow');
			 }
		 });
	}
}

function validateRequestEdits() {

	if ($("#typeOfWorkEdit").val() == "project" && $("#projectNumberEdit").val() == "") {
		$("#editRequestErrorValidate").html("You must specify a <strong>Project ID</strong>. This field is required.");
		$('#editRequestModal').animate({ scrollTop: 0 }, 'slow');
		$("#editRequestErrorValidate").show();
		return false;
	}
	if ($("#typeOfWork").val() == "req" && $("#projectNumber").val() == "") {
		$("#errorValidate").html("You must specify an associated <strong>Project ID</strong> with this request. This field is required.");
	}
	if ($("#typeOfWorkEdit").val() == "ticket" && $("#ticketNumberEdit").val() == "") {
		$("#editRequestErrorValidate").html("You must specify a <strong>KACE Ticket number</strong>. This field is required.");
		$('#editRequestModal').animate({ scrollTop: 0 }, 'slow');
		$("#editRequestErrorValidate").show();
		return false;
	}
	if ($("#typeOfWorkEdit").val() == "") {
		$("#editRequestErrorValidate").html("You must specify what kind of <strong>Work</strong> this is. This step is required.");
		$('#editRequestModal').animate({ scrollTop: 0 }, 'slow');
		$("#editRequestErrorValidate").show();
		return false;
	} else if ($("#projectNameEdit").val() == "") {
		$("#editRequestErrorValidate").html("You are missing a <strong>Project Name</strong>. This field is required.");
		$('#editRequestModal').animate({ scrollTop: 0 }, 'slow');
		$("#editRequestErrorValidate").show();
		return false;
	} else if ($("#projectOwnerSelectEdit").val() == "") {
		$("#editRequestErrorValidate").html("You need to select a <strong>Project Owner</strong>. This selection is required.");
		$('#editRequestModal').animate({ scrollTop: 0 }, 'slow');
		$("#editRequestErrorValidate").show();
		return false;
	} else if ($("#testingTypeSelectEdit").val() == "" && $("#typeOfWork".val() != "req")) {
		$("#editRequestErrorValidate").html("You need to select a <strong>Proof-of-Testing Type</strong>. This selection is required.");
		$('#editRequestModal').animate({ scrollTop: 0 }, 'slow');
		$("#editRequestErrorValidate").show();
		return false;
	}  else if ($("#authorEdit").val() == "") {
		$("#editRequestErrorValidate").html("You need to provide an <strong>author</strong>. This step is required.");
		$('#editRequestModal').animate({ scrollTop: 0 }, 'slow');
		$("#editRequestErrorValidate").show();
		return false;
	} else {
		$("#editRequestErrorValidate").hide();
		return true;
	}
}

function getRequestWithId(requestId){
	$.getJSON("php/getProjectRowWithId.php", {requestId:requestId}, function(data){

$("#typeOfWorkEdit").val(''); //get all of the information from the server to pass to the modal view
$("#ticketNumberEdit").val(data[0].ticketNumber);
$("#projectNumberEdit").val(data[0].projectId);
$("#soundNetLinkEdit").val(data[0].soundNetLink);
$("#lpProjectLinkEdit").val(data[0].liquidPlannerLink);
$("#sprintNumberEdit").val(data[0].sprint);
$("#projectNameEdit").val(data[0].projectName);
$("#appDesignerProjsEdit").val(data[0].appDesignerProjs);
$("#plsqlObjectsEdit").val(data[0].plsqlObjects);
$("#otherObjectsEdit").val(data[0].otherObjects);
$("#projectOwnerSelectEdit").val(data[0].projectOwner);
$("#summaryWorkCompletedEdit").val(data[0].sumWorkCompleted);
$("#testingTypeEdit").val(data[0].testingType);
$("#requestToEdit").val(data[0].requestTo);
$("#requesterFullNameEdit").val(data[0].reqFullName);
$("#authorEdit").val(data[0].author);
$("#authorFullNameEdit").val(data[0].authorFullName);
$("#requestDateEdit").val(data[0].requestDate);
$("#editRequestModal").modal('toggle');
$("#editRequestId").val(requestId);

});



}

function addProjectOwnerFromField() {
	if ($("#addProjectOwner").val() != "") {
		$("#projectOwnerValidate").hide();
		$.getJSON("php/addProjectOwner.php", {newOwner: $("#addProjectOwner").val()}, function(data) {
			if (data.hasOwnProperty("success")) {
				$("#addOwnerSuccess").html("Successfully added <strong>new</strong> Project Owner.");
				$("#addOwnerSuccess").show().delay(2000).fadeOut();
				$("#addProjectOwner").val("");
				viewProjectOwners();
				loadProjectOwners();
			} else {
				var sqlerror = data.error;
				$("#projectOwnerValidate").html("<strong>SQL Error: </strong>" + sqlerror);
				$("#projectOwnerValidate").show();
			}
		})
	} else {
		$("#projectOwnerValidate").html("You have not entered a new <strong>Project Owner</strong> to add.");
		$("#projectOwnerValidate").show();
	}
}


function validateRequest() {
	var users = $("input[id='requestUsers[]']").map(function(){return $(this).val();}).get();
	if ($("#typeOfWork").val() == "project" && $("#projectNumber").val() == "") {
		$("#errorValidate").html("You must specify a <strong>Project ID</strong>. This field is required.");
		window.scrollTo(0, 0);
		$("#errorValidate").show();
		return false;
	}
	if ($("#typeOfWork").val() == "req" && $("#projectNumber").val() == "") {
		$("#errorValidate").html("You must specify an associated <strong>Project ID</strong> with this request. This field is required.");
	}
	if ($("#typeOfWork").val() == "ticket" && $("#ticketNumber").val() == "") {
		$("#errorValidate").html("You must specify a <strong>KACE Ticket number</strong>. This field is required.");
		window.scrollTo(0, 0);
		$("#errorValidate").show();
		return false;
	}

	if ($("#typeOfWork").val() == "") {
		$("#errorValidate").html("You must specify what kind of <strong>Work</strong> this is. This step is required.");
		window.scrollTo(0, 0);
		$("#errorValidate").show();
		return false;
	} else if ($("#projectName").val() == "") {
		$("#errorValidate").html("You are missing a <strong>Project Name</strong>. This field is required.");
		window.scrollTo(0, 0);
		$("#errorValidate").show();
		return false;
	} else if ($("#projectOwnerSelect").val() == "") {
		$("#errorValidate").html("You need to select a <strong>Project Owner</strong>. This selection is required.");
		window.scrollTo(0, 0);
		$("#errorValidate").show();
		return false;
	} else if ($("#testingTypeSelect").val() == "" && $("#typeOfWork".val() != "req")) {
		$("#errorValidate").html("You need to select a <strong>Proof-of-Testing Type</strong>. This selection is required.");
		window.scrollTo(0, 0);
		$("#errorValidate").show();
		return false;
	}  else if (users.join() == "") {
		$("#errorValidate").html("You need to request sign-off from at least <strong>one user</strong>. This step is required.");
		window.scrollTo(0, 0);
		$("#errorValidate").show();
		return false;
	} else {
		$("#errorValidate").hide();
		return true;
	}
}

function submitNewRequest() {
	if (validateRequest()) {
		var users = $("input[id='requestUsers[]']").map(function(){return $(this).val();}).get();
		$.getJSON("php/submitNewRequest.php", {
			typeOfWork: $("#typeOfWork").val(),
			ticketNumber: $("#ticketNumber").val(),
			projectId: $("#projectNumber").val(),
			soundNetLink: $("#soundNetLink").val(),
			lpProjectLink: $("#lpProjectLink").val(),
			sprint: $("#sprintNumber").val(),
			projectName: $("#projectName").val(),
			appDesignerProjects: $("#appDesignerProjects").val(),
			plsqlObjects: $("#plsqlObjects").val(),
			otherObjects: $("#otherObjects").val(),
			projectOwner: $("#projectOwnerSelect").val(),
			summaryWorkCompleted: $("#summaryWorkCompleted").val(),
			testingType: $("#testingTypeSelect").val(),
			requestUsers: users.join()
		}, function(data) {
			if (data.hasOwnProperty("error")) {
				$("#errorValidate").show();
				$("#errorValidate").html("<strong>Active Directory: </strong>" + data.error);
			} else {
				$('#newRequestModal').modal('hide')
				refreshView();
				$("#mainSuccessMessage").html("Successfully processed new request(s)!");
				$("#mainSuccessMessage").show().delay(3000).fadeOut();
			}
		});
	}
}


function resetProjectRequests() {
	$("#filterRType").val('all');
	$("#filterRType").selectpicker("refresh");
	$("#filterDateRange").val('30');
	$("#filterDateRange").selectpicker("refresh");
	$("#filterRec").val('showrec');
	$("#filterRec").selectpicker("refresh");
	loadProjectRequests();
}


function loadProjectRequests() {
	$("#projectRequestTable").hide();
	$("#tableLoading").show();
	$.ajaxSetup({ cache: false });
	$.getJSON("php/loadProjectRequests.php", {
		filterRType: $('#filterRType').val(),
		filterDateRange: $('#filterDateRange').val(),
		filterRec: $("#filterRec").val(),
	}, function(data) {
		$('#projectRequestTableBody').html("");
		var html = "";
		var len = data.length;
		for (var i = 0; i< len; i++) {
			var t = data[i].requestDate.split(/[- :]/);
			var d = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
			html += "<tr><td class='hidden-xs'>" + (d.getMonth() + 1)  + "/" + d.getDate() + "/" + d.getFullYear() + "</td>";
			if (data[i].typeOfWork == "project") {
				html += "<td class='hidden-xs hidden-sm hidden-md'>" + data[i].projectId + "</td>";
			} else if (data[i].typeOfWork == "ticket") {
				html += "<td class='hidden-xs hidden-sm hidden-md'>TICK:" + data[i].ticketNumber + "</td>";
			} else if (data[i].typeOfWork == "req") {
				html += "<td class='hidden-xs hidden-sm hidden-md'>Doc: " + data[i].projectId + "</td>";
			} else {
				html += "<td class='hidden-xs hidden-sm hidden-md'>ERROR</td>";
			}
			if (data[i].projectName.length > 56) {
				cutStr = "";
				cutStr = data[i].projectName;
				cutStr = cutStr.substring(0,56) + "...";
				html += "<td>" + cutStr + "</td>";
			} else {
	    		html += "<td>" + data[i].projectName + "</td>";
	    	}
	    	html += "<td class='hidden'>sprint:" + data[i].sprint + "</td>";
	    	html += "<td class='hidden'>" + data[i].appDesignerProjs + "</td>";
	    	html += "<td class='hidden'>" + data[i].plsqlObjects + "</td>";
	    	html += "<td class='hidden'>" + data[i].otherObjects + "</td>";
	    	html += "<td class='hidden-xs hidden-sm hidden-md'>" + data[i].authorFullName + "</td>";
	    	html += "<td class='hidden-xs'>" + data[i].reqFullName + "</td>";
	    	if (data[i].status == "Pending") {
	    		html += "<td><span class='label label-primary hidden-xs hidden-md hidden-sm'>" + data[i].status + "</span><span class='label label-primary hidden-lg'>P</span></td>";
	    	}
	    	if (data[i].status == "Received") {
	    		html += "<td><span class='label label-success hidden-xs hidden-md hidden-sm'>" + data[i].status + "</span><span class='label label-success hidden-lg'>R</span></td>";
	    	}
	    	if (data[i].status == "Declined") {
	    		html += "<td><span class='label label-danger hidden-xs hidden-md hidden-sm'>" + data[i].status + "</span><span class='label label-danger hidden-lg'>D</span></td>";
	    	}
	    	html += "<td>";
	    	html += "<div class='btn-group'><a role='button' href='view.php?requestId=" + data[i].requestId + "' target='_blank' class='btn btn-xs btn-default'>View</button></a>";
	    	html += "<button type='button' class='btn btn-xs btn-default dropdown-toggle' data-toggle='dropdown' aria-expanded='false'>";
	    	html += "<span class='caret'></span><span class='sr-only'>Toggle Dropdown</span></button>";
	    	html += "<ul class='dropdown-menu' role='menu'>";
	    	html += "<li><a href='#' data-toggle='modal' data-target='#copyLinkModal' data-requestId=" + data[i].requestId + "><span class='glyphicon glyphicon-link'></span>&nbsp;&nbsp;Get Link</a></li>";
				html += "<li><a href='#' data-toggle='modal' data-target='#addRepresentativeModal' data-requestId=" + data[i].requestId + "><span class='glyphicon glyphicon-send'></span>&nbsp;&nbsp;Forward</a></li>";

				if (data[i].status == "Pending") {
	    		html += "<li><a href='#' data-target='#editRequestModal' id='editRequestModalButton' data-requestId=" + data[i].requestId + " onclick=getRequestWithId("+data[i].requestId+");><span class='glyphicon glyphicon-pencil'></span>&nbsp;&nbsp;Edit</a></li>";
	    	}
	    	//html += "<li><a href='#'><span class='glyphicon glyphicon-send'></span>&nbsp;&nbsp;Forward</a></li>";
	    	if (data[i].status == "Pending") {
	    	html += "<li><a href='#' data-toggle='modal' data-requestid=" + data[i].requestId + " data-target='#deleteRequestModal'><span class='glyphicon glyphicon-trash'></span>&nbsp;&nbsp;Delete</a></li>";
	    	}
	    	html += "</ul></div>";
	    	//html += "<a target='_blank' href='view.php?requestId="+ data[i].requestId +"' type='button' class='btn btn-default btn-xs' aria-label='View'>";
	    	//html += "<span class='glyphicon glyphicon-search' aria-hidden='true'></span>";
	    	//html += "</a>";
	    	//html += "<button class='btn btn-default btn-xs' data-toggle='modal' data-target='#copyLinkModal' data-requestId=" + data[i].requestId + "><span class='glyphicon glyphicon-export' aria-hidden='true'></span></button>"
	    	//if (data[i].status == "Pending") {
	    		//html += "<button type='button' class='btn btn-default btn-xs' aria-label='Delete' data-toggle='modal' data-requestid=" + data[i].requestId + " data-target='#deleteRequestModal'>";
	    		//html += "<span class='glyphicon glyphicon-trash' aria-hidden='true'></span>";
	    		//html += "</button>";
	    	//}
	    	html += "</td></tr>";
		}
		$('#projectRequestTableBody').append(html);
		$("#tableLoading").hide();
		$("#projectRequestTable").show();
		$.ajaxSetup({ cache: true });
	});
}



$(document).ready(function() {
  	refreshView();
  	$("#hidedetailfields").hide();
    $("#testinggroup").hide();
    $("#sumWorkGroup").hide();
  	//show fields related to the type of work that is being changed.
  	$('#typeOfWork').on('change', function(){
  		$("#ticketNumberGroup").hide();
		$("#soundNetGroup").hide();
    	var selected = $(this).find("option:selected").val();
    	if (selected == "project" || selected == "req") {
    		$("#ticketNumber").val("");
    		$( "#soundNetGroup" ).slideDown(500);
    		$("#hidedetailfields").show();
    		$("#testinggroup").show();
    		$("#sumWorkGroup").show();
    	}
    	if (selected == "req") {
    		$("#hidedetailfields").hide();
    		$("#testinggroup").hide();
    		$("#sumWorkGroup").hide();
    	}
    	if (selected == "ticket") {
    		$("#projectNumber").val("");
			$("#soundNetLink").val("");
			$("#lpProjectLink").val("");
    		$( "#ticketNumberGroup" ).slideDown(500);
    		$("#hidedetailfields").show();
    		$("#testinggroup").show();
    		$("#sumWorkGroup").show();
    	}
  	});


	(function ($) {
        $('#filter').keyup(function () {
            var rex = new RegExp($(this).val(), 'i');
            $('.searchable tr').hide();
            $('.searchable tr').filter(function () {
                return rex.test($(this).text());
            }).show();
        })

    }(jQuery));
    (function ($) {
        $('#filterProjectOwners').keyup(function () {
            var rex = new RegExp($(this).val(), 'i');
            $('.ownersearchable tr').hide();
            $('.ownersearchable tr').filter(function () {
                return rex.test($(this).text());
            }).show();
        })

    }(jQuery));

    $('#manageProjectOwners').on('shown.bs.modal', function () {
  		viewProjectOwners();
  		$("#filterProjectOwners").focus();
	})

	$('#userManagement').on('shown.bs.modal', function () {
  		loadUsers();
	})


	//hides search & filter, shows requests, initialized new sign-off reqeust form
	$('#newRequestModal').on('show.bs.modal', function (event) {
		//initialize project owners list
		clearRequestForm();
		loadProjectOwners();
		$("#ticketNumberGroup").hide();
		$("#soundNetGroup").hide();
		//open modal
	})


	$('#copyLinkModal').on('shown.bs.modal', function (event) {
		var request = "";
		$("#copyPasteModalLink").html("");
		var button = $(event.relatedTarget); // Button that triggered the modal
  	request = button.data('requestid');
		$("#copyPasteModalLink").val("http://signoff.pugetsound.edu/respond.php?requestId=" + request).select();
	})

	$('#addRepresentativeModal').on('shown.bs.modal', function (event) {
		var request = "";
		$("#addRepresentative").val("");
		var button = $(event.relatedTarget); // Button that triggered the modal
		request = button.data('requestid');
		$("#addRepresentativeBtn").attr("onclick", "addRepresentativeFromField(" + request + ");return false;");
		$('#addRepresentative').keypress(function(e){
	      if(e.keyCode==13){
	      	addRepresentativeFromField(request);
					return false;
				}
	    });
	})

	$('#editRequestModal').on('shown.bs.modal', function (event) {

		loadProjectOwners();
		$("#editRequestErrorValidate").val("");
		$("#editRequestErrorValidate").hide();
		var request = $('#editRequestId').val();
		$("#saveRequestEditsBtn").attr("onclick", "saveRequestEdits(" + request + ");return false;");

	})

	$('#deleteRequestModal').on('shown.bs.modal', function (event) {
		var button = $(event.relatedTarget); // Button that triggered the modal
  		var request = button.data('requestid');
		$("#deleteRequestBtn").attr("onclick", "deleteRequest(" + request + ");");
	})


	//add/remove fields to the new request form
    var max_fields      = 10; //maximum input boxes allowed
    var wrapper         = $("#input_fields_wrap"); //Fields wrapper
    var add_button      = $("#addUsers"); //Add button ID

    var x = 1; //initlal text box count
    $(add_button).click(function(e){ //on add input button click
        e.preventDefault();
        if(x < max_fields){ //max input box allowed
            x++; //text box increment
            $(wrapper).append("<div style='width: 325px; margin-top: 2px;' class='input-group request-representative-div'><span class='input-group-addon'><a href='#'' id='remove_field'>Remove</a></span><input type='text' class='form-control request-representative' id='requestUsers[]' placeholder='username' aria-describedby='email-addon'><span class='input-group-addon' id='email-addon'>@pugetsound.edu</span></div>"); //add input box
						$('.request-representative:last').focus();
        }
    });

    $(wrapper).on("click","#remove_field", function(e){ //user click on remove text
    	if (x != 1) {
        	e.preventDefault();
    		$(this).closest('div').remove();
    		x--;
    	}
    })
});
