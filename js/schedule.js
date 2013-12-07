//// MAIN SECTION RUNS WHEN THE DOCUMENT IS READY ////
$(document).ready(function() {

	//// DATE HEADER ////
	$(".dateHeader").click(function(){
		$(this).hide();
		$(".dateInput").show()
			.focus();
	});
	$(".dateInput").blur(function(){
		$(this).hide();
		$(".dateHeader").show();
	});
	$(".dateInput").change(function(){
		$(".dateSubmit").click();
	});
	
	//// EMPLOYEE NAMES ////
	$(".employeeName").click(function() {
		$(this).hide();
		$(this).next(".employeeSelect").show()
			.focus();
	});
	$(".employeeSelect").blur(function() {
		$(this).hide();
		$(this).prev(".employeeName").show();
	});
	$(".employeeSelect").change(function() {
		$(this).next(".changeEmployee").click();
	});
	
	//// NEW APPOINTMENT ////
	$("p.cell_left_new").click(newAppointment);
	$("p.cell_right_new").click(newAppointment);
	
	
	//// EDIT APPOINTMENT ////	
	$(".write_left").click(editApp);
	$(".write_right").click(editApp);
	$(".editCover").click(editCancel);
	
});
//// NEW APPOINTMENT ////
function newAppointment() {
	$(this).hide();
	$(this).next(".new_input").show()
		.focus()
		.blur(function() {
			$(this).hide();
			$(this).prev().show();
		});
}

//// EDIT APPOINTMENT ////
function editApp() {
	var id = $(this).prev(".appId").val();
	$(".editFormDiv").html('<div class="editForm"><h2>Loading...</h2></div>');
	$(".editFormDiv").show();
	$(".editFormDiv").load("includes/editAppointment.php",
	{
		editAjax: true,
		appointmentId: id
	});
	$(".editCover").show();
}
function editCancel() {
	$(".editCover").hide();
	$(".editFormDiv").hide();
}