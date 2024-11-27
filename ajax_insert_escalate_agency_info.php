<?php

include('inc/init_for_ajax.php');

$agency_id = mysql_real_escape_string($_POST['agency_id']);
$eai_field = mysql_real_escape_string($_POST['eai_field']);
$eai_val = mysql_real_escape_string($_POST['eai_val']);
$country_id = $_SESSION['country_default'];

$crm = new Sats_Crm_Class();

$jparams = array(
	'country_id' => $country_id,
	'agency_id' => $agency_id,
	'date' => date('Y-m-d')
);
$eai_sql = $crm->getEscalateAgencyInfo($jparams);

if( mysql_num_rows($eai_sql)>0 ){
	
	if( $eai_field == 'notes' ){
		$update_notes_ts_str = "
			,`notes_timestamp` = '".date("Y-m-d H:i:s")."'
		";
	}
	
	$eai = mysql_fetch_array($eai_sql);
	$sql = "
		UPDATE `escalate_agency_info` 
		SET
			{$eai_field} = '{$eai_val}'
			{$update_notes_ts_str}
		WHERE `escalate_agency_info_id` = {$eai['escalate_agency_info_id']}
	";
	
}else{
	
	if( $eai_field == 'notes' ){
		$insert_notes_ts_field = "`notes_timestamp`,";
		$insert_notes_ts_val = "'".date("Y-m-d H:i:s")."',";
	}
	
	$sql = "
		INSERT INTO 
		`escalate_agency_info` (
			`agency_id`,
			{$eai_field},
			{$insert_notes_ts_field}
			`country_id`
		)
		VALUES(
			{$agency_id},
			'{$eai_val}',
			{$insert_notes_ts_val}
			{$country_id}
		)
	";
}

mysql_query($sql);	

?>