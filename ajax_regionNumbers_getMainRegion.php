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


// get regions
$reg_str = "
	SELECT *
	FROM  `regions` 
	WHERE `region_state` = '{$state}'
	AND `country_id` = {$_SESSION['country_default']}
	AND `status` = 1
	ORDER BY `region_name`

";

$reg_sql = mysql_query($reg_str);

if( mysql_num_rows($reg_sql)>0 ){ 

?>
	<ul class="<?php echo preg_replace('/\s+/','_',$state); ?>_regions">
	<?php
	while( $reg = mysql_fetch_array($reg_sql) ){ 
		$region_id = $reg['regions_id'];
		$region_postcodes = str_replace(',,',',',$jc->getSubRegionPostcodes($region_id));
		//$region_count = $jc->getMainRegionCount($_SESSION['country_default'],$region_postcodes,$job_type,$job_status);
		//if($region_count>0){ 
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
					<span>
						<input type="checkbox" class="check_all_sub_region" />
					</span>
				</div>
				<input type="hidden" value="<?php echo $region_postcodes; ?>" />
				<div style="clear:both;"></div>
				<ul class="reg_db_sub_reg"></ul>
				<input type="hidden" class="regions_id" value="<?php echo $region_id; ?>" />
			</li>
		
		<?php	
		//}
	
	}
	?>
	</ul>
	<?php
	
}


?>