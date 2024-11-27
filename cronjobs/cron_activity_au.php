<?php

include('server_hardcoded_values.php');

include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');
include($_SERVER['DOCUMENT_ROOT'].'inc/activity_functions.php');

$country_id = 1;
$to_email = SALES_EMAIL;

function get_num_services($agency_id,$ajt,$from,$to,$country_id){
					
	$str = "";					
	if( $from!='all' && $to!='all' ){
		$from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
		$to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
		$str = "
			AND CAST(ps.`status_changed` AS DATE) BETWEEN '{$from2}' AND '{$to2}'
			AND (
				p.`is_nlm` IS NULL 
				OR p.`is_nlm` = 0
			)
		";
	}

	$sql_str = "
		SELECT COUNT(ps.`property_services_id`) AS ps_count
		FROM `property_services` AS ps
		LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE a.`agency_id` ={$agency_id}
		AND ps.`alarm_job_type_id` ={$ajt}
		AND ps.`service` = 1
		AND a.`country_id` = {$country_id}
		{$str}
	";

	$sql = mysql_query($sql_str);
	$row = mysql_fetch_array($sql);
	return $row['ps_count'];

}


function get_deleted($agency_id,$from,$to,$country_id){

	$str = "";
	if( $from!='all' && $to!='all' ){
		$from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
		$to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
		$str = "AND CAST(p.`deleted_date` AS DATE) BETWEEN '{$from2}' AND '{$to2}'";
	}

	$sql_str = "
		SELECT COUNT(ps.`property_services_id`) AS ps_count
		FROM `property_services` AS ps
		LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE a.`agency_id` ={$agency_id}
		AND p.`deleted` = 1
		AND ps.`service` = 1
		AND a.`country_id` = {$country_id}
		{$str}
	";

	$sql = mysql_query($sql_str);
	$row = mysql_fetch_array($sql);
	return $row['ps_count'];
}

function getAddedBySats($agency_id,$from,$to,$country_id){
	
	$str = "";					
	if( $from!='all' && $to!='all' ){
		$from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
		$to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
		$str = "
			AND CAST(ps.`status_changed` AS DATE) BETWEEN '{$from2}' AND '{$to2}'
			AND (
				p.`is_nlm` IS NULL 
				OR p.`is_nlm` = 0
			)
		";
	}

	$sql_str = "
		SELECT COUNT(ps.`property_services_id`) AS ps_count
		FROM `property_services` AS ps
		LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE a.`agency_id` ={$agency_id}
		AND ps.`service` = 1
		AND a.`country_id` = {$country_id}
		AND p.`added_by` > 0
		{$str}
	";

	$sql = mysql_query($sql_str);
	$row = mysql_fetch_array($sql);
	return $row['ps_count'];
	
}


function getAddedByAgency($agency_id,$from,$to,$country_id){
	
	$str = "";					
	if( $from!='all' && $to!='all' ){
		$from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
		$to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
		$str = "
			AND CAST(ps.`status_changed` AS DATE) BETWEEN '{$from2}' AND '{$to2}'
			AND (
				p.`is_nlm` IS NULL 
				OR p.`is_nlm` = 0
			)
		";
	}

	$sql_str = "
		SELECT COUNT(ps.`property_services_id`) AS ps_count
		FROM `property_services` AS ps
		LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE a.`agency_id` ={$agency_id}
		AND ps.`service` = 1
		AND a.`country_id` = {$country_id}
		AND p.`added_by` <= 0
		{$str}
	";

	$sql = mysql_query($sql_str);
	$row = mysql_fetch_array($sql);
	return $row['ps_count'];
	
}

function getDynamicServices(){
	return mysql_query("
		SELECT *
		FROM `alarm_job_type`
		WHERE `active` =1
	");
}

// cron variables
$cron_type_id = 7;
$current_week = intval(date('W'));
$current_year = date('Y');

// check if already run
$cl_sql = mysql_query("
	SELECT * 
	FROM cron_log 
	WHERE `type_id` = '{$cron_type_id}' 
	AND `week_no` = '{$current_week}' 
	AND `year` = '{$current_year}'
	AND CAST(`started` AS DATE) = '".date('Y-m-d')."' 
	AND `country_id` = {$country_id}
");


if(mysql_num_rows($cl_sql)==0){


// date range
$from = date("d/m/Y");
$to = date("d/m/Y");

// get country
$cntry_sql = getCountryViaCountryId($country_id);
$cntry = mysql_fetch_array($cntry_sql);

// subject
$subject = "Agent Activity ({$cntry['iso']}) for ".date('d/m/Y');


$body = '
<html>
<head>
  <title>'.$subject.'</title>
</head>
<table style="border: 1px solid; border-collapse: collapse;" id="jtable">
<tr bgcolor="#DDDDDD" style="border: 1px solid;">
	<th style="text-align: left; border: 1px solid;">Agency</th>';
	$ajt_sql2 = getDynamicServices();
	while($ajt2 = mysql_fetch_array($ajt_sql2)){ 
		$body .= '<th style="border: 1px solid;">'.$ajt2['type'].'</th>';
	}
	$body .= '
	<th style="border: 1px solid;">Added By SATS</th>
	<th style="border: 1px solid;">Added By Agency</th>
	<th style="border: 1px solid;">Total New</th>
	<th style="border: 1px solid;">Deleted</th>
	<th style="border: 1px solid;">Net</th>
</tr>';

$sr_sql = getActivity('','',$from,$to,$country_id);

$serv_tot = array();
$ctr = 0;
while($sr = mysql_fetch_array($sr_sql)){ 

$body .= '
<tr style="border: 1px solid;">';

	$body .= '
	<td style="text-align: left; border: 1px solid;">'.$sr['agency_name'].'</td>';
	
	$ajt_sql2 = getDynamicServices();
	$gross_tot = 0;
	$i = 0;
	while($ajt2 = mysql_fetch_array($ajt_sql2)){ 
	
		$body .= '<td style="border: 1px solid;">';
			$sa = get_num_services($sr['agency_id'],$ajt2['id'],$from,$to,$country_id); 
			if($sa>0){
				$body .= $sa;
			}
		$body .= '</td>';
	
		$gross_tot += $sa;
		$serv_tot[$i] += $sa;
		$i++;
		
	}		
	
	$body .= '<td style="border: 1px solid;">'; 
		$add_by_sats = getAddedBySats($sr['agency_id'],$from,$to,$country_id); 
		if($add_by_sats>0){
			$body .= $add_by_sats;
		}
	$body .= '</td>';
	
	$body .= '<td style="border: 1px solid;">';
		$add_by_agency = getAddedByAgency($sr['agency_id'],$from,$to,$country_id); 
		if($add_by_agency>0){
			$body .= $add_by_agency;
		}
	$body .= '</td>';
	
	$body .= '<td style="border: 1px solid;">';
		$gross_tot; 
		if($gross_tot>0){
			$body .= $gross_tot;
		}
	$body .= '</td>';
	
	$body .= '<td style="border: 1px solid;">';
		$deleted = get_deleted($sr['agency_id'],$from,$to,$country_id,$country_id);
		if($deleted>0){
			$body .= $deleted;
		}
	$body .= '</td>';
	
	$body .= '<td>';
		$net = ($gross_tot-$deleted); 
		$body .= ($net<0)?'<span style="color:red">'.$net.'</span>':$net;
	$body .= '</td>';
	
	$add_by_sats_tot += $add_by_sats;
	$add_by_agency_tot += $add_by_agency;
	$gross_tot_tot += $gross_tot;
	$deleted_tot += $deleted;
	$sats_del_tot += $sats_del;
	$net_total_tot += $net;	
	
$body .= '</tr>
';

$ctr++;
}

$body .= '
<tr bgcolor="#DDDDDD" style="border: 1px solid;">
	<td style="text-align: left; border: 1px solid;"><strong>TOTAL</strong></td>';
	foreach($serv_tot as $val){ 
		$body .= '<td style="border: 1px solid;">'.(($val>0)?$val:'').'</td>';
	}
	$body .= '
	<td style="border: 1px solid;">'.(($add_by_sats_tot>0)?$add_by_sats_tot:'').' ('.(($add_by_sats_tot>0)?number_format((($add_by_sats_tot/$gross_tot_tot)*100), 2, '.', '').'%':'').')</td>
	<td style="border: 1px solid;">'.(($add_by_agency_tot>0)?$add_by_agency_tot:'').' ('.(($add_by_sats_tot>0)?number_format((($add_by_agency_tot/$gross_tot_tot)*100), 2, '.', '').'%':'').')</td>
	<td style="border: 1px solid;">'.(($gross_tot_tot>0)?$gross_tot_tot:'').'</td>
	<td style="border: 1px solid;">'.(($deleted_tot>0)?$deleted_tot:'').'</td>
	<td style="border: 1px solid;">'.(($net_total_tot>0)?$net_total_tot:'').'</td>
</tr>
</table>
</html>
';


echo $body;



// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= "From: SATS - Smoke Alarm Testing Services <{$cntry['outgoing_email']}>" . "\r\n";

mail($to_email, $subject, $body, $headers);



// insert cron logs
mysql_query("INSERT INTO cron_log (`type_id`, `week_no`, `year`, `started`, `finished`, `country_id`) VALUES (" . $cron_type_id . "," . $current_week . ", " . $current_year . ", NOW(), NOW(), {$country_id})");


}