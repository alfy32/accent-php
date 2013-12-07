$(document).ready(function(){
	
	//// DATE HEADER CLICK ////
	$(".dateHeader").click(function(){
		$(this).hide();
		$(".dateInput").show();
		$(".dateInput").focus();
	});
	//// DATE HEADER INPUT CHANGED ////
	$(".dateInput").blur(function() {
		$(".dateSubmit").click();
	});
	//// DATE HEADER INPUT BLURRED ////
	//$(".dateInput").blur(function() {
	//	$(this).hide();
	//	$(".dateHeader").show();
	//});
	
	
	//// EMPLOYEE NAME ////
	$(".employeeName").click(function() {
		$(this).hide();
		$(".employeeSelect").show();
		$(".employeeSelect").focus();
	});
	//// EMPLOYEE SELECT CHANGE ///
	$(".employeeSelect").change(function() {
		$(".changeEmployee").click();
	})
	//// EMPLOYEE SELECT BLUR ///
	$(".employeeSelect").blur(function() {
		$(".employeeSelect").hide();
		$(".employeeName").show();
	});
	
	
	//// NEW APPOINTMENT ////
	$(".cell_new").click(function(){
		$(this).hide();
		$(this).next(".new_input").show()
			.focus();
	});
	$(".new_input").blur(function(){
		$(this).hide();
		$(this).prev(".cell_new").show();
	});
	
	//// EDIT APPOINTMENT ////
	$(".write").click(function(){
		$(this).parent().submit();
	});
});

//alert($(window).width());
/*  //// AJAX EXAMPLE ////
$.ajax({
	url:"ajax/employee_select.php",
	success:function(result){
		$(".employee_select").html(result)							
	}
});	
*/