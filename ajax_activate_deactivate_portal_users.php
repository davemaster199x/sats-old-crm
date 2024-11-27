<?php

include('inc/init_for_ajax.php');

$pm_id = $_POST['pm_id'];
$status = $_POST['status'];


if( $status == 1 ){ // activate
	
	mysql_query("
		UPDATE `agency_user_accounts`
		SET `active` = 1
		WHERE `agency_user_account_id` = {$pm_id}
	");
	
}else{ // deactivate
	
	// check properties assign to this PM
	$p_sql = mysql_query("
		SELECT *
		FROM `property`
		WHERE `pm_id_new` ={$pm_id}
		AND `deleted` = 0
	");

	if(mysql_num_rows($p_sql)==0){ // delete if PM is not assigneed to any properties
		mysql_query("
			UPDATE `agency_user_accounts`
			SET `active` = 0
			WHERE `agency_user_account_id` = {$pm_id}
		");
		$ret = 0;
	}else{
		$ret = 1;
	}

	echo $ret;
	
}




?>