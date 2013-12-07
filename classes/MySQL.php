<?php

require_once '/home/alan/public_html/accent/includes/constants.php';

class MySQL {
	private $conn;
	public $editError; 		// Error message for editAppointment
	public $appointment; 	// Array of all appointments for the day
	public $schedule;	 	// Array employee schedule data 
	
	private $userType;
	private $employeeId;
	
	function __construct() {
		$this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
		if($this->conn->connect_error)
			die("<h3>Database Error: $this->conn->connect_error</h3>");
	
	}
	
	function verifyUser($un, $pwd) {
			
		$query = "SELECT *
				FROM users
				WHERE username = ? AND password = ?
				LIMIT 1";
				
		if($stmt = $this->conn->prepare($query)) {
			$stmt->bind_param('ss', $un, $pwd);
			$stmt->execute();
			
			if($stmt->fetch()) {
				$stmt->close();
				return true;
			}
		}
		
	}
	
	function getAppointment($appointmentId) {
		$result = $this->conn->query("
			SELECT * FROM appointment 
			WHERE id=$appointmentId
		");
		
		return $result->fetch_assoc();
	}
	
	function insertAppointment($date, $startTime, $employeeId, $client) {
		$date = $this->conn->real_escape_string($date);
		$startTime = $this->conn->real_escape_string($startTime);
		$employeeId = $this->conn->real_escape_string($employeeId);
		$client = $this->conn->real_escape_string($client);
		
		return $this->conn->query("
			INSERT INTO appointment (date, startTime, endTime, 
									 employeeId, client)
			VALUES ('{$date}',
					TIME('{$startTime}'),ADDTIME('{$startTime}','00:30:00'), 
					{$employeeId},'{$client}')
		");
	}
	
	function editAppointment($appointmentId, $client, $employeeId, 
								$date, $startTime, $endTime) {
		
		// Don't allow the endTime to be before the startTime
		if(strtotime($endTime) <= strtotime($startTime)) {
			$this->editError = "End time must be after start time.";
			return false;
		}
								
		$appointmentId = $this->conn->real_escape_string($appointmentId);
		$client = $this->conn->real_escape_string($client);
		$employeeId = $this->conn->real_escape_string($employeeId);
		$date = $this->conn->real_escape_string($date);
		$startTime = $this->conn->real_escape_string($startTime);
		$endTime = $this->conn->real_escape_string($endTime);
		
		// Check for overlapping appointments
		$result = $this->conn->query("
			SELECT COUNT(*) FROM appointment WHERE date='$date'
			AND employeeId=$employeeId AND id!=$appointmentId
			AND (
			  (
				startTime >= '$startTime'
				AND startTime < '$endTime'
			  )
			  OR (
				endTime > '$startTime'
				AND endTime <= '$endTime'
			  )
			  OR (
				startTime <= '$startTime'
				AND endTime > '$startTime'
			  )
			  OR (
				startTime < '$endTime'
				AND endTime >= '$endTime'
			  )
			)
		");
		
		// If we found a result then it overlaps another appointment
		$row = $result->fetch_array();
		if($row[0]) {
			$this->editError = "There is already an appointment at that time.";
			return false;
		}
		
		
		// If we made it here there were no errors so update appointment
		$this->conn->query("
			UPDATE appointment
			SET client='{$client}', employeeId='{$employeeId}', 
				date='{$date}',
				startTime='{$startTime}', endTime='{$endTime}'
			WHERE id={$appointmentId}
		");
		
		return true;
	}
	
	function deleteAppointment($appointmentId) {
		return $this->conn->query("
			DELETE FROM appointment
			WHERE id={$appointmentId}
		");
	}
	
	function getEmployeeOptions($employeeId) {
		$result = $this->conn->query("
			SELECT id, firstName 
			FROM employee
			ORDER BY firstName
		");
		while($result && $row = $result->fetch_assoc()) {
			if($row['id'] == $employeeId)  
				$selected = 'selected';
			else
				$selected = '';
				
			echo "<option value='$row[id]' $selected>$row[firstName]</option>";
		}
	}
	
	function changeEmployeeCol($scheduleCol, $dayOfWeek, $prevEmployeeId, $employeeId) {
		$scheduleCol = $this->conn->real_escape_string($scheduleCol);
		$dayOfWeek = $this->conn->real_escape_string($dayOfWeek);
		$prevEmployeeId = $this->conn->real_escape_string($prevEmployeeId);
		$employeeId = $this->conn->real_escape_string($employeeId);
		
		//echo "$scheduleCol, $dayOfWeek, $prevEmployeeId, $employeeId";
		
		//make the current one to nothing 
		$this->conn->query("
			UPDATE employeeSchedule
			SET scheduleCol=-{$prevEmployeeId}
			WHERE employeeId={$prevEmployeeId}
			AND dayOfWeek='{$dayOfWeek}'
		");
		
		// Insert or update the new employee
		$result = $this->conn->query("
			INSERT INTO employeeSchedule 
				(employeeId, dayOfWeek, scheduleCol)
			VALUES  ($employeeId, '$dayOfWeek', $scheduleCol)
			ON DUPLICATE KEY UPDATE scheduleCol=$scheduleCol
		");
		
		return $result;
	}
	
	function selectAppointments($date, $employeeId=0) {
		
		$date = $this->conn->real_escape_string($date);
		$dayOfWeek = date("l", strtotime($date));
		
		$result = $this->conn->query("
			SELECT  `appointment`.`id`, `appointment`.`client`, 
					`appointment`.`startTime`, `appointment`.`endTime`, 
					`employee`.`firstName`, `employeeSchedule`.`scheduleCol`
			FROM  `appointment` 
			LEFT JOIN  `employee` 
			ON `appointment`.`employeeId`=`employee`.`id` 
			LEFT JOIN  `employeeSchedule` 
			ON `appointment`.`employeeId`=`employeeSchedule`.`employeeId` 
			WHERE `date`='$date'
			AND `dayOfWeek`='$dayOfWeek'
		");
		
		$this->appointment = array();
		
		while($result && $row = $result->fetch_assoc())
		{
			$startTime = explode(':', $row['startTime']);
			$start_hr = intval($startTime[0]); 
			$start_min = intval($startTime[1]);
			$endTime = explode(':', $row['endTime']);
			$end_hr = intval($endTime[0]); 
			$end_min = intval($endTime[1]);
			
			$start_min = intval($start_min/15)*15;
			$end_min = intval(($end_min+14)/15)*15;
			
			$clientName = stripslashes($row['client']);
			
			$count = 0;
			$hr = $start_hr;
			$min = $start_min;
			$col = $row['scheduleCol'];

			while($hr*100+$min < $end_hr*100+$end_min)
			{
				if($count < MAX_HEIGHT)
					$clientName = "$clientName<br/>x";
				
				$this->appointment[$col][$hr][$min]['appointmentId'] = $row['id'];
				$this->appointment[$col][$hr][$min]['startTime'] = $row['startTime'];
				$this->appointment[$col][$hr][$min]['endTime'] = $row['endTime'];
				$this->appointment[$col][$hr][$min]['count'] = $count++;
				
				$min += 15;
				if($min == 60)
				{ 
					$min = 0;
					$hr += 1;
				}
			}
			$this->appointment[$col][$start_hr][$start_min]['clientName'] = $clientName;
			$this->appointment[$col][$start_hr][$start_min]['height'] = $count < MAX_HEIGHT ? 17*$count : 17*MAX_HEIGHT;

		}
	}
	
	function selectEmployeeData($date){
		
		$date = $this->conn->real_escape_string($date);
		$dayOfWeek = date("l", strtotime($date));
		
		$result = $this->conn->query("
			SELECT `employee`.`id`, firstName, scheduleCol, dayStart, dayEnd
			FROM employeeSchedule
			LEFT JOIN employee
			ON `employee`.`id`=`employeeSchedule`.`employeeId`
			WHERE dayOfWeek='$dayOfWeek'
			AND scheduleCol >= 0
		");
		
		$this->schedule = array();
		
		while($result && $row = $result->fetch_assoc())
		{
			$col = $row['scheduleCol'];
			$this->schedule[$col]['employeeId'] = $row['id'];
			$this->schedule[$col]['employeeName'] = $row['firstName'];
			$this->schedule[$col]['dayStart'] = $row['dayStart'];
			$this->schedule[$col]['dayEnd'] = $row['dayEnd'];
		}
	}
	
	function getEmployeeHeader($col, $date)	{ 
		
		$dayOfWeek = date("l", strtotime($date));
		
		$employeeId = $this->schedule[$col]['employeeId'];
		$employeeName = "_____";
		if($this->schedule[$col]['employeeName'] != '')
			$employeeName = $this->schedule[$col]['employeeName'];
		
		echo "
		<th>
			<form action='' method='post' >
				<input type='hidden' name='date' value='{$date}' />
				<input type='hidden' name='scheduleCol' value='{$col}' />
				<input type='hidden' name='dayOfWeek' value='{$dayOfWeek}' />
				<input type='hidden' name='oldEmployeeId' value='{$employeeId}' />
				<p class='employeeName' style='margin:0px' >{$employeeName}</p>
				<select class='employeeSelect' name='employeeId' style='display:none'>
					<option value='none'>None</option>
					"; $this->getEmployeeOptions($employeeId); echo "		
			</select>
				<input class='changeEmployee' type='submit' name='changeEmployee' value='Change Employee' style='display:none' />
			</form>
		</th>
		";
	}
	
	function getEmployee($employeeId) {
		$result = $this->conn->query("
			SELECT * FROM employee
			WHERE id=$employeeId
		");
		
		return $result->fetch_assoc();
	}
	
	function getCell($col, $date, $timeString, $pos) 	{
		$time = explode(':',$timeString);
		$hour = intval($time[0]);
		$min = intval($time[1]);
		
		if($this->appointment[$col][$hour][$min]['appointmentId']) 
		{
			$appointmentId = $this->appointment[$col][$hour][$min]['appointmentId'];
			$height = $this->appointment[$col][$hour][$min]['height'];
			$clientName = $this->appointment[$col][$hour][$min]['clientName'];
			$count = $this->appointment[$col][$hour][$min]['count'];
			
			if($count == 0)
				return "
					<td class='cell_{$min}' >
						<input class='appId' type='hidden' value='$appointmentId' />
						<p class='write_{$pos}' style='height:{$height}px'>{$clientName}</p>
						<p class='cell_{$pos}' >{$min}</p>
					</td>
				";
			else if($count >= MAX_HEIGHT)
				return "
					<td class='cell_{$min}' >
						<input class='appId' type='hidden' value='$appointmentId' />
						<p class='write_{$pos}' style='height:{$height}px'>x</p>
						<p class='cell_{$pos}' >{$min}</p>
					</td>
				";
			else
				return "
					<td class='cell_{$min}' >
						<p class='cell_{$pos}' >{$min}</p>
					</td>
				";
		}
		else
		{	
			// Time not in the schedule
			if( (strtotime($this->schedule[$col]['dayStart']) > strtotime($timeString)) ||
				(strtotime($timeString) >= strtotime($this->schedule[$col]['dayEnd'])) ) {
				
				return "
					<td class='cell_{$min}_out' >
						<p class='write_{$pos}_out'>x</p>
						<p class='cell_{$pos}' >{$min}</p>
					</td>
				";
			}
			else {
				$employeeId = $this->schedule[$col]['employeeId'];
				return "
				<td class='cell_{$min}' id='cellHover' >
					<form action='' method='post'>
						<p class='cell_{$pos}_new' >{$min}</p>
						<input class='new_input' type='text' name='client' style='display:none' />
						<input type='hidden' name='employeeId' value='{$employeeId}'/>
						<input type='hidden' name='date' value='{$date}'/>
						<input type='hidden' name='startTime' value='{$timeString}' />
						<input type='submit' name='newAppointment' value='New' style='display:none'/>
					</form>
				</td>
				";
			}
		}
	}
	
	function getCellHour($hour)	{
		//convert from 24 hours to 12 hours
		$am = $hour < 12 ? 'AM' : 'PM';
		$hour = ($hour-1)%12+1;
		
		return "
			<td class='hour' rowspan='4'>
				<p class='hour'>$hour</p>
				<p class='$am'>$am</p>
			</td>
		";
	}
	
	function getEmployeeList($employeeId) {
		$result = $this->conn->query("
			SELECT id, firstName FROM employee
		");
		while($result && $row = $result->fetch_assoc()) {
			if($row['id'] == $employeeId)  
				$selected = 'selected';
			else
				$selected = '';
				
			echo "<option value='$row[id]' $selected>$row[firstName]</option>";
		}
	}
	
	function selectAppointmentsMobile($date, $employeeId) {
		
		$date = $this->conn->real_escape_string($date);
		$dayOfWeek = date("l", strtotime($date));
		
		$result = $this->conn->query("
			SELECT `id`, `client`, 
			`date`, `startTime`, `endTime`
			FROM `appointment` 
			WHERE date='$date'
			AND employeeId=$employeeId
		");
		
		$this->appointment = array();
		
		while($result && $row = $result->fetch_assoc())
		{
			$startTime = explode(':', $row['startTime']);
			$start_hr = intval($startTime[0]); 
			$start_min = intval($startTime[1]);
			$endTime = explode(':', $row['endTime']);
			$end_hr = intval($endTime[0]); 
			$end_min = intval($endTime[1]);
			
			$start_min = intval($start_min/15)*15;
			$end_min = intval(($end_min+14)/15)*15;
			
			$clientName = stripslashes($row['client']);
			
			$count = 0;
			$hr = $start_hr;
			$min = $start_min;

			while($hr*100+$min < $end_hr*100+$end_min)
			{
				if($count < MAX_HEIGHT)
					$clientName = "$clientName<br/>x";
				
				$this->appointment[$hr][$min]['appointmentId'] = $row['id'];
				$this->appointment[$hr][$min]['startTime'] = $row['startTime'];
				$this->appointment[$hr][$min]['endTime'] = $row['endTime'];
				$this->appointment[$hr][$min]['count'] = $count++;
				
				$min += 15;
				if($min == 60)
				{ 
					$min = 0;
					$hr += 1;
				}
			}
			$this->appointment[$start_hr][$start_min]['clientName'] = $clientName;
			$this->appointment[$start_hr][$start_min]['height'] = $count < MAX_HEIGHT ? 44*$count : 44*MAX_HEIGHT;

		}
	}
	
	function selectEmployeeDataMobile($date, $employeeId){
	
		$date = $this->conn->real_escape_string($date);
		$dayOfWeek = date("l", strtotime($date));
		
		$result = $this->conn->query("
			SELECT id, dayStart, dayEnd, firstName
			FROM employeeSchedule
			LEFT JOIN employee
			ON employee.id = employeeSchedule.employeeId
			WHERE dayOfWeek='$dayOfWeek'
			AND employee.id=$employeeId
		");
		
		$this->schedule = array();
		
		while($result && $row = $result->fetch_assoc())
		{
			$this->schedule['employeeId'] = $row['id'];
			$this->schedule['employeeName'] = $row['firstName'];
			$this->schedule['dayStart'] = $row['dayStart'];
			$this->schedule['dayEnd'] = $row['dayEnd'];
		}
	}
	
	function getCellMobile($date, $timeString)	{
		$time = explode(':',$timeString);
		$hour = intval($time[0]);
		$min = intval($time[1]);
		
		if($this->appointment[$hour][$min]['appointmentId']) 
		{
			$appointmentId = $this->appointment[$hour][$min]['appointmentId'];
			$height = $this->appointment[$hour][$min]['height'];
			$clientName = $this->appointment[$hour][$min]['clientName'];
			$count = $this->appointment[$hour][$min]['count'];
			
			if($count == 0)
				return "
					<td class='cell_{$min}' >
						<form method='post' action='mobileEdit.php' >
							<input type='hidden' name='appointmentId' value='$appointmentId' />
							<p class='write' style='height:{$height}px'>{$clientName}</p>
							<p class='cell' >{$min}</p>
							<input type='submit' name='editAppointment' style='display:none' />
						</form>
					</td>
				";
			else if($count >= MAX_HEIGHT)
				return "
					<td class='cell_{$min}' >
						<form method='post' action='mobileEdit.php' >
							<input type='hidden' name='appointmentId' value='$appointmentId' />
							<p class='write' style='height:{$height}px'>x</p>
							<p class='cell' >{$min}</p>
							<input type='submit' name='editAppointment' style='display:none' />
						</form>
					</td>
				";
			else
				return "
					<td class='cell_{$min}' >
						<p class='cell' >{$min}</p>
					</td>
				";
		}
		else
		{	
			// Time not in the schedule
			if( (strtotime($this->schedule['dayStart']) > strtotime($timeString)) ||
				(strtotime($timeString) >= strtotime($this->schedule['dayEnd'])) ) {
				
				return "
					<td class='cell_{$min}_out' >
						<p class='write_out'>x</p>
						<p class='cell' >{$min}</p>
					</td>
				";
			}
			else {
				$employeeId = $this->schedule['employeeId'];
				return "
				<td class='cell_{$min}' id='cellHover' >
					<form action='' method='post'>
						<p class='cell_new' >{$min}</p>
						<input class='new_input' type='text' name='client' style='display:none' />
						<input type='hidden' name='employeeId' value='{$employeeId}'/>
						<input type='hidden' name='date' value='{$date}'/>
						<input type='hidden' name='startTime' value='{$timeString}' />
						<input type='submit' name='newAppointment' value='New' style='display:none'/>
					</form>
				</td>
				";
			}
		}
	}
}

?>