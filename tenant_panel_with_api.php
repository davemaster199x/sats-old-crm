<table id="inner_new_tenants_tbl btg" border="0" cellpadding="5" cellspacing="4" class="table-center tbl-fr-red view-property-table-inner jtenant_table">
			
			<tr>
				<td class="j_tbl_heading">Primary</td>
				<td class="j_tbl_heading">First Name</td>
				<td class="j_tbl_heading">Last Name</td>
				<td class="j_tbl_heading">Mobile</td>
				<td class="j_tbl_heading">Landline</td>
				<td class="j_tbl_heading">Email</td>
				<td class="j_tbl_heading">Action</td>
				<td class="j_tbl_heading">Crm</td>
				<?php 
				// API connected
				$tdApiTxt = null;
				$enableApi = false;

				$console_sql = mysql_query("
				SELECT `id` AS cak_count
				FROM `console_api_keys`
				WHERE `agency_id` = {$agency_id}
				");
				$console_row = mysql_fetch_object($console_sql);

				if( $console_row->cak_count > 0 ){ // console, using webhooks

					$tdApiTxt = 'Console';
					$enableApi = true;

				}else{ // other API, using agency tokens

					$sel_query = "
					agen_api_tok.`agency_api_token_id`, 
					agen_api_tok.`agency_id`, 
					agen_api_tok.`api_id`,
					agen_api.`api_name`
					";
					$api_token_params = array(
						'sel_query' => $sel_query,
						'active' => 1,
						'agency_id' => $agency_id,
						'display_query' => 0
					);
					$api_sql = $crm->get_agency_api_tokens($api_token_params);
					$api_row = mysql_fetch_array($api_sql);

					
					
					if ($api_row['api_id'] == 1) { // pme
						$tdApiTxt = "PMe";
						$enableApi = true;
					}else if ($api_row['api_id'] == 4) { // palace
						$tdApiTxt = "Palace";
						$enableApi = true;
					}else if ($api_row['api_id'] == 6) { // ourtradie
						$tdApiTxt = "OurTradie";
						$enableApi = true;
					}else if ($api_row['api_id'] == 3) { // property tree
						$tdApiTxt = "Property Tree";
						$enableApi = true;
					}					

				}	

				if ( $enableApi == true ) {
					?>
						<td class="j_tbl_heading"><?=$tdApiTxt?></td>
					<?php
				}				                   
            	?>
				<td class="j_tbl_heading">Last updated</td>
			</tr>

			<?php 
			// get existing tenants
			$tenants_params = array('property_id' => $property_id,'active' => 1);
			$sqlGetTenants = $crm->getNewTenantsData($tenants_params);
			if(mysql_num_rows($sqlGetTenants) > 0){
			while($rsTenants = mysql_fetch_array($sqlGetTenants)){

				// booked with mobile
				if( $row['booked_with'] == $rsTenants['tenant_firstname'] ){
					$booked_with_mobile = $rsTenants['tenant_mobile'];
				}

				$row_hl = '';
				$crm_tnt_row = array(
					'fname' => trim($rsTenants['tenant_firstname']),
					'lname' => trim($rsTenants['tenant_lastname']),
					'mobile' => $crm->remove_space(trim($rsTenants['tenant_mobile'])),
					'landline' => $crm->remove_space(trim($rsTenants['tenant_landline'])),
					'email' => trim($rsTenants['tenant_email']),
					'modifiedDate' => trim($rsTenants['modifiedDate'])
				);

				$crm_tenant_full_name = trim("{$crm_tnt_row['fname']} {$crm_tnt_row['lname']}");			
				
			?>
				<tr class="<?php echo $row_hl; ?>">
					<td>						
						<a title="Edit" name="tenant_priority" class="edit-tenant" tid="<?=$rsTenants['property_tenant_id']?>">
							<?php if($rsTenants['tenant_priority'] == 1): ?>
								<img src="/images/key_icon_green.png" class="tenant_priority<?=$rsTenants['property_tenant_id']?>" style="cursor: pointer;" />
							<?php endif; ?>
							<input type="checkbox" class="tenant_priority_cb<?=$rsTenants['property_tenant_id']?> tp_checkbox" name="tenant_priority" <?php echo ($rsTenants['tenant_priority'] == 1 ? "checked" : "") ?> value="<?= $rsTenants['tenant_priority'] ?>" style="cursor: pointer;display:none;" />
						</a>
					</td>
					<td>
						<input type="text" name="tenant_firstname" id="tenant_firstname_det<?=$rsTenants['property_tenant_id']?>" class="addinput tenant_fields<?=$rsTenants['property_tenant_id']?> tenant_fname_field" style="display:none; width:100px !important;" value="<?=stripslashes($rsTenants['tenant_firstname'])?>">
						<span class="tenant_labels<?=$rsTenants['property_tenant_id']?>" id="tenant_firstname_lbl<?=$rsTenants['property_tenant_id']?>"><?=$rsTenants['tenant_firstname']?></span>
					</td>
					<td>
						<input type="text" name="tenant_lastname" id="tenant_lastname_det<?=$rsTenants['property_tenant_id']?>" class="addinput tenant_fields<?=$rsTenants['property_tenant_id']?> tenant_lname_field" style="display:none; width:100px !important;" value="<?=stripslashes($rsTenants['tenant_lastname'])?>">
						<span class="tenant_labels<?=$rsTenants['property_tenant_id']?>" id="tenant_lastname_lbl<?=$rsTenants['property_tenant_id']?>"><?=$rsTenants['tenant_lastname']?></span>
					</td>
					<td>
						<input type="text" name="tenant_mobile" id="tenant_mobile_det<?=$rsTenants['property_tenant_id']?>" class="addinput tenant_fields<?=$rsTenants['property_tenant_id']?> tenant_mobile_field" style="display:none; width:100px !important;" value="<?=$rsTenants['tenant_mobile']?>">
						<?php if($rsTenants['tenant_mobile'] != ""){?>
						<a href="tel:<?=trim($rsTenants['tenant_mobile'])?>">
						<span  class="tenant_labels<?=$rsTenants['property_tenant_id']?> tenant_mobile_field_v2" id="tenant_mobile_lbl<?=$rsTenants['property_tenant_id']?>"><?=$rsTenants['tenant_mobile']?></span>
						</a>
						<?php }?>
					</td>
					<td>
						<input type="text" name="tenant_landline" id="tenant_landline_det<?=$rsTenants['property_tenant_id']?>" class="addinput tenant_fields<?=$rsTenants['property_tenant_id']?> tenant_phone_field" style="display:none; width:100px !important;" value="<?=$rsTenants['tenant_landline']?>">
						<?php if($rsTenants['tenant_landline'] != "" OR $rsTenants['tenant_landline'] == "__ ____ ____"){?>
						<a href="tel:<?=trim($rsTenants['tenant_landline'])?>">
						<span  class="tenant_labels<?=$rsTenants['property_tenant_id']?> tenant_phone_field_v2" id="tenant_landline_lbl<?=$rsTenants['property_tenant_id']?>"><?=$rsTenants['tenant_landline']?></span>
						</a>
						<?php }?>
					</td>
					<td>
						<input type="email" name="tenant_email[]" id="tenant_email_det<?=$rsTenants['property_tenant_id']?>" class="addinput tenant_fields<?=$rsTenants['property_tenant_id']?> tenant_email tenant_email_field" style="display:none; width:200px !important;" value="<?=$rsTenants['tenant_email']?>">
						<span class="tenant_labels<?=$rsTenants['property_tenant_id']?>" id="tenant_email_lbl<?=$rsTenants['property_tenant_id']?>"><?=$rsTenants['tenant_email']?></span>
					</td>
					
					<td style="white-space:normal;padding:10px;" class="job_action_div">

						<a title="Edit" class="edit-tenant" tid="<?=$rsTenants['property_tenant_id']?>">
						<img src="/images/button_icons/pencil-edit-button.png" class="default-buttton<?=$rsTenants['property_tenant_id']?>" style="cursor: pointer;" />
						</a>

						<a title="Deactivate" class="delete-tenant" tid="<?=$rsTenants['property_tenant_id']?>">
						<img src="/images/button_icons/rubbish-bin-delete-button.png" class="default-buttton<?=$rsTenants['property_tenant_id']?>" style="cursor: pointer;" />
						</a>

						<?php if($rsTenants['tenant_email'] != ""){?>

							<!--
							<a target="_blank" id="custom_email_link" href="send_email_template.php?job_id=<?=$job_id?>&to_email=<?=$rsTenants['tenant_email']?>">
								<img src="/images/button_icons/black-back-closed-envelope-shape.png" class="email_icon default-buttton<?=$rsTenants['property_tenant_id']?>" style="cursor: pointer;" />
							</a>
							-->

							<?php
							//if(  in_array($_SESSION['USER_DETAILS']['StaffID'], $crm->tester()) ){
							$crm_ci_page = "/email/send";
							$crm_ci_page_params = "job_id:{$job_row['jid']}-tenant_id:{$rsTenants['property_tenant_id']}";
							$crm_ci_page_url = $crm->crm_ci_redirect($crm_ci_page,$crm_ci_page_params); 
							?>
							<a target="_blank" href="<?php echo $crm_ci_page_url; ?>">
								<img src="/images/button_icons/black-back-closed-envelope-shape.png" class="email_icon default-buttton<?=$rsTenants['property_tenant_id']?>" style="cursor: pointer;" />
							</a>

						<?php 
						}else{ ?>
							<img src="/images/button_icons/black-back-closed-envelope-shape.png" class="email_icon default-buttton<?=$rsTenants['property_tenant_id']?>" style="opacity:0.3;" />									
						<?php }?>

						<?php 
						if( checkSmsforToday($job_id) ){ ?>
						<div class="row" style="display:inline-block; default-buttton<?=$rsTenants['property_tenant_id']?>">
							<?php if($rsTenants['tenant_mobile'] != ""){?>

								<!--<img src="/images/button_icons/sms-tenant.png" class="sms_icon_new default-buttton<?=$rsTenants['property_tenant_id']?>" style="cursor: pointer;" />-->

								<?php
								//if(  in_array($_SESSION['USER_DETAILS']['StaffID'], $crm->tester()) ){
									$crm_ci_page = "/sms/send";
									$crm_ci_page_params = "job_id:{$job_row['jid']}-tenant_id:{$rsTenants['property_tenant_id']}";
									$crm_ci_page_url = $crm->crm_ci_redirect($crm_ci_page,$crm_ci_page_params); 
								?>
									<a href="<?php echo $crm_ci_page_url; ?>" target="_bank">
										<img src="/images/button_icons/speech-bubble.png" class="send_sms_icon default-buttton<?=$rsTenants['property_tenant_id']?>" style="cursor: pointer;" />
									</a>
								<?php	
								//}
								?>								

							<?php }else{ ?>
								<!--<img src="/images/button_icons/sms-tenant-disable.png" class="sms_icon_new default-buttton<?=$rsTenants['property_tenant_id']?>"  />-->									
							<?php }?>
						</div> 
						<?php }?>

						<a href="#sms_fb_div_<?php echo $rsTenants['property_tenant_id']; ?>" class="sms_fb_link" style='display:none;'>here</a>
						
						<div style="display: none;">
							<div id="sms_fb_div_<?php echo $rsTenants['property_tenant_id']; ?>">

								<h2 class="heading">SMS Tempalate</h2>
								<?php
									// SMS TEMPLATE TEXTBOX
									foreach( $sms_temp_arr as $index => $sms_temp ){ ?>
										<div class="sms_temp_div_row">
											<div class="sms_temp_lbl">
												<div><strong><?php echo $sms_temp['sms_temp_name'] ?></strong></div>
												<?php
												if( $sms_temp['sms_temp_desc'] ){ ?>
													<div>(<?php echo $sms_temp['sms_temp_desc'] ?>)</div>
												<?php
												}								
												if( $sms_temp['sms_temp_ins'] ){ ?>
													<div class="sms_temp_insert_date jItalic"><?php echo $sms_temp['sms_temp_ins'] ?></div>
												<?php
												}
												?>												
												<div><span class="sms_temp_char_count"><?php echo $sms_temp['sms_temp_boxtext_cout'] ?></span> Char</div>
												<div><span class="sms_num_count"><span class="sms_num_count_val"><?php echo $sms_temp['sms_temp_num_count'] ?></span> SMS</span></div>
												<div><input type="radio" name="sms_temp_rad" class="sms_temp_rad" /></div>
											</div>	
											<textarea class="sms_temp_txtbox" style="width:600px !important;"><?php echo $sms_temp['sms_temp_boxtext']; ?></textarea>		
											<input type="hidden" class="tenant_mobile" value="<?=$rsTenants['tenant_mobile']?>" />							
											<input type="hidden" class="sms_type" value="<?php echo $sms_temp['sms_type']; ?>" />
											<input type="hidden" class="sms_sent_to_tenant" value="<?=$rsTenants['tenant_firstname']?>" />
											<button class="submitbtnImg btn_sms" id="btn_sms" type="button">
												<img class="inner_icon" src="images/button_icons/sms_icon.png">
												SMS
											</button>	
										</div>
										<br /><br />
									<?php
									}
									?>
								<br />
					
						
							</div>
						</div>

						

						
						<button style="display:none; float: left; margin-right: 5px;" type="button" class="blue-btn submitbtnImg save-buttton<?=$rsTenants['property_tenant_id']?> btn-save" tid="<?=$rsTenants['property_tenant_id']?>">
							Save
						</button>
						<button style="display:none; float: left;" type="button" class="submitbtnImg cancel-tenant save-buttton<?=$rsTenants['property_tenant_id']?> btn-cancel" tid="<?=$rsTenants['property_tenant_id']?>">
							Cancel
						</button>
					</td>
					<td>
						<span style='font-size:30px; color: #5dca73'>&#10004;</span>
					</td>
					<?php 
						// API ticks						
						$tdApiTxt = null;
						$enableApi = false;						

						$console_sql = mysql_query("
						SELECT `id` AS cak_count
						FROM `console_api_keys`
						WHERE `agency_id` = {$agency_id}
						");
						$console_row = mysql_fetch_object($console_sql);

						if( $console_row->cak_count > 0 ){ // console, using webhooks
							
							$tenant_loop_list = $console_tenants_arr;
							$enableApi = true;

							foreach( $tenant_loop_list as $api_tenant ){

								$tenant_already_exist = 0;
								$row_hl = "#dbe4ea";
								$contact_num_match = false;
								$email_match = false;
	
								// if tenants name is blank, use company name instead as firstname
								if( $api_tenant['fname'] == '' && $api_tenant['lname'] == '' ){
									$api_tenant_full_name = trim($api_tenant['company_name']);
								}else{
									$api_tenant_full_name = trim("{$api_tenant['fname']} {$api_tenant['lname']}");
								}
								
								// match contact number, either mobile, landline or etc..
								foreach( $api_tenant['phone'] as $contact_arr ){

									if( 
										$crm_tnt_row['mobile'] == $contact_arr['number'] ||  
										$crm_tnt_row['landline'] == $contact_arr['number']
									){
										$contact_num_match = true;
									}

								}

								// email match
								foreach( $api_tenant['email'] as $email_arr ){

									if( $crm_tnt_row['email'] == $email_arr['email'] ){
										$email_match = true;
									}

								}

								// if tenant name, contact details and email match, color green tick
								if( 
									$crm_tenant_full_name == $api_tenant_full_name &&
									$contact_num_match == true  &&
									$email_match == true
								){
									$tenant_already_exist = 1;
								}

							}

						}else{ // other API, using agency tokens

							$sel_query = "
								agen_api_tok.`agency_api_token_id`, 
								agen_api_tok.`agency_id`, 
								agen_api_tok.`api_id`,
								agen_api.`api_name`
							";
							$api_token_params = array(
								'sel_query' => $sel_query,
								'active' => 1,
								'agency_id' => $agency_id,
								'display_query' => 0
							);
							$api_sql = $crm->get_agency_api_tokens($api_token_params);
							$api_row = mysql_fetch_array($api_sql);

							$enableApi = false;
							if ($api_row['api_id'] == 1) { // pme
								$tenant_loop_list = $pme_tenants_arr;
								$enableApi = true;
							}else if ($api_row['api_id'] == 4) { // palace
								$tenant_loop_list = $palace_tenants_arr;
								$enableApi = true;
							}else if ($api_row['api_id'] == 6) { // ourtradie
								$tenant_loop_list = $ourtradie_tenants_arr;
								$enableApi = true;
							}else if ($api_row['api_id'] == 3) { // Property Tree
								$tenant_loop_list = $pt_tenants_arr;
								$enableApi = true;
							}		
							
							foreach( $tenant_loop_list as $api_tenant ){

								$tenant_already_exist = 0;
								$tenant_has_update = 0;
								$not_in_pme = 0;
								$row_hl = "#dbe4ea";
	
								// if tenants name is blank, use company name instead as firstname
								if( $api_tenant['fname'] == '' && $api_tenant['lname'] == '' ){
									$api_tenant_full_name = trim($api_tenant['company_name']);
								}else{
									$api_tenant_full_name = trim("{$api_tenant['fname']} {$api_tenant['lname']}");
								}
								
								if( 
									$crm_tenant_full_name == $api_tenant_full_name &&
									$crm_tnt_row['mobile'] == $api_tenant['mobile']  &&
									$crm_tnt_row['landline'] == $api_tenant['landline']  &&
									$crm_tnt_row['email'] == $api_tenant['email']  
								){
									$tenant_already_exist = 1;
								}else{
									if( $crm_tenant_full_name == $api_tenant_full_name ){
										$tenant_has_update = 1;
									}else{
										$not_in_pme = 1;
									}
								}					
							}
							
						}																							

						// highlight color
						if( $tenant_already_exist == 1 ){
							$row_hl = "#5dca73"; // green
						}else if( $tenant_has_update == 1 || $not_in_pme == 1 ){
							$row_hl = "#dbe4ea"; // grey
						}

						if ($enableApi) {
						?>
							<td>
								<span style='font-size:30px; color: <?=$row_hl;?>'>&#10004;</span>
							</td>
						<?php
						}
					?>
					<td>					
						<?php
						if( $rsTenants['modifiedDate'] != '' && $rsTenants['modifiedDate'] != "0000-00-00 00:00:00" ){
							echo date('d/m/Y H:i', strtotime($rsTenants['modifiedDate']));
						}else{
							echo date('d/m/Y H:i', strtotime($rsTenants['createdDate']));
						}					
						?>
					</td>
				</tr>	

			<?php }
						
			}else{
				echo "<tr><td colspan='100%' style='text-align: center; padding: 10px;'>No tenant found.</td></tr>";
				
			}

			//exit();

				// NEW TENANTS
				$enableApi = false;
				$apiTxt = "";
				if ( count($pme_tenants_arr) > 0 ) { // pme
					$tenant_loop_list = $pme_tenants_arr;
					$enableApi = true;
					$apiTxt = "PropertyMe";
					$api_id = 1;
				}else if ( count($palace_tenants_arr) > 0 ) { // palace
					$tenant_loop_list = $palace_tenants_arr;
					$enableApi = true;
					$apiTxt = "Palace";
					$api_id = 4;
				}else if ( count($ourtradie_tenants_arr) > 0 ) { // palace
					$tenant_loop_list = $ourtradie_tenants_arr;
					$enableApi = true;
					$apiTxt = "OurTradie";
					$api_id = 6;
				}else if ( count($pt_tenants_arr) > 0 ) { // property tree
					$tenant_loop_list = $pt_tenants_arr;
					$enableApi = true;
					$apiTxt = "Property Tree";
					$api_id = 3;
				}else if ( count($console_tenants_arr) > 0 ) { // console
					//$tenant_loop_list = $console_tenants_arr;
					$enableApi = true;
					$apiTxt = "Console";
					$api_id = 5;
				}

	            foreach ($tenant_loop_list as $api_tnt_row) {	 

					if($api_id == 6){
						$pme_tenants_full_name = "{$api_tnt_row['fname']}";

						$row_hl = '';
						$tenant_already_exist = 0;
						$tenant_has_update = 0;
						$new_tenant = 0;

						foreach ($crm_tenants_arr as $crm_tenant) {
							$crm_tenant_full_name = trim("{$crm_tenant['fname']}");

							// same all 5 fields
							if (
								$crm_tenant_full_name == $pme_tenants_full_name &&
								$crm_tenant['mobile'] == $api_tnt_row['mobile'] &&
								$crm_tenant['landline'] == $api_tnt_row['landline'] &&
								$crm_tenant['email'] == $api_tnt_row['email']
							) {
								$tenant_already_exist = 1;
							} else {
	
								if ($crm_tenant_full_name == $pme_tenants_full_name) {
									$tenant_has_update = 1;
								} else {
									$new_tenant = 1;
								}
							}
							
						}
					}
					else{
						// hide tenants already added
						$pme_tenants_full_name = "{$api_tnt_row['fname']} {$api_tnt_row['lname']}";

						$row_hl = '';
						$tenant_already_exist = 0;
						$tenant_has_update = 0;
						$new_tenant = 0;

						foreach ($crm_tenants_arr as $crm_tenant) {

							$crm_tenant_full_name = trim("{$crm_tenant['fname']} {$crm_tenant['lname']}");
	
							// same all 5 fields
							if (
								trim($crm_tenant_full_name) == trim($pme_tenants_full_name) &&
								trim($crm_tenant['mobile']) == trim($api_tnt_row['mobile']) &&
								trim($crm_tenant['landline']) == trim($api_tnt_row['landline']) &&
								trim($crm_tenant['email']) == trim($api_tnt_row['email'])
							) {
								$tenant_already_exist = 1;
							} else {
	
								if ($crm_tenant_full_name == $pme_tenants_full_name) {
									$tenant_has_update = 1;
								} else {
									$new_tenant = 1;
								}
							}
							
						}
					}
	                

	                // highlight color
	                if ($tenant_already_exist == 1) {
	                    $row_hl = "PMe_tenant_exist_bg hideIt";
	                } else if ($tenant_has_update == 1) {
	                    //$row_hl = "crm_tenant_need_update_bg";
	                } else if ($new_tenant == 1) {
	                    //$row_hl = "PMe_tenant_new_bg";
					}
					
					// if tenants name is blank, use company name instead as firstname
					if( $api_tnt_row['fname'] == '' && $api_tnt_row['lname'] == '' ){
						$tenant_fname = $api_tnt_row['company_name'];
						$tenant_lname = '';
					}else{
						$tenant_fname = $api_tnt_row['fname'];
						$tenant_lname = $api_tnt_row['lname'];
					}

					if( $api_id != 5 ){ // do not show this on console API
	                ?>

	                <tr class="<?php echo $row_hl; ?>">
						<td>
							<a title="Edit" name="tenant_priority" class="edit-tenant" tid="<?=$rsTenants['property_tenant_id']?>">
								<?php if($rsTenants['tenant_priority'] == 1): ?>
									<img src="/images/key_icon_green.png" class="tenant_priority<?=$rsTenants['property_tenant_id']?>" style="cursor: pointer;" />
								<?php endif; ?>
								<input type="checkbox" class="tenant_priority_cb<?=$rsTenants['property_tenant_id']?> tp_checkbox" name="tenant_priority" <?php echo ($rsTenants['tenant_priority'] == 1 ? "checked" : "") ?> value="<?= $rsTenants['tenant_priority'] ?>" style="cursor: pointer;display:none;" />
							</a>
	                    </td>
	                    <td>
	                        <?php echo $tenant_fname; ?>
	                    </td>
	                    <td>
	                        <?php echo $tenant_lname; ?>
	                    </td>
	                    <td>
	                        <?php echo $api_tnt_row['mobile']; ?>
	                    </td>
	                    <td>
	                        <?php echo $api_tnt_row['landline']; ?>
	                    </td>
	                    <td>
	                        <?php echo $api_tnt_row['email']; ?>
						</td>						
	                    <td>
							<?php
							if($api_id == 6){
							?>
								<input type="hidden" class="ourtradie_tenant_fname" value="<?php echo $tenant_fname; ?>" />
								<input type="hidden" class="ourtradie_tenant_lname" value="<?php echo $tenant_lname; ?>" />
								<input type="hidden" class="ourtradie_tenant_mobile" value="<?php echo $api_tnt_row['mobile']; ?>" />
								<input type="hidden" class="ourtradie_tenant_landline" value="<?php echo $api_tnt_row['landline']; ?>" />
								<input type="hidden" class="ourtradie_tenant_email" value="<?php echo $api_tnt_row['email']; ?>" />
								<input type="hidden" class="ourtradie_api_txt" value="<?php echo $apiTxt; ?>" />

								<button type="button" class="blue-btn submitbtnImg add_new_ourtradie_tenant_btn">
									<img class="inner_icon" src="images/button_icons/add-button.png">
									<span class="inner_icon_txt">Add</span>
								</button>
							<?php }
							else{
							?>
								<input type="hidden" class="pme_tenant_fname" value="<?php echo $tenant_fname; ?>" />
	                        	<input type="hidden" class="pme_tenant_lname" value="<?php echo $tenant_lname; ?>" />
								<input type="hidden" class="pme_tenant_mobile" value="<?php echo $api_tnt_row['mobile']; ?>" />
								<input type="hidden" class="pme_tenant_landline" value="<?php echo $api_tnt_row['landline']; ?>" />
								<input type="hidden" class="pme_tenant_email" value="<?php echo $api_tnt_row['email']; ?>" />
								<input type="hidden" class="pme_api_txt" value="<?php echo $apiTxt; ?>" />

								<button type="button" class="blue-btn submitbtnImg add_new_pme_tenant_btn" style="    margin: 5px;">
									<img class="inner_icon" src="images/button_icons/add-button.png"> 
									<span class="inner_icon_txt">Add</span>
								</button>
							<?php }
							?>

	                    </td>

						<td>
							<span style='font-size:30px; color: #dbe4ea'>&#10004;</span>
						</td>

						<?php 
	                    $sel_query = "
	                        agen_api_tok.`agency_api_token_id`, 
	                        agen_api_tok.`agency_id`, 
	                        agen_api_tok.`api_id`,
	                        agen_api.`api_name`
	                    ";
	                    $api_token_params = array(
	                        'sel_query' => $sel_query,
	                        'active' => 1,
	                        'agency_id' => $agency_id,
	                        'display_echo' => 0
	                    );
	                    $api_sql_3 = $crm->get_agency_api_tokens($api_token_params);
	                    while ($api_row_3 = mysql_fetch_array($api_sql_3)) {
	                    	
			                $enableApi = false;
			            	if ($api_row_3['api_id'] == 1) { // pme
			                	$enableApi = true;
			            	}else if ($api_row_3['api_id'] == 4) { // palace
			                	$enableApi = true;
			        		}else if ($api_row_3['api_id'] == 6) { // ourtradie
			                	$enableApi = true;
			        		}else if ($api_row_3['api_id'] == 3) { // property tree
			                	$enableApi = true;
			        		}

							$row_hl = "#dbe4ea";
	                    	if ($api_row_3['api_id'] == 1 && $api_id == 1) { // pme
								$row_hl = "#5dca73"; 
	                    	}else if ($api_row_3['api_id'] == 4  && $api_id == 4) { // palace
								$row_hl = "#5dca73"; 
							}else if ($api_row_3['api_id'] == 6  && $api_id == 6) { // ourtradie
								$row_hl = "#5dca73"; 
							}else if ($api_row_3['api_id'] == 3  && $api_id == 3) { // property tree
								$row_hl = "#5dca73"; 
							}
							
							if ( $enableApi  == true ) {												
		            	?>
							<td>
								<span style='font-size:30px; color: <?=$row_hl;?>'>&#10004;</span>
							</td>
					<?php
							}

	                    }
	            	?>
						<td>
	                        <?php //echo empty($api_tnt_row['UpdatedOn']) ? "" : date('d/m/Y H:i', strtotime($api_tnt_row['UpdatedOn'])); ?>
							<?php echo empty($api_tnt_row['UpdatedOn']) ? "" : date('d/m/Y H:i'); ?>
						</td>
	                </tr>

                <?php
					}
	            }	        
            ?>
		</table>


<?php 

if( $api_id == 5 ){ ?>

<h2 class="heading" style="margin-top:10px;">Console Tenants:</h2>
<table border="0" cellpadding="5" cellspacing="4" class="table-center tbl-fr-red view-property-table-inner">
	<thead>
		<tr>
			<th class="j_tbl_heading">First Name</th>
			<th class="j_tbl_heading">Last Name</th>
			<th class="j_tbl_heading">Phone</th>
			<th class="j_tbl_heading">Email</th>
			<th class="j_tbl_heading">Action</th>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach( $console_tenants_arr as $console_tenants_row ){ ?>
		<tr>
			<td>
				<?php echo $console_tenants_row['fname']; ?>
				<input type="hidden" class="console_tenant_fname" value="<?php echo $console_tenants_row['fname']; ?>" />
			</td>
			<td>
				<?php echo $console_tenants_row['lname']; ?>
				<input type="hidden" class="console_tenant_lname" value="<?php echo $console_tenants_row['lname']; ?>" />
			</td>
			<td>
				<table clas="table-center tbl-fr-red view-property-table-inner">
					<tr>
						<th class="j_tbl_heading">Type</th>
						<th class="j_tbl_heading">Number</th>
						<th class="j_tbl_heading">Primary</th>  
						<th class="j_tbl_heading">Select As</th>                                                      
					</tr>				
				<?php 
				foreach( $console_tenants_row['phone'] as $console_tenants_phone ){ ?>

					<tr>
						<td><?php echo ucwords(strtolower($console_tenants_phone['type'])); ?></td>
						<td>
							<?php echo $console_tenants_phone['number']; ?>
							<input type="hidden" class="console_tenant_phone_number" value="<?php echo $console_tenants_phone['number']; ?>" />
						</td>
						<td>
							<?php echo ( $console_tenants_phone['primary'] == 1 )?'<span style="color:green">Yes</span>':'<span style="color:red">No</span>'; ?>
							<input type="hidden" class="console_tenant_primary" value="<?php echo $console_tenants_phone['primary']; ?>" />
						</td>
						<td>
							<select class="form-control select_phone_type">
								<option value="">---</option>
								<option value="1">Mobile</option>
								<option value="2">Landline</option>
							</select>
						</td>                                                                
					</tr>

				<?php
				}
				?>
				</table>
			</td>
			<td>
				<table clas="table-center tbl-fr-red view-property-table-inner">
					<tr>
						<th class="j_tbl_heading">Type</th>
						<th class="j_tbl_heading">Email</th>
						<th class="j_tbl_heading">Primary</th>  
						<th class="j_tbl_heading">Select</th>                                                       
					</tr>				
				<?php 
				foreach( $console_tenants_row['email'] as $console_tenants_email ){ ?>

					<tr>
						<td><?php echo ucwords(strtolower($console_tenants_email['type'])); ?></td>
						<td>
							<?php echo $console_tenants_email['email']; ?>
							<input type="hidden" class="console_tenant_phone_number" value="<?php echo $console_tenants_email['email']; ?>" />
						</td>
						<td>
							<?php echo ( $console_tenants_email['primary'] == 1 )?'<span style="color:green">Yes</span>':'<span style="color:red">No</span>'; ?>
						</td>
						<td>
							<input type="radio" class="select_email console_tenant_email" name="select_email" value="<?php echo $console_tenants_email['email']; ?>" />
						</td>                                                                
					</tr>

				<?php
				}
				?>
				</table>
			</td>
			<td>
				<button type="button" class="blue-btn submitbtnImg add_console_tenants">
					<img class="inner_icon" src="images/button_icons/add-button.png"> 
					<span class="inner_icon_txt">Save</span>
				</button>
			</td>
		</tr>
	<?php
	}
	?>
	</tbody>
</table>

<?php	
}

?>
<script>
jQuery(document).ready(function(){

	jQuery(".add_console_tenants").click(function(){

		var property_id = <?php echo $property_id; ?>;
		var save_btn_dom = jQuery(this);
		var row_dom = save_btn_dom.parents("tr:first");					

		var console_tenant_fname = row_dom.find(".console_tenant_fname").val();
		var console_tenant_lname = row_dom.find(".console_tenant_lname").val();
		var console_tenant_primary = row_dom.find(".console_tenant_primary").val();

		var console_tenant_mobile_arr = [];
		var console_tenant_landline_arr = [];

		var error = '';

		row_dom.find(".select_phone_type").each(function(){

			var pt_dom = jQuery(this);
			var pt_dom_val = pt_dom.val();
			var phone_row_dom = pt_dom.parents("tr:first");
			var console_tenant_phone_number = phone_row_dom.find(".console_tenant_phone_number").val();	
			
			if( pt_dom_val == 1 ){ // mobile									
				console_tenant_mobile_arr.push(console_tenant_phone_number);
			}

			if( pt_dom_val == 2 ){ // landline					
				console_tenant_landline_arr.push(console_tenant_phone_number);
			}

		});

		var console_tenant_email = row_dom.find(".console_tenant_email:checked").val();

		if( console_tenant_mobile_arr.length > 1 ){
			error += "Can only select 1 mobile number per tenant\n";
		}

		if( console_tenant_landline_arr.length > 1 ){
			error += "Can only select 1 landline per tenant\n";
		}


		if( property_id > 0 ){

			if( error != '' ){ // error

				alert(error);

			}else{ // success

				$('#load-screen').show(); 
				$.ajax({
					url: "ajax_save_console_tenant.php",
					type: 'POST',
					data: { 
						'property_id': property_id, 
						'tenant_firstname' : console_tenant_fname, 
						'tenant_lastname' : console_tenant_lname, 
						'tenant_mobile' :  console_tenant_mobile_arr[0], 
						'tenant_landline' :  console_tenant_landline_arr[0], 
						'tenant_email' : console_tenant_email,
						'tenant_primary': console_tenant_primary
					}
				}).done(function( ret ){
					
					$('#load-screen').hide();                    
					location.reload();

				});

			}				

		}			

	});

});
</script>