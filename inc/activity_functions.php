<?php

function getActivity($start,$limit,$from,$to,$country_id='',$state){
	
	$country_id = ($country_id!="")?$country_id:$_SESSION['country_default'];

	$str = "";					
	if( $from!='all' && $to!='all' ){
		$from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
		$to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
		$str = " AND CAST(ps.`status_changed` AS DATE) BETWEEN '{$from2}' AND '{$to2}' ";
	}
	
	if($phrase != "")
	{
		$str .= " AND (";
			# Agency address search	
			$str .= " (CONCAT_WS(' ', LOWER(a.address_1), LOWER(a.address_2), LOWER(a.address_3), LOWER(a.state), LOWER(a.postcode)) LIKE '%{$phrase}%') OR ";
			# Property address search
			$str .= " (CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$phrase}%')";
		$str .= " ) ";
	}
	
	if($state != "")
	{
		$str .= " AND a.`state` = '{$state}' ";
	}

	$str .= " ORDER BY a.`agency_name` ASC ";

	if(is_numeric($start) && is_numeric($limit))
	{
		$str .= " LIMIT {$start}, {$limit}";
	}
	
	$sql = "
		SELECT DISTINCT(a.`agency_id`), a.`agency_name`, a.`state`
		FROM `property_services` AS ps
		LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE ps.`service` = 1
		AND a.`country_id` = {$country_id}
		{$str}
	";
	
	return mysql_query($sql);

}


?>