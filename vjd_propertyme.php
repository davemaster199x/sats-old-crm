<?php 
if($rsGet_propme['propertyme_prop_id'] != "" OR !empty($rsGet_propme['propertyme_prop_id'])){
?>
<div class="invoice_details_div jfloatleft" style="margin-right: 50px;">
	<h2 class="heading">Property Details</h2>
	<table class="table-vw-job tbl-fr-red fnt-small invoice_details_tbl" style="border:none; border-collapse: inherit !important;">
		<tbody>
			<tr>
				<td class="col1">Address</td>
				<td><?=$prop['AddressText']?></td>				
			</tr>
			<tr>
				<td class="col1">Property Manager</td>
				<td><?=$prop['PropertyManager']?></td>				
			</tr>
			<tr>
				<td class="col1">Property Type</td>
				<td><?=$prop['PropertyType']?></td>				
			</tr>
			<tr>
				<td class="col1">Bedrooms</td>
				<td><?=$prop['Bedrooms']?></td>				
			</tr>
			<tr>
				<td class="col1">Notes</td>
				<td><?=$prop['Notes']?></td>				
			</tr>
			<tr>
				<td class="col1">Key Number</td>
				<td><?=($prop['KeyNumber'] == "") ? '-' : $prop['KeyNumber']?></td>				
			</tr>
		</tbody>
	</table>
</div>

<div style="clear:both;"></div>

<h2 class="heading" style="margin-top: 40px;">Tenant Details</h2>
<?php 
if(!empty($prop['Tenancy']) AND count($prop['Tenancy']) > 0){
$tenants = $propertyme_api->getContactDetails($prop['Tenancy']['ContactId']);
?>
<table border="0" cellpadding="5" cellspacing="1" class="table-left">
<tr bgcolor='#<?=$serv_color?>'>
	<td class='colorwhite bold'>First Name</td>
	<td class='colorwhite bold'>Last Name</td>
	<td class='colorwhite bold'>Email Address</td>
	<td class='colorwhite bold'>Home Phone</td>
	<td class='colorwhite bold'>Work Phone</td>
	<td class='colorwhite bold'>Mobile Phone</td>
</tr>	
<?php  foreach($tenants['ContactPersons'] as $tenant){ ?>
<tr>
	<td style="text-align:left !important;"><?=$tenant['FirstName']?></td>
	<td style="text-align:left !important;"><?=$tenant['LastName']?></td>
	<td style="text-align:left !important;"><?=$tenant['Email']?></td>
	<td style="text-align:left !important;"><?=$tenant['HomePhone']?></td>
	<td style="text-align:left !important;"><?=$tenant['WorkPhone']?></td>
	<td style="text-align:left !important;"><?=$tenant['CellPhone']?></td>
</tr>
<?php }?>
</table>
<?php } else {
echo "No tenant found.";
}
?>

<div style="clear:both;"></div>

<h2 class="heading" style="margin-top: 40px;">Landlord Details</h2>
<?php 
if(!empty($prop['Ownership']) AND count($prop['Ownership']) > 0){
	$landlord = $propertyme_api->getContactDetails($prop['Ownership']['ContactId'])['Contact'];
?>
<table border="0" cellpadding="5" cellspacing="1" class="table-left">
<tr bgcolor='#<?=$serv_color?>'>
	<td class='colorwhite bold'>First Name</td>
	<td class='colorwhite bold'>Last Name</td>
	<td class='colorwhite bold'>Company Name</td>
	<td class='colorwhite bold'>Email Address</td>
	<td class='colorwhite bold'>Home Phone</td>
	<td class='colorwhite bold'>Work Phone</td>
	<td class='colorwhite bold'>Mobile Phone</td>
</tr>	
<tr>
	<td style="text-align:left !important;"><?=$landlord['PrimaryContactPerson']['FirstName']?></td>
	<td style="text-align:left !important;"><?=$landlord['PrimaryContactPerson']['LastName']?></td>
	<td style="text-align:left !important;"><?=$landlord['PrimaryContactPerson']['CompanyName']?></td>
	<td style="text-align:left !important;"><?=$landlord['PrimaryContactPerson']['Email']?></td>
	<td style="text-align:left !important;"><?=$landlord['PrimaryContactPerson']['HomePhone']?></td>
	<td style="text-align:left !important;"><?=$landlord['PrimaryContactPerson']['WorkPhone']?></td>
	<td style="text-align:left !important;"><?=$landlord['PrimaryContactPerson']['CellPhone']?></td>
</tr>
</table>
<?php } else {
echo "No landlord found.";
}?>

<div style="clear:both;"></div>


<h2 class="heading" style="margin-top: 40px;">Lease Details</h2>
<table border="0" cellpadding="5" cellspacing="1" class="table-left">
<tr bgcolor='#<?=$serv_color?>'>
	<td class='colorwhite bold'>Tenancy Start</td>
	<td class='colorwhite bold'>Agreement Start</td>
	<td class='colorwhite bold'>Lease Length</td>
</tr>
<tr>
	<td style="text-align:left !important;"><?=date("d/m/Y", strtotime($prop['Tenancy']['TenancyStart']))?></td>
	<td style="text-align:left !important;"><?=date("d/m/Y", strtotime($prop['Tenancy']['AgreementStart']))?></td>
	<td style="text-align:left !important;"><?=$prop['Tenancy']['ReviewFrequency']?></td>
</tr>
</table>


<?php } else {
echo "This property not link to propertyme.";
}?>