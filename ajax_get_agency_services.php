<?php

include('inc/init_for_ajax.php');
include('inc/agency_services_class.php');

$agency_id = $_POST['agency_id'];
//$agency_id = 1490;

# invoke class
$as_class = new Agency_Services($agency_id);

$as_sql = $as_class->get_agency_services();

?>
<?php
$i = 0;
if(mysql_num_rows($as_sql)>0){
?>
	<h2 class='heading'>Services</h2>
	
        
     
     <div id="jtable" class="add-prop-st" style="width: 100%;">
     	  <?php
			while($as = mysql_fetch_array($as_sql)){ ?>
     		
            <div class="add-prop-st-mid">            	
            <div class="add-services-row">
				<input type="hidden" name="alarm_job_type_id[]" value="<?php echo $as['id'] ?>">
				<input type="hidden" name="price_changed[]" class="price_changed" value="0">		
            </div>
            <div class="add-services-row">
            	<span class="service_type"><?php echo $as['type']; ?></span>
            </div>
            <div class="add-services-row">		
            	<div class="lbl_price services_col">$<?php echo $as['price']; ?></div> 
				<div class="txt_price services_col" style="display:none;"><span class="agency-serv-hd">$</span><span class="agency-serv-inpt"><input type="text" name="price[]" id="price" class="price" value="<?php echo $as['price']; ?> "></span></div>
            </div>
            <div class="add-services-row">	
            	<div class="add-services-inner-radio">
                	<div class="serv_indiv_div float-left"><input type="radio" name="service<?php echo $i; ?>" class="serv_radio serv_sats" value="1"> <span class="serv_lbl_txt fadeOutText">SATS</span></div>
					<div class="serv_indiv_div float-left"><input type="radio" name="service<?php echo $i; ?>" class="serv_radio serv_not_sats" value="0"> <span class="serv_lbl_txt fadeOutText">DIY</span></div>
					<div class="serv_indiv_div float-left"><input type="radio" name="service<?php echo $i; ?>" class="serv_radio serv_not_sats" checked="checked" value="2"> <span class="serv_lbl_txt">No Response</span></div>
					<div class="serv_indiv_div float-left"><input type="radio" name="service<?php echo $i; ?>" class="serv_radio serv_not_sats" value="3"> <span class="serv_lbl_txt fadeOutText">Other Provider</span></div>
                </div>	
            </div>
            <div class="add-services-row tbl_change_price" style="display:none;">	
            	<div class="cprice-hld">
                        	<div class="cprice-hld-inr">
                            	<div class="cprice-hld-dv">Reason: <span style="color:red">*</span></div>
                            	<div class="cprice-hld-dv"><select name="price_reason[]" class="addinput price_reason">
											<option value=""></option>
											<option value="FOC">FOC</option>
											<option value="Price match">Price match</option>
											<option value="Multiple properties">Multiple properties</option>
											<option value="Agents Property">Agents Property</option>
											<option value="Other">Other</option>
										</select></div>
                            </div>
                            <div class="cprice-hld-inr">
                            	<div class="cprice-hld-dv">Details: <span style="color:red">*</span></div>
                            	<div class="cprice-hld-dv"><input type="text" name="price_details[]" class="tenantinput addinput price_details"></div>
                            </div>            
                        </div>	
            </div>
            <div class="add-services-row">
            	<button type="button" class="submitbtnImg btn_change_price">
					<img class="inner_icon" src="images/rebook.png">
					<span class="inner_icon_span">Change Price</span>
				</button>
            </div>
            </div>
        
           <?php
			$i++;
			}
			?>
     </div>
        
<style>
.txt_price .agency-serv-inpt input {
    width: 50px;
}
.tenantinput.addinput.price_details {
    padding: 0;
    width: 149px;
}
</style>       
<script>
jQuery(document).ready(function(){

	jQuery(".btn_change_price").toggle(function(){
		jQuery(this).find(".inner_icon").attr("src","images/cancel-button.png");
		jQuery(this).parents(".add-services-row").find(".inner_icon_span").html("Cancel");
		jQuery(this).parents(".add-prop-st-mid").find(".price_changed").val(1);
		jQuery(this).parents(".add-prop-st-mid").find(".tbl_change_price").show(); 
		jQuery(this).parents(".add-prop-st-mid").find(".lbl_price").hide(); 
		jQuery(this).parents(".add-prop-st-mid").find(".txt_price").show();				
	},function(){
		jQuery(this).find(".inner_icon").attr("src","images/rebook.png");
		jQuery(this).parents(".add-services-row").find(".inner_icon_span").html("Change Price");
		jQuery(this).parents(".add-prop-st-mid").find(".price_changed").val(0);
		jQuery(this).parents(".add-prop-st-mid").find(".tbl_change_price").hide(); 
		jQuery(this).parents(".add-prop-st-mid").find(".lbl_price").show(); 
		jQuery(this).parents(".add-prop-st-mid").find(".txt_price").hide();
		
	});
	
});
</script>
<?php
}
?>

