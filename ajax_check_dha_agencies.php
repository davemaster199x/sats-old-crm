<?php

include('inc/init_for_ajax.php');

$fg_id = mysql_real_escape_string($_POST['fg_id']);
$is_dha_agency = 0;

if( isDHAagenciesV2($fg_id)==true ){
	$is_dha_agency = 1;
}

echo $is_dha_agency;

?>
