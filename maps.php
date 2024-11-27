<?php

$title = "Maps";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

// data
$tech_id = $_GET['tech_id'];
$day = $_GET['day'];
$month = $_GET['month'];
$year = $_GET['year'];
$date = "{$year}-{$month}-{$day}";


// check for property that has no lat lng coordinate
$sql = "
	SELECT j.`id`, j.`property_id`, p.`address_1`, p.`address_2`, p.`address_3`, p.`state`, p.`postcode`
	FROM jobs AS j
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	WHERE j.`assigned_tech` ={$tech_id}
	AND j.date = '".$date."'
	AND p.deleted =0
	AND a.`status` = 'active'
	AND j.`del_job` = 0
	AND p.`lat` IS NULL
	AND p.`lng` IS NULL
	AND a.`country_id` = {$_SESSION['country_default']}
";
$result = mysql_query($sql);

if(mysql_num_rows($result)>0){
	
	$ctr = 2;
	while($row = mysql_fetch_array($result)){
		
		
		echo $address = "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']} {$row['postcode']}, {$_SESSION['country_name']}";
		$coordinate = getGoogleMapCoordinates($address);
		
		echo " ---- lat: {$coordinate['lat']} lng {$coordinate['lng']}<br />";

		// update lat lng
		mysql_query("
			UPDATE `property`
			SET `lat` = {$coordinate['lat']},
				`lng` = {$coordinate['lng']}
			WHERE `property_id` = {$row['property_id']}
		");
		
		
	}
	
	//echo "has data";
	echo "<script>window.location='/maps.php?tech_id={$tech_id}&day={$day}&month={$month}&year={$year}';</script>";
	
}

// REMOVE
if($_POST['remove_flag']==1){
	
	
	$map_id = $_POST['map_id'];
	$del_map_route = $_POST['del_map_route'];
	
	$j = 2;
	foreach($map_id as $val){
		
		$temp = explode(":",$val);
		$map_type = $temp[0];
		$id = $temp[1];
		
		
		
		// deleted
		if(in_array($val, $del_map_route)){
			
			// jobs
			if($map_type=="job_id"){
				$sql = "
					UPDATE `jobs`
					SET 
						`status` = 'To Be Booked',
						`date` = NULL,
						`time_of_day` = NULL,
						`assigned_tech` = NULL,
						`ts_completed` = 0,
						`job_reason_id` = 0,
						`door_knock` = 0,
						`completed_timestamp` = NULL,
						`tech_notes` = NULL,
						`sort_order` = NULL
					WHERE `id` = {$id}
				";
				//echo "<br />";
				mysql_query($sql);
			}else{ // keys
				$sql = "
					UPDATE `key_routes`
					SET `deleted` = 1
					WHERE `key_routes_id` = {$id}
				";
				//echo "<br />";
				mysql_query($sql);
			}
			
		}else{
			
			if($map_type=="job_id"){
			
				$sql = "
					UPDATE `jobs`
					SET `sort_order` = {$j}
					WHERE `id` = {$id}
				";
				//echo "<br />";
				mysql_query($sql);
				
			}else{
				
				$sql = "
					UPDATE `key_routes`
					SET `sort_order` = {$j}
					WHERE `key_routes_id` = {$id}
				";
				//echo "<br />";
				mysql_query($sql);
			}
			
			$j++;
			
		}
		
		
		
	}
	
	$delete_mp = 1;
	
	

}

// run sort on firt load
$mp_sql = mysql_query("
	SELECT *
	FROM `map_routes`
	WHERE `tech_id` = {$tech_id}
	AND `date` = '{$date}'
	AND `sorted` = 1
");

if(mysql_num_rows($mp_sql)==0){
	
	
	manualSortJobBySortOrder('ASC',$tech_id,$date);
	
	
	// add map route
	$sql = "
		INSERT INTO
		`map_routes` (
			`tech_id`,
			`date`,
			`sorted`
		)
		VALUES(
			{$tech_id},
			'{$date}',
			1
		)
	";
	mysql_query($sql);
	
}else{
	
	// updates the map listing to it's newest state
	updateMapListing($tech_id,$date);
	
}



// ------------  KEYS ------------------
if($_POST['hid_keys_submit']==1){
	
	
	$keys_action = $_POST['keys_action'];
	$keys_agency = $_POST['keys_agency'];
	
	// get agency address
	$a_sql = mysql_query("
		SELECT *
		FROM `agency`
		WHERE `agency_id` = {$keys_agency}
		AND `lat` IS NULL
		AND `lng` IS NULL
	");
	if(mysql_num_rows($a_sql)>0){
		$a = mysql_fetch_array($a_sql);
		// get geocode
		$coor = getGoogleMapCoordinates("{$a['address_1']} {$a['address_2']} {$a['address_3']} {$a['state']} {$a['postcode']}, {$_SESSION['country_name']}");
		// update agency lat/lng
		mysql_query("
			UPDATE `agency`
			SET 
				`lat` = '{$coor['lat']}',
				`lng` = '{$coor['lng']}'
			WHERE `agency_id` = {$keys_agency}
		");
	}
	
	$job_count = getJobsTotalRoutes($tech_id,$date);
	$key_count = getTotalKeyRoutes($tech_id,$date);

	$last_index = ($job_count+$key_count)+2;
	
	if($keys_action=="Pick Up & Drop Off"){
		
		$keys_array = array(
			'Pick Up',
			'Drop Off'
		);
		
		//$k = 2;
		foreach($keys_array as $val){
			
			mysql_query("
				INSERT INTO
				`key_routes`(
					`tech_id`,
					`date`,
					`action`,
					`agency_id`,
					`sort_order`
				)
				VALUES(
					{$tech_id},
					'{$date}',
					'{$val}',
					'{$keys_agency}',
					{$last_index}
				)
			");	

			$last_index++;
			
		}	
		
	}else{
		
		mysql_query("
			INSERT INTO
			`key_routes`(
				`tech_id`,
				`date`,
				`action`,
				`agency_id`,
				`sort_order`
			)
			VALUES(
				{$tech_id},
				'{$date}',
				'{$keys_action}',
				'{$keys_agency}',
				{$last_index}
			)
		");
		
	}

	$keys_success = 1;
	
}






?>

    
  <div id="mainContent">
  
  <div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Overall Schedule" href="/maps.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>"><strong>Maps</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
  
<?php

	
	// get staff
	$sa_sql = mysql_query("
		SELECT *
		FROM `staff_accounts` AS sa
		WHERE sa.`StaffID` ={$tech_id}
	");
	$sa = mysql_fetch_array($sa_sql);
	
	// total jobs
	$tot_jobs_sql = mysql_query("
		SELECT count( j.`id` ) AS jcount
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`assigned_tech` ={$tech_id}
		AND j.`status` = 'Booked'
		AND j.`date` = '".$date."'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$_SESSION['country_default']}
	");
	$tot_job = mysql_fetch_array($tot_jobs_sql);
	
	// total billable
	$tot_bill_sql = mysql_query("
		SELECT count( j.`id` ) AS jcount
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`assigned_tech` ={$tech_id}
		AND j.`status` = 'Booked'
		AND j.`door_knock` = 0
		AND j.`date` = '".$date."'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND(
			j.`job_type` = 'Yearly Maintenance'
			OR j.`job_type` = '240v Rebook'
			OR j.`job_type` = 'Once-off'
		)
		AND p.`deleted` =0
		AND a.`country_id` = {$_SESSION['country_default']}
	");
	$tot_bill = mysql_fetch_array($tot_bill_sql);
	
	// total door knock
	$tot_dk_sql = mysql_query("
		SELECT count( j.`id` ) AS jcount
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`assigned_tech` ={$tech_id}
		AND j.`status` = 'Booked'
		AND j.`door_knock` = 1
		AND j.`date` = '".$date."'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$_SESSION['country_default']}
	");
	$tot_dk = mysql_fetch_array($tot_dk_sql);
		
	
	// target
	$tot_tar_sql = mysql_query("
		SELECT `booking_target`
		FROM `calendar`
		WHERE `staff_id` = {$sa['StaffID']}
		AND ('".$date."' BETWEEN `date_start` AND `date_finish`)
		AND `country_id` = {$_SESSION['country_default']}
	");
	$tot_tar = mysql_fetch_array($tot_tar_sql);
	
	echo "
		<div class='vosch-tp aviw_drop-h'>
<style>
.fl-left > span {
    margin-right: 23px;
}
.fl-left input.addinput {
    float: none;
}
</style>
  <div class='fl-left' style='float: left;'>
	<span>{$sa['FirstName']} {$sa['LastName']} {$day}/{$month}/{$year}</span>
	<span>Target: <input type='text' style='width: 50px;' class='addinput' value='{$tot_tar['booking_target']}' readonly='readonly' /></span>
    <span>Booked:  <input type='text' style='width: 50px;' class='addinput' value='{$tot_job['jcount']}' readonly='readonly' /></span>
	 <span>Door Knocks:  <input type='text' style='width: 50px;' class='addinput' value='{$tot_dk['jcount']}' readonly='readonly' /></span>
	 <span>Billables:  <input type='text' style='width: 50px;' class='addinput' value='{$tot_bill['jcount']}' readonly='readonly' /></span>";
	 
	 
	 $mr_sql = mysql_query("
		SELECT *
		FROM `map_routes`
		WHERE `tech_id` = {$tech_id}
		AND `date` = '{$date}'
	");
	$mr = mysql_fetch_array($mr_sql);
	 
	 echo "<span>Run Complete:  <input type='checkbox' name='run_complete' id='run_complete' style=' float: none; margin-left: 6px; width: auto;' ".( ($mr['run_complete']==1)?'checked="checked"':'' )." /></span>
  </div>";
  
  
  echo "<span>Run Approved:  <input type='checkbox' name='run_approved' id='run_approved' style=' float: none; margin-left: 6px; width: auto;' ".( ($mr['run_approved']==1)?'checked="checked"':'' )." /></span>
  </div>";

  /*
  echo "<div class='fl-left pull-left'>
  <a href='export_overall_schedule_day.php?day={$useday}&amp;month={$usemonth}&amp;year={$useyear}' class='submitbtnImg colorwhite'>Export</a>
  </div>";
  */
  
echo "</div>
	\n";

	
?>


<?php
if($keys_success==1){ ?>
<div class="success">Key Routes Added!</div>
<?php	
}
?>

<?php
if($delete_mp==1){ ?>
<div class="success">Map Routes Removed</div>
<?php	
}
?>


<div id="map-canvas" style="width:100%;height:500px;border:1px solid #cccccc;"></div>


<div id="distance"></div>

<?php


$prop_address = array();
$i = 0;

// get start and end point
$mp_sql = mysql_query("
	SELECT *
	FROM `map_routes`
	WHERE `tech_id` = {$tech_id}
	AND `date` = '{$date}'
");
$mp = mysql_fetch_array($mp_sql);





	echo "
		<div class='vosch-tp aviw_drop-h'>
<style>
.fl-left > span {
    margin-right: 23px;
}
.fl-left input.addinput {
    float: none;
}
</style>
  <div class='fl-left' style='float: left;'>
	";
	
	?>
	
	
	<div style="float: left; margin-right: 25px;">
	<input type="hidden" id="orig_btn_txt" value="<?php echo ( $mp['start']!="" && $mp['end']!="" )?'EDIT':'SET'; ?> Start & End"  />
<button type="button"  id="btn_set_start_end" jclicked="0" class="submitbtnImg blue-btn"><?php echo ( $mp['start']!="" && $mp['end']!="" )?'EDIT':'SET'; ?> Start & End</button>
</div>
	
	
<div id="start_end_main_div" style="float: left; width: 700px; margin-bottom: 41px; display:none;">
	<div style="float: left; margin-right: 25px;">
	Start: 
	<select id="start_point" style="float:none; width:200px;">
		<option value="">-- Select --</option>
		<?php
		$acco_sql = mysql_query("
			SELECT *
			FROM `accomodation`
			WHERE `country_id` = {$_SESSION['country_default']}
			ORDER BY `name`
		");
		while($acco = mysql_fetch_array($acco_sql)){ ?>
			<option value="<?php echo $acco['accomodation_id']; ?>" <?php echo ($acco['accomodation_id']==$mp['start'])?'selected="selected"':''; ?>><?php echo $acco['name']; ?></option>
		<?php	
		}
		?>
	</select>
	</div>
		
		
		
		


	<div style="float: left; margin-right: 25px;">
	End: 
	<select id="end_point" style="float:none; width:200px;">
			<option value="">-- Select --</option>
			<?php
			$acco_sql = mysql_query("
				SELECT *
				FROM `accomodation`
				WHERE `country_id` = {$_SESSION['country_default']}
				ORDER BY `name`
			");
			while($acco = mysql_fetch_array($acco_sql)){ ?>
				<option value="<?php echo $acco['accomodation_id']; ?>" <?php echo ($acco['accomodation_id']==$mp['end'])?'selected="selected"':''; ?>><?php echo $acco['name']; ?></option>
			<?php	
			}
			?>
		</select>
		</div>
		
	<div style="float: left; margin-right: 25px;">
	<button type="button" id="btn_update_map" class="blue-btn submitbtnImg">Submit</button>
	</div>	
</div>
	


<div style="float: left; margin-right: 25px;">
<button type="button" id="btn_keys" class="submitbtnImg blue-btn">ADD Keys</button>
</div>




<style>
#tbl_keys tr, #tbl_keys td {
    border: medium none !important;
}
</style>
<div id="keys_div" style="display:none; float: left; margin-right: 25px;">
<form method="post" id="keys_form">
<table style="width: auto;" id="tbl_keys">
<tr>
	<td style="display:none;">Action: </td>
	<td style="display:none;">
		<select name="keys_action" id="keys_action">
			<option value="Pick Up & Drop Off">Pick Up & Drop Off</option>
		</select>
	</td>
	<td>Agency: </td>
	<td>
		<select name="keys_agency" id="keys_agency" style="width:200px;">
			<option value="">-- Select --</option>
			<?php 
			$agency_sql = mysql_query("
				SELECT DISTINCT (
					a.`agency_id`
				), a.`agency_id` , a.`agency_name` 
				FROM jobs AS j
				LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
				LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
				WHERE j.`assigned_tech` = {$tech_id}
				AND j.date = '{$date}'
				AND p.deleted = 0
				AND a.`status` = 'active'
				AND j.`del_job` = 0
				AND j.`sort_date` = '{$date}'
				AND a.`country_id` = {$_SESSION['country_default']}
			");
			while($agency = mysql_fetch_array($agency_sql)){ ?>
				<option value="<?php echo $agency['agency_id'];  ?>"><?php echo $agency['agency_name'];  ?></option>
			<?php	
			}
			?>
		</select>
	</td>
	<td>
		<input type="hidden" name="hid_keys_submit" id="hid_keys_submit" value="0" />
		<button type="button" id="btn_keys_submit" class="submitbtnImg blue-btn">Submit</button>
	</td>
</tr>
</table>
</form>
</div>

<?php
//if($_SESSION['USER_DETAILS']['StaffID']==2025){ ?>
	<div style="float: right; margin-right: 25px; display:none;">
		<a href="refresh_map.php?tech_id=<?php echo $_GET['tech_id'] ?>&year=<?php echo $_GET['year'] ?>&month=<?php echo $_GET['month'] ?>&day=<?php echo $_GET['day'] ?>">
			<button type="button" class="submitbtnImg">Refresh Map</button>
		</a>
	</div>
	
	<div style="float: right; margin-right: 25px; display:none;">
		<a href="fix_map_sort.php?tech_id=<?php echo $_GET['tech_id'] ?>&year=<?php echo $_GET['year'] ?>&month=<?php echo $_GET['month'] ?>&day=<?php echo $_GET['day'] ?>">
			<button type="button" class="submitbtnImg">Fix Map</button>
		</a>
	</div>
	
	<div style="float: right; margin-right: 25px; display:none;">
		<a href="update_map_listings.php?tech_id=<?php echo $_GET['tech_id'] ?>&year=<?php echo $_GET['year'] ?>&month=<?php echo $_GET['month'] ?>&day=<?php echo $_GET['day'] ?>">
			<button type="button" class="submitbtnImg">Update Map Listing</button>
		</a>
	</div>
<?php
//}
?>

	

	<?php
	
 echo "</div>";


  
echo "</div>";

	$ctr = 1;
	
	if($_REQUEST['order_by']){
		if($_REQUEST['order_by']=='ASC'){
			$ob = 'DESC';
			$sort_arrow = '<div class="arw-std-up arrow-top-active"></div>';
		}else{
			$ob = 'ASC';
			$sort_arrow = '<div class="arw-std-dwn arrow-dwn-active"></div>';
		}
	}else{
		$sort_arrow = '<div class="arw-std-up"></div>';
		$ob = 'ASC';
	}

	
?>
<form method="post" id="jform">
<table id="tbl_maps" border=0 cellspacing=0 cellpadding=5 width=100% class="table-left tbl-fr-red">
<tr class="nodrop nodrag" bgcolor="#b4151b" style="border-bottom: 1px solid #B4151B !important;">
<th>#</th>
<th>Created</th>
<th>Notes</th>
<th>Booking Time</th>
<th>Status</th>
<th>Job Type</th>
<th>Service</th>
<th>DK</th>
<th>Address</th>
<th>Agency</th>
<th>Time</th>
<th>Distance</th>
<th>Completed</th>
<th><input type="checkbox" id="check_all" /></th>
<th>#</th>
</tr>


<tr class="nodrop nodrag" style="background-color:#ffffff;">
<td colspan="3">
<?php 
// start
echo $ctr; 

if($mp['start']!=""){
	
	$start_acco_sql = mysql_query("
		SELECT *
		FROM `accomodation`
		WHERE `accomodation_id` = {$mp['start']}
		AND `country_id` = {$_SESSION['country_default']}
	");
	$start_acco = mysql_fetch_array($start_acco_sql);

	if(mysql_num_rows($start_acco_sql)>0){	


		// get gecode
		//$gm_coor = getGoogleMapCoordinates("{$start_acco['address']}, {$_SESSION['country_name']}");
		// store it on property address array
		
		//$prop_address[$i]['address'] = "{$start_acco['street_number']} {$start_acco['street_name']} {$start_acco['suburb']} {$start_acco['state']} , {$_SESSION['country_name']}";
		$prop_address[$i]['address'] = "{$start_acco['address']}, {$_SESSION['country_name']}";
		$prop_address[$i]['lat'] = $start_acco['lat'];
		$prop_address[$i]['lng'] = $start_acco['lng'];
		
		$i++;
		
		$start_agency_name = $start_acco['name'];
		//$start_agency_address = "{$start_acco['street_number']} {$start_acco['street_name']}, {$start_acco['suburb']}";
		$start_agency_address = $start_acco['address'];
		
	}
	
}



?>
</td>
<td colspan="3"><?php echo $start_agency_name; ?></td>
<td><img src="/images/red_house_resized.png" /></td>
<td>&nbsp;</td>
<td><?php echo $start_agency_address; ?></td>
<td>&nbsp;</td>
<td class="time">&nbsp;</td>
<td class="distance">&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td><?php 
echo $ctr; 
$ctr++; 
?></td>
</tr>




<?php


// TEST JOBS
$jr_list = getJobRouteList2($tech_id,$date);

$jr_count = mysql_num_rows($jr_list);

$comp_count = 0;
$jr_arr = array();
while($jr = mysql_fetch_array($jr_list)){
	
	if($jr['ts_completed']==1){
		$comp_count++;
	}
	
	$jr_arr[$jr['sort_order']] = array(
	
		'jid' => $jr['jid'],
		'job_type' => $jr['job_type'],
		'j_status' => $jr['j_status'],
		'tech_notes' => $jr['tech_notes'],
		'time_of_day' => $jr['time_of_day'],
		'completed_timestamp' => $jr['completed_timestamp'],
		'job_reason_id' => $jr['job_reason_id'],
		'ts_completed' => $jr['ts_completed'],
		'j_service' => $jr['j_service'],
		'created' => $jr['created'],
		'urgent_job' => $jr['urgent_job'],
		
		'property_id' => $jr['property_id'],
		'p_address_1' => $jr['p_address_1'],
		'p_address_2' => $jr['p_address_2'],
		'p_address_3' => $jr['p_address_3'],
		'p_state' => $jr['p_state'],
		'p_postcode' => $jr['p_postcode'],
		'key_number' => $jr['key_number'],
		'p_lat' => $jr['p_lat'],
		'p_lng' => $jr['p_lng'],
		
		'agency_id' => $jr['agency_id'],
		'agency_name' => $jr['agency_name'],
		'a_address_1' => $jr['a_address_1'],
		'a_address_2' => $jr['a_address_2'],
		'a_address_3' => $jr['a_address_3'],
		'a_state' => $jr['a_state'],
		'a_postcode' => $jr['a_postcode'],
		'a_phone' => $jr['a_phone']
		
	);
}

/*
echo "<pre>";
print_r($jr_arr);
echo "</pre>";

if (array_key_exists(6, $jr_arr))
  {
  echo "Match found";
  }
else
  {
  echo "Match not found";
  }
*/


	// TEST KEYS
	$kr_list = getKeyRouteList2($tech_id,$date);
	
	$kr_count = mysql_num_rows($kr_list);

$kr_arr = array();
while($kr = mysql_fetch_array($kr_list)){
	$kr_arr[$kr['sort_order']] = array(
	
		'key_routes_id' => $kr['key_routes_id'],
		'action' => $kr['action'],
		'completed' => $kr['completed'],
		'completed_date' => $kr['completed_date'],
		'number_of_keys' => $kr['number_of_keys'],
		'agency_staff' => $kr['agency_staff'],
		
		'agency_id' => $kr['agency_id'],
		'agency_name' => $kr['agency_name'],
		'address_1' => $kr['address_1'],
		'address_2' => $kr['address_2'],
		'address_3' => $kr['address_3'],
		'state' => $kr['state'],
		'postcode' => $kr['postcode'],
		'agency_hours' => $kr['agency_hours'],
		'phone' => $kr['phone'],
		'lat' => $kr['lat'],
		'lng' => $kr['lng']
		
	);
}

/*
echo "<pre>";
print_r($kr_arr);
echo "</pre>";

if (array_key_exists(6, $kr_arr))
  {
  echo "Match found";
  }
else
  {
  echo "Match not found";
  }
*/


$total_list = ($jr_count+$kr_count);

/*
echo "Total: ".$total_list."<br />".
	"Number of Jobs: ".$jr_count."<br />".
	"Completed Jobs: ".$comp_count;
*/

$total_map_routes = $total_list+1;
$job_ctr = 0;
for($j=2;$j<=$total_map_routes;$j++){ 

	// KEYS
	if(array_key_exists($j, $kr_arr)){ 
		$ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$kr_arr[$j]['agency_id']}");
	?>
		<tr id="key_routes_id:<?php echo $kr_arr[$j]['key_routes_id']; ?>" style="background-color:<?php echo ($kr_arr[$j]['completed']==1)?'#c2ffa7':'#eeeeee'; ?>;">
			<td colspan="3"><?php echo $j; ?></td>
			<td>
				<?php 
					if($kr_arr[$j]['completed']==1){
						$kr_act = explode(" ",$kr_arr[$j]['action']);
						$temp2 = ($kr_arr[$j]['action']=="Drop Off")?'p':'';
						$temp = "{$kr_act[0]}{$temp2}ed";
						$action = "{$temp} {$kr_act[1]}";
					}else{
						$action = $kr_arr[$j]['action'];
					}
					echo $action;
				?>
			</td>
			<td colspan="2"><?php echo $kr_arr[$j]['agency_name']; ?></td>
			<td><img src="/images/key_icon.png" /></td>
			<td>&nbsp;</td>
			<td><a href="<?php echo $ci_link; ?>"><?php echo "{$kr_arr[$j]['address_1']} {$kr_arr[$j]['address_2']}, {$kr_arr[$j]['address_3']}"; ?></a></td>			
			<td><a href="<?php echo $ci_link; ?>"><?php echo $kr_arr[$j]['agency_name']; ?></a></td>
			<td class="time">&nbsp;</td>
			<td class="distance">&nbsp;</td>
			<td><?php echo ($kr_arr[$j]['completed_date']!="")?date("H:i",strtotime($kr_arr[$j]['completed_date'])):""; ?></td>
			<td>
				<input type='checkbox' name="del_map_route[]" class='del_map_route' value='key_routes_id:<?php echo $kr_arr[$j]['key_routes_id']; ?>' />
				<input type="hidden" name="map_id[]" value="key_routes_id:<?php echo $kr_arr[$j]['key_routes_id']; ?>" />
			</td>
			<td><?php echo $j; ?></td>
		</tr>
	<?php
	// get gecode
	$prop_address[$i]['address'] = "{$kr_arr[$j]['address_1']} {$kr_arr[$j]['address_2']} {$kr_arr[$j]['address_3']} {$kr_arr[$j]['state']} {$kr_arr[$j]['postcode']}, {$_SESSION['country_name']}";
	$prop_address[$i]['is_keys'] = 1;
	$prop_address[$i]['lat'] = $kr_arr[$j]['lat'];
	$prop_address[$i]['lng'] = $kr_arr[$j]['lng'];
	$i++;
	}

	// JOBS
	if(array_key_exists($j, $jr_arr)){ 
	
		$bgcolor = "#FFFFFF";
		if($jr_arr[$j]['job_reason_id']>0){
			$bgcolor = "#fffca3";
		}else  if($jr_arr[$j]['ts_completed']==1){
			$bgcolor = "#c2ffa7";
		}
		
		
		$j_created = date("Y-m-d",strtotime($jr_arr[$j]['created']));
		$last_60_days = date("Y-m-d",strtotime("-60 days"));
		
		if( $jr_arr[$j]['j_status']=='To Be Booked' && $j_created<$last_60_days ){
			$bgcolor = '#7ba6c6';
		}
		
	?>
		<tr id="jobs_id:<?php echo $jr_arr[$j]['jid']; ?>" style="background-color:<?php echo $bgcolor; ?>">
			<td><?php echo $j; ?></td>
			<td><?php echo ( $jr_arr[$j]['created']!="" && $jr_arr[$j]['created']!="0000-00-00" )?date("d/m/Y",strtotime($jr_arr[$j]['created'])):''; ?></td>
			<td><?php echo $jr_arr[$j]['tech_notes']; ?></td>
			<td><?php echo $jr_arr[$j]['time_of_day']; ?></td>	
			<td class="jstatus"><?php echo $jr_arr[$j]['j_status']; ?></td>
			<td>
			<?php
			// job type
			switch($jr_arr[$j]['job_type']){
				case 'Once-off':
					$jt = 'Once-off';
				break;
				case 'Change of Tenancy':
					$jt = 'COT';
				break;
				case 'Yearly Maintenance':
					$jt = 'YM';
				break;
				case 'Fix or Replace':
					$jt = 'FR';
				break;
				case '240v Rebook':
					$jt = '240v';
				break;
				case 'Lease Renewal':
					$jt = 'LR';
				break;
			}	
			?>
			<a href="view_job_details.php?id=<?php echo $jr_arr[$j]['jid']; ?>"><?php echo $jt; ?></a></td>
			<td>
			<?php
			switch($jr_arr[$j]['j_service']){
				case 2:
					$serv_color = 'b4151b';
					$serv_icon = 'smoke_colored.png';
				break;
				case 5:
					$serv_color = 'f15a22';
					$serv_icon = 'safety_colored.png';
				break;
				case 6:
					$serv_color = '00ae4d';
					$serv_icon = 'corded_colored.png';
				break;
				case 7:
					$serv_color = '00aeef';
					$serv_icon = 'pool_colored.png';
				break;
				case 8:
					$serv_color = '9b30ff';
					$serv_icon = 'sa_ss_colored.png';
				break;
				case 9:
					$serv_color = '9b30ff';
					$serv_icon = 'sa_cw_ss_colored.png';
				break;
			}
			?>
			<img src="images/serv_img/<?php echo $serv_icon; ?>" />
			</td>
			<td><?php echo ($jr_arr[$j]['door_knock']==1)?'DK':''; ?></td>
			<td><a href="view_property_details.php?id=<?php echo $jr_arr[$j]['property_id']; ?>"><?php echo $jr_arr[$j]['p_address_1']." ".$jr_arr[$j]['p_address_2'].", ".$jr_arr[$j]['p_address_3']; ?></a></td>						
			<td>
			<?php $ci_link2 = $crm->crm_ci_redirect("/agency/view_agency_details/{$jr_arr[$j]['agency_id']}"); ?>
			<a href="<?php echo $ci_link2; ?>"><?php echo $jr_arr[$j]['agency_name']; ?></a></td>
			<td class="time">&nbsp;</td>
			<td class="distance">&nbsp;</td>
			<?php
			if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){
				echo "<td>".(($jr_arr[$j]['completed_timestamp']!="")?date("H:i",strtotime($jr_arr[$j]['completed_timestamp'])):'')."</td>";
			}
			?>
			<td>
				<input type="checkbox" name="del_map_route[]" class='del_map_route' value="job_id:<?php echo $jr_arr[$j]['jid']; ?>" />
				<input type="hidden" name="map_id[]" value="job_id:<?php echo $jr_arr[$j]['jid']; ?>" />
			</td>
			<td><?php echo $j; ?></td>
		</tr>
	<?php	
		// store it on property address array
		$prop_address[$i]['address'] = "{$jr_arr[$j]['p_address_1']} {$jr_arr[$j]['p_address_2']} {$jr_arr[$j]['p_address_3']} {$jr_arr[$j]['p_state']} {$jr_arr[$j]['p_postcode']}, {$_SESSION['country_name']}";
		$prop_address[$i]['status'] = $jr_arr[$j]['j_status'];
		$prop_address[$i]['created'] = date("Y-m-d",strtotime($jr_arr[$j]['created']));
		$prop_address[$i]['urgent_job'] = $jr_arr[$j]['urgent_job'];
		$prop_address[$i]['lat'] = $jr_arr[$j]['p_lat'];
		$prop_address[$i]['lng'] = $jr_arr[$j]['p_lng'];
		$i++;
	}

}
?>


<tr class="nodrop nodrag" style="background-color:#ffffff;">
<td colspan="3">
<?php 
if($mp['start']=="" && $mp['end']==""){
	$end_point_index = $total_list+2;
}else{
	$end_point_index = $j; 
}
echo $end_point_index;


$end_acco_sql = mysql_query("
	SELECT *
	FROM `accomodation`
	WHERE `accomodation_id` = {$mp['end']}
	AND `country_id` = {$_SESSION['country_default']}
");
$end_acco = mysql_fetch_array($end_acco_sql);

if(mysql_num_rows($end_acco_sql)>0){	

	
	//$gm_coor = getGoogleMapCoordinates("{$end_acco['address']}, {$_SESSION['country_name']}");
	// store it on property address array
	
	//$prop_address[$i]['address'] = "{$end_acco['street_number']} {$end_acco['street_name']} {$end_acco['suburb']} {$end_acco['state']} , {$_SESSION['country_name']}";
	$prop_address[$i]['address'] = "{$end_acco['address']}, {$_SESSION['country_name']}";
	$prop_address[$i]['lat'] = $end_acco['lat'];
	$prop_address[$i]['lng'] = $end_acco['lng'];

	$i++;
	
	$end_agency_name = $end_acco['name'];
	//$end_agency_address = "{$end_acco['street_number']} {$end_acco['street_name']}, {$end_acco['suburb']}";
	$end_agency_address = $end_acco['address'];
	
}

?>
</td>
<td colspan="3"><?php echo $end_agency_name; ?></td>
<td><img src="/images/red_house_resized.png" /></td>
<td>&nbsp;</td>
<td><?php echo $end_agency_address; ?>
</td>
<td>&nbsp;</td>	
<td class="time">&nbsp;</td>
<td class="distance">&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td><?php //echo $end_point_index; ?></td>
<?php
$ctr++;
?>
</tr>

<tr class="nodrop nodrag">
<td colspan="9">TOTAL</td>
<td id="tot_time">0</td>
<td id="tot_dis">0</td>
<td colspan="3">&nbsp;</td>
</tr>

</table>



<?php 

/*
$API_key = 'AIzaSyAa9QRZRQ3eucZ6OE18rSSi8a7VGJjoXQE';
$test_address = "4/16 Yarranabbe Road Darling Point NSW 2027, {$_SESSION['country_name']}";

$url = "https://maps.googleapis.com/maps/api/geocode/json?address=".rawurlencode($test_address)."&key={$API_key}";
*/

//echo "<a href='{$url}'>test geocode</a>";


echo "<pre>";
print_r($prop_address);
echo "</pre>";



/*
echo "<br />First Address: {$prop_address[0]['address']}";
echo "<br />First Address lat: {$prop_address[0]['lat']}";
 echo "<br />First Address lng: {$prop_address[0]['lng']}";
 echo "<br />last index: {$i}";
 
 $num_prop2 = count($prop_address);
 
 echo "<br />Number of Address: {$num_prop2}";
 echo "<br />".intval($num_prop2/10);
*/
 //echo "<br />Total routes: ".getTotalRoutes($tech_id,$date);
	 
	 


?>

<div id="hidden_button_div" style="margin-top: 15px; float: right; display: none;">
	<div style="display:block; float: right;">
		<input type="hidden" name="remove_flag" id="remove_flag" value="0" />
		<button type="button" name="btn_remove" id="btn_remove" class="submitbtnImg">Remove</button>
	</div>
	<div style="float: right; margin-right: 35px;" id="map_div">
		Tech:
		<select id="maps_tech">
			<option value="">-- select --</option>
			<?php
			$tech_sql = mysql_query("
				SELECT *
				FROM `staff_accounts`
				WHERE `active` = 1
				AND ( 
					`FirstName` != '' AND `LastName` != ''
				)
				ORDER BY `FirstName`, `LastName`
			");
			while($tech = mysql_fetch_array($tech_sql)){ ?>
			
				<option value="<?php echo $tech['id']; ?>"><?php echo "{$tech['FirstName']} {$tech['LastName']}"; ?></option>
			
			<?php	
			}
			?>
		</select>
		Date:
		<input type="text" id="maps_date" class="datepicker" />
		<button type="button" id="btn_assign" class="blue-btn submitbtnImg">Assign</button>
	</div>
</div>
</form>

<div style="clear:both;">&nbsp;</div>

  </div>
</div>

<br class="clearfloat" />

<script src="http://code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAa9QRZRQ3eucZ6OE18rSSi8a7VGJjoXQE"></script>
<script type="text/javascript">

jQuery(".datepicker").datepicker({ dateFormat: "dd/mm/yy" });

// variables
var markersArray = [];
var map;
var directionsService = new google.maps.DirectionsService();
var distances = "";
var icon = 'images/car.png';
var image;
<?php
if(mysql_num_rows($start_acco_sql)>0){ ?>
var jcount = 1;
<?php
}else{ ?>
var jcount = 2;
<?php	
}
?>

var tot_time = 0;
var tot_dis = 0;
var orig_dur = 0;

<?php
$js_array = json_encode($prop_address);
echo "var prop_address = ". $js_array . ";";
?>

//console.log(prop_address);
function jMarkerCustomIcon(image,x,y){
	
	console.log("X: "+x+" Y: "+y);
	
	// custom icon
	var icon = {
		url: image,
		// This marker is 29 pixels wide by 44 pixels tall.
		size: new google.maps.Size(34, 44),
		// The origin for this image is 0,0.
		origin: new google.maps.Point(x,y)
	};
	return icon;
}

function calculateDistances(start,destination) {
  var service = new google.maps.DistanceMatrixService();
  service.getDistanceMatrix(
    {
      origins: [start],
      destinations: [destination],
      travelMode: google.maps.TravelMode.DRIVING,
      unitSystem: google.maps.UnitSystem.METRIC,
      avoidHighways: false,
      avoidTolls: false
    }, callback);
}

function callback(response, status) {
	
var jtext = "";

if (status != google.maps.DistanceMatrixStatus.OK) {
	
	alert('Error was: ' + status);
	
}else{
	
	var origins = response.originAddresses;
	var destinations = response.destinationAddresses;
	//var outputDiv = document.getElementById('outputDiv');
	//outputDiv.innerHTML = '';
	//deleteOverlays();

	for (var i = 0; i < origins.length; i++) {
		var results = response.rows[i].elements;
		//addMarker(origins[i], false);
		for (var j = 0; j < results.length; j++) {
			//addMarker(destinations[j], true);
			/*
			outputDiv.innerHTML += origins[i] + ' to ' + destinations[j]
			+ ': ' + results[j].distance.text + ' in '
			+ results[j].duration.text + '<br>';
			*/
			jtext += 'index: '+jcount+' - '+origins[i] + ' ---- ' + destinations[j]
			+ ': ' + results[j].distance.text + ' in '
			+ results[j].duration.text + ' value: '+results[j].duration.value+'\n';

			console.log(jtext);

			jQuery(".time:eq("+jcount+")").html(results[j].duration.text);
			jQuery(".distance:eq("+jcount+")").html(results[j].distance.text);

			tot_time += parseFloat(results[j].duration.text);
			tot_dis += parseFloat(results[j].distance.text);
			orig_dur += results[j].duration.value;

			var totalSec = orig_dur;
			var hours = parseInt( totalSec / 3600 ) % 24;
			var minutes = parseInt( totalSec / 60 ) % 60;
			var seconds = totalSec % 60;
			var time_str = "";
			if(hours==0){
				time_str = minutes+" mins";				
			}else{
				time_str = hours+" hours "+minutes+" mins";
			}
			jQuery("#tot_time").html(time_str);
			//jQuery("#tot_time").html(tot_time+" mins");
			jQuery("#tot_dis").html(tot_dis.toFixed(1)+" km");

			jcount++;
		}
	}

}
  
}

function addMarker(location, isDestination) {
  var icon;
  if (isDestination) {
    icon = destinationIcon;
  } else {
    icon = originIcon;
  }
  geocoder.geocode({'address': location}, function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      bounds.extend(results[0].geometry.location);
      map.fitBounds(bounds);
      var marker = new google.maps.Marker({
        map: map,
        position: results[0].geometry.location
      });
      markersArray.push(marker);
    } else {
      alert('Geocode was not successful for the following reason: '
        + status);
    }
  });
}

function deleteOverlays() {
  for (var i = 0; i < markersArray.length; i++) {
    markersArray[i].setMap(null);
  }
  markersArray = [];
}

// add markers
function jAddMarkers(position,popupcontent,icon){
	
	//icon = (icon!="")?icon:'images/car.png';
	
	var beachMarker = new google.maps.Marker({
      position: position,
      map: map,
	  icon: icon
  });
  
  // pop up
  jAddPopUpWindow(beachMarker,popupcontent);
  
}

function jAddPopUpWindow(beachMarker,contentString){
  var infowindow = new google.maps.InfoWindow({
      content: contentString
  });
  
  google.maps.event.addListener(beachMarker, 'click', function() {
    infowindow.open(map,beachMarker);
  });
}

function initialize() {

	var center = new google.maps.LatLng(prop_address[0]['lat'], prop_address[0]['lng']);

	// instantiate map properties
	var mapOptions = {
		zoom: 13,  // zoom - 0 for maxed out out of earth 
		center: center // where to focus view
	}

	// create the map
	map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
	
	
	// insert original script here
	  
	 
	  
	  var i = 1;
	  var max_prop = prop_address.length;
	  var last_prop_index = (max_prop-1);
	  var first_index = 0;
	  var last_index = 9;
	  
	  var prop_index = 0;
	  
	  // pin number sprite positions, currently 1-50 max
	  var sprite_num_str = '{ "pins" : [' +
	     // 1 - 10
		'{ "x":10 , "y":5 },' +
		'{ "x":10 , "y":58 },' +
		'{ "x":10 , "y":111 },' +
		'{ "x":10 , "y":165 },' +
		'{ "x":10 , "y":219 },' +
		'{ "x":10 , "y":272 },' +
		'{ "x":10 , "y":326 },' +
		'{ "x":10 , "y":379 },' +
		'{ "x":10 , "y":431 },' +
		'{ "x":10 , "y":483 },' +
		// 11 - 20
		'{ "x":62 , "y":5 },' +
		'{ "x":62 , "y":58 },' +
		'{ "x":62 , "y":111 },' +
		'{ "x":62 , "y":165 },' +
		'{ "x":62 , "y":219 },' +
		'{ "x":62 , "y":272 },' +
		'{ "x":62 , "y":326 },' +
		'{ "x":62 , "y":379 },' +
		'{ "x":62 , "y":431 },' +
		'{ "x":62 , "y":483 },' +
		// 21 - 30
		'{ "x":115 , "y":5 },' +
		'{ "x":115 , "y":58 },' +
		'{ "x":115 , "y":111 },' +
		'{ "x":115 , "y":165 },' +
		'{ "x":115 , "y":219 },' +
		'{ "x":115 , "y":272 },' +
		'{ "x":115 , "y":326 },' +
		'{ "x":115 , "y":379 },' +
		'{ "x":115 , "y":431 },' +
		'{ "x":115 , "y":483 },' +
		// 31 - 40
		'{ "x":167 , "y":5 },' +
		'{ "x":167 , "y":58 },' +
		'{ "x":167 , "y":111 },' +
		'{ "x":167 , "y":165 },' +
		'{ "x":167 , "y":219 },' +
		'{ "x":167 , "y":272 },' +
		'{ "x":167 , "y":326 },' +
		'{ "x":167 , "y":379 },' +
		'{ "x":167 , "y":431 },' +
		'{ "x":167 , "y":483 },' +
		// 41 - 50
		'{ "x":218 , "y":5 },' +
		'{ "x":218 , "y":58 },' +
		'{ "x":218 , "y":111 },' +
		'{ "x":218 , "y":165 },' +
		'{ "x":218 , "y":219 },' +
		'{ "x":218 , "y":272 },' +
		'{ "x":218 , "y":326 },' +
		'{ "x":218 , "y":379 },' +
		'{ "x":218 , "y":431 },' +
		'{ "x":218 , "y":483 },' +
		// 51 - 60
		'{ "x":260 , "y":5 },' +
		'{ "x":260 , "y":58 },' +
		'{ "x":260 , "y":111 },' +
		'{ "x":260 , "y":165 },' +
		'{ "x":260 , "y":219 },' +
		'{ "x":260 , "y":272 },' +
		'{ "x":260 , "y":326 },' +
		'{ "x":260 , "y":379 },' +
		'{ "x":260 , "y":431 },' +
		'{ "x":260 , "y":483 },' +
		// 61 - 70
		'{ "x":301 , "y":5 },' +
		'{ "x":301 , "y":58 },' +
		'{ "x":301 , "y":111 },' +
		'{ "x":301 , "y":165 },' +
		'{ "x":301 , "y":219 },' +
		'{ "x":301 , "y":272 },' +
		'{ "x":301 , "y":326 },' +
		'{ "x":301 , "y":379 },' +
		'{ "x":301 , "y":431 },' +
		'{ "x":301 , "y":483 },' +
		// 71 - 80
		'{ "x":343 , "y":5 },' +
		'{ "x":343 , "y":58 },' +
		'{ "x":343 , "y":111 },' +
		'{ "x":343 , "y":165 },' +
		'{ "x":343 , "y":219 },' +
		'{ "x":343 , "y":272 },' +
		'{ "x":343 , "y":326 },' +
		'{ "x":343 , "y":379 },' +
		'{ "x":343 , "y":431 },' +
		'{ "x":343 , "y":483 },' +
		// 81 - 90
		'{ "x":382 , "y":5 },' +
		'{ "x":382 , "y":58 },' +
		'{ "x":382 , "y":111 },' +
		'{ "x":382 , "y":165 },' +
		'{ "x":382 , "y":219 },' +
		'{ "x":382 , "y":272 },' +
		'{ "x":382 , "y":326 },' +
		'{ "x":382 , "y":379 },' +
		'{ "x":382 , "y":431 },' +
		'{ "x":382 , "y":483 },' +
		// 91 - 100
		'{ "x":420 , "y":5 },' +
		'{ "x":420 , "y":58 },' +
		'{ "x":420 , "y":111 },' +
		'{ "x":420 , "y":165 },' +
		'{ "x":420 , "y":219 },' +
		'{ "x":420 , "y":272 },' +
		'{ "x":420 , "y":326 },' +
		'{ "x":420 , "y":379 },' +
		'{ "x":420 , "y":431 },' +
		'{ "x":420 , "y":483 } ] }';
		
		var sprite_json = JSON.parse(sprite_num_str);
		
		//console.log("X: "+sprite_json.pins[prop_index].x+" Y: "+sprite_json.pins[prop_index].y);
	   
	  //  set interval
	  timer = setInterval(function(){ 			
			
			// if last index is reach stop interval calls
			if(parseInt(prop_index)==parseInt(last_prop_index)){
				
				// clear timer
				console.log('stop timer');
				clearInterval(timer);
				
			}else{
				
				//console.log('loop: '+i+' first index: '+first_index+' last index: '+last_index);
			
				// instantiate direction object
				var directionsDisplay = new google.maps.DirectionsRenderer({
					'suppressMarkers': true
				});
				// set directions
				 directionsDisplay.setMap(map);

			
				  // distance
				var start_dis = new google.maps.LatLng(prop_address[prop_index]['lat'], prop_address[prop_index]['lng']);
				var end_dis = new google.maps.LatLng(prop_address[prop_index+1]['lat'], prop_address[prop_index+1]['lng']);
				//console.log("Distance From: "+prop_address[prop_index]['address']+" To: "+prop_address[prop_index+1]['address']);
				calculateDistances(start_dis,end_dis);
				 
				 console.log("start index: "+prop_index+" -  Address: "+prop_address[prop_index].address+" - last property index: "+last_prop_index+" x: "+sprite_json.pins[prop_index].x+" y: "+sprite_json.pins[prop_index].y);
				 var startObj = prop_address[prop_index];
				 var start = new google.maps.LatLng(startObj['lat'], startObj['lng']);
		
				 
				 // home point, index = 0
				 if(parseInt(prop_index)==0){
					
					// if sprite number is available, currently max is 50
					if(sprite_json.pins[prop_index]!=""){
						
						var image = 'images/google_map/house_pin.png';
						var icon = jMarkerCustomIcon(image,sprite_json.pins[prop_index].x,sprite_json.pins[prop_index].y);
						
					}else{
						var icon = "";
					}
										
				}else{ // start 
					 
					// if sprite number is available, currently max is 50
					if(sprite_json.pins[prop_index]!=""){
						
						var jdate = new Date(startObj['created']);
						var last_60_day = new Date('<?php echo date("Y-m-d",strtotime("-60 days")); ?>');
						
						//console.log("index: "+prop_index+" Created:"+jdate);
						
						if( parseInt(startObj['is_keys']) == 1 ){
								var image = 'images/google_map/house_pin.png';
						}else if( startObj['status']=='To Be Booked' && parseInt(startObj['urgent_job'])==1 ){
							image = 'images/google_map/alert_mappin.png';
						}else if( startObj['status']=='To Be Booked' && jdate<last_60_day ){
							image = 'images/google_map/alert_mappin.png';
						}else if(startObj['status']=='To Be Booked'){
							image = 'images/google_map/orange_sprite.png'; 
						}else if(startObj['status']=='Booked'){
							image = 'images/google_map/red_sprite.png'; 
						}else{
							image = 'images/google_map/green_sprite.png';
						}
						
						var icon = jMarkerCustomIcon(image,sprite_json.pins[prop_index].x,sprite_json.pins[prop_index].y);
						
					}else{
						var icon = "";
					}
					 					
				}
				 
				// add markers
				jAddMarkers(start,startObj['address'],icon);
				++prop_index;				
				 
				// way points
				var wp = [];
				
				// 2nd and second to the last addresses for waypoints
				var second = first_index+1;
				var second_to_the_last = last_index-1;

				 // if only one address left on the next 10 batch, there's no way point. assign it to end point
				if((last_prop_index-prop_index)!=1){
					
					var y = 1;
					while( prop_index!=last_prop_index && y<=8 ){

						 // distance
						var start_dis = new google.maps.LatLng(prop_address[prop_index]['lat'], prop_address[prop_index]['lng']);
						var end_dis = new google.maps.LatLng(prop_address[prop_index+1]['lat'], prop_address[prop_index+1]['lng']);
						//console.log("Distance From: "+prop_address[prop_index]['address']+" To: "+prop_address[prop_index+1]['address']);
						calculateDistances(start_dis,end_dis);
						 
						 //console.log("way points index: "+prop_index+" -  Address: "+prop_address[prop_index].address+" - last property index: "+last_prop_index);
						var wpObj = prop_address[prop_index];
						var wp_loc = new google.maps.LatLng(wpObj['lat'], wpObj['lng']);
						wp.push({
							'location': wp_loc,
							'stopover':true
						}); 	

						// if sprite number is available, currently max is 50
						if(sprite_json.pins[prop_index]!=""){
							
							var jdate = new Date(wpObj['created']);
							var last_60_day = new Date('<?php echo date("Y-m-d",strtotime("-60 days")); ?>');
							
							//console.log("index: "+prop_index+" Created:"+jdate);
							
							if( parseInt(wpObj['is_keys']) == 1 ){
								var image = 'images/google_map/house_pin.png';
							}else if( wpObj['status']=='To Be Booked' && parseInt(wpObj['urgent_job'])==1 ){
								image = 'images/google_map/alert_mappin.png';
							}else if( wpObj['status']=='To Be Booked' && jdate<last_60_day ){
								image = 'images/google_map/alert_mappin.png';
							}else if(wpObj['status']=='To Be Booked'){
								image = 'images/google_map/orange_sprite.png'; 
							}else if(wpObj['status']=='Booked'){
								image = 'images/google_map/red_sprite.png'; 
							}else{
								image = 'images/google_map/green_sprite.png';
							}
							
							var icon = jMarkerCustomIcon(image,sprite_json.pins[prop_index].x,sprite_json.pins[prop_index].y);
							
						}else{
							var icon = "";
						}						
						
						// add markers
						jAddMarkers(wp_loc,wpObj['address'],icon);
						
						++prop_index;
						++y;
						
					}	
					 
				}
				
				var endObj = prop_address[prop_index];
				console.log("end index: "+prop_index+" -  Address: "+prop_address[prop_index].address+" - last property index: "+last_prop_index+" x: "+sprite_json.pins[prop_index].x+" y: "+sprite_json.pins[prop_index].y);
				var end = new google.maps.LatLng(endObj['lat'], endObj['lng']);
				
				 // end point
				 if(prop_index==last_prop_index){
					
					// if sprite number is available, currently max is 50
					if(sprite_json.pins[prop_index]!=""){
						
						var image = 'images/google_map/house_pin.png';
						var icon = jMarkerCustomIcon(image,sprite_json.pins[prop_index].x,sprite_json.pins[prop_index].y);
						
					}else{
						var icon = "";
					}
					
					// add markers
					jAddMarkers(end,endObj['address'],icon);
				 }
				 
				// direction options
				var request = {
				  origin: start,
				  destination: end,
				  waypoints: wp,
				  travelMode: google.maps.TravelMode.DRIVING,
				  unitSystem: google.maps.UnitSystem.METRIC
				};

				// invoke direction
				directionsService.route(request, function(response, status) {
					if (status == google.maps.DirectionsStatus.OK) {
					  directionsDisplay.setDirections(response);
					}
				});								
			
				i++;
				first_index=(first_index+9);
				last_index=(last_index+9);				
				
			}			

	}, 1000);
	
}

// on load  
google.maps.event.addDomListener(window, 'load', initialize);

jQuery(document).ready(function(){
	
	jQuery("#btn_set_start_end").click(function(){
		
		var clicked = jQuery(this).attr("jclicked");
		var btn_txt = jQuery(this).parents("div:first").find("#orig_btn_txt").val();
		
		//console.log(jQuery(this).html());
		if(clicked==0){
			jQuery(this).attr("jclicked",1);
			jQuery(this).html("Hide");
			jQuery("#start_end_main_div").show();
		}else{
			jQuery(this).attr("jclicked",0);
			jQuery(this).html(btn_txt);
			jQuery("#start_end_main_div").hide();
		}
		
		
	});
	
	// mark run complete 
	jQuery("#run_complete").click(function(){
		
		var status = (jQuery(this).prop("checked")==true)?1:0;
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_maps_run_complete.php",
			data: { 
				tech_id: <?php echo $_GET['tech_id']; ?>,
				date: '<?php echo "{$year}-{$month}-{$day}"; ?>',
				status: status
			}
		}).done(function( ret ) {
			//window.location="/view_vehicles.php?success=1";
		});	
		
	});
	
	
	// mark run complete 
	jQuery("#run_approved").click(function(){
		
		var status = (jQuery(this).prop("checked")==true)?1:0;
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_maps_run_approved.php",
			data: { 
				tech_id: <?php echo $_GET['tech_id']; ?>,
				date: '<?php echo "{$year}-{$month}-{$day}"; ?>',
				status: status
			}
		}).done(function( ret ) {
			//window.location="/view_vehicles.php?success=1";
		});	
		
	});
	
	
	jQuery("#btn_update_map").click(function(){
		
		var start = jQuery("#start_point").val();
		var end = jQuery("#end_point").val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_map_routes.php",
			data: { 
				tech_id: <?php echo $_GET['tech_id']; ?>,
				date: '<?php echo "{$year}-{$month}-{$day}"; ?>',
				start: start,
				end: end
			}
		}).done(function( ret ){
			window.location='/maps.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>';
		});	
		
	});
	
	// show button if start or end point is change
	jQuery("#start_point, #end_point").change(function(){
		
		jQuery("#btn_update_map").show();
		
	});
	
	// check all toggle
	jQuery("#check_all").click(function(){
  
	  if(jQuery(this).prop("checked")==true){
		jQuery(".del_map_route").prop("checked",true);
		jQuery("#hidden_button_div").show();
	  }else{
		jQuery(".del_map_route").prop("checked",false);
		jQuery("#hidden_button_div").hide();
	  }
	  
	});
	
	<?php
	//if($_SESSION['USER_DETAILS']['StaffID']==2025){ ?>
		// toggle hide/show remove button
		jQuery(".del_map_route").click(function(){

		  var chked = jQuery(".del_map_route:checked").length;
		  
		  if(chked>0){
			jQuery("#hidden_button_div").show();
		  }else{
			jQuery("#hidden_button_div").hide();
		  }

		});
	<?php	
	//}
	?>
	
	// remove maps
	jQuery("#btn_remove").click(function(){
		
		//var jobs = [];
		var is_booked = false;
		jQuery(".del_map_route:checked").each(function(){
			
			var jstatus = jQuery(this).parents("tr:first").find(".jstatus").html();
			//jobs.push(jstatus);
			
			if(jstatus=='Booked'){
				is_booked = true;
			}
			
		});
		
		if(is_booked==true){
			alert("You can't remove booked jobs");
		}else{
			jQuery("#remove_flag").val(1);
			jQuery("#jform").submit();
		}
		
	});
	
	/*
	// remove maps
	jQuery("#btn_remove").click(function(){
		
		var job_id = new Array();
		
		if(confirm("Are you sure you want to continue?")==true){
			jQuery(".chk_box:checked").each(function(){
				job_id.push(jQuery(this).val());
			});
			jQuery.ajax({
				type: "POST",
				url: "ajax_maps_remove.php",
				data: { 
					job_id: job_id
				}
			}).done(function( ret ){
				//window.location='/maps.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>';
			});	
		}
		
	});
	*/
	
   // invoke table DND
	jQuery("#tbl_maps").tableDnD({
    	onDrop: function(table, row) {
			var job_id = jQuery.tableDnD.serialize({
				'serializeRegexp': null
			});
			
			jQuery.ajax({
				method: "GET",
				url: "ajax_update_map_sort.php?tech_id=<?php echo $tech_id; ?>&"+job_id
			}).done(function( ret ) {				
				//window.location='/maps.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>';
				jQuery("#btn_update_map").show();
			});	
			
		}
    });
	
	// keys script
	jQuery("#btn_keys").click(function(){
		
		jQuery("#keys_div").toggle();
		
	});
	
	
	// keys
	jQuery("#btn_keys_submit").click(function(){
		
		var action = jQuery("#keys_action").val();
		var agency = jQuery("#keys_agency").val();
		var error = "";
		
		if(action=="" || agency==""){
			error += "Action and Agency Keys are required";
		}
		
		if(error!=""){
			alert(error);
		}else{
			jQuery("#hid_keys_submit").val(1);
			jQuery("#keys_form").submit();
		}
		
	});
	
	jQuery("#btn_assign").click(function(){
		
		var job_id = new Array();
		var tech_id = jQuery("#maps_tech").val();
		var date = jQuery("#maps_date").val();
		
		jQuery(".del_map_route:checked").each(function(){
			
			var jval = jQuery(this).val();
			
			var temp = jval.split(":"); 
			
			if(temp[0]=="job_id"){
				job_id.push(temp[1]);
			}

			
		});
		
		//console.log(job_id);
		
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_move_to_maps.php",
			data: { 
				job_id: job_id,
				tech_id: tech_id,
				date: date
			}
		}).done(function( ret ){
			window.location='/maps.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>';
			//location.reload();
		});	
		
				
	});
	
	
});

</script>

</body>
</html>
