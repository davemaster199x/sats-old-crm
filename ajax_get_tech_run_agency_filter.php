<?php

include('inc/init_for_ajax.php');

//$crm = new Sats_Crm_Class();

$tech_id = mysql_real_escape_string($_POST['tech_id']);
$date = mysql_real_escape_string($_POST['date']);
$sub_regions = mysql_real_escape_string($_POST['sub_regions']);
$agency_filter = mysql_real_escape_string($_POST['agency_filter']);

//echo "agency filter: {$agency_filter}<br />";

if( $agency_filter!='' ){
	$agency_filter_arr = explode(",",$agency_filter);
}else{
	$agency_filter_arr = [];
}

//echo "agency filter array: ".print_r($agency_filter_arr)."<br />";

?>
<div class="region_dp_header">
	<ul>
		<?php				
		$afilt_sql = getJobsByRegionSort($tech_id,$date,$sub_regions,$_SESSION['country_default'],'','a.agency_id','a.agency_name ASC');
		while($afilt =  mysql_fetch_array($afilt_sql)){ ?>
			<li>
				<input type="checkbox" name="agency[]" class="agency" value="<?php echo $afilt['agency_id']; ?>" <?php echo in_array($afilt['agency_id'],$agency_filter_arr)?'checked="checked"':''; ?>/> <span><?php echo $afilt['agency_name']; ?></span>
			</li>
		<?php	
		}					
		?>						
	</ul>
</div>