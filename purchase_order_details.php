<div id="load-screen"></div>
<?php

$title = "Purchase Order Details";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$purchase_order_id = mysql_real_escape_string($_GET['id']);

$crm = new Sats_Crm_Class();

$params = array(
	'country_id' => $_SESSION['country_default'],
	'purchase_order_id' => $purchase_order_id
);
$po_sql = $crm->getPurchaseOrder($params);
$po = mysql_fetch_array($po_sql);

?>
<style>
.addproperty input, .addproperty select {
    width: 30%;
}
.addproperty label {
   width: 230px;
}
#load-screen {
	width: 100%;
	height: 100%;
	background: url("/images/loading.gif") no-repeat center center #fff;
	position: fixed;
	opacity: 0.7;
	display:none;
	z-index: 9999999999;
}
.handyman_div{
	display:none;
}
</style>




<div id="mainContent">

		<div class="sats-breadcrumb">
			<ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="Purchase Orders" href="/purchase_order.php">Purchase Orders</a></li>
			<li class="other first"><a title="<?php echo $title; ?>" href="/purchase_order_details.php?id=<?php echo $purchase_order_id; ?>"><strong><?php echo $title; ?></strong></a></li>
			</ul>
		</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['update']==1){
			echo '<div class="success">Update Successful</div>';
		}
		
		
		?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		<form action="purchase_order_details_update.php" method="post" id="jform" style="font-size: 14px; margin-top: 30px;">
			<div class="addproperty">
			
				<div class="row">
					<label class="addlabel">Purchase Order No.</label>
					<input type="text"  class="addinput" name="purchase_order_num" value="<?php echo $po['purchase_order_num']; ?>" style="width:400px;" readonly="readonly" />
				</div>

				<div class="row">
					<label class="addlabel">Date</label>
					<input type="text"  class="addinput datepicker" name="date" value="<?php echo $crm->formatDate($po['date'],'d/m/Y'); ?>" style="width:400px;" />
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
							<option value="<?php echo $sup['suppliers_id']; ?>" <?php echo ($sup['suppliers_id']==$po['suppliers_id'])?'selected="selected"':''; ?>><?php echo $sup['company_name']; ?></option>
						<?php	
						}
						?>
					</select>
					<input type="hidden" name="supplier_name" id="supplier_name" value="<?php echo $po['company_name']; ?>" />
				</div>
				
				<div class="row">
					<label class="addlabel">Supplier Address</label>
					<input type="text"  class="addinput" name="supplier_address" id="supplier_address" readonly="readonly" style="width:400px" value="<?php echo $po['sup_address']; ?>" />
				</div>
				
				<div class="row">
					<label class="addlabel">Supplier Email</label>
					<input type="text"  class="addinput" name="supplier_email" id="supplier_email" readonly="readonly" style="width:400px" value="<?php echo $po['sup_email']; ?>" />
				</div>
				
				<div class="handyman_div" <?php echo ( $po['suppliers_id']==$crm->getDynamicHandyManID() )?'style="display:block;"':''; ?>>
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
								<option value="<?php echo $agen['agency_id']; ?>" <?php echo ($agen['agency_id']==$po['agency_id'])?'selected="selected"':''; ?>><?php echo $agen['agency_name']; ?></option>
							<?php	
							}
							?>
						</select>	
						<input type="hidden" name="supplier_name" id="supplier_name" />
					</div>
					
					<div class="row">
						<label class="addlabel">Invoice Total</label>
						<div style="float:left; margin-top: 7px; margin-right: 5px;">$</div> 
						<div style="float:left;"><input type="text" class="addinput" name="invoice_total" id="invoice_total" style="width: 387px;" value="<?php echo $po['invoice_total']; ?>" /></div>
					</div>
				</div>
				
				
				<div class="row">
					<label class="addlabel" style="margin-bottom: 81px;"><?php echo ($po['suppliers_id']==$crm->getDynamicHandyManID())?'Problem':'Item'; ?></label>
					<div style="float: left; text-align: left;">
						<div id="items_div">
							<?php
							$params = array(
								'purchase_order_id' => $purchase_order_id,
								'sort_list' => array(
									'order_by' => 'poi.`purchase_order_item_id`',
									'sort' => 'ASC'
								)
							);
							$poi_sql = $crm->getPurchaseOrderItem($params);
							if( mysql_num_rows($poi_sql)>0 ){ ?>
								<table class="tbl-sd" style="width:auto; margin-bottom: 5px;">
									<tr class="toprow" style="text-align: left;">
										<th>Code</td>
										<th>Item</th>
										<th>Price</th>
										<th>Qty</th>
										<th>Total</th>
									</tr>
									<?php
									while( $poi = mysql_fetch_array($poi_sql) ){ ?>
										<tr style="background-color:#eeeeee;" class="<?php echo ( $poi['quantity'] > 0 )?'':'fadeOutText'; ?>">
											<td>
												<input type="hidden" name="purchase_order_item_id[]" value="<?php echo $poi['purchase_order_item_id']; ?>" />
												<input type="hidden" name="stocks_id[]" value="<?php echo $poi['stocks_id']; ?>" />
												<input type="text" class="addinput code" name="code[]" style="width: 100px;" readonly="readonly" value="<?php echo $poi['code']; ?>" />
											</td>
											<td><input type="text" class="addinput item" name="item[]" style="width: 150px;" readonly="readonly" value="<?php echo $poi['item']; ?>" /></td>
											<td>
												<input type="text" class="addinput price_lbl" style="width: 60px;" readonly="readonly" value="$<?php echo $poi['price']; ?>" />
												<input type="hidden" name="price[]" class="price" value="<?php echo $poi['price']; ?>" />
											</td>
											<td><input type="text" class="addinput qty" name="qty[]" style="width: 50px;" value="<?php echo $poi['quantity']; ?>" /></td>
											<td>
												<input type="text" class="addinput total_lbl" style="width: 100px;" readonly="readonly" value="$<?php echo $poi['total']; ?>" />
												<input type="hidden" name="total[]" class="total" value="<?php echo $poi['total']; ?>" />
											</td>
										</tr>
									<?php	
									}
									?>									
								</table>
							<?php	
							}
							?>							
						</div>
						<textarea name="item_note" style="width: 400px; height: 100px;"><?php echo $po['item_note']; ?></textarea>
					</div>
				</div>
				
				<div id="Deliver_to_div" <?php echo ( $po['suppliers_id']==$crm->getDynamicHandyManID() )?'style="display:none;"':''; ?>>
					<div class="row">
						<label class="addlabel" for="item">Deliver to</label>
						<select name="deliver_to" id="deliver_to" style="width:400px;">
							<option value="">----</option>	
							<?php
							// get Users
							$user_sql = getStaffByCountry();
							while( $user = mysql_fetch_array($user_sql) ){ ?>
								<option value="<?php echo $user['staff_accounts_id']; ?>" <?php echo ($user['staff_accounts_id']==$po['deliver_to'])?'selected="selected"':''; ?>><?php echo "{$user['FirstName']} ".( ($user['LastName']!="")?strtoupper(substr($user['LastName'],0,1)).'.':'' ); ?></option>
							<?php	
							}
							?>
						</select>
						<input type="hidden" name="deliver_to_name" id="deliver_to_name" value="<?php echo "{$po['dt_fname']} ".( ($po['dt_lname']!="")?strtoupper(substr($po['dt_lname'],0,1)).'.':'' ); ?>" />
					</div>
					
					<div class="row">
						<label class="addlabel">Delivery Address</label>
						<input type="text"  class="addinput" name="delivery_address" id="delivery_address" readonly="readonly" style="width:400px" value="<?php echo $po['dt_address']; ?>" />
					</div>
					
					<div class="row">
						<label class="addlabel">Receiver Email</label>
						<input type="text"  class="addinput" name="reciever_email" id="reciever_email" readonly="readonly" style="width:400px" value="<?php echo $po['dt_email']; ?>" />
					</div>
					
					<div class="row">
						<label class="addlabel">Comments</label>
						<textarea name="comments" style="float: left; width: 400px; height: 100px;"><?php echo $po['comments']; ?></textarea>
					</div>				
					<?php				
					$ordered_by_name = "{$po['ob_fname']} ".( ($po['ob_lname']!="")?strtoupper(substr($po['ob_lname'],0,1)).'.':'' );
					$ordered_by_full_name = "{$po['ob_fname']} {$po['ob_lname']}";
					?>
					<div class="row">
						<label class="addlabel">Ordered By</label>
						<select name="ordered_by"  id="ordered_by" style="width:400px;">
							<option value="">----</option>	
							<?php
							// get Users
							$user_sql = getStaffByCountry();
							while( $user = mysql_fetch_array($user_sql) ){ ?>
								<option value="<?php echo $user['staff_accounts_id']; ?>" <?php echo ($user['staff_accounts_id']==$po['ordered_by'])?'selected="selected"':''; ?>><?php echo "{$user['FirstName']} ".( ($user['LastName']!="")?strtoupper(substr($user['LastName'],0,1)).'.':'' ); ?></option>
							<?php	
							}
							?>
						</select>
						<input type="hidden" name="ordered_by_name" id="ordered_by_name" value="<?php echo $ordered_by_name; ?>" />
						<input type="hidden" name="ordered_by_full_name" id="ordered_by_full_name" value="<?php echo $ordered_by_full_name; ?>" />
					</div>
					
					<div class="row">
						<label class="addlabel">Ordered by Email</label>
						<input type="text"  class="addinput" name="order_by_email" id="order_by_email" readonly="readonly" value="<?php echo $po['ob_email']; ?>" style="width:400px;" />
					</div>
				</div>
				
				<div class="row" style="margin-top: 35px; text-align: left;">
					<label class="addlabel"></label>
					<input type="hidden" name="purchase_order_id" value="<?php echo $po['purchase_order_id']; ?>" />
					<input type="hidden" class="submitbtnImg" name="submit" value="Update" />
					<button type='submit' class='submitbtnImg' id="btn_display_statement" style="margin-right: 25px;" >
						<img class="inner_icon" src="images/button_icons/save-button.png">
						Update
					</button>
					<input type="checkbox" name="email_purchase_order" style="width:auto; display:inline;" value="1" /> Email Purchase Order
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
			
			jQuery("#order_by_email").val(ret.email);
			jQuery("#ordered_by_name").val(ret.fullname);
			jQuery("#ordered_by_full_name").val(ret.fullname2);
			jQuery("#load-screen").hide();
			
		});
			
	});
	
	
	
});
</script>
</body>
</html>