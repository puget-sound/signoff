
<?php

$theFilePath = $_GET["filepath"];// Get the filepath from the html page

if(file_exists($theFilePath)){ //set the t
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.basename($theFilePath).'"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($theFilePath));
	ob_clean();																	//discard the contents of the output buffer (make room for file)
  flush();																		//push current output all the way to the browser
	readfile($theFilePath);											//Read file and write to the output buffer.
	exit;
}else {
	echo "Error! File found in database but not on the server!";
}


?>
