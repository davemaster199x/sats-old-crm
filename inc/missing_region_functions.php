<?php

function getMissingRegionProperty($start,$limit){
	
	if(is_numeric($start) && is_numeric($limit)){
		$str .= " LIMIT {$start}, {$limit}";
	}

	return mysql_query("
		SELECT p.`property_id`, p.`address_1`, p.`address_2`,  p.`address_3`, p.`state`, p.`postcode`, a.`agency_id` , a.`agency_name`
		FROM  `property` AS p
		LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
		LEFT JOIN  `postcode_regions` AS pr ON (
			pr.`postcode_region_postcodes` LIKE CONCAT(  '%', p.`postcode` ,  '%' ) AND 
			pr.`country_id` ={$_SESSION['country_default']} AND 
			pr.`deleted` = 0
		)
		WHERE p.`deleted` =0
		AND a.`status` =  'active'
		AND a.`country_id` ={$_SESSION['country_default']}
		AND pr.`postcode_region_id` IS NULL 
		{$str}
	");
	
}

function getPostCode(){
	$sql = mysql_query("
		SELECT *
		FROM `postcode_regions`
		WHERE `country_id` = {$_SESSION['country_default']}
	");
	$post_code = "";
	while($row = mysql_fetch_array($sql)){
		$post_code .= ",".$row['postcode_region_postcodes'];
	}
	return substr(str_replace(',,',',',$post_code),1);
}

?>