<?php

include('inc/init_for_ajax.php');

// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class();

$return_type = mysql_real_escape_string($_POST['return_type']);
$sel_sub_regions = mysql_real_escape_string($_POST['sel_sub_regions']);
$sel_sub_reg = explode(",",$sel_sub_regions);

// data
$region = mysql_real_escape_string($_POST['region']);
// job type
$job_type = mysql_real_escape_string($_POST['job_type']);
// job status
$job_status = mysql_real_escape_string($_POST['job_status']);
// custom query
$custom_query_flag = mysql_real_escape_string($_POST['custom_query_flag']);

if($custom_query_flag==1){
	$custom_query = " AND j.property_vacant = 1 AND j.status NOT IN('Completed','Cancelled','Merged Certificates','Booked','Pre Completion') ";
}

/*
// check if route already set
$reg_str = "
	SELECT *
	FROM `postcode_regions`
	WHERE `region` = '{$region}'
	AND `country_id` = {$_SESSION['country_default']}
	AND `deleted` = 0
	ORDER BY `postcode_region_name` ASC
";
*/

$reg_str = "
	SELECT *
	FROM `sub_regions`
	WHERE `region_id` = '{$region}'	
	AND `active` = 1
	ORDER BY `subregion_name` ASC
";

$reg_sql = mysql_query($reg_str);

if( mysql_num_rows($reg_sql)>0 ){ 
	while( $reg = mysql_fetch_array($reg_sql) ){ 
		
		//$region_postcodes = str_replace(',,',',',$reg['postcode_region_postcodes']);

		$sel_query = "pc.`postcode`";                
		$postcode_params = array(
			'sel_query' => $sel_query,											
			'sub_region_id' => $reg['sub_region_id'],     	
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

		$region_postcodes = $postcodes_imp;

		$ret = ($return_type=='region_id')?$reg['sub_region_id']:$region_postcodes;
		$jcount = $jc->getTobeBookedSubRegionCount($_SESSION['country_default'],$region_postcodes,$job_type,$job_status,$custom_query);
		if($jcount>0){
		?>
		<li>
			<input type="checkbox" name="postcode_region_id[]" class="postcode_region_id" value="<?php echo $ret; ?>" <?php echo ( $return_type=='region_id' && in_array($reg['sub_region_id'], $sel_sub_reg) )?'checked="checked"':''; ?> /> <?php echo $reg['subregion_name']; ?> (<?php echo $jcount; ?>)
		</li>
		<?php
		}
	} 
}

?>