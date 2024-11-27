<?

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

$title = "Sales Report";

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');


$sales_result_overall_tot = 0;
	
$cntry_sql = getCountryViaCountryId($_SESSION['country_default']);
$cntry = mysql_fetch_array($cntry_sql);

$country_id = $cntry['country_id'];
//echo "Country: {$country_id}";
$country_name = $cntry['country'];
$country_iso = strtolower($cntry['iso']);



// date
$all = $_REQUEST['all'];
$from = ($_REQUEST['from'])?$_REQUEST['from']:date("01/m/Y");
$to = ($_REQUEST['to'])?$_REQUEST['to']:date("t/m/Y");





$prev_day = array(
	'from' => date('d/m/Y', strtotime('-1 day', strtotime(str_replace("/","-",$from)))),
	'to' => date('d/m/Y', strtotime('-1 day', strtotime(str_replace("/","-",$from)))),
	'title' => '<span class="arw-lft2">&nbsp;</span> Previous Day'
);

$next_day = array(
	'from' => date('d/m/Y', strtotime('+1 day', strtotime(str_replace("/","-",$from)))),
	'to' => date('d/m/Y', strtotime('+1 day', strtotime(str_replace("/","-",$from)))),
	'title' => 'Next Day <span class="arw-rgt2">&nbsp;</span>',
	'css' => 'float: right;'
);


# Create predefined date ranges
$today = date('d/m/Y');

$date_ranges = array();

$date_ranges[] = array(
'title' => 'All',
'from' => 'all',
'to' => 'all'
);

$date_ranges[] = array(
'title' => 'Today',
'from' => date('d/m/Y'),
'to' => date('d/m/Y')
);

$date_ranges[] = array(
'title' => 'Yesterday',
'from' => date('d/m/Y', (strtotime('-1 days'))),
'to' => date('d/m/Y', (strtotime('-1 days')))
);

$date_ranges[] = array(
'title' => 'Last Week',
'from' => date('d/m/Y', (strtotime('-7 days'))),
'to' => $today
);

$date_ranges[] = array(
'title' => 'Next Week',
'from' => $today,
'to' => date('d/m/Y', (strtotime('+7 days')))
);



$date_ranges[] = array(
'title' => date("F",mktime(0,0,0, (date("n") - 1 + 12) % 12, 1)),
'from' => date("01/m/Y",mktime(0,0,0, (date("n") - 1 + 12) % 12, 1)),
'to' => date("t/m/Y",mktime(0,0,0, (date("n") - 1 + 12) % 12, 1))
);


$date_ranges[] = array(
'title' => date("F",mktime(0,0,0, (date("n") - 2 + 12) % 12, 1)),
'from' => date("01/m/Y",mktime(0,0,0, (date("n") - 2 + 12) % 12, 1)),
'to' => date("t/m/Y",mktime(0,0,0, (date("n") - 2 + 12) % 12, 1))
);

$date_ranges[] = array(
'title' => date("F",mktime(0,0,0, (date("n") - 3 + 12) % 12, 1)),
'from' => date("01/m/Y",mktime(0,0,0, (date("n") - 3 + 12) % 12, 1)),
'to' => date("t/m/Y",mktime(0,0,0, (date("n") - 3 + 12) % 12, 1))
);


# Display dates in dd/mm/yyyy
$to_display = convertDateAus($to);
$from_display = convertDateAus($from);


$staff_id = (isset($_GET['sid']) ? (int)$_GET['sid']: "z");
$tech_id = (isset($_GET['tid']) ? (int)$_GET['tid']: "z");

# Get Staff details for display if needed
if($staff_id === 0)
{
	$staff_details['FirstName'] = "SATS System";
}
elseif(is_int($staff_id))
{
	$staff_details = $user->getUserDetails($staff_id);
}

# Get Tech details for display if needed
if($tech_id === 0)
{
	$tech_details['FirstName'] = "Unassigned";
}
elseif(is_int($tech_id))
{
	$tech_details = $user->getTechDetails($tech_id);
}


# Staff and tech id's to filter
$staff_filter = array(
'staff_id' => $staff_id,
'tech_id' => $tech_id
);

$report_params = array('from' => $from, 'to' => $to, 'staff_id' => $staff_id, 'tech_id' => $tech_id);

$report = new Report();

/*
$data = $report -> getSalesReportData($report_params);

$all_status_types = $report->getAllStatuses();
$all_job_types = $report->getAllJobTypes();
*/

function getDynamicServices(){
	return mysql_query("
		SELECT *
		FROM `alarm_job_type`
		WHERE `active` =1
	");
}

function get_num_services($salesrep,$ajt,$from,$to,$country_id){
					
	$str = "";					
	if( $from!='all' && $to!='all' ){
		$from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
		$to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
		$str = "AND CAST(ps.`status_changed` AS DATE) BETWEEN '{$from2}' AND '{$to2}'";
	}
	
	$sql = "
		SELECT *
		FROM `property_services` AS ps
		LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE a.`salesrep` ={$salesrep}
		AND ps.`alarm_job_type_id` ={$ajt}
		AND ps.`service` = 1
		AND a.`country_id` = {$country_id}
		AND a.`agency_id` != 3712
		{$str}
	";
	//echo $sql;
	return mysql_query($sql);
	
}

function get_deleted($salesrep,$del_stat,$from,$to){

	$str = "";
	if( $from!='all' && $to!='all' ){
		$from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
		$to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
		$str = "AND CAST(p.`deleted_date` AS DATE) BETWEEN '{$from2}' AND '{$to2}'";
	}
	
	$sql = "
		SELECT *
		FROM `property_services` AS ps
		LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE a.`salesrep` ={$salesrep}
		AND p.`agency_deleted` = {$del_stat}
		AND p.`deleted` = 1
		AND ps.`service` = 1
		AND a.`country_id` = {$_SESSION['country_default']}
		AND a.`agency_id` != 3712
		{$str}
	";
	//echo $sql;
	return mysql_query($sql);
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
		AND a.`agency_id` != 3712
		{$str}
	";
	$sql = mysql_query($sql_str);
	$row = mysql_fetch_array($sql);

	return $row['jcount'];
	
	
}




?>

<div id="mainContent">

<div class="sats-middle-cont">
	
	<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
   <table cellpadding=0 cellspacing=0 >
    <tr class="tbl-view-prop">
        <td>
          <form action="" id="date_range" method="get" style="margin: 0;">
            <div class="aviw_drop-h">
            
			  <div class="fl-left">
				<label>Report from:</label>
                <input type="text" name="from" class="datepicker addinput searchstyle" style="width:85px;" value="<?php echo $from; ?>">
			  </div>
			
  <div class="fl-left">
   <label>to:</label>
   <input type="text" name="to" class="datepicker addinput searchstyle" style="width:85px;" value="<?php echo $to; ?>">
  </div>
  
  <div class="fl-left pull-left">
  <?php if(is_int($staff_id)): ?>
	<input type="hidden" name="sid" value="<?php echo $staff_id; ?>" class="submitbtnImg">	
	<?php endif; ?>	
	<?php if(is_int($tech_id)): ?>
	<input type="hidden" name="tid" value="<?php echo $tech_id; ?>" class="submitbtnImg">	
	<?php endif; ?>
	<input type="hidden" name="get_sats" value="1" />
	<input type="submit" value="Get Stats" class="submitbtnImg">
  </div>
  
</div>

<div class="aviw_drop-h qlnk">
<span class="float-left content-black"><?php echo $report->generateLink($prev_day, $staff_filter); ?></span>
Quick Links&nbsp;|&nbsp;<?php foreach($date_ranges as $index=>$range): ?>
		<?php echo $report->generateLink($range, $staff_filter); ?>
		<? if($index < sizeof($date_ranges) - 1): ?>
		&nbsp;|&nbsp;
		<? endif; ?>		
		<?php endforeach; ?>	
        <span class="float-right pg-tp-rg content-black"><?php echo $report->generateLink($next_day, $staff_filter); ?></span>		
</div>
            
 </form>           
            
     </td>
  </tr>
</table>

<?php
// REPORT starts here
if( $_GET['get_sats']==1 ){ 


// services
$ajt_sql2 = getDynamicServices();
$ajt_arr = [];
while($ajt2 = mysql_fetch_array($ajt_sql2)){
	
	
	
	switch($ajt2['id']){
		case 8:
			$ajt_name = 'SA SS';
		break;
		case 9:
			$ajt_name = 'SA SS CW';
		break;
		case 11:
			$ajt_name = 'SA WM';
		break;
		case 13:
			$ajt_name = 'SA SS (IC)';
		break;
		case 14:
			$ajt_name = 'SA CW SS (IC)';
		break;
		default:
			$ajt_name = $ajt2['short_name'];
	}
	
	$ajt_arr[] = array(
		'id' => $ajt2['id'],
		'type' => $ajt2['type'],
		'short_name' => $ajt2['short_name'],
		'short_name_wspace' => $ajt_name
	);
}

$row_count = mysql_num_rows($ajt_sql2);


?>


	


	 <div class="report">
    
		<h2 class="heading">
			Report <?php echo $from; ?> to <?php echo $to; ?>
		</h2>
			<?php
		# Alert that viewing Staff or Tech indivudal reports if neded, and offer to reset
		if(is_int($staff_id))
		{
			echo "<div class='success'>Currently viewing statistics for staff member: " . $staff_details['FirstName'] . " " . $staff_details['LastName'] . " " . $report->generateLink(array('from' => $from, 'to' => $to, 'title' => 'back to all')) . "</div>";
		}
		if(is_int($tech_id))
		{
			echo "<div class='success'>Currently viewing statistics for technician: " . $tech_details['FirstName'] . " " . $tech_details['last_name'] . " " . $report->generateLink(array('from' => $from, 'to' => $to, 'title' => 'back to all')) . "</div>";
		}
		
		?>

		
		<p>This report shows totals that are SATS to service. These numbers do not include totals for properties that are marked DIY, No Response or Other Provider</p>

	</div>

	
	
	
<?php



// distint sales rep
$sr_sql = mysql_query("
	SELECT DISTINCT a.`salesrep` , sa.`FirstName` , sa.`LastName`, a.`salesrep`
	FROM `property_services` AS ps
	LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	LEFT JOIN `staff_accounts` AS sa ON sa.`StaffID` = a.`salesrep`
	WHERE a.`salesrep` !=0
	AND p.`deleted` = 0 
	AND a.`status` = 'active'
	AND a.`salesrep` IS NOT NULL
	AND p.`property_id` != 0
	AND p.`property_id` IS NOT NULL
	AND a.`country_id` = {$country_id}
	ORDER BY sa.`FirstName` ASC
");

$i = 1;
$max_row = mysql_num_rows($sr_sql);	
while($sr = mysql_fetch_array($sr_sql)){ 
	// date
	$sales_result_tot = 0;
	
	$sales_arr[] = array(
		'saleperson_id' => $sr['salesrep'],
		'salesperson_name' => "{$sr['FirstName']} {$sr['LastName']}"
	);
	
}



?>	
	
	
	
<div class='jdiv'>





<?php
// services
$ajt_sql2 = getDynamicServices();
$ajt_arr = [];
while($ajt2 = mysql_fetch_array($ajt_sql2)){
	
	
	
	switch($ajt2['id']){
		case 8:
			$ajt_name = 'SA SS';
		break;
		case 9:
			$ajt_name = 'SA SS CW';
		break;
		case 11:
			$ajt_name = 'SA WM';
		break;
		case 13:
			$ajt_name = 'SA SS (IC)';
		break;
		case 14:
			$ajt_name = 'SA CW SS (IC)';
		break;
		default:
			$ajt_name = $ajt2['short_name'];
	}
	
	$ajt_arr[] = array(
		'id' => $ajt2['id'],
		'type' => $ajt2['type'],
		'short_name' => $ajt2['short_name'],
		'short_name_wspace' => $ajt_name
	);
}

$row_count = mysql_num_rows($ajt_sql2);

?>


<?php
// SALES RESULT	
?>
<table id="jtable1" border=0 cellspacing=0 cellpadding=5 class='table-center tbl-fr-red jtable' style="width:auto;">
	<tr>					
		<th colspan="<?php echo (($row_count)+2); ?>" class="row_bg_color">Sales Results</th>			
	</tr>
	<tr style="background-color: #eeeeee;">
		<td><strong>Staff</strong></td>
		<?php
		foreach( $ajt_arr as $ajt ){ ?>
			<td><strong><?php echo $ajt['short_name_wspace']; ?></strong></td>
		<?php	
		}
		?>
		<td><strong>Total</strong></td>	
	</tr>
	<?php
	foreach( $sales_arr as $sales ){
	$sales_result_tot = 0;
	?>
		<tr>
			<td>
				<?php  echo $sales['salesperson_name']; ?>
			</td>
			<?php
			foreach( $ajt_arr as $ajt ){ ?>
				<td>
					<?php 
					$sa = mysql_num_rows(get_num_services($sales['saleperson_id'],$ajt['id'],$from,$to,$country_id));
					echo ($sa>0)?$sa:'';
					$sales_result_tot += $sa;
					?>
				</td>
			<?php	
			}
			?>
			<td>
				<?php
				echo ( $sales_result_tot>0 )?$sales_result_tot:'';
				$sales_result_overall_tot += $sales_result_tot;
				?>
			</td>
		</tr>
	<?php	
	}
	?>	
	<tr style="background-color: #eeeeee;">
		<td><strong>Total</strong></td>
		<?php
		foreach( $ajt_arr as $ajt ){ ?>
			<td>&nbsp;</td>
		<?php	
		}
		?>
		<td><strong><?php echo $sales_result_overall_tot; ?></strong></td>	
	</tr>
</table>



</div>

<div style="clear:both;"></div>	
	

<div class='jdiv'>





<?php
// agency logs
$agency_logs_sql = mysql_query("
	SELECT DISTINCT `contact_type`
	FROM `agency_event_log`
	WHERE `contact_type` NOT IN(
		'Agency Changed to Target',
		'Conference',
		'Phone Call - Accounts',
		'Email - Accounts',
		'Other - Accounts',
		'Agency Update'
	)
");
$al_arr = [];
while( $al = mysql_fetch_array($agency_logs_sql) ){
	$al_arr[] = $al['contact_type'];
}

$row_count = mysql_num_rows($agency_logs_sql);

?>


<?php
// SALES Activity
?>
<table id="jtable1" border=0 cellspacing=0 cellpadding=5 class='table-center tbl-fr-red jtable sales_activity_tbl_<?php echo $country_iso; ?>' style="width:auto;">
	<tr>					
		<th colspan="<?php echo (($row_count)+2); ?>" class="row_bg_color">Sales Activity</th>			
	</tr>
	<tr style="background-color: #eeeeee;">
		<td><strong>Staff</strong></td>
		<?php
		foreach( $al_arr as $al ){ ?>
			<td><strong><?php echo $al; ?></strong></td>
		<?php	
		}
		?>
	</tr>
	<?php
	foreach( $sales_arr as $sales ){
	?>
		<tr>
			<td>
				<?php  echo $sales['salesperson_name']; ?>
			</td>
			<?php
			foreach( $al_arr as $al ){ ?>				
				<td>
					<?php 
					$sa = getAgencyLogsCount($sales['saleperson_id'],$al,$country_id,$from,$to);
					echo ($sa>0)?$sa:'';
					?>
				</td>
			<?php	
			}
			?>
		</tr>
	<?php	
	}
	?>	
</table>



</div>	
	
	



<?php	
}else{ ?>

	<h2 style="text-align:left;">Press 'Get Stats' to Display Results</h2>

<?php	
}
?>	
    
    
 
	
</div>

</div>

<br class="clearfloat" />

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
<script>
jQuery(document).ready(function(){
	
	// hide users that has no data
	jQuery(".td_gross_tot").each(function(){
		var gross_tot = parseInt(jQuery(this).html());
		if(gross_tot==0){
			jQuery(this).parents("tr:first").hide();
		}		
	});
	
});
</script>
</body>
</html> 
