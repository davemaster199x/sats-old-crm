<?php
$start = microtime(true);
$title = "CRM to PM Match";
$page_url = $_SERVER['REQUEST_URI'];

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$propertyme = new Propertyme_api;
$crm = new Sats_Crm_Class;

if(isset($_GET['agency_id'])){
	
	
	$pm_agency_id = mysql_real_escape_string($_REQUEST['agency_id']);
	$agency_id = $pm_agency_id;
	$agency_name = $propertyme->getAgencyName($pm_agency_id);

	$propertyme->getAgencyDetails($agency_id);
	$properties = $propertyme->getAllProperties()['Rows'];
	
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
        <li class="other first"><a title="PM Agencies" href="pm_agencies.php">PM Agencies</a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $page_url; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
	
	
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	<div class="sats-middle-cont">


	<h2 class="heading"><?=$agency_name?></h2>
	
	<!-- BEGIN MATCHED COLUMN -->
	<?php if(count($properties) > 0){?>
	<table id="properties_table" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd">
		<thead>
			<tr class="toprow jalign_left">
				<th>Address in PropertyMe</th>
				<th>PropertyMe ID</th>
				<th>Address in CRM</th>
				<th>CRM ID</th>
				<th>CRM PropertyMe ID</th>
				<th>Matched PM ID?</th>
				<th>PropertyMe Status</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			// PM properties
			foreach($properties as $property) {
				
				$addressPM = $property['UnitNumber']." ".$property['StreetNumber']." ".$property['StreetName'].", ".$property['Suburb']." ".$property['State']." ".$property['PostalCode'];
				
				// match PM properties on CRM
				$p_sql = $propertyme->matchPmToCrmProp($agency_id,$property['Id']);
				$num_rows = mysql_num_rows($p_sql);
				
				
				$addressCRM = '';
				$propIdCrm = '';
				$pm_prop_idCRM = '';
				if( $num_rows > 0 ){
					$p = mysql_fetch_array($p_sql);
					$addressCRM = $p['address_1']." ".$p['address_2']." ".$p['address_3']." ".$p['state']." ".$p['postcode'];
					$propIdCrm = $p['property_id'];
					$pm_prop_idCRM = $p['api_prop_id'];
				}
				?>
				<tr style="text-align:left !important;">
					<td><?=$addressPM?></td>
					<td><?=$property['Id']?></td>
					<td><?=$addressCRM?></td>
					<td><?=$propIdCrm?></td>
					<td><?=$pm_prop_idCRM?></td>
					<td><?php echo ( $num_rows > 0 )?'<span class="jActiveStatus">YES</span>':'<span class="jInActiveStatus">NO</span>'; ?></td>
					<td>
						<?php echo ( $property['ArchivedOn'] != "" )?'<span class="jInActiveStatus">Inactive</span>':'<span class="jActiveStatus">Active</span>'; ?>								
					</td>
				</tr>
				<?php 
				
			} 
			?>
		</tbody>
	</table>
	<?php } else { ?>
	No properties found.
	<?php }?>
	<!-- END MATCHED COLUMN -->


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