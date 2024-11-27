<?

$title = "Whiteboard";
$onload = 1;
//$onload_txt = "zxcSelectSort('agency',1)";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$state = $_POST['state'];

// get service total
function get_service_totals($agency_id="",$service,$postcode){	

	$agency_id = ($agency_id!="")?"AND a.`agency_id` ={$agency_id}":"";

	$sql = mysql_query("
		SELECT count(j.`id`) AS jcount
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON p.`property_id` = j.`property_id`
		LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
		WHERE j.`status` = 'To Be Booked'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		{$agency_id}
		AND j.`service` ={$service}
		AND p.`postcode`
		IN ( {$postcode} )				
	");
	$serv = mysql_fetch_array($sql);
	return $serv['jcount'];
}

// get job type counts
function get_job_type_count($agency_id="",$service,$postcode,$job_type="",$urgent_job=""){	

	$agency_id = ($agency_id!="")?" AND a.`agency_id` ={$agency_id} ":"";
	$job_type = ($job_type!="")?" AND j.`job_type` ='{$job_type}' ":"";
	$urgent_job = ($urgent_job!="")?" AND j.`urgent_job` = {$urgent_job} ":"";
	

	$sql = mysql_query("
		SELECT count(j.`id`) AS jcount
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON p.`property_id` = j.`property_id`
		LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
		WHERE j.`status` = 'To Be Booked'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		{$agency_id}
		{$job_type}
		{$urgent_job}
		AND j.`service` ={$service}
		AND p.`postcode`
		IN ( {$postcode} )				
	");
	$serv = mysql_fetch_array($sql);
	return $serv['jcount'];
}

// get agency per region
function getAgencyPerRegion($pr_pc,$state){
	
	$state_str = ($state!="")?" AND a.`state` = '{$state}' ":'';
	
	$sql = "
	SELECT DISTINCT p.`agency_id` , a.`agency_name`
	FROM `jobs` AS j
	LEFT JOIN `property` AS p ON p.`property_id` = j.`property_id`
	LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
	WHERE j.`status` = 'To Be Booked'
	AND p.`deleted` = 0
	AND a.`status` = 'active'
	AND j.`del_job` = 0
	AND a.`country_id` = {$_SESSION['country_default']}
	AND p.`postcode` IN ({$pr_pc})
	{$state_str}
	ORDER BY a.`agency_name` ASC
	";
	return mysql_query($sql);
}

?>
<div id="mainContent">
	
	

    
      <div class="sats-middle-cont">
  
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Whiteboard" href="/whiteboard.php"><strong>Whiteboard</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
   <style>
   #legend{
	width: auto;
   }
   #legend tr{
	border:none;
   }
   #legend .rebooks-color{
	color:green;
   }
   #legend .fnr-color{
	color:blue;
   }
   </style>
    <div class="aviw_drop-h" style="border: 1px solid #ccc; border-bottom: none;">  
   <table id="legend">
	   <tr style="border: none !important;">
			<td class="rebooks-color">240v</td>
			<td class="fnr-color">FR</td>
			<td style='color:#f15a22'>COT/LR</td>
			<td style='color:#00ae4d'>URGENT</td>
			<td>				
				<form method="post">
					<?php
					if(ifCountryHasState($_SESSION['country_default'])==true){ ?>
						<label>State:</label>
						<select id="state" name="state" style="width: 70px;">
							<option value="">----</option> 
							<?php
							$state_sql = mysql_query("
								SELECT DISTINCT(a.`state`)
								FROM `jobs` AS j
								LEFT JOIN `property` AS p ON p.`property_id` = j.`property_id`
								LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
								WHERE j.`status` = 'To Be Booked'
								AND p.`deleted` = 0
								AND a.`status` = 'active'
								AND j.`del_job` = 0
								AND a.`country_id` = {$_SESSION['country_default']}
								ORDER BY a.`state` ASC
							");
							while( $state_row = mysql_fetch_array($state_sql) ){ ?>
								<option value="<?php echo $state_row['state']; ?>" <?php echo ($state_row['state']==$state)?'selected="selected"':''; ?>><?php echo $state_row['state']; ?></option>
							<?php	
							}
							?>						
						 </select>
					<?php	
					}
					?>
					
					 <label>Search:</label>
					 <input type="text" class="addinput searchstyle vwjbdtp" value="" name="search" />
					 <input type="submit" value="Go" name="submit" class="submitbtnImg" style="margin-left: 13px;" />
				</form>					
			</td>
			<td>This page shows all jobs that are "to be booked"</td>
	   </tr>
   </table>
   </div>
   
   
    
	<table class="whiteboard table-left tbl-fr-red" cellspacing="0" cellpadding="3">
	
		<tr bgcolor="#b4151b">
			<th>Agency Name</th>
			<?php
			// get alarm job type
			$ajt_sql = mysql_query("
				SELECT *
				FROM `alarm_job_type`
				WHERE `active` = 1
			");
			while($ajt = mysql_fetch_array($ajt_sql)){ 

			
			?>
				<th><img src="images/serv_img/<?php echo getServiceIcons($ajt['id'],1); ?>" /></th>
			<?php	
			}
			?>
			<th>Total</th>
		</tr>
		
		<?php	

		$search = $_POST['search'];
		if($search!=""){
			$search_str = "AND `subregion_name` LIKE '%{$search}%'";
		}		
		
		// get main region
		$reg_sql = getMainRegions($_SESSION['country_default'],$state);
		
		while( $reg = mysql_fetch_array($reg_sql) ){ ?>
		
		<tr style="border-right: 1px solid #cccccc !important;">
			<td colspan="8" class="region" style="color: blue; font-size: 16px; font-weight: bold;"><?php echo $reg['region_name']; ?></td>
		</tr>
			
		<?php	
		
		// get sub regions > old table
		/*$prsql_str = "SELECT *
			FROM `postcode_regions`
			WHERE `country_id` = {$_SESSION['country_default']}
			AND region = {$reg['regions_id']}
			{$search_str}			
			ORDER BY `postcode_region_name` ASC";
		$prsql = mysql_query($prsql_str);	 */
		
		## new table (by:gherx)
		$prsql_str = "SELECT * , sr.`subregion_name` as postcode_region_name, pc.`postcode` AS postcode_region_postcodes
			FROM `sub_regions` AS sr
			LEFT JOIN `postcode` AS pc ON sr.`sub_region_id` = pc.`sub_region_id`
			WHERE sr.`region_id` = {$reg['regions_id']}
			{$search_str}			
			ORDER BY sr.`subregion_name` ASC";
		$prsql = mysql_query($prsql_str);	 
		
		// loop through region
		while($pr = mysql_fetch_array($prsql)){	
			$smoke = 0;
			$switch = 0;
			$window = 0;
			$pool = 0;
			$tot = 0;
			$serv_tot_col_fin_tot = 0;
		?>
		<tr style="border-right: 1px solid #cccccc !important;">
			<td colspan="8" class="regiontitle"><?php echo $pr['postcode_region_name']; ?></td>
		</tr>
		
		<?php
		
			/*
			// get agency per regions
			$an_sql = mysql_query("
			SELECT DISTINCT p.`agency_id` , a.`agency_name`
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON p.`property_id` = j.`property_id`
			LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
			WHERE j.`status` = 'To Be Booked'
			AND p.`deleted` =0
			AND p.`postcode` IN ({$pr['postcode_region_postcodes']})
			ORDER BY a.`agency_name` ASC
			");
			*/
			
			
			$an_sql = getAgencyPerRegion($pr['postcode_region_postcodes'],$state);
									
			// loop through agencies per region			
			if(mysql_num_rows($an_sql)>0){
				$serv_tot = array();
				while($an = mysql_fetch_array($an_sql)){ 
				$serv_tot_col = 0;
				?>
					<tr style="border-right: 1px solid #cccccc !important;">
						<td><a href="whiteboard-agency-jobs.php?agency_id=<?php echo $an['agency_id']; ?>"><?php echo $an['agency_name']; ?></a></td>
						
						
						<?php
						// get alarm job type
						$ajt_sql = mysql_query("
							SELECT *
							FROM `alarm_job_type`
							WHERE `active` = 1
						");
						while($ajt = mysql_fetch_array($ajt_sql)){ 
						?>
						<td>
							<?php							
								// service count
								echo $serv_count = get_service_totals($an['agency_id'],$ajt['id'],$pr['postcode_region_postcodes']);
							
								// if smoke
								//if($ajt['id']==2){
									
									// parenthesis
									$rebook = get_job_type_count($an['agency_id'],$ajt['id'],$pr['postcode_region_postcodes'],"240v Rebook");
									echo ($rebook!=0)?"<span style='color:green'>({$rebook})</span>":'';
									$fnr = get_job_type_count($an['agency_id'],$ajt['id'],$pr['postcode_region_postcodes'],"Fix or Replace");								
									echo ($fnr!=0)?"<span style='color:blue'>({$fnr})</span>":''; 
									
									$cot = get_job_type_count($an['agency_id'],$ajt['id'],$pr['postcode_region_postcodes'],"Change of Tenancy");								
									echo ($cot!=0)?"<span style='color:#f15a22'>({$cot})</span>":'';
									$lr = get_job_type_count($an['agency_id'],$ajt['id'],$pr['postcode_region_postcodes'],"Lease Renewal");								
									echo ($lr!=0)?"<span style='color:#f15a22'>({$lr})</span>":'';
									// is urgent
									$urg = get_job_type_count($an['agency_id'],$ajt['id'],$pr['postcode_region_postcodes'],"",1);								
									echo ($urg!=0)?"<span style='color:#00ae4d'>({$urg})</span>":'';
									
									// total
									$rebook_tot += $rebook;
									$fnr_tot += $fnr;

									$cot_tot += $cot;
									$lr_tot += $lr;
									$urg_tot += $urg;
									
								//}
								// serv total
								$serv_tot[$ajt['id']] += $serv_count;
								$serv_tot_col += $serv_count;
							?>
						</td>							
						<?php
						}
							
						/*
						$smoke += $smoke;
						$window_tot += $window;
						$switch_tot += $switch;
						$tot_tot += $tot;
						*/						
						?>
						<td><?php echo $serv_tot_col; ?></td>
					</tr>				
				<?php
				$serv_tot_col_fin_tot += $serv_tot_col;				
				}?>
				<tr class="total" style="background-color:#EEEEEE">
					<td><strong>Total</strong></td>
					<?php
					foreach($serv_tot as $index=>$val){ ?>
					<td>
						<strong>
						<?php 
						echo $val;
						if($index==2){
							echo ($rebook_tot!=0)?" <span style='color:green'>({$rebook_tot})</span>":''; 
							echo ($fnr_tot!=0)?" <span style='color:blue'>({$fnr_tot})</span>":'';	
							
							echo ($cot_tot!=0)?" <span style='color:#f15a22'>({$cot_tot})</span>":'';
							echo ($lr_tot!=0)?" <span style='color:#f15a22'>({$lr_tot})</span>":'';
							echo ($urg_tot!=0)?" <span style='color:#00ae4d'>({$urg_tot})</span>":'';
						}										
						?>
						</strong>
					</td>
					<?php	
					}
					?>				
					<?php
					$rebook_tot = 0;
					$fnr_tot = 0;
					
					$cot_tot = 0;
					$lr_tot = 0;
					$urg_tot = 0;
					
					$smoke = 0;
					$window_tot = 0;
					$switch_tot = 0;
					$tot_tot = 0;
					?>
					<td><?php echo $serv_tot_col_fin_tot; ?></td>
				</tr>
			<?php
			}else{ ?>
				<tr>
					<td colspan="8" class="no_date">no data
					<script>
						jQuery(".no_date").each(function(){
							jQuery(this).parents("tr:first").hide();
							jQuery(this).parents("tr:first").prev().hide();
						});
					</script>
					</td>
				</tr>
			<?php
			}
		}
		
		
	}
		?>
				
	</table>
    
  </div>

</div>

<br class="clearfloat" />

</body>
</html>
