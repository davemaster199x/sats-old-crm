<?php

$title = "Expense Summary Details";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;


//$crm->displaySession();



$exp_sum_id = mysql_real_escape_string($_GET['id']);
$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$loggedin_staff_name = "{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}";
$country_id = $_SESSION['country_default'];

// get expense summary
$exp_sum_params = array(
	'exp_sum_id' => $exp_sum_id
);
$exp_sum_sql = $crm->getExpenseSummary($exp_sum_params);
$exp_sum = mysql_fetch_array($exp_sum_sql);

// get expense items
$jparams = array(
	'sort_list' => array(
		'order_by' => 'exp.`date`',
		'sort' => 'DESC'
	),
	'country_id' => $country_id,
	'exp_sum_id' => $exp_sum_id
);
$exp_sql = $crm->getExpenses($jparams);

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
</style>


    
    <div id="mainContent">
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="Expense Summary" href="expense_summary.php">Expense Summary</a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
      
    
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Submission Successful</div>
	<?php
	}
	?>
	
	
	 <?php
	if($_GET['update_success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Update Successful</div>
	<?php
	}
	?>
	
	
	
	<table id="expense_tbl" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;">
			<tr class="toprow jalign_left">				
				<th>Date of Purchase</th>
				<th>Card Used</th>
				<th>Supplier</th>
				<th>Description</th>
				<th>Account</th>
				<th>Entered By</th>
				<th>Amount</th>
				<th>Net Amt</th>
				<th>GST</th>
				<th>Gross Amt</th>
				<th>Image</th>
			</tr>
			<?php	

			$amount_tot = 0;
			$net_amount_tot = 0;
			$gst_tot = 0;
			
			if( mysql_num_rows($exp_sql)>0 ){
				while($exp = mysql_fetch_array($exp_sql)){ ?>
					<tr class="body_tr jalign_left">
						<td>
							<input type="hidden" name="expense_id[]" value="<?php echo $exp['expense_id']; ?>" />
							<a href="expense_details.php?id=<?php echo $exp['expense_id']; ?>">
								<?php echo  date('d/m/Y',strtotime($exp['date'])); ?>
							</a>
						</td>
						<td><?php echo $crm->getExpenseCards($exp['card']); ?></td>						
						<td><?php echo $exp['supplier']; ?></td>
						<td><?php echo $exp['description']; ?></td>
						<td><?php echo $exp['account_name']; ?></td>
						<td><?php echo "{$exp['eb_fname']} {$exp['eb_lname']}" ?></td>
						<td>$<?php echo $exp['amount']; ?></td>
						<td>$
						<?php 
							// get dynamic GST based on country
							$gst = $crm->getDynamicGST($exp['amount'],$country_id);
							$net_amount = $exp['amount']-$gst;
							echo number_format($net_amount,2);
						?>
						</td>
						<td>$<?php echo number_format($gst,2); ?></td>
						<td>$<?php echo $exp['amount']; ?></td>
						<td>
							<a target="_blank" <?php echo ($exp['file_type']=='image')?'class="fancybox"':''; ?> href="<?php echo $exp['receipt_image']; ?>">
								<img src="/images/<?php echo ($exp['file_type']=='image')?'camera_blue.png':'pdf.png'; ?>" />
							</a>
						</td>
					</tr>
				<?php
					$amount_tot += $exp['amount'];
					$net_amount_tot += $net_amount;
					$gst_tot += $gst;
				}?>
				<tr style="background-color:#eeeeee">
					<td colspan="6"><strong>TOTAL</strong></td>
					<td><strong>$<?php echo number_format($amount_tot,2); ?></strong></td>
					<td><strong>$<?php echo number_format($net_amount_tot,2); ?></strong></td>
					<td><strong>$<?php echo number_format($gst_tot,2); ?></strong></td>
					<td><strong>$<?php echo number_format($amount_tot,2); ?></strong></td>
					<td>&nbsp;</td>
				</tr>
			<?php
			}else{ ?>
				<tr><td colspan="100%" align="left">Empty</td></tr>
			<?php	
			}
			?>			
		</table>
		
		<form action="update_expense_summary_details.php" method="POST">
      	<div class="row line_manager_div" style="float: right; margin: 15px;">
		
			<label class="lm_lbl">Line Manager</label>
			<select name="line_manager" id="line_manager" style="margin-right: 15px;">
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
			
			<label class="lm_lbl">Status</label>
			<select name="exp_sum_status" class="exp_sum_status">
				<option value="">--- Select ---</option>
				<option value="1" <?php echo ( $exp_sum['exp_sum_status']==1 )?'selected="selected"':''; ?>>Approved</option>
				<option value="0" <?php echo ( is_numeric($exp_sum['exp_sum_status']) && $exp_sum['exp_sum_status']==0 )?'selected="selected"':''; ?>>Declined</option>
			</select>
			
			<button class="submitbtnImg" id="btn_update_exp_sum" type="submit" style="margin-left: 30px;;">
				<img class="inner_icon" src="images/save-button.png">
				<span class="btn_update_exp_sum_span">Update</span>
			</button>
		</div>
		<input type="hidden" name="exp_sum_id" value="<?php echo $exp_sum['expense_summary_id'] ?>" />
		</form>
		
	


    
  </div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	// invoke fancybox
	jQuery('.fancybox').fancybox();
	
});
</script>
</body>
</html>
