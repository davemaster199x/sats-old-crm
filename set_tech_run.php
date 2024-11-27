<?php
include('inc/init.php');

$crm = new Sats_Crm_Class();

// WIP only show to these people
$vip = array(2025,2070,58);

$tr_id = mysql_real_escape_string($_GET['tr_id']);
$custom_sort = mysql_real_escape_string($_GET['custom_sort']);
$sel_job_type = mysql_real_escape_string($_GET['sel_job_type']);
$age_sort = mysql_real_escape_string($_GET['age']);

if(!empty($age_sort)){
	if($age_sort == 'desc'){
		$custom_sort = 'j.created ASC';
		$new_order_age = 'asc';
	} else {
		$custom_sort = 'j.created DESC';
		$new_order_age = 'desc';
	}
} else {
	$new_order_age = 'desc';
}

$encrypt = new cast128();
$encrypt->setkey(SALT);

// logged staff name
$logged_staff_name = $crm->formatStaffName($_SESSION['USER_DETAILS']['FirstName'],$_SESSION['USER_DETAILS']['LastName']);
$today = date('d/m/Y');

$tr_sql = mysql_query("
	SELECT *
	FROM  `tech_run`
	WHERE  `tech_run_id` = {$tr_id}
");

$tr = mysql_fetch_array($tr_sql);
//print_r($tr);

$hasTechRun = ( mysql_num_rows($tr_sql)>0 )?true:false;

//$tech_id = $tr['tech_id'];
$tech_id = $tr['assigned_tech'];
$day = date("d",strtotime($tr['date']));
$month = date("m",strtotime($tr['date']));
$year = date("Y",strtotime($tr['date']));
$date = $tr['date'];
$sub_regions = $tr['sub_regions'];
$show_hidden = $tr['show_hidden'];
$agency_filter = $tr['agency_filter'];
if( $agency_filter!='' ){
	$agency_filter_arr = explode(",",$agency_filter);
	$agency_filter_count = count($agency_filter_arr);
}else{
	$agency_filter_arr = [];
	$agency_filter_count = 0;
}


//get tech name
$t_sql = mysql_query("
	SELECT *
	FROM `staff_accounts`
	WHERE `StaffID` = {$tech_id}
");
$t = mysql_fetch_array($t_sql);
//print_r($t);
//exit();

$isElectrician = ( $t['is_electrician']==1 )?true:false;
//echo $isElectrician;
//exit();

$title = (mysql_num_rows($t_sql)>0)?$crm->formatStaffName($t['FirstName'],$t['LastName'])." ".date('D',strtotime($date))." ".date('d/m/Y',strtotime($date)):"Set Tech Run";
//echo $title;
//exit();

include('inc/header_html.php');
include('inc/menu.php');


$jc = new Job_Class();


if( $hasTechRun == true ){


	// get new jobs from via region
	//appendTechRunNewListings($tr_id,$tech_id,$date,$sub_regions,$_SESSION['country_default']);

	// get new jobs from via assigned
	$isAssigned = 1;
	//appendTechRunNewListings($tr_id,$tech_id,$date,$sub_regions,$_SESSION['country_default'],$isAssigned);


}


/*
// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 200;

$this_page = $_SERVER['PHP_SELF'];
$pag_params = "&tr_id={$tr_id}";


// tech run list
$tr_params = array(
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	)
);
*/



$trr_params = array(
	'custom_select' => "
		trr.`tech_run_rows_id`,
		trr.`row_id_type`,
		trr.`row_id`,
		trr.`hidden`,
		trr.`dnd_sorted`,
		trr.`highlight_color`,

		trr_hc.`tech_run_row_color_id`,
		trr_hc.`hex`,

		p.`property_id`,

		a.`agency_id`,
		a.`allow_upfront_billing`
	",
	'custom_sort' => $custom_sort
);
$jr_list2 = getTechRunRows($tr_id,$_SESSION['country_default'],$trr_params);
//echo $jr_list2;
//print_r($jr_list2);
//exit();

//$ptotal = mysql_num_rows(getTechRunRows($tr_id,$_SESSION['country_default']));
// allow number of list to be displayed
$display = ($tr['display_num']!='')?$tr['display_num']:mysql_num_rows($jr_list2);

//echo "Diplay Num: {$tr['display_num']}";
//echo "Diplay Num:".$display;
//exit();

function getRowColor(){
	return mysql_query("
		SELECT *
		FROM  `tech_run_row_color`
		WHERE `active` = 1
	");
}

function getSavedColourTable($tr_id,$colour_id){

	return mysql_query("
		SELECT *
		FROM `colour_table`
		WHERE `tech_run_id` = {$tr_id}
		AND `colour_id` = {$colour_id}
	");


}

// First Natioanl Agency
$fn_agency_arr = $crm->get_fn_agencies();
$fn_agency_main = $fn_agency_arr['fn_agency_main'];
$fn_agency_sub =  $fn_agency_arr['fn_agency_sub'];
$fn_agency_sub_imp = implode(",",$fn_agency_sub);

// Vision Real Estate
$vision_agency_arr = $crm->get_vision_agencies();
$vision_agency_main = $vision_agency_arr['vision_agency_main'];
$vision_agency_sub =  $vision_agency_arr['vision_agency_sub'];
$vision_agency_sub_imp = implode(",",$vision_agency_sub);
?>


  <div id="mainContent">

  <div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="/set_tech_run.php<?php echo ($tr_id!='')?"?tr_id={$tr_id}":""; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>



<div style="text-align: left; display:none;" id="jlegend">
	<ul>
		<li><img src="/images/hourglass.png" style="margin-right: 7px; cursor:pointer;" /> Old job</li>
		<li><img src="/images/first_icon.png" style="width: 25px; cursor:pointer;" /> First visit (Electrician would be best)</li>
		<li><img src="/images/caution.png" style="height: 25px; cursor:pointer;" /> Priority Job (COT, LR, 240v Rebook, Urgent)</li>
	</ul>
</div>



<?php

	if( $tech_id != '' && $date != '' ){

	//echo $tech_id;
	//exit();
	//Donald Duck = 2157

	// get staff
	$sa_sql = mysql_query("
		SELECT `StaffID`, `FirstName`, `LastName`
		FROM `staff_accounts`
		WHERE `StaffID` = {$tech_id}
	");
	$sa = mysql_fetch_array($sa_sql);
	//print_r($sa);
	//exit();

	if( $hasTechRun==true ){

		// get calendar data
		$cal_sql = mysql_query("
			SELECT *
			FROM `calendar`
			WHERE staff_id = {$tech_id}
			AND `date_start` = '{$date}'
			AND `date_finish` = '{$date}'
			ORDER BY `calendar_id` DESC
		");
		$cal = mysql_fetch_array($cal_sql);
		$cal_name = $cal['region'];
		$cal_id = $cal['calendar_id'];
	}

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
	//print_r($tot_job);
	//exit();
	//Total = 7

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
	//print_r($tot_bill);
	//exit();
	//Total Bill =6

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
	//print_r($tot_dk);
	//exit();
	//Total DK = 0

	// target
	$tot_tar_sql = mysql_query("
		SELECT `booking_target`
		FROM `calendar`
		WHERE `staff_id` = {$tech_id}
		AND ('".$date."' BETWEEN `date_start` AND `date_finish`)
		AND `country_id` = {$_SESSION['country_default']}
	");
	$tot_tar = mysql_fetch_array($tot_tar_sql);
	//print_r($tot_tar);
	//exit();
	//Target = NULL

	/*
	 $mr_sql = mysql_query("
		SELECT *
		FROM `tech_run`
		WHERE `tech_id` = {$tech_id}
		AND `date` = '{$date}'
	");
	$mr = mysql_fetch_array($mr_sql);
	*/

	}



?>


<?php
if($_GET['success']==1){ ?>
<div class="success">Submission Successful</div>
<?php
}
?>


<?php
if($_GET['keys_success']==1){ ?>
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




<div id="new_job_success_msg" class="success" style="background-color: #ffff00; display:none;">&nbsp;</div>


<div id="searching_for_new_jobs_div" style="background-color: #ececec; display:none;"><img style="width: 30px; margin: 7px 0;" src="/images/loading.gif" /><span style="bottom: 14px; left: 8px; position: relative;">Searching New Jobs...</span> </div>


<?php

if( $tech_id!="" && $date!="" ){


$prop_address = array();
$i = 0;










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

}

?>


<style>
.fl-left > span {
    margin-right: 23px;
}
.fl-left input.addinput {
    float: none;
}
</style>





<form action="save_tech_run.php" id="tech_run_form" method="post" style="margin: auto; border-bottom: 1px solid #cccccc;">







<div class="vosch-tp">


<div style="float: none;" class="fl-left">






	<div id="tabs" class="c-tabs no-js">

		<div class="c-tabs-nav">
			<a href="#" data-tab_index="0" class="c-tabs-nav__link is-active">Setup</a>
			<?php
			if( $hasTechRun == true ){ ?>
				<a href="#" data-tab_index="1" class="c-tabs-nav__link">Details</a>
				<a href="#" data-tab_index="2" class="c-tabs-nav__link">Functions</a>
			<?php
			}
			?>
		</div>














		<!-- SETUP TAB -->
		<div class="c-tab is-active" data-tab_cont_name="setup">
			<div class="c-tab__content setup_tab_cont">


				<table class="table setup_table" style="width: auto;">
					<tr>
						<!-- Date -->
						<td>Date</td>
						<td>
							<input type="text" style="width: 85px; margin-left: 0;"  name="date" id="date" readonly="readonly" class="addinput date <?php echo ($hasTechRun==true)?'':'datepicker'; ?>" value="<?php echo ( $date!='' )?date('d/m/Y',strtotime($date)):''; ?>" />
						</td>
						<!-- Tech -->
						<td>Tech</td>
						<td>
							<?php
							if($hasTechRun==true){
								$sel_tech_sql = mysql_query("
									SELECT `StaffID`, `FirstName`, `LastName`
									FROM `staff_accounts`
									WHERE `StaffID` = {$tech_id}
								");
								$sel_tech = mysql_fetch_array($sel_tech_sql);
							?>
								<input type="text" id="tech_name" readonly="readonly" class="addinput tech_name" style="width: 120px !important;" value="<?php echo $crm->formatStaffName($sel_tech['FirstName'],$sel_tech['LastName']); ?>" />
								<input type="hidden" name="tech_id" id="tech_id" value="<?php echo $sel_tech['StaffID'] ?>" />
							<?php
							}else{ ?>
								<select name="tech_id" id="tech_id" class="tech_id" style=" width: auto; float: none;">
									<option value="">-- Select --</option>
									<?php
									$tech_sql = mysql_query("
										SELECT sa.`StaffID`, sa.`FirstName`, sa.`LastName`, sa.`is_electrician`, sa.`active` AS sa_active
										FROM `staff_accounts` AS sa
										LEFT JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
										WHERE ca.`country_id` ={$_SESSION['country_default']}
										AND sa.`Deleted` = 0
										AND sa.`ClassID` = 6
										AND sa.`active` = 1
										ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
									");
									while($tech = mysql_fetch_array($tech_sql)){ ?>

										<option value="<?php echo $tech['StaffID']; ?>" <?php echo ($tech['StaffID']==$tech_id)?'selected="selected"':''; ?>>
											<?php
												echo $crm->formatStaffName($tech['FirstName'],$tech['LastName']).( ( $tech['is_electrician'] == 1 )?' [E]':null );
											?>
										</option>

									<?php
									}
									?>
								</select>
							<?php
							}
							?>
							<input type="hidden" name="tech_accom_id" id="tech_accom_id" class="tech_accom_id" />
						</td>
						<!-- Keys -->
						<td>
							<?php
							if( $hasTechRun == true ){ ?>
								<div style="float: left; margin-right: 25px; margin-top: 5px;">
									<button type="button" style="width: 103px; text-align: left;" id="btn_keys" class="submitbtnImg blue-btn">
										<img class="inner_icon" src="images/add-button.png" /> Keys
									</button>
								</div>
							<?php
							}
							?>
						</td>
						<td>
							<?php
							if( $hasTechRun == true ){ ?>
								<style>
								#tbl_keys tr, #tbl_keys td {
									border: medium none !important;
								}
								</style>
								<div id="keys_div" style="display:none; float: left; margin-right: 25px;">
									<table style="width: auto;" id="tbl_keys">
										<tr>
											<td>Agency </td>
											<td>
												<select name="keys_agency" id="keys_agency" style="width:200px;">
													<option value="">-- Select --</option>
													<?php
													$add_keys_params = array(
														'distinct' => 1,
														'distinct_val' => 'a.`agency_id`',
														'job_rows_only' => 1
													);
													$agency_sql = getTechRunRows($tr_id,$_SESSION['country_default'],$add_keys_params);
													while($agency = mysql_fetch_array($agency_sql)){
														if( $agency['agency_id']!="" ){
															
															// display key address for agency that has it
															$agency_add_sql_str = "
															SELECT 
																a.`agency_name`,
																a.`agency_id`,
																agen_add.`id` AS agen_add_id,
																agen_add.`address_1` AS agen_add_street_num, 
																agen_add.`address_2` AS agen_add_street_name, 
																agen_add.`address_3` AS agen_add_suburb, 
																agen_add.`state` AS agen_add_state, 
																agen_add.`postcode` AS agen_add_postcode		
															FROM `agency_addresses` AS agen_add
															LEFT JOIN `agency` AS a ON agen_add.`agency_id` = a.`agency_id`
															WHERE agen_add.`agency_id` = {$agency['agency_id']}
															AND agen_add.`type` = 2
															";
															$agency_add_sql = mysql_query($agency_add_sql_str);
															$key_add_num = 1;

															$check_address_str= "SELECT `agency_addresses`.`id`, a.`agency_id`, a.`agency_name`, agency_addresses.`address_1` AS agen_add_street_num, agency_addresses.`address_2` AS agen_add_street_name, agency_addresses.`address_3` AS agen_add_suburb  FROM `agency_addresses` JOIN `property_keys` ON `agency_addresses`.`id`=`property_keys`.`agency_addresses_id` JOIN `agency` AS a ON agency_addresses.`agency_id` = a.`agency_id` JOIN `jobs` ON `property_keys`.`property_id` = `jobs`.`property_id` WHERE agency_addresses.`agency_id`={$agency['agency_id']} AND agency_addresses.`type`=2 AND jobs.`date`=CURDATE() AND jobs.`status`='Booked' GROUP BY agency_addresses.`id`";
															$check_address_sql = mysql_query($check_address_str);

															//Count Key Address
															$count_address_str= "SELECT `agency_addresses`.`id` FROM `agency_addresses` JOIN `property_keys` ON `agency_addresses`.`id`=`property_keys`.`agency_addresses_id` JOIN `agency` AS a ON agency_addresses.`agency_id` = a.`agency_id` JOIN `jobs` ON `property_keys`.`property_id` = `jobs`.`property_id` WHERE agency_addresses.`agency_id`={$agency['agency_id']} AND agency_addresses.`type`=2 AND jobs.`date`=CURDATE() AND jobs.`status`='Booked' GROUP BY agency_addresses.`id`";
															$count_address_sql = mysql_query($count_address_str);
															$count_address = mysql_num_rows($count_address_sql);
															$count_check_address = mysql_num_rows($check_address_sql);
															if( mysql_num_rows($check_address_sql) > 0 && $count_address == $count_check_address ){
																while( $check_address_row = mysql_fetch_object($check_address_sql) ){	
																	$agen_add_comb = "{$check_address_row->agen_add_street_num} {$check_address_row->agen_add_street_name}, {$check_address_row->agen_add_suburb}"; 
																	echo "<option value='$check_address_row->agency_id' data-agency_addresses_id='$check_address_row->id'>{$check_address_row->agency_name} Key #{$key_add_num} {$agen_add_comb}</option>";
																	$key_add_num++;
																}
															} else {
																echo "<option value='".$agency['agency_id']."'>".$agency['agency_name']."</option>";
																// First National added list
																if( $agency['agency_id'] == $fn_agency_main ){

																	$fn_agency_sub_sql_str = "
																		SELECT `agency_id`, `agency_name`
																		FROM `agency`
																		WHERE `agency_id` IN({$fn_agency_sub_imp})
																	";
																	$fn_agency_sub_sql = mysql_query($fn_agency_sub_sql_str);
																	while( $fn_agency_sub_row = mysql_fetch_array($fn_agency_sub_sql) ){
																		echo "<option value='".$fn_agency_sub_row['agency_id']."'>".$fn_agency_sub_row['agency_name']."</option>";
																	}
																}

																// // Vision Real Estate added list
																if( $agency['agency_id'] == $vision_agency_main ){

																	echo $vision_agency_sub_sql_str = "
																		SELECT `agency_id`, `agency_name`
																		FROM `agency`
																		WHERE `agency_id` IN({$vision_agency_sub_imp})
																	";
																	$vision_agency_sub_sql = mysql_query($vision_agency_sub_sql_str);
																	while( $vision_agency_sub_row = mysql_fetch_array($vision_agency_sub_sql) ){
																		echo "<option value='".$vision_agency_sub_row['agency_id']."'>".$vision_agency_sub_row['agency_name']."</option>";
																	}
																}

																if( mysql_num_rows($agency_add_sql) > 0 ){																								
																	while( $agency_add_row = mysql_fetch_object($agency_add_sql) ){	
																		$agen_add_comb = "{$agency_add_row->agen_add_street_num} {$agency_add_row->agen_add_street_name}, {$agency_add_row->agen_add_suburb}"; 
																		echo "<option value='$agency_add_row->agency_id' data-agency_addresses_id='$agency_add_row->agen_add_id'>{$agency_add_row->agency_name} Key #{$key_add_num} {$agen_add_comb}</option>";
																		$key_add_num++;
																	}
																}
															}
														}
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
								</div>
							<?php
							}
							?>
						</td>
					</tr>
					<tr>
						<!-- Region -->
						<td><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?></td>
						<td>
							<input type="text" readonly="readonly" name='region_ms' id='region_ms' class='addinput searchstyle vwjbdtp' />
							<?php
							// get region
							$sel_query = "DISTINCT(`region_id`)";
							$postcode_params = array(
								'sel_query' => $sel_query,
								'sub_region_id_imp' => $sub_regions,
								'active' => 1,
								'display_query' => 0
							);
							$sub_region_sql = $crm->get_sub_region($postcode_params);

							$sub_region_arr = [];
							while ( $sub_region_row = mysql_fetch_array($sub_region_sql)) {
								$sub_region_arr[] = $sub_region_row['region_id'];
							}

							if( count($sub_region_arr) > 0 ){
								$sub_region_imp = implode(",", $sub_region_arr);
							}
							$reg_fin = $sub_region_imp;

							// get region state
							$state_sql = mysql_query("
								SELECT DISTINCT(`region_state`)
								FROM `regions`
								WHERE `regions_id` IN ( {$reg_fin} )
							");
							while( $state = mysql_fetch_array($state_sql) ){
								$state_str .= ",{$state['region_state']}";
							}
							$state_fin = substr($state_str,1);
							?>
							<input type="hidden" name="selected_state" id="selected_state" class="selected_state" value="<?php echo $state_fin; ?>" />
							<input type="hidden" name="selected_regions" id="selected_regions" class="selected_regions" value="<?php echo $reg_fin; ?>" />
							<input type="hidden" name="selected_sub_regions" id="selected_sub_regions" class="selected_sub_regions" value="<?php echo $sub_regions; ?>" />
							<style>
							#region_dp_div{
								width:auto;
								border-radius: 5px;
								padding: 7px;
								position: absolute;
								left: 185px;
								top: 221px;
								background: #ffffff;
								border: 1px solid #cccccc;
								display: none;
								z-index: 99999;
							}
							.region_dp_header{
								background: #b4151b none repeat scroll 0 0;
								border-radius: 10px;
								color: #ffffff;
								padding: 6px;
								text-align: left;
							}
							#region_dp_div ul{
								list-style: outside none none;
								padding: 0;
								margin: 0;
								text-align: left !important;
							}
							.reg_db_main_reg{
								color: #b4151b;
								cursor: pointer;
								font-weight: bold;
								text-align: center;
							}
							#region_dp_div input{
								width:auto;
								float:none;
							}
							.region_wrapper{
								border-bottom: 1px solid;
								color: #b4151b;
							}
							</style>
							<div id="region_dp_div">
							<div class="region_dp_header">
							<ul>
							<?php
							// get state
							$job_status = 'To be Booked';
							$jstate_sql = mysql_query("
								SELECT DISTINCT (
									r.`region_state`
								) AS state
								FROM  `jobs` AS j
								
								LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id`
								LEFT JOIN `postcode` AS pc ON p.`postcode` = pc.`postcode`
								LEFT JOIN `sub_regions` AS sr ON pc.`sub_region_id` = sr.`sub_region_id`
								LEFT JOIN `regions` AS r ON sr.`region_id` = r.`regions_id`
								
								LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id`
								WHERE j.`status` =  '{$job_status}'
								AND p.`deleted` =0
								AND a.`status` =  'active'
								AND j.`del_job` =0
								AND a.`country_id` = {$_SESSION['country_default']}
								AND r.`region_state` != ''
								AND r.`region_state` IS NOT NULL
								ORDER BY r.`region_state`						
							");
							while($jstate =  mysql_fetch_array($jstate_sql)){

							// get state regions
							$main_reg_pc = "";
							$temp_sql = mysql_query("
								SELECT *
								FROM  `regions`
								WHERE `region_state` = '".mysql_real_escape_string($jstate['state'])."'
								AND `country_id` = {$_SESSION['country_default']}
								AND `status` = 1
							");
							while( $temp = mysql_fetch_array($temp_sql) ){
								$main_reg_pc .= ','.$jc->getSubRegionPostcodes($temp['regions_id']);
							}

							$reg_arr1 = explode(",",$main_reg_pc);
							$reg_arr2 = array_filter($reg_arr1);
							$main_region_postcodes = implode(",",$reg_arr2);
							$prCount = $jc->getMainRegionCount($_SESSION['country_default'],$main_region_postcodes,'',$job_status);
							$jcount_txt = ($prCount>0)?"({$prCount})":'';
							if( $prCount>0 ){
							?>
								<li>
									<input type="checkbox" name="state_ms[]" class="state_ms" value="<?php echo $jstate['state']; ?>" /> <span><?php echo $jstate['state']; ?> <?php echo $jcount_txt ?></span>
									<input type="hidden" value="<?php echo $main_region_postcodes; ?>" />
								</li>
							<?php
								}
							}
							?>
							</ul>
							</div>
							<div class="region_dp_body">
							</div>
							</div>
						</td>
						<!-- Start -->
						<td>Start</td>
						<td>
							<select name="start_point" id="start_point" style="float:none; width:100px;">
								<option value="">-- Select --</option>
								<?php
								$acco_sql = mysql_query("
									SELECT *
									FROM `accomodation`
									WHERE `country_id` = {$_SESSION['country_default']}
									ORDER BY `name`
								");
								while($acco = mysql_fetch_array($acco_sql)){ ?>
									<option value="<?php echo $acco['accomodation_id']; ?>" <?php echo ($acco['accomodation_id']==$tr['start'])?'selected="selected"':''; ?>><?php echo $acco['name']; ?></option>
								<?php
								}
								?>
							</select>
						</td>
						<!-- Supplier -->
						<td>
							<?php
							if( $hasTechRun == true ){ ?>
								<div style="float: left; margin-right: 25px; margin-top: 5px;">
									<button type="button" style="width: 103px; text-align: left;" id="btn_supplier" class="submitbtnImg blue-btn">
										<img class="inner_icon" src="images/add-button.png" /> Supplier
									</button>
								</div>
							<?php
							}
							?>
						</td>
						<td>
							<?php
							if( $hasTechRun == true ){ ?>
								<style>
								#tbl_supplier tr, #tbl_supplier td {
									border: medium none !important;
								}
								</style>
								<div id="supplier_div" style="display:none; float: left; margin-right: 25px;">
									<table style="width: auto;" id="tbl_supplier">
										<tr>
											<td>Supplier</td>
											<td>
												<select name="supplier" id="supplier" style="width:200px;">
													<option value="">-- Select --</option>
													<?php
													$sup_sql = mysql_query("
														SELECT *
														FROM `suppliers`
														WHERE `country_id` = {$_SESSION['country_default']}
														AND `status` = 1
														ORDER BY `company_name` ASC
													");
													while($sup = mysql_fetch_array($sup_sql)){
														if( $sup['suppliers_id']!="" ){
													?>
														<option value="<?php echo $sup['suppliers_id'];  ?>"><?php echo $sup['company_name'];  ?></option>
													<?php
														}
													}
													?>
												</select>
											</td>
											<td>
												<input type="hidden" name="hid_supplier_submit" id="hid_supplier_submit" value="0" />
												<button type="button" id="btn_supplier_submit" class="submitbtnImg blue-btn">Submit</button>
											</td>
										</tr>
									</table>
								</div>
							<?php
							}
							?>
						</td>
					</tr>
					<tr>
						<!-- Agency -->
						<td>Agency</td>
						<td>
							<?php
							if( $hasTechRun == true ){ ?>

								<div id="agency_filter_main_div">
									<input type="text" readonly="readonly" name='agency_ms' id='agency_ms' class='addinput searchstyle vwjbdtp' style='margin-left: 8px;' value="<?php echo "{$agency_filter_count} Selected"; ?>" />
									<style>
									#agency_dp_div{
									width:auto;
									border-radius: 5px;
									padding: 7px;
									position: absolute;
									left: 367px;
									top: 221px;
									background: #ffffff;
									border: 1px solid #cccccc;
									display: none;
									z-index: 99999;
									}
									.region_dp_header{
									background: #b4151b none repeat scroll 0 0;
									border-radius: 10px;
									color: #ffffff;
									padding: 6px;
									text-align: left;
									}
									#agency_dp_div ul{
									list-style: outside none none;
									padding: 0;
									margin: 0;
									text-align: left !important;
									}
									.reg_db_main_reg{
									color: #b4151b;
									cursor: pointer;
									font-weight: bold;
									text-align: center;
									}
									#agency_dp_div input{
									width:auto;
									float:none;
									}
									.region_wrapper{
									border-bottom: 1px solid;
									color: #b4151b;
									}
									</style>
									<div id="agency_dp_div">




									</div>

								</div>

							<?php
							}
							?>
						</td>
						<!-- End -->
						<td>End</td>
						<td>
							<select name="end_point" id="end_point" style="float:none; width:100px;">
								<option value="">-- Select --</option>
								<?php
								$acco_sql = mysql_query("
									SELECT *
									FROM `accomodation`
									WHERE `country_id` = {$_SESSION['country_default']}
									ORDER BY `name`
								");
								while($acco = mysql_fetch_array($acco_sql)){ ?>
									<option value="<?php echo $acco['accomodation_id']; ?>" <?php echo ($acco['accomodation_id']==$tr['end'])?'selected="selected"':''; ?>><?php echo $acco['name']; ?></option>
								<?php
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<!-- Calendar -->
						<td>Calendar</td>
						<td>
							<div style="float: left; margin-right: 7px;">
							<input type="text" name="calendar_name" id="calendar_name" maxlength="19" class="addinput" style="width: 120px;" value="<?php echo $cal_name; ?>" />
							<input type="hidden" name="calendar_id" id="calendar_id" value="<?php echo $cal_id; ?>" />
							</div>
							<?php
							if( $hasTechRun==true ){ ?>
								<button class="submitbtnImg blue-btn" id="btn_cal" type="button" style="display:none;">Update</button>
							<?php
							}
							?>
						</td>
						<!-- Accom -->
						<td>Accom</td>
						<td>
							<div style="float: left; margin-right: 25px;" id="accom_div">
								 <select name="accomodation" id="accomodation" style="float:none; width: 100px;">
									<option value="">None</option>
									<option value="0" <?php echo ( is_numeric($cal['accomodation']) && $cal['accomodation']==0)?'selected="selected"':''; ?>>Required</option>
									<option value="2" <?php echo ($cal['accomodation']==2)?'selected="selected"':''; ?>>Pending</option>
									<option value="1" <?php echo ($cal['accomodation']==1)?'selected="selected"':''; ?>>Booked</option>
								</select>
								<div id="sel_acco" style="display:<?php echo ($cal['accomodation']==1||$cal['accomodation']==2)?'block':'none'; ?>;">
								 <select name="accomodation_id" id="accomodation_id" style="width: 131px; margin-top: 8px;">
									<?php
									// sort by name ASC
									$acco_sql2 = mysql_query("
										SELECT *
										FROM `accomodation`
										ORDER BY `name` ASC
									");
									while($acco2 = mysql_fetch_array($acco_sql2)){ ?>
									<option value="<?php echo $acco2['accomodation_id']; ?>" <?php echo ($acco2['accomodation_id']==$cal['accomodation_id'])?'selected="selected"':''; ?>><?php echo $acco2['name']; ?></option>
									<?php
									}
									?>
								</select>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<!-- Display -->
						<td>Display</td>
						<td>
							<?php
							if( $hasTechRun == true ){ ?>

								<select name="display_num" id="display_num" style="width: 140px !important;">
									<option value="">All Jobs</option>
									<option value="25" <?php echo ($display==25)?'selected="selected"':''; ?>>25 Jobs</option>
									<option value="50" <?php echo ($display==50)?'selected="selected"':''; ?>>50 Jobs</option>
									<option value="75" <?php echo ($display==75)?'selected="selected"':''; ?>>75 Jobs</option>
									<option value="100" <?php echo ($display==100)?'selected="selected"':''; ?>>100 Jobs</option>
								</select>

							<?php
							}
							?>
						</td>
						<!-- Booking Staff -->
						<td>Booking Staff</td>
						<td>
							<?php
							$sa_sql = mysql_query("
								SELECT *
								FROM staff_accounts AS sa
								LEFT JOIN staff_classes AS sc ON sa.`ClassID` = sc.`ClassID`
								INNER JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
								WHERE sa.`Deleted` = 0
								AND sa.`active` = 1
								AND ca.`country_id` = {$_SESSION['country_default']}
								ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
							");
							?>
							<select name="booking_staff" id="booking_staff" style="width:131px;">
								<option value="">--Select--</option>
								<?php
								while( $sa = mysql_fetch_array($sa_sql) ){ ?>
									<option value="<?php echo $sa['StaffID'] ?>" <?php echo ($sa['StaffID']==$cal['booking_staff'])?'selected="selected"':'' ?>><?php echo "{$sa['FirstName']} {$sa['LastName']}"; ?></option>
								<?php
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<!-- Save -->
						<td></td>
						<td>
							<input type="hidden" id="tr_already_exist" value="0" />
							<button type="button" name="btn_save_sub_region" class="submitbtnImg blue-btn" id="btn_save_sub_region">
								<img class="inner_icon" src="images/save-button.png" />
								Save
							</button>
						</td>
						<!-- Save Start/End -->
						<td></td>
						<td>
							<?php
							if( $hasTechRun==true ){ ?>
								<!--<button type="button" class="submitbtnImg blue-btn" id="btn_set_start_end_save">
									<img class="inner_icon" src="images/save-button.png" />
									Start/End
								</button>-->
							<?php
							}
							?>
						</td>
					</tr>
				</table>


			</div>
		</div>


		<?php
			if( $hasTechRun == true ){ ?>

				<!-- Details -->
				<div class="c-tab is-active" data-tab_cont_name="details">
					<div class="c-tab__content details_tab_cont">


						<?php
						if( $hasTechRun==true ){ ?>

							<!-- COLOUR TABLE -->
							<div style="float: left; margin-right: 10px;">
								<table class="table colour_tbl">
									<tr>
										<th>Colour</th>
										<th>Time</th>
										<th>Jobs</th>
										<th>NO Keys</th>
										<th>Status</th>
									</tr>
									<?php
									$hc_sql = getRowColor();
									while( $hc = mysql_fetch_array($hc_sql) ){

									// get saved colour table
									$sql_colour_tbl = getSavedColourTable($tr_id,$hc['tech_run_row_color_id']);
									$colour = mysql_fetch_array($sql_colour_tbl);
									$ct_time = $colour['time'];
									$ct_jobs = $colour['jobs_num'];
									$ct_no_keys_chk = $colour['no_keys'];
									$ct_booking_status = $colour['booking_status'];
									$isFullyBooked = 0;


									$status_dif_txt = '';
									if($ct_booking_status!=''){

										if($ct_booking_status=='FULL'){
											$status_dif_txt = "<span class='ct_full'>(FULL)</span>";
											$isFullyBooked = 1;
										}else{
											$status_dif_txt = "({$ct_booking_status})";
										}

									}
									?>
										<tr id="ct_row_id_<?php echo $hc['tech_run_row_color_id']; ?>" class="ct_row">
											<td style="background-color:<?php echo $hc['hex']; ?>">
												<input type="hidden" class="ct_trrc_id" value="<?php echo $hc['tech_run_row_color_id']; ?>" />
												<input type="hidden" class="ct_booked_job" value="0" />
												<input type="hidden" class="ct_fully_booked" value="<?php echo $isFullyBooked; ?>" />
											</td>
											<td><input type="text" class="addinput ct_time" style="width: 100px;" value="<?php echo $ct_time; ?>" /></td>
											<td><input type="text" class="addinput ct_jobs" style="width: 35px;" value="<?php echo $ct_jobs; ?>" /></td>
											<td>
												<input type="checkbox" class="ct_no_keys_chk" <?php echo ($ct_no_keys_chk==1)?'checked="checked"':''; ?> />
												<img src="images/cross_red.png" class="redCross" style="<?php echo ($ct_no_keys_chk==1)?'display:inline;':'display:none;'; ?>" />
											</td>
											<td class="ct_status"><?php echo $status_dif_txt; ?></td>
										</tr>
									<?php
									}
									?>
								</table>
							</div>

							<div style="float:left; margin-right: 10px;">



								<!-- REGIONS TABLE -->
								<div style="text-align: left;">

									<table class="table">
										<tr>
											<th style="width:200px;">Regions you are booking:</th>
											<th class="jRightBorder">&nbsp;</th>
											<th style="width:150px;">Alternate Days: </th>
										</tr>
										<?php

										if( $tr['sub_regions'] != '' ){

											// get sub region
											$sel_query = "
											r.`region_name`,

											sr.`sub_region_id`,
											sr.`subregion_name`
											";
											$sub_region_params = array(
												'sel_query' => $sel_query,
												'sub_region_id_imp' => $tr['sub_regions'],
												'active' => 1,
												'display_query' => 0
											);
											$sub_region_sql = $crm->get_sub_region($sub_region_params);

											if( mysql_num_rows($sub_region_sql)>0 ){
												?>

												<?php
												while( $sub_region_row = mysql_fetch_array($sub_region_sql) ){

													// get all postcode that belong to a sub region
													$postcodes_imp = null;

													$sel_query = "pc.`postcode`";
													$postcode_params = array(
														'sel_query' => $sel_query,
														'sub_region_id' => $sub_region_row['sub_region_id'],
														'deleted' => 0,
														'display_query' => 0
													);
													$postcode_sql = $crm->get_postcodes($postcode_params);

													$postcodes_arr = [];
													while ( $postcode_row = mysql_fetch_array($postcode_sql)) {
														$postcodes_arr[] = $postcode_row['postcode'];
													}

													if( count($postcodes_arr) > 0 ){
														$postcodes_imp = implode(",", $postcodes_arr);
													}

													$params = array( 'postcode_regions' => $postcodes_imp );
													$pcr_num_sql = getTechRunRows($tr_id,$_SESSION['country_default'],$params);
													$pcr_num =  mysql_num_rows($pcr_num_sql);
													?>
													<tr>
														<td><?php echo "{$sub_region_row['region_name']}/{$sub_region_row['subregion_name']}"; ?></td>
														<td class="jRightBorder">(<?php echo $pcr_num; ?>)</td>
														<td>
															<?php

															// fetch all future STR
															$future_str_sql = mysql_query("
																SELECT *
																FROM  `tech_run`
																WHERE `sub_regions` LIKE '%{$sub_region_row['sub_region_id']}%'
																AND `date` > '".date('Y-m-d')."'
																AND `date` != '{$date}'
																AND `country_id` = {$_SESSION['country_default']}
															");
															$fcount = 0;
															while( $future_str = mysql_fetch_array($future_str_sql) ){

																$reg_arr = explode(",",$future_str['sub_regions']);

																if( in_array($sub_region_row['sub_region_id'], $reg_arr) ){

																echo ($fcount!=0)?', ':'';

																?><a href="/set_tech_run.php?tr_id=<?php echo $future_str['tech_run_id'] ?>"><?php echo date('D d/m',strtotime($future_str['date'])); ?></a><?php
																$fcount++;

																}else{
																	$no_set_date_flag = 1;
																}

															}


															if( $fcount==0 ){
																echo "No Days scheduled";
															}
															?>
														</td>
													</tr>
												<?php
												}

											}

										}
										?>
									</table>

								</div>

								<!-- NOTES TABLE -->
								<div style="text-align: left; margin-top: 10px;">

									<?php
									if( $tr['notes_updated_by']!='' ){
										$sa_sql3 = mysql_query("
											SELECT *
											FROM `staff_accounts` AS sa
											WHERE sa.`StaffID` ={$tr['notes_updated_by']}
										");
										$sa3 = mysql_fetch_array($sa_sql3);

										$notes_ts = ( $tr['notes_updated_ts']!='' )?date('d/m/Y H:i',strtotime($tr['notes_updated_ts'])):'';



									}
									?>

									<table class="table jNoBorder">
										<tr>
											<th>
												Notes
												<a target="_blank" id="agencyNotesLink" href="agency_booking_notes.php">
													(IMPORTANT - Read Agency Notes)
												</a>
											<th>
												<div class='jtimeStamp'>
													<?php echo "{$sa3['FirstName']} ".substr($sa3['LastName'],0,1).". <span>{$notes_ts}</span>"; ?>
												</div>
											</th>
										</tr>
										<tr>
											<td colspan="2">
												<textarea class="addtextarea" name="notes" id="notes"><?php echo $tr['notes']; ?></textarea>
											</td>
										</tr>
									</table>

								</div>


							</div>

							<div style="float:left; margin-right: 10px;">

									<table class="table details_marker_tbl">
										<tr>
											<td data-tr_mark-type="run_set" data-tr_mark-name="Run Set" data-tr_mark-val="<?php echo $tr['run_set']; ?>" class='run_status <?php echo ( ($tr['run_set']==1)?'greenBgButton':'' ); ?>'>
												<?php
												if( $tr['run_set']==1 ){ ?>
													<img src="images/done-button.png" class="details_marker_tick" />
												<?php
												}
												?>
												Run Set
											</td>
										</tr>
										<tr>
											<td data-tr_mark-type="run_coloured" data-tr_mark-name="Run Coloured" data-tr_mark-val="<?php echo $tr['run_coloured']; ?>" class='run_status <?php echo ( ($tr['run_coloured']==1)?'greenBgButton':'' ); ?>'>
												<?php
												if( $tr['run_coloured']==1 ){ ?>
													<img src="images/done-button.png" class="details_marker_tick" />
												<?php
												}
												?>
												Run Coloured
											</td>
										</tr>
										<tr>
											<td data-tr_mark-type="ready_to_book" data-tr_mark-name="Ready to Book" data-tr_mark-val="<?php echo $tr['ready_to_book']; ?>" class='run_status <?php echo ( ($tr['ready_to_book']==1)?'greenBgButton':'' ); ?>'>
												<?php
												if( $tr['ready_to_book']==1 ){ ?>
													<img src="images/done-button.png" class="details_marker_tick" />
												<?php
												}
												?>
												Ready to Book
											</td>
										</tr>
										<tr>
											<td data-tr_mark-type="first_call_over_done" data-tr_mark-name="1st Call Over Done" data-tr_mark-val="<?php echo $tr['first_call_over_done']; ?>" class='run_status <?php echo ( ($tr['first_call_over_done']==1)?'greenBgButton':'' ); ?>'>
												<?php
												if( $tr['first_call_over_done']==1 ){ ?>
													<img src="images/done-button.png" class="details_marker_tick" />
												<?php
												}
												?>
												1st Call Over Done
											</td>
										</tr>

										<tr>
											<td data-tr_mark-type="run_reviewed" data-tr_mark-name="Run Reviewed" data-tr_mark-val="<?php echo $tr['run_reviewed']; ?>" class='run_status <?php echo ( ($tr['run_reviewed']==1)?'greenBgButton':'' ); ?>'>
												<?php
												if( $tr['run_reviewed']==1 ){ ?>
													<img src="images/done-button.png" class="details_marker_tick" />
												<?php
												}
												?>
												Run Reviewed
											</td>
										</tr>
										<tr>
											<td data-tr_mark-type="finished_booking" data-tr_mark-name="2nd Call Over Done" data-tr_mark-val="<?php echo $tr['finished_booking']; ?>" class='run_status <?php echo ( ($tr['finished_booking']==1)?'greenBgButton':'' ); ?>'>
												<?php
												if( $tr['finished_booking']==1 ){ ?>
													<img src="images/done-button.png" class="details_marker_tick" />
												<?php
												}
												?>
												2nd Call Over Done
											</td>
										</tr>


										<tr>
											<td data-tr_mark-type="additional_call_over" data-tr_mark-name="Extra Call Over" data-tr_mark-val="<?php echo $tr['additional_call_over']; ?>" class='run_status <?php echo ( ($tr['additional_call_over']==1)?'greenBgButton':'' ); ?>'>
												<?php
												if( $tr['additional_call_over']==1 ){ ?>
													<img src="images/done-button.png" class="details_marker_tick" />
												<?php
												}
												?>
												Extra Call Over
											</td>
										</tr>
										<tr>
											<td data-tr_mark-type="additional_call_over_done" data-tr_mark-name="Extra Call Over Done" data-tr_mark-val="<?php echo $tr['additional_call_over_done']; ?>" class='run_status <?php echo ( ($tr['additional_call_over_done']==1)?'greenBgButton':'' ); ?>'>
												<?php
												if( $tr['additional_call_over_done']==1 ){ ?>
													<img src="images/done-button.png" class="details_marker_tick" />
												<?php
												}
												?>
												Extra Call Over Done
											</td>
										</tr>

										<tr>
											<td data-tr_mark-type="ready_to_map" data-tr_mark-name="Run Ready to Map - Please Review" data-tr_mark-val="<?php echo $tr['ready_to_map']; ?>" class='run_status <?php echo ( ($tr['ready_to_map']==1)?'greenBgButton':'' ); ?>'>
												<?php
												if( $tr['ready_to_map']==1 ){ ?>
													<img src="images/done-button.png" class="details_marker_tick" />
												<?php
												}
												?>
												Run Ready to Map
											</td>
										</tr>

										<tr>
											<td data-tr_mark-type="run_complete" data-tr_mark-name="Run Mapped" data-tr_mark-val="<?php echo $tr['run_complete']; ?>" class='run_status <?php echo ( ($tr['run_complete']==1)?'greenBgButton':'' ); ?>'>
												<?php
												if( $tr['run_complete']==1 ){ ?>
													<img src="images/done-button.png" class="details_marker_tick" />
												<?php
												}
												?>
												Run Mapped
											</td>
										</tr>
										
										<!-- Morning Call Over -->
										<tr>
											<td data-tr_mark-type="morning_call_over" data-tr_mark-name="Morning Call Over" data-tr_mark-val="<?php echo $tr['morning_call_over']; ?>" class='run_status <?php echo ( ($tr['morning_call_over']==1)?'greenBgButton':'' ); ?>'>
												<?php
												if( $tr['morning_call_over']==1 ){ ?>
													<img src="images/done-button.png" class="details_marker_tick" />
												<?php
												}
												?>
												Morning Call Over
											</td>
										</tr>


										<?php
										// only show if run mapped
										if( $tr['run_complete'] == 1 ){ ?>
										
											<tr>
												<td data-tr_mark-type="no_more_jobs" data-tr_mark-name="FULL - No More Jobs" data-tr_mark-val="<?php echo $tr['no_more_jobs']; ?>" class='run_status <?php echo ( ($tr['no_more_jobs']==1)?'greenBgButton':'' ); ?>'>
													<?php
													if( $tr['no_more_jobs']==1 ){ ?>
														<img src="images/done-button.png" class="details_marker_tick" />
													<?php
													}
													?>
													FULL - No More Jobs
												</td>
											</tr>

										<?php
										}
										?>										
									</table>



							</div>


							<div style="float:left; text-align: left;">
								<h3>Log:</h3>
								<table>
									<thead>
										<tr>
											<th>Description</th>
											<th>Who</th>
											<th>Date</th>
										</tr>
									</thead>
									<tbody>
										<?php
										// tech run logs
										$tech_run_logs_sql = mysql_query("
										SELECT 
											trl.`description`,
											trl.`created`,

											sa.`FirstName`,
											sa.`LastName`
										FROM `tech_run_logs` AS trl
										LEFT JOIN `staff_accounts` AS sa ON trl.`created_by` = sa.`StaffID`
										WHERE trl.`tech_run_id` = {$tr_id}
										");
										while( $trl_row = mysql_fetch_object($tech_run_logs_sql) ){ ?>
											<tr>
												<td><?php echo $trl_row->description; ?></td>
												<td><?php echo $crm->formatStaffName($trl_row->FirstName, $trl_row->LastName); ?></td>
												<td><?php echo date('d/m/Y H:i',strtotime($trl_row->created)); ?></td>
											</tr>
										<?php
										}
										?>										
									</tbody>
								</table>
							</div>


							<div class="view_map_div" style="float:left; text-align: left;">

								<table class="table view_map" style="margin-bottom: 10px;">
									<tr>

										<!--
										<td>View Map: </td>
										-->

										<!--
										<td>
											<?php
											$domain = $_SERVER['SERVER_NAME'];
											$dev_str = (strpos($domain,"crmdev")===false)?'':'_dev';
											?>
											<a href="<?php echo PUBlIC_MAP_DOMAIN; ?>/tech_run<?php echo $dev_str; ?>.php?api_key=sats123&tr_id=<?php echo $tr_id; ?>&country_id=<?php echo $_SESSION['country_default']; ?>">
												<img src="/images/google_map/main_pin_icon.png">
											</a>
										</td>
										-->

										<td>Map</td>
										<td>
											<?php
											// STR map CI link
											$crm_ci_page = "/tech_run/map/?tr_id={$tr_id}";
											$str_map_ci_link = $crm->crm_ci_redirect($crm_ci_page);
											?>
											<a href="<?php echo $str_map_ci_link; ?>">
												<img src="/images/google_map/main_pin_icon.png">
											</a>
										</td>


										<td>Runsheet</td>
										<td>
											<?php
											// STR map CI link
											$crm_ci_page = "/tech_run/run_sheet_admin/{$tr_id}";
											$str_map_ci_link = $crm->crm_ci_redirect($crm_ci_page);
											?>
											<a href="<?php echo $str_map_ci_link; ?>">
												<img src="/images/google_map/main_pin_icon.png">
											</a>
										</td>


									</tr>
								</table>

								<table class="table">
									<tr><td>Booked</td><td><input type='text' style='width: 30px; color: red;' class='addinput' value='<?php echo $tot_job['jcount']; ?>' readonly='readonly' /></td></tr>
									<tr><td>Door Knocks</td><td><input type='text' style='width: 30px; color: red;' class='addinput' value='<?php echo $tot_dk['jcount']; ?>' readonly='readonly' /></td></tr>
									<tr><td>Billables</td><td><input type='text' style='width: 30px; color: red;' class='addinput' value='<?php echo $tot_bill['jcount']; ?>' readonly='readonly' /></td></tr>
									<tr>
										<td colspan="100%">
											<div>
												<button type="button" style="width: 170px; text-align: left;" id="btn_sort_by_color" class="submitbtnImg btn_sort_by_color blue-btn">
													<img class="inner_icon" src="images/sort-button.png" />
													Sort By Colour
												</button>
											</div>
										</td>
									</tr>
									<tr>
										<td colspan="100%">
											<div>
												<button type="button" style="width: 170px; text-align: left;" id="btn_select_uncolored" class="submitbtnImg blue-btn btn_select_uncolored">
													<img class="inner_icon" src="images/select-button.png" />
													<span class="btn_select_uncolored_span">Select Uncoloured</span>
												</button>
											</div>
										</td>
									</tr>
									<tr>
										<td colspan="100%">
											<div>
												<button type="button" style="width: 170px; text-align: left;" id="btn_refresh" class="submitbtnImg blue-btn btn_refresh">
													<img class="inner_icon" src="images/rebook.png" />
													<span class="btn_refresh_span">Refresh</span>
												</button>
											</div>
										</td>
									</tr>
								</table>

							</div>



						<?php
						}
						?>


















							<?php
							$trr_hid_sql = mysql_query("
								SELECT *
								FROM `tech_run_rows`
								WHERE `tech_run_id` = '{$tr_id}'
								AND `hidden` = 1
							");
							//$trr_hid_count = mysql_num_rows($trr_hid_sql);
							if( $trr_hid_count >0 ){ ?>
								<div style="float: right; margin-top: 26px; margin-right: 10px;">
									<button type="button" id="btn_show_row" class="submitbtnImg">Show <?php echo $trr_hid_count; ?> Hidden By User</button>
								</div>
							<?php
							}
							?>


							<div style="clear:both;"></div>







					</div>
				</div>



				<!-- FUNCTIONS TAB -->
				<div class="c-tab" data-tab_cont_name="functions">
					<div class="c-tab__content functions_tab_cont">




					<?php
					if( mysql_num_rows($tr_sql)>0 ){ ?>



						<div class="sort_by_div" style="float:left; margin-right:9px; text-align:left;">

							<div>
								<button class="submitbtnImg blue-btn" id="btn_sort_suburb" type="button" style="margin-bottom: 3px;">
									<img class="inner_icon" src="images/sort-button.png" />
									Sort By Suburb
								</button>
							</div>

							<div>
								<button class="submitbtnImg blue-btn" id="btn_sort_street" type="button" style="margin-bottom: 3px;">
									<img class="inner_icon" src="images/sort-button.png" />
									Sort By Street
								</button>
							</div>

							<div>
								<button class="submitbtnImg btn_sort_by_color blue-btn" id="btn_sort_by_color" type="button">
									<img class="inner_icon" src="images/sort-button.png" />
									Sort By Colour
								</button>
							</div>

						</div>


						<?php
						  //if (in_array($_SESSION['USER_DETAILS']['StaffID'], $vip)){ ?>
								<!--<div style="float:right; margin-right:9px;">
									<button class="submitbtnImg blue-btn" id="btn_sort_by_distance" type="button">Sort By Distance</button>
								</div>
								-->
						  <?php
						  //}
						?>


						<!--<div style="float:right; margin-right:9px;">
							<button class="submitbtnImg green-btn" id="btn_distance_to_agency" type="button">DISPLAY distance to agency</button>
						</div>-->


						<div style="float: left; margin-right: 9px;">
						<?php
						if( $show_hidden==1 ){ ?>

								<button type="button" id="btn_hide_hidden_rows" class="submitbtnImg">
									<img class="inner_icon" src="images/hide-button.png" /> Hide
									<span id="hiddenRowsCount_span"></span> Jobs
								</button>

						<?php
						}else{ ?>


								<button type="button" id="btn_show_hidden_rows" class="submitbtnImg" style="background-color:#dedede;">
									<img class="inner_icon" src="images/show-button.png" />
									<span id="hiddenRowsCount_span"></span> Hidden Jobs
								</button>

								<?php
								// get job types
								$job_type_sql = mysql_query("
								SELECT *
								FROM job_type
								");
								?>

								<div style="margin-top: 66px;">
									<select name='select_job_type' id='select_job_type' class='vw-jb-sel' style="width: auto !important; margin-bottom: 2px;">
										<option value=''>----</option>
										<?php
										while( $job_row = mysql_fetch_array($job_type_sql) ){ ?>
											<option data-attrTT="<?php echo str_replace(' ','_',$job_row['job_type']) ?>" value='<?php echo $job_row['job_type']; ?>' <?php echo ( $sel_job_type == $job_row['job_type'] )?'selected':null; ?>><?php echo $job_row['job_type']; ?></option>
										<?php
										}
										?>
										<option data-attrTT="Electrician_Only" value='Electrician Only' <?php echo ( $sel_job_type == 'Electrician Only' )?'selected':null; ?>><?php echo "Electrician Only"; ?></option>
									</select>
								</div>

								<?php
								$add_keys_params = array(
									'distinct' => 1,
									'distinct_val' => 'a.`agency_id`',
									'job_rows_only' => 1
								);
								$agency_sql = getTechRunRows($tr_id,$_SESSION['country_default'],$add_keys_params);
								?>
								<div id="select_agency_jobs_div">

									<select name="jobs_per_agency_select" id="jobs_per_agency_select" class='vw-jb-sel' style="width: 147px;">
										<option value="">----</option>
										<?php
										while($agency = mysql_fetch_array($agency_sql)){
											if( $agency['agency_id']!="" ){
										?>
											<option value="<?php echo $agency['agency_id'];  ?>"><?php echo $agency['agency_name'];  ?></option>
										<?php
												// First National added list
												if( $agency['agency_id'] == $fn_agency_main ){

													$fn_agency_sub_sql_str = "
														SELECT `agency_id`, `agency_name`
														FROM `agency`
														WHERE `agency_id` IN({$fn_agency_sub_imp})
													";
													$fn_agency_sub_sql = mysql_query($fn_agency_sub_sql_str);
													while( $fn_agency_sub_row = mysql_fetch_array($fn_agency_sub_sql) ){
													?>
														<option value="<?php echo $fn_agency_sub_row['agency_id'];  ?>"><?php echo $fn_agency_sub_row['agency_name'];  ?></option>
													<?php
													}
												}


												// // Vision Real Estate added list
												if( $agency['agency_id'] == $vision_agency_main ){

													echo $vision_agency_sub_sql_str = "
														SELECT `agency_id`, `agency_name`
														FROM `agency`
														WHERE `agency_id` IN({$vision_agency_sub_imp})
													";
													$vision_agency_sub_sql = mysql_query($vision_agency_sub_sql_str);
													while( $vision_agency_sub_row = mysql_fetch_array($vision_agency_sub_sql) ){
													?>
														<option value="<?php echo $vision_agency_sub_row['agency_id'];  ?>"><?php echo $vision_agency_sub_row['agency_name'];  ?></option>
													<?php
													}
												}

											}
										}
										?>
									</select>

								</div>

						<?php
						}
						?>
						</div>

						<div style="float: left; margin-right: 9px; text-align: left;">

							<div>
								<button type="button" id="btn_select_uncolored" class="submitbtnImg blue-btn btn_select_uncolored">
									<img class="inner_icon" src="images/select-button.png" />
									<span class="btn_select_uncolored_span">Select Uncoloured</span>
								</button>
							</div>

							<div>
								<button type="button" class="submitbtnImg blue-btn" id="btn_first_visit" >
									<img class="inner_icon" src="images/select-button.png" />
									<span class="btn_first_visit_span">Select First Visit</span>
								</button>
							</div>

							<div>
								<button type="button" class="submitbtnImg blue-btn" id="btn_escalate_jobs" >
									<img class="inner_icon" src="images/select-button.png" />
									<span class="btn_escalate_jobs_span">Select Escalate Jobs</span>
								</button>
							</div>

							<!--
							<div>
								<button type="button" class="submitbtnImg blue-btn" id="btn_select_240v_jobs" >
									<img class="inner_icon" src="images/select-button.png" />
									<span class="btn_select_240v_jobs_span">Select 240v Jobs</span>
								</button>
							</div>
							-->

							<div>
								<button type="button" class="submitbtnImg blue-btn" id="select_job_type_btn">
									<img class="inner_icon" src="images/select-button.png" />
									<span class="select_job_type_span">Select Job Type</span>
								</button>
							</div>

							<button type="button" class="submitbtnImg blue-btn" id="select_agency_jobs" style="float: left;">
								<img class="inner_icon" src="images/select-button.png" />
								<span class="select_agency_jobs">Select Agency Jobs</span>
							</button>

							<div>
								<button type="button" class="submitbtnImg blue-btn" id="btn_select_no_tenant_details" >
									<img class="inner_icon" src="images/select-button.png" />
									<span class="select_no_tenant_details_span">Select No Tenant Details</span>
								</button>
							</div>



							<div style="clear:both;"></div>

							<button type="button" class="submitbtnImg blue-btn" id="select_holiday_rental">
								<img class="inner_icon" src="images/select-button.png" />
								<span class="select_holiday_rental_span">Select Holiday Rental</span>
							</button>



						</div>


						<div style="float: left; margin-right:9px;">
							<button class="submitbtnImg green-btn" id="btn_EN" type="button">
							<img class="inner_icon" src="images/entry-button.png" />
							<span class="btn_en_inner_txt">Entry Notice</span>
							</button>
							<input type="hidden" id="btn_EN_hidden" value="0" />
						</div>

						<div style="float: left; margin-right:9px;">
						<table class="table view_map" style="margin-bottom: 10px;">
							<tr>
								<td>Map</td>
								<td>
									<?php
									// STR map CI link
									$crm_ci_page = "/tech_run/map/?tr_id={$tr_id}";
									$str_map_ci_link = $crm->crm_ci_redirect($crm_ci_page);
									?>
									<a href="<?php echo $str_map_ci_link; ?>">
										<img src="/images/google_map/main_pin_icon.png">
									</a>
								</td>
							</tr>
						</table>
						</div>



						<div style="float:right; margin-right: 9px;">

							<button class="submitbtnImg" id="btn_delete_mp" type="button">
								<img class="inner_icon" src="images/cancel-button.png" />Delete
							</button>

						</div>




					<?php
					}
					?>



					</div>
				</div>

			<?php
			}
			?>






	</div>

	<script src="js/responsive_tabs.js"></script>
	<script>
	  var myTabs = tabs({
		el: '#tabs',
		tabNavigationLinks: '.c-tabs-nav__link',
		tabContentContainers: '.c-tab'
	  });

	  myTabs.init();

	  myTabs.goToTab(<?php echo ($hasTechRun == true)?1:0; ?>);
	</script>







	<input type="hidden" name="tr_id" value="<?php echo $tr_id; ?>" />




</div>

</div>


<div style="clear:both;"></div>




</form>

<?php

if($hasTechRun==true){

?>

<form method="post" id="jform">
<table id="tbl_maps" border=0 cellspacing=0 cellpadding=5 width=100% class="table-left tbl-fr-red">
<tr class="nodrop nodrag str_header_row" bgcolor="#b4151b" style="border-bottom: 1px solid #B4151B !important;">
<th class="EN_hide_elem">#</th>
<th style="width:30px;">&nbsp;</th>
<th>Details</th>
<th>Deadline</th>
<th>
	<div class="tbl-tp-name colorwhite bold">Age</div>
	<a href="<?php echo $_SERVER['PHP_SELF'] ?>?tr_id=<?php echo $tr_id; ?>&age=<?php echo $new_order_age; ?>"> 
		<div class="arw-std-<?php echo ($age_sort=='asc')?'up':'dwn'; ?> arrow-<?php echo ($age_sort=='asc')?'up':'dwn'; ?>-<?php echo ($sort=='j.created')?'active':''; ?>"></div>
	</a>
</th>
<th class="EN_hide_elem" style="width: 120px;">Notes</th>
<th class="EN_hide_elem">Time</th>
<th style="width: 140px;">Status</th>
<th>Job Type</th>
<th>Service</th>
<th>DK</th>
<th>Address</th>
<th><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?></th>
<th>Agency</th>
<th style="width: 15%;">Job Comments</th>
<th style="width: 10%;">Property Comments</th>
<th class="EN_show_elem">Alarms Required</th>
<th style="width: 10%;">Preferred Time</th>
<th class="DTA_elem">Distance to agency</th>
<th class="EN_show_elem">Time</th>
<th class="EN_show_elem">Keys/EN</th>
<?php
if($show_hidden==1){ ?>
	<th class="hidden_elem">Hidden</th>
<?php
}
?>
<th><input type="checkbox" id="check_all" /></th>
<th class="EN_hide_elem">#</th>
</tr>

<?php
if( $tech_id!="" && $date!="" ){

	if($tr['start']!=""){

		$start_acco_sql = mysql_query("
			SELECT *
			FROM `accomodation`
			WHERE `accomodation_id` = {$tr['start']}
			AND `country_id` = {$_SESSION['country_default']}
		");

		if(mysql_num_rows($start_acco_sql)>0){

			$start_acco = mysql_fetch_array($start_acco_sql);

			$prop_address[$i]['address'] = "{$start_acco['address']}, {$_SESSION['country_name']}";
			$prop_address[$i]['lat'] = $start_acco['lat'];
			$prop_address[$i]['lng'] = $start_acco['lng'];

			$i++;

			$start_agency_name = $start_acco['name'];
			$start_agency_address = $start_acco['address'];
			$start_acco_phone = $start_acco['phone'];

			$start_row_color = '#eeeeee';

		}

	}


?>

<tr class="nodrop nodrag chops" style="background-color:<?php echo $start_row_color; ?>;">
<td class="EN_hide_elem">
<?php
// start
echo $ctr;
?>
</td>
<td colspan="4">
</td>
<td colspan="2" class="EN_hide_elem">
	<?php echo "{$start_agency_name}<br />{$start_acco_phone}"; ?>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td><img src="/images/red_house_resized.png" class="red_house_img" /></td>
<td>&nbsp;</td>
<td><?php echo $start_agency_address; ?></td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="DTA_elem">&nbsp;</td>
<td class="EN_show_elem">&nbsp;</td>
<td class="EN_show_elem">&nbsp;</td>
<td class="EN_show_elem">&nbsp;</td>
<?php
if($show_hidden==1){ ?>
	<td class="hidden_elem">&nbsp;</td>
<?php
}
?>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="EN_hide_elem"><?php
echo $ctr;
$ctr++;
?></td>
</tr>




<?php





$total_list = ($jr_count+$kr_count);

/*
echo "Total: ".$total_list."<br />".
	"Number of Jobs: ".$jr_count."<br />".
	"Completed Jobs: ".$comp_count;
*/

$total_tech_run = $total_list+1;
$job_ctr = 0;


$j = 2;
$hiddenRowsCount = 0;
$hideChk = 0;
$jobCount = 0;
$trr_count = 0;
$agency_comb_arr = [];

while( $row = mysql_fetch_array($jr_list2) ){

		// display control
		if( $trr_count < $display ){

		$hiddenText = "";
		$showRow = 1;
		$isUnavailable = 0;
		$isHidden = 0;
		$isPriority = 0;
		$is_no_en = false;

		// JOBS
		if( $row['row_id_type'] == 'job_id' ){
			//echo $row['row_id_type'];

			$jr_sql = getJobRowData($row['row_id'],$_SESSION['country_default']);
			//echo $jr_sql;

			$row2 = mysql_fetch_array($jr_sql);
			//print_r($row2);
			//echo $this->db->last_query();



			// if job type is 240v Rebook and status is to be booked and the tech is not electricianthen hide it
			if( ( $row2['job_type']=='240v Rebook' || $row2['is_eo'] == 1 ) && $row2['j_status']=='To Be Booked' && $isElectrician==false ){
				$hiddenText .= '240v<br />';
				$showRow = 0;
			}else{
				$showRow = 1;
			}

			if( $row['hidden']==1 ){
				$hiddenText .= 'User<br />';
			}

			if( $row2['unavailable']==1 && $row2['unavailable_date']==$date ){
				$isUnavailable = 1;
				$hiddenText .= 'Unavailable<br />';
			}

			$startDate = date('Y-m-d',strtotime($row2['start_date']));

			if( $row2['job_type'] == 'Lease Renewal' && ( $row2['start_date']!="" && $date < $startDate ) ){
				$hiddenText .= 'LR<br />';
			}

			if( $row2['job_type'] == 'Change of Tenancy' && ( $row2['start_date']!="" && $date < $startDate  ) ){
				$hiddenText .= 'COT<br />';
			}

			if( $row2['j_status'] == 'DHA' && ( $row2['start_date']!="" && $date < $startDate ) ){
				$hiddenText .= 'DHA<br />';
			}

			if( $row2['j_status'] == 'On Hold' && ( $row2['start_date']!="" && $date < $startDate ) ){
				$hiddenText .= 'On Hold<br />';
			}

			if( $row2['j_status'] == 'On Hold' && $row['allow_upfront_billing']==1 ){
				$hiddenText .= 'Up Front Billing<br />';
			}

			// this job is for electrician only
			if( $row2['electrician_only'] == 1 && $isElectrician == false ){
				$hiddenText .= 'Electrician Only<br />';
			}


			/*
			if( $row2['j_status'] == 'Allocate' && ( $row2['start_date']!="" && $date < $startDate ) ){
				$hiddenText .= 'Allocate<br />';
			}
			*/

			if( $show_hidden==0 && $hiddenText!="" && $row2['j_status']!='Booked' ){
				$showRow = 0;
			}else{
				$showRow = 1;
			}




			$bgcolor = "#FFFFFF";
			if($row2['job_reason_id']>0){
				//$bgcolor = "#fffca3";
			}else if($row2['ts_completed']==1){
				$bgcolor = "#c2ffa7";
			}




			$j_created = date("Y-m-d",strtotime($row2['created']));
			$last_60_days = date("Y-m-d",strtotime("-60 days"));


			if( $row['dnd_sorted']==0 ){
				$bgcolor = '#ffff8e';
			}

			if( $hiddenText!="" ){
				$hiddenRowsCount++;
				//$bgcolor = "#ADD8E6";
				$isHidden = 1;
			}

			if( $show_hidden==1 && ( $row['hidden']==1 || $isUnavailable==1 ) ){
				$hideChk = 0;
			}else if( $show_hidden==1 ){
				$hideChk = 1;
			}else{
				$hideChk = 0;
			}


			// if property and agency is NO to EN
			if( $row2['no_en'] == 1 || ( is_numeric($row2['allow_en']) && $row2['allow_en'] == 0 ) ){
				$is_no_en = true;
			}


			if( $row['highlight_color']!="" ){
				//$bgcolor = $row['highlight_color'];
			}


			// priority jobs
			if(
				$row2['job_type'] == "Change of Tenancy" ||
				$row2['job_type'] == "Lease Renewal" ||
				$row2['job_type'] == "Fix or Replace" ||
				$row2['job_type'] == "240v Rebook" ||
				$row2['is_eo'] == 1 ||
				$row2['j_status'] == 'DHA' ||
				$row2['urgent_job'] == 1
			){
				$isPriority = 1;
			}else{
				$isPriority = 0;
			}


			//if( $show_hidden==1 $hiddenText=="" ){

			//if( $row2['jdate']=="" || $row2['jdate']=="0000-00-00" || $row2['jdate']==$date ){



			if( $showRow==1 ){

			$isEscalateJob = '';
			if( $row2['j_status']=='Escalate' ){

				// get Escalate Reasons
				$escalate_sql = mysql_query("
					SELECT *
					FROM `selected_escalate_job_reasons` AS sejr
					LEFT JOIN `escalate_job_reasons` AS ejr ON sejr.`escalate_job_reasons_id` = ejr.`escalate_job_reasons_id`
					WHERE sejr.`job_id` = {$row2['jid']}
				");
				$escalate_arr = [];
				while( $escalate = mysql_fetch_array($escalate_sql) ){
					$escalate_arr[] = $escalate['reason_short'];
				}
				$ecalate_reason = implode("<br />",$escalate_arr);
				$ecalate_reason_str =  "<strong style='color:red;'>{$ecalate_reason}</strong>";
				$isEscalateJob = 1;

			}else{
				$ecalate_reason_str = $row2['j_status'];
				$isEscalateJob = 0;
			}
			?>
			<tr data-hlc_id="<?php echo $row['highlight_color']; ?>"
			id="<?php echo $row['tech_run_rows_id']; ?>"
			style="background-color:<?php echo $bgcolor; ?>"
			class="
			tech_run_row
			<?php echo ($row['hex']!='')?'hasColor':'NoColor'; ?>
			<?php echo ($isHidden!="")?'hidden_elem hiddenJobs':''; ?>
			<?php echo ($isEscalateJob==1)?'isEscalateJobClass':'' ?>
			<?php echo ($row2['j_status']=='Booked')?'isBooked':''; ?>
			<?php echo ( $crm->check_prop_first_visit($row2['property_id'])  == true )?'jrow_first_visit':''; ?>
			<?php echo ( $isEscalateJob == 1 )?'jrow_escalate_jobs':''; ?>
			<?php echo ( $row2['job_type'] == 'Once-off' )?'jrow_once_off':''; ?>
			<?php echo ( $row2['job_type'] == 'Change of Tenancy' )?'jrow_cot':''; ?>
			<?php echo ( $row2['job_type'] == 'Yearly Maintenance' )?'jrow_ym':''; ?>
			<?php echo ( $row2['job_type'] == 'Fix or Replace' )?'jrow_fr':''; ?>
			<?php echo ( $row2['job_type'] == '240v Rebook' )?'jrow_240v_rebook':''; ?>
			<?php echo ( $row2['job_type'] == 'Lease Renewal' )?'jrow_lr':''; ?>
			<?php echo ( $row2['job_type'] == 'IC Upgrade' )?'jrow_ic_upg':''; ?>
			<?php echo ( $row2['job_type'] == 'Annual Visit' )?'jrow_annual_vis':''; ?>
			<?php echo ( $row2['holiday_rental'] == 1 )?'jrow_holiday_rental':''; ?>
			<?php echo ($is_no_en == true)?'is_no_en':null; ?>
			chops0">
				<td class="EN_hide_elem"><?php
//                                var_dump($row);
                                echo $j; ?></td>
				<td style="background-color:<?php echo $row['hex']; ?>">&nbsp;</td>
				<td class="<?php echo ($isPriority==1)?'redBorder':''; ?>">
				<?php



				if( displayGreenPhone2($row2['jid'],$row2['j_status'])==true ){
					echo '<img src="/images/green_phone.png" style="cursor: pointer; margin-right: 10px;" title="Phone Call" />';
				}



				// old job
				//echo (($j_created<$last_60_days)?'<img src="/images/hourglass.png" class="jicon" style="margin-right: 7px; cursor:pointer;" title="Old job" />':'');

				// if first visit
				if( $crm->check_prop_first_visit($row2['property_id']) == true  ){
					$fv = '<img src="/images/first_icon2.png" class="jicon" style="margin-right: 7px; cursor:pointer;" title="First visit" />';
				}else{
					$fv = '';
				}

				echo $fv;


				//  if job type = COT, LR, FR, 240v or if marked Urgent
				if( $isPriority==1 ){
					//echo '<img src="/images/caution.png" class="jicon" style="height: 25px; cursor:pointer;" title="Priority Job" />';
					echo '<img title="Priority Jobs" class="priority_icon" style="cursor: pointer;" src="/images/priority.png" />';
				}

				//  if job priority
				if( $row2['job_priority'] == 1 ){
					echo '<img title="Job Priority" class="priority_icon" style="cursor: pointer;" src="/images/priority_icon.png" />';
				}

				if( $row2['key_access_required'] == 1 && $row2['j_status']=='Booked' ){
					echo '<img src="/images/key_icon_green.png" style="cursor: pointer;" title="Key Access Required" />';
				}


				$has_tenants = false;
				if( $row2['property_id'] > 0 ){

					$pt_params = array(
						'property_id' => $row2['property_id'],
						'active' => 1,
						'return_count' => 1
					 );
					$tenants_count = $crm->getNewTenantsData($pt_params);
					$has_tenants = ( $tenants_count > 0 )?true:false;

				}

				// if no tenants
				if( $has_tenants == false ){
					//$row_color = "style='background-color:#ffff9d;'";
					$is_no_tenants = 1;
					echo '<img title="No Tenants" class="no_tenant_icon" style="cursor: pointer;" src="/images/no_tenant.png" />';
				}

				// AGE

				$date1=date_create($j_created);
				$date2=date_create(date('Y-m-d'));
				$diff=date_diff($date1,$date2);
				$age_temp = $diff->format("%r%a");
				$age = (int)$age_temp;

				// job that is over 60 days.
				if(  $age > 60  ){
					//$row_color = "style='background-color:#ffff9d;'";
					echo '<img title="60+ days old" style="cursor: pointer;" src="/images/bomb.png" />';
				}



				if( $isHidden==1 ){
					echo '<img title="Hidden" class="hidden_icon" style="cursor: pointer;" src="/images/hidden_job.png" />';
				}

				if( $row2['p_state'] == 'NSW' && $row2['service_garage'] == 1 ){
					echo '<img title="Service Garage" src="/images/serv_img/service_garage_icon.png" style="cursor: pointer;" />';
				}


				?>
				</td>
				<td><?php echo ( $row2['deadline'] >= 0 )?$row2['deadline']:"<span style='color:red;'>{$row2['deadline']}</span>"; ?></td>
				<td>
				<?php
				// Age

				echo ($age!=0)?$age:0;
				?>
				</td>
				<td class="EN_hide_elem"><?php echo $row2['tech_notes']; ?></td>
				<td class="EN_hide_elem time_of_day_td">
					<?php
					if( $tr['run_complete'] == 1 ){ ?>

						<div class="time_of_day_div">
						<a href="javascript:void(0);" class="time_of_day_link"><?php echo $row2['time_of_day']; ?></a>
						<input type="text" class="time_of_day_hid" value="<?php echo $row2['time_of_day']; ?>" />
						<img class="check_icon" src="/images/check_icon2.png" />
						</div>

					<?php
					}else{
						echo $row2['time_of_day'];
					}
					?>

				</td>
				<td class="jstatus chops">
					<div style="float: left; padding-top: 4px; margin-right: 6px;">
					<?php echo $ecalate_reason_str; ?>
					</div>
					<?php
					if($row2['j_status']=='Booked'){
					//$bgcolor = "#eeeeee";
					?>
						<img title="Booked" class="booked_icon" style="cursor: pointer; width: 24px; display: block; float: left;" src="/images/check_icon2.png" />
					<?php
					}
					?>
				</td>
				<td>
				<?php
				// job type
				switch($row2['job_type']){
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
					case 'IC Upgrade':
						$jt = 'IC.UP';
					break;
					case 'Electrician Only':
						$jt = 'EO';
					break;
					default:
						$jt = $row2['job_type'];
				}

				// if job type is 'IC Upgrade' show IC upgrade icon
				$show_ic_icon = ( $row2['job_type'] == 'IC Upgrade' )?1:0;
				?>
				<a href="view_job_details.php?id=<?php echo $row2['jid']; ?>&tr_tech_id=<?php echo $tech_id; ?>&tr_date=<?php echo $date; ?>&tr_booked_by=<?php echo $_SESSION['USER_DETAILS']['StaffID']; ?>"><?php echo $jt; ?></a></td>
				<td>
					<?php
					// display icons
					$job_icons_params = array(
						'job_id' => $row2['jid']
					);
					echo $crm->display_job_icons_v2($job_icons_params);

					// requires PPE
					if( $row2['requires_ppe'] == 1 ){
						echo "<img src='/images/ppe_icon.png' class='ppe_icon' />";
					}
					?>
				</td>
				<td><?php echo ($row2['door_knock']==1)?'DK':''; ?></td>
				<td>
					<?php if($crm->check_links() == 0){ ?>
						<a href="view_property_details.php?id=<?php echo $row2['property_id']; ?>"><?php echo $row2['p_address_1']." ".$row2['p_address_2'].", ".$row2['p_address_3']; ?></a>
					<?php } else { ?>
						<a href="<?php echo $crm->crm_ci_redirect(rawurlencode("/properties/details/?id={$row2['property_id']}&tab=1")); ?>"><?php echo "{$row2['p_address_1']} {$row2['p_address_2']}, {$row2['p_address_3']}"; ?></a>
					<?php } ?>
				</td>
				<td>
					<?php
					// get all postcode that belong to a region
					$sel_query = "sr.`subregion_name`";
					$postcode_params = array(
						'sel_query' => $sel_query,
						'region_id' => $region,
						'postcode' => $row2['p_postcode'],
						'deleted' => 0,
						'display_query' => 0
					);
					$postcode_sql = $crm->get_postcodes($postcode_params);
					$postcode_row = mysql_fetch_array($postcode_sql);

					echo $postcode_row['subregion_name'];
					?>
				</td>
				<td>
					<?php
					//echo "agency_id: {$row2['agency_id']}<br />";
					//echo "fn_agency_main: {$fn_agency_main}<br />";

					if($row2['priority'] == 1){
						$ap = "(HT)";
					}
					else if($row2['priority'] == 2){
						$ap = "(VIP)";
					}
					else if($row2['priority'] == 3){
						$ap = "(HWC)";
					}
					else{
						$ap = "";
					}

					if( $row2['agency_id'] == $fn_agency_main || $row2['agency_id'] == $vision_agency_main ){
						echo "Select appropriate Key location";
					}else{ ?>
						<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row2['agency_id']}"); ?>
						<a class="<?php echo ($row2['priority'] > 0) ? 'j_bold' : ''; ?>" href="<?php echo $ci_link ?>" class="agency_td">
							<?php echo str_replace('*do not use*','',"{$row2['agency_name']} ".( $ap )); ?>
						</a>
					<?php
					}
					?>
				</td>
				<td><?php echo $row2['j_comments']; ?></td>
				<td><?php echo $row2['p_comments']; ?></td>
				<td class="EN_show_elem chops" style="text-align: center;"><?php echo $row2['qld_new_leg_alarm_num']; ?></td>
				<td><?php echo $row2['preferred_time']; ?></td>
				<td class="DTA_elem">
				<?php
				// get the distance
				// job property address
				$job_prop_add = "{$row2['p_address_1']} {$row2['p_address_2']} {$row2['p_address_3']} {$row2['p_state']} {$row2['p_postcode']}";
				// job agency address
				$agency_add = "{$row2['a_address_1']} {$row2['a_address_2']} {$row2['a_address_3']} {$row2['a_state']} {$row2['a_postcode']}";
				?>
				<div class="DTA_val">&nbsp;</div>
				<input type="hidden" class="job_prop_add" value="<?php echo $job_prop_add; ?>" />
				<input type="hidden" class="agency_add" value="<?php echo $agency_add; ?>" />
				</td>
				<td class="EN_show_elem"><input type="text" class="addinput en_time" style="width: 64px; padding-left: 5px;" value="8.30-3.30"></td>
				<?php
				// orig url

				//$orig_url = "{$_SERVER['SERVER_NAME']}/view_entry_notice.php?letterhead=1&job_id={$row2['jid']}&tr_id={$tr_id}&en_time=8.30-3.30";


				$job_enc = utf8_encode($encrypt->encrypt($row2['jid']));
				$job_url_enc = rawurlencode($job_enc);

				$orig_url_without_params = "/view_entry_notice_new.php?letterhead=1&i={$row2['jid']}&m=".md5($row2['agency_id'].$row2['jid']);
				$orig_url = "/view_entry_notice_new.php?letterhead=1&i={$row2['jid']}&m=".md5($row2['agency_id'].$row2['jid'])."&tr_id={$tr_id}&en_time=8.30-3.30";



				$has_tenant_email = false;
				$has_tenant_mobile = false;

				$pt_params = array(
					'property_id' => $row2['property_id'],
					'active' => 1
				 );
				$pt_sql = $crm->getNewTenantsData($pt_params);

				while( $pt_row = mysql_fetch_array($pt_sql) ){

					if( $pt_row['tenant_email'] != "" && filter_var($pt_row['tenant_email'], FILTER_VALIDATE_EMAIL) ){
						$has_tenant_email = true;
					}

					if( $pt_row['tenant_mobile'] != "" ){
						$has_tenant_mobile = true;
					}

				}

				// if no tenant or tenant has no email and SMS, hide checkbox
				$tenant_has_no_email_and_mob = false;
				if( $has_tenant_email == false && $has_tenant_mobile == false ){
					//$hideChk = 1;
					$tenant_has_no_email_and_mob = true;
				}				
				?>				
				<td class="EN_show_elem"><?php echo ( $row2['key_allowed']!=1 || $row2['no_keys']==1 || $row2['no_en']==1 )?'<img src="/images/cross_red.png" />':''; ?></td>
				<?php
				if($show_hidden==1){ ?>
					<td class="hidden_elem"><?php echo $hiddenText; ?></td>
				<?php
				}
				?>
				<td>
					<?php
					if( $has_tenants == false ){
						echo '
						<img
							title="No Tenants"
							class="no_tenant_icon"
							data-prop_vacant="'.$row2['property_vacant'].'"
							data-start_date="'.$row2['start_date'].'"
							data-due_date="'.$row2['due_date'].'"
							style="cursor: pointer;"

							src="/images/no_tenant.png"
						/>
						';
					}

					if( $tenant_has_no_email_and_mob == true ){ ?>
						<img class="invalid_en_icon" title="No tenant mobile and email, invalid for EN" style="cursor: pointer;" src="/images/invalid_en.png" />
					<?php
					}
					?>

					<!--<input type="checkbox" name="del_map_route[]" class='del_map_route' value="job_id:<?php echo $row2['jid']; ?>" />-->
					<input <?php echo ($hideChk==1)?'style="display:none;"':''; ?> type="checkbox" name="chk_trr_id[]" class='chk_trr_id <?php echo ( $tenant_has_no_email_and_mob == true )?'chk_no_tenant':'';?>' value="<?php echo $row['tech_run_rows_id']; ?>" />
					<input type="hidden" name="trr_row_type[]" class='trr_row_type' value="<?php echo $row['row_id_type']; ?>" />
					<input type="hidden" name="chk_job_id[]" class='chk_job_id' value="<?php echo $row2['jid']; ?>" />
					<input type="hidden" name="map_id[]" value="job_id:<?php echo $row2['jid']; ?>" />
					<input type="hidden" class="is_dk_allowed" value="<?php echo $row2['allow_dk']; ?>" />
					<input type="hidden" class="agency_id" value="<?php echo $row2['agency_id']; ?>" />
					<input type="hidden" class="orig_row_highlight" value="<?php echo $bgcolor; ?>" />
					<input type="hidden" class="trr_job_type" value="<?php echo $row2['j_status']; ?>" />
					<input type="hidden" class="trrc_id" value="<?php echo $row['tech_run_row_color_id']; ?>" />
					<input type="hidden" class="map_pins_color" value="<?php echo $row['hex']; ?>" />
					<input type="hidden" class="no_dk" value="<?php echo $row2['no_dk']; ?>" />
					<input type="hidden" class="p_address" value="<?php echo $row2['p_address_1']." ".$row2['p_address_2'].", ".$row2['p_address_3']; ?>" />
					<input type="hidden" class="en_hidden_orig_url" value="<?php echo $orig_url_without_params; ?>" />
					<?php 
						if($row2['is_eo']==1){
							$selected_filter =  "Electrician_Only";
						}
						else {
							$selected_filter = str_replace(' ','_',$row2['job_type']);
						} 
					?>
					<input type="hidden" class="hid_job_type_marker" value="<?php echo $selected_filter; ?>" >
				</td>

				<td class="EN_hide_elem"><?php echo $j; ?></td>
			</tr>
		<?php
			// store it on property address array
			$prop_address[$i]['address'] = "{$row2['p_address_1']} {$row2['p_address_2']} {$row2['p_address_3']} {$row2['p_state']} {$row2['p_postcode']}, {$_SESSION['country_name']}";
			$prop_address[$i]['status'] = $row2['j_status'];
			$prop_address[$i]['created'] = date("Y-m-d",strtotime($row2['created']));
			$prop_address[$i]['urgent_job'] = $row2['urgent_job'];
			$prop_address[$i]['lat'] = $row2['p_lat'];
			$prop_address[$i]['lng'] = $row2['p_lng'];
			$i++;

			$j++;

			$jobCount++;

			$trr_count++;

			}

			//}

			//}

			}else if( $row['row_id_type'] == 'keys_id' ){

				// KEYS
				$k_sql = getTechRunKeys($row['row_id'],$_SESSION['country_default']);
				$kr = mysql_fetch_array($k_sql);

				if( $show_hidden==1 ){
					$hideChk = 1;
				}else{
					$hideChk = 0;
				}


				//$bgcolor = ($kr['completed']==1)?'#c2ffa7':'#eeeeee';

				$bgcolor = '#eeeeee';

				?>
					<tr id="<?php echo $row['tech_run_rows_id']; ?>" class="tech_run_row" style="background-color:<?php echo $bgcolor; ?>;">
						<td class="EN_hide_elem"><?php echo $j; ?></td>
						<td colspan="4">&nbsp;</td>
						<td class="EN_hide_elem">
							<?php
								if($kr['completed']==1){
									$kr_act = explode(" ",$kr['action']);
									$temp2 = ($kr['action']=="Drop Off")?'p':'';
									$temp = "{$kr_act[0]}{$temp2}ed";
									$action = "{$temp} {$kr_act[1]}";
								}else{
									$action = $kr['action'];
								}
								echo $action;
							?>
						</td>
						<td class="EN_hide_elem"></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><img src="/images/key_icon_green.png" /></td>
						<td>&nbsp;</td>
						<td>
							<?php $ci_link2 = $crm->crm_ci_redirect("/agency/view_agency_details/{$kr['agency_id']}"); ?>
							<a href="<?php echo $ci_link2; ?>">
								<?php 
								if( $kr['agen_add_id'] > 0 ){ // key address

									echo "{$kr['agen_add_street_num']} {$kr['agen_add_street_name']}, {$kr['agen_add_suburb']}"; 

								}else{ // default

									echo "{$kr['address_1']} {$kr['address_2']}, {$kr['address_3']}"; 

								}								
								?>
							</a>
						</td>
						<td>&nbsp;</td>
						<td>
							<a href="<?php echo $ci_link2; ?>">
								<?php echo str_replace('*do not use*','',$kr['agency_name']); ?>
							</a>
						</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td class="DTA_elem">&nbsp;</td>
						<td class="EN_show_elem">&nbsp;</td>
						<td class="EN_show_elem">&nbsp;</td>
						<td class="EN_show_elem">&nbsp;</td>
						<?php
						if($show_hidden==1){ ?>
							<td class="hidden_elem"><?php echo $hiddenText; ?></td>
						<?php
						}
						?>
						<td>
							<!--<input type='checkbox' name="del_map_route[]" class='del_map_route' value='key_routes_id:<?php echo $kr['key_routes_id']; ?>' />-->
							<input <?php echo ($hideChk==1)?'style="display:none;"':''; ?> type="checkbox" name="chk_trr_id[]" class='chk_trr_id' value="<?php echo $row['tech_run_rows_id']; ?>" />
							<input type="hidden" name="trr_row_type[]" class='trr_row_type' value="<?php echo $row['row_id_type']; ?>" />
							<input type="hidden" name="chk_trk_id[]" class='chk_trk_id' value="<?php echo $kr['tech_run_keys_id']; ?>" />
							<input type="hidden" name="map_id[]" value="key_routes_id:<?php echo $kr['key_routes_id']; ?>" />
							<input type="hidden" class="orig_row_highlight" value="<?php echo $bgcolor; ?>" />
						</td>
						<td class="EN_hide_elem"><?php echo $j; ?></td>
					</tr>
				<?php
				// get gecode
				$prop_address[$i]['address'] = "{$kr['address_1']} {$kr['address_2']} {$kr['address_3']} {$kr['state']} {$kr['postcode']}, {$_SESSION['country_name']}";
				$prop_address[$i]['is_keys'] = 1;
				$prop_address[$i]['lat'] = $kr['lat'];
				$prop_address[$i]['lng'] = $kr['lng'];
				$i++;

				$j++;

				$trr_count++;

			}else if( $row['row_id_type'] == 'supplier_id' ){

				// Suppliers
				$sup_sql = getTechRunSuppliers($row['row_id']);
				$sup = mysql_fetch_array($sup_sql);

				if($sup['on_map']==1){

				if( $show_hidden==1 ){
					$hideChk = 1;
				}else{
					$hideChk = 0;
				}


				//$bgcolor = ($kr['completed']==1)?'#c2ffa7':'#eeeeee';

				$bgcolor = '#eeeeee';

				?>
					<tr id="<?php echo $row['tech_run_rows_id']; ?>" class="tech_run_row" style="background-color:<?php echo $bgcolor; ?>;">
						<td class="EN_hide_elem"><?php echo $j; ?></td>
						<td colspan="4">&nbsp;</td>
						<td class="EN_hide_elem">Supplier</td>
						<td class="EN_hide_elem"><?php echo $sup['company_name']; ?></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><?php echo $sup['sup_address']; ?></td>
						<td>&nbsp;</td>
						<td><?php echo $sup['company_name']; ?></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td class="DTA_elem">&nbsp;</td>
						<td class="EN_show_elem">&nbsp;</td>
						<td class="EN_show_elem">&nbsp;</td>
						<?php
						if($show_hidden==1){ ?>
							<td class="hidden_elem"><?php echo $hiddenText; ?></td>
						<?php
						}
						?>
						<td>
							<!--<input type='checkbox' name="del_map_route[]" class='del_map_route' value='key_routes_id:<?php echo $kr['key_routes_id']; ?>' />-->
							<input <?php echo ($hideChk==1)?'style="display:none;"':''; ?> type="checkbox" name="chk_trr_id[]" class='chk_trr_id' value="<?php echo $row['tech_run_rows_id']; ?>" />
							<input type="hidden" name="trr_row_type[]" class='trr_row_type' value="<?php echo $row['row_id_type']; ?>" />
							<input type="hidden" name="chk_trs_id[]" class='chk_trs_id' value="<?php echo $sup['tech_run_suppliers_id']; ?>" />
							<input type="hidden" name="map_id[]" value="supplier_routes_id:<?php echo $sup['suppliers_id']; ?>" />
							<input type="hidden" class="orig_row_highlight" value="<?php echo $bgcolor; ?>" />
						</td>
						<td class="EN_hide_elem"><?php echo $j; ?></td>
					</tr>
				<?php
				// get gecode
				$prop_address[$i]['address'] = $sup['sup_address'];
				$prop_address[$i]['is_keys'] = 1;
				$prop_address[$i]['lat'] = $sup['lat'];
				$prop_address[$i]['lng'] = $sup['lng'];
				$i++;

				$j++;

				$trr_count++;

				}

			}




		}

		// used for detecting agency booking notes
		if( $row['agency_id']!='' ){
			$agency_comb_arr[] = $row['agency_id'];
		}


}



$end_acco_sql = mysql_query("
	SELECT *
	FROM `accomodation`
	WHERE `accomodation_id` = {$tr['end']}
	AND `country_id` = {$_SESSION['country_default']}
");
$end_acco = mysql_fetch_array($end_acco_sql);

if(mysql_num_rows($end_acco_sql)>0){

	$prop_address[$i]['address'] = "{$end_acco['address']}, {$_SESSION['country_name']}";
	$prop_address[$i]['lat'] = $end_acco['lat'];
	$prop_address[$i]['lng'] = $end_acco['lng'];

	$i++;

	$end_agency_name = $end_acco['name'];
	$end_agency_address = $end_acco['address'];
	$end_acco_phone = $end_acco['phone'];

	$end_row_color = '#eeeeee';

}

?>


<tr class="nodrop nodrag chops1" style="background-color:<?php echo $end_row_color; ?>;">
<td class="EN_hide_elem">
<?php

$end_point_index = $j;

echo $end_point_index;

?>
</td>
<td colspan="4">&nbsp;</td>
<td colspan="2" class="EN_hide_elem"><?php echo "{$end_agency_name}<br />{$end_acco_phone}"; ?></td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td><img src="/images/red_house_resized.png" class="red_house_img" /></td>
<td>&nbsp;</td>
<td>
	<?php echo $end_agency_address; ?>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="DTA_elem">&nbsp;</td>
<td class="EN_show_elem">&nbsp;</td>
<td class="EN_show_elem">&nbsp;</td>
<td class="EN_show_elem">&nbsp;</td>
<?php
if($show_hidden==1){ ?>
	<td class="hidden_elem">&nbsp;</td>
<?php
}
?>
<td class="EN_hide_elem"><?php
echo $end_point_index;
$ctr++;
?></td>
</tr>

<?php
}
?>

</table>

<?php
/*
// Initiate pagination class
$jp = new jPagination();

$per_page = $limit;
$page = ($_GET['page']!="")?$_GET['page']:1;
$offset = ($_GET['offset']!="")?$_GET['offset']:0;

echo $jp->display($page,$ptotal,$per_page,$offset,$pag_params);
*/


//print_r($agency_comb_arr);
$agency_comb_imp = implode(',',$agency_comb_arr);



// find agency booking notes for agencies present on the list
$bn_params = array(
	'echo_query' => 0,
	'country_id' => $_SESSION['country_default'],
	'custom_filter' => " AND a.`agency_id` IN({$agency_comb_imp}) "
);
$bn_sql = $crm->getBookingNotes($bn_params);
?>
<input type="hidden" id="hasBookingNotes" value="<?php echo mysql_num_rows($bn_sql); ?>" />

<!--
<div style=" margin-top: 16px;">
	<label>Total Jobs:</label> <?php echo $jobCount; ?>
</div>
-->

<input type="hidden" id="hiddenRowsCount" value="<?php echo $hiddenRowsCount; ?>" />

<?php

/*
echo "<pre>";
print_r($prop_address);
echo "</pre>";
*/


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

	<div style="float: right; margin-top: 10px; margin-left: 10px;">
		<select id="tech_run_functions">
			<option value="">Select</option>
			<option value="hide">Hide/Unhide</option>
			<option id="door-knock-opt" value="dk">Door Knocks</option>
			<option value="keys">Keys</option>
			<option value="highlight">Highlight Row</option>
			<option value="escalate">Escalate</option>
			<option value="change_tech">Change Tech</option>
			<option value="mark_tech_sick">Mark Tech Sick</option>
		</select>
	</div>




	<div class="function_btn_div" id="dk_main_div"  style="float: right; margin-top: 10px; display:none;">
		<button type="button" id="btn_assign_dk" class="submitbtnImg">Assign Door Knock</button>
	</div>


	<div class="function_btn_div" id="keys_main_div" style="display:none; float: right;">
		<div id="btn_remove_keys_div" style="float: right; margin-top: 10px; margin-right: 10px; display:none;">
			<button type="button" id="btn_remove_keys" class="submitbtnImg">Remove Keys</button>
		</div>
	</div>


	<div class="function_btn_div" id="hide_show_row_main_div" style="display:none; float: right;">

		<?php
		if( $show_hidden==0 ){ ?>
			<div style="float: right; margin-top: 10px; margin-right: 10px;">
				<button type="button" id="btn_hide_row" class="submitbtnImg">Hide</button>
			</div>
		<?php
		}
		?>

		<?php
		if( $show_hidden==1 ){ ?>
			<div style="float: right; margin-top: 10px; margin-right: 10px;">
			<button type="button" id="btn_unhide_row" class="submitbtnImg">Unhide Jobs</button>
		</div>
		<?php
		}
		?>

	</div>

	<div class="function_btn_div" id="str_func_escalate_div"  style="float: right; margin-top: 10px; display:none;">
		<button type="button" id="str_func_escalate_btn" class="submitbtnImg">Submit</button>
	</div>


	<div class="function_btn_div" id="mark_tech_sick_div"  style="float: right; margin-top: 10px; display:none;">
		<button type="button" id="mark_tech_sick_btn" class="submitbtnImg">Mark</button>
	</div>




	<div class="function_btn_div" id="highlight_color_main_div" style="display:none; float: right;">

		<div id="btn_remove_color_div" style="float: right; margin-top: 10px; margin-right: 10px; display:none;">
			<button type="button" id="btn_remove_color" class="submitbtnImg">Remove Color</button>
		</div>

		<div id="btn_assign_color_div" style="float: right; margin-top: 10px; margin-right: 10px; display:none;">
			<select id="row_highlight_color">
				<option value="">Select</option>
				<?php
				$hc_sql = getRowColor();
				while( $hc = mysql_fetch_array($hc_sql) ){ ?>
					<option value="<?php echo $hc['tech_run_row_color_id']; ?>"><?php echo $hc['color']; ?></option>
				<?php
				}
				?>
			</select>
			<button type="button" id="btn_assign_color" class="submitbtnImg">Assign Color</button>
		</div>

	</div>



	<?php
	if( $hasTechRun == true ){ ?>
		<div id="change_tech_div" class="function_btn_div" style="display:none; float: right; margin-top: 10px; ">

				<select id="change_to_tech_id">
					<option value="">-- Select --</option>
					<?php
					$tech_sql = mysql_query("
						SELECT sa.`StaffID`, sa.`FirstName`, sa.`LastName`, sa.`is_electrician`, sa.`active` AS sa_active
						FROM `staff_accounts` AS sa
						LEFT JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
						WHERE ca.`country_id` ={$_SESSION['country_default']}
						AND sa.`Deleted` = 0
						AND sa.`ClassID` = 6
						AND sa.`active` = 1
						AND sa.`StaffID` > 1
						ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
					");
					while($tech = mysql_fetch_array($tech_sql)){

						$ct_tn = $crm->formatStaffName($tech['FirstName'],$tech['LastName']);

						?>

						<option data-tech_name="<?php echo $ct_tn; ?>" value="<?php echo $tech['StaffID']; ?>" <?php echo ($tech['StaffID']==$tech_id)?'selected="selected"':''; ?>>
							<?php
								echo $ct_tn.( ( $tech['is_electrician'] == 1 )?' [E]':null );
							?>
						</option>

					<?php
					}
					?>
				</select>
				<button type="button" class="submitbtnImg blue-btn" id="change_tech_update_btn">
					<img class="inner_icon" src="images/save-button.png">
					Update
				</button>

		</div>
	<?php
	}
	?>


</div>




<div id="btn_issue_EN_div" style="margin-top: 15px; margin-right: 35px; float: right; display: none;">
<button type="button" id="btn_issue_EN" class="submitbtnImg green-btn" style="margin-top: 10px;">ISSUE Entry Notice</button>
</div>


</form>

<?php
}
?>

<div style="clear:both;">&nbsp;</div>

  </div>
</div>

<br class="clearfloat" />



<style>
.rowHighlight{
	border: 1px solid red;
	background-color: #fcbdb6 !important;
}

.redBorder{
	border: 1px solid red;
}

.hiddenJobs{
	background-color: #add8e6 !important;
    border: 1px solid #006df0;
}
.EN_show_elem{
	display:none;
}
.green-btn{
    background-color: green!important;
}
.green-btn:hover {
    background-color: green!important;
}
.DTA_elem{
	display:none;
}

.jtimeStamp{
	    color: #00D1E5;
		font-size: 13px;
}
.c-tab__content {
    height: auto!important;
}
#notes{
	height: 100px;
	width: 100%;
	margin: 0;
}

.jRightBorder{
	border-right: 1px solid #cccccc;
}

.details_tab_cont .table{
	font-size: 13px;
	text-align: left;
}

.details_tab_cont .table input{
	margin: 0;
}

.details_tab_cont textarea{
	font-size: 13px !important;
	text-align: left;
}

.vosch-tp .fl-left {
    margin-top: 0px!important;
}
.redCross{
	width: 13px;
	display: none;
}

.c-tab__content{
	padding: 5px !important;
}
.jNoBorder tr, .jNoBorder td{
	border: none !important;
}
.greenBgButton{
	background-color: #00AE4D;
	color: white;
}
.greenBgButton:hover {
    background-color: #04be56 !important;
}
.details_marker_tbl td{
	cursor: pointer;
	text-align: left;
	font-weight: bold;
}
.details_marker_tbl td:hover{
	background-color: #E6E6E6;
}
.inner_icon{
	position: relative;
	top: 2px;
	margin-right: 3px;
}

.sort_by_div button{
	width: 150px;
	text-align: left;
}
.setup_tab_cont, .setup_tab_cont table{
	font-size: 13px;
	text-align: left;
}
.setup_tab_cont input{
	margin: 0 !important;
}
.details_marker_tick {
    position: relative;
    top: 2px;
}
.view_map_div table,
.view_map_div table tr,
.view_map_div table td{
	border:none;
}
.setup_table input[type=text],
.setup_table select{
	width: 130px!important;
}

.setup_table,
.setup_table tr,
.setup_table td{
	border:none;
}

.view_map,
.view_map tr,
.view_map td{
	border:none;
	font-size: 13px;
}
.check_all_sub_region{
	display: none;
}
.btn_select_uncolored,
#btn_first_visit,
#btn_escalate_jobs,
#btn_select_240v_jobs,
#select_job_type_span,
#select_job_type_btn,
#select_agency_jobs,
#btn_select_no_tenant_details,
#select_holiday_rental{
	width: 216px;
	margin-bottom: 3px;
	text-align: left;
	padding-left: 7px;
}
#agencyNotesLink{
	color:red;
	margin-left: 20px;
	display: none;
}
.ct_full{
	color: red;
	font-weight: bold;
}
.time_of_day_hid{
	width: 60px;
	float: left;
	margin-right: 6px;
	display: none;
}
.time_of_day_div{
	width: 110px;
}
.check_icon{
	opacity: 0.5;
	cursor:pointer;
	display: none;
}
.time_of_day_link{
	display: block;
	float: left;
	margin-right: 5px;
}
.invalid_en_icon{
	display: none;
}
.ppe_icon {
	position: relative;
	left: 7px;
	bottom: 2px;
}
</style>
<script type="text/javascript">



function CountNumberOfSelectedKeys(){
	var count = 0;
	jQuery(".chk_trr_id:checked").each(function(){

	  var trr_rt = jQuery(this).parents("tr:first").find(".trr_row_type").val();
	  if(trr_rt=="keys_id"){
	  count++;
	  }

	});

	//console.log(count);
	return count;
}

function CountNumberOfSelectedJobs(){
	var count = 0;
	jQuery(".chk_trr_id:checked").each(function(){

	  var trr_rt = jQuery(this).parents("tr:first").find(".trr_row_type").val();
	  if(trr_rt=="job_id"){
	  count++;
	  }

	});

	//console.log(count);
	return count;
}

function showENissueButton(){

	if( jQuery("#btn_EN_hidden").val()==1 ){

		jQuery("#hidden_button_div").hide();

		if( jQuery(".chk_trr_id:checked").length>0 ){
			jQuery("#btn_issue_EN_div").show();
		}else{
			jQuery("#btn_issue_EN_div").hide();
		}

	}


}


function getTechRunNewLists(gao){

	jQuery("#searching_for_new_jobs_div").show();
	jQuery.ajax({
		type: "POST",
		url: "ajax_tech_run_get_new_list.php",
		data: {
			tr_id: '<?php echo $tr_id; ?>',
			tech_id: '<?php echo $tech_id; ?>',
			date: '<?php echo $date; ?>',
			sub_regions: '<?php echo $sub_regions; ?>',
			get_assigned_only: gao
		}
	}).done(function( ret ){

		jQuery("#searching_for_new_jobs_div").hide();
		//console.log('new jobs: '+ret);
		var msg = '';

		if(parseInt(ret)>0){
			//alert('New Jobs avavilable');
			msg = 'New Jobs Available <a href="/set_tech_run.php?tr_id=<?php echo $tr_id; ?>">Refresh</a>';
		}else{
			msg = 'No New Jobs Found';
		}

		jQuery("#new_job_success_msg").html(msg);
		jQuery("#new_job_success_msg").slideDown().delay(2000).slideUp();

		//obj.parents("tr:first").find(".en_a_link").prop("href",ret);

		//window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
		//location.reload();
		//window.location='/main.php';
	});

}

// get unique agency from STR page
function getUniqueAgenciesFromTheList(){

	// get unique agency from the list
	var agencies = new Array();
	var ex_agencies = new Array();

	jQuery("#tbl_maps .agency_id").each(function(){
	  var agency_id = jQuery(this).val();
	  if( jQuery.inArray( agency_id, agencies ) == -1 ){
		  agencies.push(parseInt(agency_id));
	  }
	});

	<?php
	// add FN agencies
	if( count($fn_agency_sub) > 0 ){
		foreach( $fn_agency_sub as $fn_sub_agency_id ){ ?>
			agencies.push(parseInt(<?php echo $fn_sub_agency_id; ?>));
		<?php
		}
	}
	?>

<?php
	// add FN agencies
	if( count($vision_agency_sub) > 0 ){
		foreach( $vision_agency_sub as $vision_sub_agency_id ){ ?>
			agencies.push(parseInt(<?php echo $vision_sub_agency_id; ?>));
		<?php
		}
	}
	?>

	//console.log("agencies: "+agencies);
	//console.log("ex_agencies: "+ex_agencies);

	// remove agency not in the list
	jQuery("#keys_agency option").each(function(index){

		var opt = jQuery(this);
		var agency_id = parseInt(opt.val());
		if( index>0 && jQuery.inArray( agency_id, agencies ) == -1 ){
			opt.remove();
		}

	});


}


function showHideAccom(accomodation_id){

	if( jQuery("#start_point").val()==accomodation_id && jQuery("#end_point").val()==accomodation_id ){
		jQuery("#accom_div").hide();
	}else{
		jQuery("#accom_div").show();
	}

}

<?php
if( $hasTechRun == true ){ ?>

	function upateTRcolourTable(obj){

		var colour_id = obj.parents("tr:first").find(".ct_trrc_id").val();
		var time = obj.parents("tr:first").find(".ct_time").val();
		var jobs_num = obj.parents("tr:first").find(".ct_jobs").val();
		var no_keys = obj.parents("tr:first").find(".ct_no_keys_chk").prop("checked");
		var no_keys_fin = (no_keys==true)?1:0;
		var booked_jobs = obj.parents("tr:first").find(".ct_booked_job").val();
		var status_dif = '';
		var isFullyBooked = 0;


		// invoke ajax
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_tr_set_colour_table.php",
			data: {
				tr_id: <?php echo $tr_id; ?>,
				colour_id: colour_id,
				time: time,
				jobs_num: jobs_num,
				no_keys: no_keys_fin,
				booked_jobs: booked_jobs
			}
		}).done(function( ret ){

			//updateStatusColourTableBooked();

			var status_dif = jobs_num-booked_jobs;
			var booking_status = getCTstatusReturnData(status_dif);

			if(booking_status=='FULL'){
				status_txt = '<span class="ct_full">FULL</span>';
				isFullyBooked = 1;
			}else{
				status_txt = '-'+status_dif;
			}

			obj.parents("tr:first").find(".ct_status").html(status_txt);
			obj.parents("tr:first").find(".ct_fully_booked").val(isFullyBooked);

			<?php
			if( $tr['run_complete'] != 1 && $tr['no_more_jobs'] != 1 ){ ?>
				hideFullyBookedJobs();
			<?php
			}
			?>

			jQuery("#load-screen").hide();
		});

	}


	function countNumOfBookedJobsEachColor(){

		jQuery(".ct_booked_job").val(0); // clear them on load, bec shitty firefox autofills them on refresh

		jQuery(".isBooked").each(function(){

			var trrc_id = jQuery(this).find(".trrc_id").val();
			var booked_job = parseInt(jQuery("#ct_row_id_"+trrc_id).find(".ct_booked_job").val());
			var booked_tot = booked_job+1;
			jQuery("#ct_row_id_"+trrc_id).find(".ct_booked_job").val(booked_tot);

		});

	}


	function getCTstatusReturnData(status_dif){

		if( status_dif>0 ){
			booking_status = '-'+status_dif;
		}else{
			booking_status = 'FULL';
		}
		return booking_status

	}

	function updateStatusColourTableBooked(){

		jQuery(".ct_jobs").each(function(){

			var colour_id = parseInt(jQuery(this).parents("tr:first").find(".ct_trrc_id").val());
			var time = jQuery(this).parents("tr:first").find(".ct_time").val();
			var num_jobs = parseInt(jQuery(this).parents("tr:first").find(".ct_jobs").val());
			var booked_job = parseInt(jQuery(this).parents("tr:first").find(".ct_booked_job").val());
			var booking_status = '';
			var status_txt = '';
			var isFullyBooked = 0;



			if( time!='' ){

				// calculate status
				var status_dif = num_jobs-booked_job;

				var booking_status = getCTstatusReturnData(status_dif);

				if(booking_status=='FULL'){
					status_txt = '<span class="ct_full">FULL</span>';
					isFullyBooked = 1;
				}else{
					status_txt = '-'+status_dif;
				}


				// ajax
				jQuery.ajax({
					type: "POST",
					url: "ajax_update_colour_table_status.php",
					data: {
						tr_id: <?php echo $tr_id; ?>,
						colour_id: colour_id,
						booking_status: booking_status
					}
				}).done(function( ret ){
					// function here
				});


				jQuery(this).parents("tr:first").find(".ct_status").html(status_txt);
				jQuery(this).parents("tr:first").find(".ct_fully_booked").val(isFullyBooked);

				<?php
				if( $tr['run_complete'] != 1 && $tr['no_more_jobs'] != 1 ){ ?>
					hideFullyBookedJobs();
				<?php
				}
				?>

			}


		});

	}


	// Agency Booking Notes Script
	function displayAgencyBookingNotes(){
		var hasBookingNotes = jQuery("#hasBookingNotes").val();
		if( parseInt(hasBookingNotes) > 0 ){
			jQuery("#agencyNotesLink").show();
		}
	}


	// colour table: hide fully booked script
	function hideFullyBookedJobs(){

		jQuery(".ct_fully_booked").each(function(){

			var ct_trrc_id = jQuery(this).parents("tr:first").find(".ct_trrc_id").val();
			var isFullyBooked = jQuery(this).val();

			if( isFullyBooked == 1 ){
				jQuery('#tbl_maps tr[data-hlc_id="'+ct_trrc_id+'"]:not(".isBooked")').hide();
			}else{
				jQuery('#tbl_maps tr[data-hlc_id="'+ct_trrc_id+'"]:not(".isBooked")').show();
			}


		});

	}

	function hideNonBookedJobs(){

		jQuery("tr.tech_run_row.hasColor:not(.isBooked):visible").hide();

	}


<?php
}
?>




jQuery(document).ready(function(){

	// select agency jobs toggle
	jQuery("#select_agency_jobs").click(function(){

		var table = jQuery("#tbl_maps");
		var agency_dp_dom = jQuery("#jobs_per_agency_select");
		var agency_id = agency_dp_dom.val();

		if( agency_id > 0 ){

			var agency_id_arr = [];
			table.find(".agency_id").each(function(){

				var agency_id_hid_dom = jQuery(this);
				var agency_id_hid = agency_id_hid_dom.val();
				var parent_row = agency_id_hid_dom.parents(".tech_run_row");

				if( agency_id == agency_id_hid ){
					parent_row.find(".chk_trr_id:visible").prop("checked",true);
				}

			});

		}else{
			table.find(".chk_trr_id:visible:checked").prop("checked",false);
		}

		var checked_count = jQuery(".chk_trr_id:visible:checked").length;
		if( checked_count > 0 ){

			jQuery("#btn_assign_color_div").show();
			jQuery("#btn_remove_color_div").show();
			jQuery("#btn_assign_dk").show();
			jQuery("#btn_hide_row").show();

			jQuery("#hidden_button_div").show();

		}else{

			jQuery("#btn_assign_color_div").hide();
			jQuery("#btn_remove_color_div").hide();
			jQuery("#btn_assign_dk").hide();
			jQuery("#btn_hide_row").hide();

			jQuery("#hidden_button_div").hide();

		}

	});

	<?php
	if( $hasTechRun == true ){ ?>

		jQuery("#change_tech_update_btn").click(function(){

			// loop through checked item
			var trr_id_arr = [];
			jQuery(".chk_trr_id:visible:checked").each(function(){
				var trr_id = jQuery(this).val();
				trr_id_arr.push(trr_id);
			});

			//console.log("trr_id_arr: "+trr_id_arr);

			var change_to_tech_id = jQuery("#change_to_tech_id").val();
			var tech_name = jQuery("#tech_name").val();
			var change_to_tech_name = jQuery("#change_to_tech_id option:selected").attr("data-tech_name");
			var num_items = trr_id_arr.length;

			if( change_to_tech_id > 0 && num_items > 0 ){

				if( confirm("Do you want to move these "+num_items+" jobs to "+change_to_tech_name+"?") ){

					// invoke ajax
					jQuery("#load-screen").show();
					jQuery.ajax({
						type: "POST",
						url: "ajax_tech_run_change_tech.php",
						data: {
							trr_id_arr: trr_id_arr,
							change_to_tech_id: change_to_tech_id,
							from_tech_name: tech_name,
							change_to_tech_name: change_to_tech_name
						}
					}).done(function( ret ){

						jQuery("#load-screen").hide();
						window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';

					});

				}

			}else{
				alert("Tech is required");
			}

		});

	<?php
	}
	?>




	// time of day update
	jQuery(".time_of_day_link").click(function(){

		var obj = jQuery(this);
		obj.hide();
		obj.parents("td.time_of_day_td:first").find(".time_of_day_hid").show();
		obj.parents("td.time_of_day_td:first").find(".check_icon").show();

	});




	jQuery(".check_icon").click(function(){

		var obj = jQuery(this);
		var job_id = obj.parents("tr:first").find(".chk_job_id").val();
		var time_of_day = obj.parents("tr:first").find(".time_of_day_hid").val();

		// invoke ajax
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_job_time_of_day.php",
			data: {
				job_id: job_id,
				time_of_day: time_of_day
			}
		}).done(function( ret ){

			obj.parents("td.time_of_day_td:first").find(".check_icon").css("opacity","1");
			obj.parents("td.time_of_day_td:first").find(".time_of_day_hid").hide();
			obj.parents("td.time_of_day_td:first").find(".time_of_day_link").html(time_of_day);
			obj.parents("td.time_of_day_td:first").find(".time_of_day_link").show();
			obj.parents("td.time_of_day_td:first").find(".time_of_day_link").css("position","relative");
			obj.parents("td.time_of_day_td:first").find(".time_of_day_link").css("top","10px");
			jQuery("#load-screen").hide();

		});

	});




	// first visit script
	jQuery("#btn_first_visit").click(function(){

		var fv = jQuery(this).find(".btn_first_visit_span").html();
		var button_name_txt = 'Select First Visit';


		if( fv == button_name_txt ){
			jQuery(this).find(".btn_first_visit_span").html("Cancel");
			jQuery(".jrow_first_visit").find(".chk_trr_id:visible").prop("checked",true);
		}else{
			jQuery(this).find(".btn_first_visit_span").html(button_name_txt);
			jQuery(".jrow_first_visit").find(".chk_trr_id:visible").prop("checked",false);

		}

		var checked_count = jQuery(".chk_trr_id:checked").length;
		if( checked_count > 0 ){
			jQuery("#hidden_button_div").show();
		}else{
			jQuery("#hidden_button_div").hide();
		}

	});

	// escalate jobs script
	jQuery("#btn_escalate_jobs").click(function(){

		var fv = jQuery(this).find(".btn_escalate_jobs_span").html();
		var button_name_txt = 'Select Escalate Jobs';


		if( fv == button_name_txt ){
			jQuery(this).find(".btn_escalate_jobs_span").html("Cancel");
			jQuery(".jrow_escalate_jobs").find(".chk_trr_id:visible").prop("checked",true);
		}else{
			jQuery(this).find(".btn_escalate_jobs_span").html(button_name_txt);
			jQuery(".jrow_escalate_jobs").find(".chk_trr_id:visible").prop("checked",false);

		}

		var checked_count = jQuery(".chk_trr_id:checked").length;
		if( checked_count > 0 ){
			jQuery("#hidden_button_div").show();
		}else{
			jQuery("#hidden_button_div").hide();
		}

	});


	// escalate jobs script
	jQuery("#select_holiday_rental").click(function(){

		var fv = jQuery(this).find(".select_holiday_rental_span").html();
		var button_name_txt = 'Select Holiday Rental';


		if( fv == button_name_txt ){
			jQuery(this).find(".select_holiday_rental_span").html("Cancel");
			jQuery(".jrow_holiday_rental").find(".chk_trr_id:visible").prop("checked",true);
		}else{
			jQuery(this).find(".select_holiday_rental_span").html(button_name_txt);
			jQuery(".jrow_holiday_rental").find(".chk_trr_id:visible").prop("checked",false);

		}

		var checked_count = jQuery(".chk_trr_id:checked").length;
		if( checked_count > 0 ){
			jQuery("#hidden_button_div").show();
		}else{
			jQuery("#hidden_button_div").hide();
		}

	});


	/*
	// select 240v jobs script
	jQuery("#btn_select_240v_jobs").click(function(){

		var fv = jQuery(this).find(".btn_select_240v_jobs_span").html();
		var button_name_txt = 'Select 240v Jobs';


		if( fv == button_name_txt ){
			jQuery(this).find(".btn_select_240v_jobs_span").html("Cancel");
			jQuery(".jrow_240v_rebook").find(".chk_trr_id:visible").prop("checked",true);
		}else{
			jQuery(this).find(".btn_select_240v_jobs_span").html(button_name_txt);
			jQuery(".jrow_240v_rebook").find(".chk_trr_id:visible").prop("checked",false);

		}

		var checked_count = jQuery(".chk_trr_id:checked").length;
		if( checked_count > 0 ){
			jQuery("#hidden_button_div").show();
		}else{
			jQuery("#hidden_button_div").hide();
		}

	});
	*/


	// select job types script
	jQuery("#select_job_type_btn").click(function(){

		// clear ticks
		jQuery(".chk_trr_id:checked").prop("checked",false);
		jQuery(".rowHighlight").removeClass("rowHighlight");

		//var job_type = jQuery("#select_job_type").val();

		//gherx changes start
		var table = jQuery("#tbl_maps");

		var select_job_type = jQuery("#select_job_type option:selected").attr('data-attrTT');
		var jobs_per_agency_select = jQuery("#jobs_per_agency_select").val();

		var hid_job_type_marker = jQuery(".hid_job_type_marker").val()
		var hid_agency_id = jQuery(".agency_id").val()

		console.log(select_job_type);
		console.log(jobs_per_agency_select);
		console.log(hid_job_type_marker);

		if( jobs_per_agency_select>0 &&  select_job_type!=""){ //do combo filter
			
			table.find(".agency_id").each(function(){

				var agencyObj = $(this);
				var thisAgencyVal = agencyObj.val()

				var jobTypeVal = agencyObj.parents('.tech_run_row').find('.hid_job_type_marker').val()

				if( jobs_per_agency_select==thisAgencyVal ){

					if( select_job_type==jobTypeVal ){
						agencyObj.parents('.tech_run_row').find(".chk_trr_id:visible").prop("checked",true);
						agencyObj.parents('.tech_run_row').addClass("rowHighlight");
					}

				}

			});

		}else if(select_job_type!="" && jobs_per_agency_select<=0){ //job type filter only regardless of agency

			console.log("ELSE");

			table.find(".hid_job_type_marker").each(function(){

				var jtObj = $(this);
				var thisJtVal = jtObj.val()

				console.log("JT VAL");
				console.log(thisJtVal);
				console.log(select_job_type);

				if( select_job_type==thisJtVal ){

					jtObj.parents('.tech_run_row').find(".chk_trr_id:visible").prop("checked",true);
					jtObj.parents('.tech_run_row').addClass("rowHighlight");

				}

			});

		}
		//gherx changes end
		
		/*
		if( job_type == 'Once-off' ){
			jQuery(".jrow_once_off").find(".chk_trr_id:visible").prop("checked",true);
			jQuery(".jrow_once_off").addClass("rowHighlight");
		}

		if( job_type == 'Change of Tenancy' ){
			jQuery(".jrow_cot").find(".chk_trr_id:visible").prop("checked",true);
			jQuery(".jrow_cot").addClass("rowHighlight");
		}

		if( job_type == 'Yearly Maintenance' ){
			jQuery(".jrow_ym").find(".chk_trr_id:visible").prop("checked",true);
			jQuery(".jrow_ym").addClass("rowHighlight");
		}

		if( job_type == 'Fix or Replace' ){
			jQuery(".jrow_fr").find(".chk_trr_id:visible").prop("checked",true);
			jQuery(".jrow_fr").addClass("rowHighlight");
		}

		if( job_type == '240v Rebook' ){
			jQuery(".jrow_240v_rebook").find(".chk_trr_id:visible").prop("checked",true);
			jQuery(".jrow_240v_rebook").addClass("rowHighlight");
		}

		if( job_type == 'Lease Renewal' ){
			jQuery(".jrow_lr").find(".chk_trr_id:visible").prop("checked",true);
			jQuery(".jrow_lr").addClass("rowHighlight");
		}

		if( job_type == 'IC Upgrade' ){
			jQuery(".jrow_ic_upg").find(".chk_trr_id:visible").prop("checked",true);
			jQuery(".jrow_ic_upg").addClass("rowHighlight");
		}

		if( job_type == 'Annual Visit' ){
			jQuery(".jrow_annual_vis").find(".chk_trr_id:visible").prop("checked",true);
			jQuery(".jrow_annual_vis").addClass("rowHighlight");
		}
		*/

		var checked_count = jQuery(".chk_trr_id:visible:checked").length;
		if( checked_count > 0 ){

			jQuery("#btn_assign_color_div").show();
			jQuery("#btn_remove_color_div").show();
			jQuery("#btn_assign_dk").show();
			jQuery("#btn_hide_row").show();

			jQuery("#hidden_button_div").show();

		}else{

			jQuery("#btn_assign_color_div").hide();
			jQuery("#btn_remove_color_div").hide();
			jQuery("#btn_assign_dk").hide();
			jQuery("#btn_hide_row").hide();

			jQuery("#hidden_button_div").hide();

		}

	});


	// select no tenant details
	jQuery("#btn_select_no_tenant_details").click(function(){

		var fv = jQuery(this).find(".select_no_tenant_details_span").html();
		var button_name_txt = 'Select No Tenant Details';

		if( fv == button_name_txt ){

			jQuery(this).find(".select_no_tenant_details_span").html("Cancel");

			jQuery(".no_tenant_icon").each(function(){

				var no_tenant_icon_dom = jQuery(this);
				var prop_vacant = no_tenant_icon_dom.attr("data-prop_vacant");

				if( prop_vacant == 0 ){
					no_tenant_icon_dom.parents("tr:first").find(".chk_trr_id:visible").prop("checked",true);
				}

			});

		}else{

			jQuery(this).find(".select_no_tenant_details_span").html(button_name_txt);

			jQuery(".no_tenant_icon").each(function(){

				var no_tenant_icon_dom = jQuery(this);
				var prop_vacant = no_tenant_icon_dom.attr("data-prop_vacant");

				if( prop_vacant != 1 ){
					no_tenant_icon_dom.parents("tr:first").find(".chk_trr_id:visible").prop("checked",false);
				}

			});
		}

		var checked_count = jQuery(".chk_trr_id:checked").length;
		if( checked_count > 0 ){
			jQuery("#hidden_button_div").show();
		}else{
			jQuery("#hidden_button_div").hide();
		}

	});


	<?php
	if( $hasTechRun == true ){ ?>


		displayAgencyBookingNotes();


		// count number of booked jobs
		countNumOfBookedJobsEachColor();

		// update colour table status
		updateStatusColourTableBooked();

		// selects the previous tab on load
		var curr_tab = $.cookie('str_tab_index');
		if( curr_tab!='' ){

			if(curr_tab!=''){
				myTabs.goToTab(curr_tab);
			}

		}
		// keep tab script
		jQuery(".c-tabs-nav__link").click(function(){

			var tab_index = jQuery(this).attr('data-tab_index');
			//console.log(tab_index);
			$.cookie('str_tab_index', tab_index);

		});


		// colour table
		jQuery(".ct_time, .ct_jobs, .ct_no_keys_chk").change(function(){

			var obj = jQuery(this);
			upateTRcolourTable(obj)

		});

		<?php
		if( $tr['run_complete'] != 1 && $tr['no_more_jobs'] != 1 ){ ?>
			hideFullyBookedJobs();
		<?php
		}
		?>



	<?php
	}
	?>



	// check calendar entry
	jQuery("#tech_id").change(function(){

		var tech_id = jQuery(this).val();
		var date = jQuery("#date").val();

		if( date!='' ){


			// invoke ajax
			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "ajax_check_str_calendar_entry.php",
				dataType: 'json',
				data: {
					tech_id: tech_id,
					date: date
				}
			}).done(function( ret ){

				var cal_id = parseInt(ret.cal_id);

				if(cal_id!=''){
					jQuery("#calendar_id").val(ret.cal_id);
					jQuery("#calendar_name").val(ret.cal_name);
				}

				jQuery("#load-screen").hide();
				//window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';

			});

		}


	});




	// success message hide script
	var myVar = setInterval(function(){

		jQuery(".success").slideUp();

	}, 5000);


	// colour table "NO keys" column red cross toggle script
	jQuery(".ct_no_keys_chk").change(function(){

		var state_chk = jQuery(this).prop("checked");
		if( state_chk == true ){
			jQuery(this).parents("tr:first").find(".redCross").show();
		}else{
			jQuery(this).parents("tr:first").find(".redCross").hide();
		}

	});




	// show/hide accom
	jQuery("#start_point, #end_point").change(function(){

		var accomodation_id = jQuery("#tech_accom_id").val();
		showHideAccom(accomodation_id);

	});


	// update Agency Selected Number
	jQuery(document).on("click",".agency",function(){
		var sel_count = jQuery(".agency:checked").size();
		//console.log(sel_count);
		jQuery("#agency_ms").val(sel_count+" Selected");
	});

	// update Agency Selected Number
	jQuery(document).on("click",".postcode_region_id",function(){
		var sel_count = jQuery(".postcode_region_id:checked").size();
		jQuery("#agency_filter_main_div").hide();
		//console.log(sel_count);
		jQuery("#region_ms").val(sel_count+" Selected");
	});

	// prefill booking staff with tech call center upon select
	jQuery("#tech_id").change(function(){

		var tech = parseInt(jQuery(this).val());
		jQuery("#booking_staff option").prop("selected",false);

		if( tech>0 ){
			jQuery("#load-screen").show();
			// invoke ajax
			jQuery.ajax({
				type: "POST",
				url: "ajax_get_tech_call_centre.php",
				dataType: 'json',
				data: {
					tech: tech
				}
			}).done(function( ret ){
				var call_centre = parseInt(ret.other_call_centre);
				var accomodation_id = parseInt(ret.accomodation_id);

				jQuery("#tech_accom_id").val(accomodation_id);

				// call centre
				jQuery("#booking_staff option").each(function(){

					if( jQuery(this).val()==call_centre ){
						jQuery(this).prop("selected",true);
					}

				});

				// accomodation
				// start
				jQuery("#start_point option").each(function(){

					if( jQuery(this).val()==accomodation_id ){
						jQuery(this).prop("selected",true);
					}

				});
				// end
				jQuery("#end_point option").each(function(){

					if( jQuery(this).val()==accomodation_id ){
						jQuery(this).prop("selected",true);
					}

				});

				showHideAccom(accomodation_id);

				jQuery("#load-screen").hide();
				//window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';

			});
		}

	});



	<?php
	if( $hasTechRun == true ){ ?>


		jQuery(".btn_select_uncolored").click(function(){

			var fv = jQuery(this).find(".btn_select_uncolored_span").html();
			var button_name_txt = 'Select Uncoloured';

			if( fv == button_name_txt ){

				jQuery(this).find(".btn_select_uncolored_span").html("Cancel");
				// select uncolored script
				jQuery(".NoColor:visible").each(function(){
					jQuery(this).find(".chk_trr_id").prop("checked",true);
				});

			}else{

				jQuery(this).find(".btn_select_uncolored_span").html(button_name_txt);
				// select uncolored script
				jQuery(".NoColor:visible").each(function(){
					jQuery(this).find(".chk_trr_id").prop("checked",false);
				});

			}


			var checked_count = jQuery(".chk_trr_id:checked").length;
			if( checked_count > 0 ){
				jQuery("#hidden_button_div").show();
				jQuery("#btn_assign_color_div").show();
			}else{
				jQuery("#hidden_button_div").hide();
				jQuery("#btn_assign_color_div").hide();
			}

			/*
			// select uncolored script
			jQuery(".NoColor:visible").each(function(){

			  jQuery(this).find(".chk_trr_id").prop("checked",true);
			  jQuery("#hidden_button_div").show();
			  jQuery("#btn_assign_color_div").show();

			});
			*/

		});





		// display num script
		jQuery("#display_num").change(function(){

			var display_num = jQuery(this).val()

			// invoke ajax
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_str_display_num.php",
				data: {
					tr_id: <?php echo $tr_id; ?>,
					display_num: display_num
				}
			}).done(function( ret ){
				window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
			});

		});

	<?php
	}
	?>



	// get unique agency from STR page
	getUniqueAgenciesFromTheList();


	jQuery("#accomodation").change(function(){
			var opt = jQuery(this).val();
			if(opt==1||opt==2){
				jQuery("#sel_acco").show();
			}else{
				jQuery("#sel_acco").hide();
			}
		});


	<?php
	if( $hasTechRun == true ){ ?>
		getTechRunNewLists(0);
	<?php
	}
	?>

	// calendar update script
	// note button hide/show script
	jQuery("#calendar_name, #accomodation, #accomodation_id, #booking_staff").change(function(){
		jQuery("#btn_cal").show();
	});

	jQuery("#btn_cal").click(function(){

		var calendar_id = jQuery("#calendar_id").val();
		var calendar_name = jQuery("#calendar_name").val();
		var accomodation = jQuery("#accomodation").val();
		var accomodation_id = jQuery("#accomodation_id").val();
		var booking_staff = jQuery("#booking_staff").val();

		jQuery.ajax({
			type: "POST",
			url: "ajax_update_calendar.php",
			data: {
				calendar_id: calendar_id,
				calendar_name: calendar_name,
				accomodation: accomodation,
				accomodation_id: accomodation_id,
				booking_staff: booking_staff
			}
		}).done(function( ret ){
			window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
		});

	});

	jQuery("#btn_distance_to_agency").click(function(){

		if(jQuery(this).html()=="DISPLAY distance to agency"){
			jQuery(this).html("Cancel");
			jQuery(".DTA_elem").show();

			jQuery("#load-screen").show();
			jQuery(".DTA_val").each(function(){

				var obj = jQuery(this);
				var orig_add = obj.parents("tr:first").find(".job_prop_add").val();
				var dist_add = obj.parents("tr:first").find(".agency_add").val();

				setTimeout(function(){



					//console.log("Property Address: "+job_prop_add+"\nAgency Address: "+agency_add);

					jQuery("#load-screen").show();
					jQuery.ajax({
						type: "POST",
						url: "ajax_get_distance.php",
						data: {
							orig_add: orig_add,
							dist_add: dist_add
						}
					}).done(function( ret ){

						jQuery("#load-screen").hide();
						obj.html(ret);

						//window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
						//location.reload();
						//window.location='/main.php';

					});

				}, 1000);

			});
			jQuery("#load-screen").hide();

		}else{
			jQuery(this).html("DISPLAY distance to agency");
			jQuery(".DTA_elem").hide();
		}


	});



	// update EN short url link
	jQuery(".en_time").change(function(){

		var obj = jQuery(this);
		var en_hidden_orig_url = obj.parents("tr:first").find(".en_hidden_orig_url").val();
		var en_time = obj.parents("tr:first").find(".en_time").val();

		/*
		jQuery.ajax({
				type: "POST",
				url: "ajax_convertToGoogleShortUrl.php",
				data: {
					en_hidden_orig_url: en_hidden_orig_url,
					tr_id: '<?php echo $tr_id; ?>',
					en_time: en_time
				}
			}).done(function( ret ){

				obj.parents("tr:first").find(".en_a_link").prop("href",ret);

				//window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
				//location.reload();
				//window.location='/main.php';
			});
			*/

			var fin_url = en_hidden_orig_url+"&tr_id=<?php echo $tr_id; ?>&en_time="+en_time;
			obj.parents("tr:first").find(".en_a_link").prop("href",fin_url);


	});


	// EN issue
	jQuery(".en_fields").click(function(){
		showENissueButton();
	});


	// EN script
	jQuery("#btn_EN").click(function(){

		if(jQuery(this).find(".btn_en_inner_txt").html()=="Entry Notice"){
			jQuery(this).find(".btn_en_inner_txt").html("Cancel");
			jQuery(".EN_hide_elem").hide();
			jQuery(".EN_show_elem").show();
			jQuery("#tbl_maps tr.str_header_row").addClass("green-btn");
			jQuery("#tbl_maps tr.str_header_row").css("border-bottom","none");

			// hide escalate job on EN
			jQuery(".isEscalateJobClass").hide();
			jQuery(".is_no_en .chk_trr_id").hide();
			jQuery(".chk_no_tenant").hide();
			jQuery(".invalid_en_icon").show();

			jQuery("#btn_EN_hidden").val(1);
		}else{
			jQuery(this).find(".btn_en_inner_txt").html("Entry Notice");
			jQuery(".EN_hide_elem").show();
			jQuery(".EN_show_elem").hide();
			jQuery("#tbl_maps tr.str_header_row").removeClass("green-btn");
			jQuery("#tbl_maps tr.str_header_row").css("border-bottom","1px solid #b4151b !important");

			// redisplay the hidden escalate job upon exiting EN view
			jQuery(".isEscalateJobClass").show();
			jQuery(".is_no_en .chk_trr_id").show();
			jQuery(".chk_no_tenant").show();
			jQuery(".invalid_en_icon").hide();

			jQuery("#btn_EN_hidden").val(0);
		}

	});




	// sort by distance script
	jQuery("#btn_sort_by_distance").click(function(){

		if( confirm("Are you sure you want to sort by distance?")==true ){

			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "ajax_tech_run_sort_by_distance.php",
				data: {
					tr_id: '<?php echo $tr_id; ?>'
				}
			}).done(function( ret ){
				jQuery("#load-screen").hide();
				window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
				//location.reload();
				//window.location='/main.php';
			});

		}


	});


	// ajax send entry notice in bulk script
	jQuery("#btn_issue_EN").click(function(){

		//console.log("Bugo");
		//return false;

		var job_ids = new Array();
		var en_email_arr = new Array();
		var en_sms_arr = new Array();
		var en_time_arr = new Array();

		// loop through selected item
		jQuery(".chk_trr_id:checked:visible").each(function(){

			//var trr_id = jQuery(this).val();
			//trr_id_arr.push(trr_id);

			var trr_row_type = jQuery(this).parents("tr:first").find(".trr_row_type").val();

			// store jobs id
			if(trr_row_type=='job_id'){
				var job_id = jQuery(this).parents("tr:first").find(".chk_job_id").val();
				job_ids.push(job_id);
				var en_email = (jQuery(this).parents("tr:first").find(".en_email").prop("checked")==true)?1:0;
				en_email_arr.push(en_email);
				var en_sms = (jQuery(this).parents("tr:first").find(".en_sms").prop("checked")==true)?1:0;
				en_sms_arr.push(en_sms);
				var en_time = jQuery(this).parents("tr:first").find(".en_time").val();
				en_time_arr.push(en_time);
			}

		});

		var ajax_url = "ajax_send_entry_notice_in_bulk.php";
		//var ajax_url = "ajax_send_entry_notice_in_bulk2.php";

		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: ajax_url,
			data: {
				job_ids: job_ids,
				str_tech: '<?php echo $tech_id; ?>',
				str_date: '<?php echo $date; ?>',
				en_time_arr:en_time_arr
			}
		}).done(function( ret ){
			jQuery("#load-screen").hide();
			window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
			//location.reload();
			//window.location='/main.php';
		});

	});

	// remove row highlight color script
	jQuery("#btn_remove_color").click(function(){

		var trr_id_arr = new Array();

		jQuery(".chk_trr_id:checked").each(function(){

			var trr_id = jQuery(this).val();
			trr_id_arr.push(trr_id);

		});

		jQuery.ajax({
			type: "POST",
			url: "ajax_tech_run_remove_job_row_color.php",
			data: {
				tr_id: '<?php echo $tr_id; ?>',
				trr_id_arr: trr_id_arr
			}
		}).done(function( ret ){
			window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
			//location.reload();
			//window.location='/main.php';
		});

	});

	/// assign row highlight color script
	jQuery("#btn_assign_color").click(function(){

		var trr_id_arr = new Array();
		var trr_hl_color = jQuery("#row_highlight_color").val();

		jQuery(".chk_trr_id:checked").each(function(){

			var trr_id = jQuery(this).val();
			trr_id_arr.push(trr_id);

		});

		jQuery.ajax({
			type: "POST",
			url: "ajax_tech_run_assign_job_row_color.php",
			data: {
				tr_id: '<?php echo $tr_id; ?>',
				trr_id_arr: trr_id_arr,
				trr_hl_color: trr_hl_color
			}
		}).done(function( ret ){
			window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
			//location.reload();
			//window.location='/main.php';
		});

	});




	// hidden rows ccount script
	var hiddenRowsCount = jQuery("#hiddenRowsCount").val();
	jQuery("#hiddenRowsCount_span").html(hiddenRowsCount);



	jQuery("#btn_show_hidden_rows").click(function(){

		jQuery.ajax({
			type: "POST",
			url: "ajax_tech_run_update_hidden_state.php",
			data: {
				tr_id: '<?php echo $tr_id; ?>',
				show_hidden: 1
			}
		}).done(function( ret ){
			window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
			//location.reload();
			//window.location='/main.php';
		});

	});


	jQuery("#btn_hide_hidden_rows").click(function(){

		jQuery.ajax({
			type: "POST",
			url: "ajax_tech_run_update_hidden_state.php",
			data: {
				tr_id: '<?php echo $tr_id; ?>',
				show_hidden: 0
			}
		}).done(function( ret ){
			window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
			//location.reload();
			//window.location='/main.php';
		});

	});

	// hide tech rows script
	jQuery("#btn_hide_row").click(function(){

		var trr_id_arr = new Array();
		var isBooked = 0;
		jQuery(".chk_trr_id:checked").each(function(){

			var obj = jQuery(this);
			var trr_id = obj.val();
			trr_id_arr.push(trr_id);

			var jt = obj.parents("tr:first").find(".trr_job_type").val();
			if( jt=="Booked" ){
				isBooked = 1;
			}

		});

		if( isBooked==1 ){
			alert("Booked jobs can't be hidden");
		}else{

			if( confirm("Are you sure you want to hide all selected list?")==true ){
				jQuery.ajax({
					type: "POST",
					url: "ajax_tech_run_set_hidden_status.php",
					data: {
						tr_id: '<?php echo $tr_id; ?>',
						trr_id_arr: trr_id_arr,
						operation: 'hide'
					}
				}).done(function( ret ){
					window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
					//location.reload();
					//window.location='/main.php';
				});
			}

		}





	});


	// show tech rows script
	jQuery("#btn_unhide_row").click(function(){

		var trr_id_arr = new Array();
		var job_id_arr = new Array();
		jQuery(".chk_trr_id:checked").each(function(){

			var trr_id = jQuery(this).val();
			var job_id = jQuery(this).parents("tr:first").find(".chk_job_id").val();

			trr_id_arr.push(trr_id);
			job_id_arr.push(job_id);

		});

		if( confirm("Are you sure you want to show all hidden jobs?")==true ){
			jQuery.ajax({
				type: "POST",
				url: "ajax_tech_run_set_hidden_status.php",
				data: {
					tr_id: '<?php echo $tr_id; ?>',
					trr_id_arr: trr_id_arr,
					job_id_arr: job_id_arr,
					operation: 'unhide'
				}
			}).done(function( ret ){
				window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
				//location.reload();
				//window.location='/main.php';
			});
		}


	});


	jQuery("#mark_tech_sick_btn").click(function(){

		var job_id_arr = [];
		jQuery(".chk_trr_id:checked:visible").each(function(){

			var job_id = jQuery(this).parents("tr:first").find(".chk_job_id").val();
			job_id_arr.push(job_id);

		});

		var comment = 'Bulk marked tech sick on <b><?php echo $today; ?></b> by <b><?php echo $logged_staff_name; ?></b>';

		// confirm
		if( confirm("Are you sure you want to mark all selected list as sick tech?")==true ){

			var jr_id = 25; // Staff Sick
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_job_reason.php",
				data: {
					job_id_arr: job_id_arr,
					jr_id: jr_id,
					comment: comment
				}
			}).done(function (ret) {

				window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';

			});

		}

	});


	/*
	// show tech rows script
	jQuery("#btn_show_row").click(function(){

		if( confirm("Are you sure you want to show all hidden list?")==true ){
			jQuery.ajax({
				type: "POST",
				url: "ajax_tech_run_show_all_rows.php",
				data: {
					tr_id: '<?php echo $tr_id; ?>'
				}
			}).done(function( ret ){
				window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
				//location.reload();
				//window.location='/main.php';
			});
		}


	});
	*/


	// sort by suburb ajax
	jQuery("#btn_sort_street").click(function(){

		if( confirm("Are you sure you want to sort them via street?")==true ){
			jQuery.ajax({
				type: "POST",
				url: "ajax_tech_run_sort_by_street.php",
				data: {
					tr_id: '<?php echo $tr_id; ?>',
					country_id: <?php echo $_SESSION['country_default']; ?>
				}
			}).done(function( ret ){
				window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
				//location.reload();
				//window.location='/main.php';
			});
		}


	});



	// sort by suburb ajax
	jQuery("#btn_sort_suburb").click(function(){

		if( confirm("Are you sure you want to sort them via suburb?")==true ){
			jQuery.ajax({
				type: "POST",
				url: "ajax_tech_run_sort_by_suburb.php",
				data: {
					tr_id: '<?php echo $tr_id; ?>',
					country_id: <?php echo $_SESSION['country_default']; ?>
				}
			}).done(function( ret ){
				window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
				//location.reload();
				//window.location='/main.php';
			});
		}


	});


	// sort by suburb ajax
	jQuery(".btn_sort_by_color").click(function(){

		if( confirm("Are you sure you want to sort them by color?")==true ){
			jQuery.ajax({
				type: "POST",
				url: "ajax_tech_run_sort_by_color.php",
				data: {
					tr_id: '<?php echo $tr_id; ?>',
					country_id: <?php echo $_SESSION['country_default']; ?>
				}
			}).done(function( ret ){
				window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
				//location.reload();
				//window.location='/main.php';
			});
		}


	});

	/*
	// note button hide/show script
	jQuery("#notes").click(function(){
		jQuery("#btn_notes").show();
	});
	*/

	/*
	jQuery("#notes").blur(function(){
		jQuery("#btn_notes").hide();
	});
	*/


	// save notes
	jQuery("#notes").change(function(){

		var notes = jQuery(this).val();

		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_tech_run_update_notes.php",
			data: {
				tr_id: '<?php echo $tr_id; ?>',
				notes: notes
			}
		}).done(function( ret ){
			jQuery("#load-screen").hide();
			jQuery(".jtimeStamp").html(ret);
			//window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
		});

	});



	// save start/end point
	jQuery("#btn_set_start_end_save").click(function(){

		var start_point = jQuery("#start_point").val();
		var end_point = jQuery("#end_point").val();

		jQuery.ajax({
			type: "POST",
			url: "ajax_tech_run_set_start_end_point.php",
			data: {
				tr_id: '<?php echo $tr_id; ?>',
				start_point: start_point,
				end_point: end_point
			}
		}).done(function( ret ){
			window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
		});

	});


	jQuery("#btn_save_sub_region").click(function(){

		var date = jQuery("#date").val();
		var tech_id = jQuery("#tech_id").val();
		var start_point = jQuery("#start_point").val();
		var end_point = jQuery("#end_point").val();
		var tr_already_exist = jQuery("#tr_already_exist").val();
		var error = "";

		if(date==""){
			error += "Date is required\n";
		}

		if(tech_id==""){
			error += "Tech is required\n";
		}

		if(start_point==""){
			error += "Start point is required\n";
		}

		if(end_point==""){
			error += "End Point is required\n";
		}

		if( tr_already_exist == 1 ){
			error += "This tech run already exist\n";
		}

		if(error!=""){
			alert(error);
		}else{
			console.log("success");
			jQuery("#tech_run_form").submit();
		}

	});

	<?php
	if( $hasTechRun==false ){ ?>

		jQuery("#date,#tech_id").change(function(){

			var date = jQuery("#date").val();
			var tech_id = jQuery("#tech_id").val();

			if( date!="" && tech_id!="" ){

				jQuery("#load-screen").show();
				jQuery.ajax({
					type: "POST",
					url: "ajax_check_if_tech_run_already_exist.php",
					data: {
						date: date,
						tech_id: tech_id
					}
				}).done(function( ret ){
					jQuery("#load-screen").hide();
					//window.location='/maps.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>';
					//location.reload();
					//window.location='/main.php';
					if(ret=="1"){
						jQuery("#tr_already_exist").val(1);
						alert("This tech run already exist");
					}else{
						jQuery("#tr_already_exist").val(0);
					}
				});

			}

		});

	<?php
	}
	?>




	// remove map routes
	jQuery("#btn_delete_mp").click(function(){

		if( confirm("Are you sure you want to delete?")==true ){
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_tech_run.php",
				data: {
					tech_run_id: '<?php echo $tr_id; ?>'
				}
			}).done(function( ret ){
				//window.location='/maps.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>';
				//location.reload();
				window.location='/main.php';
			});
		}


	});

	<?php
	if( $hasTechRun==true ){ ?>
		// notes auto height script
		//jQuery("#notes").height( jQuery("#notes")[0].scrollHeight );
		//jQuery("#notes").width( jQuery("#notes").innerWidth() );
	<?php
	}
	?>


	// number of selected items script
	var sel_sub_reg = jQuery("#selected_sub_regions").val();
	if( sel_sub_reg == "" ){
		var sel_sub_reg_count = 0;
	}else{
		var sel_sub_reg_arr = sel_sub_reg.split(',');
		var sel_sub_reg_count = sel_sub_reg_arr.length;
	}

	jQuery("#region_ms").val(sel_sub_reg_count+' Selected');

	// assign to DK
	jQuery("#btn_assign_dk").click(function(){

		var job_id = new Array();
		var tech_id = '<?php echo $tech_id; ?>';
		var date = '<?php echo $date; ?>';
		var agency_id_arr = new Array();
		var agency_arr = new Array();
		var no_dk_arr = [];
		var no_dk_prop_arr = [];
		var error = '';

		jQuery(".chk_trr_id:checked").each(function(){

			var trr_row_type = jQuery(this).parents("tr:first").find(".trr_row_type").val();

			if( trr_row_type=='job_id' ){

				var jid = jQuery(this).parents("tr:first").find(".chk_job_id").val();
				var is_dk_allowed = jQuery(this).parents("tr:first").find(".is_dk_allowed").val();

				if(is_dk_allowed==1){
					job_id.push(jid);
				}else{
					var agency_id = jQuery(this).parents("tr:first").find(".agency_id").val();
					var agency_name = jQuery(this).parents("tr:first").find(".agency_td").text();
					if(jQuery.inArray(agency_id,agency_id_arr)===-1){
						agency_id_arr.push(agency_id);
						agency_arr.push(agency_name);
					}
				}

				// no DK
				var no_dk = jQuery(this).parents("tr:first").find(".no_dk").val();
				var p_address = jQuery(this).parents("tr:first").find(".p_address").val();
				if( parseInt(no_dk) ==1 ){
					no_dk_arr.push(jid);
					no_dk_prop_arr.push(p_address);
				}

			}

		});


		if( no_dk_arr.length > 0 ){
			error += "The following properties doesn't allow DK, please unselect them: \n\n";
			for( var i=0; i<no_dk_prop_arr.length; i++ ){
				error += no_dk_prop_arr[i]+"\n";
			}
		}

		if( error !='' ){
			alert(error);
		}else{

			if( job_id.length>0 && tech_id!="" && date!="" ){

				if(agency_arr.length>0){
					var msg = "These agencies are not allowed Dks: \n\n";
					for(var i=0;i<agency_arr.length;i++){
						msg += agency_arr[i]+" \n";
					}
					msg += "\n";
					msg += "Other jobs will be added as DKs \n";
					msg += "Press OK to continue";
					if(confirm(msg)){


						jQuery.ajax({
							type: "POST",
							url: "ajax_to_be_booked_assign_dk.php",
							data: {
								job_id: job_id,
								tech_id: tech_id,
								date: date
							}
						}).done(function( ret ){
							//window.location='/maps.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>';
							//location.reload();
							window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
						});



					}
				}else{

					console.log("number of not allowed DKs agencies: "+agency_arr.length);

					jQuery.ajax({
						type: "POST",
						url: "ajax_to_be_booked_assign_dk.php",
						data: {
							job_id: job_id,
							tech_id: tech_id,
							date: date
						}
					}).done(function( ret ){
						//window.location='/maps.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>';
						//location.reload();
						window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
					});

				}

			}else{

				var msg = "These agencies are not allowed Dks: \n\n";
				for(var i=0;i<agency_arr.length;i++){
					msg += agency_arr[i]+" \n";
				}

				alert(msg)

			}

		}


	});



	// Escalate
	jQuery("#str_func_escalate_btn").click(function(){

		var job_id = new Array();

		jQuery(".chk_trr_id:checked").each(function(){

			var jid = jQuery(this).parents("tr:first").find(".chk_job_id").val();

			job_id.push(jid);


		});


		console.log(job_id);


		if( job_id.length > 0  ){

			jQuery.ajax({
				type: "POST",
				url: "ajax_str_process_escalate.php",
				data: {
					job_id: job_id
				}
			}).done(function( ret ){
				window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
			});

		}



	});



	function systemClick(obj){
		//obj.prop("checked",true);
		obj.click();
		//obj.prop("checked",true);
	}

	// get main region
	function getMainRegion(obj){

		var state_val = obj.val();

		obj.prop("checked",true);
		//jQuery("#load-screen").show();

		jQuery.ajax({
			type: "POST",
			url: "ajax_getMainRegionsViaState.php",
			data: {
				state: state_val
			}
		}).done(function( ret ){
			jQuery("#load-screen").hide();
			jQuery(".region_dp_body").append(ret);


			// get selected sub region
			var reg = jQuery(".selected_regions").val();
			sel_reg_arr = reg.split(",");
			var i;

			jQuery(".reg_db_main_reg").each(function(){
				var obj = jQuery(this);
				var reg_val = obj.parents(".region_wrapper").find(".sel_region_id").val();
				if( sel_reg_arr.indexOf(reg_val)!=-1 ){
					obj.click();
				}
			});

		});

	}


	// region pre load script
	if( jQuery("#selected_sub_regions").val()!="" ){

		// show region dropdown
		//jQuery("#region_ms").click();

		// get selected region
		var state = jQuery(".selected_state").val();
		sel_state_arr = state.split(",");
		var i;
		for( i=0; i < sel_state_arr.length; i++ ){

			jQuery(".state_ms").each(function(){
				var obj = jQuery(this);
				var state_val = obj.val();
				if(state_val==sel_state_arr[i]){
					getMainRegion(obj);
				}

			});
		}

	}






	// add/remove sub region
	jQuery(document).on("click",".postcode_region_id",function(){

		var sub_reg_id = jQuery(this).val();
		var chk_sel = jQuery(this).prop("checked");
		// add
		if(chk_sel==true){
			var sel_sub_reg = jQuery("#selected_sub_regions").val();
			if( sel_sub_reg.indexOf(sub_reg_id)==-1 ){
				var pc = (sel_sub_reg=="")?sub_reg_id:","+sub_reg_id;
				jQuery("#selected_sub_regions").val(sel_sub_reg+pc);
			}
		}else{ // remove
			var sel_sub_reg = jQuery("#selected_sub_regions").val();
			//console.log(sel_sub_reg.indexOf(sub_reg_id));
			var match_pos = sel_sub_reg.indexOf(sub_reg_id);
			if( match_pos >-1 ){
				//console.log("in");
				// if only one left
				if( sel_sub_reg.indexOf(',')==-1 ){
					var find = sub_reg_id;
				}else{
					var find = (match_pos==0)?sub_reg_id+",":","+sub_reg_id;
				}

				var sel_sub_reg_fin = sel_sub_reg.replace(find, "");
				jQuery("#selected_sub_regions").val(sel_sub_reg_fin);
			}
		}

	});

	/*
	// region multi select - region check all sub
	jQuery(document).on("click",".region_check_all",function(){
		var chk_state = jQuery(this).prop("checked");
		if(chk_state==true){
			jQuery(this).parents("li:first").find(".reg_db_sub_reg input").prop("checked",true);
		}else{
			jQuery(this).parents("li:first").find(".reg_db_sub_reg input").prop("checked",false);
		}

	});
	*/

	// region multi select script
	jQuery(document).on('click',".state_ms",function(){

		var obj = jQuery(this);
		var state = obj.val();
		var state_chk = obj.prop("checked");

		console.log(state_chk);



		if(state_chk==true){

			//obj.prop("checked",true);
			jQuery("#load-screen").show();

			jQuery.ajax({
				type: "POST",
				url: "ajax_getMainRegionsViaState.php",
				data: {
					state: state
				}
			}).done(function( ret ){
				jQuery("#load-screen").hide();
				jQuery(".region_dp_body").append(ret);
			});

		}else{
			jQuery("."+state+"_regions").remove();
		}



	});


	// region multiselect - get sub region
	jQuery(document).on("click",".reg_db_main_reg",function(){

		var obj = jQuery(this);
		var region = obj.parents("li:first").find(".regions_id").val();
		var sub_reg_space = obj.parents("li:first").find(".reg_db_sub_reg").html();
		var sel_sub_regions = jQuery(".selected_sub_regions").val();
		var check_all = obj.parents("li.main_region_li").find(".check_all_sub_region").prop("checked");



		if(sub_reg_space==""){

			jQuery("#load-screen").show();

			jQuery.ajax({
				type: "POST",
				url: "ajax_getSubRegionsViaRegion.php",
				data: {
					region: region,
					return_type: 'region_id',
					sel_sub_regions: sel_sub_regions
				}
			}).done(function( ret ){
				jQuery("#load-screen").hide();
				obj.parents("li:first").find(".reg_db_sub_reg").html(ret);
				if( check_all == true ){
					obj.parents("li.main_region_li").find(".postcode_region_id").prop("checked",true);
				}
			});

		}else{
			obj.parents("li:first").find(".reg_db_sub_reg").html("");
		}

	});


	// set start/end point
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


});

<?php
if( $tech_id!="" && $date!="" ){
?>

jQuery(document).ready(function(){

	// missing border-top fix
	jQuery(".hiddenJobs, .redBorder").each(function(){

		jQuery(this).prev().css("border-bottom","none");

	});

	jQuery("#tech_run_functions").change(function(){

		jQuery(".function_btn_div").hide();

		var func = jQuery(this).val();

		switch( func ){
			case 'dk':
				jQuery("#dk_main_div").show();
			break;
			case 'keys':
				jQuery("#keys_main_div").show();
			break;
			case 'hide':
				jQuery("#hide_show_row_main_div").show();
			break;
			case 'highlight':
				jQuery("#highlight_color_main_div").show();
			break;
			case 'escalate':
				jQuery("#str_func_escalate_div").show();
			break;
			case 'change_tech':
				jQuery("#change_tech_div").show();
			break;
			case 'mark_tech_sick':
				jQuery("#mark_tech_sick_div").show();
			break;

		}

	});

	// remove keys
	jQuery("#btn_remove_keys").click(function(){

		var trr_id_arr = new Array();
		jQuery(".chk_trr_id:checked").each(function(){

		  var trr_rt = jQuery(this).parents("tr:first").find(".trr_row_type").val();
		  if(trr_rt=="keys_id"){
			var trr_id = jQuery(this).val();
			trr_id_arr.push(trr_id);
		  }

		});


		if( confirm("Are you sure you want to remove all selected keys?")==true ){
			jQuery.ajax({
				type: "POST",
				url: "ajax_tech_run_remove_keys.php",
				data: {
					tr_id: '<?php echo $tr_id; ?>',
					trr_id_arr: trr_id_arr
				}
			}).done(function( ret ){
				window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
				//location.reload();
				//window.location='/main.php';
			});
		}


	});



	// rebook script
	// rebook
	jQuery("#btn_create_rebook").click(function(){

		if(confirm("Are you sure you want to continue?")==true){

			var job_id = new Array();
			jQuery(".del_map_route:checked").each(function(){
				var jval = jQuery(this).val();
				var temp = jval.split(":");
				if(temp[0]=="job_id"){
					job_id.push(temp[1]);
				}
			});

			jQuery.ajax({
				type: "POST",
				url: "ajax_rebook_script.php",
				data: {
					job_id: job_id,
					is_240v: 0
				}
			}).done(function( ret ){
				window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
			});

		}

	});

	// icon legend show/hide toggle
	jQuery(".jicon").click(function(){

		jQuery("#jlegend").toggle();

	});


	// mark run complete
	jQuery(".run_status").click(function(){

		var run_status_dom = jQuery(this);

		var run_type = run_status_dom.attr("data-tr_mark-type");
		var status = (run_status_dom.attr("data-tr_mark-val")==0)?1:0;
		var tech_name = jQuery("#tech_name").val();
		var booking_staff = jQuery("#booking_staff").val();
		var run_type_name = run_status_dom.attr("data-tr_mark-name");



		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_tech_run_update_run_status.php",
			data: {
				run_type: run_type,
				status: status,
				tech_run_id: '<?php echo $tr_id; ?>',
				tech_name: tech_name,
				booking_staff: booking_staff,
				run_type_name: run_type_name
			}
		}).done(function( ret ) {

			jQuery("#load-screen").hide();

			// set status value via js, useful when toggled many time before refresh
			run_status_dom.attr("data-tr_mark-val",status);

			// green higlight js toggle
			if( status == 1 ){

				run_status_dom.addClass('greenBgButton');
				run_status_dom.find(".details_marker_tick").show();

			}else{

				run_status_dom.removeClass('greenBgButton');
				run_status_dom.find(".details_marker_tick").hide();

			}

			location.reload();

		});

	});



	// manual refresh
	jQuery("#btn_refresh").click(function(){
		window.location="/set_tech_run.php?tr_id=<?php echo $tr_id; ?>";
	});


	/*
	jQuery("#btn_update_map").click(function(){

		var start = jQuery("#start_point").val();
		var end = jQuery("#end_point").val();

		jQuery.ajax({
			type: "POST",
			url: "ajax_update_tech_run.php",
			data: {
				tech_id: <?php echo $tech_id; ?>,
				date: '<?php echo "{$date}"; ?>',
				start: start,
				end: end
			}
		}).done(function( ret ){
			window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
		});

	});
	*/

	// show button if start or end point is change
	jQuery("#start_point, #end_point").change(function(){

		jQuery("#btn_update_map").show();

	});


	// check all toggle
	jQuery("#check_all").click(function(){

	  if(jQuery(this).prop("checked")==true){

		// IMPORTANT - :visible
		// ONLY tick the visible ones since there are hidden rows on this list
		jQuery(".en_email:visible").prop("checked",true);
		jQuery(".en_sms:visible").prop("checked",true);
		jQuery(".chk_trr_id:visible").prop("checked",true);

		if( CountNumberOfSelectedKeys()>0 ){
			jQuery("#btn_remove_keys_div").show();
		}else{
			jQuery("#btn_remove_keys_div").hide();
		}

		if( CountNumberOfSelectedJobs()>0 ){
			jQuery("#btn_assign_color_div").show();
			jQuery("#btn_remove_color_div").show();
			jQuery("#btn_assign_dk").show();
			jQuery("#btn_hide_row").show();
		}else{
			jQuery("#btn_assign_color_div").hide();
			jQuery("#btn_remove_color_div").hide();
			jQuery("#btn_assign_dk").hide();
			jQuery("#btn_hide_row").hide();
		}

		jQuery("#tbl_maps tr.tech_run_row").addClass("rowHighlight");
		jQuery("#hidden_button_div").show();

	  }else{

		jQuery(".en_email").prop("checked",false);
		jQuery(".en_sms").prop("checked",false);

		jQuery("#tbl_maps tr.tech_run_row").removeClass("rowHighlight");
		jQuery(".chk_trr_id").prop("checked",false);
		jQuery("#hidden_button_div").hide();

	  }

	  // show/hide EN issue
	  showENissueButton();

	});


	/*
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
	*/




	jQuery(".chk_trr_id").click(function(){

		  var chked = jQuery(".chk_trr_id:checked").length;
		  var status = jQuery(this).prop("checked");
		  var orig_row_highlight = jQuery(this).parents("tr:first").find(".orig_row_highlight").val();

		  if(status==true){
			  jQuery(this).parents("tr:first").addClass("rowHighlight");
			  jQuery(this).parents("tr:first").prev().css("border-bottom","none");
		  }else{
			  jQuery(this).parents("tr:first").removeClass("rowHighlight");
			  jQuery(this).parents("tr:first").css('background-color',orig_row_highlight);
		  }


		  if(chked>0){

			if( CountNumberOfSelectedKeys()>0 ){
				jQuery("#btn_remove_keys_div").show();
			}else{
				jQuery("#btn_remove_keys_div").hide();
			}

			if( CountNumberOfSelectedJobs()>0 ){
				jQuery("#btn_assign_color_div").show();
				jQuery("#btn_remove_color_div").show();
				jQuery("#btn_assign_dk").show();
				jQuery("#btn_hide_row").show();
			}else{
				jQuery("#btn_assign_color_div").hide();
				jQuery("#btn_remove_color_div").hide();
				jQuery("#btn_assign_dk").hide();
				jQuery("#btn_hide_row").hide();
			}

			var prop_vacant = jQuery(this).parents("tr:first").find(".no_tenant_icon")[1];

			prop_vacant = prop_vacant ? prop_vacant.getAttribute('data-prop_vacant') : 0;

			if (prop_vacant == 1){
				$("#door-knock-opt").css("display", "none");
			} else {
				$("#door-knock-opt").css("display", "");
			}

			jQuery("#hidden_button_div").show();
		  }else{

			$("#door-knock-opt").css("display", "");

			jQuery("#hidden_button_div").hide();
		  }



		  // show/hide EN issue
		  showENissueButton();


		});



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
				//window.location='/set_tech_run.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>';
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
				url: "ajax_sort_tech_run.php?tr_id=<?php echo $tr_id; ?>&"+job_id
			}).done(function( ret ) {
				//window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
				//jQuery("#btn_update_map").show();
			});

		}
    });


	// keys script
	jQuery("#btn_keys").click(function(){

		jQuery("#keys_div").toggle();

	});

	// supplier script
	jQuery("#btn_supplier").click(function(){

		jQuery("#supplier_div").toggle();

	});

	/*
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
	*/

	jQuery("#btn_keys_submit").click(function(){

		var keys_agency = jQuery("#keys_agency").val();
		var agency_addresses_id_dp = jQuery("#keys_agency option:selected").attr("data-agency_addresses_id");
		var agency_addresses_id = ( agency_addresses_id_dp > 0 )?agency_addresses_id_dp:0;
		var error = "";

		if( keys_agency=="" ){
			error += "Agency is required";
		}

		if(error!=""){
			alert(error);
		}else{

			jQuery.ajax({
				type: "POST",
				url: "ajax_tech_run_add_keys.php",
				data: {
					tech_run_id: '<?php echo $tr_id; ?>',
					keys_agency: keys_agency,
					tech_id: <?php echo $tech_id; ?>,
					date: '<?php echo "{$date}"; ?>',
					country_id: <?php echo $_SESSION['country_default']; ?>,
					agency_addresses_id: agency_addresses_id
				}
			}).done(function( ret ){
				//window.location='/set_tech_run.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>';
				//location.reload();
				window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>&keys_success=1';
			});

		}

	});



	jQuery("#btn_supplier_submit").click(function(){

		var supplier = jQuery("#supplier").val();
		var error = "";

		if( supplier=="" ){
			error += "Supplier is required";
		}

		if(error!=""){
			alert(error);
		}else{

			jQuery.ajax({
				type: "POST",
				url: "ajax_tech_run_add_suppliers.php",
				data: {
					tech_run_id: '<?php echo $tr_id; ?>',
					supplier: supplier,
					country_id: <?php echo $_SESSION['country_default']; ?>
				}
			}).done(function( ret ){
				//window.location='/set_tech_run.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>';
				//location.reload();
				window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>&suppliers_success=1';
			});

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
			url: "ajax_move_to_maps_new.php",
			data: {
				job_id: job_id,
				tech_id: tech_id,
				date: date
			}
		}).done(function( ret ){
			window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
			//location.reload();
		});


	});


	// selecting lease renewal will sort list by job due date ascending
	jQuery("#select_job_type").change(function(){

		var select_job_type = jQuery(this).val();
		if( select_job_type == 'Lease Renewal' ){


			var custom_sort = "j.due_date ASC";
			window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>&sel_job_type=Lease Renewal&custom_sort='+custom_sort;

		}

	});


});

<?php
}
?>



// clicking out the container script :)
jQuery(document).mouseup(function (e)
{

	// region
	var container = jQuery("#region_dp_div");

	if (!container.is(e.target) // if the target of the click isn't the container...
		&& container.has(e.target).length === 0) // ... nor a descendant of the container
	{
		container.hide();
	}



	// agency
	var container = jQuery("#agency_dp_div");

	if (!container.is(e.target) // if the target of the click isn't the container...
		&& container.has(e.target).length === 0) // ... nor a descendant of the container
	{
		container.hide();
	}


});

jQuery("#region_ms").click(function(){

  jQuery("#region_dp_div").show();

});


jQuery("#agency_ms").click(function(){

  //jQuery("#agency_dp_div").show();

	jQuery("#load-screen").show();
	jQuery.ajax({
		type: "POST",
		url: "ajax_get_tech_run_agency_filter.php",
		data: {
			tech_id: '<?php echo $tech_id; ?>',
			date: '<?php echo $date; ?>',
			sub_regions: '<?php echo $sub_regions; ?>',
			agency_filter: '<?php echo $agency_filter; ?>'
		}
	}).done(function( ret ){
		jQuery("#agency_dp_div").html(ret);
		jQuery("#load-screen").hide();
		jQuery("#agency_dp_div").show();
		//window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
	});

});


</script>

</body>
</html>