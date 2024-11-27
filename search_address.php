<?php
include('inc/init.php');
$propertyme = new Propertyme_api();
$agencies = $propertyme->getAgencies();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"> 
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Search Address</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
</head>
<body>

<div class="container" style="width: 85% !important;">
	<div class="row">
		<div class="col-md-6"><h1>Search Address</h1></div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<?php if(!empty($agencies)) {?>
			<h4>List of Agencies</h4>
			<table class="table table-hover table-striped table-condensed" style="margin-top:30px;" id="agency_table">
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

					$sqlPM = "SELECT count(p.`property_id`) as pcount
							FROM `property` AS p
							LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
							WHERE a.`propertyme_agency_id` = '".$agency['CustomerId']."'
							AND p.`deleted` = 0";
					$sqlCount = mysql_query($sqlPM);
					$rsPM = mysql_fetch_array($sqlCount);
				?>
				<tr>
					<td><?=$agency['CustomerId']?></td>
					<td><?=$agency['CustomerCompanyName']?></td>
					<td><?=$activePM?></td>
					<td><?=$inActivePM?></td>
					<td><?=$rsPM['pcount']?></td>
					<td><a href="search_address_result.php?agency_id=<?=$agency['CustomerId'].'**'.$agency['CustomerCompanyName']?>" class="btn btn-primary btn-sm">View Properties</a></td>
				</tr>
				<?php }?>
			</tbody>
			</table>
			<?php } else {
				echo "No agencies found";
			} ?>
		</div>
	</div>
</div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">

$(document).ready( function () {

	$('#agency_table').DataTable({
		"pageLength": 15
	});
	
} );

</script>
</body>
</html>
