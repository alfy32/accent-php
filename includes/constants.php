<?php

	define(DB_SERVER, 'localhost');
	define(DB_USER, 'alan_accent');
	define(DB_PASSWORD, 'IdrB*@&T[M}w');
	define(DB_NAME, 'alan_accent');
	
	// Height for the wrap around client names
	define(MAX_HEIGHT, 3);
	
	define(OPEN_TIME, 	'08:00:00');
	define(CLOSED_TIME, '18:00:00');
	
	$dbuser = "alan_accent";
	$dbpass = "IdrB*@&T[M}w";
	$dbname = "alan_accent";

	echo exec(" mysqldump  -u'alan_accent' -p'IdrB*@&T[M}w' alan_accent > db.sql"); 
?>