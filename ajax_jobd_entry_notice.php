<?php 

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$booked_with = mysql_real_escape_string($_POST['booked_with']);

$sql = "
	INSERT INTO 
	`job_log` (
		`contact_type`,
		`eventdate`,
		`comments`,
		`job_id`, 
		`staff_id`,
		`eventtime`
	) 
	VALUES (
		'Entry Notice',
		'".date('Y-m-d')."',
		'".$booked_with." refused the offer of an entry notice',
		{$job_id}, 
		'".$_SESSION['USER_DETAILS']['StaffID']."',
		'".date('H:i')."'
	)
";
mysql_query($sql);

if(mysql_affected_rows()>0){
	echo 1;
}else{
	echo 0;
}

?>

