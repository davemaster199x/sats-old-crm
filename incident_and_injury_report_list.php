<?php

$title = "Incident Summary";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$search_staff = mysql_real_escape_string($_POST['search_staff']);
$search_phrase = mysql_real_escape_string($_POST['search_phrase']);
$country_id = $_SESSION['country_default'];

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


$jparams = array(
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	)
);
$tools_sql = $crm->getIncidentAndReport($jparams);
$ptotal = mysql_num_rows($crm->getIncidentAndReport());

?>

<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
#btn_add_vehicle{
	float: left; 
	position: relative; 
	bottom: 87px;
}
</style>
<div id="mainContent">    

	<div class="sats-middle-cont">
  
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="<?php echo $title; ?>" href="/incident_and_injury_report_list.php"><strong><?php echo $title; ?></strong></a></li>
		  </ul>
		</div>	
		<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
		 <?php
		if($_GET['success']==1){ ?>
			<div class="success">New Tools Added</div>
		<?php
		}
		?>
		
		 <?php
		if($_GET['add_success']==1){ ?>
			<div class="success">New vehicle added</div>
		<?php
		}
		?>
		
		
		<div class="aviw_drop-h" style="border: 1px solid #ccc;">
		<form id="form_search" method="post" style="visibility:hidden;">
			<div class="fl-left">
				<label style="margin-right: 9px;">Staff:</label>
				<select style="width: 125px;" name="search_staff">
					<option value="">----</option>	
					<?php
					$jparams = array(
						'distinct'=>'t.assign_to',
						'country_id' => $country_id
					);
					$staff_sql = $crm->getTools($jparams);	
					while($staff = mysql_fetch_array($staff_sql)){ ?>
						<option value="<?php echo $staff['StaffID']; ?>"><?php echo $crm->formatStaffName($staff['FirstName'],$staff['LastName']); ?></option>
					<?php	
					}
					?>
				</select>
			</div>
			
			<div class="fl-left">
				<label style="margin-right: 9px;">Search:</label>
				<input type="text" name="search_phrase" id="search_phrase" style="width: 100px" class="addinput" />
			</div>
			
			<div class="fl-left" style="float:left; margin-left: 10px;">
				<input type="submit" name="btn_submit" class="submitbtnImg" value="Go" />
			</div>	
		</form>
		</div>

		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;">
			<tr class="toprow jalign_left">				
				<th>Date of incident</th>
				<th>Time of incident</th>
				<th>Nature of incident</th>
				<th>Location of incident</th>
				<th>Describe the incident</th>
				<th>Injured Person</th>
				<th>PDF</th>
				<!--<th>Edit</th>-->
			</tr>
			<?php				
			if( mysql_num_rows($tools_sql)>0 ){
				while($t = mysql_fetch_array($tools_sql)){ ?>
					<tr class="body_tr jalign_left">
						<td>
							<span class="txt_lbl"><a href="/incident_and_injury_report_details.php?id=<?php echo $t['incident_and_injury_id']; ?>"><?php echo date('d/m/Y',strtotime($t['datetime_of_incident'])); ?></span>
							<input type="text" name="item_id" class="txt_hid item_id" value="<?php echo $t['item_id']; ?>" />						
						</td>
						<td>
							<span class="txt_lbl"><?php echo date('H:i',strtotime($t['datetime_of_incident'])); ?></span>
							<input type="text" name="item_id" class="txt_hid item_id" value="<?php echo $t['item_id']; ?>" />
						</td>	
						<td>
							<span class="txt_lbl">
							<?php
							switch($t['nature_of_incident']){
								case 1:
									$nature_of_incident2 = 'Near Miss';
								break;
								case 2:
									$nature_of_incident2 = 'First Aid';
								break;
								case 3:
									$nature_of_incident2 = 'Medical Treatment';
								break;
								case 4:
									$nature_of_incident2 = 'Car accident';
								break;
								case 5:
									$nature_of_incident2 = 'Property damage';
								break;
								case 6:
									$nature_of_incident2 = 'Incident report';
								break;
							}
							echo $nature_of_incident2;
							?>
							</span>
							<select name="item" class="txt_hid item">
								<option value="">----</option>							
							</select>
							<input type="hidden" name="tools_id" class="tools_id" value="<?php echo $t['tools_id']; ?>" />
						</td>
						<td>
							<span class="txt_lbl"><?php echo $t['location_of_incident']; ?></span>
							<input type="text" name="description" class="txt_hid description" value="" />
						</td>
						<td>
							<span class="txt_lbl"><?php echo $t['describe_incident']; ?></span>
							<input type="text" name="description" class="txt_hid description" value="" />
						</td>
						<td>
							<span class="txt_lbl"><?php echo $t['ip_name']; ?></span>
							<input type="text" name="description" class="txt_hid description" value="" />
						</td>
						<td>
							<a href="/incident_and_injury_report_pdf.php?id=<?php echo $t['incident_and_injury_id']; ?>">
								<img src="images/pdf.png" />
							</a>
						</td>
						<!--
						<td>			
							<button class="blue-btn submitbtnImg btn_update">Update</button>
							<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
							<button class="submitbtnImg btn_cancel" style="display:none;">Cancel</button>
						</td>	
						-->
					</tr>
				<?php
				}
			}else{ ?>
				<tr><td colspan="100%" align="left">Empty</td></tr>
			<?php	
			}
			?>			
		</table>
		
		<?php
									
		// Initiate pagination class
		$jp = new jPagination();
		
		$per_page = $limit;
		$page = ($_GET['page']!="")?$_GET['page']:1;
		$offset = ($_GET['offset']!="")?$_GET['offset']:0;	
		
		echo $jp->display($page,$ptotal,$per_page,$offset,$params);
		
		?>
	
		<a href="incident_and_injury_report.php">
			<button type="button" id="btn_add_vehicle" class="submitbtnImg">
				<img class="inner_icon" src="images/button_icons/add-button.png">
				Report
			</button>
		</a>
	
	</div>
	
</div>

<br class="clearfloat" />

<script>
jQuery(document).ready(function(){

	jQuery(".btn_edit").click(function(){
	
		jQuery(this).parents("tr:first").find(".btn_update").show();
		jQuery(this).parents("tr:first").find(".btn_edit").hide();
		jQuery(this).parents("tr:first").find(".btn_cancel").show();
		jQuery(this).parents("tr:first").find(".btn_delete").show();
		jQuery(this).parents("tr:first").find(".txt_hid").show();
		jQuery(this).parents("tr:first").find(".txt_lbl").hide();
	
	});	
	
	jQuery(".btn_cancel").click(function(){
		
		jQuery(this).parents("tr:first").find(".btn_update").hide();
		jQuery(this).parents("tr:first").find(".btn_edit").show();
		jQuery(this).parents("tr:first").find(".btn_cancel").hide();
		jQuery(this).parents("tr:first").find(".btn_delete").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".txt_hid").hide();	
		
	});
	
	jQuery(".btn_update").click(function(){
	
		var vehicles_id = jQuery(this).parents("tr:first").find(".vehicles_id").val();
		var make = jQuery(this).parents("tr:first").find(".make").val();
		var model = jQuery(this).parents("tr:first").find(".model").val();
		var plant_id = jQuery(this).parents("tr:first").find(".plant_id").val();
		var number_plate = jQuery(this).parents("tr:first").find(".number_plate").val();
		var staff_id = jQuery(this).parents("tr:first").find(".staff_id").val();
		var tech_vehicle = jQuery(this).parents("tr:first").find(".tech_vehicle").val();		
		var kms = jQuery(this).parents("tr:first").find(".kms").val();
		var next_service = jQuery(this).parents("tr:first").find(".next_service").val();
		var active = jQuery(this).parents("tr:first").find(".active").val();		
		var error = "";
		
		if(number_plate==""){
			error += "Number Plate is required";
		}
		
		if(error!=""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "test_ajax_update_tools.php",
				data: { 
					vehicles_id: vehicles_id,
					make: make,
					model: model,
					plant_id: plant_id,
					number_plate: number_plate,
					staff_id: staff_id,
					kms: kms,
					next_service: next_service,
					tech_vehicle: tech_vehicle,
					active: active
				}
			}).done(function( ret ) {
				//window.location="/view_vehicles.php?success=1";
			});				
			
		}		
		
	});
	
	jQuery(".btn_delete").click(function(){
	
		var vehicles_id = jQuery(this).parents("tr:first").find(".vehicles_id").val();
	
		if(confirm("Are you sure you want to delete")){
			jQuery.ajax({
				type: "POST",
				url: "test_ajax_delete_tools.php",
				data: { 
					vehicles_id: vehicles_id,
				}
			}).done(function( ret ){
				//window.location = "/view_vehicles.php";
			});	
		}
	});
	
	

});
</script>
</body>
</html>
