<?php

include('inc/init_for_ajax.php');
include('inc/future_pendings_functions.php');

// Initiate job class
$jc = new Job_Class();

$return_type = mysql_real_escape_string($_POST['return_type']);
$sel_sub_regions = mysql_real_escape_string($_POST['sel_sub_regions']);
$sel_sub_reg = explode(",",$sel_sub_regions);

// data
$region = mysql_real_escape_string($_POST['region']);
// job type
$job_type = mysql_real_escape_string($_POST['job_type']);
// job status
$job_status = mysql_real_escape_string($_POST['job_status']);
// ageing day filter
$days_filter = mysql_real_escape_string($_POST['days_filter']);

if( $days_filter == '30-60' ){
	$date_span_from = date('Y-m-d', strtotime("-60 days"));
	$date_span_to = date('Y-m-d', strtotime("-30 days"));
	$custom_filter = " AND CAST(j.`created` AS DATE) BETWEEN '{$date_span_from}' AND '{$date_span_to}' ";
}else if( $days_filter == '60-90' ){
	$date_span_from = date('Y-m-d', strtotime("-90 days"));
	$date_span_to = date('Y-m-d', strtotime("-60 days"));
	$custom_filter = " AND CAST(j.`created` AS DATE) BETWEEN '{$date_span_from}' AND '{$date_span_to}' ";
}else if( $days_filter == '90+' ){
	$last_90_days = date('Y-m-d', strtotime("-90 days"));
	$custom_filter = " AND CAST(j.`created` AS DATE) < '{$last_90_days}' ";
}


// check if route already set
$reg_str = "
	SELECT *
	FROM `postcode_regions`
	WHERE `region` = '{$region}'
	AND `country_id` = {$_SESSION['country_default']}
	AND `deleted` = 0
	ORDER BY `postcode_region_name` ASC
";

$reg_sql = mysql_query($reg_str);

if( mysql_num_rows($reg_sql)>0 ){ 
	while( $reg = mysql_fetch_array($reg_sql) ){ 
		
		$region_postcodes = str_replace(',,',',',$reg['postcode_region_postcodes']);
		$ret = ($return_type=='region_id')?$reg['postcode_region_id']:$region_postcodes;
		$sub_region_count_sql = getAgeingJobs('','',$region_postcodes,$job_type,$state,null,null,null,null,$custom_filter);
		$sub_region_count = mysql_num_rows($sub_region_count_sql);
		if($sub_region_count>0){ 
		$jcount_txt = ($sub_region_count>0)?"({$sub_region_count})":""; 
		?>
		
			<li>
				<input type="checkbox" name="postcode_region_id[]" class="postcode_region_id" value="<?php echo $ret; ?>" <?php echo ( $return_type=='region_id' && in_array($reg['postcode_region_id'], $sel_sub_reg) )?'checked="checked"':''; ?> /> <?php echo $reg['postcode_region_name']; ?> <?php echo $jcount_txt; ?>
			</li>
		
		<?php
		}
		
	} 
}

?>