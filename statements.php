<?php

$title = "Statements";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$url = 'statements.php';

// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class;

$from = mysql_real_escape_string($_REQUEST['from']);
$from2 = ( $from != '' )?$crm->formatDate($from):'';
$to = mysql_real_escape_string($_REQUEST['to']);
$to2 = ( $to != '' )?$crm->formatDate($to):'';
$agency_id = mysql_real_escape_string($_REQUEST['agency_id']);
$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$search_flag = mysql_real_escape_string($_REQUEST['search_flag']);

// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'p.`postcode`';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort=".urlencode($sort)."&order_by=".urlencode($order_by)."&from=".urlencode($from)."&to=".urlencode($to)."&agency_id=".urlencode($agency_id)."&phrase=".urlencode($phrase);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

// static financial year 
$financial_year = '2019-07-01'; 
// unpaid marker
$unpaid_marker_str = '
OR(
	j.`unpaid` = 1 AND
	j.`invoice_balance` > 0
)
';


// get unpaid jobs and exclude 0 job price
$custom_filter = "
	AND j.`job_price` > 0 
	AND j.`invoice_balance` > 0
	AND j.`status` = 'Completed'
	AND (
		a.`status` = 'Active' OR
		a.`status` = 'Deactivated'
	)

	AND j.`date` >= '{$financial_year}'
	{$unpaid_marker_str}
";


if( $search_flag == 1 ){

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
				'order_by' => 'j.`date`',
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
				 if( $search_flag ==1 ){ ?>
				 
					<div class='fl-left'>
						<label>From:</label><input type=label name='from' value='<?php echo ( $from != '' )?$from:''; ?>' class='addinput searchstyle datepicker vwjbdtp' style="width:85px!important;">		
					</div>
					
					<div class='fl-left'>
						<label>To:</label><input type=label name='to' value='<?php echo ( $to != '' )?$to:''; ?>' class='addinput searchstyle datepicker vwjbdtp' style="width:85px!important;">		
					</div>
				 
				 <?php
				 }
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
							
							'distinct_sql' => 'p.`agency_id`, a.`agency_name`, a.`status`',
							'echo_query' => 1
						);
						$am_sql = $crm->getUnpaidJobs($jparams);
						while( $am = mysql_fetch_array($am_sql) ){ ?>
							<option value="<?php echo $am['agency_id']; ?>" <?php echo ($am['agency_id']==$agency_id)?'selected="selected"':''; ?>><?php echo $am['agency_name']; ?> <?php echo ( $am['status'] == 'Deactivated' )?'(INACTIVE)':''; ?></option>
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
					
					
					<?php					
					if( $search_flag ==1 ){ ?>
						<div class='fl-left' style="float:right;">	
							<a href="statement_list_pdf.php?<?php echo $params; ?>" target="_blank" style="margin-left: 0 !important;">
								<button type='button' class='submitbtnImg blue-btn' id="btn_display_statement">
									<img class="inner_icon" src="images/button_icons/pdf_white.png">
									Display Statement
								</button>
							</a>
						</div>
					<?php
					}					
					?>
					
					
					
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
					<th>Refunds</th>
					<th>Credits</th>
					<th>Balance</th>
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
					
					// address
					$p_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";
	
					$invoice_amount = number_format($row['invoice_amount'],2);
					$invoice_payments = number_format($row['invoice_payments'],2);
					$invoice_refunds = number_format($row['invoice_refunds'],2);
					$invoice_credits = number_format($row['invoice_credits'],2);
					
					$balance_tot += $row['invoice_balance'];
					$invoice_balance = number_format($row['invoice_balance'],2);
					
					// payments
					if( $invoice_payments > 0 ){
						$invoice_payments_str = '$'.$invoice_payments;
					}else{
						$invoice_payments_str = '';
					}
						
					// refunds
					if( $invoice_refunds > 0 ){
						$invoice_refunds_str = "<span class='colorItRed'>-\${$invoice_refunds}</span>";
					}else{
						$invoice_refunds_str = '';
					}
					
					// credits
					if( $invoice_credits > 0 ){
						$invoice_credits_str = "<span class='colorItRed'>-\${$invoice_credits}</span>";
					}else{
						$invoice_credits_str = '';
					}
					
					
					// Age
					$date1=date_create(date('Y-m-d',strtotime($row['jdate'])));
					$date2=date_create(date('Y-m-d'));
					$diff=date_diff($date1,$date2);
					$age = $diff->format("%r%a");
					$age_val = (((int)$age)!=0)?$age:0;
					
					
					if( $age_val <= 30 ){ // not overdue, within 30 days
						$not_overdue += $row['invoice_balance'];
					}else if( $age_val >= 31 && $age_val <= 60 ){ // overdue, within 31 - 60 days
						$overdue_31_to_60 += $row['invoice_balance'];
					}else if( $age_val >= 61 && $age_val <= 90 ){ // overdue, within 61 - 90 days
						$overdue_61_to_90 += $row['invoice_balance'];
					}else if( $age_val >= 91 ){ // overdue over 91 days or more
						$overdue_91_more += $row['invoice_balance'];
					}
					

					
				?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							<td><?php echo ($crm->isDateNotEmpty($row['jdate'])==true)?$crm->formatDate($row['jdate'],'d/m/Y'):''; ?></td>						
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $bpay_ref_code; ?></a></td>			
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							<td>$<?php echo $invoice_amount; ?></td>							
							<td><?php echo $invoice_payments_str; ?></td>
							<td><?php echo $invoice_refunds_str; ?></td>
							<td><?php echo $invoice_credits_str; ?></td>
							<td>$<?php echo $invoice_balance; ?></td>
					
						
						</tr>
						
				<?php
					$i++;
					}
				}else{ ?>
					<td colspan="12" align="left">Select Agency to display data</td>
				<?php
				}
				?>
					
			</table>
			
			
			
			<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" >
				<tr class="toprow jalign_left">
					<th>0-30 days (Not Overdue)</th>
					<th>31-60 days OVERDUE</th>
					<th>61-90 days OVERDUE</th>
					<th>91+ days OVERDUE</th>
					<th>Total Amount Due</th>
				<tr>
				<tr class="toprow jalign_left">
					<td><?php echo '$'.number_format($not_overdue,2); ?></td>
					<td><?php echo '$'.number_format($overdue_31_to_60,2); ?></td>
					<td><?php echo '$'.number_format($overdue_61_to_90,2); ?></td>
					<td><?php echo '$'.number_format($overdue_91_more,2); ?></td>
					<td><?php echo '$'.number_format($balance_tot,2); ?></td>
				</tr>
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