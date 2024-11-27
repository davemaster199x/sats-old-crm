<?

$title = "Process Pending Jobs";
$onload = 1;
$onload_txt = "zxcSelectSort('agency',1)";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

?>

  <div id="mainContent">
  
  <div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Process Pending Jobs" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Process Pending Jobs</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
    <table>
    <tr><td>
		<?php	
		
		
			$ren_sql = mysql_query("
				SELECT * 
				FROM  `renewals` 
				WHERE CAST(  `date` AS DATE ) =  '".date("Y-m-d")."'
				AND  `country_id` ={$_SESSION['country_default']}
			");

			// renewals has not yet been run this day
			if(mysql_num_rows($ren_sql)==0){
				
				
				$ym_tot = 0;
			
				$last_year = date("Y",strtotime("-1 year"));	
				$next_month = date("m",strtotime("+1 month"));	
				$max_day = date("t",strtotime("{$last_year}-{$next_month}"));
				
				$this_year = date("Y");
				$this_month = date("m");
				$date_str = "";
				
				// if december
				if(intval($this_month)==12){
					$this_month_max_day = date("t",strtotime("{$this_year}-01"));
					$date_str = " AND j.`date` BETWEEN '{$this_year}-01-01' AND '{$this_year}-01-{$this_month_max_day}'";
				}else{
					$date_str = " AND j.`date` BETWEEN '{$last_year}-{$next_month}-01' AND '{$last_year}-{$next_month}-{$max_day}'";
				}
				
				// get jobs
				$fp_sql = mysql_query("				
					SELECT j.`property_id`, j.`job_price`, j.`service`, ps.`price` AS ps_price, p.`agency_id`
					FROM `jobs` AS j
					LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
					LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
					INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id` AND j.`service` = ps.`alarm_job_type_id` )
					WHERE j.`status` = 'Completed'
					AND j.`job_type` = 'Yearly Maintenance'
					AND ps.`service` =1
					AND p.`deleted` =0
					AND a.`status` = 'active'
					AND j.`del_job` = 0
					AND a.`country_id` = {$_SESSION['country_default']}
					{$date_str}
				");
				
				$next_month_full = date("Y-m-01 H:i:s",strtotime("+1 month"));
				
				
				while($fp = mysql_fetch_array($fp_sql)){
					
					$agency_id = $fp['agency_id'];
					
					// get Franchise Group
					$agen_sql = mysql_query("
						SELECT `franchise_groups_id`
						FROM `agency`
						WHERE `agency_id` = {$agency_id}
					");
					$agen = mysql_fetch_array($agen_sql);

					// if agency is DHA agencies with franchise group = 14(Defence Housing) OR if agency has maintenance program
					$dha_need_processing = 0;
					if( isDHAagenciesV2($agen['franchise_groups_id'])==true || agencyHasMaintenanceProgram($agency_id)==true ){
						$dha_need_processing = 1;
					}
					
					
					// insert jobs
					mysql_query("
						INSERT INTO 
						`jobs` (
							`status`, 
							`retest_interval`, 
							`auto_renew`, 
							`job_type`, 
							`property_id`, 
							`sort_order`, 
							`job_price`, 
							`service`,
							`created`,
							`start_date`,
							`dha_need_processing`
						)
						VALUE(
							'Pending', 
							365, 
							1, 
							'Yearly Maintenance', 
							{$fp['property_id']}, 
							1, 
							{$fp['ps_price']}, 
							{$fp['service']},
							'{$next_month_full}',
							'{$next_month_full}',
							'{$dha_need_processing}'
						)
					");
					
					$job_id = mysql_insert_id();
					
					// AUTO - UPDATE INVOICE DETAILS
					$crm->updateInvoiceDetails($job_id);
					
					$alarm_job_type_id = $fp['service'];
					
					// get alarm job type
					$ajt_sql = mysql_query("
						SELECT *
						FROM `alarm_job_type`
						WHERE `id` = {$alarm_job_type_id}
					");
					$ajt = mysql_fetch_array($ajt_sql);


					// if bundle
					if($ajt['bundle']==1){
						$b_ids = explode(",",trim($ajt['bundle_ids']));
						// insert bundles
						foreach($b_ids as $val){
							mysql_query("
								INSERT INTO
								`bundle_services`(
									`job_id`,
									`alarm_job_type_id`
								)
								VALUES(
									{$job_id},
									{$val}
								)
							");
							
							
							$bundle_id = mysql_insert_id();
							$bs_id = $bundle_id;
							$bs2_sql = getbundleServices($job_id,$bs_id);
							$bs2 = mysql_fetch_array($bs2_sql);
							$ajt_id = $bs2['alarm_job_type_id'];
							
							//echo "Job ID: {$job_id} - ajt ID: {$alarm_job_type_id} Bundle ID: {$bundle_id} <br />";
							
							// sync alarm
							runSync($job_id,$ajt_id,$bundle_id);

						}	
					}else{
						runSync($job_id,$alarm_job_type_id);
					}
					
					
					// insert job logs
					mysql_query("
						INSERT INTO 
						`job_log` (
							`contact_type`,
							`eventdate`,
							`comments`,
							`job_id`, 
							`staff_id`,
							`eventtime`
						) 
						VALUES (
							'Service Due',
							'" . date('Y-m-d') . "',
							'Service Due Job Created', 
							'{$job_id}',
							'{$staff_id}',
							'".date("H:i")."'
						)
					");
					
					
				}
				
				
				
				$ym_tot = mysql_num_rows($fp_sql);
				
				echo "<div class='success'>
					{$ym_tot} Jobs Successfully Created <br />
					If there were 0 jobs added, Then the script has already been run this Month
				</div>";
				
				// insert renewal record
				mysql_query("
					INSERT INTO 
					`renewals`(
						`StaffID`,
						`country_id`,
						`date`,
						`num_jobs_created`
					)
					VALUES(
						'{$staff_id}',
						{$_SESSION['country_default']},
						'".date("Y-m-d H:i:s")."',
						{$ym_tot}
					)
				");
				
				
				
			}else{
				
				echo "<div class='success'>
					Renewals has already been run
				</div>";
				
			}
		
		
			
		  	
		  	
		  	// close the database connection
		  	
		?>
	</td></tr>
	</table>
    
  </div>
  
</div>

<br class="clearfloat" />

</body>
</html>