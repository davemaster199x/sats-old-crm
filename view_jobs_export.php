<?php

include ('inc/init.php');

$status = $_GET['status'];

$filter_job_type = $_GET['filter_job_type'];
$filter_service = $_GET['filter_service'];
$filter_state = $_GET['filter_state'];
$filterdate = $_GET['filterdate'];
$filter_from_date = $_GET['filter_from_date'];
$filter_to_date = $_GET['filter_to_date'];
$filter_tech = $_GET['filter_tech'];
$filterbyagency = $_GET['agencyid'];

// (1) Open the database connection

//init variable

$rownum = 0;

$techcomment = "";

$istatus = "";


function getTechnician($tech_id){
	$sql = mysql_query("
		SELECT `FirstName`, `LastName`
		FROM `staff_accounts`
		WHERE `StaffID` = {$tech_id}
	");
	$row = mysql_fetch_array($sql);
	return "{$row['FirstName']} {$row['LastName']}";
}

switch($status) {

	case "tobebooked" :
		$istatus = "To Be Booked";

		$fn = "To_Be_Booked";

		break;

	case "sendletters" :
		$istatus = "Send Letters";

		$fn = "SendLetters";

		break;

	case "booked" :
		$istatus = "Booked";

		$fn = $istatus;

		break;

	case "merged" :
		$istatus = "Merged Certificates";

		$fn = "Merged_Certificates";

		break;

	case "cancelled" :
		$istatus = "Cancelled";

		$fn = $istatus;

		break;

	case "completed" :
		$istatus = "Completed";

		$fn = $istatus;

		break;

	case "precompleted" :
		$istatus = "Pre Completion";

		$fn = $istatus;

		break;
		
	case "pending" :
		$istatus = "Pending";

		$fn = $istatus;

		break;	
		
	case "escalate" :
		$istatus = "Escalate";

		$fn = $istatus;

		break;	

	case "" :
		$istatus = "";

		$fn = "All";

		break;
}

// send headers for download

$filename = "Jobs_" . $fn . "_" . date("d") . "-" . date("m") . "-" . date("y") . ".csv";

$filter = " AND a.agency_id=p.agency_id AND p.deleted = 0 ";

if ($filter_job_type != "") {

	$filter .= " AND j.`job_type` = '{$filter_job_type}' ";

}

if ($filter_service != "") {

	$filter .= " AND j.`service` = '{$filter_service}' ";

}

if ($filter_state != "") {

	$filter .= " AND p.`state` = '{$filter_state}' ";

}


if ($filterdate != "") {

	$filter .= " AND j.date='$filterdate'";

}

if ($filter_tech != "") {

	$filter .= " AND j.assigned_tech='{$filter_tech}'";

}

if ( $filter_from_date != "" && $filter_to_date != "" ) {

	$filter .= " AND j.`date` BETWEEN '{$filter_from_date}' AND '{$filter_to_date}' ";

}

if ($filterbyagency != "") {

	//echo "Agency filter by ID: ".$filterbyagency."\n";

	$filter .= "  AND a.agency_id='$filterbyagency'";

}


// Adam Edit - Add Search files.. note I did not code this whole script :(
$fields_to_search = array('j.id', 'j.comments', 'j.tech_comments', 'a.agency_name');
$search_params = trim(strtolower($_GET['search']));
$search_params = str_replace(" ", "%", $search_params);

if ($search_params != "") {
	$filter_tmp .= " AND (";

	# Agency address search
	$filter_tmp .= " (CONCAT_WS(' ', LOWER(a.address_1), LOWER(a.address_2), LOWER(a.address_3), LOWER(a.state), LOWER(a.postcode)) LIKE '%{$search_params}%') OR ";

	# Property address search
	$filter_tmp .= " (CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$search_params}%') OR ";

	foreach ($fields_to_search as $field) {
		$filter_tmp .= " LOWER(" . $field . ") LIKE '%" . $search_params . "%' OR ";
	}

	$filter_tmp .= " )";
	$filter_tmp = str_replace("OR  )", " )", $filter_tmp);

	$filter .= $filter_tmp;
}

if ($istatus == "") {

	// echo "executing with no status\n\n";

	$Query = "SELECT p.property_id, j.job_type, j.service, j.date, j.status, p.address_1, p.address_2, p.address_3, p.state, p.postcode, j.id, p.tenant_firstname1, p.tenant_lastname1, p.tenant_firstname2, p.tenant_lastname2, p.inv_number, j.tech_comments, p.tenant_ph1, p.tenant_ph2, a.agency_name, p.landlord_firstname, landlord_lastname FROM jobs j, property p, agency a

 WHERE (p.agency_id = a.agency_id AND j.property_id = p.property_id" . $filter . ") AND a.`country_id` = {$_SESSION['country_default']} AND j.`del_job` = 0 ORDER BY j.job_type;";


} elseif ($istatus == "To Be Booked") {

	$Query = "SELECT p.property_id, j.job_type, j.service, j.date, j.status, p.retest_date, p.address_1, p.address_2, p.address_3, p.state, p.postcode, j.id, p.landlord_firstname, landlord_lastname, p.tenant_firstname1, p.tenant_lastname1, p.tenant_firstname2, p.tenant_lastname2, p.inv_number, j.tech_comments, p.tenant_ph1, p.tenant_ph2, a.agency_name FROM jobs j, property p, agency a

  WHERE (p.agency_id = a.agency_id AND j.property_id = p.property_id AND j.status = 'To Be Booked' " . $filter . ") AND a.`country_id` = {$_SESSION['country_default']} AND j.`del_job` = 0 ORDER BY j.job_type, p.address_3;";

	//$Query = "SELECT j.job_type, j.date, j.status, p.address_1, p.address_2, p.address_3, p.state, p.postcode, j.id, p.property_id FROM jobs j, property p, agency a WHERE (j.property_id = p.property_id AND j.status = 'to be booked' AND a.agency_id=p.agency_id AND a.agency_id=".$filter.") ORDER BY j.job_type, p.address_3;";

} elseif ($istatus == "Pending") {

	$Query = "SELECT p.property_id, j.job_type, j.service, a.agency_name, p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.tenant_firstname1, p.tenant_lastname1, p.tenant_ph1, p.tenant_firstname2, p.tenant_lastname2, p.tenant_ph2 FROM jobs j, property p, agency a

 WHERE  (p.agency_id = a.agency_id AND j.property_id = p.property_id AND j.status = 'Pending' " . $filter . ")  AND a.`country_id` = {$_SESSION['country_default']} AND j.`del_job` = 0 ORDER BY j.job_type, p.address_3;";

} else {

	//	echo "executing WITH status $status\n\n";

	// improved query
	$Query = "SELECT 
	p.property_id, j.job_type, j.service, j.date, j.status, p.address_1, p.address_2, p.address_3, p.state, p.postcode, j.id,
	 p.landlord_firstname, landlord_lastname, p.tenant_firstname1, p.tenant_lastname1, p.tenant_firstname2, p.tenant_lastname2, p.inv_number, 
	 j.tech_comments, p.tenant_ph1, p.tenant_ph2, a.agency_name, p.a1_type, p.a1_pwr, p.a1_exp, p.a2_type, p.a2_pwr, p.a2_exp, p.a3_type,
	  p.a3_pwr, p.a3_exp, p.a4_type, p.a4_pwr, p.a4_exp, p.a5_type, p.a5_pwr, p.a5_exp, p.a6_type, p.a6_pwr, p.a6_exp, p.landlord_firstname, 
	  landlord_lastname, a.`agency_id` AS agen_id, j.`created` AS jcreated, j.`comments` AS jcomments, j.assigned_tech 
	FROM jobs AS j
	LEFT JOIN property AS p ON j.property_id = p.property_id
	LEFT JOIN agency AS a ON p.agency_id = a.agency_id
	WHERE  j.status = '{$istatus}'
	{$filter}
	AND a.`country_id` = {$_SESSION['country_default']} 
	AND j.`del_job` = 0;";

}

$result = mysql_query($Query, $connection);

if (mysql_num_rows($result) == 0) {

	echo "<br><br>No Jobs to display of Status: $istatus.<br><br><br>\n";

}



header("Content-Type: text/csv");
header("Content-Disposition: Attachment; filename=$filename");
header("Pragma: no-cache");

$odd = 0;

// print the header

if ($istatus == "" || $istatus == "Completed" || $istatus == "Pre Completion") {

	echo "Invoice No, Invoice Amount, Test Date,Job Type,Service Type,Address,Suburb,State,Postcode,Landlord FirstName,Landlord LastName,Tenant1 FirstName,Tenant1 LastName,Tenant1 Ph,&,Tenant2 Firstname,Tenant2 Lastname,Tenant2 Ph,&,Tenant3 Firstname,Tenant3 Lastname,Tenant3 Ph,&,Tenant4 Firstname,Tenant4 Lastname,Tenant4 Ph,Tech Comments,Agency Name,Technician\n";

} else if ($istatus == "To Be Booked") {

	echo "Job Type,Service Type,Retest date,Address,Suburb,State,Postcode,Landlord FirstName,Landlord LastName,Tenant1 FirstName,Tenant1 LastName,Tenant1 Ph,&,Tenant2 Firstname,Tenant2 Lastname,Tenant2 Ph,&,Tenant3 Firstname,Tenant3 Lastname,Tenant3 Ph,&,Tenant4 Firstname,Tenant4 Lastname,Tenant4 Ph,Tech Comments,Agency Name\n";

} else if ($istatus == "Booked") {

	echo "Job Type,Service Type,Address,Suburb,State,Postcode,Landlord FirstName,Landlord LastName,Tenant1 FirstName,Tenant1 LastName,Tenant1 Ph,&,Tenant2 Firstname,Tenant2 Lastname,Tenant2 Ph,&,Tenant3 Firstname,Tenant3 Lastname,Tenant3 Ph,&,Tenant4 Firstname,Tenant4 Lastname,Tenant4 Ph,Tech Comments,Agency Name\n";

} else if ($istatus == "Merged Certificates") {

	echo "Test Date,Job Type,Service Type,Address,Suburb,State,Postcode,Agency Name,Account Emails\n";


} else if ($istatus == "Send Letters" ) {

	echo "Job Type,Service Type,Address,Suburb,State,Country,Postcode,Tenant1 FirstName,Tenant1 LastName,&,Tenant2 Firstname,Tenant2 Lastname,&,Tenant3 Firstname,Tenant3 Lastname,&,Tenant4 Firstname,Tenant4 Lastname,Agency Name\n";

} else if ( $istatus == "Escalate" ) {

	echo "Job Type,Service Type,Address,Suburb,State,Country,Postcode,Tenant1 FirstName,Tenant1 LastName,&,Tenant2 Firstname,Tenant2 Lastname,&,Tenant3 Firstname,Tenant3 Lastname,&,Tenant4 Firstname,Tenant4 Lastname,Agency Name,Job Comments,Created Date,Reason\n";

} else if ($istatus == "Pending") {

	echo "Job Type,Service Type,Address,Suburb,State,Postcode,Tenant1 FirstName,Tenant1 LastName,Tenant1 Ph,&,Tenant2 Firstname,Tenant2 Lastname,Tenant2 Ph,&,Tenant3 Firstname,Tenant3 Lastname,Tenant3 Ph,&,Tenant4 Firstname,Tenant4 Lastname,Tenant4 Ph,Agency Name,\n";

}

// (3) While there are still rows in the result set,

// fetch the current row into the array $row

//while ($row = mysql_fetch_row($result))

while ($row = mysql_fetch_array($result)) {

	// (4) Print out each element in $row, that is,

	// print the values of the attributes

	// cycle through the rows and see if there's a CR in there.

	for ($rownum = 0; $rownum < mysql_num_rows($result) + 1; $rownum++) {

		if (!empty($row[$rownum])) {

			$row[$rownum] = rtrim($row[$rownum]);

			//str_replace('\n'," ",$row[$rownum]);

		}

	}


	$techcomment = isset($row[16]) ? trim($row[16]) : "";

	if (isset($row['tech_comments'])) {

		$row['tech_comments'] = str_replace("\n", " ", $row['tech_comments']);

		$row['tech_comments'] = str_replace("\r", " ", $row['tech_comments']);

	}
	

	
	$serv_sql2 = mysql_query("
		SELECT *
		FROM `alarm_job_type`
		WHERE `id` = {$row['service']}
	");
	$serv2 = mysql_fetch_array($serv_sql2);
	
	if($serv2['bundle']==0){
		$service = $serv2['type'];
	}else{
		$serv_sql3 = mysql_query("
			SELECT *
			FROM `alarm_job_type`
			WHERE `id` IN ({$serv2['bundle_ids']})
		");	
		$serv3_str	= "";
		while($serv3 = mysql_fetch_array($serv_sql3)){
			$serv3_str .= ", {$serv3['type']}";
		}
		$service = '"'.substr($serv3_str,2).'"';
	}
	
	// new tenants
	$pt_params = array( 
		'property_id' => $row['property_id'],
		'active' => 1,
		'paginate' => array(
			'offset' => 0,
			'limit' => 4
		)
	 );
	$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
	$new_tent_fname_arr = [];
	$new_tent_lname_arr = [];
	$new_tent_landline_arr = [];
	while( $pt_row = mysql_fetch_array($pt_sql) ){
	
		$new_tent_fname_arr[] = trim($pt_row['tenant_firstname']);
		$new_tent_lname_arr[] = trim($pt_row['tenant_lastname']);
		$new_tent_landline_arr[] =  trim($pt_row['tenant_landline']);
		
	}
	// only display 4 tenants, to have consistent number of columns
	$num_tenants = 4; //set tenants max columns



	if ($istatus == "" || $istatus == "Completed"|| $istatus == "Pre Completion") {
	
			// get invoice number
		   if(isset($row['tmh_id']))
			{
				$invoice_num = $row['tmh_id'];
			}
			else
			{
				$invoice_num = $row['id'];
			}
			
			// get job price
			$j_sql = mysql_query("
				SELECT *
				FROM `jobs`
				WHERE `id`  = {$row['id']}	
			");
			$j = mysql_fetch_array($j_sql);
			$grand_total = $j['job_price'];
	
			// get alarms
			$a_sql = mysql_query("
				SELECT *
				FROM `alarm`
				WHERE `job_id`  = {$row['id']}	
			");
			while($a = mysql_fetch_array($a_sql))
			{		
				if($a['new']==1){
					$grand_total += $a['alarm_price'];
				}				
			}

			


		echo "{$invoice_num},$".number_format($grand_total, 2).",{$row['date']},{$row['job_type']},{$service},{$row['address_1']} {$row['address_2']},{$row['address_3']},{$row['state']},{$row['postcode']},{$row['landlord_firstname']},{$row['landlord_lastname']},";
		for( $pt_i=0; $pt_i<$num_tenants; $pt_i++ ){ //new tenants
			echo '"'.$new_tent_fname_arr[$pt_i].'",';
			echo '"'.$new_tent_lname_arr[$pt_i].'",';
			echo '"'.$new_tent_landline_arr[$pt_i].'",';
			if($pt_i<=2){
				echo "&,";
			}
			
		}
		
		$tech_name = getTechnician($row['assigned_tech']);
		
		echo "{$row['tech_comments']},{$row['agency_name']},\"{$tech_name}\"\n";

	} else if ($istatus == "To Be Booked") {

		echo "{$row['job_type']},{$service},{$row['retest_date']},{$row['address_1']} {$row['address_2']},{$row['address_3']},{$row['state']},{$row['postcode']},{$row['landlord_firstname']},{$row['landlord_lastname']},";
		for( $pt_i=0; $pt_i<$num_tenants; $pt_i++ ){ //new tenants
			echo '"'.$new_tent_fname_arr[$pt_i].'",';
			echo '"'.$new_tent_lname_arr[$pt_i].'",';
			echo '"'.$new_tent_landline_arr[$pt_i].'",';
			if($pt_i<=2){
				echo "&,";
			}
			
		}
		
		echo "{$row['tech_comments']},{$row['agency_name']}\n";
		//less alarm details total 18 fields

	} else if ($istatus == "Booked") {

		echo "{$row['job_type']},{$service},{$row['address_1']} {$row['address_2']},{$row['address_3']},{$row['state']},{$row['postcode']},{$row['landlord_firstname']},{$row['landlord_lastname']},";
		for( $pt_i=0; $pt_i<$num_tenants; $pt_i++ ){ //new tenants
			echo '"'.$new_tent_fname_arr[$pt_i].'",';
			echo '"'.$new_tent_lname_arr[$pt_i].'",';
			echo '"'.$new_tent_landline_arr[$pt_i].'",';
			if($pt_i<=2){
				echo "&,";
			}
			
		}
		echo "{$row['tech_comments']},{$row['agency_name']}\n";
		//less alarm details total 18 fields

	} else if ($istatus == "Merged Certificates") {
	
		// get account email
		$ae_esql = mysql_query("
			SELECT `account_emails`
			FROM `agency`
			WHERE `agency_id` = {$row['agen_id']}
		");
		$ae = mysql_fetch_array($ae_esql);
		
		$account_emails = str_replace("\n",",",$ae['account_emails']);

		echo "{$row['date']},{$row['job_type']},{$service},{$row['address_1']} {$row['address_2']},{$row['address_3']},{$row['state']},{$row['postcode']},{$row['agency_name']},\"{$account_emails}\"\n";

	} else if ( $istatus == "Send Letters" ) {  // dont know what is the export column of escalate so i'll just you send letter 
		
		$c_sql = mysql_query("
			SELECT `country` 
			FROM  `countries`
			WHERE `country_id` = {$_SESSION['country_default']}
		");
		$c = mysql_fetch_array($c_sql);

		echo "{$row['job_type']},{$service},{$row['address_1']} {$row['address_2']},{$row['address_3']},{$row['state']},{$c['country']},{$row['postcode']},";
		for( $pt_i=0; $pt_i<$num_tenants; $pt_i++ ){ //new tenants
			echo '"'.$new_tent_fname_arr[$pt_i].'",';
			echo '"'.$new_tent_lname_arr[$pt_i].'",';
			if($pt_i<=2){
				echo "&,";
			}
			
		}
		echo "{$row['agency_name']}\n";

	} else if ( $istatus == "Escalate" ) {  
		
		$c_sql = mysql_query("
			SELECT `country` 
			FROM  `countries`
			WHERE `country_id` = {$_SESSION['country_default']}
		");
		$c = mysql_fetch_array($c_sql);

		echo "{$row['job_type']},{$service},{$row['address_1']} {$row['address_2']},{$row['address_3']},{$row['state']},{$c['country']},{$row['postcode']},";
		for( $pt_i=0; $pt_i<$num_tenants; $pt_i++ ){ //new tenants
			echo '"'.$new_tent_fname_arr[$pt_i].'",';
			echo '"'.$new_tent_lname_arr[$pt_i].'",';
			if($pt_i<=2){
				echo "&,";
			}
			
		}

		// display escalate job reasons
		$sel_esc_str = "
		SELECT `reason`
		FROM `selected_escalate_job_reasons` AS sejr
		LEFT JOIN `escalate_job_reasons` AS ejr ON sejr.`escalate_job_reasons_id` = ejr.`escalate_job_reasons_id`
		WHERE sejr.`deleted` = 0
		AND sejr.`active` = 1
		AND sejr.`job_id` = {$row['id']}
		";
		$sel_esc_job_sql = mysql_query($sel_esc_str);
		$sel_esc_job = mysql_fetch_array($sel_esc_job_sql);
		
		echo "{$row['agency_name']},\"{$row['jcomments']}\",\"".date('d/m/Y',strtotime($row['jcreated']))."\",\"".$sel_esc_job['reason']."\"\n";
	} else if ($istatus == "Pending") {

		echo "{$row['job_type']},{$service},{$row['address_1']} {$row['address_2']},{$row['address_3']},{$row['state']},{$row['postcode']},";
		for( $pt_i=0; $pt_i<$num_tenants; $pt_i++ ){ //new tenants
			echo '"'.$new_tent_fname_arr[$pt_i].'",';
			echo '"'.$new_tent_lname_arr[$pt_i].'",';
			echo '"'.$new_tent_landline_arr[$pt_i].'",';
			if($pt_i<=2){
				echo "&,";
			}
			
		}
		echo "{$row['agency_name']}\n";

	}

}



/*

 // Print out the Unique Agencies.

 if ($status == "booked")

 {

 $query4 = "SELECT DISTINCT a.agency_name, a.address_1, a.address_2, a.address_3 FROM jobs j, property p, agency a

 WHERE (p.agency_id = a.agency_id AND j.property_id = p.property_id AND j.status = '$istatus'".$filter.");";

 // echo "\nThe Query is: $query4\n\n";

 $connection4 = mysql_connect("localhost","satsuser","dell123");

 mysql_select_db("sats", $connection4);

 // (2) Run the query.

 $result4 = mysql_query($query4, $connection4);

 if (mysql_num_rows($result4) == 0)

 {

 echo "<br><br>No Jobs to display of Status: $istatus.<br><br><br>\n";

 }

 while ($row4 = mysql_fetch_row($result4))

 {

 echo "$row4[0],$row4[1],$row4[2],$row4[3]\n";

 }

 }	 // if

 */

// (5) Close the database connection

//   mysql_close($connection4);
?>