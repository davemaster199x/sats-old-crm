<?php

$title = "Expense";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;


//$crm->displaySession();

$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$loggedin_staff_name = "{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}";
$country_id = $_SESSION['country_default'];

// get 1st entered expenses
$jparams = array(
	'sort_list' => array(
		'order_by' => 'exp.`expense_id`',
		'sort' => 'ASC'
	),
	'paginate' => array(
		'offset' => 0,
		'limit' => 1
	),
	'entered_by' => $loggedin_staff_id,
	'country_id' => $country_id,
	'exc_sub_exp' => 1
);
$exp_user_sql = $crm->getExpenses($jparams);
$exp_user = mysql_fetch_array($exp_user_sql);


// get non-submitted expense list
$jparams = array(
	'sort_list' => array(
		'order_by' => 'exp.`date`',
		'sort' => 'DESC'
	),
	'entered_by' => $loggedin_staff_id,
	'country_id' => $country_id,
	'exc_sub_exp' => 1
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
#remember_lm{
	float: left;
    width: auto;
    margin-left: 10px;
    margin-top: 10px;
}
.remember_lm_span{
	float: left;
	margin-top: 7px;
	margin-left: 6px;
}
.line_manager_div{
	display: none;
}
.lm_lbl{
	margin-right: 13px;
	position: relative;
	top: 5px;
}
</style>


    
    <div id="mainContent">
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="Leave Summary" href="expense_summary.php">Expense Summary</a></li>
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
	}else if($_GET['exp_state_succ']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Expenses Submitted</div>
	<?php	
	}else if($_GET['cleared']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Expenses Cleared</div>
	<?php	
	}
	?>
	
	
	
	
	<form id="submit_expense_form" action="submit_expense_statement.php" method="post">
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
							<a target="_blank" <?php
							$ext = end((explode(".", $exp['receipt_image']))); # extra () to prevent notice
							if ($ext == 'heic') {
								# code...
							} else {
							echo ($exp['file_type']=='image')?'class="fancybox"':''; 
							}
							?> href="<?php echo $exp['receipt_image']; ?>">
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
		
		
		
		<?php
		if( mysql_num_rows($exp_sql)>0 ){ ?>	


			
		
		<input type="hidden" name="employee" value="<?php echo $exp_user['emp_staff_id']; ?>" />
		<input type="hidden" name="total_amount" value="<?php echo $amount_tot; ?>" />

		
			<button class="submitbtnImg" id="btn_submit_expense" type="button" style="float: right; margin-top: 15px;">
				<img class="inner_icon" src="images/select-button.png">
				<span class="btn_submit_expense_span">Submit</span>
			</button>
			<div class="row line_manager_div" style="float: right; margin: 15px;">
				<label class="lm_lbl">Line Manager</label>
				<select name="line_manager" id="line_manager" style="float: right;">
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
						<option value="<?php echo $staff['staff_accounts_id'] ?>"><?php echo $staff['FirstName'].' '.$staff['LastName']; ?></option>
					<?php 
					}
					?>
				</select>
			</div>
			<button type="button" class="submitbtnImg blue-btn" id="btn_clear" style="float: right; margin-top: 15px; margin-right: 10px;">
			<img class="inner_icon" src="images/cancel-button.png">
			Clear
			</button>
		<?php	
		}
		?>		
		</form>
	
      	
	<form action="/add_expense.php" method="post" id="jform" style="font-size: 14px;" enctype="multipart/form-data">
	<div class="addproperty" style="width: 100%;">	
		
		
		<div class="row">
			<h2 class="heading"><?php echo $title; ?> Form</h2>
		</div>

		<div class="row">
			<label class="addlabel">Name</label>
			<?php
			
			
			if( mysql_num_rows($exp_user_sql)>0 ){ 
			
			?>
			
				<input type="text" readonly="readonly" class="addinput employe_name" name="employe_name" id="employe_name" value="<?php echo "{$exp_user['emp_fname']} {$exp_user['emp_lname']}"; ?>" />
				<input type="hidden" name="employee" value="<?php echo $exp_user['emp_staff_id']; ?>" />
			
			<?php	
			}else{ ?>
			
				<select name="employee" id="employee">
					<option value="">----</option>
					<?php
					$jparams = array(
						'sort_list' => array(
							'order_by' => '`FirstName`',
							'sort' => 'ASC'
						)
					);
					$sa_sql = $crm->getStaffAccount($jparams);
					while($sa = mysql_fetch_array($sa_sql)){ ?>
						<option value="<?php echo $sa['StaffID'] ?>" <?php echo ($sa['StaffID']==$loggedin_staff_id)?'selected="selected"':''; ?>><?php echo "{$sa['FirstName']} {$sa['LastName']}"; ?></option>
					<?php 
					}
					?>
				</select>
			
			<?php	
			}
			?>
			
		</div>

		<div class="row">
			<label class="addlabel">Date of Purchase</label>
			<input type="text"  class="addinput datepicker" name="date" id="date" value="<?php echo date("d/m/Y"); ?>" />
		</div>
		
		
		<div class="row">
			<label class="addlabel">Card Used</label>
			<select name="card" id="card">
				<option value="1">Company Card</option>	
				<option value="2">Personal Card</option>
				<option value="3">AU Main Card</option>
				<option value="4">NZ Main Card</option>
				<option value="5">Cash</option>
			</select>
		</div>
		
		
		<div class="row">
			<label class="addlabel">Supplier</label>
			<input type="text"  class="addinput supplier" name="supplier" id="supplier" />
		</div>
		
		<div class="row">
			<label class="addlabel">Description</label>
			<input type="text"  class="addinput description" name="description" id="description" placeholder="Eg. Lunch whilst away in Dubbo" />
		</div>
		
		<div class="row">
			<label class="addlabel">Account</label>
			<select name="account" id="account">
				<option value="">----</option>
				<?php
				$jparams = array(
					'sort_list' => array(
						'order_by' => '`account_name`',
						'sort' => 'ASC'
					)
				);
				$supp_sql = $crm->getExpenseAccount($jparams);
				while($supp = mysql_fetch_array($supp_sql)){ ?>
					<option value="<?php echo $supp['expense_account_id'] ?>"><?php echo $supp['account_name']; ?></option>
				<?php 
				}
				?>
			</select>
		</div>
		
		
		<div class="row">
			<label class="addlabel">Amount</label>
			<div style="float:left;">
				$<input type="text" class="addinput amount" name="amount" id="amount" />
			</div>
		</div>
		
		
		<div class="row">
			<label class="addlabel">Receipt</label>
			<div>
				<input type="file" capture="camera" name="receipt_image" id="receipt_image" class="addinput receipt" />
			</div>
		</div>		
		
		
		<div class="row">
			<label class="addlabel">&nbsp;</label>
			<div>
				<input type="checkbox" class="addinput" name="confirm_chk" id="confirm_chk" style="width:auto; float: left;" value="1" />
				<span style=" float: left; margin: 5px 0 0 10px; text-align: left; width: 28%;">
					I declare that the attached purchase was made in line with company policies and was an expense incurred as part of me performing my role
				</span>
			</div>
		</div>
		
		
		<div class="row">
			<label class="addlabel">&nbsp;</label>
        	<button class="submitbtnImg" id="btn_submit" type="submit" style="float: left; margin-top: 30px;">
				<img class="inner_icon" src="images/select-button.png">
				Submit
			</button>
        </div>
	</div>
	</form>


    
  </div>

<br class="clearfloat" />



<script>


jQuery(document).ready(function(){
	
	// invoke fancybox
	jQuery('.fancybox').fancybox();
	
	
	
	// clear expense
	jQuery("#btn_clear").click(function(){
		
		if( confirm('Are you sure you want to proceed?') ){
			window.location='clear_expense.php';
		}
		
	});

	
	jQuery("#btn_submit_expense").click(function(){
		
		var btn_txt_span =  jQuery(this).find('.btn_submit_expense_span');
		var bnt_txt = btn_txt_span.html();
		
		if( bnt_txt == 'Submit' ){	
		
			jQuery(".line_manager_div").show();
			btn_txt_span.html("Submit Expense Statement");
			
		}else if( bnt_txt == 'Submit Expense Statement' ){	
		
			var line_manager = jQuery("#line_manager").val();
			var error = '';
			
			if( line_manager == '' ){
				error += 'Line Manager is Required\n';
			}
			
			if( error != '' ){
				alert(error);
			}else{				
				if( confirm("This will Submit Expense Statement. Proceed?") ){				
					jQuery("#submit_expense_form").submit();					
				}				
			}
			
		}
		
		
	});
	
	
	// form validation
	jQuery("#jform").submit(function(){
		
		var error = "";
		
		// Leave Request Form
		var employee = jQuery("#employee").val();
		var date = jQuery("#date").val();
		var supplier = jQuery("#supplier").val();
		var description = jQuery("#description").val();
		var account = jQuery("#account").val();
		var amount = jQuery("#amount").val();
		var receipt_image = jQuery("#receipt_image").val();


		//console.log(line_manager_app);
		
		
		// The Incident
		if( employee == "" ){
			error += "Name is required\n";
		}
		if( date == "" ){
			error += "Date of Purchase is required\n";
		}
		if( supplier == "" ){
			error += "Supplier is required\n";
		}
		if( description == "" ){
			error += "Description is required\n";
		}
		if( account == "" ){
			error += "Account is required\n";
		}
		if( amount == "" ){
			error += "Amount is required\n";
		}
		if( receipt_image == "" ){
			error += "Reciept Image is required\n";
		}

		if( jQuery("#confirm_chk").prop("checked")==false ){
			error += "Please confirm checkbox";
		}

		
		if( error!="" ){			
			alert(error);
			return false;
		}else{
			return true;
		}
		
	});
	
	
});
</script>
</body>
</html>
