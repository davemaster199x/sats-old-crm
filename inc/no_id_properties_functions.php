<?php

function getPropertyNoAgency($start,$limit){

	$str = "";

	if(is_numeric($start) && is_numeric($limit))
	{
		$str .= " LIMIT {$start}, {$limit}";
	}

	return mysql_query("
		SELECT *
		FROM `property`
		WHERE `agency_id` =0
		ORDER BY `tenant_ltr_sent` ASC
		{$str}
	");

}

?>