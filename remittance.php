<?php

$title = "Remittance";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$url = 'remittance.php';

// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class;

$from = mysql_real_escape_string($_REQUEST['from']);
$from2 = ( $from != '' )?$crm->formatDate($from):'';
$to = mysql_real_escape_string($_REQUEST['to']);
$to2 = ( $to != '' )?$crm->formatDate($to):'';
$agency_id = mysql_real_escape_string($_REQUEST['agency_id']);
$phrase = mysql_real_escape_string($_REQUEST['phrase']);


// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'p.`postcode`';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort=".urlencode($sort)."&order_by=".urlencode($order_by)."&from=".urlencode($from)."&to=".urlencode($to)."&agency_id=".urlencode($agency_id);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$custom_filter = "
	AND j.`job_price` > 0 
	AND j.`invoice_balance` > 0
	AND j.`status` = 'Completed'
	AND (
		a.`status` = 'Active' OR
		a.`status` = 'Deactivated'
	)
";

$jparams = array(
	'custom_filter' => $custom_filter,
	
	'agency_id' => $agency_id,
	'phrase' => $phrase,
	
	'filterDate' => array(
		'from' => $from2,
		'to' => $to2
	),	
	'sort_list' => array(
		array(
			'order_by' => 'j.`id`',
			'sort' => 'ASC'
		)
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'echo_query' => 0
);
$plist = $crm->getUnpaidJobs($jparams);

$jparams = array(
	'custom_filter' => $custom_filter,
	
	'agency_id' => $agency_id,
	'phrase' => $phrase,
	
	'filterDate' => array(
		'from' => $from2,
		'to' => $to2
	),
	'return_count' => 1
);
$ptotal = $crm->getUnpaidJobs($jparams);


// get payment types
$pt_sql = $crm->getPaymentTypes();
$pt_arr = [];
while( $pt = mysql_fetch_array($pt_sql) ){ 
	$pt_arr[] = array(
		'pt_id' => $pt['payment_type_id'],
		'pt_name' => $pt['pt_name']
	);	
}



$isAgencyFiltered = ( $agency_id != '' && mysql_num_rows($plist) > 0 )?true:false;

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
.payment_details_table td {
    padding: 5px;
	border: none;
	text-align: left;
}
.payment_details_table tr {
	border: none;
}
.save_div{
	float:right; 
	margin-bottom: 20px; 
	position: relative; 
	bottom: 85px;
	display:none;
}
.jcolorItRed{
	color: red;
}
.jcolorItGreen{
	color: green;
}
</style>




<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title ?>" href="<?php echo $url; ?>"><strong><?php echo $title ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['print_clear']==1){
			echo '<div class="success">Printed jobs has been cleared</div>';
		}

		if($_GET['success']==1){
			echo '<div class="success">Payment Successful</div>';
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
						<label>From:</label><input type=label name='from' value='<?php echo ( $from != '' )?$from:''; ?>' class='addinput searchstyle datepicker vwjbdtp' style="width:85px!important;">		
					</div>
					
					<div class='fl-left'>
						<label>To:</label><input type=label name='to' value='<?php echo ( $to != '' )?$to:''; ?>' class='addinput searchstyle datepicker vwjbdtp' style="width:85px!important;">		
					</div>
					
					<?php					
					$jparams = array(
						'custom_filter' => $custom_filter,
						
						'sort_list' => array(
							array(
								'order_by' => 'a.`agency_name`',
								'sort' => 'ASC'
							)
						),
						
						'distinct_sql' => 'p.`agency_id`, a.`agency_name`',	
						'echo_query' => 0
					);
					$am_sql = $crm->getUnpaidJobs($jparams);					
					?>
					<div class='fl-left'>
						<label>Agency:</label>
						<select name="agency_id">
						<option value=''>--- Select ---</option>
						<?php										
						while( $am = mysql_fetch_array($am_sql) ){ ?>
							<option value="<?php echo $am['agency_id']; ?>" <?php echo ($am['agency_id']==$agency_id)?'selected="selected"':''; ?>><?php echo $am['agency_name']; ?></option>
						<?php	
						}
						
						?>
						</select>
					</div>
					
			
					
					<div class="fl-left">
						<label>Phrase:</label>
						<input name="phrase" value="<?php echo $phrase; ?>" class="addinput searchstyle vwjbdtp" style="width: 100px !important;" type="label">
					</div>
					
					
					
					<div class='fl-left' style="float:left;">				
						<button type='submit' class='submitbtnImg' id="btn_search">
							<img class="inner_icon" src="images/button_icons/search-button.png">
							Search
						</button>
					</div>
					
					
					<div style="clear:both;"></div>

					<?php
					if( $isAgencyFiltered == true ){ ?>
					
					
						<div class='fl-left' id="bulk_payment_details_div" style="float:left; display:none">
						
							<h2 class="heading">Bulk Payment Details</h2>
							<table class="table payment_details_table" style="border:none;">						
								
								<tr>
									<td class="col1" style="font-size: 13px;">Payment Date:<br /><input style="width: 80px;" type="text" name="payment_date" id="payment_date" class="addinput vw-jb-inpt datepicker payment_date" value="<?php echo date('d/m/Y'); ?>" /></td>
							
									<td class="col1" style="font-size: 13px;">Amount Paid:<br />
										<input style="width: 80px;" type="text" name="amount_paid" id="amount_paid" class="addinput vw-jb-inpt amount_paid" value="<?php echo $amount_paid ?>">
										<input name="orig_amount_paid" type="hidden" id="orig_amount_paid" class="addinput vw-jb-inpt orig_amount_paid" value="<?php echo $amount_paid ?>">
									</td>
						
									<td class="col1" style="font-size: 13px;">Type of Payment:<br />										
										<select style="width: 105px;" name="type_of_payment" id="type_of_payment" class="type_of_payment" />
											<option value="">--- Select ---</option>
											<?php				
											foreach( $pt_arr as $pt ){
											?>
												<option value="<?php echo $pt['pt_id']; ?>" <?php echo ( $pt['pt_id'] == $type_of_payment )?'selected="selected"':''; ?>><?php echo $pt['pt_name'] ?></option>
											<?php
											}										
											?>
										</select>
										<?php 	//print_r($pt_arr); ?>
									</td>
									
									<td class="col1" style="font-size: 13px;"><br />
										<button type='button' class='submitbtnImg' id="btn_clear" style="margin:0;">
											<img class="inner_icon" src="images/button_icons/cancel-button.png">
											CLEAR
										</button>
									</td>
									
									<td>
										<div style="margin: 15px 0 0 60px;">
											$<span id="bulk_amount">0</span>/
											<span class="amount_alloc_main_div jcolorItRed">
												$<span id="amount_alloc">0</span>
											</span>
										</div>
									</td>
								</tr>

							</table>
							
						</div>
						
						<div style="clear:both;"></div>
						
						<div class='fl-left' style="float:left;">
							<button type='button' class='submitbtnImg blue-btn' id="btn_bulk_payment" style="margin: 15px 5px;">
								<img class="inner_icon" src="images/button_icons/add-button.png">
								<span class="inner_icon_span">BULK Payment</span>
							</button>
						</div>
					
					
					<?php
					}
					?>
					
					  
					  
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

	
			<table id="invoice_table" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
				
				<tr class="toprow jalign_left">
					<th>Invoice Date</th>
					<th>Invoice #</th>
					<th>Property Address</th>
					<th>Agency</th>
					<th>Amount</th>
					<th>Balance</th>
					<th>Amount Paid</th>
					<th>Payment Date</th>
					<th>Type of Payment</th>
					
					<!--
					<th>PAID in FULL</th>
					<th>Assign total</th>
					-->
					<th></th>
				</tr>
				<?php	
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
					// grey alternation color
					$row_color = ($i%2==0)?"":"style='background-color:#eeeeee;'";
					
					// append checkdigit to job id for new invoice number
					$check_digit = getCheckDigit(trim($row['jid']));
					$bpay_ref_code = "{$row['jid']}{$check_digit}";	

					
				?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							<td><?php echo ($crm->isDateNotEmpty($row['jdate'])==true)?$crm->formatDate($row['jdate'],'d/m/Y'):''; ?></td>						
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $bpay_ref_code; ?></a></td>			
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							<td>
							<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['a_id']}"); ?>
							<a href="<?php echo $ci_link ?>"><?php echo $row['agency_name']; ?></a></td>							
							<td>
								<strong>
									$<?php
									echo $amount = number_format($row['invoice_amount'],2)
									?>
								</strong>
								<input type="hidden" class="amount" value="<?php echo $amount; ?>" />
							</td>	
							<td>
								<em style="color:red;">
									$<?php
									echo $balance = number_format($row['invoice_balance'],2)
									?>
								</em>
								<input type="hidden" class="balance" value="<?php echo $balance; ?>" />
							</td>
							<td style="border-left: 1px solid #cccccc"><input type="text" class="addinput payment_fields amount_paid" style="width: 60px" /></td>
							<td><input type="text" class="addinput datepicker payment_fields payment_date" style="width: 80px" /></td>
							<td>
								<select style="width: 105px;" class="payment_fields type_of_payment" />
									<option value="">--- Select ---</option>
									<?php				
									foreach( $pt_arr as $pt ){
									?>
										<option value="<?php echo $pt['pt_id']; ?>" <?php echo ( $pt['pt_id'] == $type_of_payment )?'selected="selected"':''; ?>><?php echo $pt['pt_name'] ?></option>
									<?php
									}										
									?>
								</select>
							</td>						
							<!---
							<td><input type="checkbox" /></td>
							<td><input type="checkbox" /></td>
							--->
							<td><input type="checkbox" class="job_chk" value="<?php echo $row['jid']; ?>" /></td>
					
						
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
		
		
		<div class="save_div">	
		
			<button type='submit' class='submitbtnImg blue-btn' id="btn_save">
				<img class="inner_icon" src="images/button_icons/save-button.png">
				SAVE
			</button>
			
			<button type="button" class="submitbtnImg" id="btn_clear_list" style="margin:0;">
				<img class="inner_icon" src="images/button_icons/cancel-button.png">
				CLEAR
			</button>
			
		</div>
		
		
		

		
	</div>
</div>

<br class="clearfloat" />
<script>





jQuery(document).ready(function(){
	
	
	jQuery(".payment_fields").change(function(){
		
		console.log('check the 3 fields');
		var amount_paid = jQuery(this).parents("tr:first").find(".amount_paid").val();
		var payment_date = jQuery(this).parents("tr:first").find(".payment_date").val();
		var type_of_payment = jQuery(this).parents("tr:first").find(".type_of_payment").val();
		
		console.log("amount_paid: "+amount_paid);
		console.log("payment_date: "+payment_date);
		console.log("type_of_payment: "+type_of_payment);
		
		if( amount_paid !='' && payment_date !='' && type_of_payment !='' ){
			jQuery(this).parents("tr:first").addClass('jredHighlightRow');
			jQuery(this).parents("tr:first").find(".job_chk").prop("checked",true);
		}else{
			jQuery(this).parents("tr:first").removeClass('jredHighlightRow');
			jQuery(this).parents("tr:first").find(".job_chk").prop("checked",false);
		}
		
		var num_sel = jQuery(".job_chk:checked").length;
		if( parseInt(num_sel) > 0){
			jQuery(".save_div").show();
		}else{
			jQuery(".save_div").hide();
		}
		
	});
	
	
	// clear script
	jQuery("#btn_clear").click(function(){
		
		jQuery("#payment_date").val("");
		jQuery("#amount_paid").val("");
		jQuery("#type_of_payment").val("");
		jQuery("#bulk_amount").html("0");
		
	});
	
	
	// clear script
	jQuery("#btn_clear_list").click(function(){
		
		list_tbl = jQuery("#invoice_table");
		list_tbl.find(".body_tr").removeClass('jredHighlightRow');	
		list_tbl.find(".amount_paid").val("");
		list_tbl.find(".payment_date").val("");
		list_tbl.find(".type_of_payment").val("");
		list_tbl.find(".job_chk").prop("checked",false);	
		
	});
	
	
	// save payment details
	jQuery("#btn_save").click(function(){
		
		var pd_row_count = jQuery(".job_chk:checked").length;
		var i = 1;
		var pd_empty = 0;
		var ap_empty = 0;
		var top_empty = 0;
		var error = '';
		
		// validation, rushing change to json if possible
		jQuery(".job_chk:checked").each(function(){
			
			var this_row = jQuery(this).parents("tr:first");
			var ip_id = this_row.find(".ip_id").val();
			var payment_date = this_row.find(".payment_date").val();
			var amount_paid = this_row.find(".amount_paid").val();
			var orig_amount_paid = this_row.find(".orig_amount_paid").val();
			var type_of_payment = this_row.find(".type_of_payment").val();	

			if( payment_date == '' ){
				pd_empty = 1;
				this_row.find(".payment_date").addClass('redBorder');
			}
			
			if( amount_paid == '' ){
				ap_empty = 1;
				this_row.find(".amount_paid").addClass('redBorder');
			}
			
			if( type_of_payment == '' ){
				top_empty = 1;
				this_row.find(".type_of_payment").addClass('redBorder');
			}	
	
			
		});
		
		
		
		if( pd_empty == 1 ){
			error += "Payment Date is required\n";
		}
		
		if( ap_empty == 1 ){
			error += "Amount Paid is required\n";
		}
		
		if( top_empty == 1 ){
			error += "Type of Payment is required\n";
		}
		
		
		
		if( error != '' ){ // error
			alert(error);
		}else{
		
			// success
			jQuery(".job_chk:checked").each(function(){
			
				var this_row = jQuery(this).parents("tr:first");
				var ip_id = this_row.find(".ip_id").val();
				var payment_date = this_row.find(".payment_date").val();
				var amount_paid = this_row.find(".amount_paid").val();
				var orig_amount_paid = this_row.find(".orig_amount_paid").val();
				var type_of_payment = this_row.find(".type_of_payment").val();
				var edited = this_row.find(".edited").val();
				var job_id = jQuery(this).val();

				var payments_arr = []; // only single payment per job

				var json_data = { 
					'payment_date': payment_date, 
					'amount_paid': amount_paid,
					'type_of_payment': type_of_payment,
					'ip_id': ip_id,
					'orig_amount_paid': orig_amount_paid,
					'edited': edited
				}
				var json_str = JSON.stringify(json_data);
				
				payments_arr.push(json_str);

				jQuery.ajax({
					type: "POST",
					url: "ajax_submit_invoice_payment.php",
					data: { 	
						job_id: job_id,
						payments_arr: payments_arr
					}
				}).done(function( ret ){	
					window.location="<?php echo $url; ?>?success=1<?php echo $params ?>";		
				});	
		
				
			});
		
		}
		
		
		
		
		
	});
	
	
	jQuery("#btn_bulk_payment").click(function(){	

		var btn_txt = jQuery(this).find(".inner_icon_span").html();
		var default_btn_txt = 'BULK Payment';
		var add_icon_src ='images/button_icons/add-button.png';
		var cancel_icon_src = 'images/button_icons/cancel-button.png';
		
		if( btn_txt == default_btn_txt ){
			jQuery("#bulk_payment_details_div").show();	
			jQuery(this).find(".inner_icon_span").html("Cancel");
			jQuery(this).find(".inner_icon").attr("src",cancel_icon_src);
		}else{
			jQuery("#bulk_payment_details_div").hide();	
			jQuery(this).find(".inner_icon_span").html(default_btn_txt);
			jQuery(this).find(".inner_icon").attr("src",add_icon_src);
		}
	
	});
	
	
	jQuery("#amount_paid").change(function(){
		
		var amount_paid = Number(jQuery(this).val());
		jQuery("#bulk_amount").html(amount_paid.toFixed(2));
		
	});
	
	
	
	jQuery(".job_chk").change(function(){
		
		var obj = jQuery(this);
		var this_row = obj.parents("tr:first");
		var job_chk_state = obj.prop("checked");
		var payment_date = jQuery("#payment_date").val();
		var amount_paid = jQuery("#amount_paid").val();
		var type_of_payment = jQuery("#type_of_payment").val();
		var amount = this_row.find(".amount").val();
		var balance = this_row.find(".balance").val();
		var bulk_amount = Number(jQuery("#bulk_amount").html());
		var payment_details_div = jQuery("#bulk_payment_details_div:visible").length;
		var isBulkPay = false;
		
		isBulkPay = ( payment_details_div > 0 )?true:false;
		
		//console.log("job_chk_state: "+job_chk_state)
		
		if( job_chk_state == true ){
			
			this_row.addClass('jredHighlightRow');	
			this_row.find(".amount_paid").val(balance);
			
			if( isBulkPay == true ){
				
				this_row.find(".payment_date").val(payment_date);				
				this_row.find(".type_of_payment").val(type_of_payment);

				
				// add
				var amount_paid = Number(balance);
				var amount_alloc = Number(jQuery("#amount_alloc").html());	
				var tot_amount_alloc = amount_alloc+amount_paid;
				
				jQuery("#amount_alloc").html(tot_amount_alloc.toFixed(2));
				
			
				if(  bulk_amount == tot_amount_alloc  ){
					jQuery(".amount_alloc_main_div").removeClass('jcolorItRed');
					jQuery(".amount_alloc_main_div").addClass('jcolorItGreen');
				}
				
			}
			
		}else{
			
			this_row.removeClass('jredHighlightRow');
			this_row.find(".amount_paid").val("");
			
			if( isBulkPay == true ){
				
				this_row.find(".payment_date").val("");				
				this_row.find(".type_of_payment").val("");
				
				
				// minus
				var amount_paid = Number(balance);
				var amount_alloc = Number(jQuery("#amount_alloc").html());	
				var tot_amount_alloc = amount_alloc-amount_paid;	
				jQuery("#amount_alloc").html(tot_amount_alloc.toFixed(2));
				
				if(  tot_amount_alloc < bulk_amount  ){
					jQuery(".amount_alloc_main_div").addClass('jcolorItRed');
					jQuery(".amount_alloc_main_div").removeClass('jcolorItGreen');
				}
				
			}

		}
		
		var num_sel = jQuery(".job_chk:checked").length;
		if( parseInt(num_sel) > 0){
			jQuery(".save_div").show();
		}else{
			jQuery(".save_div").hide();
		}
		
	});
	
	
});
</script>
</body>
</html>