<?php
$title = "Create Adjustment Request";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$cr_id = mysql_real_escape_string($_REQUEST['id']);
$country_id = $_SESSION['country_default'];

// list
$list_params = array(
	'cr_id' => $cr_id,
	'country_id' => $country_id,
	'result' => 'ALL',
	'echo_query' => 0
);
$cr_sql = $crm->getCreditRequestData($list_params);

$cr = mysql_fetch_array($cr_sql);

// get amount
$grand_total = getJobAmountGrandTotal($cr['job_id'],$country_id);

?>
<style>
.addproperty input, .addproperty select, .addproperty textarea {
    width: 20%;
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
.redBorder{
	border: 1px solid #b4151b;
}

.dollarClass{
	float: left;
    width: auto !important;
    margin-right: 4px !important;
    margin-top: 8px !important;
}
</style>

    
    <div id="mainContent">
	
	
    <div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="Adjustment Request Summary" href="credit_requests.php">Adjustment Request Summary</a></li>
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
      	
	
	<div class="addproperty" style="width: 100%;">	
		
		<form id="credit_request_form" method="POST" action="update_credit_request_details.php">
			<div class="row">
				<label class="addlabel">Job Number</label>
				<input type="text"  class="addinput job_number" name="job_number" id="job_number" readonly="readonly" value="<?php echo $cr['job_id']; ?>" />
			</div>	
			<div class="row">
				<label class="addlabel">Amount</label>
				<input type="text"  class="addinput" name="amount" id="amount" readonly="readonly" value="<?php echo '$'.number_format($grand_total,2); ?>" />
			</div>
			<div class="row">
				<label class="addlabel">Agency</label>
				<input type="text"  class="addinput" name="agency" id="agency" readonly="readonly" value="<?php echo  $cr['agency_name']; ?>" />
			</div>
			<div class="row">
				<label class="addlabel">Staff</label>
				<input type="text"  class="addinput" name="requested_by_name" id="requested_by_name" readonly="readonly" readonly="readonly" value="<?php echo  "{$cr['rb_fname']} {$cr['rb_lname']}"; ?>" />
			</div>
			<div class="row">
				<label class="addlabel">Reason </label>
				<textarea name="reason" id="reason" class="addtextarea wider desc_inci" style="height: 84px; margin:0px;"><?php echo  $cr['cr_reason']; ?></textarea>
			</div>
			
			
			<h2 class="heading">Accounts Use Only</h2>
			<div class="row">
				<label class="addlabel">Result </label>
				<select name="result">
					<option value="">--- Select ---</option>	
					<option value="1" <?php echo ($cr['result']==1)?'selected="selected"':''; ?>>Accept</option>	
					<option value="0" <?php echo ( is_numeric($cr['result']) && $cr['result']==0 )?'selected="selected"':''; ?>>Decline</option>
					<option value="2" <?php echo ( is_numeric($cr['result']) && $cr['result']==2 )?'selected="selected"':''; ?>>More info needed</option>
				</select>
			</div>		
			<div class="row">
				<label class="addlabel">Amount Credited <span style="color:red;">*</span></label>
				<label class="dollarClass">$</label>
				<input type="text" style="float:left; width: 19%;" class="addinput" name="amount_credited" id="amount_credited" value="<?php echo $cr['amount_credited']; ?>" />
			</div>
			<div class="row">
				<label class="addlabel">Date Processed</label>
				<input type="text"  class="addinput datepicker" name="date_processed" id="date_processed" value="<?php echo ($cr['date_processed']!='' && $cr['date_processed']!='0000-00-00 00:00:00' )?date('d/m/Y',strtotime( $cr['date_processed'])):''; ?>" />
			</div>
			<div class="row">
				<label class="addlabel">Comments </label>
				<textarea name="comments" id="comments" class="addtextarea wider comments" style="height: 84px; margin:0px;"><?php echo $cr['cr_comments']; ?></textarea>
			</div>
			


			
			<div class="row">
				<input type="hidden"  class="addinput" name="cr_id" id="cr_id" value="<?php echo  $cr['credit_request_id']; ?>" />
				<input type="hidden"  class="addinput" name="job_id" id="job_id" value="<?php echo  $cr['job_id']; ?>" />
				<input type="hidden"  class="addinput" name="requested_by_id" id="requested_by_id" value="<?php echo  $cr['rb_staff_id']; ?>" />
				<label class="addlabel">&nbsp;</label>
				<button class="submitbtnImg blue-btn" id="btn_update" type="submit" style="float: left; margin-top: 30px;">Update</button>
				
				<?php
				if( $_SESSION['USER_DETAILS']['ClassID']==2 ){ ?>
				
					<a href="delete_credit_request.php?id=<?php echo $cr_id; ?>" class="link_delete" onclick="return confirm('Are you sure you want to delete?')">
						<button class="submitbtnImg" id="btn_delete" type="button" style="float: left; margin-top: 30px; margin-left: 10px;">Delete</button>
					</a>
				
				<?php	
				}
				?>
				
			</div>
		</form>
		
		
	</div>
    
  </div>

<br class="clearfloat" />

<script>
jQuery("#credit_request_form").submit(function(){
	
	var amount_credited = jQuery("#amount_credited").val();
	var error = '';
	
	if( amount_credited=='' ){
		error += 'Amount Credited field is required\n';
	}
	
	if( error!='' ){
		alert(error);
		return false;
	}else{
		return true;
	}
	
});
</script>

</body>
</html>
