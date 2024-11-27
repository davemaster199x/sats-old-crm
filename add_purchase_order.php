<?php
$title = "Add Purchase Order";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');



$crm = new Sats_Crm_Class();




?>
<style>
.addproperty input, .addproperty select {
    width: 30%;
}
.addproperty label {
   width: 230px;
}
.addtextarea{
	margin: 0;
}
.handyman_div{
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
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Submission Successful</div>';
		}
		
		
		?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		<form action="add_purchase_order_script.php" method="post" id="jform" style="font-size: 14px; margin-top: 30px;">
			<div class="addproperty">
			
				<?php
				$pon_sql = $crm->getPurchaseOrderLastIDNumber();
				$pon = mysql_fetch_array($pon_sql);
				?>
				<div class="row">
					<label class="addlabel">Purchase Order No.</label>
					<input type="text"  class="addinput" name="purchase_order_num" value="<?php echo str_pad($pon['purchase_order_num']+1, 7, '0', STR_PAD_LEFT);  ?>" style="width:400px;" />
				</div>

				<div class="row">
					<label class="addlabel">Date</label>
					<input type="text"  class="addinput datepicker" name="date" value="<?php echo date('d/m/Y'); ?>" style="width:400px;" />
				</div>
				
				
				<div class="row">
					<label class="addlabel">Supplier Name</label>
					<select name="supplier" id="supplier" style="width:400px;">
						<option value="">----</option>	
						<?php
						$params = array(
							'country_id' => $_SESSION['country_default'],
							'sort_list' => array(
								'order_by' => '`company_name`',
								'sort' => 'ASC'
							)
						);
						$sup_sql = $crm->getSupplier($params);
						while( $sup = mysql_fetch_array($sup_sql) ){ ?>
							<option value="<?php echo $sup['suppliers_id']; ?>" <?php echo ($sup['suppliers_id'] == $crm->getDynamicHandyManID())?'style="color:red;"':'' ?>><?php echo $sup['company_name']; ?></option>
						<?php	
						}
						?>
					</select>	
					<input type="hidden" name="supplier_name" id="supplier_name" />
				</div>
				
				<div class="row">
					<label class="addlabel">Supplier Address</label>
					<input type="text"  class="addinput" name="supplier_address" id="supplier_address" readonly="readonly" style="width:400px" />
				</div>
				
				<div class="row">
					<label class="addlabel">Supplier Email</label>
					<input type="text"  class="addinput" name="supplier_email" id="supplier_email" readonly="readonly" style="width:400px" />
				</div>
				
				<div class="handyman_div">
					<div class="row">
						<label class="addlabel">Agency</label>
						<select name="agency" id="agency" style="width:400px;">
							<option value="">----</option>	
							<?php
							$params = array(
								'status' => 'active',
								'country_id' => $_SESSION['country_default'],
								'sort_list' => array(
									array(
										'order_by' => '`agency_name`',
										'sort' => 'ASC'
									)
								)
							);
							$agen_sql = $crm->getAgency($params);
							while( $agen = mysql_fetch_array($agen_sql) ){ ?>
								<option value="<?php echo $agen['agency_id']; ?>"><?php echo $agen['agency_name']; ?></option>
							<?php	
							}
							?>
						</select>	
						<input type="hidden" name="supplier_name" id="supplier_name" />
					</div>
					
					<div class="row">
						<label class="addlabel">Invoice Total</label>
						<div style="float:left; margin-top: 7px; margin-right: 5px;">$</div> 
						<div style="float:left;"><input type="text" class="addinput" name="invoice_total" id="invoice_total" style="width: 387px;" /></div>
					</div>
				</div>
				
				<div class="row">
					<label class="addlabel" id="item_header_lbl" style="margin-bottom: 81px;">Item</label>
					<div style="float: left; text-align: left;">
						<div id="items_div"></div>
						<textarea name="item_note" class="addtextarea" style="width: 400px; height: 100px;">Leave at front door</textarea>
					</div>
				</div>
				
				<div id="Deliver_to_div">
					<div class="row">
						<label class="addlabel" for="item">Deliver to</label>
						<select name="deliver_to" id="deliver_to" style="width:400px;">
							<option value="">----</option>	
							<?php
							// get Users
							$user_sql = getStaffByCountry();
							while( $user = mysql_fetch_array($user_sql) ){ ?>
								<option value="<?php echo $user['staff_accounts_id']; ?>"><?php echo "{$user['FirstName']} ".( ($user['LastName']!="")?strtoupper(substr($user['LastName'],0,1)).'.':'' ); ?></option>
							<?php	
							}
							?>
						</select>
						<input type="hidden" name="deliver_to_name" id="deliver_to_name" />
					</div>
					
					<div class="row">
						<label class="addlabel">Delivery Address</label>
						<input type="text"  class="addinput" name="delivery_address" id="delivery_address" readonly="readonly" style="width:400px" />
					</div>
					
					<div class="row">
						<label class="addlabel">Receiver Email</label>
						<input type="text"  class="addinput" name="reciever_email" id="reciever_email" readonly="readonly" style="width:400px" />
					</div>
					
					<div class="row">
						<label class="addlabel">Comments</label>
						<textarea name="comments" class="addtextarea" style="float: left; width: 400px; height: 100px;">Leave at front door</textarea>
					</div>
					
					<?php
					// current logged user
					/*$curr_user_sql = mysql_query("
						SELECT *
						FROM `staff_accounts`
						WHERE `StaffID` = {$_SESSION['USER_DETAILS']['StaffID']}
					");*/
					$params = array(
						'staff_id' => $_SESSION['USER_DETAILS']['StaffID']
					);
					$curr_user_sql = $crm->getStaffAccount($params);
					$curr_user = mysql_fetch_array($curr_user_sql);
					$ordered_by = $curr_user['StaffID'];
					$ordered_by_name = "{$curr_user['FirstName']} ".( ($curr_user['LastName']!="")?strtoupper(substr($curr_user['LastName'],0,1)).'.':'' );
					$ordered_by_full_name = "{$curr_user['FirstName']} {$curr_user['LastName']}";
					$ordered_by_email = $curr_user['Email'];
					?>
					<div class="row">
						<label class="addlabel">Ordered By</label>
						<select name="ordered_by"  id="ordered_by" style="width:400px;">
							<option value="">----</option>	
							<?php
							// get Users
							$user_sql = getStaffByCountry();
							while( $user = mysql_fetch_array($user_sql) ){ ?>
								<option value="<?php echo $user['staff_accounts_id']; ?>" <?php echo ($user['staff_accounts_id']==$ordered_by)?'selected="selected"':''; ?>><?php echo "{$user['FirstName']} ".( ($user['LastName']!="")?strtoupper(substr($user['LastName'],0,1)).'.':'' ); ?></option>
							<?php	
							}
							?>
						</select>
						<input type="hidden" name="ordered_by_name" id="ordered_by_name" value="<?php echo $ordered_by_name; ?>" />
						<input type="hidden" name="ordered_by_full_name" id="ordered_by_full_name" value="<?php echo $ordered_by_full_name; ?>" />
					</div>
					
					<div class="row">
						<label class="addlabel">Ordered by Email</label>
						<input type="text"  class="addinput" name="order_by_email" id="order_by_email" readonly="readonly" value="<?php echo $ordered_by_email; ?>" style="width:400px;" />
					</div>
				</div>
				
				
				<div class="row" style="margin-top: 35px; text-align: left;">
					<label class="addlabel"></label>
					<input type="hidden" name="staff_id" value="<?php echo $staff_id; ?>" />
					<input type="hidden" class="submitbtnImg" name="submit" value="Submit" />
					<button type='submit' class='submitbtnImg' id="btn_display_statement" style="margin-right: 25px;" >
						<img class="inner_icon" src="images/button_icons/save-button.png">
						Submit
					</button>
					<input type="checkbox" name="email_purchase_order" style="width:auto; display:inline;" checked="checked" value="1" /> Email Purchase Order
				</div>
					
			
				

				
				
			</div>
		</form>


	
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	jQuery(document).on("keyup",".qty",function(){
		
		 var qty = jQuery(this).val();
		 var price = jQuery(this).parents("tr:first").find(".price").val();
		 var total = price*qty;
		 
		 if( qty>0 ){
			jQuery(this).parents("tr:first").removeClass("fadeOutText");
		 }else{
			 jQuery(this).parents("tr:first").addClass("fadeOutText");
		 }
		 
		 jQuery(this).parents("tr:first").find(".total_lbl").val("$"+total.toFixed(2));
		 jQuery(this).parents("tr:first").find(".total").val(total.toFixed(2));
		 
		
		
	});
	
	
	// ajax supplier
	jQuery("#supplier").change(function(){
		
		var supplier = jQuery(this).val();
		
		jQuery("#items_div").html("");
					
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_get_suppliers.php",
			data: { 
				suppliers_id: supplier,
				country_id: <?php echo $_SESSION['country_default']; ?>
			},
			dataType: "json"
		}).done(function( ret ) {	

			if( parseInt(supplier)==parseInt(<?php echo $crm->getDynamicHandyManID(); ?>) ){
				jQuery(".handyman_div").show();
				jQuery("#item_header_lbl").html("Problem");
				jQuery("#Deliver_to_div").hide();
			}else{
				jQuery(".handyman_div").hide();
				jQuery("#item_header_lbl").html("Item");
				jQuery("#Deliver_to_div").show();
			}
			
			jQuery("#supplier_address").val(ret.address);
			jQuery("#supplier_email").val(ret.email);
			jQuery("#supplier_name").val(ret.company_name);
			jQuery("#items_div").html(ret.tech_stock_list);		
			jQuery("#load-screen").hide();
			
		});
			
	});
	
	
	
	// ajax deliver to
	jQuery("#deliver_to").change(function(){
		
		var staff_id = jQuery(this).val();
					
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_get_staff_accounts.php",
			data: { 
				staff_id: staff_id
			},
			dataType: "json"
		}).done(function( ret ) {		
			
			jQuery("#delivery_address").val(ret.address);
			jQuery("#reciever_email").val(ret.email);
			jQuery("#deliver_to_name").val(ret.fullname);
			jQuery("#load-screen").hide();
			
		});
			
	});
	
	
	// ajax Order by
	jQuery("#ordered_by").change(function(){
		
		var staff_id = jQuery(this).val();
					
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_get_staff_accounts.php",
			data: { 
				staff_id: staff_id
			},
			dataType: "json"
		}).done(function( ret ) {		
			
			jQuery("#order_by_email").val(ret.email)
			jQuery("#ordered_by_name").val(ret.fullname);
			jQuery("#ordered_by_full_name").val(ret.fullname2);
			jQuery("#load-screen").hide();
			
		});
			
	});
	
	
	
});
</script>
</body>
</html>