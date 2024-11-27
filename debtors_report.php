<?php

$title = "Debtors Report";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$url = 'debtors_report.php';

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

$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'a.`agency_name`';
$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort=".urlencode($sort)."&order_by=".urlencode($order_by)."&from=".urlencode($from)."&to=".urlencode($to)."&agency_id=".urlencode($agency_id)."&phrase=".urlencode($phrase);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


$custom_select = "
	SUM(j.`invoice_balance`) AS invoice_balance_tot, a.`agency_name`, a.`agency_id`
";

// get unpaid jobs and exclude 0 job price
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
	'custom_select' => $custom_select,
	'custom_filter' => $custom_filter,
	
	'agency_id' => $agency_id,
	'phrase' => $phrase,
	
	'filterDate' => array(
		'from' => $from2,
		'to' => $to2
	),	
	'group_by' => 'a.`agency_id`',
	'sort_list' => array(
		array(
			'order_by' => $order_by,
			'sort' => $sort
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
	'group_by' => 'a.`agency_id`'
);
$ptotal_sql = $crm->getUnpaidJobs($jparams);
$ptotal = mysql_num_rows($ptotal_sql);



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
.jtblfooter{
	background-color: #eeeeee;
	font-weight: bold;
}
</style>




<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title ?></strong></a></li>
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

					
					
					
					
					<div class='fl-left'>
						<label>Agency:</label>
						<select name="agency_id">
						<option value=''>--- Select ---</option>
						<?php				
						$jparams = array(
							'custom_select' => $custom_select,
							'custom_filter' => $custom_filter,
							'group_by' => 'a.`agency_id`',
							
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
						<input type="hidden" name="search_flag" value="1" />
						<button type='submit' class='submitbtnImg' id="btn_search">
							<img class="inner_icon" src="images/button_icons/search-button.png">
							Search
						</button>
					</div>
					
					
				
					<div class='fl-left' style="float:right;">	
						<a href="debtors_report_pdf.php" target="_blank" style="margin-left: 0 !important;">
							<button type='button' class='submitbtnImg blue-btn' id="btn_display_statement">
								<img class="inner_icon" src="images/button_icons/pdf_white.png">
								Debtors Report
							</button>
						</a>
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
					<th>Agency Name</th>					
					<th>0-30 days (Not Overdue)</th>
					<th>31-60 days OVERDUE</th>
					<th>61-90 days OVERDUE</th>
					<th>91+ days OVERDUE</th>
					<th>Total Amount Due</th>
				</tr>
				<?php	
				$i= 0;
				if(mysql_num_rows($plist)>0){
					
					$not_overdue_tot = 0;
					$overdue_31_to_60_tot = 0;
					$overdue_61_to_90_tot = 0;
					$overdue_91_more_tot = 0;
					$invoice_balance_tot = 0;
					$invoice_balance_tot = 0;
					
					while($row = mysql_fetch_array($plist)){
						
					// grey alternation color
					//$row_color = ($i%2==0)?"":"style='background-color:#eeeeee;'";
					
					// append checkdigit to job id for new invoice number
					$check_digit = getCheckDigit(trim($row['jid']));
					$bpay_ref_code = "{$row['jid']}{$check_digit}";	
					
					// address
					$p_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";
	
					$invoice_amount = number_format($row['invoice_amount'],2);
					$invoice_payments = number_format($row['invoice_payments'],2);
					$invoice_credits = number_format($row['invoice_credits'],2);
					
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
							
							<td>
								<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
								<a href="<?php echo $ci_link; ?>">
									<?php echo $row['agency_name']; ?>
								</a>
							</td>						
							<td>
								<?php $not_overdue = $crm->getOverdueTotal($row['agency_id'],'DateDiff <= 30',false); ?>
								$<?php echo number_format($not_overdue,2); ?>
							</td>			
							<td>
								<?php $overdue_31_to_60 = $crm->getOverdueTotal($row['agency_id'],'DateDiff BETWEEN 31 AND 60',false); ?>
								$<?php echo number_format($overdue_31_to_60,2); ?>
							</td>	
							<td>
								<?php $overdue_61_to_90 = $crm->getOverdueTotal($row['agency_id'],'DateDiff BETWEEN 61 AND 90',false); ?>
								$<?php echo number_format($overdue_61_to_90,2); ?>
							</td>
							<td>
								<?php $overdue_91_more = $crm->getOverdueTotal($row['agency_id'],'DateDiff >= 91',false); ?>
								$<?php echo number_format($overdue_91_more,2); ?>
							</td>								
							<td>
								<?php $invoice_balance = $row['invoice_balance_tot']; ?>
								$<?php echo number_format($invoice_balance,2); ?>
							</td>				
						
						</tr>
						
				<?php
					$not_overdue_tot += $not_overdue;
					$overdue_31_to_60_tot += $overdue_31_to_60;
					$overdue_61_to_90_tot += $overdue_61_to_90;
					$overdue_91_more_tot += $overdue_91_more;
					$invoice_balance_tot += $invoice_balance;
				
					$i++;
					}
				?>
				
					<tr class="jalign_left jtblfooter">
							
						<td>TOTAL</td>						
						<td>$<?php echo number_format($not_overdue_tot,2); ?></td>			
						<td>$<?php echo number_format($overdue_31_to_60_tot,2); ?></td>	
						<td>$<?php echo number_format($overdue_61_to_90_tot,2); ?></td>
						<td>$<?php echo number_format($overdue_91_more_tot,2); ?></td>
						<td>$<?php echo number_format($invoice_balance_tot,2); ?></td>				
					
					</tr>
					
					<?php
					// get percentage
					$not_overdue_perc = ($not_overdue_tot / $invoice_balance_tot) * 100;
					$overdue_31_to_60_perc = ($overdue_31_to_60_tot / $invoice_balance_tot) * 100;
					$overdue_61_to_90_perc = ($overdue_61_to_90_tot / $invoice_balance_tot) * 100;
					$overdue_91_more_perc = ($overdue_91_more_tot / $invoice_balance_tot) * 100;
					?>
					<tr class="jalign_left jtblfooter">
							
						<td>Ageing Percentage</td>						
						<td><?php echo number_format($not_overdue_perc); ?>%</td>			
						<td><?php echo number_format($overdue_31_to_60_perc); ?>%</td>
						<td><?php echo number_format($overdue_61_to_90_perc); ?>%</td>
						<td><?php echo number_format($overdue_91_more_perc); ?>%</td>
						<td>100%</td>	
						
					</tr>
				
				<?php
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
		</div>

		
	</div>
</div>

<br class="clearfloat" />

</body>
</html>