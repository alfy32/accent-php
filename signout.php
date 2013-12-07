<?php
	session_start();
	
	require_once 'classes/Authenticate.php';
	
	$authentication = new Authenticate();
	$authentication->logOut();
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<title>Accent signout</title>
		<meta charset="UTF-8" />
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		Thank you for signing out.
	</body>
</html>