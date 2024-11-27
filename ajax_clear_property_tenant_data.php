<?php
include('inc/init_for_ajax.php');

$property_id = mysql_real_escape_string($_POST['property_id']);

$max_num_ten = getCurrentMaxTenants();
$tent_arr = [];
for( $i=1; $i<=$max_num_ten; $i++ ){
	$tent_arr[] = "
	`tenant_firstname{$i}` = NULL,
	`tenant_lastname{$i}` = NULL,
	`tenant_mob{$i}` = NULL,
	`tenant_ph{$i}` = NULL,
	`tenant_email{$i}` = NULL
	";
}

$tent_comb = implode(",",$tent_arr);

$sql = "
	UPDATE `property`
	SET 
		{$tent_comb}
	WHERE `property_id` = {$property_id}
";
mysql_query($sql);
?>