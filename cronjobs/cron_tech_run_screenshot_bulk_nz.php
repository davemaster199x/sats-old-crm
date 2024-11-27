<?php

include('server_hardcoded_values.php');

# add trailing slash
if ($_SERVER['DOCUMENT_ROOT'][strlen($_SERVER['DOCUMENT_ROOT'])-1] != "/") $_SERVER['DOCUMENT_ROOT'] .= "/";

# include files
include($_SERVER['DOCUMENT_ROOT'] . 'inc/config.php');
include($_SERVER['DOCUMENT_ROOT'] . 'inc/encryption.class.php');
include($_SERVER['DOCUMENT_ROOT'] . 'inc/functions.php');
//include($_SERVER['DOCUMENT_ROOT'] . 'inc/email_functions.php');
//include($_SERVER['DOCUMENT_ROOT'] . 'inc/swiftmailer/lib/swift_required.php');
include($_SERVER['DOCUMENT_ROOT'] . 'inc/sats_crm_class.php');

$crm = new Sats_Crm_Class;

# Connect to Database
$connection = mysql_connect(HOST, USERNAME, PASSWORD) or die("Could not connect to database:" . mysql_error());
mysql_select_db(DATABASE, $connection) or die("Unable to Select database");

// CHANGE per country
$country_id = 2;
$cc = KEYS_EMAIL;

define("IS_CRON", 1);
define("CRON_TYPE_ID", 8);
define("CURR_WEEK", intval(date('W')));
define("CURR_YEAR", date('Y'));

$cl_sql = mysql_query("
	SELECT * 
	FROM `cron_log` 
	WHERE `type_id` = '".CRON_TYPE_ID."' 
	AND CAST( `started` AS DATE ) = '".date('Y-m-d')."'
	AND `country_id` = {$country_id}
");

if(mysql_num_rows($cl_sql)==0){

	$crm = new Sats_Crm_Class;

	// tommorow
	$tomorrow = date('Y-m-d',strtotime('+1 day'));
	
	$day_txt = date('D');
	$date_filt = '';
	
	if( $day_txt == 'Fri' ){
		// get saturday and monday 
		$date_filt = " ( date = '{$tomorrow}' OR date = '" . date('Y-m-d', (strtotime('+3 days'))) . "' ) ";
	}else{
		$date_filt = " date = '{$tomorrow}' ";
	}

	$tr_str = "
		SELECT * 
		FROM  `tech_run` 
		WHERE  {$date_filt}
		AND `country_id` = {$country_id}
	";
	$tr_sql = mysql_query($tr_str);


	if( mysql_num_rows($tr_sql)>0 ){
		
		while( $tr = mysql_fetch_array($tr_sql)  ){
		
			$tr_id = $tr['tech_run_id'];

			$cntry_sql = getCountryViaCountryId($country_id);
			$cntry = mysql_fetch_array($cntry_sql);
			$country_name = $cntry['country'];
			$country_iso_uc = strtoupper($cntry['iso']);
			$country_iso_lw = strtolower($cntry['iso']);

			$hasTechRun = true;

			$tech_id = $tr['assigned_tech'];
			$day = date("d",strtotime($tr['date']));
			$month = date("m",strtotime($tr['date']));
			$year = date("Y",strtotime($tr['date']));
			$date = $tr['date'];
			$sub_regions = $tr['sub_regions'];


			//get tech name
			$t_sql = mysql_query("
				SELECT *
				FROM `staff_accounts`
				WHERE `StaffID` = {$tech_id}
			");
			$t = mysql_fetch_array($t_sql);
			$isElectrician = ( $t['is_electrician']==1 )?true:false;
			$tech_name = "{$t['FirstName']} {$t['LastName']}";
			$tech_email = $t['Email'];

			$title = $tech_name;
			
			// table border
			$css_border = 'border: 1px solid #cccccc;';
			
			ob_start();
			?>
			<style>
			#tbl_tr td{
				border: 1px solid #cccccc;
			}
			</style>
			<table border=0 cellspacing=0 cellpadding=5 width=100% id="tbl_tr"  class="table-left tbl-fr-red" style="margin-bottom: 12px;">
				<tr bgcolor="#b4151b" class="nodrop nodrag" style="color:white;background-color:#000000;">
				<?php
				if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
					<th><b>#</b></th>
				<?php	
				}
				?>
				<th><b>Status</b></th>
				<?php
				if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
					<th><b>Age</b></th>
				<?php	
				}
				?>
				<?php
				if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
					<th><b>Details</b></th>
				<?php	
				}
				?>
				<th><b>Type</b></th>
				<th><b>Address</b></th>
				<th><b>Key #</b></th>
				<th><b>Notes</b></th>
				<th><b>Time</b></th>
				<th><b>Agent</b></th>
				<th><b>Agent Address</b></th>		
				</tr>


				<?php

					
					
					// start
					$start_acco_sql = mysql_query("
						SELECT *
						FROM `accomodation`
						WHERE `accomodation_id` = {$tr['start']}
						AND `country_id` = {$country_id}
					");
					$start_acco = mysql_fetch_array($start_acco_sql);
					$start_agency_name = $start_acco['name'];
					$start_agency_address = $start_acco['address'];
				?>


				<tr class="nodrop nodrag">
				<?php
				if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
					<td  style="<?php echo $css_border; ?>">1</td>
				<?php	
				}
				?>
				<td  style="<?php echo $css_border; ?>"><?php echo $start_agency_name; ?></td>
				<?php
				if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
					<td  style="<?php echo $css_border; ?>">&nbsp;</td>
				<?php	
				}
				?>
				<td  style="<?php echo $css_border; ?>">&nbsp;</td>
				<td  style="<?php echo $css_border; ?>">&nbsp;</td>
				<td  style="<?php echo $css_border; ?>"><?php echo $start_agency_address; ?></td>
				<td  style="<?php echo $css_border; ?>">&nbsp;</td>
				<td  style="<?php echo $css_border; ?>">&nbsp;</td>
				<td  style="<?php echo $css_border; ?>">&nbsp;</td>
				<td  style="<?php echo $css_border; ?>">&nbsp;</td>
				<td  style="<?php echo $css_border; ?>">&nbsp;</td>
				</tr>


				<?php

					
				$job_ctr = 0;

				$params = array('display_only_booked'=>1);
				$jr_list2 = getTechRunRows($tr_id,$country_id,$params);
				$j = 2;
				$comp_count = 0;
				$jobs_count = 0;
				while( $row = mysql_fetch_array($jr_list2) ){ 

					

						// JOBS
						if( $row['row_id_type'] == 'job_id' ){
							
							$jr_sql = getJobRowData($row['row_id'],$country_id);
							$row2 = mysql_fetch_array($jr_sql);
							
							// only show 240v rebook to electrician 
							if( $row2['job_type']=='240v Rebook' && $isElectrician==false  ){
								$showRow = 0;
							}else{
								$showRow = 1;
							}
							
							if( $row2['jdate']=="" || $row2['jdate']=="0000-00-00" || $row2['jdate']==$date ){
								
							if( $showRow==1 ){
								
							$jobs_count++;
							
							$bgcolor = "#FFFFFF";
							if($row2['job_reason_id']>0){
								$bgcolor = "#fffca3";
							}else  if($row2['ts_completed']==1){
								$bgcolor = "#c2ffa7";
								$comp_count++;
							}
							
							
							
							
							$j_created = date("Y-m-d",strtotime($row2['created']));
							$last_60_days = date("Y-m-d",strtotime("-60 days"));
						
							
							if( $row2['job_type']=='Lease Renewal' || $row2['job_type']=='Change of Tenancy' || $row2['j_status']=='DHA' || $row2['job_type']=='Fix or Replace' ){
								//$bgcolor = '#ffe5e5';
							}
							
							if($row2['j_status']=='Booked'){
								//$bgcolor = "#eeeeee";
							}
							
							if( $row['dnd_sorted']==0 ){
								$bgcolor = '#FFFF00';
							}
							
							$serv_color = getServiceColor($row2['j_service']);

							
						?>
							<tr id="<?php echo $row['tech_run_rows_id']; ?>" style="background-color:<?php //echo $bgcolor; ?>">
								<?php
								if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
									<td  style="<?php echo $css_border; ?>"><?php echo $j; ?></td>
								<?php	
								}
								?>
								
								
								<?php
								if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
									<td class="jstatus">
											<?php echo $row2['j_status'].'[ts]'; ?>								
									</td>
								<?php	
								}else{ ?>
									<td class="jstatus">
										<?php echo $row2['j_status']; ?>
									</td>
								<?php	
								}
								?>
								
								
								<?php
								if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
									<td  style="<?php echo $css_border; ?>">
									<?php
									// Age
									$date1=date_create($j_created);
									$date2=date_create(date('Y-m-d'));
									$diff=date_diff($date1,$date2);
									$age = $diff->format("%r%a");
									echo (((int)$age)!=0)?$age:0; 
									?>
									</td>
									
									<td  style="<?php echo $css_border; ?>">
									<?php
									
									// old job
									//echo (($j_created<$last_60_days)?'<img src="https://'.$_SERVER['HTTP_HOST'].'/images/hourglass.png" class="jicon" style="margin-right: 7px; cursor:pointer;" title="Old job" />':'');
									
									// if first visit
									// WATER METER
									if($row2['j_service']==7){
										$ha_sql = mysql_query("
											SELECT * 
											FROM  `water_meter` 
											WHERE  `job_id` ={$row2['jid']}
											AND  `meter_image` !=  ''
										");
									}else{
										$ha_sql = mysql_query("
											SELECT *
											FROM `alarm`
											WHERE `job_id` ={$row2['jid']}
										");
									}
									
									if( mysql_num_rows($ha_sql)==0  ){
										$fv = '<img src="https://'.$_SERVER['HTTP_HOST'].'/images/first_icon.png" class="jicon" style="width: 25px; margin-right: 7px; cursor:pointer;" title="First visit" />';
									}else{
										$fv = '';
									}
									
									echo $fv;
									
									
									//  if job type = COT, LR, FR, 240v or if marked Urgent
									if( 
										$row2['job_type'] == "Change of Tenancy" || 
										$row2['job_type'] == "Lease Renewal" || 
										$row2['job_type'] == "Fix or Replace" || 
										$row2['job_type'] == "240v Rebook" || 
										$row2['urgent_job'] == 1 
									){
										echo '<img src="https://'.$_SERVER['HTTP_HOST'].'/images/caution.png" class="jicon" style="height: 25px; cursor:pointer;" title="Priority Job" />';
									}
									
									if( $row2['key_access_required'] == 1 && $row2['j_status']=='Booked' ){
										echo '<img src="https://'.$_SERVER['HTTP_HOST'].'/images/key_icon_green.png" style="height: 25px;" title="Key Access Required" />';
									}
									
									$chk_logs_str = "
										SELECT *
										FROM job_log j 
										LEFT JOIN staff_accounts s ON s.StaffID = j.staff_id
										WHERE j.`job_id` = {$row2['jid']}
										AND j.`deleted` = 0 
										AND j.`eventdate` = '".date('Y-m-d')."'
										AND j.`contact_type` = 'Phone Call'
									";
									$chk_logs_sql = mysql_query($chk_logs_str);
									$chk_log = mysql_fetch_array($chk_logs_sql);
									
									$current_time = date("Y-m-d H:i:s");
									$job_log_time = date("Y-m-d H:i",strtotime("{$chk_log['eventdate']} {$chk_log['eventtime']}:00"));
									$last4hours = date("Y-m-d H:i",strtotime("-4 hours"));
									//echo "Current time: {$current_time }<br />Log Time: {$job_log_time}<br /> last 4 hours: ".$last4hours;
									
									if( 
										$row2['j_status']=='To Be Booked' 
										&& mysql_num_rows($chk_logs_sql)>0 
										&& ( $job_log_time >= $last4hours && $job_log_time <= $current_time )
									){
										echo '<img src="https://'.$_SERVER['HTTP_HOST'].'/images/green_phone.png" style="height: 25px" title="Phone Call" />';
									}
									
									?>
									</td>
									
								<?php	
								}
								?>
								
								
								
								
								<td  style="<?php echo $css_border; ?>">
								<?php
								// job type
								switch($row2['job_type']){
									case 'Once-off':
										$jt = 'Once-off';
									break;
									case 'Change of Tenancy':
										$jt = 'COT';
									break;
									case 'Yearly Maintenance':
										$jt = 'YM';
									break;
									case 'Fix or Replace':
										$jt = 'FR';
									break;
									case '240v Rebook':
										$jt = '240v';
									break;
									case 'Lease Renewal':
										$jt = 'LR';
									break;
								}
								?>
								<?php echo $jt; ?>
								</td>
								
								<?php
								$paddress =  $row2['p_address_1']." ".$row2['p_address_2'].", ".$row2['p_address_3'];
								if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
									<td  style="<?php echo $css_border; ?>"><?php echo $paddress; ?></td>						
								<?php				
								}else{ ?>
									<td  style="<?php echo $css_border; ?>"><?php echo $paddress; ?></td>						
								<?php	
								}
								?>
								
								
								<td  style="<?php echo $css_border; ?>"><?php echo $row2['key_number']; ?>
								<?php
								// if job is entry notice, show pdf link
								if( $row2['job_entry_notice']==1 ){ 
								?>
									<img src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/images/pdf.png" /></td>
								<?php	
								}
								?>
								
								
								<td  style="<?php echo $css_border; ?>"><?php echo $row2['tech_notes']; ?></td>
								<td  style="<?php echo $css_border; ?>"><?php echo $row2['time_of_day']; ?></td>	
								
								<?php 
								if( $_SESSION['USER_DETAILS']['ClassName'] != 'TECHNICIAN' ){ ?>
								
									<td  style="<?php echo $css_border; ?>">
										
											<?php echo $row2['agency_name']; ?>
										
										<br /><?php echo $row2['a_phone']; ?>
									</td>
								
								<?php	
								}else{ ?>
									<td  style="<?php echo $css_border; ?>">
										<?php echo $row2['agency_name']; ?>
										<br /><?php echo $row2['a_phone']; ?>
									</td>
								<?php	
								}
								?>
								
								
								<td  style="<?php echo $css_border; ?>">
									<?php echo "{$row2['a_address_1']} {$row2['a_address_2']}<br />{$row2['a_address_3']} {$row2['a_postcode']}"; ?>
								</td>
								
							
							</tr>
						<?php	
							// store it on property address array
							$prop_address[$i]['address'] = "{$row2['p_address_1']} {$row2['p_address_2']} {$row2['p_address_3']} {$row2['p_state']} {$row2['p_postcode']}, {$country_name}";
							$prop_address[$i]['status'] = $row2['j_status'];
							$prop_address[$i]['created'] = date("Y-m-d",strtotime($row2['created']));
							$prop_address[$i]['urgent_job'] = $row2['urgent_job'];
							$prop_address[$i]['lat'] = $row2['p_lat'];
							$prop_address[$i]['lng'] = $row2['p_lng'];
							$i++;
							
							$j++;
							
							}
							
							}
								
							}else if( $row['row_id_type'] == 'keys_id' ){ 
							
								// KEYS
								$k_sql = getTechRunKeys($row['row_id'],$country_id);
								$kr = mysql_fetch_array($k_sql);
								
						
								

								?>
									<tr id="<?php echo $row['tech_run_rows_id']; ?>" style="background-color:<?php echo ($kr['completed']==1)?'#c2ffa7':'#ffffff'; ?>;">
										
										<?php
										if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
											<td  style="<?php echo $css_border; ?>"><?php echo $j; ?></td>
										<?php	
										}
										?>
										<td  style="<?php echo $css_border; ?>">
											<?php 
												if($kr['completed']==1){
													$kr_act = explode(" ",$kr['action']);
													$temp2 = ($kr['action']=="Drop Off")?'p':'';
													$temp = "{$kr_act[0]}{$temp2}ed";
													$action = "{$temp} {$kr_act[1]}";
												}else{
													$action = $kr['action'];
												}
												echo $action;
											?>
										</td>
										<?php
										if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
											<td  style="<?php echo $css_border; ?>">&nbsp;</td>
											<td  style="<?php echo $css_border; ?>">&nbsp;</td>
										<?php	
										}
										?>
										<td  style="<?php echo $css_border; ?>"><img src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/images/key_icon_green.png" /></td>						
										<td  style="<?php echo $css_border; ?>"><?php echo "{$kr['address_1']} {$kr['address_2']}, {$kr['address_3']}"; ?><?php echo ($kr['agency_id']==4102)?'(IMPORTANT - Read Agency Notes)':''; ?></td>
										<td  style="<?php echo $css_border; ?>">&nbsp;</td>
										<td  style="<?php echo $css_border; ?>">&nbsp;</td>
										<td  style="<?php echo $css_border; ?>"><?php echo $kr['agency_hours']; ?></td>						
										<td  style="<?php echo $css_border; ?>"><?php echo $kr['agency_name']; ?><br /><?php echo $kr['phone']; ?></td>
										<td  style="<?php echo $css_border; ?>"><?php echo "{$kr['address_1']} {$kr['address_2']}<br/>{$kr['address_3']} {$kr['state']} {$kr['postcode']}"; ?></td>											
									</tr>
								<?php
								// get gecode
								$prop_address[$i]['address'] = "{$kr['address_1']} {$kr['address_2']} {$kr['address_3']} {$kr['state']} {$kr['postcode']}, {$country_name}";
								$prop_address[$i]['is_keys'] = 1;
								$prop_address[$i]['lat'] = $kr['lat'];
								$prop_address[$i]['lng'] = $kr['lng'];
								$i++;
								
								$j++;
								
							}else if( $row['row_id_type'] == 'supplier_id' ){ 
							
								// supplier
								$sup_sql = getTechRunSuppliers($row['row_id']);
								$sup = mysql_fetch_array($sup_sql);
								
								if($sup['on_map']==1){
								

								?>
									<tr id="<?php echo $row['tech_run_rows_id']; ?>" style="background-color:<?php echo ($kr['completed']==1)?'#c2ffa7':'#ffffff'; ?>;">
										
										<?php
										if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
											<td  style="<?php echo $css_border; ?>"><?php echo $j; ?></td>
										<?php	
										}
										?>
										<td  style="<?php echo $css_border; ?>">Supplier</td>
										<?php
										if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
											<td  style="<?php echo $css_border; ?>">&nbsp;</td>
											<td  style="<?php echo $css_border; ?>">&nbsp;</td>
										<?php	
										}
										?>
										<td  style="<?php echo $css_border; ?>">&nbsp;</td>						
										<td  style="<?php echo $css_border; ?>"><?php echo $sup['sup_address']; ?></td>
										<td  style="<?php echo $css_border; ?>">&nbsp;</td>
										<td  style="<?php echo $css_border; ?>">&nbsp;</td>
										<td  style="<?php echo $css_border; ?>">&nbsp;</td>						
										<td  style="<?php echo $css_border; ?>"><?php echo $sup['company_name']; ?><br /><?php echo $sup['phone']; ?></td>
										<td  style="<?php echo $css_border; ?>"><?php echo  $sup['sup_address']; ?></td>													
									</tr>
								<?php
								// get gecode
								$prop_address[$i]['address'] = $sup['sup_address'];
								$prop_address[$i]['is_keys'] = 1;
								$prop_address[$i]['lat'] = $sup['lat'];
								$prop_address[$i]['lng'] = $sup['lng'];
								$i++;
								
								$j++;
								
								}
								
							}
						
					
						
					
						
						

				}

				?>
					
					
				<?php

				$end_acco_sql = mysql_query("
					SELECT *
					FROM `accomodation`
					WHERE `accomodation_id` = {$tr['end']}
					AND `country_id` = {$country_id}
				");
				$end_acco = mysql_fetch_array($end_acco_sql);
				$end_agency_name = $end_acco['name'];
				$end_agency_address = $end_acco['address'];

				?>
				<tr class="nodrop nodrag">
				<?php
				if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
					<td  style="<?php echo $css_border; ?>">
					<?php
					$end_point_index = $j; 

					echo $end_point_index;
					?>
					</td>
				<?php
				}
				?>
				<td  style="<?php echo $css_border; ?>"><?php echo $end_agency_name; ?></td>
				<?php
				if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
					<td  style="<?php echo $css_border; ?>">&nbsp;</td>
				<?php	
				}
				?>
				<td  style="<?php echo $css_border; ?>">&nbsp;</td>
				<td  style="<?php echo $css_border; ?>">&nbsp;</td>
				<td  style="<?php echo $css_border; ?>"><?php echo $end_agency_address; ?></td>
				<td  style="<?php echo $css_border; ?>">&nbsp;</td>
				<td  style="<?php echo $css_border; ?>">&nbsp;</td>
				<td  style="<?php echo $css_border; ?>">&nbsp;</td>
				<td  style="<?php echo $css_border; ?>">&nbsp;</td>
				<td  style="<?php echo $css_border; ?>">&nbsp;</td>
				</tr>

				</table>
			
			
			<?php
			
			
			$message = ob_get_clean();
			
			//echo $message;
			
			
			// email starts here
			//$to = 'vaultdweller123@gmail.com';
			$to = $tech_email;
			$from = "Smoke Alarm Testing Services <{$cntry['outgoing_email']}>";
			//$cc = 'danielk@sats.com.au';		
			$subject = "Tech Run({$country_iso_uc}) - {$tech_name} - ".date('d/m/Y',strtotime($date));


			$params = array(
				'to' => $to,
				'from' => $from,
				'subject' => $subject,
				'message' => $message,
				'cc' => $cc
			);
			
			if( filter_var($to, FILTER_VALIDATE_EMAIL) ){
				$crm->nativeEmail($params);
			}	
		
		}
		
		
	}else{
		echo 'Cron finished executing';
		//die();
	}  

	mysql_query("INSERT INTO cron_log (`type_id`, `week_no`, `year`, `started`, `finished`, `country_id`) VALUES (" . CRON_TYPE_ID . "," . CURR_WEEK . ", " . CURR_YEAR . ", NOW(), NOW(), {$country_id})");

}

?>