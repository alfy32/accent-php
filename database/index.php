<?php
// Create the mysql backup file
// edit this section
$dbuser = "alan_accent";
$dbpass = "IdrB*@&T[M}w";
$dbname = "alan_accent";
$sendto = "Webmaster <alfy32@gmail.com>";
$sendfrom = "Automated Backup <backup@accent.westhostsite.com>";
$sendsubject = "Daily Mysql Backup";
$bodyofemail = "Here is the daily backup.";
// don't need to edit below this section

$backupfile = $dbname . date("\_Y-m-d\_H:i:s") . '.sql'; 
exec(" mysqldump  -u'$dbuser' -p'$dbpass' $dbname > $backupfile");

// Mail the file

    include('Mail.php');
    include('Mail/mime.php');

	$message = new Mail_mime();
	$text = "$bodyofemail";
	$message->setTXTBody($text);
	$message->AddAttachment($backupfile);
    	$body = $message->get();
        $extraheaders = array("From"=>"$sendfrom", "Subject"=>"$sendsubject");
        $headers = $message->headers($extraheaders);
    $mail = Mail::factory("mail");
    $mail->send("$sendto", $headers, $body);

// Delete the file from your server
unlink($backupfile);
?>