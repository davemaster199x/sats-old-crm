<?php

include('inc/init_for_ajax.php');

$agency_id = mysql_real_escape_string($_POST['agency_id']);

$sql_txt = "
	SELECT `franchise_groups_id`
	FROM `agency`
	WHERE `agency_id` = {$agency_id}
";
$sql = mysql_query($sql_txt);

$row = mysql_fetch_array($sql);
echo $row['franchise_groups_id'];

//header("Location: /tech_stock.php?ts_sub=1");

?>
