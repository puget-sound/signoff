<?php

$message = "This is a test message";
$to = "vnilakantan@pugetsound.edu";
$subject = "testing tsprojects";
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'From: TS Project Management Office <tsprojects@pugetsound.edu>' . "\r\n" .
'BCC: vnilakantan@pugetsound.edu' . "\r\n" .
'Reply-To: tsprojects@pugetsound.edu' . "\r\n" .
'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);

?>