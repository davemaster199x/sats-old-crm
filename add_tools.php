<?php

$title = "Add Tools";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

?>
<style>
.addproperty input, .addproperty select {
    width: 30%;
}
.addproperty label {
   width: 230px;
}
</style>
    
    <div id="mainContent">
      
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">New tools added</div>
	<?php
	}
	?>
      	
	<form action="add_tools_script.php" method="post" id="jform" style="font-size: 14px;">
	<div class="addproperty">		
		<div class="row">
			<label class="addlabel">Item</label>
			<select name="item" id="item">
				<option value="">----</option>				
				<?php
				$ti_sql = $crm->getToolItems($params);				
				while( $ti = mysql_fetch_array($ti_sql) ){ ?>
					<option value="<?php echo $ti['tool_items_id'] ?>"><?php echo $ti['item_name'] ?></option>
				<?php	
				}
				?>
			</select>
		</div>
		<div class="row">
			<label class="addlabel">Item ID</label>
			<input type="text"  class="addinput" name="item_id" id="item_id">
		</div>
		<div class="row">
			<label class="addlabel">Brand</label>
			<input type="text"  class="addinput brand brand_input" name="brand_input" />
			<select name="brand_dp" class="brand brand_dp"  style="display:none;">
				<option value="">----</option>				
				<option value="GORILLA">GORILLA</option>
				<option value="RHINO">RHINO</option>
				<option value="WERNER">WERNER</option>
			</select>
		</div>	
		<div class="row">
			<label class="addlabel">Description</label>
			<input type="text"  class="addinput description description_input" name="description_input" />
			<select name="description_dp" class="description description_dp" style="display:none;">
				<option value="">----</option>				
				<option value="3FT Single sided ladder">3FT Single sided ladder</option>
				<option value="3FT Double sided ladder">3FT Double sided ladder</option>
				<option value="4FT Single sided ladder">4FT Single sided ladder</option>
				<option value="4FT Double sided ladder">4FT Double sided ladder</option>
				<option value="6FT Single sided ladder">6FT Single sided ladder</option>				
				<option value="6FT Double sided ladder">6FT Double sided ladder</option>
				<option value="8FT Single sided ladder">8FT Single sided ladder</option>
				<option value="8FT Double sided ladder">8FT Double sided ladder</option>
				<option value="10FT Single sided ladde">10FT Single sided ladder</option>
				<option value="10FT Double sided ladder">10FT Double sided ladder</option>
			</select>
		</div>
		<div class="row">
			<label class="addlabel">Purchase Date</label>
			<input type="text"  class="addinput datepicker" name="purchase_date" id="purchase_date">
		</div>
		<div class="row">
			<label class="addlabel">Purchase Price</label>
			<input type="text"  class="addinput" name="purchase_price" id="purchase_price" />
		</div>
		<div class="row">
			<label class="addlabel">Assign to Vehicle</label>
			<select name="assign_to_vehicle" id="assign_to_vehicle">
				<option value="">----</option>
				<?php
				// get Users
				$jparams = array(
					'country_id' => $_SESSION['country_default'],
					'tech_vehicle' => 1,
					'sort_list' => array(
						'order_by' => 'v.`number_plate`',
						'sort' => 'ASC'
					)
				);
				$v_sql = $crm->getVehicles($jparams);
				while( $v = mysql_fetch_array($v_sql) ){ ?>
					<option value="<?php echo $v['vehicles_id']; ?>"><?php echo $v['number_plate']; ?> - <?php echo $crm->formatStaffName($v['FirstName'],$v['LastName']); ?></option>
				<?php	
				}
				?>
			</select>
		</div>
		<div class="row">
        	<button class="submitbtnImg" id="btn_add_vehicle" type="button" style="float: left;">Add Tools</button>
        </div>
	</div>
	</form>


    
  </div>

<br class="clearfloat" />


<script>
jQuery(document).ready(function(){
	
	
	
	jQuery("#item").change(function(){
		
		var item = jQuery(this).val();
		if( item==1 ){
			// brand
			jQuery(".brand_dp").show();
			jQuery(".brand_input").hide();
			// description
			jQuery(".description_dp").show();
			jQuery(".description_input").hide();
		}else{
			// brand
			jQuery(".brand_dp").hide();
			jQuery(".brand_input").show();
			// description
			jQuery(".description_dp").hide();
			jQuery(".description_input").show();
		}
		
	});
	
	

	jQuery("#btn_add_vehicle").click(function(){
	
		var item = jQuery("#item").val();
		var item_id = jQuery("#item_id").val();
		var error = "";
		
		if(item==""){
			error += "Item is required\n";
		}
		if(item_id==""){
			error += "Item ID is required\n";
		}
		
		if(error!=""){
			alert(error);
		}else{
			jQuery("#jform").submit();
		}
		
	});

	
	
});
</script>

</body>
</html>
