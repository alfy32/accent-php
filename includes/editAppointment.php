<?php
	
	//// WHEN ACCESSED BY AJAX ////
	if(isset($_POST['editAjax'])) {
		require_once '../classes/MySQL.php';
		
		$mysql = new MySQL();	
		
		$appointmentId = $_POST['appointmentId'];
		
		$row = $mysql->getAppointment($appointmentId);
		
		$client = $row['client'];
		$employeeId = $row['employeeId'];
		$date = $row['date'];
		$startTime = $row['startTime'];
		$endTime = $row['endTime'];
		
		$showEdit = true;
	}
	else {	
?>
<div class='editCover' <?php if(isset($coverStyle)) echo $coverStyle ?> ></div>
<div class='editFormDiv'>
<?php
	}
	//// WHEN ACCESED ON EDIT FAIL STAY AS IS ////
	if(isset($showEdit) && $showEdit === true){
?>
	<div class="editForm">
		<h2>Edit Appointment</h2>
		<form action="" method="POST" >
			<input type="hidden" name="appointmentId" value="<?php echo $appointmentId ?>" />
			<label>Client:</label>		
			<input type="text" name="client" value="<?php echo $client ?>" /><br>
			<label>Employee:</label>	
			<select name="employeeId" id="edit_employee">
				<?php $mysql->getEmployeeOptions($employeeId); ?>
			</select><br>
			<label>Date:</label>		
			<input type="date" name="date" value="<?php echo $date ?>" /><br>
			<label>Start Time:</label>	
			<input type="time" name="startTime" value="<?php echo $startTime ?>" /><br>
			<label>End Time:</label>	
			<input type="time" name="endTime" value="<?php echo $endTime ?>" /><br>
			<?php echo "<p>$mysql->editError</p>" ?>
			<input class="edit_submit" type="submit"  name="editAppointment" value="Edit Appointment" /> <br/>
			<input class="edit_submit" type="submit"  name="deleteAppointment" value="Delete Appointment" /> <br/>
			<input class="edit_cancel" type="button"  value="Cancel" onclick="editCancel()" />
		</form>
	</div>
<?php
	}
	if(!isset($_POST['editAjax'])) {
		echo "</div>";
	}
?>