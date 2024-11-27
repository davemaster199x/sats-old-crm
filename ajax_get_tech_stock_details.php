<?php

include('inc/init_for_ajax.php');

$vehicle = $_POST['vehicle'];
$country_id = $_POST['country_id'];

$sql_txt = "
	SELECT *
	FROM `tech_stock`
	WHERE `vehicle` = {$vehicle}
	AND `status` = 1
	AND `country_id` = {$country_id}
	ORDER BY `tech_stock_id` DESC
	LIMIT 1
";
$sql = mysql_query($sql_txt);

$row = mysql_fetch_array($sql);
echo json_encode($row)

//header("Location: /tech_stock.php?ts_sub=1");

?>
