<?php
include('inc/init_for_ajax.php');

$country_id = $_SESSION['country_default'];

// update agency printing tracking fields
$sql = "
	UPDATE `agency`
	SET 
		`pt_completed` = NULL,
		`pt_no_statement_needed` = NULL,
		`pt_sent_to_va` = NULL
	WHERE `country_id` = {$country_id}
";
mysql_query($sql);
?>