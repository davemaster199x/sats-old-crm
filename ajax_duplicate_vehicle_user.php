<?php
include('inc/init.php');
$json_data['status'] = false;
$staffid = mysql_real_escape_string($_POST['staffid']);

$tt_q = mysql_query("
    SELECT vehicles_id
	FROM `vehicles`
    WHERE (StaffID = {$staffid} AND StaffID!=1)
");

if( mysql_num_rows($tt_q)>0 ){
    $json_data['status'] = true;
}else{
    $json_data['status'] = false;
}
echo json_encode($json_data);
?>