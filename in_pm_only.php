<?php
$start = microtime(true);
$title = "In PM Only";
$page_url = $_SERVER['REQUEST_URI'];

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$propertyme = new Propertyme_api;
$crm = new Sats_Crm_Class;


$agencies = $propertyme->getAgencies();


if(isset($_GET['agency_id'])){
	
	$pm_agency_id = mysql_real_escape_string($_REQUEST['agency_id']);
	$agency_id = $pm_agency_id;
	$agency_name = $propertyme->getAgencyName($pm_agency_id);

	// switch to new agency
	$propertyme->getAgencyDetails($agency_id);
	$properties = $propertyme->getAllProperties(TRUE)['Rows'];
	
}


function getAgencyDatas($pm_agency_id){
	
	return mysql_query("
		SELECT `agency_id`,`franchise_groups_id`,`allow_indiv_pm` 
		FROM `agency` 
		WHERE `propertyme_agency_id`='{$pm_agency_id}'
	");
	
}

?>
<style type="text/css">
#load-screen{
	display: block;
}
#properties_table th{
	text-align: left;
}
</style>
<div id="mainContent">


	<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="PM Agencies" href="pm_agencies.php"><strong>PM Agencies</a></strong></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $page_url; ?>"><strong><?php echo $title; ?></strong></a></li>
	  </ul>
    </div>
	
	
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	<div class="sats-middle-cont">


	<h2 class="heading"><?=$agency_name?></h2>
	
	<!-- BEGIN IN PM ONLY COLUMN -->
	<div class="row">
		<div class="col-md-12">
			<p style="text-align:left;">List of Properties under <strong><?=$agency_name?></strong> Agency in <strong>PropertyME</strong></p>
			
			<table id="properties_table" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd">
				<thead>
					<tr class="toprow jalign_left">
						<th>PropertyMe Agency ID</th>
						<th>Address in PropertyMe</th>
						<th>Status in PropertyMe</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					foreach($properties as $property) { 
					
					

						// match PM property to CRM property
						$p_sql = $propertyme->matchPmToCrmProp($agency_id,$property['Id']);
						$num_rows = mysql_num_rows($p_sql);
		
					
						if( $num_rows == 0 ){
							
							// agency details
							$a_sql = getAgencyDatas($agency_id);
							$a = mysql_fetch_array($a_sql);	
							
							// PM prop details
							//$propertiesDetails = $propertyme->getPropertyDetails($property['Id']);
							//$addressText = $propertiesDetails['AddressText'];
							//$addressText = "{$property['StreetNumber']} {$property['StreetName']} {$property['Suburb']} {$property['State']}";
							//$addressText = $property['PropertyReference'];
							
							$addressText = $property['UnitNumber']." ".$property['StreetNumber']." ".$property['StreetName']." ".$property['Suburb']." ".$property['State']." ".$property['PostalCode'];
	
							if($property['ArchivedOn'] != "")
							{
								$label = "danger";
								$text = "INACTIVE";
							} else {
								$label = "success";
								$text = "ACTIVE";
							}	
						?>
						<tr style="text-align:left !important;">
							<td><?=$property['Id']?></td>
							<td><?=$addressText?>
							<?php //print_r($property); ?>
							</td>
							<td>
								<?php echo ( $property['ArchivedOn'] != "" )?'<span class="jInActiveStatus">Inactive</span>':'<span class="jActiveStatus">Active</span>'; ?>								
							</td>
							<td>
								<a href="add_property_static.php?exec=api&agency_id=<?=$a['agency_id']?>&fg=<?=$a['franchise_groups_id']?>&allow_pm=<?=$a['allow_indiv_pm']?>&pid=<?=$property['Id']?>" class="btn btn-primary btn-sm" target="_blank">
									<button type="button" class="submitbtnImg blue-btn">
										<span class="inner_icon_span">Add to CRM</span>
									</button>							
								</a>								
							</td>
						</tr>
						<?php 
						
						} 
					} 
					?>
				</tbody>
			</table>
		</div>
	</div>
	<!-- END IN PM ONLY COLUMN -->


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
echo "Execution Time: {$time_elapsed_secs }";
 ?>