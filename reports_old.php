<?php

$title = "Reports";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$url = 'reports.php';

// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class;

$from = mysql_real_escape_string($_REQUEST['from']);
$from2 = ( $from != '' )?$crm->formatDate($from):'';
$to = mysql_real_escape_string($_REQUEST['to']);
$to2 = ( $to != '' )?$crm->formatDate($to):'';

	

?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.yello_mark{
	background-color: #ffff9d;
}
.green_mark{
	background-color: #c2ffa7;
}
.payment_details_table td {
    padding: 5px;
	border: none;
	text-align: left;
}
.payment_details_table tr {
	border: none;
}
.save_div{
	float:right; 
	margin-bottom: 20px; 
	position: relative; 
	bottom: 85px;
	display:none;
}
.jcolorItRed{
	color: red;
}
.jcolorItGreen{
	color: green;
}
.reports_tbl{
	margin-top: 0px; 
	margin-bottom: 13px; 
	width:40%;
	float: left;
	margin-right: 25px;
}
</style>





<div id="mainContent">


   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
			  <ul>
				<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
				<li class="other first"><a title="<?php echo $title ?>" href="<?php echo $url; ?>"><strong><?php echo $title ?></strong></a></li>
			  </ul>
			</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['print_clear']==1){
			echo '<div class="success">Printed jobs has been cleared</div>';
		}
		
		
		//echo date('Y-m-d', strtotime("-30 days"));
		
		
		?>
		
	
			
			
			<?php
			
			// no sort yet
			if($_REQUEST['sort']==""){
				$sort_arrow = 'up';
			}
			
			?>

	
			<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
				
				<tr class="toprow jalign_left">
					<th>
						<img class="inner_icon" src="images/button_icons/house_icon.png">
						Property Reports
					</th>										
				</tr>	
				<tr class="body_tr jalign_left">					
					<td><a href="view_properties.php">Active Properties</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="active_properties.php">Active Job Properties</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="view_deleted_properties.php">Inactive Properties</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="new_properties_report.php">New Properties</a></td>	
				</tr>
									
			</table>
			
			
			
			<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
				
				<tr class="toprow jalign_left">
					<th>
						<img class="inner_icon" src="images/button_icons/dollar_icon.png">
						Accounts Reports
					</th>										
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="credit_requests.php">Credit Requests</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="debtors_report.php">Debtors</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="expense_summary.php">Expenses</a></td>
				</tr>				
				<tr class="body_tr jalign_left">					
					<td><a href="nlm_properties.php">No Longer Managed</a></td>
				</tr>
					
			</table>
	
			
			<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
				
				<tr class="toprow jalign_left">
					<th>
						<img class="inner_icon" src="images/button_icons/employee_icon.png">
						Employee Reports
					</th>										
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="incident_and_injury_report_list.php">Incident Reports</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="leave_requests.php">Leave Reports</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="view_sats_users.php">Users</a></td>	
				</tr>
				
					
			</table>
			
			
			<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
				
				<tr class="toprow jalign_left">
					<th>
						<img class="inner_icon" src="images/button_icons/car_icon.png">
						Vehicle Reports
					</th>										
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="kms.php">KMS Report</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="view_vehicles.php">Vehicles</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="view_tools.php">View Tools</a></td>	
				</tr>
				
					
			</table>
			
			
			<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
				
				<tr class="toprow jalign_left">
					<th>
						<img class="inner_icon" src="images/button_icons/notes-button.png">
						Job Reports
					</th>										
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="view_jobs.php">All Jobs</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="booked_jobs.php">Booked Jobs</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="booked.php">Booked Report</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="cancelled_jobs.php">Cancelled Jobs</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="completed_report.php">Completed Jobs</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="daily_figures.php">Daily Figures</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="deleted_jobs.php">Deleted Jobs</a></td>
				</tr>				
				<tr class="body_tr jalign_left">					
					<td><a href="new_jobs_report.php">New Jobs Report</a></td>
				</tr>
				<?php $next_month = date("M",strtotime("+1 month")); ?>
				<tr class="body_tr jalign_left">					
					<td><a href="servicedue.php">Service Due (<?php echo strtoupper($next_month); ?>)</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="servicedue.php">Service Due Report</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="todays_jobs.php">Todays Jobs</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="urgent_jobs.php">Urgent Jobs</a></td>
				</tr>
				<?php
				// quick solution for custom query vacant jobs
				$custom_query = " AND j.property_vacant = 1 AND j.status NOT IN('Completed','Cancelled','Merged Certificates','Booked','Pre Completion') ";
				$jtot = mysql_num_rows($jc->getJobs('','',$sort,$order_by,'','','','','','','','','','','','',0,'','','','','','','','','',$custom_query));
				?>
				<tr class="body_tr jalign_left">					
					<td><a href="vacant_jobs.php">Vacant Jobs <?php echo ($jtot>0)?'<span class="hm-circle">'.$jtot.'</span>':''; ?></a></td>
				</tr>
				
					
			</table>
			
			
			<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
				
				<tr class="toprow jalign_left">
					<th>
						<img class="inner_icon" src="images/button_icons/notes-button.png">
						General Reports
					</th>										
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="report_admin.php">Admin Report</a></td>	
				</tr>				
				<tr class="body_tr jalign_left">					
					<td><a href="call_centre_report.php">Call Centre</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="cron_report.php">Cron Report</a></td>
				</tr>									
				<tr class="body_tr jalign_left">					
					<td><a href="figures.php">Figures</a></td>
				</tr>		
				<tr class="body_tr jalign_left">					
					<td><a href="icons.php">Icons</a></td>
				</tr>						
				
					
			</table>
			
			
			<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
				
				<tr class="toprow jalign_left">
					<th>
						<img class="inner_icon" src="images/button_icons/notes-button.png">
						Sales Reports
					</th>										
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="sales_activity.php">Sales Activity</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="report_sales_admin.php">Sales Report</a></td>	
				</tr>	
				<tr class="body_tr jalign_left">					
					<td><a href="sales_snapshot.php">Sales Snapshot</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="view_target_agencies.php">Target Agencies</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="view_all_agencies.php">All Agencies Report</a></td>	
				</tr>
					
			</table>
			
			
			<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
				
				<tr class="toprow jalign_left">
					<th>
						<img class="inner_icon" src="images/button_icons/agency_icon.png">
						Agency Reports
					</th>										
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="agency_keys.php">Agency Keys</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="user_manager.php">Agency Logins</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="agency_portal_data.php">Agency Portal Data</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="view_deactivated_agencies.php">Deactivated Agencies</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="franchise_groups.php">Franchise Groups</a></td>	
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="maintenance_program_agencies.php">MM Agencies</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="no_auto_renew_agencies.php">No Auto Renew Agencies</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="whiteboard.php">Whiteboard</a></td>
				</tr>
					
			</table>
			
			
			
			<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
				
				<tr class="toprow jalign_left">
					<th>
						<img class="inner_icon" src="images/button_icons/notes-button.png">
						Operations Report
					</th>										
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="contractors.php">Contractors</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="expiring.php">Expiring Alarms</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="installed_alarms.php">Installed Alarms</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="key_tracking.php">Key Tracking</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="kpi.php">KPIs</a></td>
				</tr>	
				<tr class="body_tr jalign_left">					
					<td><a href="missed_jobs.php">Missed Jobs</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="purchase_order.php">Purchase Orders</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="region_numbers.php">Region Numbers</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="status.php">Status Report</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="add_tech_stock.php">Stock Items</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="suppliers.php">Suppliers</a></td>
				</tr>
				<tr class="body_tr jalign_left">					
					<td><a href="tech_stock.php">Tech Stock Report</a></td>
				</tr>				
					
			</table>



		
	</div>
</div>

<br class="clearfloat" />

</body>
</html>