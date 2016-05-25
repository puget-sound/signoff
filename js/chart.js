var myProjectsPerMonthChart;
var ctx = document.getElementById("myChart").getContext("2d");

var myTypeOfRequestsChart;
var typeOfWorkChart = document.getElementById("typeOfWorkChart").getContext("2d");

var myProjectsPerAuthorChart;
var projectsPerAuthorChart = document.getElementById("projectsPerAuthorChart").getContext("2d");

var myProjectsPerOfficeChart;
var projectsPerRequestorChart = document.getElementById("projectsPerRequestorChart").getContext("2d");

function getYearsForProjects() {

    $.getJSON("php/calculateDashboardAnalytics.php", {findYears: true}, function(data) {
        var years = data[0];

        $("#numProjectRequestsYearSelect").html();
        var html = "<option data-hidden='true' value='1'></option>"
        var htmlValidAfter2015 = "<option data-hidden='true' value='1'></option>";
        for(var i=0; i<years.length; i++){
          //get the html select and dynamically put in the years to select
          if(years[i] >0){
            html+= '<option value="' + years[i] + '">' + years[i] + '</option>';
          }
          if(years[i] >=2015){
            htmlValidAfter2015 += '<option value="' + years[i] + '">' + years[i] + '</option>';
          }
        }

        $("#numProjectRequestsYearSelect").append(html);
        $("#numProjectRequestsYearSelect").selectpicker("refresh");

        $("#numProjectRequestsYearSelect2").append(html);
        $("#numProjectRequestsYearSelect2").selectpicker("refresh");

        $("#typeOfRequestsYear").append(html);
        $("#typeOfRequestsYear").selectpicker("refresh");

        $("#numberOfProjectsAuthoredYear").append(htmlValidAfter2015);
        $("#numberOfProjectsAuthoredYear").selectpicker("refresh");

        $("#numProjPerOfficeYear").append(htmlValidAfter2015);
        $("#numProjPerOfficeYear").selectpicker("refresh");
    });
}

function loadNumberOfProjectRequests(year1, year2){

  $.getJSON("php/calculateDashboardAnalytics.php", {
      loadNumberOfProjectRequests: true, selectYear1: year1 , selectYear2: year2
  }, function(data) {

      //Number of Project Requests
      var projectsPerMonth = {
          labels: ["Jan", "Feb", "March", "April", "May", "June", "July", "Aug", "Sept", "Oct", "Nov", "Dec"],
          datasets: [{
              label: "My First dataset",
              fillColor: "rgba(220,220,220,0.2)",
              strokeColor: "rgba(220,220,220,1)",
              pointColor: "rgba(220,220,220,1)",
              pointStrokeColor: "#fff",
              pointHighlightFill: "#fff",
              pointHighlightStroke: "rgba(220,220,220,1)",
              data: data[0]
          }, {
              label: "My Second dataset",
              fillColor: "rgba(151,187,205,0.2)",
              strokeColor: "rgba(151,187,205,1)",
              pointColor: "rgba(151,187,205,1)",
              pointStrokeColor: "#fff",
              pointHighlightFill: "#fff",
              pointHighlightStroke: "rgba(151,187,205,1)",
              data: data[1]
          }]
      };
      // Get the context of the canvas element we want to select
      myProjectsPerMonthChart = new Chart(ctx).Bar(projectsPerMonth);
    });
}

function loadTypeOfRequests(requestsYear){
  $.getJSON("php/calculateDashboardAnalytics.php", {
      typeOfRequestsYear:requestsYear
  }, function(data) {
    console.log(data);
    //Type of Requests 2015
    var typeOfWorkThisYear = [{
        value: data[0],
        color: "#F7464A",
        highlight: "#FF5A5E",
        label: "Projects"
    }, {
        value: data[1],
        color: "#46BFBD",
        highlight: "#5AD3D1",
        label: "Tickets"
    }, {
        value: data[2],
        color: "#FDB45C",
        highlight: "#FFC870",
        label: "Requirements"
    }]

    // Get the context of the canvas element we want to select
    myTypeOfRequestsChart = new Chart(typeOfWorkChart).Doughnut(typeOfWorkThisYear);

  });
}

function loadProjectsPerAuthor(authoredYear){

  $.getJSON("php/calculateDashboardAnalytics.php", {
      projectsPerAuthorYear:authoredYear
  }, function(data) {

    //Number of Projects Authored
    var projectsPerAuthor = {
        labels: data[0],
        datasets: [{
            label: "My Second dataset",
            fillColor: "rgba(151,187,205,0.2)",
            strokeColor: "rgba(151,187,205,1)",
            pointColor: "rgba(151,187,205,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(151,187,205,1)",
            data: data[1]
        }]
    };
    // Get the context of the canvas element we want to select
    myProjectsPerAuthorChart = new Chart(projectsPerAuthorChart).Bar(projectsPerAuthor);
  });
}

function loadProjectsPerOffice(requestedYear){

  $.getJSON("php/calculateDashboardAnalytics.php", {
      projectsPerOfficeYear:requestedYear
  }, function(data) {

    //Number of Projects Requested
    var projectsPerRequestor = {
        labels: data[0],
        datasets: [{
            label: "My Second dataset",
            fillColor: "rgba(151,187,205,0.2)",
            strokeColor: "rgba(151,187,205,1)",
            pointColor: "rgba(151,187,205,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(151,187,205,1)",
            data: data[1]
        }]
    };
    // Get the context of the canvas element we want to select
    myProjectsPerOfficeChart = new Chart(projectsPerRequestorChart).Bar(projectsPerRequestor);
  });
}

$(document).ready(function() {

  //When the user selects a year, destroy the old chart and draw the new one
  $( "#numProjectRequestsYearSelect" ).on("change", function() {

    if($("#numProjectRequestsYearSelect").val() != $("#numProjectRequestsYearSelect2").val()){
      myProjectsPerMonthChart.destroy();
      loadNumberOfProjectRequests($("#numProjectRequestsYearSelect").val(), $("#numProjectRequestsYearSelect2").val());
      $("#year1-legend").html("<li id='year1-legend'> <span style='background-color:rgba(220,220,220,1)'></span>" + $("#numProjectRequestsYearSelect").val()  +"</li></select>");
    }
  });

  $("#numProjectRequestsYearSelect2").on("change", function(){

    if($("#numProjectRequestsYearSelect").val() != $("#numProjectRequestsYearSelect2").val()){
      myProjectsPerMonthChart.destroy();
      loadNumberOfProjectRequests($("#numProjectRequestsYearSelect").val(), $("#numProjectRequestsYearSelect2").val());
      $("#year2-legend").html("<li id='year2-legend'> <span style='background-color:rgba(151,187,205,1)'></span>" + $("#numProjectRequestsYearSelect2").val()  +"</li></select>");
    }
  });

  $("#typeOfRequestsYear").on("change", function(){
    myTypeOfRequestsChart.destroy();
    loadTypeOfRequests($("#typeOfRequestsYear").val());
  });

  $("#numberOfProjectsAuthoredYear").on("change", function(){
    myProjectsPerAuthorChart.destroy();
    loadProjectsPerAuthor($("#numberOfProjectsAuthoredYear").val());
  });

  $("#numProjPerOfficeYear").on("change", function(){
    myProjectsPerOfficeChart.destroy();
    loadProjectsPerOffice($("#numProjPerOfficeYear").val());
  })



  getYearsForProjects();
  loadNumberOfProjectRequests(2014, 2015);
  loadTypeOfRequests(2015);
  loadProjectsPerAuthor(2015);
  loadProjectsPerOffice(2015);
});
