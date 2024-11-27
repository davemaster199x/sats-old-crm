<?php
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$fg_id = mysql_real_escape_string($_POST['fg_id']);

if( $crm->getAgencyPrivateFranchiseGroups($fg_id)== true ){
	echo 1;
}else{
	echo 0;
}

?>
