<?php

$title = "Expense Summary";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$user_type = $_SESSION['USER_DETAILS']['ClassID'];
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

//$crm->displaySession();

$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$loggedin_staff_name = "{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}";
$country_id = $_SESSION['country_default'];

$from_date = ($_REQUEST['from_date']!='')?mysql_real_escape_string($_REQUEST['from_date']):'';
if($from_date!=''){
	$from_date2 = $crm->formatDate($from_date);
}
$to_date = ($_REQUEST['to_date']!='')?mysql_real_escape_string($_REQUEST['to_date']):'';
if($to_date!=''){
	$to_date2 = $crm->formatDate($to_date);
}

// add global and full access class
if( 
	$staff_id == 2296 ||
	$staff_id == 2097 || 
	$staff_id == 2158 || 
	$staff_id == 2189 || 
	$staff_id == 2156 || 
	$staff_id == 2216 ||
	$user_type == 2 ||
	$user_type == 9
){ // can see all users data
	
	$employee = ($_REQUEST['employee']!='')?mysql_real_escape_string($_REQUEST['employee']):'';
	
}else{ // only their data can see
	
	$employee = ($_REQUEST['employee']!='')?mysql_real_escape_string($_REQUEST['employee']):$loggedin_staff_id;
	
}


$line_manager_search = mysql_real_escape_string($_REQUEST['line_manager_search']);
$filt_sum_status = ($_REQUEST['filt_sum_status']!='')?mysql_real_escape_string($_REQUEST['filt_sum_status']):-1;

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort={$sort}&order_by={$order_by}&from_date={$from_date}&to_date={$to_date}&employee={$employee}&line_manager_search={$line_manager_search}&filt_sum_status={$filt_sum_status}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


// list
$list_params = array(
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'sort_list' => array(
		'order_by' => 'exp_sum.`date`',
		'sort' => 'DESC'
	),
	'filterDate' => array(
		'from' => $from_date2,
		'to' => $to_date2
	),
	'employee' => $employee,
	'country_id' => $country_id,
	'line_manager' => $line_manager_search,
	'exp_sum_status' => $filt_sum_status,
	'echo_query' => 0
);
$exp_sum_sql = $crm->getExpenseSummary($list_params);

// pagination 
$pag_num_params = array(
	'filterDate' => array(
		'from' => $from_date2,
		'to' => $to_date2
	),
	'employee' => $employee,
	'country_id' => $country_id,
	'line_manager' => $line_manager_search,
	'exp_sum_status' => $filt_sum_status
);
$ptotal = mysql_num_rows($crm->getExpenseSummary($pag_num_params));


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
.exp_sum_stat_span, .lm_name_span{
	cursor: pointer;
}
.exp_sum_status, .line_manager{
	display: none;
}
</style>

    
    <div id="mainContent">
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="Leave Summary" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong>Expense Summary</strong></a></li>				
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
	}else if($_GET['exp_state_succ']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Expenses Submitted</div>
	<?php	
	}
	?>
	
	
	<div class="aviw_drop-h" style="border: 1px solid #ccc;">
	
		<form id="form_search" method="post" action="/expense_summary.php">			
			<div class="fl-left">
				<label style="margin-right: 9px;">From:</label>
				<input type="text" name="from_date" id="from_date" style="width: 100px" class="addinput datepicker" value="<?php echo $from_date; ?>" />
			</div>
			
			<div class="fl-left">
				<label style="margin-right: 9px;">To:</label>
				<input type="text" name="to_date" id="to_date" style="width: 100px" class="addinput datepicker" value="<?php echo $to_date; ?>" />
			</div>
			
			<?php	
					
			// list
			$list_params = array(
				'filterDate' => array(
					'from' => $from_date2,
					'to' => $to_date2
				),
				'employee' => $employee,
				'distinct' => 'exp_sum.`employee`',
				'country_id' => $country_id,
				'sort_list' => array(
					'order_by' => 'sa.`FirstName`',
					'sort' => 'ASC'
				),
				'exp_sum_status' => $filt_sum_status
			);
			$emp_sql = $crm->getExpenseSummary($list_params);					
			?>
			<div class="fl-left">
				<label style="margin-right: 9px;">Name:</label>
				<select name="employee">
					<option value="">--- Select ---</option>
					<?php
					while( $emp = mysql_fetch_array($emp_sql) ){ ?>
						<option value="<?php echo $emp['employee']; ?>" <?php echo ($emp['employee']==$employee)?'selected="selected"':''; ?>><?php echo "{$emp['sa_fname']} {$emp['sa_lname']}"; ?></option>
					<?php
					}
					?>
				</select>
			</div>
			
			
			<?php	
					
			// list
			$list_params = array(
				'filterDate' => array(
					'from' => $from_date2,
					'to' => $to_date2
				),
				'employee' => $employee,
				'custom_select' => 'SELECT DISTINCT exp_sum.`line_manager`, lm.`FirstName` AS lm_fname, lm.`LastName` AS lm_lname',
				'country_id' => $country_id,
				'sort_list' => array(
					'order_by' => 'lm.`FirstName`',
					'sort' => 'ASC'
				),
				'exp_sum_status' => $filt_sum_status
			);
			$lm_sql = $crm->getExpenseSummary($list_params);					
			?>
			<div class="fl-left">
				<label style="margin-right: 9px;">Line Manager:</label>
				<select name="line_manager_search">
					<option value="">--- Select ---</option>
					<?php
					while( $lm = mysql_fetch_array($lm_sql) ){ 
						if( $lm['line_manager']>0 ){
						?>
							<option value="<?php echo $lm['line_manager']; ?>" <?php echo ($lm['line_manager']==$line_manager_search)?'selected="selected"':''; ?>><?php echo "{$lm['lm_fname']} {$lm['lm_lname']}"; ?></option>
						<?php
						}
					}
					?>
				</select>
			</div>
			
			
			<div class="fl-left">
				<label style="margin-right: 9px;">Status:</label>
				<select name="filt_sum_status" class="filt_sum_status">
					<option value="-2">All</option>
					<option value="-1" <?php echo ($filt_sum_status==-1)?'selected="selected"':''; ?>>Pending</option>
					<option value="1" <?php echo ($filt_sum_status==1)?'selected="selected"':''; ?>>Approved</option>
					<option value="0" <?php echo ( is_numeric($filt_sum_status) && $filt_sum_status==0)?'selected="selected"':''; ?>>Declined</option>
				</select>
			</div>
			
			<div class="fl-left" style="float:left; margin-left: 10px;">
				<button type="submit" name="btn_submit" class="submitbtnImg">
					<img class="inner_icon" src="images/button_icons/search-button.png">
					Go 
				</button>
			</div>	
		</form>
		
		<div style="float: right;">
			<a href="/export_expense_summary.php?from_date=<?php echo $from_date ?>&to_date=<?php echo $to_date ?>">
				<button type="button" class="submitbtnImg">
					<img class="inner_icon" src="images/export.png">
					Export
				</button>
			</a>
		</div>
		
	</div>
	

	<table id="expense_tbl" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;">
			<tr class="toprow jalign_left">				
				<th>Date of Purchase</th>
				<th>Total Amount</th>
				<th>Name</th>
				<th>Entered By</th>
				<th>Line Manager</th>
				<th>Who</th>
				<th>Status</th>
				<th>PDF</th>
				<th>Date Processed</th>
								
			</tr>
			<?php				
			if( mysql_num_rows($exp_sum_sql)>0 ){
				$i = 0;
				while($exp_sum = mysql_fetch_array($exp_sum_sql)){ ?>
					<tr class="body_tr jalign_left"  <?php echo ($i%2==0)?'':'style="background-color:#eeeeee"'; ?>>
						<td>
							<input type="hidden" name="expense_summary_id[]" class="exp_sum_id" value="<?php echo $exp_sum['expense_summary_id']; ?>" />
							<a href="expense_summary_details.php?id=<?php echo $exp_sum['expense_summary_id']; ?>">
								<?php echo  date('d/m/Y',strtotime($exp_sum['date'])); ?>
							</a>
						</td>		
						<td>
						<?php 
						$exp_tot = $crm->sumExpense($exp_sum['expense_summary_id']); 
						echo '$'.number_format($exp_tot,2,'.',',');
						?>
						</td>
						<td><?php echo "{$exp_sum['sa_fname']} {$exp_sum['sa_lname']}"; ?></td>
						<td><?php echo $crm->getEnteredBy($exp_sum['expense_summary_id']); ?></td>
						<td>							
							<span class="txt_lbl lm_name_span"><?php echo ($exp_sum['line_manager']>0)?"{$exp_sum['lm_fname']} {$exp_sum['lm_lname']}":'<span style="color:#5050c2;">Assign</span>'; ?></span>
							<select class="line_manager">
								<option value="">--- Select ---</option>
								<?php
								// for global and full access
								$staff_sql = mysql_query("
								SELECT DISTINCT(ca.`staff_accounts_id`), sa.`FirstName`, sa.`LastName`
								FROM staff_accounts AS sa
								INNER JOIN `country_access` AS ca ON (
									sa.`StaffID` = ca.`staff_accounts_id` 
									AND ca.`country_id` ={$_SESSION['country_default']}
								)
								WHERE sa.deleted =0
								AND sa.active =1											
								ORDER BY sa.`FirstName`
								");
								while($staff = mysql_fetch_array($staff_sql)){ ?>
									<option value="<?php echo $staff['staff_accounts_id'] ?>" <?php echo ( $staff['staff_accounts_id'] == $exp_sum['line_manager'] )?'selected="selected"':''; ?>><?php echo $staff['FirstName'].' '.$staff['LastName']; ?></option>
								<?php 
								}
								?>
							</select>
						</td>	
						<td><?php echo "{$exp_sum['sa_who_fname']} {$exp_sum['sa_who_lname']}"; ?></td>	
						<td>
							<?php
							$exp_sum_status_txt = '';
							$exp_sum_status_class = '';
							if( $exp_sum['exp_sum_status'] == 1 ){
								$exp_sum_status_txt = 'Approved';
								$exp_sum_status_class = 'approvedHLstatus';
							}else if( is_numeric($exp_sum['exp_sum_status']) && $exp_sum['exp_sum_status'] == 0 ){
								$exp_sum_status_txt = 'Declined';
								$exp_sum_status_class = 'deniedHLstatus';
							}else{
								$exp_sum_status_txt = 'Pending';
								$exp_sum_status_class = 'pendingHLstatus';
							}								
							?>
							<span class="txt_lbl exp_sum_stat_span <?php echo $exp_sum_status_class; ?>"><?php echo $exp_sum_status_txt; ?></span>
							<select class="exp_sum_status">
								<option value="">--- Select ---</option>
								<option value="1" <?php echo ( $exp_sum['exp_sum_status']==1 )?'selected="selected"':''; ?>>Approved</option>
								<option value="0" <?php echo ( is_numeric($exp_sum['exp_sum_status']) && $exp_sum['exp_sum_status']==0 )?'selected="selected"':''; ?>>Declined</option>
							</select>
						</td>
						<td>
							<a target="_blank" href="/expense_summary_pdf.php?id=<?php echo $exp_sum['expense_summary_id']; ?>">
								<img src="images/pdf.png">
							</a>
						</td>
						<td>
							<a href="javascript:void(0);" class="link_date_reim" <?php echo ($exp_sum['date_reimbursed']!='')?'style="display:none;"':''; ?>>+ADD</a>
							<input type="text" class="date_reimbursed datepicker" style="width:80px; <?php echo ($exp_sum['date_reimbursed']!='')?'':'display:none'; ?>" value="<?php echo ($exp_sum['date_reimbursed']!='')?date('d/m/Y',strtotime($exp_sum['date_reimbursed'])):''; ?>" />
						</td>
											
					</tr>
				<?php
				$i++;
				}
				?>
				
				<tr>
					<td><strong>TOTAL</strong></td>
					<td>
					<?php
					// list
					$list_params = array(
						'join_table' => 'expenses',
						'return_sum' => 1,
						'filterDate' => array(
							'from' => $from_date2,
							'to' => $to_date2
						),
						'employee' => $employee,
						'country_id' => $country_id,
						'exp_sum_status' => $filt_sum_status
					);
					$exp_grand_tol_sql = $crm->getExpenseSummary($list_params);
					$exp_grand_tot = mysql_fetch_array($exp_grand_tol_sql);
					echo '$'.number_format($exp_grand_tot['jsum'],2,'.',',');
					?>
					</td>
					<td colspan="100%"></td>
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
		
		<a href="/expense.php">
			<button class="submitbtnImg" id="btn_add_expense" type="button" style="float: left; margin-top: 15px;">
				<img class="inner_icon" src="images/add-button.png">
				Expense
			</button>
		</a>
    
  </div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	
	
	jQuery(".line_manager").change(function(){
		
		var line_manager = jQuery(this).val();
		var exp_sum_id = jQuery(this).parents("tr:first").find(".exp_sum_id").val();	
			
		if( exp_sum_id!='' ){
			
			jQuery.ajax({
			type: "POST",
			url: "ajax_update_exp_sum_line_manager.php",
			data: { 
				exp_sum_id: exp_sum_id,
				line_manager: line_manager
			}
		}).done(function( ret ){
			window.location="/expense_summary.php";
		});
			
		}			
		
	});
	
	
	
	jQuery(".exp_sum_status").change(function(){
		
		var exp_sum_status = jQuery(this).val();
		var exp_sum_id = jQuery(this).parents("tr:first").find(".exp_sum_id").val();	
			
		if( exp_sum_id!='' ){
			
			jQuery.ajax({
			type: "POST",
			url: "ajax_update_exp_sum_status.php",
			data: { 
				exp_sum_id: exp_sum_id,
				exp_sum_status: exp_sum_status
			}
		}).done(function( ret ){
			window.location="/expense_summary.php";
		});
			
		}			
		
	});
	
	
	jQuery(".lm_name_span").click(function(){
		
		jQuery(this).hide();
		jQuery(this).parents("tr:first").find(".line_manager").show();
		
	});
	
	jQuery(".exp_sum_stat_span").click(function(){
		
		jQuery(this).hide();
		jQuery(this).parents("tr:first").find(".exp_sum_status").show();
		
	});
	
	
	jQuery(".link_date_reim").click(function(){
		
		jQuery(this).hide();
		jQuery(this).parents("tr:first").find(".date_reimbursed").show();			
		
	});
	
	
	jQuery(".date_reimbursed").change(function(){
		
		var date_reimbursed = jQuery(this).val();
		var exp_sum_id = jQuery(this).parents("tr:first").find(".exp_sum_id").val();	
			
		if( date_reimbursed!='' ){
			
			jQuery.ajax({
			type: "POST",
			url: "ajax_update_date_reimbursed.php",
			data: { 
				exp_sum_id: exp_sum_id,
				date_reimbursed: date_reimbursed
			}
		}).done(function( ret ){
			window.location="/expense_summary.php";
		});
			
		}			
		
	});
	
});
</script>
</body>
</html>
