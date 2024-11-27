<?php

include('inc/init_for_ajax.php');
include('inc/completed_report_function.php');

$from = $_POST['from'];
$to = $_POST['to'];
$ajt_id = $_POST['ajt_id'];

switch($ajt_id){
	case 2:
		$serv_color = 'b4151b';
	break;
	case 5:
		$serv_color = 'f15a22';
	break;
	case 6:
		$serv_color = '00ae4d';
	break;
	case 7:
		$serv_color = '0000FF';
	break;
	default:
	$serv_color = '9B30FF';
}

$cntry_sql = getCountryViaCountryId($_SESSION['country_default']);
$cntry = mysql_fetch_array($cntry_sql);

$country_id = $cntry['country_id'];
$country_name = $cntry['country'];
$country_iso = strtolower($cntry['iso']);
?>



	<div style="width: 45%; float: left; margin-right: 10px;">


	<h2 class="heading" style="color:#<?php echo $serv_color; ?>">Days to Complete</h2>

	<?php
	// Job Status
	$job_type_arr = array(
		array(
			'full'=>'Yearly Maintenance',
			'short'=>'YM',
			'tot'=>0,
			'tot_age'=>0
		),
		array(
			'full'=>'Change of Tenancy',
			'short'=>'COT',
			'tot'=>0,
			'tot_age'=>0
		),
		array(
			'full'=>'Fix or Replace',
			'short'=>'FR',
			'tot'=>0,
			'tot_age'=>0
		),
			array(
			'full'=>'Lease Renewal',
			'short'=>'LR',
			'tot'=>0,
			'tot_age'=>0
		),
		array(
			'full'=>'Once-off',
			'short'=>'ONCE OFF',
			'tot'=>0,
			'tot_age'=>0
		)
	);
	?>
	<table border=0 cellspacing=0 cellpadding=5 width=100% class='table-center tbl-fr-red'>
		<tr bgcolor="#<?php echo $serv_color; ?>">
			<th>&nbsp;</th>
			<?php
			// job types
			foreach( $job_type_arr as $job_type ){ ?>
				<th><?php echo $job_type['short'] ?></th>
			<?php	
			}
			?>
			<th>Total
			<input type="hidden" value="<?php echo $tot = getCompletedCount($from,$to,$ajt_id,'',$country_id,''); ?>" />
			</th>
			<th>Total %</th>
		</tr>				
		<?php
					$green = '#e0fde0';
					$orange = '#ffedcc';
					$red = '#ffe5e5';
					$red2 = '#ffb2b2';
					$red3 = '#ff6666';
					$age=array(
						array("min"=>0,"max"=>3,'bg_color'=>$green),
						array("min"=>4,"max"=>7,'bg_color'=>$green),
						array("min"=>8,"max"=>14,'bg_color'=>$orange),
						array("min"=>15,"max"=>30,'bg_color'=>$orange),
						array("min"=>31,"max"=>60,'bg_color'=>$red),
						array("min"=>61,"max"=>90,'bg_color'=>$red),
						array("min"=>91,"max"=>120,'bg_color'=>$red),
						array("min"=>121,"max"=>150,'bg_color'=>$red2),
						array("min"=>151,"max"=>180,'bg_color'=>$red2),
						array("min"=>181,"max"=>181,'bg_color'=>$red3)
					);							
					
					$yt_tot = 0;
					$cot_tot = 0;
					$fr_tot = 0;
					$lr_tot = 0;
					$oo_tot = 0;
					$tot_sm_tot = 0;
					$grand_total = 0;
					$grand_tot_percent = 0;
					
					foreach($age as $val){ 
					$tot_sm = 0;
					?>
						<tr style="background-color: <?php echo $val['bg_color']; ?>;">
							<td class="f_col">
							<?php
							if($val['min']==$val['max']){
								echo "{$val['min']}+";
							}else{
								echo "{$val['min']}-{$val['max']}";
							}
							?>
							</td>
							<?php
							// job types
							foreach( $job_type_arr as $index=>$job_type ){ 						
							?>							
								<td><?php echo $jt_count = daysToComplete($from,$to,$ajt_id,$job_type['full'],$val['min'],$val['max'],$country_id); ?></td>
							<?php
								$job_type_arr[$index]['tot'] += $jt_count;	
								$tot_sm += $jt_count;
							}
							?>	
							<td>
							<?php 
							echo $tot_sm;
							$grand_total += $tot_sm;
							?>
							</td>
							<td>
								<?php 
									$tot_percent = number_format((($tot_sm/$tot)*100), 2, '.', ''); 
									echo "{$tot_percent}%";
									$grand_tot_percent += $tot_percent;
								?> 
							</td>							
						</tr>
					<?php	
					}
					?>
					<tr style="background-color:#DDDDDD">
						<td class="f_col"><strong>TOTAL COMPLETED</strong></td>		
						<?php
						// job types
						foreach( $job_type_arr as $index=>$job_type ){ 
						$yt_tot_age = 0;
						?>
							<td>
								<strong><?php echo $job_type['tot']; ?></strong>
								<?php
								$asql = getCompletedCount($from,$to,$ajt_id,$job_type['full'],$country_id,1);
								while($a = mysql_fetch_array($asql)){
									$date1=date_create($a['jcreated']);
									$date2=date_create($a['date']);
									$diff=date_diff($date1,$date2);
									$yt_tot_age += $diff->format("%a");
								}
								?>
								<input type="hidden" value="<?php echo $job_type_arr[$index]['tot_age'] = $yt_tot_age; ?>" />
							</td>
						<?php	
						}
						?>					
						<td><strong><?php echo $grand_total; ?></strong></td>
						<td><strong><?php echo $grand_tot_percent; ?> %</strong></td>
					</tr>
	</table>

	</div>


	<div style="width: 54%; float: left;">
				
	<h2 class="heading" style="color:#<?php echo $serv_color; ?>">Average Days to Complete</h2>

	<table border=0 cellspacing=0 cellpadding=5 width=50% class='table-center tbl-fr-red'>	
		<tr bgcolor="#<?php echo $serv_color; ?>">
			<th class="f_col"></th>
			<?php
			// job types
			foreach( $job_type_arr as $job_type ){ ?>
				<th><?php echo $job_type['short'] ?></th>
			<?php	
			}
			?>
		</tr>
		<tr>	
			<?php $ctr = count($age); ?>
			<td class="f_col">Average Days</td>
			<?php
			// job types
			foreach( $job_type_arr as $job_type ){ ?>
				<td><?php echo number_format(($job_type['tot_age']/$job_type['tot']), 2, '.', ''); ?></td>
			<?php	
			}
			?>
		</tr>
		
	</table>

	</div>
	
	<div style="clear:both">&nbsp;</div>



