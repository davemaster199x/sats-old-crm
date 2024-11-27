<?php

$title = "Send Statements";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$url = 'send_statements.php';

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
	SUM(j.`invoice_balance`) AS invoice_balance_tot, a.`agency_name`, a.`agency_id`, a.`account_emails`, a.`agency_emails`, a.`send_statement_email_ts`
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
.functions_div{
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
.body_tr:hover{
	background-color: #fcbdb6 !important;
}
.hover_color{
	background-color: #fcbdb6 !important;
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
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Email Sent</div>';
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
					
					
					<!--
					<div class='fl-left' style="float:right;">	
						<a href="debtors_report_pdf.php" target="_blank" style="margin-left: 0 !important;">
							<button type='button' class='submitbtnImg blue-btn' id="btn_display_statement">
								<img class="inner_icon" src="images/button_icons/pdf_white.png">
								Debtors Report
							</button>
						</a>
					</div>
					-->
					
					
					
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
					<th>Total Amount Due</th>
					<th>Email Address</th>
					<th>Last Sent</th>
					<th>
						<input type="checkbox" id="check_all" />
					</th>
				</tr>
				<?php	
				$i= 0;
				if(mysql_num_rows($plist)>0){
					
					while($row = mysql_fetch_array($plist)){
					
				?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							<td>
								<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
								<a href="<?php echo $ci_link ?>">
									<?php echo $row['agency_name']; ?>
								</a>
							</td>							
							<?php $invoice_balance = $row['invoice_balance_tot']; ?>
							<td>$<?php echo number_format($invoice_balance,2); ?></td>	
							<?php 
							$acc_em_arr = $crm->convertEmailToArray($row['account_emails']); 
							$accounts_email = implode(",",$acc_em_arr);
							?>
							<td><input type="text" class="accounts_email" value="<?php echo $accounts_email; ?>" /></td>
							<td class="jtimestamp"><?php echo ( $crm->isDateNotEmpty($row['send_statement_email_ts']) )?date('d/m/Y H:i',strtotime($row['send_statement_email_ts'])):''; ?></td>
							<td><input type="checkbox" class="chk_agency" value="<?php echo $row['agency_id']; ?>" /></td>
						
						</tr>
						
				<?php
				

					$invoice_balance_tot += $invoice_balance;
				
					$i++;
					}
				?>
				
					<tr class="jalign_left jtblfooter">
							
						<td>TOTAL</td>	
						<td>$<?php echo number_format($invoice_balance_tot,2); ?></td>
						<td></td>
						<td></td>
						<td></td>
					
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
		
		
		<div class="functions_div">				
			<button type='submit' class='submitbtnImg blue-btn' id="btn_email">
				<img class="inner_icon" src="images/button_icons/email.png">
				Email
			</button>
		</div>

		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	
	jQuery("#check_all").change(function(){
		
		var isTicked = jQuery(this).prop("checked");
		var num_ticked_agency = jQuery(".chk_agency:checked").length;
		
		if( isTicked == true ){
			jQuery(".chk_agency").prop("checked",true);
			jQuery(".body_tr").addClass("hover_color");
		}else{
			jQuery(".chk_agency").prop("checked",false);
			jQuery(".body_tr").removeClass("hover_color");
		}
		
		if( num_ticked_agency > 0 ){
			jQuery(".functions_div").show();
		}else{
			jQuery(".functions_div").hide();
		}
		
	});
	
	
	jQuery(".chk_agency").change(function(){
		
		var isTicked = jQuery(this).prop("checked");
		var num_ticked_agency = jQuery(".chk_agency:checked").length;
		
		if( isTicked == true ){
			jQuery(this).parents("tr:first").addClass("hover_color");
		}else{
			jQuery(this).parents("tr:first").removeClass("hover_color");
		}
			
		
		if( num_ticked_agency > 0 ){
			jQuery(".functions_div").show();
		}else{
			jQuery(".functions_div").hide();
		}
		
	});
	
	
	jQuery("#btn_email").click(function(){
		
		var agency_id_arr = [];
		jQuery(".chk_agency:checked").each(function(){
			
			var agency_id = jQuery(this).val();
			var accounts_email = jQuery(this).parents("tr:first").find(".accounts_email").val();
			
			json_data = { 
				'agency_id': agency_id, 
				'accounts_email': accounts_email 
			}
			var json_str = JSON.stringify(json_data);
			
			agency_id_arr.push(json_str);
			
		});
		
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_email_agency_statements.php",
			data: { 
				agency_id_arr: agency_id_arr,
				
			}
		}).done(function( ret ) {
			window.location="<?php echo $url; ?>?success=1";
		});	
		
		
	});
	
});
</script>
</body>
</html>