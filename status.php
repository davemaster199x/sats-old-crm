<?

$title = "Status";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

function get_job_count($status,$job_type="",$letter_sent="",$lease_renewal=""){
		$lease_renewal = ($lease_renewal!=="")?" OR  j.job_type = '{$lease_renewal}'":"";
		$job_type = ($job_type!=="")?" AND ( j.job_type = '{$job_type}' {$lease_renewal} )":"";
		$letter_sent = ($letter_sent!=="")?" AND j.`letter_sent` ={$letter_sent}":"";
		
		$sql = "
			SELECT `id`
			FROM jobs AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE j.status = '{$status}'
			AND p.deleted = '0'
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			AND a.`country_id` = {$_SESSION['country_default']}
			{$job_type}
			{$letter_sent}
			GROUP BY j.id		
		";
		return mysql_query($sql);
	}
	

   $to_be_booked = "
   	<a href='/to_be_booked_jobs.php'>
   	 <i class='user-sprites icon-tobooked'>&nbsp;</i>
	 <div class='status'>".mysql_num_rows(get_job_count('To Be Booked'))."</div>
	 <div class='head-info visibletext allhd'>To Be Booked</div>	
	 <div class='head-info hiddentext allhd'>2 Be Booked</div>
	</a>
	";
   $renewals = "
   	<a href='/service_due_jobs.php'>
   	 <i class='user-sprites icon-renewal'>&nbsp;</i>
	 <div class='status'>".mysql_num_rows(get_job_count('Pending'))."</div>
	 <div class='head-info'>Renewals <?php echo date('F'); ?></div>	
	</a>
   ";
   $booked = "
   <a href='/booked_jobs.php'>
   	 <i class='user-sprites icon-booked'>&nbsp;</i>
	 <div class='status'>".mysql_num_rows(get_job_count('Booked'))."</div>
	 <div class='head-info'>Booked</div>	
	</a>
	";
   $pre_completed = "
    <a href='/precompleted_jobs.php'>
   	 <i class='user-sprites icon-completed'>&nbsp;</i>
	 <div class='status'>".mysql_num_rows(get_job_count('Pre Completion'))."</div>
	 <div class='head-info visibletext allhd'>Pre Completed</div>	
	 <div class='head-info hiddentext allhd'>Pre Compl</div>
	</a>
   ";
   $send_letters = "
   <a href='send_letter_jobs.php'>
   	 <i class='user-sprites icon-send'>&nbsp;</i>
	 <div class='status'>".mysql_num_rows(get_job_count('Send Letters','',0))."</div>
	 <div class='head-info visibletext allhd'>Send Letters</div>	
	 <div class='head-info hiddentext allhd'>Send Letter</div>	
	</a>
   ";
   $merged = "
   <a href='/merged_jobs.php'>
   	 <i class='user-sprites icon-merged'>&nbsp;</i>
	 <div class='status'>".mysql_num_rows(get_job_count('Merged Certificates'))."</div>
	 <div class='head-info visibletext allhd'>Merged Certificates</div>
	 <div class='head-info hiddentext allhd'>Merged</div>	
	</a>
   ";
   $rebooks_240v = "
   <a href='/to_be_booked_jobs.php?job_type=240v Rebook'>
   	 <i class='user-sprites icon-reebooks'>&nbsp;</i>
	 <div class='status'>".mysql_num_rows(get_job_count('To Be Booked','240v Rebook'))."</div>
	 <div class='head-info visibletext allhd'>240v Rebooks</div>
	 <div class='head-info hiddentext allhd'>240v Rebook</div>	
	</a>
   ";
   $fnr = "
    <a href='/to_be_booked_jobs.php?job_type=Fix or Replace'>
   	 <i class='user-sprites icon-fix'>&nbsp;</i>
	 <div class='status'>".mysql_num_rows(get_job_count('To Be Booked','fix or replace'))."</div>
	 <div class='head-info visibletext allhd'>Fix and Replace</div>	
	 <div class='head-info hiddentext allhd'>F&amp;R- Repair</div>	
	</a>
   ";
   $cot = "
   <a href='/to_be_booked_jobs.php?job_type=".urlencode('cot & lr')."'>
   	 <i class='user-sprites icon-ctltr'>&nbsp;</i>
	 <div class='status'>".mysql_num_rows(get_job_count('To Be Booked','Change of Tenancy','','Lease Renewal'))."</div>
	 <div class='head-info'>COT & LR</div>	
	</a>
	";
	$tbi = "
   <a href='/to_be_invoiced_jobs.php'>
   	 <i class='user-sprites icon-merged'>&nbsp;</i>
	 <div class='status'>".mysql_num_rows(get_job_count('To Be Invoiced'))."</div>
	 <div class='head-info visibletext allhd'>To Be Invoiced</div>
	 <div class='head-info hiddentext allhd'>To Be Invoiced</div>	
	</a>
   ";
	
	// get urgent
	$urg_sql = mysql_query("
		SELECT `id`
			FROM (
			jobs j, property p, agency a
			)
			WHERE a.agency_id = p.agency_id
			AND j.property_id = p.property_id
			AND p.deleted = '0'
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			AND a.`country_id` = {$_SESSION['country_default']}
			AND j.status = 'To Be Booked'
			AND j.`urgent_job` = 1
			GROUP BY j.id		
	");
	
	$urg = "
   <a href='/to_be_booked_jobs.php?is_urgent=1'>
   	 <i class='user-sprites icon-blank'>&nbsp;</i>
	 <div class='status'>".mysql_num_rows($urg_sql)."</div>
	 <div class='head-info'>Urgent</div>	
	</a>
	";
	
   $completed = "
   <a href='/view_jobs.php?status=completed'>
   	 <i class='user-sprites icon-renewal'>&nbsp;</i>
	 <div class='status'>".mysql_num_rows(get_job_count('Completed'))."</div>
	 <div class='head-info'>Renewals <?php echo date('F'); ?></div>	
	</a>
	";
   
   mysql_free_result($result);
   $row[0] = "";
   
   
   // agency total
   $arr = getHomeTotals(); 
   $atotal = "
   	<a href='/view_agencies.php'>
   	 <i class='user-sprites icon-user'>&nbsp;</i>
	 <div class='status'>$arr[2]</div>
	 <div class='head-info'>Agencies</div>	
	</a>
   ";
   
   
   
	
	
	function get_services_total($ajt){
		
		$sql = "
		SELECT count( ps.`property_services_id` ) AS jcount
		FROM `property_services` AS ps
		LEFT JOIN `property` AS p ON p.`property_id` = ps.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE ps.`alarm_job_type_id` ={$ajt}
		AND ps.`service` =1
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND a.`country_id` = {$_SESSION['country_default']}
		";
		
	   $row = mysql_fetch_array(mysql_query($sql));
	   echo $row['jcount'];
	}
	
	
	// action required
	$ar = "
   <a href='/action_required_jobs.php'>
   	 <i class='user-sprites icon-blank'>&nbsp;</i>
	 <div class='status'>".mysql_num_rows(get_job_count('Action Required'))."</div>
	 <div class='head-info'>Action Required</div>	
	</a>
	";
	
	
	// allocate
	$allocate = "
   <a href='/allocate.php'>
   	 <i class='user-sprites icon-blank'>&nbsp;</i>
	 <div class='status'>".mysql_num_rows(get_job_count('Allocate'))."</div>
	 <div class='head-info'>Allocate</div>	
	</a>
	";

?>

<div id="mainContent">
  
  <div class="sats-middle-cont">
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Status" href="/status.php"><strong>Status</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
	
	<?php
	if($_GET['success']==1){
		echo '<div class="success">Import Successful</div>';
	}
	?>

	<style>
	.jsub_div{
		width: 340px;
		float: left;
		margin: 0 4px;
	}
	.jsub_div .sats-block{
		float:none; width:auto;
	}
	.block-color-color_purple{
		background-color: #9b30ff;
	}
	.block-color-sass{
		background-color: #f15a22;
	}
	.block-color-sawm{
		background-color: #00aeef;
	}
	.block-color-allocate{
		background-color: #ec1acc
	}
	</style>
   <div class="sats-main-top-h">
  	
		<div class="sats-main-block">

			<div class="jsub_div">
		
				<?php
				$ajt_sql = mysql_query("
					SELECT *
					FROM `alarm_job_type`
					WHERE `active` = 1
				");
				while($ajt = mysql_fetch_array($ajt_sql)){ 
				
					switch($ajt['id']){
						case 2:
							$color = 'deepred';
							$icon = 'properties';
							$txt = 'fdv';
							$hidtxt = 'fdh';
						break;
						case 5:
							$color = 'sprop';
							$icon = 'swtchprop';
							$txt = 'frdv';
							$hidtxt = 'frdh';						
						break;
						case 6:
							$color = 'lggreen';
							$icon = 'cwprop';	
							$txt = 'sdv';
							$hidtxt = 'sdh';
						break;
						case 7:	
							$color = 'pparty';
							$icon = 'poolprop';
							$txt = 'tdv';
							$hidtxt = 'tdh';
						break;
						case 8:	
							$color = 'sass';
							$icon = 'properties';
							$txt = 'fdv';
							$hidtxt = 'fdh';
						break;
						case 11:	
							$color = 'sawm';
							$icon = 'properties';
							$txt = 'fdv';
							$hidtxt = 'fdh';
						break;
						default:
							$color = 'color_purple';
							$icon = 'properties';
							$txt = 'fdv';
							$hidtxt = 'fdh';
					}
				
				?>
				
				<div class="sats-block block-color-<?php echo $color; ?>">
					<a href='#'>
						<i class='user-sprites icon-<?php echo $icon; ?>'>&nbsp;</i>
						<div class='status'><?php echo get_services_total($ajt['id']); ?></div>
						<div class='head-info visibletext <?php echo $txt; ?>'><?php echo $ajt['type'] ?></div>	
						<div class='head-info hiddentext <?php echo $hidtxt; ?>'><?php echo $ajt['type'] ?></div>	
					</a>
				</div>
				
				<?php if($ajt['id']==2){ 
				
				$dha_sql = mysql_query("
					SELECT COUNT( * ) AS num_serv
					FROM `property_services` AS ps
					LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
					LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
					WHERE a.`franchise_groups_id` =14
					AND p.`deleted` =0
					AND a.`status` = 'active'
					AND ps.`alarm_job_type_id` =2
					AND ps.`service` =1
					AND a.`country_id` = {$_SESSION['country_default']}
				");
				$dha = mysql_fetch_array($dha_sql);
				
				?>
				
				<div class="sats-block block-color-<?php echo $color; ?>" style="background-color: #00ffff;">
					<a href='#'>
						<i class='user-sprites icon-<?php echo $icon; ?>' style="background-image: url('../images/dha_icon.png'); background-position: 0 center;">&nbsp;</i>
						<div class='status'><?php echo $dha['num_serv']; ?></div>
						<div class='head-info visibletext <?php echo $txt; ?>'>DHA Total</div>	
						<div class='head-info hiddentext <?php echo $hidtxt; ?>'>DHA Total</div>	
					</a>
				</div>
					
				<?php } ?>
				
				
				<?php
				}
				?>
    	
		
			
			</div>
			
			<div class="jsub_div">
			
				<div class="sats-block block-color-green">
				<?php echo $to_be_booked; ?>
				</div>
				<div class="sats-block block-color-purple">
					<?php echo $renewals; ?>
				</div>
				<div class="sats-block block-color-grey">
					<?php echo $rebooks_240v; ?>
				</div>
				<div class="sats-block block-color-darkred">
					<?php echo $cot; ?>
				</div>
				<div class="sats-block block-color-deeppurple">
					<?php echo $fnr; ?>
				</div>
				<div class="sats-block block-color-yellow">
					<?php echo $urg; ?>
				</div>
				<div class="sats-block block-color-sprop">
					<?php echo $ar; ?>
				</div>
				<div class="sats-block block-color-allocate">
					<?php echo $allocate; ?>
				</div>
			
				
			</div>
			
			<div class="jsub_div">
			
				<div class="sats-block block-color-brown" style="background-color: #d89df1;">
					<a href='/on_hold_jobs.php'>
						 <i class='user-sprites icon-merged' style="background-image: url('../images/sprites-icon-main.png'); background-position: 0 center; background-position: -1px -171px;">&nbsp;</i>
						 <div class='status'><?php echo mysql_num_rows(get_job_count('On Hold')); ?></div>
						 <div class='head-info visibletext allhd'>On Hold</div>
						 <div class='head-info hiddentext allhd'>On Hold</div>	
					</a>
				</div>
				<div class="sats-block block-color-seagreen">
					<?php echo $send_letters; ?>
				</div>
				<div class="sats-block block-color-deepblue">
					<?php echo $booked; ?>
				</div>
				 <div class="sats-block block-color-lightblue">
					<?php echo $pre_completed; ?>
				</div>			
				 <div class="sats-block block-color-brown">
					<?php echo $merged; ?>
				</div>
				<div class="sats-block block-color-brown" style="background-color: #9bf000;">
					<a href='/dha_jobs.php'>
						 <i class='user-sprites icon-merged' style="background-image: url('../images/dha_icon.png'); background-position: 0 center;">&nbsp;</i>
						 <div class='status'><?php echo mysql_num_rows(get_job_count('DHA')); ?></div>
						 <div class='head-info visibletext allhd'>DHA</div>
						 <div class='head-info hiddentext allhd'>DHA</div>	
					</a>
				</div>
				
				 <div class="sats-block block-color-brown">
					<?php echo $tbi; ?>
				</div>
				
				<div class="sats-block block-color-brown" style="background-color: #f37b53;">
					<a href='/escalate_jobs.php'>
						 <i class='user-sprites icon-merged' style="background-image: url('../images/escalate_icon.png'); background-position: 0 center; width:80px; height:75px;">&nbsp;</i>
						 <div class='status'><?php echo mysql_num_rows(get_job_count('Escalate')); ?></div>
						 <div class='head-info visibletext allhd'>Escalate</div>
						 <div class='head-info hiddentext allhd'>Escalate</div>	
					</a>
				</div>
				
				<div class="sats-block block-color-orange">
					<?php echo $atotal; ?>
				</div>
				
				<div class="sats-block block-color-orange" style="background-color: #b4151b;">
						<a href='/view_target_agencies.php'>
						 <i class='user-sprites icon-user'>&nbsp;</i>
						 <div class='status'>
						 <?php
						 $ta_sql = mysql_query("
							SELECT COUNT(`agency_id`) as jcount
							FROM `agency`
							WHERE `status` = 'target'
							AND `country_id` = {$_SESSION['country_default']}
						 ");
						 $ta = mysql_fetch_array($ta_sql);
						 echo $ta['jcount'];
						 ?>
						 </div>
						 <div class='head-info'>Target Agencies</div>	
						</a>
				</div>
			
			</div>
			
			
			
	
		</div>
		
		
	  
	  </div>	
   

  </div>
  
</div>

<br class="clearfloat" />

  
</body>
</html>
