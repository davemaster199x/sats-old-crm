<?php
// THIS PAGE HAS CRON, ANY UPDATES SHOULD ALSO BE DONE THERE
$title = "SMS";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');



?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.highlight_grey{
	background-color: #eeeeee;
}
.indv_tenants_num_div {
    margin: 5px 0;
}
</style>





<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="SMS" href="/sms.php"><strong>SMS</strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">SMS Sent</div>';
		}
		
		
		?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		
		<?php
			$date = ($_REQUEST['date']!="")?date("Y-m-d",strtotime(str_replace("/","-",$_REQUEST['date']))):$now = date("Y-m-d",strtotime("+1 day"));
		?>
		<div class="aviw_drop-h" style="border: 1px solid #ccc; border-bottom: none;">		 
			<div class="fl-left" style="float:left;">
				<form method="POST">
				<label>Date:</label>
				<input type="text" name="date" class="datepicker" style="margin: 0 3px 0 2px; width: 70px;" value="<?php echo date("d/m/Y",strtotime($date)); ?>" />								
			
				<button type="submit" class="submitbtnImg">
					<img class="inner_icon" src="images/button_icons/search-button.png">
					<span class="inner_icon_txt">Submit</span>
				</button>
				</form>
			</div>	
			
			<style>
			.fl-left li {
				display: inline;
				padding: 0 10px;
			}
			</style>
			<div class="fl-left" style="float:left;">
				<ul style="text-align: left;">
					<li>1. Select Template</li>
					<li>2. Preview Message</li>
					<li>3. Tick all boxes with mobile numbers EXCEPT door knocks</li>
					<li>4. Send SMS</li>
				</ul>
			</div>
			
			<div class="fl-left" style="float:right;">
				<label>Message:</label>
				<?php
				// get sms messages
				$msg_sql = mysql_query("
					SELECT *
					FROM `sms_messages`
					WHERE `country_id` = {$_SESSION['country_default']}
					ORDER BY `title` ASC
				");		
				?>
				<select id="sms_msg_id">
				<?php
					while($msg = mysql_fetch_array($msg_sql)){ ?>
					<option value="<?php echo $msg['sms_messages_id']; ?>"><?php echo $msg['title']; ?></option>
					<?php
					}
				?>
				</select>
			</div>
			
		</div>
		
		<form action="sms_script.php" method="post">
			<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">
				<tr class="toprow jalign_left">
					<th>Service</th>
					<th>Address</th>		
					<th>Tenants</th>
					<th>Time</th>
					<th>Notes</th>
					<th>DK</th>
					<th>Vacant</th>
					<th>Message</th>
					<th>Status</th>
					<th><input type="checkbox" id="chk_all" /></th>
				</tr>
					<?php
					
					// get date default is today
					//$date = ($_REQUEST['date']!="")?date("Y-m-d",strtotime(str_replace("/","-",$_REQUEST['date']))):$now = date("Y-m-d");
					
					// get jobs for sms
					$sql_str = "
						SELECT *, j.`id` AS jid, j.`service` AS jservice, p.`address_1` AS paddress1, p.`address_2` AS paddress2, p.`address_3` AS paddress3, j.`property_id` AS jprop_id, j.status AS jstatus, j.`property_vacant`
						FROM `jobs` AS j
						LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
						LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
						WHERE j.status = 'Booked' 
						AND j.date = '{$date}' 
						AND p.deleted = 0
						AND j.`del_job` = 0
						AND a.`status` = 'active'
						AND a.`country_id` = {$_SESSION['country_default']}
						AND j.`door_knock` = 0
						ORDER BY p.`address_2` ASC
					";
					$sql = mysql_query($sql_str);
									
					
					if(mysql_num_rows($sql)>0){
						$i = 0;
						while($row = mysql_fetch_array($sql)){
							
							$tr_class = '';
							if( ($row['door_knock']==1 && $row['jstatus']=="Booked") || $row['key_access_required']==1 ){
								$tr_class = 'highlight_grey';
							}
							
							
							
							$pt_params = array( 
								'property_id' => $row['property_id'],
								'active' => 1
							 );
							$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
							
							$has_mobile_num = 0;
							while( $pt_row = mysql_fetch_array($pt_sql) ){
								
								// check if there is at least 1 tenant mobile
								if( $pt_row['tenant_mobile'] != "" ){
									$has_mobile_num = 1;
								}
								
								
							}
							
							if( $has_mobile_num == 1 ){
								
								
							
					?>
							<tr class="body_tr jalign_left <?php echo $tr_class; ?>">
								<td>
									<input type="hidden" name="job_id[]" class="job_id" value="<?php echo $row['jid']; ?>" />
									<input type="hidden" name="prop_id[]" class="prop_id" value="<?php echo $row['jprop_id']; ?>" />
									<?php 
									//echo getServiceName($row['jservice']); 
									$ajt_sql = mysql_query("
										SELECT *
										FROM `alarm_job_type`
										WHERE `active` = 1
										AND `id` = {$row['jservice']}
									");
									$ajt = mysql_fetch_array($ajt_sql);
									echo $ajt['type'];
									?>
								</td>
								<td>
									<a href="/view_job_details.php?id=<?php echo $row['jid']; ?>">
										<?php echo "{$row['paddress1']} {$row['paddress2']}"; ?>
									</a>
								</td>
								<td>			
									<button class="blue-btn submitbtnImg view_tenants_btn" type="button">
										<img class="inner_icon" src="images/button_icons/show-button.png">
										<span class="inner_icon_txt">View</span>
									</button>
									
									<a href="#tenants_num_div_<?php echo $row['jid']; ?>" class="fb_link" style='display:none;'>here</a>
									<div style="display:none;">
										
										<div id="tenants_num_div_<?php echo $row['jid']; ?>">	
											<table class="tbl-sd">
												<tr class="toprow jalign_left">
													<th>Name</th>
													<th>Mobile</th>
												</tr>																							
												<?php
												// new tenants switch
												//$new_tenants = 0;
												$new_tenants = NEW_TENANTS;

												if( $new_tenants == 1 ){ // NEW TENANTS

													$pt_params = array( 
														'property_id' => $row['property_id'],
														'active' => 1
													 );
													$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
													
													while( $pt_row = mysql_fetch_array($pt_sql) ){ ?>
														<tr>
															<td>
																<input type="text" name="tenant_firstname<?php echo $i; ?>[]" value="<?php echo $pt_row['tenant_firstname']; ?>" readonly="readonly" />
															</td>
															<td>
																<input type="text" name="tenant_mobile<?php echo $i; ?>[]" class="edit_mob_field" value="<?php echo $pt_row['tenant_mobile']; ?>"  />																
															</td>
														</tr>
													<?php	
													}

												}else{ // OLD TENANTS
												
													$num_tenants = getCurrentMaxTenants();
													for( $pt_i=1; $pt_i<=$num_tenants; $pt_i++ ){ 
													?>															
														<tr>
															<td>
																<input type="text" name="tenant_firstname[]" value="<?php echo $row['tenant_firstname'.$pt_i]; ?>" readonly="readonly" />
															</td>
															<td>
																<input type="text" name="tenant_mob<?php echo $pt_i; ?>[]" class="edit_mob_field<?php echo $pt_i; ?>" value="<?php echo $row['tenant_mob'.$pt_i]; ?>"  />																
															</td>
														</tr>
													<?php														
													}
													
												}											
												?>	
											</table>
										</div>
									</div>
								</td>		
								<td><?php echo $row['time_of_day']; ?></td>
								<td><?php echo $row['tech_notes']; ?></td>
								<td><?php echo ($row['door_knock']==1)?'Yes':''; ?></td>
								<td>
									<?php 
									if( $row['property_vacant']==1 ){ ?>
										<img title="No Tenants" class="no_tenant_icon" style="cursor: pointer;" src="/images/no_tenant.png" />
									<?php	
									}
									?>
								</td>
								<td><a href="javascript:void(0);" class="preview">Preview</a></td>
								<td><?php echo $row['jstatus']; ?></td>	
								<td><input type="checkbox" name="job_chk[]" class="job_chk" value="<?php echo $i; ?>" /></td>
							<tr>
					<?php
							$i++;
							}
						
						}
					}else{ ?>
						<td colspan="10" align="left">Empty</td>
					<?php
					}
					?>
			</table>	

			<div class="jalign_left">
				<input type="hidden" name="sms_msg_id" id="hid_sms_msg_id" value="0" />
				
				<button type="submit" class="submitbtnImg">
					<img class="inner_icon" src="images/button_icons/sms_icon.png">
					<span class="inner_icon_txt">Send SMS</span>
				</button>
			</div>
		</form>
		
		
	</div>
</div>

<br class="clearfloat" />



<script>
jQuery(document).ready(function(){
	
	
	
	// fancy box
	jQuery(".fb_link").fancybox();
	
	
	jQuery(".view_tenants_btn").click(function(){
		
		jQuery(this).parents("td:first").find(".fb_link").click();
		
	});
	
	
	/*
	// view tenants script
	// add page
	jQuery(".view_tenants_btn").click(function(){
		
		var btn_txt = jQuery(this).find(".inner_icon_txt").html();
		var orig_btn_txt = 'View';
		var orig_btn_icon = 'images/button_icons/show-button.png';
		var cancel_btn_icon = 'images/button_icons/cancel-button.png';
		
		if( btn_txt == orig_btn_txt ){
			jQuery(this).removeClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html('Cancel');
			jQuery(this).find(".inner_icon").attr("src",cancel_btn_icon)
			jQuery(".tenants_num_div").show();
		}else{
			jQuery(this).addClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html(orig_btn_txt);
			jQuery(this).find(".inner_icon").attr("src",orig_btn_icon)
			jQuery(".tenants_num_div").hide();
		}
		
		
	});
	*/
	
	

	// get sms ID
	function getSmsMsgId(){
		var msg = jQuery("#sms_msg_id").val();
		jQuery("#hid_sms_msg_id").val(msg);
	}
	
	getSmsMsgId();
	
	jQuery("#sms_msg_id").change(function(){
		getSmsMsgId();
	});

	
	
	// preview sms msg
	jQuery(".preview").click(function(){
		var job_id = jQuery(this).parents("tr:first").find(".job_id").val();
		var sms_msg_id = jQuery("#sms_msg_id").val();
		jQuery.ajax({
			type: "POST",
			url: "ajax_preview_sms_message.php",
			data: { 
				job_id: job_id,
				sms_msg_id: sms_msg_id
			}
		}).done(function( ret ){
			alert(ret);
		});	
	});
	

	// check all toggle
	jQuery("#chk_all").click(function(){
		if(jQuery(this).prop("checked") == true){
			jQuery(".job_chk").each(function(){
				
				if( jQuery(this).parents("tr:first").hasClass("highlight_grey")==false ){ // exclude grey rows
					
					//jQuery(this).parents("tr:first").addClass("jredHighlightRow");
					jQuery(this).prop("checked",true);
					
				}
				
			});
			//jQuery(".job_chk").prop("checked",true);
		}else{
			
			//jQuery(".jredHighlightRow").removeClass("jredHighlightRow");
			jQuery(".job_chk").prop("checked",false);
			
		}
	});
	
	/*
	jQuery(".job_chk").click(function(){
		
		
		var chk = jQuery(this).prop("checked");
		
		console.log(chk);
		
		if( chk == true ){
			
			
			if( jQuery(this).parents("tr:first").hasClass("highlight_grey")==false ){ // exclude grey rows
						
				//jQuery(this).parents("tr:first").addClass("jredHighlightRow");
				jQuery(this).prop("checked",true);
				
			}
			
		}else{
			
			//jQuery(this).parents("tr:first").removeClass("jredHighlightRow");
			jQuery(this).prop("checked",false);
			
		}
		
		
		
	});
	*/
	
});
</script>


</body>
</html>