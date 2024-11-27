<?php
$title = "Adjustment Requests";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$current_page = $_SERVER['PHP_SELF'];

$user_type = $_SESSION['USER_DETAILS']['ClassID'];
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

//$crm->displaySession();

$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$loggedin_staff_name = "{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}";
$country_id = $_SESSION['country_default'];

$requested_by = mysql_real_escape_string($_REQUEST['requested_by']);

$from_date = ($_REQUEST['from_date']!='')?mysql_real_escape_string($_REQUEST['from_date']):'';
if($from_date!=''){
	$from_date2 = $crm->formatDate($from_date);
}
$to_date = ($_REQUEST['to_date']!='')?mysql_real_escape_string($_REQUEST['to_date']):'';
if($to_date!=''){
	$to_date2 = $crm->formatDate($to_date);
}

$result = mysql_real_escape_string($_REQUEST['result']);
$agency_id = mysql_real_escape_string($_REQUEST['agency_id']);


// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort={$sort}&order_by={$order_by}&from_date={$from_date}&to_date={$to_date}&requested_by={$requested_by}&result={$result}&agency_id={$agency_id}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;



// list
$list_params = array(
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'sort_list' => array(
		array(
			'order_by' => 'cr.`date_of_request`',
			'sort' => 'DESC'
		)
	),
	'filterDate' => array(
		'from' => $from_date2,
		'to' => $to_date2
	),
	'requested_by' => $requested_by,
	'country_id' => $country_id,
	'result' => $result,
	'echo_query' => 0,
	'agency_id' => $agency_id
);
$cr_sql = $crm->getCreditRequestData($list_params);


// pagination 
$pag_num_params = array(
	'filterDate' => array(
		'from' => $from_date2,
		'to' => $to_date2
	),
	'requested_by' => $requested_by,
	'country_id' => $country_id,
	'result' => $result,
	'agency_id' => $agency_id
);
$ptotal = mysql_num_rows($crm->getCreditRequestData($pag_num_params));


function this_getGrandTotalAmount($cr_sql,$country_id){
	
	$grand_total = 0;
	$cred_tot = 0;
	while($cr = mysql_fetch_array($cr_sql)){
		// get amount
		$grand_total += getJobAmountGrandTotal($cr['job_id'],$country_id);
		$cred_tot += $cr['amount_credited'];
	}
	
	$return_arr = [];
	
	return $return_arr = array(
		'amount_tot' => $grand_total,
		'credited_tot' => $cred_tot
	);
	
}


?>
<style>
.addproperty input, .addproperty select {
    width: 350px;
}
.addproperty label {
   width: 230px;
}
.tbl_chkbox td{
	text-align: left;
}

.tbl_chkbox tr{
	border: none !important;
}

.tbl_chkbox tr.tr_last_child{
	border-bottom: medium none !important;
}
.chkbox {
    width: auto !important;
}
.chk_div{
	float: left;
}
.chk_div input, .chk_div span{
	float: left;
}
.chk_div input{
	margin-top: 3px;
}
.chk_div span{
    margin: 0 5px 0 5px;
}
textarea.description{
	height: 79px;
    margin: 0;
    width: 340px;
}
input#amount{
	display: inline;
    margin-left: 4px;
    width: 338px;
}

table#expense_tbl td, table#expense_tbl th{
	text-align: left;
}

.approvedHLstatus {
    color: green;
    font-weight: bold;
}
.pendingHLstatus {
    color: red;
    font-style: italic;
}
.declinedHLstatus {
    color: red;
	font-weight: bold;
}
.more_infoHLstatus {
    color: #f37b53;
	font-weight: bold;
}

</style>


    
    <div id="mainContent">
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title; ?></strong></a></li>				
		</ul>
	</div>
      
    
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Submission Successful</div>
	<?php
	}else if($_GET['del_success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Delete Successful</div>
	<?php	
	}else if($_GET['update_success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Update Successful</div>
	<?php	
	}
	?>
	
	
	<div class="aviw_drop-h" style="border: 1px solid #ccc;">
	
		<form id="form_search" method="post" action="<?php echo $current_page; ?>">			
			<div class="fl-left">
				<label style="margin-right: 9px;">From:</label>
				<input type="text" name="from_date" id="from_date" style="width: 100px" class="addinput datepicker" value="<?php echo ($_REQUEST['from_date']!='')?$from_date:''; ?>" />
			</div>
			
			<div class="fl-left">
				<label style="margin-right: 9px;">To:</label>
				<input type="text" name="to_date" id="to_date" style="width: 100px" class="addinput datepicker" value="<?php echo($_REQUEST['to_date']!='')?$to_date:''; ?>" />
			</div>
			
			<?php	
			$list_params = array(
				'sort_list' => array(
					array(
						'order_by' => 'rb.`FirstName`',
						'sort' => 'ASC'
					),
					array(
						'order_by' => 'rb.`LastName`',
						'sort' => 'ASC'
					)
				),
				'distinct' => 'cr.`requested_by`',
				'country_id' => $country_id,
				'result' => $result
			);
			$rb_sql = $crm->getCreditRequestData($list_params);		
			?>
			<div class="fl-left">
				<label style="margin-right: 9px;">Requested By:</label>
				<select name="requested_by">
					<option value="">--- Select ---</option>
					<?php
					while( $rb = mysql_fetch_array($rb_sql) ){ ?>
						<option value="<?php echo $rb['requested_by'] ?>" <?php echo ($rb['requested_by']==$requested_by)?'selected="selected"':''; ?>><?php echo $crm->formatStaffName($rb['FirstName'],$rb['LastName']); ?></option>
					<?php	
					}
					?>
				</select>
			</div>
			
			
			<div class="fl-left">
				<label style="margin-right: 9px;">Result</label>
				<select name="result">
					<option value="">Pending</option>
					<option value="1" <?php echo ( $result==1 )?'selected="selected"':''; ?>>Accepted</option>	
					<option value="0" <?php echo ( is_numeric($result) && $result==0 )?'selected="selected"':''; ?>>Declined</option>
					<option value="2" <?php echo ( is_numeric($result) && $result==2 )?'selected="selected"':''; ?>>More info needed</option>
					<option value="ALL" <?php echo ( $result=='ALL' )?'selected="selected"':''; ?>>ALL</option>
				</select>
			</div>
			
			<?php	
			// list
			$list_params = array(
				'sort_list' => array(
					array(
						'order_by' => 'a.`agency_name`',
						'sort' => 'ASC'
					)
				),
				'filterDate' => array(
					'from' => $from_date2,
					'to' => $to_date2
				),
				'country_id' => $country_id,
				'custom_select' => ' DISTINCT a.`agency_id`, a.`agency_name` ',
				'echo_query' => 0,
				'result' => $result
			
			);
			$agen_sql = $crm->getCreditRequestData($list_params);	
			?>
			<div class="fl-left">
				<label style="margin-right: 9px;">Agency:</label>
				<select name="agency_id" style="width: 200px;">
					<option value="">--- Select ---</option>
					<?php
					while( $agen = mysql_fetch_array($agen_sql) ){ ?>
						<option value="<?php echo $agen['agency_id']; ?>" <?php echo ( $agen['agency_id'] == $agency_id )?'selected="selected"':''; ?>><?php echo $agen['agency_name']; ?></option>
					<?php	
					}
					?>
				</select>
			</div>
			
			<div class="fl-left" style="float:left; margin-left: 10px;">
				<button type="submit" class="submitbtnImg">
					<img class="inner_icon" src="images/search.png">
					Search 
				</button>
			</div>	
		</form>
		
		<!--
		<div style="float: right;">
			<a href="/export_expense_summary.php?from_date=<?php echo $from_date ?>&to_date=<?php echo $to_date ?>">
				<button type="button" name="btn_submit" class="submitbtnImg">Export</button>
			</a>
		</div>
		-->
		
	</div>
	

	<table id="expense_tbl" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;">
			<tr class="toprow jalign_left">				
				<th>Date of Request</th>
				<th>Invoice #</th>
				<th>Amount</th>
				<th>Agency</th>
				<th>Requested By</th>
				<th>Reason</th>
				<th>Result</th>
				<th>Comments</th>
				<th>Date Processed</th>			
				<th>Credited</th>
				<th>Who</th>
			</tr>
			<?php				
			if( mysql_num_rows($cr_sql)>0 ){
				$i = 0;
				while($cr = mysql_fetch_array($cr_sql)){ 

				// get amount
				$grand_total = getJobAmountGrandTotal($cr['job_id'],$country_id);

				
				// get invoice number
				if(isset($cr['tmh_id'])){
					$invoice_num = $cr['tmh_id'];
				}else{
					$invoice_num = $cr['id'];
				}

				?>
					<tr class="body_tr jalign_left"  <?php echo ($i%2==0)?'':'style="background-color:#eeeeee"'; ?>>
						<td>
							<input type="hidden" name="cr_id[]" class="cr_id" value="<?php echo $cr['credit_request_id']; ?>" />
							<a href="credit_request_details.php?id=<?php echo $cr['credit_request_id']; ?>">
								<?php echo  date('d/m/Y',strtotime($cr['date_of_request'])); ?>
							</a>
						</td>		
						<td>
							<a href="view_job_details.php?id=<?php echo $cr['job_id']; ?>">
								<?php echo $invoice_num; ?>
							</a>
						</td>
						<td><?php echo '$'.number_format($grand_total,2); ?></td>
						<td>
							<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$cr['agency_id']}"); ?>
							<a href="<?php echo $ci_link; ?>">
								<?php echo  $cr['agency_name']; ?>
							</a>
						</td>
						<td><?php echo  $crm->formatStaffName($cr['rb_fname'],$cr['rb_lname']); ?></td>
						<td><?php echo  $cr['cr_reason']; ?></td>
						<td>
							<?php 
							if( $cr['result'] == 1 ){
								echo '<label class="approvedHLstatus">Accepted</label>';
							}else if( is_numeric($cr['result']) && $cr['result'] == 0 ){
								echo '<label class="declinedHLstatus">Declined</label>';
							}else if( is_numeric($cr['result']) && $cr['result'] == 2 ){
								echo '<label class="more_infoHLstatus">More info needed</label>';
							}else{
								echo '<label class="pendingHLstatus">Pending</label>';
							}
							?>
						</td>
						<td><?php echo  $cr['cr_comments']; ?></td>
						<td>
							<?php echo ($cr['date_processed']!='')?date('d/m/Y',strtotime($cr['date_processed'])):''; ?>
						</td>	
						<td><?php echo  ($cr['amount_credited']>0)?'$'.$cr['amount_credited']:''; ?></td>
						<td style="border-right: 1px solid #cccccc;"><?php echo  $crm->formatStaffName($cr['who_fname'],$cr['who_lname']); ?></td>						
					</tr>
				<?php
				$i++;
				}
				?>
				
				<?php
				// get TOTALS
				$cr_params2 = array(
					'filterDate' => array(
						'from' => $from_date2,
						'to' => $to_date2
					),
					'requested_by' => $requested_by,
					'country_id' => $country_id,
					'result' => $result,
					'agency_id' => $agency_id
				);
				$cr2_sql = $crm->getCreditRequestData($cr_params2);
				
				$cr_ret = this_getGrandTotalAmount($cr2_sql,$country_id);
				$amount_tot = $cr_ret['amount_tot'];
				$credited_tot = $cr_ret['credited_tot'];
				?>
				<tr>
					<td><strong>TOTAL</strong></td>
					<td></td>
					<td><?php echo '$'.number_format($amount_tot,2); ?></td>
					<td colspan="6"></td>
					<td><?php echo '$'.number_format($credited_tot,2); ?></td>
					<td></td>
				</tr>
				
			<?php	
			}else{ ?>
				<tr><td colspan="100%" align="left">Empty</td></tr>
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
		
		
		<input type="hidden" name="employee" value="<?php echo $loggedin_staff_id; ?>" />
		<input type="hidden" name="total_amount" value="<?php echo $amount_tot; ?>" />
		
		<a href="create_credit_request.php">
			<button class="submitbtnImg" id="btn_credit_request" type="button" style="float: left; margin-top: 15px;">Create Adjustment Request</button>
		</a>
    
  </div>

<br class="clearfloat" />

</body>
</html>
