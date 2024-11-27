<?php

include('inc/init.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override.php');

$crm = new Sats_Crm_Class;

$ca_id = mysql_real_escape_string($_REQUEST['ca_id']);

// delete old image first
$c_sql = mysql_query("
	SELECT `file_path`
	FROM `contractor_appointment`
	WHERE `contractor_appointment_id` = {$ca_id}
");
$c = mysql_fetch_array($c_sql);

//echo $c['file_path'];


if( $c['file_path']!='' ){
	$file_to_delete = $c['file_path'];
	if( $file_to_delete!="" ){
		$crm->genericDeleteFile($file_to_delete);
	}
}


// delete db
$sql_str = "
	DELETE
	FROM `contractor_appointment`
	WHERE `contractor_appointment_id` = {$ca_id}
";
mysql_query($sql_str);



//header("location: /view_agency_details.php?id={$agency_id}?ca_del_success=1");

?>