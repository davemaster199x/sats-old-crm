<?php

include ('inc/init.php');

$filterdate = $_GET['filterdate'];

$filterbyagency = $_GET['agencyid'];

// (1) Open the database connection

//init variable

$rownum = 0;

$techcomment = "";

$istatus = "Pending";

$fn = $istatus;

// send headers for download

$filename = "Jobs_" . $fn . "_" . date("d") . "-" . date("m") . "-" . date("y") . ".csv";

$filter = " AND a.agency_id=p.agency_id AND p.deleted = 0 ";

if ($filterdate != "") {
	
	$date = date("Y-m-d",strtotime(str_replace("/","-",$_GET['filterdate'])));

	$filter = " AND j.date='{$date}'";

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

$Query = "SELECT p.property_id, j.job_type, j.service, a.agency_name, p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.tenant_firstname1, p.tenant_lastname1, p.tenant_ph1, p.tenant_firstname2, p.tenant_lastname2, p.tenant_ph2, j.`created` FROM jobs j, property p, agency a

WHERE  (p.agency_id = a.agency_id AND j.property_id = p.property_id AND j.status = 'Pending' " . $filter . ")  AND a.`country_id` = {$_SESSION['country_default']} AND p.`deleted` =0 AND a.`status` = 'active' AND j.`del_job` = 0 ORDER BY j.job_type, p.address_3;";

$result = mysql_query($Query, $connection);

if (mysql_num_rows($result) == 0) {

	echo "<br><br>No Jobs to display of Status: $istatus.<br><br><br>\n";

}



header("Content-Type: text/csv");
header("Content-Disposition: Attachment; filename=$filename");
header("Pragma: no-cache");

$odd = 0;

// print the header

echo "Job Type,Service Type,Address,Suburb,State,Postcode,Tenant1 FirstName,Tenant1 LastName,Tenant1 Ph,&,Tenant2 Firstname,Tenant2 Lastname,Tenant2 Ph,&,Tenant3 Firstname,Tenant3 Lastname,Tenant3 Ph,&,Tenant4 Firstname,Tenant4 Lastname,Tenant4 Ph,Agency Name,\"Job Created Date\"\n";

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



	//get new tenants
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

	
	//print tenants info each relevant columns
	echo "{$row['job_type']},{$service},\"{$row['address_1']} {$row['address_2']}\",\"{$row['address_3']}\",{$row['state']},{$row['postcode']},";
	for( $pt_i=0; $pt_i<$num_tenants; $pt_i++ ){ //new tenants
		echo '"'.$new_tent_fname_arr[$pt_i].'",';
		echo '"'.$new_tent_lname_arr[$pt_i].'",';
		echo '"'.$new_tent_landline_arr[$pt_i].'",';
		if($pt_i<=2){
			echo "&,";
		}
		
	}
	echo "{$row['agency_name']},\"".(($row['created']!='')?date('d/m/Y',strtotime($row['created'])):'')."\"\n";
}




?>