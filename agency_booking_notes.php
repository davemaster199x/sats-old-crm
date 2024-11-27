<?php
$title = "Agency Booking Notes";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');
//include('inc/ws_sms_class.php');

$crm = new Sats_Crm_Class;
//$crm->displaySession();

$current_page = $_SERVER['PHP_SELF'];

$user_type = $_SESSION['USER_DETAILS']['ClassID'];
$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$loggedin_staff_name = "{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}";
$country_id = $_SESSION['country_default'];
$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$agency_id = mysql_real_escape_string($_REQUEST['agency_id']);

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort={$sort}&order_by={$order_by}&phrase={$phrase}&agency_id={$agency_id}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;




function whoAddedIt($bn_id){
	
	$sql = mysql_query("
		SELECT *
		FROM `booking_notes_log` AS bnl
		LEFT JOIN `staff_accounts` AS st_ac ON bnl.`staff_id` = st_ac.`StaffID`
		WHERE bnl.`title` = 'Add Booking Notes'
		AND bnl.`booking_notes_id` = {$bn_id}
	");
	$row = mysql_fetch_array($sql);
	return Sats_Crm_Class::formatStaffName($row['FirstName'],$row['LastName']);
	
}



?>
<style>
#btn_add_div{
	text-align: left;
	margin-top: 10px;
}
#template_tbl th, #template_tbl td{
	text-align: left;
}
.colorItGreen{
	color: green;
}
.colorItRed{
	color: red;
}
.txt_hid, .btn_update_bn, .btn_cancel_bn, .btn_delete_bn{
	display:none;
}
</style>


    
    <div id="mainContent">
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title; ?></strong></a></li>				
		</ul>
	</div>
      
    
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	 <?php
	if($_GET['success']==1){ ?>
	
		<div class="success">Agency Booking Notes Added</div>
	
	<?php	
	}
	?>
	
	
   <?php
	if($_GET['booking_notes_updated']==1){ ?>
	
		<div class="success">Agency Booking Notes Updated</div>
	
	<?php	
	}
	?>
	
	<?php
	if($_GET['bn_del_success']==1){ ?>
	
		<div class="success">Agency Booking Notes Deleted</div>
	
	<?php	
	}
	?>
	
	
	
	
	<?php
	$agen_filt_params = array(
		'sort_list' => array(
			array(
				'order_by' => 'a.`agency_name`',
				'sort' => 'ASC'
			)
		),
		'distinct_sql' => ' a.`agency_id`, a.`agency_name` ',
		'echo_query' => 0,
		'country_id' => $country_id
	);
	$agen_filt_sql = $crm->getBookingNotes($agen_filt_params);
	while($adh = mysql_fetch_array($agen_filt_sql)){ ?>
	
	
		<h2 class="heading"><?php echo $adh['agency_name'] ?></h2>
		
		<table id="template_tbl" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;">
			<tr class="toprow jalign_left">				
				<th style="width: 60%">Notes</th>
				<th style="width: 20%;">Agency</th>
				<th>Added By</th>
				<th>Edit</th>
			</tr>
			<?php	
			// list
			$list_params = array(				
				'sort_list' => array(
					array(
						'order_by' => 'a.`agency_name`',
						'sort' => 'ASC'
					)
				),
				'echo_query' => 0,
				'country_id' => $country_id,
				'phrase' => $phrase,
				'agency_id' => $adh['agency_id']
			);
			$a_sql = $crm->getBookingNotes($list_params);
			if( mysql_num_rows($a_sql)>0 ){
				$i = 0;
				while( $row = mysql_fetch_array($a_sql) ){ 
				?>
					<tr class="body_tr jalign_left" <?php echo ($i%2==0)?'':'style="background-color:#eeeeee"'; ?>>										
						<td>
							<span class="txt_lbl"><?php echo $row['notes']; ?></span>
							<input type="text" class="txt_hid bn_notes" value="<?php echo $row['notes']; ?>" />
						</td>
						<td>
							<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
							<a href="<?php echo $ci_link; ?>">
								<?php echo $row['agency_name']; ?>
							</a>
						</td>
						<td><?php echo whoAddedIt($row['booking_notes_id']); ?></td>
						<td>
							<input type="hidden" class="bn_id" value="<?php echo $row['booking_notes_id']; ?>" />
							<button type="button" class="blue-btn submitbtnImg btn_update_bn" style="margin-bottom: 5px;">Update</button>
							<a href="javascript:void(0);" class="btn_del_vf btn_edit_bn">Edit</a>
							<button type="button" class="submitbtnImg btn_cancel_bn">Cancel</button>
							<button type="button" class="blue-btn submitbtnImg btn_delete_bn">Delete</button>
						</td>
					</tr>
				<?php
				$i++;
				}
			}else{ ?>
				<tr><td colspan="100%" align="left">Empty</td></tr>
			<?php	
			}
			?>			
		</table>
	
	<?php
	}
	?>
	
	

	<div class="row addproperty">

		<button class="addinput submitbtnImg" id="btn-add_agency_booking_notes" type="button" style="width: auto; margin: 20px 0;">
			<img class="inner_icon" src="images/add-button.png">
			Booking Notes
		</button>
		
		<form id="agency_booking_notes_form" action="add_agency_booking_notes.php" method="POST">
		<div id="agency_booking_notes_div" style="display:none; clear: both;">			
			<div class="row">
				<label class="addlabel">Agency</label>
				<select class="addinput agency_id" name='agency_id' id='agency_id' style="width: auto; margin: 10px 0;">
					<option value="">--- Select ---</option>
					<?php
					$agen_filt_params = array(
						'status' => 'active',
						'country_id' => $country_id,
						'sort_list' => array(
							array(
								'order_by' => 'a.`agency_name`',
								'sort' => 'ASC'
							)
						),
						'display_echo' => 0
					);
					$agen_sql = $crm->getAgency($agen_filt_params);
					while( $agen = mysql_fetch_array($agen_sql) ){ ?>
						<option value="<?php echo $agen['agency_id'] ?>"><?php echo $agen['agency_name']; ?></option>
					<?php
					}
					?>
					
				</select>
			</div>
			<div style="clear:both;"></div>
			<div class="row">
				<label class="addlabel">Notes</label>
				<input class="addinput agency_booking_notes" style="width: 80%; margin: 0;" name='agency_booking_notes' id='agency_booking_notes' type="text" placeholder="Enter Agency Booking Notes" />
			</div>
			<div style="clear:both;"></div>
			<div class="row">
				<button class="addinput submitbtnImg eagdtbt blue-btn btn-save_booking_note" id="btn-save_booking_note" type="submit">
					<img class="inner_icon" src="images/save-button.png">
					Save Note
				</button>
			</div>
		</div>
		</form>
		
	</div>
	
	
	
	
    
  </div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	
	jQuery("#agency_booking_notes_form").submit(function(){
		
		var agency_booking_notes = jQuery("#agency_booking_notes").val();
		var agency_id = jQuery("#agency_id").val();
		var error = "";
		
		if( agency_booking_notes == "" ){
			error += "Agency booking note is required\n";
		}
		
		if( agency_id == "" ){
			error += "Agency is required\n";
		}
		
		if( error != '' ){
			
			alert(error);
			return false;
			
		}else{
			
			return true;	
		
		}
		
		
		
	});
	
	
	
	jQuery("#btn-add_agency_booking_notes").click(function(){
		
		jQuery("#agency_booking_notes_div").toggle();
		
	});
	
	
	jQuery(".btn_edit_bn").click(function(){
	
		jQuery(this).parents("tr:first").find(".btn_update_bn").show();
		jQuery(this).parents("tr:first").find(".btn_edit_bn").hide();
		jQuery(this).parents("tr:first").find(".btn_cancel_bn").show();
		jQuery(this).parents("tr:first").find(".btn_delete_bn").show();
		jQuery(this).parents("tr:first").find(".txt_hid").show();
		jQuery(this).parents("tr:first").find(".txt_lbl").hide();
	
	});	
	
	jQuery(".btn_cancel_bn").click(function(){
		
		jQuery(this).parents("tr:first").find(".btn_update_bn").hide();
		jQuery(this).parents("tr:first").find(".btn_edit_bn").show();
		jQuery(this).parents("tr:first").find(".btn_cancel_bn").hide();
		jQuery(this).parents("tr:first").find(".btn_delete_bn").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".txt_hid").hide();	
		
	});
	
	jQuery(".btn_update_bn").click(function(){
	
		var bn_id = jQuery(this).parents("tr:first").find(".bn_id").val();
		var bn_notes = jQuery(this).parents("tr:first").find(".bn_notes").val();
		var error = '';
		
		if(bn_notes==""){
			error += "Notes is required";
		}
		
		if(error!=""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_booking_notes.php",
				data: { 
					bn_id: bn_id,
					bn_notes: bn_notes
				}
			}).done(function( ret ) {
				window.location = "agency_booking_notes.php?booking_notes_updated=1";
			});				
			
		}		
		
	});
	
	jQuery(".btn_delete_bn").click(function(){
	
		var bn_id = jQuery(this).parents("tr:first").find(".bn_id").val();
	
		if(confirm("Are you sure you want to delete booking notes?")){
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_booking_notes.php",
				data: { 
					bn_id: bn_id
				}
			}).done(function( ret ){
				window.location = "agency_booking_notes.php?bn_del_success=1";
			});	
		}
	});
	
	
});
</script>
</body>
</html>
