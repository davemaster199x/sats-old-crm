<?php

include('inc/init.php');

// file name
$filename = "Export_Sales_Snapshot".date("d/m/Y").".csv";

// send headers for download
header("Content-Type: text/csv");
header("Content-Disposition: Attachment; filename={$filename}");
header("Pragma: no-cache");

$sql = mysql_query("
	SELECT *, ss_s.`name` AS status_name, ss.`sales_snapshot_status_id` AS ss_status_id, a.`agency_name`, pr.`postcode_region_name`
				FROM `sales_snapshot` AS ss
				LEFT JOIN `agency` AS a ON ss.`agency_id` = a.`agency_id`
				LEFT JOIN `postcode_regions` AS pr ON a.`postcode_region_id` = pr.`postcode_region_id`
				LEFT JOIN `sales_snapshot_status` AS ss_s ON ss.`sales_snapshot_status_id` = ss_s.`sales_snapshot_status_id`
				LEFT JOIN `sales_snapshot_sales_rep` AS ss_sr ON ss.`sales_snapshot_sales_rep_id` = ss_sr.`sales_snapshot_sales_rep_id`
				WHERE ss.`country_id` = {$_SESSION['country_default']}
");
echo "Date,Sales Rep,Agency,Properties,".getDynamicRegionViaCountry($_SESSION['country_default']).",Status,Details \n";
if(mysql_num_rows($sql)>0){
	$total = 0;
	while($row = mysql_fetch_array($sql)){
		echo "\"".(($row['date']!="")?date("d/m/Y",strtotime($row['date'])):'')."\",\"{$row['first_name']} {$row['last_name']}\",\"{$row['agency_name']}\",\"{$row['properties']}\",\"{$row['postcode_region_name']}\",\"{$row['status_name']}\",\"{$row['details']}\", \n";
	}
}
	


?>