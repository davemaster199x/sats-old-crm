<?php
$title = "Add Warranty";
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
		
		<form action="add_warranty_script.php" method="post" id="jform" style="font-size: 14px; margin-top: 30px;">
			<div class="addproperty">
			
				<?php
				$pon_sql = $crm->getPurchaseOrderLastIDNumber();
				$pon = mysql_fetch_array($pon_sql);
				?>
				

				
				
				<div class="row">
					<label class="addlabel">Make</label>
					<input type="text"  class="addinput" name="make" id="make"  style="width:400px" />
				</div>
				
				<div class="row">
					<label class="addlabel">Model</label>
					<input type="text"  class="addinput" name="model" id="model" style="width:400px" />
				</div>
				
				<div class="row">
					<label class="addlabel">Amount Replaced</label>
					<input type="text"  class="addinput" name="amount_replaced" id="amount_replaced" style="width:400px" />
				</div>
				
				<div class="row">
					<label class="addlabel">Amount Discarded</label>
					<input type="text"  class="addinput" name="amount_discarded" id="amount_discarded" style="width:400px" />
				</div>
				
				<div class="row" style="margin-top: 35px; text-align: left;">
					<label class="addlabel"></label>
					<input type="hidden" name="staff_id" value="<?php echo $staff_id; ?>" />
					<input type="hidden" class="submitbtnImg" name="submit" value="Submit" />
					<button type='submit' class='submitbtnImg' id="btn_display_statement" style="margin-right: 25px;" >
						<img class="inner_icon" src="images/button_icons/save-button.png">
						Submit
					</button>					
				</div>
					
			
				

				
				
			</div>
		</form>


	
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	/*
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
	
	*/
	
});
</script>
</body>
</html>