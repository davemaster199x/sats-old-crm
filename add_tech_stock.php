<?php

$title = "Stock Items";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class();

?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
#form_Stock .addlabel{
	width: 150px;
}
</style>





<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="Stock" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Stock Successfully Added</div>';
		}else if($_GET['success']==2){
			echo '<div class="success">Stock Successfully Updated</div>';
		}
		
		
		?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">
			<tr class="toprow jalign_left">
				<th>Code</th>
				<th>Item</th>
				<th>Display Name</th>
				<th>Price EX GST</th>
				<th>Supplier</th>
				<th>Show on Report</th>
				<th>Show on Stocktake</th>
				<th>Active</th>
				<th>Edit</th>
			</tr>
				<?php
				
				/*
				$sql = mysql_query("
					SELECT *
					FROM `stocks`
					WHERE `country_id` = {$_SESSION['country_default']}
					ORDER BY `item` ASC
				");
				*/
				$params = array(
					'country_id' => $_SESSION['country_default'],
					'sort_list' => array(
						'order_by' => 's.`item`',
						'sort' => 'ASC'
					)
				);
				$sql = $crm->getStock($params);				
				
				if(mysql_num_rows($sql)>0){
					while($row = mysql_fetch_array($sql)){
				?>
						<tr class="body_tr jalign_left">
							<td>
								<span class="txt_lbl"><?php echo $row['code']; ?></span>
								<input type="text" class="txt_hid code" value="<?php echo $row['code']; ?>" />
								<input type="hidden" class="stocks_id" value="<?php echo $row['stocks_id']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['item']; ?></span>
								<input type="text" class="txt_hid item" value="<?php echo $row['item']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['display_name']; ?></span>
								<input type="text" class="txt_hid display_name" value="<?php echo $row['display_name']; ?>" />
							</td>
							<td>
								<span class="txt_lbl">$<?php echo $row['price']; ?></span>
								<span class="txt_hid">
								$<input type="text" class="price" value="<?php echo $row['price']; ?>" />
								</span>
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['company_name']; ?></span>
								<span class="txt_hid">
									<select name="supplier" class="supplier">
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
											<option value="<?php echo $sup['suppliers_id']; ?>" <?php echo ($sup['suppliers_id']==$row['suppliers_id'])?'selected="selected"':''; ?>><?php echo $sup['company_name']; ?></option>
										<?php	
										}
										?>
									</select>	
								</span>
							</td>
							<td>
								<span class="txt_lbl"><?php echo ($row['display']==1)?'Yes':'No'; ?></span>
								<select class="txt_hid display">
									<option value="1" <?php echo ($row['display']==1)?'selected="selected"':''; ?>>Yes</option>
									<option value="0" <?php echo ($row['display']==0)?'selected="selected"':''; ?>>No</option>
								</select>
							</td>
							<td>
								<span class="txt_lbl"><?php echo ($row['show_on_stocktake']==1)?'Yes':'No'; ?></span>
								<select class="txt_hid show_on_stocktake">
									<option value="1" <?php echo ($row['show_on_stocktake']==1)?'selected="selected"':''; ?>>Yes</option>
									<option value="0" <?php echo ($row['show_on_stocktake']==0)?'selected="selected"':''; ?>>No</option>
								</select>
							</td>
							<td>
								<span class="txt_lbl"><?php echo ($row['s_status']==1)?'Yes':'No'; ?></span>
								<select class="txt_hid status">
									<option value="1" <?php echo ($row['s_status']==1)?'selected="selected"':''; ?>>Yes</option>
									<option value="0" <?php echo ($row['s_status']==0)?'selected="selected"':''; ?>>No</option>
								</select>
							</td>
							<td>
								<button class="blue-btn submitbtnImg btn_update">Update</button>
								<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
								<button class="submitbtnImg btn_cancel" style="display:none;">Cancel</button>
							</td>
						</tr>
				<?php
					}
				}else{ ?>
					<td colspan="5" align="left">Empty</td>
				<?php
				}
				?>
		</table>	

		<div class="jalign_left">
		
			<button type="button" id="btn_add_new" class="submitbtnImg">Add New</button>
			
            <div style="padding-top: 20px;" id="div_staff" class="addproperty formholder">
				<form id="form_Stock" method="post" action="/add_tech_stock_process.php" style="display:none;">
					<div class="row">
						<label class="addlabel" for="code">Code</label>
						<input type="text" name="code" id="code" class="code" />
					</div>         			
					<div class="row">
						<label class="addlabel" for="item">Item</label>
						<input type="text" name="item" id="item" class="item" />
					</div>
					<div class="row">
						<label class="addlabel" for="display_name">Display Name</label>
						<input type="text" name="display_name" id="display_name" class="display_name" />
					</div>
					<div class="row">
						<label class="addlabel" for="price">Price</label>
						<input type="text" name="price" id="price" class="price" />
					</div>
					<div class="row">
						<label class="addlabel" for="price">Supplier</label>
						<select name="supplier" id="supplier">
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
								<option value="<?php echo $sup['suppliers_id']; ?>"><?php echo $sup['company_name']; ?></option>
							<?php	
							}
							?>
						</select>	
					</div>					
					<div class="row">
						<label class="addlabel" for="display">Show on Report</label>
						<input type="checkbox" name="display" id="display" class="display" style="width: auto; margin-top: 6px;" value="1" />
					</div>
					<div class="row">
						<label class="addlabel" for="show_on_stocktake">Show on Stocktake</label>
						<input type="checkbox" name="show_on_stocktake" id="show_on_stocktake" class="show_on_stocktake" style="width: auto; margin-top: 6px;" value="1" />
					</div>
					<div style="padding-top: 15px; text-align:left;" class="row clear">
						<input type="submit" class="submitbtnImg" style="width: auto;" name="btn_submit" value="Submit" />
					</div>
				</form>
			</div>			
			
		</div>
		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){


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

	jQuery("#form_Stock").submit(function(event){
		
		var code = jQuery("#code").val();		
		var item = jQuery("#item").val();
		var price = jQuery("#price").val();
		var error = "";
		
		if(code==""){
			error += "Code Field  is required\n";
		}
		
		if(error!=""){
			alert(error);
			event.preventDefault();	
		}else{
			return true;
		}
		
	});

	jQuery(".btn_edit").click(function(){
	
		jQuery(this).parents("tr:first").find(".btn_update").show();
		jQuery(this).parents("tr:first").find(".btn_edit").hide();
		jQuery(this).parents("tr:first").find(".btn_cancel").show();
		jQuery(this).parents("tr:first").find(".txt_hid").show();
		jQuery(this).parents("tr:first").find(".txt_lbl").hide();
	
	});	
	
	jQuery(".btn_cancel").click(function(){
		
		jQuery(this).parents("tr:first").find(".btn_update").hide();
		jQuery(this).parents("tr:first").find(".btn_edit").show();
		jQuery(this).parents("tr:first").find(".btn_cancel").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".txt_hid").hide();	
		
	});
	
	jQuery(".btn_update").click(function(){
	
		var stocks_id = jQuery(this).parents("tr:first").find(".stocks_id").val();
		var code = jQuery(this).parents("tr:first").find(".code").val();
		var item = jQuery(this).parents("tr:first").find(".item").val();
		var display_name = jQuery(this).parents("tr:first").find(".display_name").val();
		var price = jQuery(this).parents("tr:first").find(".price").val();
		var display = jQuery(this).parents("tr:first").find(".display").val();
		var status = jQuery(this).parents("tr:first").find(".status").val();
		var supplier = jQuery(this).parents("tr:first").find(".supplier").val();
		var show_on_stocktake = jQuery(this).parents("tr:first").find(".show_on_stocktake").val();		
		var error = "";
		
		if(code==""){
			error += "Update Code field is required\n";
		}
		
		if(price!="" && is_numeric(price)==false){
			error += "Update Price field must be numeric\n";
		}
		
		
		if(error!=""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_stock.php",
				data: { 
					stocks_id: stocks_id,
					code: code,
					item: item,
					display_name: display_name,
					price: price,
					display: display,
					status: status,
					supplier: supplier,
					show_on_stocktake: show_on_stocktake
				}
			}).done(function( ret ) {
				window.location="/add_tech_stock.php?success=2";
			});				
			
		}		
		
	});

	jQuery("#btn_add_new").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#form_Stock").slideDown();
	},function(){
		jQuery(this).html("Add New");		
		jQuery("#form_Stock").slideUp();
	});
});
</script>
</body>
</html>