<?php
include('inc/init.php');

$propertyme = new Propertyme_api();
$sats_query = new Sats_query();


$agency_id = explode("**", $_GET['agency_id'])[0];
$agency_name = explode("**", $_GET['agency_id'])[1];

// switch to new agency
$propertyme->getAgencyDetails($agency_id);
$properties = $propertyme->getAllProperties()['Rows'];


if(isset($_POST['btnsave'])){


	if(!empty($_POST['chkProperty'])) {

		foreach($_POST['chkProperty'] as $key => $value) {

			if($_POST['chkProperty'][$key] != "") {

				// echo $_POST['lastname'][$_POST['chk'][$key]]."<br />";

				$k = $_POST['chkProperty'][$key];
				$property_id = intval($_POST['searchCRM'][$k]); 

				$sql = mysql_query("SELECT * FROM `property` WHERE `property_id`=".$property_id.' AND `deleted` = 0');
				$rs = mysql_fetch_array($sql);
				$address = $rs['address_1']." ".$rs['address_2']." ".$rs['address_3']." ".$rs['state']." ".$rs['postcode'];

				$data['propertyme_prop_id'] = mysql_real_escape_string($_POST['propertymeid'][$k]);
				$where['property_id'] = $property_id;
				$result = $sats_query->dbUpdate('property', $data, $where);

				

				if($result){
					$_SESSION['success']['message'][] = "Property <strong>".$address."</strong> has been updated.";
				} else {
					$_SESSION['error']['message'][] = "Their's an error in updating property.";
				}
			
			}

		}

	}




}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"> 
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Search Address</title>

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css">
	<link rel="stylesheet" href="https://select2.github.io/select2-bootstrap-theme/css/gh-pages.css">
	<style type="text/css">
	.row-highlighted {
		background: #fcf8e3 !important;
		color: #8a6d3b !important;
	}
	</style>
</head>
<body>

<div class="container" style="width: 85% !important;">
	<div class="row">
		<div class="col-md-6"><h1>Search Address</h1></div>
	</div>

	<?= isset($_SESSION['error']['message']) ? '<div class="alert alert-danger alert-dismissible" role="alert"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>' . implode("<br />", $_SESSION['error']['message']) . '</div>' : "" ?>
	<?= isset($_SESSION['success']['message']) ? '<div class="alert alert-success alert-dismissible" role="alert"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>' . implode("<br />", $_SESSION['success']['message']) . '</div>' : "" ?>
	<?php 
	unset($_SESSION['error']['message']);
	unset($_SESSION['success']['message']);
	?>

	<?php 
	if(!empty($properties)){
	?>

	<div class="row">
		<div class="col-md-6">
			<br />
			<h4>List of Properties under <strong><?=$agency_name?></strong> Agency</h4>
			<a href="search_address.php">Back to Agency List</a>
			<br />
		</div>
	</div>


	<div class="row">
		<div class="col-md-12">
			<div id="searchResult" style="margin-top:20px;"></div>
		</div>
	</div>
	<?php }?>
</div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.js"></script>
<script type="text/javascript">

$(document).ready( function () {


var search_text = $('#search_property').val();
var percentage = $('#percentage').val();

$.ajax({
	url: 'ajax_search_propertyme.php',
	type: 'post',
	data: {"agency_id" : "<?=$agency_id?>"},
	success: function(data) {
		$('#searchResult').html(data);
	}, beforeSend: function() {
		$('#searchResult').html("<br /><br /><br /><center><img src='https://cdn-images-1.medium.com/max/1600/1*9EBHIOzhE1XfMYoKz1JcsQ.gif'><br />Please wait while addresses are trying to match...</center>");
	}
});




} );

</script>
</body>
</html>
