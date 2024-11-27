<?php

$title = "Expiring";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$country_id = $_SESSION['country_default'];

// get expiring alarms
function get_expiring_alarm($alarm,$date,$is_batteries,$country_id){

	$last_year = date("Y",strtotime("{$date} -1 year"));	
	$this_month = date("m",strtotime($date));
	$this_year = date("Y",strtotime($date));	
	$max_day = date("t",strtotime("{$last_year}-{$this_month}"));
	$bat_str = "";

	if($is_batteries!=1){
		$bat_str = "
			AND a.`expiry` = '{$this_year}'
			AND j.`job_type` = 'Yearly Maintenance'
			AND a.`alarm_power_id` = {$alarm}
		";
	}
	
	$str = "
		SELECT count( a.`alarm_id` ) AS jcount
		FROM `alarm` AS a
		LEFT JOIN `jobs` AS j ON a.`job_id` = j.`id`
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS agen ON agen.`agency_id` = p.`agency_id`
		WHERE j.`status` = 'Completed'		
		AND (j.`date` BETWEEN '{$last_year}-{$this_month}-01' AND '{$last_year}-{$this_month}-{$max_day}')
		AND p.`deleted` = 0
		AND agen.`status` = 'active'
		AND j.`del_job` = 0
		AND agen.`country_id` = {$country_id}
		{$bat_str}
	";
	$sql = mysql_query($str);
	$row = mysql_fetch_array($sql);
	return $row['jcount'];
}

// get alarms
function get_alarms(){
	return mysql_query("
		SELECT *
		FROM `alarm_pwr`
	");	
}

// get country
$cntry_sql = getCountryViaCountryId($country_id);
$cntry = mysql_fetch_array($cntry_sql);

$country_iso = strtolower($cntry['iso']);

?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.exp_col{
	width: 6%;
}
</style>
<div id="mainContent">

	
   
    <div class="sats-middle-cont">
	
		
	
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="Expiring" href="/expiring.php"><strong>Expiring</strong></a></li>
		  </ul>
		</div>
		<div id="time"><?php echo date("l jS F Y"); ?></div>


			
				<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd jtable <?php echo $country_iso; ?>_border" style="margin-top: 0px; margin-bottom: 13px;">	
			
					<tr class="toprow jalign_left">
						<th style="width:7%">Months</th>
						<?php
						
						$tot = array();
						$tot2 = array();				
						
						// get alarms
						$a_sql = get_alarms();
						if(mysql_num_rows($a_sql)>0){
							while($a = mysql_fetch_array($a_sql)){ ?>
							<th><?php echo $a['alarm_pwr']; ?></th>
							<th><?php echo $a['alarm_pwr']; ?> $</th>													
							<?php
								}
						}
						?>	
						<th>TOTAL</th>
					</tr>
					
					<?php
					// months, loop for 11 months
					$num_months = ($_GET['num_months']!="")?$_GET['num_months']-1:0;
					for($i=0;$i<=$num_months;$i++){ ?>
					<tr class="body_tr jalign_left">
						<td><?php echo date("F Y",strtotime("+{$i} month")); ?></td>
						<?php					
						$a_sql = get_alarms();
						$x = 0;
						$tot_mon = 0;
						while($a = mysql_fetch_array($a_sql)){ 									
						$is_bat = ($a['alarm_pwr_id']==6)?1:0;
						?>
						<td><?php echo $ea = get_expiring_alarm($a['alarm_pwr_id'],date("Y-m-1",strtotime("+{$i} month")),$is_bat,$country_id); ?></td>		
						<td>$<?php echo $ea2 = number_format(($a['alarm_price']*$ea),2,'.', ''); ?></td>
						<?php
						$tot[$x] += $ea;
						$tot2[$x] += $ea2;
						$tot_mon += $ea2;
						$x++;
						}					
						?>
						<td>$<?php echo number_format($tot_mon,2,'.', ''); ?></td>
					</tr>
					<?php
					}
					?>	
					
					<tr class="body_tr jalign_left" style="background-color:#DDDDDD">
						<td>
							<strong>TOTAL</strong>
						</td>
						<?php
						$tot_ae = 0;
						foreach($tot as $index=>$val){ 
						?>
						<td><?php echo $val; ?></td>
						<td>$<?php echo $tot_ae = number_format($tot2[$index],2,'.', ''); ?></td>					
						<?php
						$tot_ae2 += $tot_ae;
						}					
						?>
						<td>$<?php echo number_format($tot_ae2,2,'.', ''); ?></td>
					</tr>
					
				</table>
			
		
			
			
			<?php
			if($_GET['num_months']==""){ ?>
				<a href="/expiring.php?num_months=12">
					<button class="blue-btn submitbtnImg" id="btn_assign" type="button">Load 12 months</button>
				</a>	
			<?php	
			}
			?>
			
		
		
	</div>
</div>

<br class="clearfloat" />
<style>
.jtable tr td, .jtable tr th{
	border: 1px solid #cccccc;
}
</style>
</body>
</html>