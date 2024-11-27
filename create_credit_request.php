<?php
$title = "Create Adjustment Request";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$logged_user = $_SESSION['USER_DETAILS']['StaffID'];

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
#job_data_div, #results_not_found_div, #btn_submit{
	display: none;
}
#results_not_found_div{
    float: left;
    margin-left: 230px;
	color: #b4151b;
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
	}
	?>
      	
	
	<div class="addproperty" style="width: 100%;">	
		
		<form method="POST" action="create_credit_request_script.php">
			<div class="row">
				<label class="addlabel">Job Number</label>
				<input type="text"  class="addinput job_number" name="job_number" id="job_number" />
			</div>
			<div id="job_data_div">
				<div class="row">
					<label class="addlabel">Invoice #</label>
					<input type="text"  class="addinput invoice_num" name="invoice_num" id="invoice_num" readonly="readonly" />
				</div>	
				<div class="row">
					<label class="addlabel">Amount</label>
					<input type="text"  class="addinput" name="amount" id="amount" readonly="readonly" />
				</div>
				<div class="row">
					<label class="addlabel">Agency</label>
					<input type="text"  class="addinput" name="agency" id="agency" readonly="readonly" />
				</div>
				<div class="row">
					<label class="addlabel">Staff</label>			
					<?php
						$sql = mysql_query("
							SELECT *
							FROM `staff_accounts`
							WHERE `active` =1
							AND `Deleted` =0
							ORDER BY `FirstName` ASC, `LastName` ASC
						");
					?>
					<select name="staff" id="staff" class="addinput">
					<option value="">----</option>
					<?php 
					while($row=mysql_fetch_array($sql)){ ?>
						<option value="<?php echo $row['StaffID']; ?>" <?php echo ( $row['StaffID']==$logged_user )?'selected="selected"':''; ?>><?php echo $row['FirstName'].' '.$row['LastName']; ?></option>
					<?php
					}
					?>
					</select>
				</div>
				<div class="row">
					<label class="addlabel">Reason </label>
					<textarea name="reason" id="reason" class="addtextarea wider desc_inci" style="height: 84px; margin:0px;"></textarea>
				</div>
			</div>
			<div id="results_not_found_div"></div>
			
			<div class="row">
				<label class="addlabel">&nbsp;</label>
				<button class="submitbtnImg" id="btn_submit" type="submit" style="float: left; margin-top: 30px;">Submit</button>
			</div>
		</form>
		
	</div>
    
  </div>

<br class="clearfloat" />



<script>
jQuery(document).ready(function(){
	
	jQuery("#job_number").keyup(function(){

		var obj = jQuery(this);
		var job_number = obj.val();
			
		if( job_number!='' ){
			
			// invoke ajax
			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "ajax_search_job.php",
				data: { 
					job_number: job_number
				},
				dataType: 'json'
			}).done(function( ret ){
				
				if(ret.alreadyExist==1){
					obj.addClass('redBorder');
					jQuery("#job_data_div #invoice_num").val('');
					jQuery("#job_data_div #amount").val('');
					jQuery("#job_data_div #agency").val('');
					//jQuery("#job_data_div #staff").val('');
					jQuery("#job_data_div").hide();
					jQuery("#results_not_found_div").html("Credit request already exists");
					jQuery("#results_not_found_div").show();
					jQuery("#btn_submit").hide();					
				}else if(ret.invoice_num==null){
					obj.addClass('redBorder');
					jQuery("#job_data_div #invoice_num").val('');
					jQuery("#job_data_div #amount").val('');
					jQuery("#job_data_div #agency").val('');
					//jQuery("#job_data_div #staff").val('');
					jQuery("#job_data_div").hide();
					jQuery("#results_not_found_div").html("Job not found");
					jQuery("#results_not_found_div").show();
					jQuery("#btn_submit").hide();
				}else if(ret.invoice_num!=null){
					obj.removeClass('redBorder');
					jQuery("#job_data_div #invoice_num").val(ret.invoice_num);
					jQuery("#job_data_div #amount").val('$'+ret.amount);
					jQuery("#job_data_div #agency").val(ret.agency);
					//jQuery("#job_data_div #staff").val(ret.staff);
					jQuery("#job_data_div").show();
					jQuery("#results_not_found_div").hide();
					jQuery("#btn_submit").show();					
				}
				jQuery("#load-screen").hide();
				
				
			});	
			
		}			
		
	});

	
});
</script>
</body>
</html>
