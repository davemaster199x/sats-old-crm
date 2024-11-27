<?php

$title = "Payments Credits";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$url = 'payments_credits.php';

// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class;

$from = ($_REQUEST['from']!='')?mysql_real_escape_string($_REQUEST['from']):date('d/m/Y');
$from2 = ( $from != '' )?$crm->formatDate($from):'';
$to = ($_REQUEST['to'])?mysql_real_escape_string($_REQUEST['to']):date('d/m/Y');
$to2 = ( $to != '' )?$crm->formatDate($to):'';
$agency_id = mysql_real_escape_string($_REQUEST['agency_id']);
$credit_reason = mysql_real_escape_string($_REQUEST['credit_reason']);
$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$search_flag = mysql_real_escape_string($_REQUEST['search_flag']);

// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'p.`postcode`';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort=".urlencode($sort)."&order_by=".urlencode($order_by)."&from=".urlencode($from)."&to=".urlencode($to)."&agency_id=".urlencode($agency_id)."&phrase=".urlencode($phrase)."&credit_reason=".urlencode($credit_reason)."&search_flag=".urlencode($search_flag);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


// get unpaid jobs and exclude 0 job price
$custom_filter = "
	AND j.`job_price` > 0 
	AND j.`invoice_balance` > 0
	AND j.`status` = 'Completed'
	AND (
		a.`status` = 'Active' OR
		a.`status` = 'Deactivated'
	)
	AND (
		j.`invoice_payments` > 0 OR
		j.`invoice_credits` > 0
	)
";


if( $search_flag == 1 ){

	// per page
	$custom_select = '
		j.`id` AS jid,
		j.`date` AS jdate,
		j.`invoice_amount`,
		j.`invoice_payments`,
		j.`invoice_credits`,
		j.`invoice_balance`,
		
		p.`property_id`,
		p.`address_1` AS p_address_1, 
		p.`address_2` AS p_address_2, 
		p.`address_3` AS p_address_3,
		p.`state` AS p_state,
		p.`postcode` AS p_postcode,
		
		a.`agency_id`,
		a.`agency_name`,

		inv_cred.`credit_paid`,
		inv_cred.`credit_reason`
	';
	$jparams = array(
		'custom_select' => $custom_select,
		'custom_filter' => $custom_filter,
		
		'agency_id' => $agency_id,
		'phrase' => $phrase,
		
		'filterDate' => array(
			'from' => $from2,
			'to' => $to2
		),	
		'sort_list' => array(
			array(
				'order_by' => 'j.`date`',
				'sort' => 'ASC'
			)
		),
		'paginate' => array(
			'offset' => $offset,
			'limit' => $limit
		),
		'join_table' => 'inv_cred',
		'credit_reason' => $credit_reason,
		'echo_query' => 0
	);
	$plist = $crm->getUnpaidJobs($jparams);

	// total rows
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

	// total count
	$custom_select = '
		SUM(j.`invoice_amount`) AS tot_inv_amt,
		SUM(j.`invoice_payments`) AS tot_inv_pay,
		SUM(j.`invoice_credits`) AS tot_inv_cred,
		SUM(j.`invoice_balance`) AS tot_inv_bal
	';
	$jparams = array(
		'custom_select' => $custom_select,
		'custom_filter' => $custom_filter,
		
		'agency_id' => $agency_id,
		'phrase' => $phrase,
		
		'filterDate' => array(
			'from' => $from2,
			'to' => $to2
		),
		'join_table' => 'inv_cred',
		'credit_reason' => $credit_reason,
		'echo_query' => 0
	);
	$tot_count_sql = $crm->getUnpaidJobs($jparams);


	$isAgencyFiltered = ( $agency_id != '' && mysql_num_rows($plist) > 0 )?true:false;
	
}

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
		
		
		//echo date('Y-m-d', strtotime("-30 days"));
		
		
		?>
		
		
		<form method="POST" name='example' id='example'>
			<input type='hidden' name='status' value='<?php echo $status ?>'>

			<table border=1 cellpadding=0 cellspacing=0 width="100%">
				<tr class="tbl-view-prop">
				<td>

				<div class="aviw_drop-h aviw_drop-vp" id="view-jobs">

				 <?php
				 //if( $search_flag ==1 ){ ?>
				 
					<div class='fl-left'>
						<label>From:</label><input type=label name='from' value='<?php echo ( $from != '' )?$from:''; ?>' class='addinput searchstyle datepicker vwjbdtp' style="width:85px!important;">		
					</div>
					
					<div class='fl-left'>
						<label>To:</label><input type=label name='to' value='<?php echo ( $to != '' )?$to:''; ?>' class='addinput searchstyle datepicker vwjbdtp' style="width:85px!important;">		
					</div>
				 
				 <?php
				 //}
				 ?>
					
					
					
					<div class='fl-left'>
						<label>Agency:</label>
						<select name="agency_id">
						<option value=''>--- Select ---</option>
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
							'join_table' => 'inv_cred',
							'credit_reason' => $credit_reason,
							'echo_query' => 0
						);
						$am_sql = $crm->getUnpaidJobs($jparams);
						while( $am = mysql_fetch_array($am_sql) ){ ?>
							<option value="<?php echo $am['agency_id']; ?>" <?php echo ($am['agency_id']==$agency_id)?'selected="selected"':''; ?>><?php echo $am['agency_name']; ?></option>
						<?php	
						}
						
						?>
						</select>
					</div>
					

					<div class='fl-left'>
						<label>Reason:</label>
						<select name="credit_reason">
						<option value=''>All</option>
						<?php				
						$jparams = array(
							'custom_filter' => $custom_filter,
							
							'sort_list' => array(
								array(
									'order_by' => 'a.`agency_name`',
									'sort' => 'ASC'
								)
							),
							
							'distinct_sql' => 'inv_cred.`credit_reason`',
							'join_table' => 'inv_cred',
							'echo_query' => 0
						);
						$cr_sql = $crm->getUnpaidJobs($jparams);
						while( $cr = mysql_fetch_array($cr_sql) ){ ?>
							<option value="<?php echo $cr['credit_reason']; ?>" <?php echo ($cr['credit_reason']==$credit_reason)?'selected="selected"':''; ?>><?php echo $crm->getInvoiceCreditReason($cr['credit_reason']); ?></option>
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
						<input type="hidden" name="search_flag" value="1" />
						<button type='submit' class='submitbtnImg' id="btn_search">
							<img class="inner_icon" src="images/button_icons/search-button.png">
							Search
						</button>
					</div>
					
					
				
					
					
					
					<div style="clear:both;"></div>

					
					
					  
					  
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
					<th>Invoice</th>
					<th>Property</th>
					<th>Charges</th>
					<th>Payments</th>
					<th>Credits</th>
					<th>Balance</th>
					<th>Reason</th>
				</tr>
				<?php	
				$i= 0;
				if(mysql_num_rows($plist)>0){
					
					$invoice_amount_tot = 0;
					$invoice_payments_tot = 0;
					$invoice_credits_tot = 0;
					while($row = mysql_fetch_array($plist)){
						
					// grey alternation color
					$row_color = ($i%2==0)?"":"style='background-color:#eeeeee;'";
					
					// append checkdigit to job id for new invoice number
					$check_digit = getCheckDigit(trim($row['jid']));
					$bpay_ref_code = "{$row['jid']}{$check_digit}";	
					
					// address
					$p_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";
	
					$invoice_amount = number_format($row['invoice_amount'],2);
					$invoice_payments = number_format($row['invoice_payments'],2);
					$invoice_credits = number_format($row['credit_paid'],2);
					
					$balance_tot += $row['invoice_balance'];
					$invoice_balance = number_format($row['invoice_balance'],2);
					
					if( $invoice_payments > 0 ){
						$invoice_payments_str = '$'.$invoice_payments;
					}else{
						$invoice_payments_str = '';
					}
					
					if( $invoice_credits > 0 ){
						$invoice_credits_str = "<span class='colorItRed'>-\${$invoice_credits}</span>";
					}else{
						$invoice_credits_str = '';
					}
	
					
				?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							<td><?php echo ($crm->isDateNotEmpty($row['jdate'])==true)?$crm->formatDate($row['jdate'],'d/m/Y'):''; ?></td>						
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $bpay_ref_code; ?></a></td>			
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							<td>$<?php echo $invoice_amount; ?></td>							
							<td><?php echo $invoice_payments_str; ?></td>
							<td><?php echo $invoice_credits_str; ?></td>
							<td>$<?php echo $invoice_balance; ?></td>
							<td><?php echo $crm->getInvoiceCreditReason($row['credit_reason']); ?></td>
						
						</tr>
						
				<?php									
					$i++;
					} 

					// get total
					$tot_count_row = mysql_fetch_array($tot_count_sql);
				?>			
					<tr class="body_tr jalign_left" <?php echo $row_color; ?>>							
						<td colspan="3"><strong>TOTAL</strong></td>						
						<td><strong><?php echo '$'.number_format($tot_count_row['tot_inv_amt'],2); ?></strong></td>							
						<td><strong><?php echo '$'.number_format($tot_count_row['tot_inv_pay'],2); ?></strong></td>
						<td><strong><?php echo '$'.number_format($tot_count_row['tot_inv_cred'],2); ?></strong></td>
						<td><strong><?php echo '$'.number_format($tot_count_row['tot_inv_bal'],2); ?></strong></td>
						<td>&nbsp;</td>
					</tr>
					
				<?php
				}else{ ?>
					<td colspan="100%" align="left">Select Agency to display data</td>
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
		</div>

		
	</div>
</div>

<br class="clearfloat" />

</body>
</html>