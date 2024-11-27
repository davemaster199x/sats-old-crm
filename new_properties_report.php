<?

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

$title = "New Properties Report";

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');

include('inc/activity_functions.php');

function get_num_services($agency_id,$ajt,$from,$to){
					
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
		WHERE p.`agency_id` ={$agency_id}
		AND ps.`alarm_job_type_id` ={$ajt}
		AND ps.`service` = 1
		AND p.deleted = 0
		{$str}
	";
	//echo $sql;
	return mysql_query($sql);

}


function get_deleted($agency_id,$from,$to){

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
		AND p.`deleted` = 1
		AND ps.`service` = 1
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
		$str = "AND CAST(ps.`status_changed` AS DATE) BETWEEN '{$from2}' AND '{$to2}'";
	}

	$sql = "
		SELECT *
		FROM `property_services` AS ps
		LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE a.`agency_id` ={$agency_id}
		AND ps.`service` = 1
		AND a.`country_id` = {$_SESSION['country_default']}
		AND p.`added_by` > 0
		{$str}
	";
	//echo $sql;
	return mysql_query($sql);
	
}


function getAddedByAgency($agency_id,$from,$to){
	
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
		WHERE a.`agency_id` ={$agency_id}
		AND ps.`service` = 1
		AND a.`country_id` = {$_SESSION['country_default']}
		AND p.`added_by` <= 0
		{$str}
	";
	//echo $sql;
	return mysql_query($sql);
	
}

# Report Parameters
/*
if(isValidDate($_GET['from']) && isValidDate($_GET['to']))
{
	$to = convertDate($_GET['to']);
	$from = convertDate($_GET['from']);
	$to_text = " to ";
}
elseif($_GET['from'] == 'all')
{
	$to = 'All Records';
	$from = '';
	$to_text = '';
}
else
{
	# Default back to month to date if nothing entered or bad input
	$to = date('Y-m-d');
	$from = date('Y-m-') . "01";
	$to_text = " to ";
}
*/


// date
$all = $_REQUEST['all'];
$from = ($_REQUEST['from'])?$_REQUEST['from']:date("01/m/Y");
$to = ($_REQUEST['to'])?$_REQUEST['to']:date("t/m/Y");
$state = $_REQUEST['state'];


# Get dates for << prev day and next day >> links

/*
$tmp = explode("-", $from);

$prev_day = array(
	'from' => date('Y-m-d', strtotime('-1 day', mktime(0,0,0, $tmp[1], $tmp[2], $tmp[0]))),
	'to' => date('Y-m-d', strtotime('-1 day', mktime(0,0,0, $tmp[1], $tmp[2], $tmp[0]))),
	'title' => '<< Prev Day'
);

$next_day = array(
	'from' => date('Y-m-d', strtotime('+1 day', mktime(0,0,0, $tmp[1], $tmp[2], $tmp[0]))),
	'to' => date('Y-m-d', strtotime('+1 day', mktime(0,0,0, $tmp[1], $tmp[2], $tmp[0]))),
	'title' => 'Next Day >>',
	'css' => 'float: right;'
);
*/



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

?>

<div id="mainContent">

<div class="sats-middle-cont">
	
	<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="/new_properties_report.php"><strong><?php echo $title; ?></strong></a></li>
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
				$ajt_sql2 = getDynamicServices();
				while($ajt2 = mysql_fetch_array($ajt_sql2)){ 
				
				
				?>
					<th><img src="images/serv_img/<?php echo getServiceIcons($ajt2['id'],1); ?>" /></th>
				<?php
				}
				?>	
				
				<th>Total New</th>
				<th>Deleted</th>
				<th>Net</th>
				
				<th>By SATS</th>
				<th>By Agency</th>
				
				<!--
				<th>Agency Deleted</th>
				<th>SATS Deleted</th>
				<th>NET Total</th>
				-->
			</tr>
				<?php 
				/*
				foreach($data['staff'] as $name=>$values):
					
					if($name == "") $name = 'N/A';

					echo "<tr>";
					echo "<td bgcolor='#F0F0F0'>" . $name . "</td>";

					# Total
					$class = $values['total'] < 1 ? "no_result" : "highlighted";
					echo "<td class='{$class}'>" . $values['total'] . "</td>";

					# Active
					$class = $values['agency_deleted'][0] < 1 ? "no_result" : "highlighted";
					echo "<td class='{$class}'>" . (is_numeric($values['agency_deleted'][0]) ? $values['agency_deleted'][0] : "0") . "</td>";

					# Deleted
					$class = $values['agency_deleted'][1] < 1 ? "no_result" : "highlighted";
					echo "<td class='{$class}'>" . (is_numeric($values['agency_deleted'][1]) ? $values['agency_deleted'][1] : "0") . "</td>";

					# Yes Service
					$class = $values['service'][1] < 1 ? "no_result" : "highlighted";
					echo "<td class='{$class}'>" . (is_numeric($values['service'][1]) ? $values['service'][1] : "0") . "</td>";

					# No Service
					$class = $values['service'][0] < 1 ? "no_result" : "highlighted";
					echo "<td class='{$class}'>" . (is_numeric($values['service'][0]) ? $values['service'][0] : "0") . "</td>";
					
					?>
					
					<td>0</td>
					<td>0</td>
					<td>0</td>
					
					<?php

					echo "</tr>";
				endforeach; 
				*/
				?>
				
			<?php
			// distint agency
			$sr_sql = getActivity('','',$from,$to,$_SESSION['country_default'],$state);
			?>
			<?php
			
			if(mysql_num_rows($sr_sql)>0){
			
				$serv_tot = array();
				$ctr = 0;
				while($sr = mysql_fetch_array($sr_sql)){ ?>
				
				<tr bgcolor="<?php echo ($ctr%2==0)?'ffffff':'#eeeeee'; ?>">
					<td style="text-align: left;">
					<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$sr['agency_id']}"); ?>
					<a href="<?php echo $ci_link; ?>"><?php echo $sr['agency_name']; ?></a></td>	
					<td><?php echo $sr['state']; ?></td>
					<?php
					$ajt_sql2 = getDynamicServices();
					$gross_tot = 0;
					$i = 0;
					
					while($ajt2 = mysql_fetch_array($ajt_sql2)){ ?>
						<td>
							<?php 
							$sa = mysql_num_rows(get_num_services($sr['agency_id'],$ajt2['id'],$from,$to)); 
							if($sa>0){
								echo $sa;
							}
							?>
						</td>
					<?php
						$gross_tot += $sa;
						$serv_tot[$i] += $sa;
						$i++;
					}						
					?>
					
					<td>
						<?php 
							$gross_tot; 
							if($gross_tot>0){
								echo $gross_tot;
							}
						?>
					</td>						
					<td>
						<?php 
						$deleted = mysql_num_rows(get_deleted($sr['agency_id'],$from,$to));
						if($deleted>0){
							echo '<span style="color:red;">'.$deleted.'</span>';
						}
						?>
						
					</td>
					<td>
						<?php 
							$net = ($gross_tot-$deleted); 
							echo ($net<0)?'<span style="color:red">'.$net.'</span>':$net;
						?>
					</td>
					<td>
						<?php 
						$add_by_sats = mysql_num_rows(getAddedBySats($sr['agency_id'],$from,$to)); 
						if($add_by_sats>0){
							echo $add_by_sats;
						}
						?>
					</td>
					<td>
						<?php 
						$add_by_agency = mysql_num_rows(getAddedByAgency($sr['agency_id'],$from,$to)); 
						if($add_by_agency>0){
							echo $add_by_agency;
						}
						?>
					</td>
					
					
					<!--
					<td><?php //echo $sats_del = mysql_num_rows(get_deleted($sr['salesrep'],0,$from,$to)); ?></td>
					<td><?php //echo $net_total = ($gross_tot-$deleted-$sats_del);  ?></td>
					-->
					<?php 
						$add_by_sats_tot += $add_by_sats;
						$add_by_agency_tot += $add_by_agency;
						$gross_tot_tot += $gross_tot;
						$deleted_tot += $deleted;
						$sats_del_tot += $sats_del;
						$net_total_tot += $net;						
					?>
				</tr>
				
				<?php	
					$ctr++;
				}
				?>				
					
				<tr bgcolor="#DDDDDD">
					<td style="text-align: left;"><strong>TOTAL</strong></td>
					<td>&nbsp;</td>
					<?php
					foreach($serv_tot as $val){ ?>
						<td><strong><?php echo ($val>0)?$val:''; ?></strong></td>
					<?php
					}
					?>
					
					<td><strong><?php echo ($gross_tot_tot>0)?$gross_tot_tot:''; ?></strong></td>
					<td><strong><?php echo ($deleted_tot>0)?$deleted_tot:''; ?></strong></td>
					<td><strong><?php echo ($net_total_tot>0)?$net_total_tot:''; ?></strong></td>
					
					<td><strong><?php echo ($add_by_sats_tot>0)?$add_by_sats_tot:''; ?> (<?php echo ($add_by_sats_tot>0)?number_format((($add_by_sats_tot/$gross_tot_tot)*100)).'%':''; ?>)</strong></td>
					<td><strong><?php echo ($add_by_agency_tot>0)?$add_by_agency_tot:''; ?> (<?php echo ($add_by_sats_tot>0)?number_format((($add_by_agency_tot/$gross_tot_tot)*100)).'%':''; ?>)</strong></td>
					
					<!--
					<td><?php echo $deleted_tot; ?></td>
					<td><?php echo $sats_del_tot; ?></td>
					<td><?php echo $net_total_tot; ?></td>
					-->
				</tr>
			
			<?php
			}else{ ?>
				<tr>
				<td>No results</td>
				<?php
				$ajt_sql2 = getDynamicServices();
				while($ajt2 = mysql_fetch_array($ajt_sql2)){ ?>
					<td>&nbsp;</td>
				<?php	
				}
				?>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				</tr>
				<tr bgcolor="#DDDDDD">
					<td>TOTAL</td>
					<?php
					$ajt_sql2 = getDynamicServices();
					while($ajt2 = mysql_fetch_array($ajt_sql2)){ ?>
						<td>&nbsp;</td>
					<?php	
					}
					?>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
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
