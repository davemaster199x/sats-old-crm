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

$sc_id = $_SESSION['USER_DETAILS']['ClassID'];
$sa_id = $_SESSION['USER_DETAILS']['StaffID'];	
$tester = array(2070,2025);

function getCrmPages($page_id_arr){
	
	$pages_id_imp = implode(",",$page_id_arr);

	$sql = "
		SELECT * FROM `crm_pages`
		WHERE `crm_page_id` IN({$pages_id_imp})
		ORDER BY `page_name` ASC
	";
	return mysql_query($sql);
	
}

function getCrmPagesVer2($page_id_arr){
	
	$pages_id_imp = "'".implode("','",$page_id_arr)."'";

	$sql = "
		SELECT * FROM `crm_pages`
		WHERE `page_url` IN($pages_id_imp)
		ORDER BY `page_name` ASC
	";
	return mysql_query($sql);
}

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
	margin-right: 25px;
}
.reports_column{
	float: left;
	width: 45%;
}
.reports_col1{
	margin-right: 15px;
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

	
			
			
			<div class="reports_column reports_col1">
			
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
				
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/house_icon.png">
							Property Reports
						</th>
					</tr>
						<?php
						// page id
						$page_id_arr = array(1,2,6,56);
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){
							
							$page_url = $page['page_url'];
							$page_name = $page['page_name']." (OLD)";
							
							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							)
							{ 
														
								if( $page['page_url'] == 'active_properties.php' ){
										
									$crm_ci_page = 'properties/active_job_properties';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'view_properties.php' ){
										
									$crm_ci_page = 'properties/active_properties';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'new_jobs_report.php' ){
										
									$crm_ci_page = 'reports/new_properties_report';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								} else if( $page['page_url'] == 'view_deleted_properties.php' ){
										
									$crm_ci_page = 'properties/deactivated_properties';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								} 
							
							?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page_url; ?>"><?php echo $page_name; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>
						<?php
						// static links
						$page_name = 'Property Services Updated to SATS';
						$crm_ci_page = 'properties/serviced_to_sats';
						$page_url = $crm->crm_ci_redirect($crm_ci_page);
						?>
						<tr class="body_tr jalign_left">					
							<td><a href="<?php echo $page_url; ?>"><?php echo $page_name; ?></a></td>	
						</tr>										
				</table>
				
				
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
	
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/employee_icon.png">
							Employee Reports
						</th>					
						<?php
						// page id
						$page_id_arr = array(96,98,86);
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){

							$page_url = $page['page_url'];
							$page_name = $page['page_name']." (OLD)";

							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							)
							{ 
								
								if( $page['page_url'] == 'leave_requests.php' ){
										
									$crm_ci_page = 'users/leave_requests';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}
								
								?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page_url; ?>"><?php echo $page_name; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>
					</tr>				
						
				</table>
				
				
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
	
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/notes-button.png">
							Job Reports
						</th>	
						<?php
						// page id
						$page_id_arr = array(9,10,43,11,12,47,14,56,62,61,24,27,28);
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){
							
							$page_url = $page['page_url'];
							$page_name = $page['page_name']." (OLD)";

							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							){ 
							
							if( $page['menu'] == 4 && $page['crm_page_id'] == 62 ){

								$next_month = strtoupper(date("M",strtotime("+1 month")));
								$crm_ci_page = 'jobs/future_pendings';
								$page_url = $crm->crm_ci_redirect($crm_ci_page);
								$page_name = "Service Due ({$next_month})";

							}else{								

								if( $page['page_url'] == 'view_jobs.php' ){
										
									$crm_ci_page = 'jobs';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'booked_jobs.php' ){
										
									$crm_ci_page = 'jobs/booked';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'deleted_jobs.php' ){
										
									$crm_ci_page = 'jobs/deleted';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'completed_jobs.php' ){
										
									$crm_ci_page = 'jobs/completed';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'completed_jobs.php' ){
										
									$crm_ci_page = 'jobs/completed';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'cancelled_jobs.php' ){
										
									$crm_ci_page = 'jobs/cancelled';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'new_jobs_report.php' ){
										
									$crm_ci_page = 'jobs/new_jobs_report';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'future_pendings.php' ){
										
									$crm_ci_page = 'jobs/future_pendings';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'todays_jobs.php' ){
										
									$crm_ci_page = 'jobs/todays_jobs';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'urgent_jobs.php' ){
										
									$crm_ci_page = 'jobs/urgent_jobs';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'vacant_jobs.php' ){
										
									$crm_ci_page = 'jobs/vacant';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'booked.php' ){
										
									$crm_ci_page = 'jobs/booked_report';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}
								
								
							}
							
							?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page_url; ?>"><?php echo $page_name; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>
					</tr>			
						
				</table>
				
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
                    
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/notes-button.png">
							Operations Report
						</th>
                        </tr>	
						<?php
						// page id 
                        if($_SESSION['country_default']==1){ //AU
                            $page_id_arr = array(66,48,51,52,53,54,68,59,63,69,117,72,45,157);
                        }else{ //NZ
                            $page_id_arr = array(66,48,51,52,53,54,68,59,63,69,117,72,45,153);
                        }
						
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){

							$page_url = $page['page_url'];
							$page_name = $page['page_name']." (OLD)";

							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							)
							{ 

								//$page_name = ( $page['crm_page_id'] == 45 )?'Completed Jobs Report':$page['page_name'];
								$page_name = ( $page['crm_page_id'] == 45 )?'Completed Jobs Report':$page_name;

								if( $page['page_url'] == 'kpi.php' ){
										
									$crm_ci_page = 'reports/kpi';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'installed_alarms.php' ){
										
									$crm_ci_page = 'reports/installed_alarms';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'contractors.php' ){
										
									$crm_ci_page = 'reports/contractors';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'key_tracking.php' ){
										
									$crm_ci_page = 'reports/key_tracking';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'status.php' ){

									$crm_ci_page = 'jobs/status';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];

								}else if( $page['page_url'] == 'completed_report.php' ){

									$crm_ci_page = 'jobs/completed_report';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];

								}else if( $page['page_url'] == 'discarded_alarms.php' ){

									$crm_ci_page = 'reports/discarded_alarms';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];

								}else if( $page['page_url'] == 'expiring.php' ){

									$crm_ci_page = 'reports/expiring';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];

								}
								else if( $page['page_url'] == 'missed_jobs.php' ){

									$crm_ci_page = 'jobs/missed_jobs';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];

								}else if( $page['page_url'] == 'tech_stock.php' ){

									$crm_ci_page = 'stock/tech_stock';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];

								}else if( $page['page_url'] == 'add_tech_stock.php' ){

									$crm_ci_page = 'stock/stock_items';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];

								}else if( $page['page_url'] == 'suppliers.php' ){

									$crm_ci_page = 'stock/suppliers';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];

								}else if( $page['page_url'] == 'region_numbers.php' ){

									$crm_ci_page = 'reports/region_numbers';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];

								}
															
							
							?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page_url; ?>"><?php echo $page_name; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>	

				</table>
				

			</div>
			
			
			
			
			
			<!-- RIGHT COLUMN -->
			<div class="reports_column reports_col2">
			
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
	
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/dollar_icon.png">
							Accounts Reports
						</th>
						<?php
						// page id
						$page_id_arr = array(131,132,133,134,141);
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){

							$page_url = $page['page_url'];
							$page_name = $page['page_name']." (OLD)";

							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							)
							{ 
								
								if( $page['page_url'] == 'credit_requests.php' ){
										
									$crm_ci_page = 'credit/request_summary';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}
								
								?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page_url; ?>"><?php echo $page_name; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>
					</tr>

						
				</table>
				
				
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
	
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/car_icon.png">
							Vehicle Reports
						</th>
						<?php
						// page id
						$page_id_arr = array(127,129,128);
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){

							$page_url = $page['page_url'];
							$page_name = $page['page_name']." (OLD)";

							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							)
							{
								
								if( $page['page_url'] == 'view_tools.php' ){
										
									$crm_ci_page = 'vehicles/view_tools';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'view_vehicles.php' ){
										
									$crm_ci_page = 'vehicles/view_vehicles';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}
								
								?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page_url; ?>"><?php echo $page_name; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>
					</tr>
						
				</table>
				
				
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
	
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/notes-button.png">
							General Reports
						</th>
						<?php
						// page id
						$page_id_arr = array(41,44,46,49,50);
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){

							$page_url = $page['page_url'];
							$page_name = $page['page_name']." (OLD)";
							
							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							)
							{ 
								
								if( $page['page_url'] == 'report_admin.php' ){
										
									$crm_ci_page = 'reports/report_admin';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'cron_report.php' ){
									$crm_ci_page = 'reports/cron_report';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
								}
								
								?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page_url; ?>"><?php echo $page_name; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>					
					</tr>									
						
				</table>
				
				
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
	
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/agency_icon.png">
							Agency Reports
						</th>	
					</tr>
					<?php
					// page id
					$page_id_arr = array(42,78,79,80,81,55,58);
					$pages_sql = getCrmPages($page_id_arr);
					while( $page = mysql_fetch_array($pages_sql) ){

						$page_url = $page['page_url'];
						$page_name = $page['page_name']." (OLD)";

						if( 
							$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
							$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
						)
						{
							if( $page['page_url'] == 'agency_keys.php' ){
										
								$crm_ci_page = 'agency/agency_keys';
								$page_url = $crm->crm_ci_redirect($crm_ci_page);
								$page_name = $page['page_name'];
								
							}else if( $page['page_url'] == 'agency_portal_data.php' ){
								$crm_ci_page = 'agency/agency_portal_data';
								$page_url = $crm->crm_ci_redirect($crm_ci_page);
								$page_name = $page['page_name'];
							}else if( $page['page_url'] == 'view_deactivated_agencies.php' ){
								$crm_ci_page = 'agency/view_deactivated_agencies';
								$page_url = $crm->crm_ci_redirect($crm_ci_page);
								$page_name = $page['page_name'];
							}else if( $page['page_url'] == 'maintenance_program_agencies.php' ){
								$crm_ci_page = 'agency/maintenance_program_agencies';
								$page_url = $crm->crm_ci_redirect($crm_ci_page);
								$page_name = $page['page_name'];
							}else if( $page['page_url'] == 'no_auto_renew_agencies.php' ){
								$crm_ci_page = 'agency/non_auto_renew_agencies';
								$page_url = $crm->crm_ci_redirect($crm_ci_page);
								$page_name = $page['page_name'];
							}
							


							?>
							<tr class="body_tr jalign_left">					
								<td><a href="<?php echo $page_url; ?>"><?php echo $page_name; ?></a></td>	
							</tr>
							
						<?php
						}
					}
					?>
					<?php
					// static links
					$page_name = 'Subscription Billing';
					$crm_ci_page = 'agency/subscription_billing_agencies';
					$page_url = $crm->crm_ci_redirect($crm_ci_page);
					?>
					<tr class="body_tr jalign_left">					
						<td><a href="<?php echo $page_url; ?>"><?php echo $page_name; ?></a></td>	
					</tr>						
						
				</table>
				
				
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
	
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/notes-button.png">
							Sales Reports
						</th>
						<?php
						// page id
						$page_id_arr = array(88,60,91,92,93);
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){

							$page_url = $page['page_url'];
							$page_name = $page['page_name']." (OLD)";

							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							)
							{ 
								
								if( $page['page_url'] == 'sales_activity.php' ){
										
									$crm_ci_page = 'reports/sales_activity';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'report_sales_admin.php' ){
										
									$crm_ci_page = 'reports/sales_report';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'sales_snapshot.php' ){
										
									$crm_ci_page = 'reports/sales_snapshot';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'view_target_agencies.php' ){
										
									$crm_ci_page = 'agency/view_target_agencies';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}else if( $page['page_url'] == 'view_all_agencies.php' ){
										
									$crm_ci_page = 'agency/view_all_agencies';
									$page_url = $crm->crm_ci_redirect($crm_ci_page);
									$page_name = $page['page_name'];
									
								}
								
								?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page_url; ?>"><?php echo $page_name; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>		
					</tr>
						
				</table>
				
				
			</div>


		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	// hide empty script
	jQuery(".reports_tbl").each(function(){
		
		var obj = jQuery(this);
		var num_pages = obj.find(".body_tr").length;
		
		console.log("num_pages: "+num_pages);
		if( num_pages <= 0 ){
			obj.hide();
		}
		
	});
	
	
	
});
</script>
</body>
</html>