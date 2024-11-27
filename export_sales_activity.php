<?php

include('inc/init.php');

$user_type = $_SESSION['USER_DETAILS']['ClassID'];
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$country_id = $_SESSION['country_default'];

$crm = new Sats_Crm_Class;


$from_date = ($_REQUEST['from']!='')?mysql_real_escape_string($_REQUEST['from']):'';
if($from_date!=''){
	$from_date2 = $crm->formatDate($from_date);
}
$to_date = ($_REQUEST['to']!='')?mysql_real_escape_string($_REQUEST['to']):'';
if($to_date!=''){
	$to_date2 = $crm->formatDate($to_date);
}

$state = $_GET['state'];
$salesrep = $_GET['salesrep'];

if( $salesrep!='' && isset($salesrep) ){		
		$filter .= " AND ael.`staff_id` = {$salesrep} ";
	}
	
if( $state!='' && isset($state) ){		
    $filter .= " AND a.`state` = '{$state}' ";
}
	


// file name
$filename = "sales_activity_".date("d/m/Y").".csv";

// send headers for download
header("Content-Type: text/csv");
header("Content-Disposition: Attachment; filename={$filename}");
header("Pragma: no-cache");

// headers
echo "Date,Sales Rep,Type,Agency,Status,Comment,Next Contact\n";

	
    $salesActivity_sql = mysql_query("
			SELECT *, ael.`comments` AS ael_comments, a.`status` AS a_status
            FROM `agency_event_log` AS ael
            LEFT JOIN `agency` AS a ON ael.`agency_id` = a.`agency_id`
            LEFT JOIN `staff_accounts` AS sa ON ael.`staff_id` = sa.`StaffID`
            WHERE sa.deleted =0
            AND sa.active =1
            AND (
				sa.`ClassID` = 5 OR
				sa.`StaffID` = 2165 OR
				sa.`StaffID` = 2189 OR
                sa.`StaffID` = 2296
			)
            AND ael.`contact_type` !=  'Agency Update'
            AND a.`country_id` ={$country_id} 
            AND ael.`eventdate` BETWEEN '{$from_date2}' AND '{$to_date2}'
            {$filter}
		");

// body
while($row=mysql_fetch_array($salesActivity_sql)){
    
    
    $date = $row['eventdate'];
    $salesRep = $row['FirstName'].' '.$row['LastName'];
    $type = $row['contact_type'];
    $agency = $row['agency_name'];
    $status = $row['a_status'];
    $comment = $row['ael_comments'];
    $nextContact = ( $row['next_contact']!='0000-00-00' && $row['next_contact']!='' && $row['next_contact']!='1970-01-01' )?date('d/m/Y',strtotime($row['next_contact'])):'';
	
	echo "\"{$date}\",\"{$salesRep}\",\"{$type}\",\"{$agency}\",\"{$status}\",\"{$comment}\",\"{$nextContact}\"\n";
}

?>