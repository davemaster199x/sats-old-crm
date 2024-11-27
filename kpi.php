<?

$title = "KPIs";

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');

// intiate class
$crm = new Sats_Crm_Class;

$from = date('Y-m-01');
$to = date('Y-m-t');


// get agency count
function getTotalAgencyCount($country_id){
	$sql = mysql_query("
		SELECT COUNT(`agency_id`) AS jcount
		FROM `agency`
		WHERE `status` =  'active'
		AND `country_id` ={$country_id}
	");
	$row = mysql_fetch_array($sql);
	return $row['jcount'];
}


function getAgencyLogsCount($staff_id,$contact_type,$country_id,$from,$to){
	
	$str = '';
	if( $from!='' && $to!='' ){
		$from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
		$to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
		$str = " AND ael.`eventdate` BETWEEN '{$from2}' AND '{$to2}' ";
	}
	
	
	$sql_str = "
		SELECT count(ael.`agency_event_log_id`) AS jcount
		FROM `agency_event_log` AS ael
		LEFT JOIN `agency` AS a ON ael.`agency_id` = a.`agency_id`
		WHERE ael.`contact_type` = '{$contact_type}'
		AND ael.`staff_id` = {$staff_id}
		AND a.`country_id` = {$country_id}
		{$str}
	";
	$sql = mysql_query($sql_str);
	$row = mysql_fetch_array($sql);

	return $row['jcount'];
	
	
}


// get country
$cntry_sql = getCountryViaCountryId($_SESSION['country_default']);
$cntry = mysql_fetch_array($cntry_sql);


$country_id = $cntry['country_id'];
//echo "Country: {$country_id}";
$country_name = $cntry['country'];
$country_iso = strtolower($cntry['iso']);


//echo print_r($countries);

?>
<style>
.jtable tr td, .jtable tr th{
	border: 1px solid #cccccc;	
}



.jtable{
	float:left;
	margin-right: 15px;
	margin-bottom: 42px;
	width: 90% !important;
}

.jtable tr th{
	padding: 5px;
}

.jtable tr td{
	height: 16px;
}

.hideTd{
	border: 1px solid #ffffff !important;
	border-bottom: 1px solid #cccccc !important;
	color: #b4151b !important;
	font-weight: bold;
}


.jtable_first{
	width: 150px !important;
	margin-right: 0!important;
}

.row_bg_color{
	background-color: #b4151b !important;
}
</style>
<div id="mainContent">


 <div class="sats-middle-cont">
   
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="/kpi.php"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
    
   






<div id="snapshot_div" class='jdiv' style='margin-top: 30px;'>

<?php
// SNAPSHOT
?>
<table id="jtable1" border=0 cellspacing=0 cellpadding=5 class='table-center tbl-fr-red jtable' style="width:auto;">
	<tr>					
		<th colspan="2" class="row_bg_color">Snapshot</th>			
	</tr>
	<tr>
		<td>Total Properties</td>
		<td>
			<?php 
				echo number_format(mysql_num_rows($crm->kpi_getTotalPropertyCount($country_id))); 
			?>
		</td>
	</tr>
	<tr>
		<td>Total Agencies</td>
		<td>
			<?php 
				echo number_format(getTotalAgencyCount($country_id)); 
			?>
		</td>
	</tr>
	<tr>
		<td>Outstanding Jobs</td>
		<td>
			<?php
			$params = array(
				'exclude_status_for_kpi_report' => 1,
				'country_id' => $country_id,
				'return_count' => 1
			);
			echo number_format($crm->getJobs($params));	
			?>
		</td>
	</tr>
	<tr>
		<td>Outstanding Value</td>
		<td>
			<?php
			$params = array(
				'exclude_status_for_kpi_report' => 1,
				'country_id' => $country_id,
				'sum_job_price' => 1
			);
			$jp_sql = $crm->getJobs($params);
			$jp = mysql_fetch_array($jp_sql);
			$job_price = $jp['j_price'];
			echo '$'.number_format($job_price, 2);	
			?>
		</td>	
	</tr>
	<tr>
		<td>Average Age (Not Completed)</td>
		<td>
			<?php
			// get sum age and job count
			$custom_select = "
				SUM( DATEDIFF( '".date('Y-m-d')."', CAST( j.`created` AS DATE ) ) ) AS sum_age, 
				COUNT(j.`id`) AS jcount 
			";
			// Not Completed status
			$custom_filter = "
				AND ( 
					j.`status` != 'On Hold' AND
					j.`status` != 'Pending' AND
					j.`status` != 'Completed' AND
					j.`status` != 'Cancelled'
				)
			";
			
			// sum age
			$params = array(
				'custom_filter' => $custom_filter,
				'custom_select' => $custom_select,
				'country_id' => $country_id
			);
			$sa_sql = $crm->getJobsData($params);	
			$sa = mysql_fetch_array($sa_sql);
			$sum_age = $sa['sum_age'];
			$jcount = $sa['jcount'];					
			echo number_format(($sum_age/$jcount), 2, '.', '').' days';
			?>
		</td>
	</tr>
	<tr>
		<td>Average Age (Completed this Month)</td>
		<td>					
			<?php
			// get sum age and job count
			$custom_select = "
				SUM( DATEDIFF( j.`date`, CAST( j.`created` AS DATE ) ) ) AS sum_completed_age, 
				COUNT(j.`id`) AS jcount 
			";
			// completed status
			$custom_filter = "
				AND ( j.`status` = 'Completed' OR j.`status` = 'Merged Certificates' )
			";
			
			// sum age
			$params = array(
				'custom_filter' =>$custom_filter,
				'custom_select' => $custom_select,
				'date_range' => array(
					'from' => $from,
					'to' => $to
				),
				'country_id' => $country_id
			);
			$sa_sql = $crm->getJobsData($params);	
			$sa = mysql_fetch_array($sa_sql);
			$sum_completed_age = $sa['sum_completed_age'];
			$jcount = $sa['jcount'];					
			echo number_format(($sum_completed_age/$jcount), 2, '.', '').' days';
			?>
		</td>
	</tr>
</table>



</div>


<div style="clear:both;"></div>

<div>		
	<button type="button" class="submitbtnImg" id="display_bookings_btn">Display Bookings</button>
</div>
<div id="bookings_div"></div>


<div style="clear:both;"></div>

<div>		
	<button type="button" class="submitbtnImg" id="display_sales_result_btn">Display Sales Result</button>
</div>
<div id="sales_report_div"></div>

<div style="clear:both;"></div>
	
	

</div>

</div>

<br class="clearfloat" />
<style>
#display_bookings_btn,
#display_sales_result_btn{
	margin-bottom: 15px;
}
</style>
<script>
jQuery(document).ready(function(){

	// display bookings
	jQuery("#display_bookings_btn").click(function(){
		
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_get_kpi_bookings.php"
		}).done(function( ret ){

			jQuery("#load-screen").hide();
			jQuery("#bookings_div").html(ret);

		});	
		
	});

	// display sales report
	jQuery("#display_sales_result_btn").click(function(){
		
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_get_kpi_sales_result.php"
		}).done(function( ret ){

			jQuery("#load-screen").hide();
			jQuery("#sales_report_div").html(ret);

		});	
		
	});
	

});
</script>

</body>
</html> 
