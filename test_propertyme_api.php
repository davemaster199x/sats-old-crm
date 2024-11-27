<?php
include('inc/init_for_ajax.php');

$propertyme = new Propertyme_api;

$agencies = $propertyme->getAgencies();

if(isset($_GET['agency_id'])){
	$agency_id = explode("**", $_GET['agency_id'])[0];
	$agency_name = explode("**", $_GET['agency_id'])[1];

	// switch to new agency
	$propertyme->getAgencyDetails($agency_id);
	$properties = $propertyme->getAllProperties()['Rows'];
	$id = 'a8f600de-8742-4417-8056-4a487dd30a20';
	// $propertiesDetails = $propertyme->getPropertyDetails($id);
	// echo '<pre>'.print_r($propertiesDetails, TRUE).'</pre>';
	// die();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"> 
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>PropertyMe</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
</head>
<body>

<div class="container">
	<div class="row">
		<div class="col-md-6"><h1>Properties</h1></div>
	</div>
	<?php if(!isset($_GET['agency_id'])){?>
	<div class="row">
		<div class="col-md-12">
			<?php if(!empty($agencies)) {?>
			<h4>List of Agencies</h4>
			<table class="table table-hover table-striped table-condensed" style="margin-top:30px;">
			<thead>
				<tr>
					<th>Agency ID</th>
					<th>Agency Name</th>
					<th>Active in PM</th>
					<th>Inactive in PM</th>
					<th>Active in CRM</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<?php 
				foreach($agencies as $agency) {

					$propertyme->getAgencyDetails($agency['CustomerId']);
					$activePM = count($propertyme->getAllProperties()['Rows']);
					$includeInActive = count($propertyme->getAllProperties(FALSE)['Rows']);
					$inActivePM = $includeInActive - $activePM;

					$sql = "SELECT ps.`property_services_id`
								  FROM `property_services` AS ps 
								  LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id` 
								  LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
								  WHERE ps.`service` = 1 
								  AND ps.`alarm_job_type_id` = 2 
								  AND a.`propertyme_agency_id` = '".$agency['CustomerId']."'
								  AND p.`deleted` = 0";
					$sqlCount = mysql_query($sql);
					$rsCount = mysql_num_rows($sqlCount);
				?>
				<tr>
					<td><?=$agency['CustomerId']?></td>
					<td><?=$agency['CustomerCompanyName']?></td>
					<td><?=$activePM?></td>
					<td><?=$inActivePM?></td>
					<td><?=$rsCount?></td>
					<td><a href="<?=$_SERVER['REQUEST_URI']?>?agency_id=<?=$agency['CustomerId'].'**'.$agency['CustomerCompanyName']?>" class="btn btn-primary">View Properties</a></td>
				</tr>
				<?php }?>
			</tbody>
			</table>
			<?php } else {
				echo "No agencies found";
			} ?>
		</div>
	</div>
	<?php }?>

	<div class="row">
		<div class="col-md-6">
			<?php if(!empty($properties) AND isset($_GET['agency_id'])){?>
			<br />
			<h4>List of Properties under <strong><?=$agency_name?></strong> Agency</h4>
			<a href="test_propertyme_api.php">Back to Agency List</a>
			<br /><br />
			<br />
			<?php }?>
		</div>
	</div>
	<div class="row">
		<div class="col-md-7">
			<h3>PM</h3>
			<?php 
			if(!empty($properties) AND isset($_GET['agency_id'])){

				
			?>
			<table class="table table-hover table-striped table-condensed" style="margin-top:30px;" id="myTable">
				<thead>
					<tr>
						<th>Property ID</th>
						<th>Property Address</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					foreach($properties as $property){ 
						$propertiesDetails = $propertyme->getPropertyDetails($property['Id']);
					?>
					<tr>
						<td><?=$property['Id']?></td>
						<td><?=$propertiesDetails['AddressText']?></td>
					</tr>
					<?php }?>
				</tbody>
			</table>
			<?php } else {
				echo "";
			} ?>
		</div>
		<div class="col-md-5">
			<h3>CRM</h3>
			<?php
			$sql = "SELECT p.*
					FROM `property_services` AS ps 
					LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id` 
					LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
					WHERE ps.`service` = 1 
					AND ps.`alarm_job_type_id` = 2
					AND a.`propertyme_agency_id` = '".$agency_id."' 
					AND p.`deleted` = 0";
			$propertySQL = mysql_query($sql);
			?>
			<table class="table table-hover table-striped table-condensed" style="margin-top:30px;" id="table1">
				<thead>
					<tr>
						<th>Address</th>
						<th>Property ID</th>
					</tr>
				</thead>
				<tbody>
					<?php while($rs = mysql_fetch_array($propertySQL)){?>
					<tr>
						<td><?=$rs['address_1']." ".$rs['address_2']." ".$rs['address_3']." ".$rs['state']." ".$rs['postcode']?></td>
						<td><?=(empty($rs['propertyme_agency_id'])) ? '-' : $rs['propertyme_agency_id']?></td>
					</tr>
					<?php }?>
				</tbody>
			</table>
		</div>
	</div>
</div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
$(document).ready( function () {
    $('#myTable').DataTable({
    	"pageLength": 15,
    	"columns" : [
    		{"width" : "50%"},
    		{"width" : "50%"},
    	]
    });
    $('#table1').DataTable();
} );
</script>
</body>
</html>
