<?php

$title = "MM Pre Com";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');



// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class;

//include('inc/Openssl_Encrypt_Decrypt.php'); 
$encrypt_decrypt = new Openssl_Encrypt_Decrypt();

$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$job_type = mysql_real_escape_string($_REQUEST['job_type']);
$service = mysql_real_escape_string($_REQUEST['service']);
$state = mysql_real_escape_string($_REQUEST['state']);
$mp = mysql_real_escape_string($_REQUEST['maintenance_program']);
if($_POST['postcode_region_id']){
	$filterregion = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$filterregion = $_GET['postcode_region_id'];
	//echo $filterregion;
}
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$job_status = 'DHA';

// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'p.`postcode`';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort=".urlencode($sort)."&order_by=".urlencode($order_by)."&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase)."&postcode_region_id=".$filterregion."&maintenance_program=".urlencode($mp);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


$jparams = array(
	'dha_need_processing' => 1,
	'join_maintenance_program' => 1,
	'api_token_join' => 1,
	
	'date' => $date,
	'phrase' => $phrase,
	'maintenance_id' => $mp,
	
	'custom_filter' => " 
		AND ( j.`status` = 'Merged Certificates' OR j.`status` = 'Completed' ) 
		AND ( j.`assigned_tech` != 1 OR j.`assigned_tech` IS NULL )
		AND j.`date` >= am.`updated_date`
	 ",
	'sort_list' => array(
		array(
			'order_by' => 'a.`agency_name`',
			'sort' => 'ASC'
		)
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'display_echo' => 0
);
$plist = $crm->getJobsData($jparams);

$jparams = array(
	'dha_need_processing' => 1,
	'join_maintenance_program' => 1,
	'api_token_join' => 1,
	
	'date' => $date,
	'phrase' => $phrase,
	'maintenance_id' => $mp,
	
	'custom_filter' => " 
		AND ( j.`status` = 'Merged Certificates' OR j.`status` = 'Completed' ) 
		AND ( j.`assigned_tech` != 1 OR j.`assigned_tech` IS NULL )
		AND j.`date` >= am.`updated_date`
	 ",
	'return_count' => 1
);
$ptotal = $crm->getJobsData($jparams);



?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.yello_mark{
	background-color: #ffff9d;
}
.green_mark{
	background-color: #c2ffa7;
}
#btn_search{
	margin: 0 !important; 
	padding: 5px !important;
}
.btn_email_agency{
	cursor: pointer;
	margin-left: 5px;
}
.leftGreyBorder{
	border-left: 1px solid #cccccc;
}
</style>





<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
		<div class="sats-breadcrumb">
			<ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="MM Needs Processing" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong>MM Needs Processing</strong></a></li>
			</ul>
		</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Agency Update Successfull</div>';
		}
		
		
		//echo date('Y-m-d', strtotime("-30 days"));
		
		
		?>
		
		
		<form method="POST" name='example' id='example'>
			<input type='hidden' name='status' value='<?php echo $status ?>'>

			<table border=1 cellpadding=0 cellspacing=0 width="100%">
				<tr class="tbl-view-prop">
				<td>

				<div class="aviw_drop-h aviw_drop-vp" id="view-jobs">

				 
					
					
					<div class='fl-left'>
						<label>Date:</label><input type=label name='date' value='<?php echo $_REQUEST['date']; ?>' class='addinput searchstyle datepicker vwjbdtp' style="width:85px!important;">		
					</div>
					
					
					<div class='fl-left'>
						<label>Phrase:</label>
						<input type=label name='phrase' value="<?php echo $_REQUEST['phrase']; ?>" class='addinput searchstyle vwjbdtp' style='width: 100px !important;'>
					</div>
					
					
					<div class='fl-left'>
						<label>Maintenance Program:</label>
						<select name="maintenance_program">
						<option value=''>None</option>
						<?php
						$jparams = array(
							'distinct' => 'am.`maintenance_id`',
							'dha_need_processing' => 1,
							'join_maintenance_program' => 1,
							'custom_filter' => " AND ( j.`status` = 'Merged Certificates' OR j.`status` = 'Completed' ) AND ( j.`assigned_tech` != 1 OR j.`assigned_tech` IS NULL ) "
						);
						$am_sql = $crm->getJobsData($jparams);											
						while( $am = mysql_fetch_array($am_sql) ){ ?>
							<option value="<?php echo $am['maintenance_id']; ?>" <?php echo ($am['maintenance_id']==$mp)?'selected="selected"':''; ?>><?php echo $am['m_name']; ?></option>
						<?php	
						}
						
						?>
						</select>
					</div>
					
					<div class='fl-left'>
						<label>&nbsp;</label>
						<button type='submit' class='submitbtnImg' id="btn_search">
							<img class="inner_icon" src="images/search.png">
							Search
						</button>
					</div>

					<div class='fl-left' style="float:left; padding-top: 16px;">
						Login: <a style="margin:0!important;" target="__blank" href="https://my.mmgr.com.au/index.php/site/login">Maintenance Manager</a>
					</div>

				<!-- duplicated filter here -->



				<div class='fl-right'>
					<button type='button' class='submitbtnImg' id="refresh_btn">
						<img class="inner_icon" src="images/rebook.png">
						Refresh
					</button>
				</div>


					  
					  
				</td>
				</tr>
			</table>	  
				  
			</form>
			
			
			<?php
			
			// no sort yet
			if($_REQUEST['sort']==""){
				$sort_arrow = 'up';
			}
			
			?>

		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
			<tr class="toprow jalign_left">
				<th>Date</th>
				<th>Software</th>
				<th>Agency</th>
				<th>MITM/Work Order</th>
				<th>Invoice Number</th>
				<th>Address</th>
				<th>Inv. Amount</th>
				<th>Invoice/Cert</th>
				<th class="leftGreyBorder">Invoice</th>
				<th class="leftGreyBorder">Quote Amount</th>
				<th>QLD Upgrade Quote</th>
				<th>Needs Processing</th>

			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){

						$fade_it = false;
						
						// grey alternation color
						$row_color = ($i%2==0)?"style='background-color:#eeeeee;'":"";

						// $palace_postable = ( $row['palace_prop_id'] !='' && $row['palace_supplier_id'] !='' && $row['palace_diary_id'] !='' )?true:false;
						// $pme_postable = ( $row['propertyme_prop_id'] !='' && $row['pme_supplier_id'] !='' && $row['connection_date'] !='' )?true:false;

						$api = ( ($row['api_prop_id'] !='' && $row['pme_supplier_id'] !='' && $row['connection_date'] !='') || ( $row['api_prop_id'] !='' && $row['palace_supplier_id'] !='' && $row['palace_diary_id'] !='' ) )?true:false;
						

						// fade send invoice button
						// if( 
						// 	date('Y-m-d',strtotime($row['mm_need_proc_inv_emailed'])) == date('Y-m-d') || 
						// 	date('Y-m-d',strtotime($row['client_emailed'])) == date('Y-m-d') ||
						// 	$palace_postable == true ||
						// 	$pme_postable == true
						// ){
						// 	$fade_it = true;
						// }		
						
						if( 
							date('Y-m-d',strtotime($row['mm_need_proc_inv_emailed'])) == date('Y-m-d') || 
							date('Y-m-d',strtotime($row['client_emailed'])) == date('Y-m-d') ||
							$api == true
						){
							$fade_it = true;
						}	
						
					
				?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							<td><?php echo ($crm->isDateNotEmpty($row['jdate'])==true)?$crm->formatDate($row['jdate'],'d/m/Y'):''; ?></td>
							<td>
								<?php echo ( isDHAagenciesV2($row['franchise_groups_id'])==true )?'DHA':$row['m_name'];  ?>
								<input type="hidden" class="main_prog" value="<?php echo $row['m_name']; ?>" />
							</td>
							<td>
							<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['a_id']}"); ?>
							<a href="<?php echo $ci_link; ?>"><?php echo $row['agency_name']; ?></a></td>
							<td><?php echo $row['work_order']; ?></td>
							<td><a href="/view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $crm->getInvoiceNumber($row['jid']); ?></a></td>
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							<td>$<?php echo number_format($crm->getInvoiceTotal($row['jid']),2); ?></td>
							
							<!-- combined -->
							<td>
								<?php	
								if( $row['ts_completed'] == 1 ){ ?>
									<div <?php echo ( isDHAagenciesV2($row['franchise_groups_id'])==true )?'style="display:none;"':''; ?>>
										<!-- old
										<a target="blank" href="/view_combined.php?job_id=<?php echo $row['jid']; ?>"><img src="/images/pdf.png" /></a>
										<a target="blank" style="margin-left: 10px;" href="/view_combined.php?job_id=<?php echo $row['jid']; ?>&output_type=D"><img src="/images/download_icon.png" /></a>
										-->
										<?php
											$encode_encrypt_combined_id = rawurlencode($encrypt_decrypt->encrypt($row['jid']));
											$pdf_combine_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_combined/?job_id={$encode_encrypt_combined_id}"));
											$pdf_combine_ci_link_dl = $crm->crm_ci_redirect(rawurlencode("/pdf/view_combined/?job_id={$encode_encrypt_combined_id}&output_type=D"));
										?>
										<a target="blank" href="<?php echo $pdf_combine_ci_link_view; ?>"><img src="/images/pdf.png" /></a>
										<a target="blank" style="margin-left: 10px;" href="<?php echo $pdf_combine_ci_link_dl; ?>"><img src="/images/download_icon.png" /></a>

										<!-- send invoice -->
										<img 
											src="images/email_green.png" 
											data-job_id="<?php echo $row['jid']; ?>" 
											data-pdf_type="invoice_cert" 
											class="btn_email_agency <?php echo ( $fade_it == true )?'fadeIt':''; ?>" 
										/>
										
									</div>
								<?php
								}								
								?>							
							</td>
							
							<!-- invoice -->
							<td class="leftGreyBorder">
								<div <?php echo ( isDHAagenciesV2($row['franchise_groups_id'])==true )?'style="display:none;"':''; ?>>
									<!-- old
									<a target="blank" href="/view_invoice.php?job_id=<?php echo $row['jid']; ?>"><img src="/images/pdf.png" /></a>
									<a target="blank" style="margin-left: 10px;" href="/view_invoice.php?job_id=<?php echo $row['jid']; ?>&output_type=D"><img src="/images/download_icon.png" /></a>
									-->
									<?php
										$encode_encrypt_invoice_id = rawurlencode($encrypt_decrypt->encrypt($row['jid']));
										$pdf_invoice_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_invoice/?job_id={$encode_encrypt_invoice_id}"));
										$pdf_invoice_ci_link_dl = $crm->crm_ci_redirect(rawurlencode("/pdf/view_invoice/?job_id={$encode_encrypt_invoice_id}&output_type=D"));
									?>
									<a target="blank" href="<?php echo $pdf_invoice_ci_link_view; ?>"><img src="/images/pdf.png" /></a>
									<a target="blank" style="margin-left: 10px;" href="<?php echo $pdf_invoice_ci_link_dl; ?>"><img src="/images/download_icon.png" /></a>									
								
									<!-- send invoice -->
									<img 
										src="images/email_green.png" 
										data-job_id="<?php echo $row['jid']; ?>" 
										data-pdf_type="invoice" 
										class="btn_email_agency <?php echo ( $fade_it == true )?'fadeIt':''; ?>" 
									/>
								
								</div>
							</td>
							
							<?php
							if( $row['p_state']=='QLD' ){


								if( $row['prop_upgraded_to_ic_sa'] == 1 ){ ?>
									<td class="leftGreyBorder">Upgraded</td>
									<td>N/A</td>
								<?php
								}else{ ?>
								
								
									<!-- THIS IS HARDCODED; needs to be changed to dynamic sooner -->
									<?php
									$quote_qty = $row['qld_new_leg_alarm_num'];
									$price_240vrf = $crm->get240vRfAgencyAlarm($row['a_id']);
									$quote_price = ( $price_240vrf > 0 )?$price_240vrf:200;
									$quote_total = $quote_price*$quote_qty;
									// QUOTE
									if( $quote_total > 0 ){ ?>
										<td class="leftGreyBorder">	
											<?php echo "$".number_format($quote_total,2); ?>			
										</td>										
										<td>
											<div <?php echo ( isDHAagenciesV2($row['franchise_groups_id'])==true )?'style="display:none;"':''; ?>>
												<!-- old
												<a target="blank" href="view_quote.php?job_id=<?php echo $row['jid']; ?>"><img src="/images/pdf.png" /></a>
												<a target="blank" style="margin-left: 10px;" href="view_quote.php?job_id=<?php echo $row['jid']; ?>&output_type=D"><img src="/images/download_icon.png" /></a>
												-->
												<?php
													$encode_encrypt_quote_id = rawurlencode($encrypt_decrypt->encrypt($row['jid']));
													$pdf_quote_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_quote/?job_id={$encode_encrypt_quote_id}"));
													$pdf_quote_ci_link_dl = $crm->crm_ci_redirect(rawurlencode("/pdf/view_quote/?job_id={$encode_encrypt_quote_id}&output_type=D"));
												?>
												<a target="blank" href="<?php echo $pdf_quote_ci_link_view; ?>"><img src="/images/pdf.png" /></a>
												<a target="blank" style="margin-left: 10px;" href="<?php echo $pdf_quote_ci_link_dl; ?>"><img src="/images/download_icon.png" /></a>
												<img src="images/email_green.png" data-job_id="<?php echo $row['jid']; ?>" data-pdf_type="quote" class="btn_email_agency <?php echo ( date('Y-m-d',strtotime($row['qld_upgrade_quote_emailed'])) == date('Y-m-d') )?'fadeIt':''; ?>" />
											</div>
										</td>
									<?php	
									}else{ ?>
										<td class="leftGreyBorder">N/A</td>
										<td>N/A</td>
									<?php
									}
									?>
															
									
								
								<?php
								}

							?>
								
							
							<?php
							}else{ ?>
							
								<td class="leftGreyBorder">N/A</td>
								<td>N/A</td>
							
							<?php
							}
							?>
							
							
							
							<td><input type="checkbox" class="np_chk" <?php echo ($row['dha_need_processing']==1)?'checked="checked"':''; ?> value="<?php echo $row['jid']; ?>" /></td>
							
							
						
						</tr>
						
				<?php
					$i++;
					}
				}else{ ?>
					<td colspan="12" align="left">Empty</td>
				<?php
				}
				?>
				
		</table>
			
		

		<?php

		// Initiate pagination class
		$jp = new jPagination();
		
		$per_page = $limit;
		$page = ($_GET['page']!="")?$_GET['page']:1;
		$offset = ($_GET['offset']!="")?$_GET['offset']:0;	
		
		echo $jp->display($page,$ptotal,$per_page,$offset,$params);
		
		?>

		
		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){


	// refresh button
	jQuery("#refresh_btn").click(function(){
		jQuery("#btn_search").click();
	});
	
	
	jQuery(".btn_email_agency").click(function(){

		var btn_email_agency = jQuery(this);
		
		//if( btn_email_agency.hasClass("fadeIt") == false ){

			if(confirm('Are you sure you sure you want to continue?')){
			
				var job_id = btn_email_agency.attr("data-job_id");
				var pdf_type = btn_email_agency.attr("data-pdf_type");

				if( job_id > 0 ){

					jQuery("#load-screen").show();
					jQuery.ajax({
						type: "POST",
						url: "ajax_dha_precomp_email_agency_accounts.php",
						data: { 
							job_id: job_id,
							pdf_type: pdf_type
						}
					}).done(function( ret ){	

						jQuery("#load-screen").hide();
						// fade button		
						btn_email_agency.addClass('fadeIt');

					});

				}			
				
			}	

		//}					
				
	});
	
	
	
	
	jQuery(".np_chk").change(function(){
		
		var state = jQuery(this).prop('checked');
		var job_id = jQuery(this).val();
		var main_prog = jQuery(this).parents("tr:first").find('.main_prog').val();
		
		var dha_need_processing = (state==true)?1:0;
		
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_dha_need_processing.php",
			data: { 
				job_id: job_id,
				dha_need_processing: dha_need_processing,
				main_prog: main_prog
			}
		}).done(function( ret ){			
			jQuery("#load-screen").hide();
		});
		
	});
	
});
</script>
</body>
</html>