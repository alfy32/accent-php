<?php
	
	require_once 'classes/Authenticate.php';	
	require_once 'classes/MySQL.php';	
	
	//// LOGIN SETUP ////
	$authentication = new Authenticate();
	$authentication->confirmMember();
	
	$mysql = new MySQL();
	
	$appointmentId = $_POST['appointmentId'];
		
	$row = $mysql->getAppointment($appointmentId);
	
	$client = $row['client'];
	$employeeId = $row['employeeId'];
	$date = $row['date'];
	$startTime = $row['startTime'];
	$endTime = $row['endTime'];
	
	//echo "<pre>"; print_r($_POST); echo "</pre>";
	// go back a page: onclick="window.history.go(-1)";
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Edit Appointment</title>
		<link rel="stylesheet" type="text/css" href="css/mobileEdit.css">
		<meta charset="UTF-8">
		<meta name="viewport" content="width=320, user-scalable=no" />
		
		<!-- TO DISABLE CHACHING -->
		<meta http-equiv="cache-control" content="no-cache">
		<meta http-equiv="pragma" content="no-cache">
		<!-- 					 -->
		
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script type="text/javascript" src="js/mobileEdit.js"></script>
	</head>
	<body>
		<header>Edit Appointment</header>
		<form action="mobile.php" method="post">
			<input type="hidden" name="appointmentId" value="<?php echo $appointmentId ?>" />
			<label>Client:</label>		
			<input type="text" name="client" value="<?php echo $client ?>" />
			<label>Employee:</label>	
			<select name="employeeId" id="edit_employee">
				<?php $mysql->getEmployeeOptions($employeeId); ?>
			</select>
			<label>Date:</label>		
			<input type="date" name="date" value="<?php echo $date ?>" />
			<label>Start Time:</label>	
			<input type="time" name="startTime" value="<?php echo $startTime ?>" />
			<label>End Time:</label>	
			<input type="time" name="endTime" value="<?php echo $endTime ?>" />
			<?php echo "<p>$mysql->editError</p>" ?>
			<input class="edit_submit" type="submit"  name="editAppointment" value="Edit Appointment" />
			<input class="edit_submit" type="submit"  name="deleteAppointment" value="Delete Appointment" /> 
			<input class="edit_cancel" type="submit"  name="cancel" value="Cancel" />
		</form>
	</body>
</html>