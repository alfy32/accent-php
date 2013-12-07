<?php

require_once 'MySQL.php';

class Authenticate {
	
	function validateUser($un, $pwd) {
		$mysql = New Mysql();
		
		$ensureCredentials = $mysql->verifyUser($un, md5($pwd));
		
		if($ensureCredentials) {
			$_SESSION['status'] = 'authorized';
		/*	if(preg_match("/(android)|(mobile)/i", $_SERVER['HTTP_USER_AGENT']))
				header("location: mobile.php");
			else
			*/	header("location: index.php");
		} 
		else 
			return "Please enter a correct username and password";
		
	} 
	
	function logOut() {
		if(isset($_SESSION['status'])) {
			unset($_SESSION['status']);
			
			if(isset($_COOKIE[session_name()])) 
				setcookie(session_name(), '', time() - 1000);

			session_destroy();
		}
	}
	
	function confirmMember() {
		session_start();		
		if($_SESSION['status'] !='authorized') 
			header("location: login.php");
	}
	
}