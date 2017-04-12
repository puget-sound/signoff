(function(){

	// the minimum version of jQuery we want
		var v = "",
		u = "";
	if(top.frames["kbox"]) {v = top.frames["kbox"].document.getElementsByClassName('k-main')[0].getElementsByTagName('h1')[0].innerHTML;
u = top.frames["kbox"].document.getElementById('edit-title').innerText}
else {
v = document.getElementsByClassName('k-main')[0].getElementsByTagName('h1')[0].innerHTML;
u = document.getElementById('edit-title').innerText}
var p = "<div id='trayframe' style='position:fixed;z-index:1050;'>\
	<div id='trayframe_veil'></div>\
	<iframe src='https://lxphpdev01.pugetsound.edu/signoff/create.php?ticketNumber="+v+"&projectTitle="+u+"' onload=\"$('#trayframe iframe').slideDown(500);\">Enable iFrames.</iframe>\
	<style type='text/css'>\
		#trayframe_veil { display: none; position: fixed; width: 100%; height: 100%; top: 0; left: 0; background-color: rgba(255,255,255,.25); cursor: pointer; z-index: 900; }\
		#trayframe iframe { display: none; position: fixed; top: 0; left: 0; width: 100%; height:397px; z-index: 999; border:none; margin: 0; }\
	</style>\
</div>";
if(top.frames["kbox"]) p = "<body>" + p + "</body>";

if (window.jQuery === undefined || window.jQuery.fn.jquery < v) {
	var done = false;
	var script = document.createElement("script");
	script.src = "https://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js";
	script.onload = script.onreadystatechange = function(){
		if (!done && (!this.readyState || this.readyState == "loaded" || this.readyState == "complete")) {
			done = true;
			initMyBookmarklet();
		}
	};
	document.getElementsByTagName("head")[0].appendChild(script);
} else {
	initMyBookmarklet();
}


	function initMyBookmarklet() {
		(window.myBookmarklet = function() {
			if ($("#trayframe").length == 0) {
				if(top.frames["kbox"])
					$("html").append(p);
				else {
					$("body").append(p);
				}
					$("#trayframe_veil").fadeIn(750);
				}
			$("#trayframe_veil").click(function(event){
				$("#trayframe_veil").fadeOut(750);
				$("#trayframe iframe").slideUp(500);
				setTimeout("$('#trayframe').remove()", 750);
			});
			function receiveMessage(event){
  				if (event.origin !== "https://lxphpdev01.pugetsound.edu")
    				return;
				$("#trayframe_veil").fadeOut(750);
				$("#trayframe iframe").slideUp(500);
				setTimeout("$('#trayframe').remove()", 750);
			}
			window.addEventListener("message", receiveMessage, false);
		})();
	}

})();
