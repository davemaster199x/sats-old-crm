<?php

$title = "Leave Request Form";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
//$loggedin_staff_name = "{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}";

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
textarea.reason_for_leave{
	height: 79px;
    margin: 0;
    width: 346px;
}
</style>


    <div id="mainContent">
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="Leave Summary" href="leave_requests.php">Leave Summary</a></li>
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
      	
	<form action="/leave_form_script.php" method="post" id="jform" style="font-size: 14px;">
	<div class="addproperty" style="width: 100%;">	
		
		
		<div class="row">
			<h2 class="heading">Leave Request Form</h2>
		</div>

		<div class="row">
			<label class="addlabel">Name <span style="color:red">*</span></label>
			<select name="employee" id="employee">
				<option value="">----</option>
				<?php
				$jparams = array(
					'sort_list' => array(
						'order_by' => 'sa.`FirstName`',
						'sort' => 'ASC'
					),
					'country_id' => $country_id
				);
				$staff_sql = $crm->getStaffAccount($jparams);
				while($staff = mysql_fetch_array($staff_sql)){ ?>
					<option value="<?php echo $staff['staff_accounts_id'] ?>" <?php echo ($staff['staff_accounts_id']==$loggedin_staff_id)?'selected="selected"':''; ?>><?php echo $staff['FirstName'].' '.$staff['LastName']; ?></option>
				<?php 
				}
				?>
			</select>			
		</div>
		<div class="row">
			<label class="addlabel">Type of Leave <span style="color:red">*</span></label>
			<select name="type_of_leave" id="type_of_leave">
				<option value="">----</option>
				<option value="1">Annual</option>	
				<option value="2">Personal(sick)</option>
				<option value="3">Personal(carer's)</option>
				<option value="4">Compassionate</option>
				<option value="5">Cancel Previous Leave</option>
				<option value="-1">Other</option>
			</select>
		</div>
		<div class="row" id="backup_leave_div" style="display:none;">
			<label class="addlabel">If you have no more sick leave, would you like to use <span style="color:red">*</span></label>
			<select name="backup_leave" id="backup_leave">
				<option value="">----</option>
				<option value="1">Annual leave</option>	
				<option value="2">Leave without pay</option>
			</select>
		</div>
		<div class="row">
			<label class="addlabel">First Day of Leave <span style="color:red">*</span></label>
			<input type="text"  class="addinput datepicker lday_of_work" name="lday_of_work" id="lday_of_work" />
		</div>
		<div class="row">
			<label class="addlabel">Last Day of Leave <span style="color:red">*</span></label>
			<input type="text"  class="addinput datepicker fday_back" name="fday_back" id="fday_back" />
		</div>
		<div class="row">
			<label class="addlabel">Number of days <span style="color:red">*</span></label>
			<input type="text"  class="addinput" name="num_of_days" id="num_of_days">
		</div>
		<div class="row">
			<label class="addlabel">Reason for Leave <span style="color:red">*</span></label>
			<textarea class="addtextarea reason_for_leave" name="reason_for_leave" id="reason_for_leave"></textarea>
		</div>
		<div class="row">
			<label class="addlabel">Line Manager <span style="color:red">*</span></label>
			<select name="line_manager" id="line_manager">
				<option value="">----</option>
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
		
		<div class="row">
			<label class="addlabel">&nbsp;</label>
			<div>
				<input type="checkbox" class="addinput" name="confirm_chk" id="confirm_chk" style="width:auto; float: left;" value="1" />
				<span style=" float: left; margin: 5px 0 0 10px; text-align: left; width: 28%;">
					I understand that on submitting this form it is only a request for leave and leave is not granted until I receive confirmation from SATS
				</span>
			</div>
		</div>
		
		
		
		<div class="row">
			<label class="addlabel">&nbsp;</label>
        	<button class="submitbtnImg" id="btn_add_vehicle" type="submit" style="float: left; margin-top: 30px;">Submit</button>
        </div>
	</div>
	</form>


    
  </div>

<br class="clearfloat" />



<script>


jQuery(document).ready(function(){

	
	
	// form validation
	jQuery("#jform").submit(function(){
		
		var error = "";
		
		// Leave Request Form
		var employee = jQuery("#employee").val();
		var date = jQuery("#date").val();
		var type_of_leave = jQuery("#type_of_leave").val();
		var lday_of_work = jQuery("#lday_of_work").val();
		var fday_back = jQuery("#fday_back").val();
		var num_of_days = jQuery("#num_of_days").val();
		var reason_for_leave = jQuery("#reason_for_leave").val();
		var line_manager = jQuery("#line_manager").val();
		var backup_leave = jQuery("#backup_leave").val();
		
		

		//console.log(line_manager_app);
		
		
		// The Incident
		if( employee == "" ){
			error += "Name is required\n";
		}
		if( date == "" ){
			error += "Date is required\n";
		}
		if( type_of_leave == "" ){
			error += "Type of Leave is required\n";
		}
		if( lday_of_work == "" ){
			error += "Last Day of Work is required\n";
		}
		if( fday_back == "" ){
			error += "First Day Back is required\n";
		}
		if( num_of_days == "" ){
			error += "Number of days is required\n";
		}
		if( reason_for_leave == "" ){
			error += "Reason for Leave is required\n";
		}
		if( line_manager == "" ){
			error += "Line Manager is required\n";
		}

		if( jQuery("#backup_leave").hasClass('g_validate') && backup_leave==""){
			error += "If you have no more sick leave, would you like to use is required\n";
		}
		
		
		
		if(  jQuery("#confirm_chk").prop("checked")==false ){
			error += "Please tick the confirm box to proceed\n";
		}
		
		if( error!="" ){			
			alert(error);
			return false;
		}else{
			return true;
		}
		
	});

	$('#type_of_leave').change(function(){
		if( $(this).val()==2 ){
			$('#backup_leave_div').show();
			$('#backup_leave').addClass('g_validate');
		}else{
			$('#backup_leave_div').hide();
			$('#backup_leave').val(""); //empty backup_leave dropdown
			$('#backup_leave').removeClass('g_validate');
		}
	})
	
	
});
</script>
</body>
</html>
