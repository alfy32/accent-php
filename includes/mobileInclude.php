<?php //echo "<pre>"; print_r($_POST); echo "</pre>";
	define(HOST, "localhost");
	define(USER, "alan_alan");
	define(PASSWORD, "ze3^717?0F-e");
	define(DATABASE, "alan_salon");
	
	define(MAX_HEIGHT, 3);
	
	$database = new mysqli(HOST, USER, PASSWORD, DATABASE);
	if($database->connect_error)
		die("<h3>Database Error: $database->connect_error</h3>");
	
	//// ON NO POST SET THE DATE TO TODAY ////	
	$date = date('Y-m-d');	
	
	//// CHANGE DATE ////
	if(isset($_POST['changeDate'])){	
		$date = htmlentities($_POST['date']);
	}	
	//// LEFT ARROW ////
	if(isset($_POST['leftArrow'])){
		$date = date("Y-m-d", strtotime(htmlentities($_POST['date'])."-1 day"));
	}
	//// RIGHT ARROW ////
	if(isset($_POST['rightArrow'])){
		$date = date("Y-m-d", strtotime(htmlentities($_POST['date'])."+1 day"));
	}
	//// CHANGE EMPLOYEE ////
	if(isset($_POST['changeEmployee'])){
		$date = htmlentities($_POST['date']);
	}
	//// NEW APPOINTMENT ////
	if(isset($_POST['newAppointment'])){
		$client = htmlentities($_POST['client']);
		$date = htmlentities($_POST['date']);
		$employeeId = htmlentities($_POST['employeeId']);
		$startTime = htmlentities($_POST['startTime']);
		
		$mysql->insertAppointment($date, $startTime, $employeeId, $client);
	}
	//// EDIT CANCEL ////
	if(isset($_POST['cancel'])){
		$date = htmlentities($_POST['date']);
		$employeeId = htmlentities($_POST['employeeId']);
	}
	//// EDIT APPOINTMENT ////
	if(isset($_POST['editAppointment'])){
		$date = htmlentities($_POST['date']);
		$appointmentId = htmlentities($_POST['appointmentId']);
		$client = htmlentities($_POST['client']);
		$employeeId = htmlentities($_POST['employeeId']);
		$startTime = htmlentities($_POST['startTime']);
		$endTime = htmlentities($_POST['endTime']);
		
		$mysql->editAppointment($appointmentId, $client, $employeeId, 
								$date, $startTime, $endTime);
	}
	//// DELETE APPOINTMENT ////
	if(isset($_POST['deleteAppointment'])){
		$date = htmlentities($_POST['date']);
		$appointmentId = htmlentities($_POST['appointmentId']);
		$employeeId = htmlentities($_POST['employeeId']);
		
		$mysql->deleteAppointment($appointmentId);
	}
	//// TRACK THE CURRENT EMPLOYEE ////
	if(isset($_POST['employeeId']))
		$employeeId = htmlentities($_POST['employeeId']);
	else
		$employeeId = 1;
		
	$employee = $mysql->getEmployee($employeeId);
	$employeeName = $employee['firstName'];
	
	
	$mysql->selectAppointmentsMobile($date, $employeeId);
	$mysql->selectEmployeeDataMobile($date, $employeeId);	
	
	
	$dayOfWeek = date("l", strtotime($date));
	
	///////////////////  GET APPOINTMENT DATA  //////////////////////////////////	
	$result = $database->query("
		SELECT `appointment_id`, `client`, 
				`date`, `start_time`, `end_time`
		FROM `appointment` 
		WHERE date='$date'
		AND employee_id=$employeeId
	");
	
	$appointment = array();
	while($row = $result->fetch_assoc())
	{
		$start_time = explode(':', $row['start_time']);
		$start_hr = intval($start_time[0]); 
		$start_min = intval($start_time[1]);
		$end_time = explode(':', $row['end_time']);
		$end_hr = intval($end_time[0]); 
		$end_min = intval($end_time[1]);
		
		$client_name = stripslashes($row['client']);
		
		$count = 0;
		$hr = $start_hr;
		$min = $start_min;

		while($hr*100+$min < $end_hr*100+$end_min)
		{
			if($count < MAX_HEIGHT)
					$client_name = "$client_name<br/>x";
					
			$appointment[$hr][$min]['appointmentId'] = $row['appointment_id'];
			$appointment[$hr][$min]['startTime'] = $row['start_time'];
			$appointment[$hr][$min]['endTime'] = $row['end_time'];
			$appointment[$hr][$min]['count'] = $count++;
			
			$min += 15;
			if($min == 60)
			{ 
				$min = 0;
				$hr += 1;
			}
		}
		$appointment[$start_hr][$start_min]['clientName'] = $client_name;
		$appointment[$start_hr][$start_min]['height'] = $count < MAX_HEIGHT ? 44*$count : 44*MAX_HEIGHT;
	}
	
	///////////////////  GET SCHEDULE DATA  /////////////////////////////////////	
	
	
	$result = $database->query("
		SELECT employee.employee_id, schedule_col, day_start, day_end, name_first
		FROM employee_schedule
		LEFT JOIN employee
		ON employee.employee_id = employee_schedule.employee_id
		WHERE day_of_week='$dayOfWeek'
		AND schedule_col >= 0
		AND employee.employee_id=$employeeId
	");
	
	$schedule = array();
	while($result && $row = $result->fetch_assoc()) {
		$schedule['employeeId'] = $row['employee_id'];
		$schedule['employeeName'] = $row['name_first'];
		$schedule['dayStart'] = $row['day_start'];
		$schedule['dayEnd'] = $row['day_end'];
	}
	/////////////////////////////////////////////////////////	
?>