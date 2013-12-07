<?php

//// NEW APPOINTMENT ////
if(isset($_POST['newAppointment'])) {
	$client = htmlentities($_POST['client']);
	$date = htmlentities($_POST['date']);
	$startTime = htmlentities($_POST['startTime']);
	$employeeId = htmlentities($_POST['employeeId']);
	
	$mysql->insertAppointment($date, $startTime, $employeeId, $client);
}

//// EDIT APPOINTMENT ////
if(isset($_POST['editAppointment'])){
	
	$appointmentId = htmlentities($_POST['appointmentId']);
	$client = htmlentities($_POST['client']);
	$employeeId = htmlentities($_POST['employeeId']);
	$date = htmlentities($_POST['date']);
	$startTime = htmlentities($_POST['startTime']);
	$endTime = htmlentities($_POST['endTime']);
	
	//	Start Time is before the salon opens.
	if(strtotime($startTime) < strtotime(OPEN_TIME))
		$mysql->editError = "Start Time is before the salon opens.";
	//	End Time is after the salon closes.
	else if(strtotime($endTime) >= strtotime(CLOSED_TIME))
		$mysql->editError = "End Time is after the salon closes.";
	//	End time is before start time.
	else if(strtotime($endTime) <= strtotime($startTime))
		$mysql->editError = "End time has to be after start time.";
	else {

		$editResult = $mysql->editAppointment($appointmentId, $client, 
							$employeeId, $date, $startTime, $endTime);	
	}
	if($editResult != true) {
		$showEdit = true;
		$coverStyle = "style='display:block'";
	}
}

//// DELETE APPOINTMENT ////
if(isset($_POST['deleteAppointment'])){
	$appointmentId = htmlentities($_POST['appointmentId']);
	
	$mysql->deleteAppointment($appointmentId);
}
//// CHANGE EMPLOYEE ////
if(isset($_POST['switchEmployee'])){
	
	$date = htmlentities($_POST['date']);
	$scheduleCol = htmlentities($_POST['scheduleCol']);
	$dayOfWeek = htmlentities($_POST['dayOfWeek']);
	$oldEmployeeId = htmlentities($_POST['oldEmployeeId']);
	$employeeId = htmlentities($_POST['employeeId']);
	
	$mysql->changeEmployeeCol($scheduleCol, $dayOfWeek, $oldEmployeeId, $employeeId);
}
?>