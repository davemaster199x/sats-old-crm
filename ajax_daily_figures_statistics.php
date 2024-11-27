<?php
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$country_id = $_SESSION['country_default'];
$date = date('Y-m-d');
$ic_service = getICService();
$ic_service_imp = implode(',',$ic_service);

$from = mysql_real_escape_string($_POST['from']);
$to = mysql_real_escape_string($_POST['to']);
$mtd_sales = mysql_real_escape_string($_POST['mtd_sales']);
$df_budget = mysql_real_escape_string($_POST['df_budget']);
$df_working_days = mysql_real_escape_string($_POST['df_working_days']);

function getIcUpgradeTotal($params){
	
	$ic_service = $params['ic_service'];
	$ic_service_imp = implode(',',$ic_service);
	
	$filter = '';
	if($params['date_range']!=''){
		$filter .= " AND j.`date` BETWEEN '{$params['date_range']['from']}' AND '{$params['date_range']['to']}' ";
	}else{
		$filter .= " AND j.`date` = '".date('Y-m-d')."' ";
	}
	
	$sql_str = "
		SELECT j.`job_price`, SUM(al.`alarm_price`) AS alarm_tot, am.`price` AS am_price
		FROM `jobs` AS j 
		INNER JOIN `alarm` AS al ON  j.`id` = al.`job_id`
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `agency_maintenance` AS am ON ( a.`agency_id` = am.`agency_id` AND am.`surcharge` = 1 )
		WHERE al.`new` = 1
		{$filter}
		AND ( j.`status` = 'Completed' OR j.`status` = 'Merged Certificates' )		
		AND a.`country_id` = {$params['country_id']}
		AND j.`job_type` = 'IC Upgrade'
	";
	
	return mysql_query($sql_str);
	
}


function getTotalPaymentsAndCredits(){

	$sql = "
	SELECT SUM(j.`invoice_payments`) AS inv_pay_tot, SUM(j.`invoice_credits`) AS inv_cred_tot
	FROM `jobs` AS j 
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
	WHERE j.`id` > 0 
	AND j.`job_price` > 0 
	AND j.`invoice_balance` > 0 
	AND j.`status` = 'Completed' 
	AND ( 
		a.`status` = 'Active' 
		OR a.`status` = 'Deactivated' 
	) 
	AND j.`date` = '".date('Y-m-d')."'
	";
	
	return mysql_query($sql);

}

?>
<table id="table2" border=0 cellspacing=0 class='table-center tbl-fr-red'>
				<?php
				// TODAY
				$dfpd_sql = $crm->getDailyFiguresPerDate($date);
				$dfpd = mysql_fetch_array($dfpd_sql);
				$sales = $dfpd['sales'];
				$techs = $dfpd['techs'];
				$jobs = $dfpd['jobs'];
				$working_day = $dfpd['working_day'];

				// get country
				$cntry_sql = getCountryViaCountryId($country_id);
				$cntry = mysql_fetch_array($cntry_sql);
				?>
				<!--- TODAY --->
				<tr class="noBorderTop">
					<td class="t_header" colspan="2"><strong>Today in <?php echo $cntry['country']; ?></strong></td>
				</tr>
				<tr>
					<td><strong>Income</strong></td>
					<td><strong><?php echo '$'.number_format($sales,2,'.',','); ?></strong></td>
				</tr>
				<tr>
					<td><strong>Average Jobs per Tech</strong></td>
					<td><?php echo round($jobs/$techs); ?></td>
				</tr>
				<tr>
					<td><strong>Average $ per Tech</strong></td>
					<td>$<?php echo number_format(($sales/$techs),2,'.',',');?>	
					</td>
				</tr>
				<tr>
					<td><strong>Total Techs Worked</strong></td>
					<td><?php echo $techs; ?></td>
				</tr>						
				<?php
				// AU ONLY				
				if( $country_id == 1 ){ 
				$custom_filter = "
					AND j.`job_type` = 'IC Upgrade'
					AND ( j.`status` = 'Completed' OR j.`status` = 'Merged Certificates' )
					AND j.`date` = '".date('Y-m-d')."'					
				";	
				?>
					<tr>
						<td><strong>Total Upgrades Completed</strong></td>
						<td>
						<?php
						$params = array(
							'custom_filter' => $custom_filter,
							'country_id' => $country_id,
							'return_count' => 1,
							'display_echo' => 0
						);
						echo number_format($crm->getJobsData($params));	
						?>
						</td>
					</tr>
					<tr>
						<td><strong>Total Upgrade Income</strong></td>
						<td>
						<?php
						$params = array(
							'country_id' => $country_id,
							'ic_service' => $ic_service
						);
						$up_sql = getIcUpgradeTotal($params);
						$up = mysql_fetch_array($up_sql);
						$up_tot = ($up['job_price']+$up['alarm_tot']+$up['am_price']);
						echo '$'.number_format($up_tot,2);
						?>
						</td>
					</tr>
				<?php
				}
				?>
				
				
				
				
				
				
				<!--- THIS MONTH --->
				<tr>
					<td class="t_header" colspan="2"><strong>Month to Date</strong></td>
				</tr>
				<tr>
					<td><strong>Income</strong></td>
					<td><strong>$<?php echo number_format($mtd_sales,2,'.',','); ?></strong></td>
				</tr>
				
				<tr>
					<td><strong>Monthly Target</strong></td>
					<td>$<?php echo number_format($df_budget,2,".",","); ?></td>
				</tr>
				<tr>
					<td><strong>Distance to Target</strong></td>
					<td>
					<?php 
					$dtt = $df_budget-$mtd_sales;
					echo '$'.number_format($dtt,2,".",","); 
					?>
					</td>
				</tr>
				<tr>
					<td><strong>Working Days Left</strong></td>
					<td><?php echo $working_days_left = ($df_working_days-$working_day); ?></td>
				</tr>
				
				
				<?php
				// AU ONLY
				if( $country_id == 1 ){ 
				$custom_filter = "
					AND j.`job_type` = 'IC Upgrade'
					AND ( j.`status` = 'Completed' OR j.`status` = 'Merged Certificates' )					
				";
				?>
					<tr>
						<td><strong>Total Upgrades Completed</strong></td>
						<td>
						<?php
						// sum age
						$params = array(
							'custom_filter' => $custom_filter,							
							'return_count' => 1,
							'country_id' => $country_id,							
							'display_echo' => 0,
							'date_range' => array(
								'from' => date('Y-m-01'),
								'to' => $to
							)
						);
						echo $crm->getJobsData($params);
						?>
						</td>
					</tr>
					<tr>
						<td><strong>Total Upgrade Income</strong></td>
						<td>
						<?php
						$params =  array(
							'country_id' => $country_id,
							'ic_service' => $ic_service,
							'date_range' => array(
								'from' => date('Y-m-01'),
								'to' => $to
							)
						);
						$up_sql = getIcUpgradeTotal($params);
						$up = mysql_fetch_array($up_sql);
						$up_tot = ($up['job_price']+$up['alarm_tot']+$up['am_price']);
						echo '$'.number_format($up_tot,2);
						?>
						</td>
					</tr>
				<?php
				}
				?>
				
				
				
				<tr>
					<td><strong>Daily AVG Required</strong></td>
					<td><strong>$<?php echo number_format(($dtt/$working_days_left),2,".",","); ?></strong></td>
				</tr>
				<tr>
					<td><strong>Days Worked</strong></td>
					<td><?php echo $working_day; ?></td>
				</tr>
				<tr>
					<td><strong>Daily Average</strong></td>
					<td>$<?php echo number_format(($mtd_sales/$working_day),2,".",","); ?></td>
				</tr>
				<tr>
					<td><strong>Booked Jobs until EOM</strong></td>
					<td>
					<?php
					// sum age
					$params = array(
						'booked' => 1,
						'date_range' => array(
							'from' => date('Y-m-d'),
							'to' => $to
						),
						'return_count' => 1,
						'country_id' => $country_id
					);
					echo $booked = $crm->getJobsData($params);	
					?>
					</td>
				</tr>
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
				?>
				<tr>
					<td><strong>Average Age (Completed)</strong></td>
					<td>
					<?php
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
				
				
				
				
				
				
				
				<!--- STATISTICS --->
				<tr>
					<td class="t_header" colspan="2"><strong>Statistics</strong></td>
				</tr>
				
				<tr>
					<td><strong>Total Properties</strong></td>
					<td><?php echo number_format(mysql_num_rows($crm->kpi_getTotalPropertyCount($country_id))); ?></td>
				</tr>
				<?php
				// exclude this status
				$custom_filter = "
					AND (
						j.`status` != 'On Hold' AND
						j.`status` != 'Pending' AND
						j.`status` != 'Completed' AND
						j.`status` != 'Cancelled' AND
						j.`status` != 'Booked'
					)
				";
				?>
				<tr>
					<td><strong>Outstanding Jobs</strong></td>
					<td>
					<?php
					$params = array(
						'custom_filter' => $custom_filter,
						'country_id' => $country_id,
						'return_count' => 1,
						'display_echo' => 0
					);
					echo number_format($crm->getJobsData($params));	
					?>
					</td>
				</tr>
				<tr>
					<td><strong>Outstanding Value</strong></td>
					<td>
					<?php
					$params = array(
						'custom_filter' => $custom_filter,
						'country_id' => $country_id,
						'sum_job_price' => 1
					);
					$jp_sql = $crm->getJobsData($params);
					$jp = mysql_fetch_array($jp_sql);
					$job_price = $jp['j_price'];
					echo '$'.number_format($job_price, 2);	
					?>
					</td>
				</tr>
				
				<?php
				// get sum age and job count
				$custom_select = "
					SUM( DATEDIFF( '".date('Y-m-d')."', CAST( j.`created` AS DATE ) ) ) AS sum_age, 
					COUNT(j.`id`) AS jcount 
				";
				?>
				<tr>
					<td><strong>Average Age (Not Completed)</strong></td>
					<td>
					<?php
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

				<!--- Accounts --->
				<tr>
					<td class="t_header" colspan="2"><strong>Accounts</strong></td>
				</tr>
				<?php
				$acc_sql = getTotalPaymentsAndCredits();
				$acc = mysql_fetch_array($acc_sql);
				$tot_payments = $acc['inv_pay_tot'];
				$tot_credits = $acc['inv_cred_tot'];
				?>
				<tr>
					<td><strong>Total Payments</strong></td>
					<td>$<?php echo number_format($tot_payments,2); ?></td>
				</tr>
				<tr>
					<td><strong>Total Credits</strong></td>
					<td>$<?php echo number_format($tot_credits,2); ?></td>
				</tr>
			</table>  