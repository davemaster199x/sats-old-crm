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
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"> 
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Test</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
</head>
<body>

<div class="container" style="width: 85% !important;">
	<div class="row">
		<div class="col-md-6"><h1>Search Address</h1></div>
	</div>
	<?php if(!isset($_GET['agency_id']) AND !empty($agencies)){?>
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
					<td><a href="<?=$_SERVER['REQUEST_URI']?>?agency_id=<?=$agency['CustomerId'].'**'.$agency['CustomerCompanyName']?>" class="btn btn-primary btn-sm">View Properties</a></td>
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

	<?php 
	if(isset($_GET['search'])){
		echo $_POST['searchText'];
	}
	?>


	<?php 
	if(!empty($properties) AND isset($_GET['agency_id'])){
	?>

	<div class="row">
		<div class="col-md-6">
			<br />
			<h4>List of Properties under <strong><?=$agency_name?></strong> Agency</h4>
			<a href="test_propertyme.php">Back to Agency List</a>
			<br />
		</div>
		<div class="col-md-3">
			<input type="text" class="form-control" onkeyup="propSearch(this.value)">
		</div>
	</div>

	<div id="searchResult"></div>
	

	<?php }?>
</div>

<!-- Modal -->
<form method="post" onsubmit="return confirm('Are you sure you want to save?')">

<div id="modalID" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Modal Header</h4>
      </div>
      <div class="modal-body">
      <input type="hidden" name="property_id" id="property_id">
          <div class="form-group">
		    <label for="propertyme_id">PropertyMe ID</label>
		    <input type="text" class="form-control" id="propertyme_id" name="propertyme_id" required>
		  </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary" name="btnsave">Save</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
</form>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">

function add_property_id(property_id, address)
{
	$('#modalID .modal-title').text('Add PropertyMe ID in ' + address);
	$('#property_id').val(property_id);
	$('#modalID').modal();
}

function propSearch(val)
{
	$.ajax({
		url: 'test_propertyme.php?search',
		type: 'post',
		data: {"searchText" : val},
		success: function(result){
			$('#searchResult').html(result);
		}, beforeSend: function() {
			$('#searchResult').html("<h3>Loading...</h3>");
		}
	});
}

$(document).ready( function () {

    $('#myTable').DataTable({
    	"pageLength": 15,
    	"columns" : [
    		{"width" : "50%"},
    		{"width" : "50%"},
    	]
    });
    $('#table1').DataTable({
    	"pageLength": 15
    });
    $('#agency_table').DataTable({
    	"pageLength": 15
    });
} );
</script>
</body>
</html>
