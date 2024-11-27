<?php
include('inc/init_for_ajax.php');

$propertyme = new Propertyme_api;

$agency_id = filter_input(INPUT_POST, "agency_id");


$propertyme->getAgencyDetails($agency_id);
$properties = $propertyme->getAllProperties()['Rows'];

// get non-connected crm properties
$sql = "SELECT A.`property_id`, A.`address_1`, A.`address_2`, A.`address_3`, A.`state`, A.`postcode` 
		FROM `property` A 
		LEFT JOIN `agency` B ON B.`agency_id` = A.`agency_id`
		WHERE B.`propertyme_agency_id` = '".$agency_id."' 
		AND (
			A.`propertyme_prop_id` IS NULL OR
			A.`propertyme_prop_id` = ''
		)
		AND A.`deleted` = 0 
		ORDER BY A.`address_1`,A.`address_2` ASC";

$query = mysql_query($sql);
$crm = [];
if(mysql_num_rows($query)) {
	while($row = mysql_fetch_array($query)) {
		$crm[] = $row;
	}
}
?>
<div class="alert alert-warning"><strong>Notes: </strong> Sometimes addresses are not accurate. Please check if addresses are matched.</div>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-primary">
	      <div class="panel-heading">List of Properties in PropertyMe</div>
	      <div class="panel-body">
	      	<?php 
	  //     	$search = strtolower($search);
	  //     	result_properties = [];
	  //     	foreach($properties as $key => $value) {
	  //     		$fullAddress = $value['UnitNumber']." ".$value['StreetNumber']." ".$value['StreetName']." ".$value['Suburb']." ".$value['State']." ".$value['PostalCode'];
			// 	if(preg_match("@".$search."@", strtolower($value['UnitNumber'])) OR 
			// 		preg_match("@".$search."@", strtolower($value['StreetNumber'])) OR 
			// 		preg_match("@".$search."@", strtolower($value['StreetName'])) OR 
			// 		preg_match("@".$search."@", strtolower($value['PostalCode'])) OR 
			// 		preg_match("@".$search."@", strtolower($value['Suburb'])) OR 
			// 		preg_match("@".$search."@", strtolower($value['State'])) OR
			// 		preg_match("@".$search."@", strtolower($fullAddress)) ) {
			// 		result_properties[] = $value;
			// 	}
			// }
	      	?>
	      	<?php if(!empty($properties)){?>
	      	<form method="post" id="formProperties" onsubmit="return confirm('Are you sure you want to save?')">
	      	<input type="submit" name="btnsave" class="btn btn-primary btn-sm pull-left" value="Save Matched Properties" style="margin-bottom:20px;">
			<table class="table table-hover table-striped table-condensed table-bordered">
				<thead>
					<tr>
						<th width="2%"><input type="checkbox" style="margin-left:15px;" id="select-all"></th>
						<th width="15%">Property Name</th>
						<th width="20%">Property from CRM</th>
					</tr>
				</thead>
				<tbody>
				<?php 
				$count = 0;
				foreach($properties as $property){
					
					$v = mysql_query("SELECT * FROM `property` WHERE `propertyme_prop_id`='".$property['Id']."' AND `deleted` = 0");
					if(mysql_num_rows($v) == 0){

					$propertiesDetails = $propertyme->getPropertyDetails($property['Id']);
				?>
					<tr id="addressrow<?=$count?>">
						<td align="center"><input type="checkbox" name="chkProperty[]" value="<?=$count?>" class="chkaddress"></td>
						<td><?=$propertiesDetails['AddressText']?></td>
						<td>
						<input type="hidden" name="propertymeid[]" value="<?=$property['Id']?>">
							<select class="form-control input-sm select2-single" name="searchCRM[]" id="searchCRM<?=$property['Id']?>" style="width:100% !important;">
								<option value=""></option>
								<?php 
									if(!empty($crm)) {
										foreach($crm as $c) {
											$addresscrm = $c['address_1']." ".$c['address_2']." ".$c['address_3']." ".$c['state']." ".$c['postcode'];
											echo "<option value='".$c['property_id']."'>".$addresscrm."</option>";
										}
									}
								?>
							</select>
						</td>
					</tr>
				<?php 
				$count++;
				} }?>
				</tbody>
			</table>
			<input type="submit" name="btnsave" class="btn btn-primary btn-sm pull-left" value="Save Matched Properties">
			</form>
			
			<?php }else{?>
			No properties found
			<?php }?>	
	      </div>
	    </div>
	</div>
</div>
<script>

$(".select2-single").select2();

$('#select-all').click(function(event) {   
    if(this.checked) {
        // Iterate each checkbox
        $(':checkbox').each(function() {
            this.checked = true;                        
        });
    } else {
        $(':checkbox').each(function() {
            this.checked = false;                       
        });
    }
});

$('.chkaddress').on('click', function(){
	if($(this).is(":checked")) {
		$('#addressrow' + this.value).addClass('row-highlighted');
	} else {
		$('#addressrow' + this.value).removeClass('row-highlighted');
	}
	
});

<?php 

foreach($properties as $property){
	$propertiesDetails = $propertyme->getPropertyDetails($property['Id']);

			if(!empty($crm)) {

				foreach($crm as $c) { 
					$addresscrm = $c['address_1']." ".$c['address_2']." ".$c['address_3']." ".$c['state']." ".$c['postcode'];
					$addresspm = strtolower($propertiesDetails['AddressText']);
					similar_text(strtolower($addresscrm), $addresspm, $percent);
					if($percent >= 90) { 
						// echo "alert('#searchCRM".$property['Id']."');";
						// echo "alert('12');";
						echo "$('#searchCRM".$property['Id']."').val(".$c['property_id'].").trigger('change')";
					?>
						// alert('<?=$percent?>');
						// $('#searchCRM<?=$property["Id"]?>').val(<?=$c['property_id']?>).trigger('change');
					<?php }
				}
			}
} ?>


</script>