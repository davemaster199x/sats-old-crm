<?php
$start = microtime(true);
$title = "CRM Properties";
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

	$crmSql = "
	SELECT 
		p.`property_id`, 
		p.`address_1`,
		p.`address_2`,
		p.`address_3`,
		p.`state`,
		p.`postcode`,
		p.`deleted`,
		p.`propertyme_prop_id`
	FROM `property` AS p
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
	WHERE a.`propertyme_agency_id` = '{$agency_id}'
	";
	$crmQuery = mysql_query($crmSql);
	
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
        <li class="other first"><a title="PM Agencies" href="<?=URL;?>pm_agencies.php">PM Agencies</a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $page_url; ?>"><strong><?php echo $title; ?></strong></a></li>
	  </ul>
    </div>
	
	
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	<div class="sats-middle-cont">


	<h2 class="heading"><?=$agency_name?></h2>
	
	<!-- BEGIN IN CRM ONLY -->
	<p style="text-align:left;">Properties under <strong><?=$agency_name?></strong> Agency that are in <strong>CRM ONLY</strong></p>			
		
	<?php if(mysql_num_rows($crmQuery) > 0){?>
	<table id="properties_table" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd">	
		<thead>
			<tr class="toprow jalign_left">
				<th>ID in CRM</th>
				<th>Address in CRM</th>			
				<th>Status in CRM</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		while($rowCRM = mysql_fetch_array($crmQuery)){
			
			$addressText = $rowCRM['address_1']." ".$rowCRM['address_2']." ".$rowCRM['address_3']." ".$rowCRM['state']." ".$rowCRM['postcode'];

		?>
			<tr id="addressrow<?=$count?>" style="text-align:left !important;">						
				<td><?=$rowCRM['property_id']?></td>
				<td>
					<a href="view_property_details.php?id=<?php echo $rowCRM['property_id']; ?>">
						<?=$addressText?>
					</a>
				</td>						
				<td>
				<?php echo ( $rowCRM['deleted'] == 1 )?'<span class="jInActiveStatus">Inactive</span>':'<span class="jActiveStatus">Active</span>'; ?>
				</td>
			</tr>
		<?php }?>
		</tbody>
	</table>
	<?php } else { ?>
	No properties found.
	<?php }?>
	<!-- BEGIN IN CRM ONLY -->


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