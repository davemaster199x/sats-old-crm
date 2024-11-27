<?php

$title = "Expense Details";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;


//$crm->displaySession();

$expense_id = mysql_real_escape_string($_GET['id']);
$country_id = $_SESSION['country_default'];

$jparams = array(
	'sort_list' => array(
		'order_by' => 'exp.`date`',
		'sort' => 'DESC'
	),
	'expense_id' => $expense_id,
	'country_id' => $country_id
);
$exp_sql = $crm->getExpenses($jparams);
$exp = mysql_fetch_array($exp_sql);

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
		<li class="other first"><a title="Expense" href="expense.php">Expense</a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
      
    
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Update Successful</div>
	<?php
	}
	?>
	
	
	
      	
	<form action="/update_expense.php" method="post" id="jform" style="font-size: 14px;" enctype="multipart/form-data">
	<div class="addproperty" style="width: 100%;">	
		
		
		<div class="row">
			<h2 class="heading"><?php echo $title; ?> Form</h2>
		</div>

		<div class="row">
			<label class="addlabel">Name</label>
			<input type="text" readonly="readonly" class="addinput employe_name" name="employe_name" id="employe_name" value="<?php echo "{$exp['emp_fname']} {$exp['emp_lname']}"; ?>" />
		</div>
		
		<div class="row">
			<label class="addlabel">Date of Purchase</label>
			<input type="text"  class="addinput datepicker" name="date" id="date" value="<?php echo  date('d/m/Y',strtotime($exp['date'])); ?>" />
		</div>
		
		<div class="row">
			<label class="addlabel">Card Used</label>
			<select name="card" id="card">
				<option value="1" <?php echo ($exp['card']==1)?'selected="selected"':''; ?>>Company Card</option>	
				<option value="2" <?php echo ($exp['card']==2)?'selected="selected"':''; ?>>Personal Card</option>
				<option value="3" <?php echo ($exp['card']==3)?'selected="selected"':''; ?>>AU Main Card</option>
				<option value="4" <?php echo ($exp['card']==4)?'selected="selected"':''; ?>>NZ Main Card</option>
				<option value="5" <?php echo ($exp['card']==5)?'selected="selected"':''; ?>>Cash</option>
			</select>
		</div>
		
		<div class="row">
			<label class="addlabel">Supplier</label>
			<input type="text"  class="addinput supplier" name="supplier" id="supplier" value="<?php echo $exp['supplier']; ?>" />
		</div>
		
		<div class="row">
			<label class="addlabel">Description</label>	
			<input type="text"  class="addinput description" name="description" id="description" placeholder="Eg. Lunch whilst away in Dubbo" value="<?php echo $exp['description']; ?>" />
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
					<option value="<?php echo $supp['expense_account_id']; ?>" <?php echo ($supp['expense_account_id']==$exp['account'])?'selected="selected"':''; ?>><?php echo $supp['account_name']; ?></option>
				<?php 
				}
				?>
			</select>
		</div>
		
		
		<div class="row">
			<label class="addlabel">Amount</label>
			<div style="float:left;">
				$<input type="text" class="addinput amount" name="amount" id="amount" value="<?php echo $exp['amount']; ?>" />
			</div>
		</div>
		
		
		<div class="row">
			<label class="addlabel">Receipt</label>
			<div>				
				<a target="_blank" <?php echo ($exp['file_type']=='image')?'class="fancybox"':''; ?> href="<?php echo $exp['receipt_image']; ?>">
					<img style="float: left; margin: 4px 10px 0 0; width: 23px;" src="/images/<?php echo ($exp['file_type']=='image')?'camera_blue.png':'pdf.png'; ?>" />
				</a>
				<input type="file" capture="camera" name="receipt_image" id="receipt_image" class="addinput receipt" style="float: left; width: 314px;" />
			</div>
		</div>		
		
		
		<div class="row">
			<label class="addlabel">Entered By</label>
			<input type="text" readonly="readonly" class="addinput entered_by_name" name="entered_by_name" id="entered_by_name" value="<?php echo "{$exp['eb_fname']} {$exp['eb_lname']}"; ?>" />
		</div>
		
		
		
		<div class="row">
			<label class="addlabel">&nbsp;</label>
			<input type="hidden" name="expense_id" id="expense_id" value="<?php echo $expense_id; ?>" />
			<input type="hidden" name="image_touched" id="image_touched" value="" />
        	<button class="submitbtnImg blue-btn" id="btn_update" type="submit" style="float: left; margin-top: 30px;">
				<img class="inner_icon" src="images/button_icons/save-button.png">
				Update
			</button>
			<button class="submitbtnImg" id="btn_delete" type="button" style="float: left; margin-top: 30px; margin-left: 10px;">
				Delete
				<img class="inner_icon" src="images/button_icons/cancel-button.png">
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
	
	
	// if image is touched
	jQuery("#receipt_image").change(function(){		
		jQuery("#image_touched").val(1);		
	});
	
	
	
	jQuery("#btn_delete").click(function(){

		if( confirm("Are you sure you want to delete?") ){
			window.location='delete_expense.php?id=<?php echo $expense_id ?>';
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
