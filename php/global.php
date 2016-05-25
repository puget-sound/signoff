<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
# Should log to the same directory as this file
require dirname(dirname(__FILE__)) . '/logs/KLogger.php';
$log   = KLogger::instance((dirname(dirname(__FILE__)) . '/logs'), KLogger::DEBUG);

?>