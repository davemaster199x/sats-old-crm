<?php
$start = microtime(true);
$title = "PM Properties";
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
	$properties = $propertyme->getAllProperties(FALSE)['Rows'];
	
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


	<!-- BEGIN VIEW PROPERTIES IN PM -->
	<p style="text-align:left;">List of Properties under <strong><?=$agency_name?></strong> Agency in <strong>PM</strong></p>
	<?php if(count($properties) > 0){?>
	<table id="properties_table" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd">
		<thead>
			<tr class="toprow jalign_left">
				<th>ID in PM</th>
				<th>Address in PM</th>
				<th>Status in PM</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		foreach($properties as $prop){

			$addressText = $prop['UnitNumber']." ".$prop['StreetNumber']." ".$prop['StreetName']." ".$prop['Suburb']." ".$prop['State']." ".$prop['PostalCode'];
			//$propertiesDetails = $propertyme->getPropertyDetails($prop['Id']);
			//$addressText = $propertiesDetails['AddressText'];
			
		?>
			<tr id="addressrow<?=$count?>" style="text-align:left !important;">
				<td><?=$prop['Id']?></td>
				<td><?=$addressText?></td>
				<td>
				<?php echo ( $prop['ArchivedOn'] !='' )?'<span class="jInActiveStatus">Inactive</span>':'<span class="jActiveStatus">Active</span>'; ?>
				</td>
			</tr>
		<?php }?>
		</tbody>
	</table>
	<?php } else { ?>
	No properties found.
	<?php }?>
	<!-- END VIEW PROPERTIES IN PM -->
	


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