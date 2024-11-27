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

$date_ranges[] = array(
'title' => 'Month to date',
'from' => date('Y-m-') . "01",
'to' => $today
);

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

$function_params = array('from' => $from, 'to' => $to);

$report = new Report();
$data = $report -> getAdminReportData($function_params);
?>
<div id="mainContent">
	<h1 class="style4">SATS Reports</h1>
	<h5 class="style3">
	<hr noshade="noshade" size=1 />
	<form action="" id="date_range" method="get">
	Report from: <input type="text" name="from" value="<?php echo $from_display; ?>" SIZE=15>
	<a href="#" onClick="cal.select(document.forms['date_range'].from,'anchor1','dd/MM/yyyy'); return false;" name="anchor1" id="anchor1">
	<img src='images/cal.gif' border='0' /></a>
	&nbsp;&nbsp;&nbsp;
	to: <input type="text" name="to" value="<?php echo $to_display; ?>" SIZE=15>
	<a href="#" onClick="cal.select(document.forms['date_range'].to,'anchor1','dd/MM/yyyy'); return false;" name="anchor2" id="anchor2">
	<img src='images/cal.gif' border='0' /></a>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="submit" value="Generate" />
	</p>
	<p><?php foreach($date_ranges as $range): ?>
		<a href="?from=<?php echo $range['from'];?>&to=<?php echo $range['to']; ?>" /><?php echo $range['title']; ?></a>&nbsp;|&nbsp;		
		<?php endforeach; ?>
		
		</p>
	<hr noshade="noshade" size=1 />
	</form>
	<p>&nbsp;</p>
	<h1 class="style4">
		Report for <?php echo $from_display;?>
		to <?php echo $to_display;?>
	</h1>
	<h5 class="style3">
	<p>
		Status Breakdown
	</p>
	<table border=1 cellpadding=0 cellspacing=0  width=740>
		<tr>
			<td>
			<table border=0 cellspacing=0 cellpadding=5 width=100% class='centered bordered'>
				<tr bgcolor="#F0F0F0">
					<td>Total</td>
					<?php foreach($data['status'] as $status) echo "<td>" . $status['status'] . "</td>"; ?>
				</tr>
				<tr>
				<td><?php echo $data['total']; ?></td>
				<?php foreach($data['status'] as $status) echo "<td>" . $status['num_jobs'] . "</td>"; ?>
		</tr>
	</table> </td></tr></table>
	
	<p>&nbsp;</p>
	<p>
		Type Breakdown
	</p>
	<table border=1 cellpadding=0 cellspacing=0  width=740>
		<tr>
			<td>
			<table border=0 cellspacing=0 cellpadding=5 width=100% class='centered bordered'>
				<tr bgcolor="#F0F0F0">
					<td>Total</td>
					<?php foreach($data['job_type'] as $status) echo "<td>" . $status['job_type'] . "</td>"; ?>
				</tr>
				<tr>
				<td><?php echo $data['total']; ?></td>
				<?php foreach($data['job_type'] as $status) echo "<td>" . $status['num_jobs'] . "</td>"; ?>
		</tr>
	</table> </td></tr></table>
	
	
	<div class='report_box'>
	<p>
		Tech Completed Jobs
	</p>
	<table border=1 cellpadding=0 cellspacing=0  width=320>
		<tr>
			<td>
			<table border=0 cellspacing=0 cellpadding=5 width=100% class='bordered'>
				<tr bgcolor="#F0F0F0">
					<th>Tech Name</th><td align='center'># Completed</td>
				</tr>
				<?php foreach($data['techs'] as $tech): ?>
				<tr>
					<th><?php echo ($tech['tech_name'] ? $tech['tech_name'] : "N/A"); ?></th><td align='center'><?php echo $tech['num_jobs']; ?></td>
				</tr>	
				<?php endforeach; ?>
		</tr>
	</table> </td></tr></table>
	</div>
	
	<div class='report_box'>
	<p>
		Staff Booked Jobs
	</p>
	<table border=1 cellpadding=0 cellspacing=0  width=320>
		<tr>
			<td>
			<table border=0 cellspacing=0 cellpadding=5 width=100% class='bordered'>
				<tr bgcolor="#F0F0F0">
					<th>Tech Name</th><td align='center'># Completed</td>
				</tr>
				<?php foreach($data['staff'] as $staff): ?>
				<tr>
					<th><?php echo ($staff['staff_name'] ? $staff['staff_name'] : "SATS System"); ?></th><td align='center'><?php echo $staff['num_jobs']; ?></td>
				</tr>	
				<?php endforeach; ?>
		</tr>
	</table> </td></tr></table>
	</div>
	
	 </h5>
	<p>
		&nbsp;
	</p>
	<p>
		&nbsp;
	</p>
	<p>
		<!-- end #mainContent -->
	</p>
</div>
<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats -->
<br class="clearfloat" />
<div id="footer">
	<p class="style13">
		Logged in to SATs CRM
	</p>
	<!-- end #footer -->
</div>
<!-- end #container --></div>
</body>
</html> 