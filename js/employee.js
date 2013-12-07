$(document).ready(function(){
	//// NOT_IN CHECKED ////
	if( $(".notMon:checked").length ) $(".mon").hide();
	if( $(".notTue:checked").length ) $(".tue").hide();
	if( $(".notWed:checked").length ) $(".wed").hide();
	if( $(".notThu:checked").length ) $(".thu").hide();
	if( $(".notFri:checked").length ) $(".fri").hide();
	if( $(".notSat:checked").length ) $(".sat").hide();
	
	//// NOT_IN CLICKED ////
	$(".notMon").click(function(){ notIn_check(this,'.mon') });
	$(".notTue").click(function(){ notIn_check(this,'.tue') });
	$(".notWed").click(function(){ notIn_check(this,'.wed') });
	$(".notThu").click(function(){ notIn_check(this,'.thu') });
	$(".notFri").click(function(){ notIn_check(this,'.fri') });
	$(".notSat").click(function(){ notIn_check(this,'.sat') });
});

function notIn_check(checkBox, col){
	if(checkBox.checked) 
		$(col).hide();
	else
		$(col).show();
}