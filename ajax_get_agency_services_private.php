<?php

include('inc/init_for_ajax.php');
include('inc/agency_services_class.php');
include('inc/sats_crm_class.php');

$agency_id = $_POST['agency_id'];
//$agency_id = 1490;

# invoke class
$as_class = new Agency_Services($agency_id);
$sats_class = new Sats_Crm_Class($agency_id);

$as_sql = $as_class->get_agency_services();

?>
<?php
$i = 0;
$piea_sql = mysql_query("
SELECT *
FROM `price_increase_excluded_agency`
WHERE `agency_id` = {$agency_id}                  
AND (
	`exclude_until` >= '".date('Y-m-d')."' OR
	`exclude_until` IS NULL
)
");  

$is_price_increase_excluded = ( mysql_num_rows($piea_sql) > 0 )?1:0;
if(mysql_num_rows($as_sql)>0){
?>
	
	
        
     
     <div id="jtable" class="add-prop-st" style="width: 100%;">
     	  <?php
			while($as = mysql_fetch_array($as_sql)){ ?>
     		
            <div class="agency_service_row">            	
				<div class="add-services-row">
					<input type="hidden" name="alarm_job_type_id[]" class="alarm_job_type_id" value="<?php echo $as['id'] ?>">
					<input type="hidden" name="price_changed[]" class="price_changed" value="0">		
				</div>
			   
			   <div class="add-services-row">		
					<?php                    
					if( $is_price_increase_excluded == 1 ){ // orig price 
						echo '<div class="lbl_price">$'.$as['price'].'</div> ';
					}else{ // new price, price variation

						// $price_var_params = array(
						// 	'service_type' => $service_row['id'],
						// 	'agency_id' => $agency_id
						// );
						// $price_var_arr = $this->system_model->get_agency_price_variation($price_var_params);
						// echo $price_var_arr['price_breakdown_text'];

					}                                                                
					?>
					<div class="lbl_price">$<?php echo $as['price']; ?></div> 
					<div class="txt_price" style="display:none;"><span class="agency-serv-hd">$</span><span class="agency-serv-inpt"><input type="text" name="price[]" id="price" class="price" value="<?php echo $as['price']; ?> "></span></div>
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
					<button type="button" class="submitbtnImg btn_change_price">Change Price</button>
				</div>
            </div>
        
           <?php
			$i++;
			}
			?>
     </div>
        
        
		<script>
		jQuery(document).ready(function(){
		
			jQuery(".btn_change_price").toggle(function(){
				jQuery(this).html("Cancel");
				jQuery(this).parents(".add-prop-st-mid").find(".price_changed").val(1);
				jQuery(this).parents(".add-prop-st-mid").find(".tbl_change_price").show(); 
				jQuery(this).parents(".add-prop-st-mid").find(".lbl_price").hide(); 
				jQuery(this).parents(".add-prop-st-mid").find(".txt_price").show();				
			},function(){
				jQuery(this).html("Change Price");
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

