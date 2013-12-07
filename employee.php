<?php //print_r($_POST);

	require_once 'classes/Authenticate.php';	
	require_once 'classes/MySQL.php';	
	
	//// LOGIN SETUP ////
	$authentication = new Authenticate();
	$authentication->confirmMember();
	
	$mysql = new MySQL();
	
	$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
	if($conn->connect_error) 
		die("<h3>Database Error: $conn->connect_error</h3>");
	
	$firstName = '';
	$lastName = '';
	$phone = '';
	
	$notIn['Monday'] = '';
	$notIn['Tuesday'] = '';
	$notIn['Wednesday'] = '';
	$notIn['Thursday'] = '';
	$notIn['Friday'] = '';
	$notIn['Saturday'] = '';
	$col['Monday'] = '';
	$col['Tuesday'] = '';
	$col['Wednesday'] = '';
	$col['Thursday'] = '';
	$col['Friday'] = '';
	$col['Saturday'] = '';
	$start['Monday'] = '';
	$start['Tuesday'] = '';
	$start['Wednesday'] = '';
	$start['Thursday'] = '';
	$start['Friday'] = '';
	$start['Saturday'] = '';
	$end['Monday'] = '';
	$end['Tuesday'] = '';
	$end['Wednesday'] = '';
	$end['Thursday'] = '';
	$end['Friday'] = '';
	$end['Saturday'] = '';

	/// ON CHOOSE EMPLOYEE ////
	if(isset($_POST['selectEmployeeId'])){ //echo "<pre>"; print_r($_POST); echo "</pre>";
		
		$employeeId = htmlentities($_POST['selectEmployeeId']);
		
		$result = $conn->query("
			SELECT * FROM employee 
			WHERE id='$employeeId'
		");
		
		$row = $result->fetch_assoc();
		
		$firstName = $row['firstName'];
		$lastName = $row['lastName'];
		$phone = $row['phone'];
		$address = $row['address'];
		$city = $row['city'];
		$state = $row['state'];
		$zip = $row['zip'];
		
		
		$result = $conn->query("
			SELECT * FROM employeeSchedule 
			WHERE employeeId='$employeeId'
		");
		
		while($row = $result->fetch_assoc()) {
			$start[ucfirst($row['dayOfWeek'])] = $row['dayStart'];
			$end[ucfirst($row['dayOfWeek'])] = $row['dayEnd'];
			$col[ucfirst($row['dayOfWeek'])] = $row['scheduleCol'];
			if($row['scheduleCol'] < 0)
				$notIn[ucfirst($row['dayOfWeek'])] = 'checked';
		}
	}
	
	//// ON ACCEPT CHANGES ////
	if(isset($_POST['accept'])) { //echo "<pre>"; print_r($_POST); echo "</pre>";
		
		$employeeId = htmlentities($_POST['employeeId']);
		$firstName = htmlentities($_POST['firstName']);
		$lastName = htmlentities($_POST['lastName']);
		$phone = htmlentities($_POST['phone']);
		$address = htmlentities($_POST['address']);
		$city = htmlentities($_POST['city']);
		$state = htmlentities($_POST['state']);
		$zip = htmlentities($_POST['zip']);
		
		$result = $conn->query("
			UPDATE employee
			SET firstName='$firstName', lastName='$lastName', 
				phone='$phone', address='$address',
				city='$city', state='$state', zip='$zip'
			WHERE id=$employeeId
		");
		
		if($result)
			$message = "$firstName's info was updated successfully.";
		else
			$message = "Database update fail";
		
		$notIn = $_POST['notIn'];
		$col = $_POST['col'];
		$start = $_POST['start'];
		$end = $_POST['end'];
		
		foreach($col as $day=>$val) {
			$col[$day] = htmlentities($col[$day]);
			$start[$day] = htmlentities($start[$day]);
			$end[$day] = htmlentities($end[$day]);
			
			if(isset($notIn[$day])){
				$col[$day] = -$employeeId;
				$notIn[$day] = 'checked';
			}
			
			$result = array();
			$result[$day] = $conn->query("
				INSERT INTO employeeSchedule (employeeId, scheduleCol, dayStart, dayEnd, dayOfWeek)
				VALUES ($employeeId, $col[$day], '$start[$day]', '$end[$day]', '$day')
			");
			$result[$day] = $conn->query("
				UPDATE employeeSchedule
				SET scheduleCol=$col[$day], 
					dayStart='$start[$day]', dayEnd='$end[$day]'
				WHERE dayOfWeek='$day'
				AND employeeId=$employeeId
			");
		}		
	}	
	
 ?>
 <!DOCTYPE html>
<html lang='en'>
	<head>
		<title>Employee Manager</title>
		<meta charset="UTF-8" />
		<link rel="stylesheet" type="text/css" href="css/employee.css">
		<link rel="stylesheet" type="text/css" href="css/schedule_header.css">
		<link rel="stylesheet" type="text/css" href="css/footer.css">
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script type="text/javascript" src="js/employee.js"></script>
	</head>
	<body>
		<header>
			<a class="headerLeft" href="">
				<img class="logoLeft" src="images/logoHeader.jpg" alt="Accent Logo" />
			</a>
			<div class="headerCenter">
				<p>Employee Manager</p>
			</div>
			<a class="headerRight" href="">
				<img class="logoRight" src="images/logoHeader.jpg" alt="Accent Logo" />
			</a>
		</header>
		<div class="main">
			<div class="left">
				<fieldset><legend>Choose Employee</legend>
					<form action="employee.php" method="POST">
						<select tabindex="1" name="selectEmployeeId" size='2' ondblclick="submit()">
						<?php $mysql->getEmployeeOptions(); ?>
						</select> <br />
						<input type="submit" name="chooseEmployee" value="Choose Employee" />
					</form>
				</fieldset>
			</div>
			<div class="center">
				<form action="employee.php" method="POST">
				<fieldset class="personal"><legend>Personal</legend>
					<input type="hidden" name="employeeId" value="<?php echo $employeeId ?>" />
					<table>
						<tr>
							<td><label>First Name</label></td>
							<td><input tabindex="2" type="text" name="firstName" value="<?php echo $firstName; ?>" /></td>
							<td class="tableSpacer"></td>
							<td><label>Address</label></td>
							<td><input tabindex="5" type="text" name="address" value="<?php echo $address; ?>" /></td>
						</tr>
						<tr>
							<td><label>Last Name</label></td>
							<td><input tabindex="3" type="text" name="lastName" value="<?php echo $lastName; ?>" /></td>
							<td class="tableSpacer"></td>
							<td><label>City</label></td>
							<td><input tabindex="6" type="text" name="city" value="<?php echo $city; ?>" /></td>
						</tr>
						<tr>
							<td><label>Phone</label></td>
							<td><input tabindex="4" type="text" name="phone" value="<?php echo $phone; ?>" /></td>
							<td class="tableSpacer"></td>
							<td><label>State</label></td>
							<td><input tabindex="7" type="text" name="state" value="<?php echo $state; ?>" />
							<label class="zip">Zip</label><input tabindex="8" type="text" name="zip" value="<?php echo $zip; ?>" /></td>
						</tr>
					</table>
				</fieldset>
				<fieldset class="schedule"><legend>Schedule</legend>
					<table>
						<tr>
							<th></th>
							<th>Monday</th>
							<th>Tuesday</th>
							<th>Wednesday</th>
							<th>Thursday</th>
							<th>Friday</th>
							<th>Saturday</th>
						</tr>
						<tr>
							<td>Not In</td>
							<td><input class="notMon" type="checkbox" name="notIn[Monday]" <?php echo $notIn['Monday']; ?> /></td>
							<td><input class="notTue" type="checkbox" name="notIn[Tuesday]" <?php echo $notIn['Tuesday']; ?> /></td>
							<td><input class="notWed" type="checkbox" name="notIn[Wednesday]" <?php echo $notIn['Wednesday']; ?> /></td>
							<td><input class="notThu" type="checkbox" name="notIn[Thursday]" <?php echo $notIn['Thursday']; ?> /></td>
							<td><input class="notFri" type="checkbox" name="notIn[Friday]" <?php echo $notIn['Friday']; ?> /></td>
							<td><input class="notSat" type="checkbox" name="notIn[Saturday]" <?php echo $notIn['Saturday']; ?> /></td>
						</tr>
						<tr>
							<td>Column</td>
							<td><input class="mon" type="text" name="col[Monday]" value="<?php echo $col['Monday']; ?>" maxlength="1" size="1" /></td>
							<td><input class="tue" type="text" name="col[Tuesday]" value="<?php echo $col['Tuesday']; ?>" maxlength="1" size="1" /></td>
							<td><input class="wed" type="text" name="col[Wednesday]" value="<?php echo $col['Wednesday']; ?>" maxlength="1" size="1" /></td>
							<td><input class="thu" type="text" name="col[Thursday]" value="<?php echo $col['Thursday']; ?>" maxlength="1" size="1" /></td>
							<td><input class="fri" type="text" name="col[Friday]" value="<?php echo $col['Friday']; ?>" maxlength="1" size="1" /></td>
							<td><input class="sat" type="text" name="col[Saturday]" value="<?php echo $col['Saturday']; ?>" maxlength="1" size="1" /></td>
						</tr>
						<tr>
							<td>Start Time</td>
							<td><input class="mon" type="time" name="start[Monday]" value="<?php echo $start['Monday']; ?>" /></td>
							<td><input class="tue" type="time" name="start[Tuesday]" value="<?php echo $start['Tuesday']; ?>" /></td>
							<td><input class="wed" type="time" name="start[Wednesday]" value="<?php echo $start['Wednesday']; ?>" /></td>
							<td><input class="thu" type="time" name="start[Thursday]" value="<?php echo $start['Thursday']; ?>" /></td>
							<td><input class="fri" type="time" name="start[Friday]" value="<?php echo $start['Friday']; ?>" /></td>
							<td><input class="sat" type="time" name="start[Saturday]" value="<?php echo $start['Saturday']; ?>" /></td>
						</tr>
						<tr>
							<td>End Time</td>
							<td><input class="mon" type="time" name="end[Monday]" value="<?php echo $end['Monday']; ?>" /></td>
							<td><input class="tue" type="time" name="end[Tuesday]" value="<?php echo $end['Tuesday']; ?>" /></td>
							<td><input class="wed" type="time" name="end[Wednesday]" value="<?php echo $end['Wednesday']; ?>" /></td>
							<td><input class="thu" type="time" name="end[Thursday]" value="<?php echo $end['Thursday']; ?>" /></td>
							<td><input class="fri" type="time" name="end[Friday]" value="<?php echo $end['Friday']; ?>" /></td>
							<td><input class="sat" type="time" name="end[Saturday]" value="<?php echo $end['Saturday']; ?>" /></td>
						</tr>
					</table>
				</fieldset>
				<input type="submit" name="cancel" value="Cancel" />
				<input type="submit" name="accept" value="Update" />
				</form>
			</div>
			<div style="clear:both"></div>
		</div>
		<div class="spacer"></div>
		<footer>
			<ul>
				<li><a href="index.php">Schedule</a></li>|
				<li><a href="login.php?status=loggedout">Log Out</a></li>
			</ul>
		</footer>
	</body>
</html>