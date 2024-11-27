<?php 

include('inc/init_for_ajax.php');

$tr_id = mysql_real_escape_string($_POST['tr_id']);
$tech_id = mysql_real_escape_string($_POST['tech_id']);
$date = mysql_real_escape_string($_POST['date']);
$sub_regions = mysql_real_escape_string($_POST['sub_regions']);
$get_assigned_only = mysql_real_escape_string($_POST['get_assigned_only']);

$ret1 = 0;
$ret2 = 0;
	
if($get_assigned_only==1){
	
	// get new jobs from via assigned
	$isAssigned = 1;
	$ret2 = appendTechRunNewListings($tr_id,$tech_id,$date,$sub_regions,$_SESSION['country_default'],$isAssigned);
	
}else{
	
	// get new jobs from via region
	$ret1 = appendTechRunNewListings($tr_id,$tech_id,$date,$sub_regions,$_SESSION['country_default']);	
	// get new jobs from via assigned
	$isAssigned = 1;
	$ret2 = appendTechRunNewListings($tr_id,$tech_id,$date,$sub_regions,$_SESSION['country_default'],$isAssigned);
	
}

echo $ret1+$ret2;

?>