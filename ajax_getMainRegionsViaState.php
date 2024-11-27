<?php

include('inc/init_for_ajax.php');

// Initiate job class
$jc = new Job_Class();

// data
$state = mysql_real_escape_string($_POST['state']);
// job type
$job_type = mysql_real_escape_string($_POST['job_type']);
// job status
$job_status = mysql_real_escape_string($_POST['job_status']);
// custom query
$custom_query_flag = mysql_real_escape_string($_POST['custom_query_flag']);

if($custom_query_flag==1){
	$custom_query = " AND j.property_vacant = 1 AND j.status NOT IN('Completed','Cancelled','Merged Certificates','Booked','Pre Completion') ";
}

// get distinct region by state
$sel_query = "DISTINCT(r.`regions_id`), r.`region_name`";                
$postcode_params = array(
	'sel_query' => $sel_query,
	'state' => $state,    	
	'active' => 1,
	'sort_list' => array(
		'order_by' => 'r.`region_name`',
		'sort' => 'ASC'
	),
	'display_query' => 0
);
$reg_sql = Sats_Crm_Class::get_sub_region($postcode_params);	

if( mysql_num_rows($reg_sql)>0 ){ 

?>
	<ul class="<?php echo $state; ?>_regions">
	<?php
	while( $reg = mysql_fetch_array($reg_sql) ){ 
	$region_id = $reg['regions_id'];
	$region_postcodes = str_replace(',,',',',$jc->getTobeBookedPostcodeViaRegion($region_id));
	$region_count = $jc->getTobeBookedSubRegionCount($_SESSION['country_default'],$region_postcodes,$job_type,$job_status,$custom_query);
	if($region_count>0){
		$jcount_txt = ($region_count>0)?"({$region_count})":"";
	?>
		<li class="main_region_li">
			<div class="region_wrapper">
				<span>
					<input type="checkbox" class="region_check_all" style="display:none;" value="" />
				</span>
				<span class="reg_db_main_reg">
					<?php echo $reg['region_name']; ?> <?php echo $jcount_txt; ?>
					<input type="hidden" class="sel_region_id" value="<?php echo $reg['regions_id']; ?>" />
				</span>
				<span style="position: relative; top: 2px;">
					<input type="checkbox" class="check_all_sub_region" />
				</span>
			</div>
			<input type="hidden" value="<?php echo $region_postcodes; ?>" />
			<div style="clear:both;"></div>
			<ul class="reg_db_sub_reg"></ul>
			<input type="hidden" class="regions_id" value="<?php echo $region_id; ?>" />
		</li>
	<?php	
		}
	}
	?>
	</ul>
	<?php
	
}


?>