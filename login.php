<?php
session_start();
require_once 'classes/Authenticate.php';
$authentication = new Authenticate();

// If the user clicks the "Log Out" link on the index page.
if(isset($_REQUEST['status']) && $_REQUEST['status'] == 'loggedout') {
	$authentication->logOut();
}

// Did the user enter a password/username and click submit?
if($_POST && isset($_POST['user']) && isset($_POST['pwd'])) {
	$response = $authentication->validateUser($_POST['user'], $_POST['pwd']);
}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<title>Accent Login</title>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=480" />
		<link rel="stylesheet" type="text/css" href="css/accent_login.css">
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script type="text/javascript" src="js/login.js"></script>
	</head>
	<body>
		<header></header>
		<div class="padding">
			<div class="login">
				<form method="post" action="">
					<label for="name">Username: </label> <br/>
					<input class="input" type="text" name="user" />
					<br/>
					<label for="pwd">Password: </label> <br/>
					<input class="input" type="password" name="pwd" />
					<br />
					<?php if(isset($response)) echo "<h4 class='alert'>$response</h4>"; ?>
					<input class="submit" type="submit" id="submit" value="Login" name="submit" />
				</form>
			</div>
		</div>
	</body>
</html>