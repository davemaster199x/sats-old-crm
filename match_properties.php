<?php
$start = microtime(true);
$title = "Match Properties";
$page_url = $_SERVER['REQUEST_URI'];

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$propertyme = new Propertyme_api;
$crm = new Sats_Crm_Class;

$agency_id = mysql_real_escape_string($_REQUEST['agency_id']);
$agency_name = mysql_real_escape_string($_REQUEST['agency_name']);





if( $_REQUEST['form_submitted'] ){
	
	
	$pm_prop_id_arr = $_REQUEST['pm_prop_id'];
	$crm_prop_id_arr = $_REQUEST['crm_prop_id'];
	$is_ticked_arr = $_REQUEST['is_ticked'];
	$success_msg = '';
	
	/*
	print_r($pm_prop_id_arr);
	echo "<br /><br />";
	print_r($crm_prop_id);
	echo "<br /><br />";
	print_r($is_ticked_arr);
	echo "<br /><br />";
	*/
	
	foreach( $pm_prop_id_arr as $index => $pm_prop_id ){
		
		if( $is_ticked_arr[$index] == 1 ){ // checkbox is ticked
		
			$crm_prop_id = mysql_real_escape_string($crm_prop_id_arr[$index]);
			
			$sql_str = "
				UPDATE `property`
				SET `propertyme_prop_id` = '{$pm_prop_id}'
				WHERE `property_id` = {$crm_prop_id}
			";
			//echo "<br />";
			mysql_query($sql_str);
		
		}
		
	}
	
	$success_msg = "Property match successful";
	
	
}





// improved - jc
if( $agency_id != '' ){

	$propertyme->getAgencyDetails($agency_id);
	$properties = $propertyme->getAllProperties()['Rows'];
	
	// get crm properties for dropdown
	$sql = "
	SELECT 
		p.`property_id`, 
		p.`address_1`, 
		p.`address_2`, 
		p.`address_3`, 
		p.`state`, 
		p.`postcode` 
	FROM `property` AS p
	LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
	WHERE a.`propertyme_agency_id` = '".$agency_id."' 
	AND (
		p.`propertyme_prop_id` IS NULL OR
		p.`propertyme_prop_id` = ''
	)
	AND p.`deleted` = 0 
	ORDER BY p.`address_1` ASC, p.`address_2` ASC
	";

	$query = mysql_query($sql);
	$crm = [];
	if(mysql_num_rows($query)) {
		while($row = mysql_fetch_array($query)) {
			$crm[] = $row;
		}
	}
	
}

function findClosestMatch($pm_address,$crm_address){	

	similar_text(strtolower($crm_address), strtolower($pm_address), $percent);
	if( $percent >= 90 ) { 
		return true;
	}else{
		return false;
	}
	
}


function checkIfPmPropAlreadyLinkedOnCRM($pm_prop_id,$agency_id){
	
	$ret = false;
	
	if( $pm_prop_id != '' ){
		
		$sql_str = "
		SELECT p.`propertyme_prop_id`
		FROM `property` AS p
		LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
		WHERE p.`propertyme_prop_id` = '{$pm_prop_id}'
		AND a.`propertyme_agency_id` = '{$agency_id}' 
		AND p.`deleted` = 0 
		";
		$sql = mysql_query($sql_str);
		
		if( mysql_num_rows($sql) > 0 ){
			$ret = true;
		}
		
	}
	
	
	return $ret;
	
}

?>
<style type="text/css">
#load-screen{
	display: block;
}
#properties_table{
	margin: 20px 0;
}
#properties_table th,
#properties_table td{
	text-align: left;
}
.save_match_btn{
	display: none;
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
	
	
	<?php
	if( $success_msg != '' ){ ?>
	<div class="success"><?php echo $success_msg; ?></div>
	<?php
	}
	?>


	<h2 class="heading">Match Properties</h2>

	<p style="text-align:left;"><strong>Note: </strong> Sometimes addresses are not accurate. Please check if addresses are matched.</p>
	
	<form method="post" style="text-align: left;" id="match_prop_form">
	<button type="submit" class="submitbtnImg blue-btn save_match_btn">
		<img class="inner_icon" src="images/button_icons/save-button.png">
		<span class="inner_icon_span">Save</span>
	</button>
	<?php if(count($properties) > 0){?>
	<table id="properties_table" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd">
		<thead>
			<tr class="toprow jalign_left">
				<th><input type="checkbox" id="select-all"></th>
				<th>Address in PM</th>
				<th>Property from CRM</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		foreach($properties as $index => $prop){
			
			if( checkIfPmPropAlreadyLinkedOnCRM($prop['Id'],$agency_id) == false ){

				$pm_address = $prop['UnitNumber']." ".$prop['StreetNumber']." ".$prop['StreetName']." ".$prop['Suburb']." ".$prop['State']." ".$prop['PostalCode'];
			?>
				<tr id="addressrow<?=$count?>" class="prop_row" style="text-align:left !important;">
					<td align="center">						
						<input type="checkbox" class="chkaddress prop_id_chk" />
						<input type="hidden" name="pm_prop_id[]" value="<?php echo $prop['Id']; ?>" />
						<input type="hidden" name="is_ticked[]" value="0" class="isTicked" />
					</td>
					<td><?=$pm_address?></td>
					<td>
					<input type="hidden" name="propertymeid[]" value="<?=$property['Id']?>">
						<select class="form-control input-sm crm_prop_id" name="crm_prop_id[]" style="width:100% !important;">
							<option value="">--- Select ---</option>
							<?php 
								if(!empty($crm)) {
									foreach($crm as $c) {
										$crm_address = $c['address_1']." ".$c['address_2']." ".$c['address_3']." ".$c['state']." ".$c['postcode'];
										?>
										<option value="<?php echo $c['property_id']; ?>" <?php echo ( findClosestMatch($pm_address,$crm_address) == true )?'selected="selected"':''; ?>><?php echo $crm_address; ?></option>
										<?php
									}
								}
							?>
						</select>
					</td>
				</tr>
			<?php 
			}
		}
		?>
		</tbody>
	</table>
	<?php } else { ?>
	No properties found.
	<?php }?>
	<button type="submit" class="submitbtnImg blue-btn save_match_btn">
		<img class="inner_icon" src="images/button_icons/save-button.png">
		<span class="inner_icon_span">Save</span>
	</button>
	<input type="hidden" name="form_submitted" value="1" />
	</form>


</div>
</div>


<!-- BEGIN MODAL -->
<div id="responsive" class="modal fade bs-modal-lg" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false"></div>
<!-- END MODAL -->


<script type="text/javascript">

function showButtonIfTicked(){
	
	
	var num_checked = jQuery(".prop_id_chk:checked").length;
		
	if( num_checked > 0 ){
		//return true;
		jQuery(".save_match_btn").show();
	}else{
		//return false;	
		jQuery(".save_match_btn").hide();
	}
	
}


$(document).ready( function () {
	
	
	jQuery("#load-screen").hide();
	
	
	
	jQuery(".prop_id_chk").change(function(){	

		var chk_state = jQuery(this).prop("checked");
	
		if( chk_state == true  ){
			jQuery(this).parents("tr:first").addClass('jredHighlightRow');
			jQuery(this).parents("tr:first").find('.isTicked').val(1);
		}else{
			jQuery(this).parents("tr:first").removeClass('jredHighlightRow');
			jQuery(this).parents("tr:first").find('.isTicked').val(0);
		}	
		
		showButtonIfTicked();
	});
	
	
	
	jQuery("#match_prop_form").submit(function(){
		
		var hasTicked = false;
		var noCrmPropSel = false;
		var error = '';
		
		jQuery(".prop_id_chk:checked").each(function(){
			
			hasTicked = true;
			var obj = jQuery(this);
			var row = obj.parents("tr:first");
			var crm_prop_id = row.find(".crm_prop_id").val();
			
			if( crm_prop_id == "" ){
				noCrmPropSel = true;
				row.addClass("jredHighlightRow");
			}
			
		});
		
		
		if( hasTicked == false ){
			error += "Please select property\n";
		}
		
		if( hasTicked == true && noCrmPropSel == true ){
			error += "One of selected property has no CRM property selected\n";
		}
		
		
		if( error != '' ){
			alert(error);
			return false;
		}else{
			return true;
		}
		
	});
	
	
	jQuery('#select-all').click(function(event) {   
	    if(this.checked) {
	        // Iterate each checkbox
	        jQuery(".prop_id_chk:visible").prop("checked",true);
	    } else {
	        jQuery(".prop_id_chk:visible").prop("checked",false);
			jQuery(".prop_row").removeClass('jredHighlightRow');
	    }
		
	    showButtonIfTicked();
	});


} );
</script>
</body>
</html>
<?php 
$time_elapsed_secs = microtime(true) - $start;
echo "Execution Time: {$time_elapsed_secs }";
 ?>