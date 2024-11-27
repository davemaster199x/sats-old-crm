<?php
$start = microtime(true);
$title = "PM Agencies";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$propertyme = new Propertyme_api;
$crm = new Sats_Crm_Class;

// get PM Agencies
$agencies = $propertyme->getAgencies();
?>
<style type="text/css">
#load-screen{
	display: block;
}
</style>
<div id="mainContent">


	<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="PM Agencies" href="pm_agencies.php"><strong>PM Agencies</strong></a></li>
      </ul>
    </div>
	
	
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	<div class="sats-middle-cont">


	<h1 style="text-align:left;"><?=$pTitle?></h1>
	
	<!-- BEGIN AGENCY LIST TABLE -->
	<?php if(!empty($agencies)) {?>
		<h2 class="heading">List of Agencies</h2>
		<p style="text-align:left;">This page is used to Make CRM match to PropertyMe. It is importnat to process Step 1 then Step 2 etc. IT must be done in order</p>
		<table id="agency_table" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd">
		<thead>
			<tr class="toprow jalign_left">
				<th rowspan="2" style="border-top: 1px solid #000; border-left: 1px solid #000;">PropertyMe Agency</th>
				<th colspan="3" style="border-right: 1px solid #000; border-left: 1px solid #000; border-top: 1px solid #000;">PropertyMe Properties</th>
				<th colspan="3" style="border-right: 1px solid #000; border-top: 1px solid #000;">CRM Properties</th>
				<th colspan="3" style="border-right: 1px solid #000; border-top: 1px solid #000; text-align:center;">Step 1</th>
				<th style="border-right: 1px solid #000; border-top: 1px solid #000; text-align:center;">Step 2</th>
				<th style="border-right: 1px solid #000; border-top: 1px solid #000; text-align:center;">Step 3</th>
				<th style="border-right: 1px solid #000; border-top: 1px solid #000; text-align:center;">Step 4</th>					
			</tr>
			<tr class="toprow jalign_left">
				<!-- PM properties -->
				<th style="border-left: 1px solid #000;">Active</th>
				<th>Inactive</th>
				<th style="border-right: 1px solid #000; width:3%;">View</th>
				<!-- CRM properties -->
				<th>Active</th>
				<th>Inactive</th>
				<!-- Matched -->
				<th style="border-right: 1px solid #000; width:3%;">View</th>
				<th>CRM/PropertyMe</th>
				<th>Match Now</th>
				<th style="border-right: 1px solid #000; width:3%;">View</th>
				<th style="border-right: 1px solid #000;">In PM Only</th>
				<th style="border-right: 1px solid #000;">In CRM Only</th>
				<th style="border-right: 1px solid #000;">Inactive in PM</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		// loop through PM agencies			
		foreach($agencies as $agency) {

			// PM properties
			$propertyme->getAgencyDetails($agency['CustomerId']);
			$props = $propertyme->getAllProperties(FALSE)['Rows'];

			$inActivePM = 0;
			$activePM = 0;	
			$inActivePmPropID = [];
			$activeCrmThatDeactivatedInPM = [];
			$crmPropMatched = 0;
			foreach($props as $prop){
			
			
			if( $prop['ArchivedOn'] !='' ){ // inactive		
			
				$inActivePmPropID[] = $prop['Id'];
				$inActivePM++;	
				
			}else{ // active
										
				//$p_sql2 = $propertyme->matchPmToCrmProp($agency['CustomerId'],$prop['Id']);
				$num_rows = mysql_num_rows($p_sql2);
				if( $num_rows > 0 ){
					$crmPropMatched++;
				}
				$activePM++;
			}		
			
			
		}
										

		// CRM properties
		$inActiveCRM = 0;
		$activeCRM = 0;					
		$inpmonly = 0;
		$inCrmOnly = 0;				
		if( $agency['CustomerId'] != '' ){
			
			$p_sql_str = "
			SELECT p.`property_id`, p.`deleted`, p.`propertyme_prop_id` 
			FROM `property` AS p 
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
			WHERE a.`propertyme_agency_id` = '{$agency['CustomerId']}'
			";
			$p_sql = mysql_query($p_sql_str);
			if( mysql_num_rows($p_sql)>0 ){
				while( $p =  mysql_fetch_array($p_sql) ){
				
					if( $p['deleted'] == 1 ){ // deleted								
						$inActiveCRM++;
					}else{	// active	

						if( $p['propertyme_prop_id'] != '' ){ // has PM prop ID	
							if( in_array( $p['propertyme_prop_id'], $inActivePmPropID ) ){
								$activeCrmThatDeactivatedInPM[] = $p['property_id'];
							}
							$crmPropMatched++;
						}else if( $p['propertyme_prop_id'] == '' ){
							$inCrmOnly++;
						}	
						
						$activeCRM++;
						
					}
											
					
				}
			}
		}									

		?>
			<tr style="text-align:left !important;">
				<td style="border-left: 1px solid #000;">
					<span style="color:#337ab7;"><?=$agency['CustomerCompanyName']?></span>
				</td>
				<!-- PM properties -->
				<td style="border-left: 1px solid #000;"><?=$activePM?></td>
				<td><span style="color:#337ab7">(<?=$inActivePM?>)</span></td>
				<td style="border-right: 1px solid #000;">
					<a href="pm_properties.php?agency_id=<?=$agency['CustomerId']?>">
						<button type="button" class="submitbtnImg blue-btn">
							<span class="inner_icon_span">View</span>
						</button>
					</a>
				</td>
				<!-- CRM properties -->
				<td><?=$activeCRM?></td>
				<td><span style="color:#337ab7">(<?=$inActiveCRM?>)</span></td>
				<td style="border-right: 1px solid #000;">
					<a href="crm_properties.php?agency_id=<?=$agency['CustomerId']?>">
						<button type="button" class="submitbtnImg blue-btn">
							<span class="inner_icon_span">View</span>
						</button>
					</a>
					
				</td>



				<!-- Matched -->
				<td>
					<span style='color:<? echo ( $crmPropMatched == $activePM )?'green':'red'; ?>'><?=$crmPropMatched?></span>
					/
					<span style='color:<? echo ( $crmPropMatched == $activePM )?'green':''; ?>'><?=$activePM?></span>
				</td>
				<td>
					<?php
					if( $crmPropMatched == $activePM ){
						$fclass = 'fadeIt';
						$flink = 'javascript:void(0);';							
					}else{
						$fclass = '';
						$flink = 'match_properties.php?agency_id='.$agency['CustomerId'].'&agency_name='.$agency['CustomerCompanyName'];
					}
					?>
					<a href="<?php echo $flink; ?>">
						<button type="button" class="submitbtnImg green-btn <?php echo $fclass; ?>">
							<span class="inner_icon_span">Match Properties</span>
						</button>						
					</a>					
				</td>
				<td style="border-right: 1px solid #000;">
					<a href="crm_to_pm_match.php?agency_id=<?=$agency['CustomerId']?>">
						<button type="button" class="submitbtnImg blue-btn">
							<span class="inner_icon_span">View</span>
						</button>
					</a>
				</td>

				<!-- In PM Only -->
				<td>
					<?php  
					
					$inpmonly = ($activePM-$crmPropMatched);
					$pmonly = ( intval($inpmonly) > 0 ) ? "#ff0000" : "#00AE4D"; 
					
					?>
					<span style='color:<?=$pmonly?>'><?=$inpmonly?></span>
					<?php
					if( intval($inpmonly) > 0 ){
						$fclass = '';
						$flink = 'in_pm_only.php?agency_id='.$agency['CustomerId'];
					}else{
						$fclass = 'fadeIt';
						$flink = 'javascript:void(0);';
					}
					?>
					<a href="<?php echo $flink; ?>">
						<button type="button" class="submitbtnImg green-btn <?php echo $fclass; ?> <?php echo $fclass; ?>">
							<span class="inner_icon_span">Add to CRM</span>
						</button>					
					</a>
				</td>
				
				<!-- In CRM Only -->
				<td style="border-right: 1px solid #000;">
					<?php  $crmonly = (intval($inCrmOnly) > 0) ? "#ff0000" : "#00AE4D"; ?>
					<?="<span style='color:".$crmonly."'>".$inCrmOnly."</span>"?>
					<?php
					if( intval($inCrmOnly) > 0 ){
						$fclass = '';
						$flink = "mark_nlm.php?agency_id={$agency['CustomerId']}&in_crm_only=1";
					}else{
						$fclass = 'fadeIt';
						$flink = 'javascript:void(0);';
					}
					?>	
					<a href="<?php echo $flink; ?>">
						<button type="button" class="submitbtnImg green-btn <?php echo $fclass; ?>">
							<span class="inner_icon_span">Mark NLM</span>
						</button>
					</a>
					
				</td>
				
				<!-- Inactive in PM -->
				<?php
				if( count($activeCrmThatDeactivatedInPM) > 0 ){
					$fclass = '';
					$flink = "mark_nlm.php?agency_id={$agency['CustomerId']}&inactive_in_pm=1";
				}else{
					$fclass = 'fadeIt';
					$flink = 'javascript:void(0);';
				}
				?>	
				<td style="border-right: 1px solid #000;">
					<span style="color:#00AE4D"><?php echo count($activeCrmThatDeactivatedInPM); ?></span>
					<a href="<?php echo $flink; ?>">
						<button type="button" class="submitbtnImg green-btn <?php echo $fclass; ?>">
							<span class="inner_icon_span">Mark NLM</span>
						</button>
					</a>
					
				</td>
			</tr>
			<?php 
			}
		?>
		</tbody>
		
		</table>
		<?php 
		} else {
			echo "No agencies found";
		} 
		?>
	<!-- END AGENCY LIST TABLE -->

</div>
</div>


<!-- BEGIN MODAL -->
<div id="responsive" class="modal fade bs-modal-lg" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false"></div>
<!-- END MODAL -->


<script type="text/javascript">
jQuery(document).ready(function(){
	
	jQuery("#load-screen").hide();

});
</script>
</body>
</html>
<?php 
$time_elapsed_secs = microtime(true) - $start;
echo "<p style='text-align:center;'>Execution Time: {$time_elapsed_secs }</p>";
 ?>