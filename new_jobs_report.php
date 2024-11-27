<?

// Start buffering
ob_start();

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

$title = "New Jobs Report";

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');


include('inc/activity_functions.php');

function get_num_services($agency_id,$job_type,$from,$to,$distinct,$return_count=1,$state){
	
	$sel_str = "SELECT COUNT(j.`id`) AS jcount";
	$filter_str = '';
	
	if($distinct!=''){
		switch($distinct){
			case 'a.agency_id':
				$sel_str = "SELECT DISTINCT a.`agency_id`, a.`agency_name`, a.`state`, a.`salesrep` ";
			break;
		}		
	}
	
	if($agency_id!=''){
		$filter_str .= " AND a.`agency_id` ={$agency_id} ";
	}
	
	if($state!=''){
		$filter_str .= " AND a.`state` = '{$state}' ";
	}
	
	if($job_type!=''){
		$filter_str .= " AND j.`job_type` = '{$job_type}' ";
	}
									
	if( $from!='all' && $to!='all' ){
		$from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
		$to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
		$filter_str .= " AND CAST(j.`created` AS DATE) BETWEEN '{$from2}' AND '{$to2}' ";
	}

	$sql_str = "
		{$sel_str}
		FROM jobs AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`del_job` =0
		AND a.`status` = 'active'		
		AND a.`country_id` = {$_SESSION['country_default']}
		{$filter_str}
		ORDER BY a.`agency_name` ASC
	";
	
	if($return_count==1){
		$sql =  mysql_query($sql_str);
		$row = mysql_fetch_array($sql);
		return $row['jcount'];
	}else{
		return mysql_query($sql_str);
	}
	

}


function get_deleted_old($agency_id,$del_stat,$from,$to){
	
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
		WHERE a.`agency_id` ={$agency_id}
		AND p.`agency_deleted` = {$del_stat}
		AND p.`deleted` = 1
		AND ps.`service` = 1
		AND a.`country_id` = {$_SESSION['country_default']}
		{$str}
	";
	//echo $sql;
	return mysql_query($sql);
	
}


// v2
function get_deleted($agency_id,$del_stat,$from,$to){
	
	$str = "";
	if( $from!='all' && $to!='all' ){
		$from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
		$to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
		$str = "AND CAST(p.`deleted_date` AS DATE) BETWEEN '{$from2}' AND '{$to2}'";
	}

	$sql = "
		SELECT *
		FROM jobs AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE a.`agency_id` ={$agency_id}
		AND p.`agency_deleted` = {$del_stat}
		AND(
			j.`del_job` = 1 OR
			j.`status` = 'Cancelled'
		)
		AND a.`status` = 'active'		
		AND a.`country_id` = {$_SESSION['country_default']}
		{$str}
	";
	//echo $sql;
	return mysql_query($sql);
	
}


function getAddedBySats($agency_id,$from,$to){
	
	$str = "";					
	if( $from!='all' && $to!='all' ){
		$from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
		$to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
		$str = "AND CAST(j.`created` AS DATE) BETWEEN '{$from2}' AND '{$to2}'";
	}

	$sql_str = "
		SELECT COUNT(j.`id`) AS jcount
		FROM jobs AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE a.`agency_id` ={$agency_id}
		AND a.`status` = 'active'
		AND j.`del_job` =0
		AND p.`added_by` > 0
		AND a.`country_id` = {$_SESSION['country_default']}
		{$str}
	";
	
	$sql =  mysql_query($sql_str);
	$row = mysql_fetch_array($sql);
	//echo $sql;
	return $row['jcount'];
	
}


function getAddedByAgency($agency_id,$from,$to){
	
	$str = "";					
	if( $from!='all' && $to!='all' ){
		$from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
		$to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
		$str = "AND CAST(j.`created` AS DATE) BETWEEN '{$from2}' AND '{$to2}'";
	}

	$sql_str = "
		SELECT COUNT(j.`id`) AS jcount
		FROM jobs AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE a.`agency_id` ={$agency_id}
		AND a.`status` = 'active'
		AND j.`del_job` =0
		AND p.`added_by` <= 0
		AND a.`country_id` = {$_SESSION['country_default']}
		{$str}
	";
	
	$sql =  mysql_query($sql_str);
	$row = mysql_fetch_array($sql);
	//echo $sql;
	return $row['jcount'];
	
}


function getJobPriceTotal($agency_id,$from,$to){
	
	$str = "";					
	if( $from!='all' && $to!='all' ){
		$from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
		$to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
		$str = "AND CAST(j.`created` AS DATE) BETWEEN '{$from2}' AND '{$to2}'";
	}

	$sql_str = "
		SELECT SUM(j.`job_price`) AS jtot
		FROM jobs AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE a.`agency_id` ={$agency_id}
		AND a.`status` = 'active'
		AND j.`del_job` =0
		AND a.`country_id` = {$_SESSION['country_default']}
		{$str}
	";
	
	$sql =  mysql_query($sql_str);
	$row = mysql_fetch_array($sql);
	//echo $sql;
	return $row['jtot'];
	
}



function this_getAgencySalesRep($sr_id){
	return mysql_query("
		SELECT DISTINCT a.`salesrep` , sa.`FirstName` , sa.`LastName`
		FROM `agency` AS a
		LEFT JOIN `staff_accounts` AS sa ON sa.`StaffID` = a.`salesrep`
		WHERE a.`salesrep` = {$sr_id}
		AND a.`status` = 'active'
		AND a.`country_id` ={$_SESSION['country_default']}
		AND a.`salesrep` !=0
		 
	");
}


// date
$all = $_REQUEST['all'];
$from = ($_REQUEST['from'])?$_REQUEST['from']:date("01/m/Y");
$to = ($_REQUEST['to'])?$_REQUEST['to']:date("t/m/Y");
$state = $_REQUEST['state'];



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

/*
$date_ranges[] = array(
'title' => 'Month to date',
'from' => date('Y-m-') . "01",
'to' => $today
);
 */
 


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

$report = new Report();

function jgetAllJobTypes(){
	return mysql_query("
		SELECT * 
		FROM `job_type` 
	");
}




if( $_GET['get_sats']==1 ){
	
	// job types
	$export = [];
	$jt_sql = jgetAllJobTypes();
	$jt_arr = [];
	while($jt = mysql_fetch_array($jt_sql)){ 
		$jt_arr[] = $jt['job_type'];
	}





	// get distinct Agency
	$sr_sql = get_num_services('','',$from,$to,'a.agency_id',0,$state);
		
	if(mysql_num_rows($sr_sql)>0){

		
		while($sr = mysql_fetch_array($sr_sql)){
			
			$salesrep = '';
			
			// job types
			$jt_count = [];
			$jt_tot = 0;
			foreach( $jt_arr as $job_type ){
				$serv_ret = get_num_services($sr['agency_id'],$job_type,$from,$to,null,1,$state);
				$jt_count[] = ($serv_ret>0)?$serv_ret:'';
				$jt_tot += $serv_ret;
			}
			
			// total new
			$total_new = ($jt_tot>0)? $jt_tot:'';
			
			// total amount
			$tot_jp = getJobPriceTotal($sr['agency_id'],$from,$to); 
			$total_amount = ($tot_jp>0)?$tot_jp:0;
			
			// deleted
			$deleted = mysql_num_rows(get_deleted($sr['agency_id'],1,$from,$to));
			$deleted_tot = ($deleted>0)?$deleted:'';
			
			// net 
			$net = ($jt_tot-$deleted_tot); 
			
			// Added by Agency
			$add_by_agency = getAddedByAgency($sr['agency_id'],$from,$to); 
			$added_by_agency = ($add_by_agency>0)?$add_by_agency:'';
			
			// added by SATS
			$add_by_sats = getAddedBySats($sr['agency_id'],$from,$to); 
			$added_by_sats = ($add_by_sats>0)?$add_by_sats:'';
			
			// salesrep
			$salesrep_sql = this_getAgencySalesRep($sr['salesrep']);
			$salesrep = mysql_fetch_array($salesrep_sql);
			
			$export[] = array(
				'agency_id' => $sr['agency_id'],
				'agency' => $sr['agency_name'],
				'state' => $sr['state'],
				'job_type_count' => $jt_count,
				'total_new' => $total_new,
				'total_amount' => $total_amount,
				'deleted_tot' => $deleted_tot,
				'net' => $net,
				'added_by_agency' => $added_by_agency,
				'added_by_sats' => $added_by_sats,
				'salesrep' => "{$salesrep['FirstName']} {$salesrep['LastName']}"
			);
		}

	}
	
}




//echo "<pre>";
//print_r($export);
//echo "</pre>";

// Get value of buffering so far
$getContent = ob_get_contents();
// Stop buffering
ob_end_clean();


// process export
if($_GET['export']==1){
	
	
	$filename = "new_jobs_report_".rand()."_".date('YmdHis').".csv";
	
	header("Content-Type: text/csv");
	header("Content-Disposition: Attachment; filename={$filename}");
	header("Pragma: no-cache");
	
	// job type
	$jt_str = implode(",",$jt_arr);

	// headers
	$export_str = "Agency,State,".$jt_str.",Total New,Total $,Deleted,Net,Added By Agency,Added By SATS,Salesrep\n";

	foreach( $export as $exp_row ){
		$exp_jt_str = implode(",",$exp_row['job_type_count']);
		$total_amount_fin = "$".$exp_row['total_amount'];
		$export_str .= "\"{$exp_row['agency']}\",\"{$exp_row['state']}\",".$exp_jt_str.",\"{$exp_row['total_new']}\",\"{$total_amount_fin}\",\"{$exp_row['deleted_tot']}\",\"{$exp_row['net']}\",\"{$exp_row['added_by_agency']}\",\"{$exp_row['added_by_sats']}\",\"{$exp_row['salesrep']}\"\n";
	}
	
	echo $export_str;
	exit();
	
}else{
	echo $getContent;
}
	


?>

<div id="mainContent">

<div class="sats-middle-cont">
	
	<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="/new_jobs_report.php"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
   <table cellpadding=0 cellspacing=0 >
    <tr class="tbl-view-prop">
        <td>
          <form action="" id="date_range" method="get">
            <div class="aviw_drop-h">
            
			  <div class="fl-left">
				<label>Report from:</label>
                <input type="text" name="from" class="datepicker addinput searchstyle" value="<?php echo $from; ?>">
			  </div>
			
  <div class="fl-left">
   <label>to:</label>
   <input type="text" name="to" class="datepicker addinput searchstyle" value="<?php echo $to; ?>">
  </div>
  
  <div class="fl-left">
   <label>State:</label>
   <select style="width: 70px;" id="state" name="state" class="addinput vpr-adev-sel">
		<option value="">----</option> 
		<option value="NSW" <?php echo ($state=='NSW')?'selected="selected"':''; ?>>NSW</option>
		<option value="VIC" <?php echo ($state=='VIC')?'selected="selected"':''; ?>>VIC</option>
		<option value="QLD" <?php echo ($state=='QLD')?'selected="selected"':''; ?>>QLD</option>
		<option value="ACT" <?php echo ($state=='ACT')?'selected="selected"':''; ?>>ACT</option>
		<option value="TAS" <?php echo ($state=='TAS')?'selected="selected"':''; ?>>TAS</option>
		<option value="SA" <?php echo ($state=='SA')?'selected="selected"':''; ?>>SA</option>
		<option value="WA" <?php echo ($state=='WA')?'selected="selected"':''; ?>>WA</option>
		<option value="NT" <?php echo ($state=='NT')?'selected="selected"':''; ?>>NT</option>
	 </select>
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
  
  
  <div class="fl-right pull-right" style="margin:0;">
	<a href="/new_jobs_report.php?export=1&get_sats=1">
		<button type="button" class="submitbtnImg">Export</button>
	</a>
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

if( $_GET['get_sats']==1 ){ ?>

<div class="report">
    
<h2 class="heading">
	Report <?php echo $from; ?> for <?php echo $to; ?>
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



<table border=0 cellspacing=0 cellpadding=5 width=100% class='table-center tbl-fr-red'>
	<tr bgcolor="#b4151b">
		<th style="text-align: left;">Agency</th>
		<th>State</th>
		<?php
		// job type
		foreach( $jt_arr as $jt_row ){				
		?>
			<th><?php echo getJobTypeAbbrv($jt_row); ?></th>
		<?php
		}
		?>	
		<th>Total New</th>
		<th>Total $</th>
		<th>Deleted</th>
		<th>Net</th>
		<th>Added By Agency</th>
		<th>Added By SATS</th>		
	</tr>	
	<?php
	// distint agency
	$sr_sql = get_num_services('','',$from,$to,'a.agency_id',0,$state);
	?>
	<?php

	if(count($export)>0){

	$serv_tot_gt = array();
	$ctr = 0;
	$tot_jb_tot = 0;
	foreach( $export as $exp_row ){ ?>

		<tr bgcolor="<?php echo ($ctr%2==0)?'ffffff':'#eeeeee'; ?>">
			<td style="text-align: left;">
			<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$exp_row['agency_id']}"); ?>
			<a href="<?php echo $ci_link; ?>"><?php echo $exp_row['agency']; ?></a></td>	
			<td><?php echo $exp_row['state']; ?></td>
			<?php
			//$jt_sql = jgetAllJobTypes();
			$gross_tot = 0;
			$i = 0;
			// job types
			foreach( $exp_row['job_type_count'] as  $jtc_count ){ ?>
				<td><?php echo $jtc_count; ?></td>
			<?php
				$serv_tot_gt[$i] += $jtc_count;
				$i++;
			}						
			?>
			<td><?php echo $exp_row['total_new']; ?></td>	
			<td><?php echo '$'.number_format($exp_row['total_amount'],2); ?></td>
			<td><?php echo $exp_row['deleted_tot']; ?></td>
			<td><?php echo $exp_row['net']; ?></td>
			<td><?php echo $exp_row['added_by_agency']; ?></td>
			<td><?php echo $exp_row['added_by_sats']; ?></td>
			<?php 								
				
			?>
		</tr>
	<?php	
		$total_new_gt += $exp_row['total_new'];
		$total_amount_gt += $exp_row['total_amount'];
		$deleted_tot_gt += $exp_row['deleted_tot'];
		$net_gt += $exp_row['net'];
		$added_by_agency_gt += $exp_row['added_by_agency'];
		$added_by_sats_gt += $exp_row['added_by_sats'];
		
		$ctr++;
	}
	?>				

	<tr bgcolor="#DDDDDD">
		<td style="text-align: left;"><strong>TOTAL</strong></td>
		<td>&nbsp;</td>
		<?php
		foreach($serv_tot_gt as $val){ ?>
			<td><strong><?php echo ($val>0)?$val:''; ?></strong></td>
		<?php
		}
		?>
		<td><strong><?php echo ($total_new_gt>0)?$total_new_gt:''; ?></strong></td>
		<td><strong><?php echo '$'.number_format($total_amount_gt,2); ?></strong></td>
		<td><strong><?php echo ($deleted_tot_gt>0)?$deleted_tot_gt:''; ?></strong></td>
		<td><strong><?php echo ($net_gt>0)?$net_gt:''; ?></strong></td>	
		<td><strong><?php echo ($added_by_agency_gt>0)?$added_by_agency_gt:''; ?> <?php echo ($added_by_agency_gt>0)?'('.number_format((($added_by_agency_gt/$total_new_gt)*100)).'%)':''; ?></strong></td>
		<td><strong><?php echo ($added_by_sats_gt>0)?$added_by_sats_gt:''; ?> <?php echo ($added_by_sats_gt>0)?'('.number_format((($added_by_sats_gt/$total_new_gt)*100)).'%)':''; ?></strong></td>
	</tr>

	<?php
	}else{ ?>
	<tr>
		<td>No results</td>
		<td colspan="100%">&nbsp;</td>
	</tr>
	<?php	
	}
	?>
</table>


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
table.tbl-fr-red td,
table.tbl-fr-red th{
	border: 1px solid #cccccc;
}
</style>
</body>
</html> 
