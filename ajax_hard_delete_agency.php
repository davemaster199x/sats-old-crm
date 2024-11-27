<?php

include('inc/init_for_ajax.php');

$agency_id = mysql_real_escape_string($_POST['agency_id']);

$sql = mysql_query("
	SELECT *
	FROM `property`
	WHERE `agency_id` = {$agency_id}
");

$num_rows = mysql_num_rows($sql);

if( $num_rows>0 ){ // properties found, dont delete	
	echo $num_rows;
}else{
	
	// delete agency
	mysql_query("
		DELETE 
		FROM `agency`
		WHERE `agency_id` = {$agency_id}
	");
	
}

?>