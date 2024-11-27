<?

$title = "SATS Reports";

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');

# Report Parameters

if(isValidDate($_GET['from']) && isValidDate($_GET['to']))
{
	$to = convertDate($_GET['to']);
	$from = convertDate($_GET['from']);
}
else
{
	# Default back to month to date if nothing entered or bad input
	$to = date('Y-m-d');
	$from = date('Y-m-') . "01";
}

# Get dates for << prev day and next day >> links
$tmp = explode("-", $from);

$prev_day = array(
	'from' => date('Y-m-d', strtotime('-1 day', mktime(0,0,0, $tmp[1], $tmp[2], $tmp[0]))),
	'to' => date('Y-m-d', strtotime('-1 day', mktime(0,0,0, $tmp[1], $tmp[2], $tmp[0]))),
	'title' => '<span class="arw-lft2">&nbsp;</span> Previous Day'
);

$next_day = array(
	'from' => date('Y-m-d', strtotime('+1 day', mktime(0,0,0, $tmp[1], $tmp[2], $tmp[0]))),
	'to' => date('Y-m-d', strtotime('+1 day', mktime(0,0,0, $tmp[1], $tmp[2], $tmp[0]))),
	'title' => 'Next Day <span class="arw-rgt2">&nbsp;</span>',
	'css' => 'float: right;'
);


# Create predefined date ranges
$today = date('Y-m-d');

$date_ranges = array();

$date_ranges[] = array(
'title' => 'Today',
'from' => $today,
'to' => $today
);

$date_ranges[] = array(
'title' => 'Yesterday',
'from' => date('Y-m-d', (strtotime('-1 days'))),
'to' => date('Y-m-d', (strtotime('-1 days')))
);

$date_ranges[] = array(
'title' => 'Last Week',
'from' => date('Y-m-d', (strtotime('-7 days'))),
'to' => $today
);

$date_ranges[] = array(
'title' => 'Next Week',
'from' => $today,
'to' => date('Y-m-d', (strtotime('+7 days')))
);

/*
$date_ranges[] = array(
'title' => 'Month to date',
'from' => date('Y-m-') . "01",
'to' => $today
);
 */

$date_ranges[] = array(
'title' => date('F', strtotime("this month")),
'from' => date('Y-m-', strtotime("this month")) . "01",
'to' => date('Y-m-t', strtotime("this month"))
);

$date_ranges[] = array(
'title' => date('F', strtotime("last month")),
'from' => date('Y-m-', strtotime("last month")) . "01",
'to' => date('Y-m-t', strtotime("last month"))
);

$date_ranges[] = array(
'title' => date('F', strtotime("-2 months")),
'from' => date('Y-m-', strtotime("-2 months")) . "01",
'to' => date('Y-m-t', strtotime("-2 months"))
);

$date_ranges[] = array(
'title' => date('F', strtotime("-3 months")),
'from' => date('Y-m-', strtotime("-3 months")) . "01",
'to' => date('Y-m-t', strtotime("-3 months"))
);


# Display dates in dd/mm/yyyy
$to_display = convertDateAus($to);
$from_display = convertDateAus($from);

# For individual reports get staff id / tech id from session details - First try TechID, then StaffID
$tech_id = (!empty($_SESSION['USER_DETAILS']['TechID']) ? (int)$_SESSION['USER_DETAILS']['TechID'] : "z");
$staff_id = (!empty($_SESSION['USER_DETAILS']['StaffID']) ? (int)$_SESSION['USER_DETAILS']['StaffID'] : "z");

# Little hack to prevent non-admin looking at all reports
if(!is_numeric($tech_id) && !is_numeric($staff_id))
{
	die("Access denied");
}
# Ensure Techs get reports relating to the TechID column only (StaffID not required)
if(is_numeric($tech_id)) $staff_id = "z";

# Staff and tech id's to filter
$staff_filter = array(
'staff_id' => $staff_id,
'tech_id' => $tech_id
);

$report_params = array('from' => $from, 'to' => $to, 'staff_id' => $staff_id, 'tech_id' => $tech_id);

$report = new Report();

$data = $report -> getReportData($report_params);

$all_status_types = $report->getAllStatuses();
$all_job_types = $report->getAllJobTypes();

?>
<div id="mainContent">

 <div class="sats-middle-cont">
  
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="My Reports" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>My Reports</strong></a></li>
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
				<input type="text" class="addinput searchstyle datepicker" name="from" value="<?php echo $from_display; ?>">
			  </div>
			
  <div class="fl-left">
   <label>to:</label>
				<input type="text" name="to" value="<?php echo $to_display; ?>" class="addinput searchstyle datepicker">
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
	Report for <?php echo $from_display;?>
	to <?php echo $to_display;?>
</h1>


<?php if(is_numeric($staff_id)): ?>
<p>This report provides statistics on all jobs booked by you in the SATS system.</p>
<?php elseif(is_numeric($tech_id)): ?>
<p>This report provides statistics on all jobs assigned to you as a technician.</p>
<?php endif; ?>	

<p>Status Breakdown</p>

</div>



<table border=0 cellspacing=0 cellpadding=5 width=100% class='table-center tbl-fr-red'>
			<tr bgcolor="#b4151b">
				<th>Total</th>
				<!-- <?php foreach($data['status'] as $status) echo "<th>" . $status['status'] . "</th>"; ?> -->
				<?php foreach($all_status_types as $status) echo "<th>" . $status['status'] . "</th>"; ?>
			</tr>
			<tr>
			<td class='<?php echo ($data['total'] > 0 ? "highlighted" : "no_result");?>'><?php echo $data['total']; ?></td>
			
				<?php foreach($all_status_types as $status):
				
					# Defaul value is 0, replace if found below
					$value = "0";
				
					foreach($data['status'] as $status_i):
					
					if($status_i['status'] == $status['status']):
						$value = $status_i['num_jobs'];
						break;
					endif;
					
					endforeach; ?>
				
				<?php echo "<td class='" . ($value > 0 ? "highlighted" : "no_result") . "'> " . $value . "</td>"; endforeach; ?>
			
			
	</tr>
</table>


<h2 class="heading">Type Breakdown</h2>

<table border=0 cellspacing=0 cellpadding=5 width=100% class='table-center tbl-fr-red'>
			<tr bgcolor="#b4151b">
				<th>Total</th>
				<!-- <?php foreach($data['job_type'] as $status) echo "<th>" . $status['job_type'] . "</th>"; ?> -->
				<?php foreach($all_job_types as $jobs) echo "<th>" . $jobs['job_type'] . "</td>"; ?>
			</tr>
			<tr>
			<td class='<?php echo ($data['total'] > 0 ? "highlighted" : "no_result");?>'><?php echo $data['total']; ?></td>

			<?php foreach($all_job_types as $jobs):
				
					# Defaul value is 0, replace if found below
					$value = "0";
				
					foreach($data['job_type'] as $job):
					
					if($job['job_type'] == $jobs['job_type']):
						$value = $job['num_jobs'];
						break;
					endif;
					
					endforeach; ?>
				
				<?php echo "<td class='" . ($value > 0 ? "highlighted" : "no_result") . "'> " . $value . "</td>"; endforeach; ?>
	</tr>
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



</body>
</html> 
