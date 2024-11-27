<?php
$title = "Sales Snapshot";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');


$crm = new Sats_Crm_Class;

// get Agency
$aprams = array(
	'country_id' => $_SESSION['country_default'],
	'sort_list' => array(
		array(
			'order_by' => 'a.`agency_name`',
			'sort' => 'ASC'
		)
	)
);
$a_sql = $crm->getAgency($aprams);

$a_arr = [];
while( $a = mysql_fetch_array($a_sql) ){
	$a_arr[] = array(
		'agency_id' => $a['agency_id'],
		'agency_name' => "{$a['agency_name']} ({$a['status']})"
	);
}

/*
echo "<pre>";
echo print_r($a_arr);
echo "</pre>";
*/

?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}

.green_check{
	display:none;
	width: 30px;
	margin-top: 5px;
}
.sales_rep_elem{
	display: none;
}
</style>
<div id="mainContent">

	
   
    <div class="sats-middle-cont">
	
		
	
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="Sales Snapshot" href="/sales_snapshot.php"><strong>Sales Snapshot</strong></a></li>
		  </ul>
		</div>
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Opportunity Successfully Added</div>';
		}else if($_GET['success']==2){
			echo '<div class="success">Opportunity Successfully Updated</div>';
		}else if($_GET['success']==3){
			echo '<div class="success">Sales Rep Successfully Updated</div>';
		}else if($_GET['success']==4){
			echo '<div class="success">Update Successfull</div>';
		}
		
		
		?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		
		<div style="border: 1px solid #cccccc;" class="aviw_drop-h">		 
			<div class="fl-left">
				<a href="export_sales_snapshot.php"><button class="submitbtnImg" type="button">Export</button></a>
			</div>	
		</div>
		

				<?php
				$ss_sr_sql = mysql_query("
					SELECT DISTINCT (
					ss.`sales_snapshot_sales_rep_id`
					), ss.`sales_snapshot_sales_rep_id` , ss_sr.`first_name` , ss_sr.`last_name`
					FROM `sales_snapshot` AS ss
					LEFT JOIN `sales_snapshot_sales_rep` AS ss_sr ON ss.`sales_snapshot_sales_rep_id` = ss_sr.`sales_snapshot_sales_rep_id`
					WHERE ss_sr.`country_id` ={$_SESSION['country_default']}
					ORDER BY ss_sr.`first_name` ASC
				");
				
				$salerep_arr = [];
				while($ss_sr = mysql_fetch_array($ss_sr_sql)){
				$salerep_arr[] = $ss_sr['sales_snapshot_sales_rep_id'];
				?>
				
					<h2 class="heading"><?php echo "{$ss_sr['first_name']} {$ss_sr['last_name']}"; ?></h2>
					<input type="hidden" class="ss_sales_rep_id" value="<?php echo $ss_sr['sales_snapshot_sales_rep_id']; ?>" />
					<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd jtable" style="margin-top: 0px; margin-bottom: 13px;">
						<tr class="toprow jalign_left">
							<th class="sales_rep_elem">Salesrep</th>
							<th style="width: 100px;">Date</th>
							<th style="width: 280px;">Agency</th>
							<th style="width: 100px;">Properties</th>
							<th style="width: 100px;"><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?></th>
							<th style="width: 100px;">Status</th>
							<th style="width: auto;">Details</th>					
							<th style="width: 1px;">Edit</th>
						</tr>
							<?php
							
							$sql = mysql_query("
								SELECT *, ss_s.`name` AS status_name, ss.`sales_snapshot_status_id` AS ss_status_id
								FROM `sales_snapshot` AS ss
								LEFT JOIN `agency` AS a ON ss.`agency_id` = a.`agency_id`
								LEFT JOIN `postcode_regions` AS pr ON a.`postcode_region_id` = pr.`postcode_region_id`
								LEFT JOIN `sales_snapshot_status` AS ss_s ON ss.`sales_snapshot_status_id` = ss_s.`sales_snapshot_status_id`
								LEFT JOIN `sales_snapshot_sales_rep` AS ss_sr ON ss.`sales_snapshot_sales_rep_id` = ss_sr.`sales_snapshot_sales_rep_id`
								WHERE ss.`country_id` = {$_SESSION['country_default']}
								AND ss_sr.`sales_snapshot_sales_rep_id` = {$ss_sr['sales_snapshot_sales_rep_id']}
								ORDER BY ss.`date` DESC
							");
							
							
							if(mysql_num_rows($sql)>0){
								$total = 0;
								while($row = mysql_fetch_array($sql)){
							?>
									<tr class="body_tr jalign_left">
										<td class="sales_rep_elem">
											<span class="txt_lbl"><?php echo "{$ss_sr['first_name']} {$ss_sr['last_name']}"; ?></span>
											<span class="txt_hid">
												<select class="sales_rep">
													<option value="">----</option>
													<?php
													$ss_sr_sql2 = mysql_query("
														SELECT *
														FROM `sales_snapshot_sales_rep`
														WHERE `country_id` = {$_SESSION['country_default']}
													");
													while($ss_sr2 = mysql_fetch_array($ss_sr_sql2)){ ?>
														<option value="<?php echo $ss_sr2['sales_snapshot_sales_rep_id']; ?>" <?php echo ( $ss_sr2['sales_snapshot_sales_rep_id'] == $ss_sr['sales_snapshot_sales_rep_id'] )?'selected="selected"':''; ?>><?php echo $ss_sr2['first_name'].' '.$ss_sr2['last_name']; ?></option>
													<?php
													}
													?>						
												</select>
												<input type="hidden" class="sales_rep_updated" value="0" />
											</span>
										</td>
										<td>
											<?php $date = ($row['date']!="")?date("d/m/Y",strtotime($row['date'])):''; ?>
											<span class="txt_lbl"><?php echo $date; ?></span>
											<input type="text" class="txt_hid date" value="<?php echo $date; ?>" readonly="readonly" />
										</td>				
										<td>
											<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
											<span class="txt_lbl"><a href="<?php echo $ci_link ?>"><?php echo $row['agency_name']; ?></a></span>
											<select name="agency_id" class="txt_hid agency_id" >
												<option value="">----</option>
												<?php							
												foreach( $a_arr as $a_row ){ ?>
													<option value="<?php echo $a_row['agency_id']; ?>" <?php echo ($a_row['agency_id']==$row['agency_id'])?'selected="selected"':''; ?>><?php echo $a_row['agency_name']; ?></option>
												<?php
												}
												?>						
											</select>
											<input type="hidden" class="sales_snapshot_id" value="<?php echo $row['sales_snapshot_id']; ?>" />
										</td>
										<td>
											<span class="txt_lbl"><?php echo $row['properties']; ?></span>
											<input type="text" class="txt_hid properties" value="<?php echo $row['properties']; ?>" />
											<?php $total += $row['properties']; ?>
										</td>
										<td>
											<span class="txt_lbl"><?php echo ( $row['postcode_region_id']!="" )?$row['postcode_region_name']:''; ?></span>
											<input type="text" class="txt_hid" value="<?php echo $row['postcode_region_name']; ?>" readonly="readonly" />
										</td>
										<td>
											<span class="txt_lbl"><?php echo $row['status_name']; ?></span>					
											<select class="txt_hid status">
												<?php
												$ss_s_sql = mysql_query("
													SELECT *
													FROM `sales_snapshot_status`
												");
												while($ss_s = mysql_fetch_array($ss_s_sql)){ ?>
													<option value="<?php echo $ss_s['sales_snapshot_status_id']; ?>" <?php echo ($ss_s['sales_snapshot_status_id']==$row['ss_status_id'])?'selected="selected"':''; ?>><?php echo $ss_s['name']; ?></option>
												<?php
												}
												?>						
											</select>
										</td>
										<td>
											<span class="txt_lbl"><?php echo $row['details']; ?></span>
											<input type="text" class="txt_hid details" value="<?php echo $row['details']; ?>" />
											<img id="escalate_green_check"  class="green_check" style="display:none;" src="/images/check_icon2.png" />
										</td>								
										<td>
											<button class="blue-btn submitbtnImg btn_update">Update</button>
											<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
											<button class="submitbtnImg btn_cancel" style="display:none;">Cancel</button>
											<button class="blue-btn submitbtnImg btn_delete" style="display:none;">Delete</button>
										</td>
									</tr>
							<?php
								}
							}else{ ?>
								<td colspan="7" align="left">Empty</td>
							<?php
							}
							?>
							<tr class="body_tr jalign_left">
								<td><strong>TOTAL</strong></td>		
								<td>&nbsp;</td>
								<td colspan="5"><?php echo $total; ?></td>
							</tr>
					</table>
				
				<?php	
				}
				
				?>
				
				
				
		
		
			

		<div class="jalign_left">
		
			<button type="button" id="btn_add_opportunity" class="submitbtnImg">Add Opportunity</button>
			
            <div style="padding-top: 20px;" id="div_staff" class="addproperty formholder">
				<form id="form_sales_snapshot" method="post" action="/sales_snapshot_script.php" style="display:none;">
					<div class="row">
						<label class="addlabel" for="agency_id">Agency</label>
						<select name="agency_id">
							<option value="">----</option>
							<?php							
							foreach( $a_arr as $a_row ){ ?>
								<option value="<?php echo $a_row['agency_id']; ?>"><?php echo $a_row['agency_name']; ?></option>
							<?php
							}
							?>						
						</select>
					</div>         			
					<div class="row">
						<label class="addlabel" for="title">Properties</label>
						<input type="text" name="properties" id="properties" class="properties">
					</div>

					<div class="row">
						<label class="addlabel" for="title">Status</label>
						<select name="status">
							<option value="">----</option>
							<?php
							$ss_s_sql = mysql_query("
								SELECT *
								FROM `sales_snapshot_status`								
							");
							while($ss_s = mysql_fetch_array($ss_s_sql)){ ?>
								<option value="<?php echo $ss_s['sales_snapshot_status_id']; ?>"><?php echo $ss_s['name']; ?></option>
							<?php
							}
							?>						
						</select>
					</div>
					<div class="row">
						<label class="addlabel" for="title">Sales Rep</label>
						<select name="sales_rep">
							<option value="">----</option>
							<?php
							$ss_sr_sql = mysql_query("
								SELECT *
								FROM `sales_snapshot_sales_rep`
								WHERE `country_id` = {$_SESSION['country_default']}
							");
							while($ss_sr = mysql_fetch_array($ss_sr_sql)){ ?>
								<option value="<?php echo $ss_sr['sales_snapshot_sales_rep_id']; ?>"><?php echo $ss_sr['first_name'].' '.$ss_sr['last_name']; ?></option>
							<?php
							}
							?>						
						</select>
					</div>
					<div class="row">
						<label class="addlabel" for="title">Details</label>
						<textarea name="details" id="details" class="details"></textarea>
					</div>
					<div class="row">
						<label class="addlabel" for="title">Insert Agency log</label>
						<input type="checkbox" name="insert_agency_log" id="insert_agency_log" class="insert_agency_log" value="1" style="width: auto; margin-top: 6px;" />
					</div>
					
					<div style="padding-top: 15px; text-align:left;" class="row clear">
						<input type="submit" class="submitbtnImg" style="width: auto; margin-bottom: 50px;" name="btn_submit" value="Submit" />
					</div>
				</form>
			</div>			
			
		</div>
			
				
	
		
		<div class="jalign_left">
		
			<button type="button" id="btn_add_edit_sales_rep" class="submitbtnImg blue-btn">Add/Edit Sales Rep</button>
					
			<div id="sales_rep_div" style="display:none;">
			
				<?php
				$ss_sr_sql = mysql_query("
					SELECT *
					FROM `sales_snapshot_sales_rep`
					WHERE `country_id` = {$_SESSION['country_default']}
				");
				if(mysql_num_rows($ss_sr_sql)>0){ ?>
					<form method="post" action="/update_sales_snapshot_sales_rep.php">
                    <style>#pm_table tr td{ border: 1px solid transparent;}</style>
						<table id="pm_table" style="width: auto; margin-top: 15px; margin-bottom: 15px;">
							<tbody>
							<?php						
							while($ss_sr = mysql_fetch_array($ss_sr_sql)){ ?>
								<tr>
									<td>
										<input type="hidden" class="ss_sr_id" name="ss_sr_id[]"  value="<?php echo $ss_sr['sales_snapshot_sales_rep_id']; ?>">
										<input type="text" class="fname pm_name" name="edit_fname[]" value="<?php echo $ss_sr['first_name']; ?>">
										<input type="text" class="fname pm_name" name="edit_lname[]" value="<?php echo $ss_sr['last_name']; ?>">
									</td>
									<td>
										<button type="button" class="submitbtnImg btn_del_sr">X</button>
									</td>
								</tr>
							<?php
							}
							?>										
							</tbody>	
						</table>
						<?php 
						if(mysql_num_rows($ss_sr_sql)>0){ ?>
							<input type="submit" class="submitbtnImg blue-btn" style="width: auto; margin-bottom: 50px;" name="btn_update_sr" value="Update" />
						<?php
						}
						?>					
					</form>
				<?php
				}else{
					echo '<div class="jalign_left" style="margin: 17px 0;">Empty</div>';
				}
				?>		

				<br />

				<button type="button" id="btn_add_sales_rep" class="submitbtnImg">Add Sales Rep</button>
				
				<div style="padding-top: 20px;" id="div_staff" class="addproperty formholder">
					<form id="form_sales_rep" method="post" action="/add_sales_snapshot_sales_rep.php" style="display:none;">
						<div class="row">
							<label class="addlabel" for="title">First Name</label>
							<input type="text" name="fname" id="fname" class="fname">
						</div>         			
						<div class="row">
							<label class="addlabel" for="title">Last Name</label>
							<input type="text" name="lname" id="lname" class="fname">
						</div>
						<div style="padding-top: 15px; text-align:left;" class="row clear">
							<input type="submit" class="submitbtnImg" style="width: auto; margin-bottom: 50px;" name="btn_submit" value="Submit" />
						</div>
					</form>
				</div>
				
			</div>
			
		</div>
		
		
	</div>
</div>

<br class="clearfloat" />

<script>
jQuery(document).ready(function(){	


	// if page ready
	jQuery("#load-screen").hide();
	

	function is_numeric(num){
		if(num.match( /^\d+([\.,]\d+)?$/)==null){
			return false
		}
	}

	function validate_email(email){
		var atpos = email.indexOf("@");
		var dotpos = email.lastIndexOf(".");
		if ( atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length ){
		  return false
		}
	}
		
	
	// opportunity validation check
	jQuery("#form_sales_snapshot").submit(function(event){
		
		var agency_id = jQuery("#agency_id").val();		
		var properties = jQuery("#properties").val();
		var error = "";
		
		if(agency_id==""){
			error += "Agency is required\n";
		}
		
		if(properties!="" && is_numeric(properties)==false){
			error += "Properties must be numeric\n";
		}
				
		if(error!=""){
			alert(error);
			return false;
		}else{
			return true;
		}
	});

	// sales rep validation check
	jQuery("#form_sales_rep").submit(function(event){
		
		var fname = jQuery("#fname").val();		
		var lname = jQuery("#lname").val();
		var error = "";
		
		if(fname==""){
			error += "Sales Rep first name is required\n";
		}
		
		if(lname==""){
			error += "Sales Rep last name is required\n";
		}
				
		if(error!=""){
			alert(error);
			return false;
		}else{
			return true;
		}
	});

	jQuery(".btn_edit").click(function(){
	
		jQuery(this).parents("tr:first").find(".btn_update").show();
		jQuery(this).parents("tr:first").find(".btn_edit").hide();
		jQuery(this).parents("tr:first").find(".btn_cancel").show();
		jQuery(this).parents("tr:first").find(".btn_delete").show();
		jQuery(this).parents("tr:first").find(".txt_hid").show();
		jQuery(this).parents("tr:first").find(".txt_lbl").hide();
		jQuery(".sales_rep_elem").show();
	
	});	
	
	jQuery(".btn_cancel").click(function(){
		
		jQuery(this).parents("tr:first").find(".btn_update").hide();
		jQuery(this).parents("tr:first").find(".btn_edit").show();
		jQuery(this).parents("tr:first").find(".btn_cancel").hide();
		jQuery(this).parents("tr:first").find(".btn_delete").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".txt_hid").hide();	
		jQuery(".sales_rep_elem").hide();
		
	});
	
	
	jQuery(".sales_rep").change(function(){
		
		jQuery(this).parents("tr:first").find(".sales_rep_updated").val(1);
		
	});
	
	
	jQuery(".btn_update").click(function(){
	
		var obj = jQuery(this);
		var sales_snapshot_id = obj.parents("tr:first").find(".sales_snapshot_id").val();
		var sales_rep = obj.parents("tr:first").find(".sales_rep").val();
		var sales_rep_updated = obj.parents("tr:first").find(".sales_rep_updated").val();
		var agency_id = obj.parents("tr:first").find(".agency_id").val();
		var properties = obj.parents("tr:first").find(".properties").val();
		var area = obj.parents("tr:first").find(".area").val();
		var status = obj.parents("tr:first").find(".status").val();		
		var details = obj.parents("tr:first").find(".details").val();
		var date = obj.parents("tr:first").find(".date").val();
		var error = "";
		
		if(agency_id==""){
			error += "Update Agency field is required\n";
		}
		
		if(properties!="" && is_numeric(properties)==false){
			error += "Update Rate field must be numeric\n";
		}
		
		
		if(error!=""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_sales_snapshot.php",
				data: { 
					sales_snapshot_id: sales_snapshot_id,
					sales_rep: sales_rep,
					agency_id: agency_id,
					properties: properties,
					area: area,
					status: status,
					details: details,
					date: date
				}
			}).done(function( ret ) {
				obj.parents("tr:first").find(".green_check").show();
				if( parseInt(sales_rep_updated) == 1 ){
					window.location="/sales_snapshot.php?success=4";
				}
				
			});				
			
		}		
		
	});

	// delete opportunity
	jQuery(".btn_delete").click(function(){
		if(confirm("Are you sure you want to delete?")){
			var sales_snapshot_id = jQuery(this).parents("tr:first").find(".sales_snapshot_id").val();
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_snapshot.php",
				data: { 
					sales_snapshot_id: sales_snapshot_id
				}
			}).done(function( ret ) {	
				window.location.reload();
			});	
		}				
	});
	
	// delete salesrep
	jQuery(".btn_del_sr").click(function(){
		
		// get current salesrep with opportunity
		var current_salesrep = [];
		var i = 0;
		jQuery(".ss_sales_rep_id").each(function(){			
			current_salesrep[i] = parseInt(jQuery(this).val());
			i++;
		});
		
		// salesrep id
		var ss_sr_id = parseInt(jQuery(this).parents("tr:first").find(".ss_sr_id").val());
		
		// prevent deleting salesrep with active opportunity
		if( jQuery.inArray( ss_sr_id, current_salesrep )>-1 ){
			alert("Cannot Delete Salesrep with Active Opportunities");
		}else{
			if(confirm("Are you sure you want to delete?")){
				jQuery.ajax({
					type: "POST",
					url: "ajax_delete_snapshot_sales_rep.php",
					data: { 
						ss_sr_id: ss_sr_id
					}
				}).done(function( ret ) {	
					window.location.reload();
				});	
			}
		}
		
	});

	//  opportunity show/hide form toggle
	jQuery("#btn_add_opportunity").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#form_sales_snapshot").slideDown();
	},function(){
		jQuery(this).html("Add Opportunity");		
		jQuery("#form_sales_snapshot").slideUp();
	});
	
	
	// main sales rep show/hide form toggle
	jQuery("#btn_add_edit_sales_rep").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#sales_rep_div").slideDown();
	},function(){
		jQuery(this).html("Add/Edit Sales Rep");		
		jQuery("#sales_rep_div").slideUp();
	});
	
	// sales rep show/hide form toggle
	jQuery("#btn_add_sales_rep").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#form_sales_rep").slideDown();
	},function(){
		jQuery(this).html("Add Sales Rep");		
		jQuery("#form_sales_rep").slideUp();
	});
	
	
	
	
	
});
</script>
</body>
</html>