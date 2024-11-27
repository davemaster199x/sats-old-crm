<?php 

include('inc/init_for_ajax.php');

$job_id_arr = $_POST['job_id_arr'];
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$country_id = $_SESSION['country_default'];

foreach( $job_id_arr as $job_id ){
	send_letters_send_tenant_email($job_id,$staff_id,$country_id);
}

?>

