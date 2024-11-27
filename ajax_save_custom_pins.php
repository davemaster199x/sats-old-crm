<?php

include('inc/init_for_ajax.php');

$pr_id = mysql_real_escape_string($_POST['pr_id']);
$pin_coor_arr = $_POST['pin_coor_arr'];

/*
$custom_pins = implode(',',$pin_coor_arr);
// get custom pins
$pr_sql_str = "
	SELECT `custom_pins`
	FROM `postcode_regions`
	WHERE `postcode_region_id` = {$pr_id}
"; 
$pr_sql = mysql_query($pr_sql_str);
$pr = mysql_fetch_array($pr_sql);
if( $pr['custom_pins']=='' ){
	$sql_str = "
		UPDATE `postcode_regions`
			SET `custom_pins` = '".mysql_real_escape_string($custom_pins)."'
		WHERE `postcode_region_id` = {$pr_id}
	";
}else{
	$sql_str = "
		UPDATE `postcode_regions`
			SET `custom_pins` = '".mysql_real_escape_string($pr['custom_pins']).",".mysql_real_escape_string($custom_pins)."'
		WHERE `postcode_region_id` = {$pr_id}
	";
}



mysql_query($sql_str);
*/

foreach( $pin_coor_arr as $pin_coor ){
	$sql_str = "
		INSERT INTO 
		postcode_regions_custom_pins(
			`postcode_region_id`,
			`coordinates`,
			`country_id`
		)
		VALUES(
			{$pr_id},
			'".mysql_real_escape_string($pin_coor)."',
			{$_SESSION['country_default']}
		)

	";
	mysql_query($sql_str);
}

?>