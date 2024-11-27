<?php

$title = "Leave Details";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
//$staff_name = "{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}";
$country_id = $_SESSION['country_default'];


$leave_id = mysql_real_escape_string($_GET['id']);

$jparams = array(
	'leave_id' => $leave_id,
	'country_id' => $country_id
);
$leave_sql = $crm->getLeave($jparams);
$leave = mysql_fetch_array($leave_sql);

?>
<style>
.addproperty input, .addproperty select, .addproperty textarea {
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
textarea.addtextarea{
	height: 79px;
    margin: 0;
}


.approvedHL{
	color:green; 
}
.pendingHL{
	color:red; 
	font-style: italic;
}
.approvedHLstatus{
	color:green; 
	font-weight:bold;
}
.pendingHLstatus{
	color:red; 
	font-style: italic;
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
		<div class="success" style="margin-bottom: 12px;">Update Successful</div>
	<?php
	}
	?>
      	
	<form action="/leave_details_update.php" method="post" id="jform" style="font-size: 14px;">
	<div class="addproperty" style="width: 100%;">	
		
		
		<div class="row">
			<h2 class="heading">Leave Request Form</h2>
		</div>
		<div class="row">
			<label class="addlabel">Date</label>
			<input type="text"  class="addinput date" name="date" id="date" value="<?php echo date('d/m/Y',strtotime($leave['date'])); ?>" readonly="readonly" />
		</div>
		<div class="row">
			<label class="addlabel">Name</label>
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
					<option value="<?php echo $staff['staff_accounts_id'] ?>" <?php echo ($staff['staff_accounts_id']==$leave['emp_staff_id'])?'selected="selected"':''; ?>><?php echo $staff['FirstName'].' '.$staff['LastName']; ?></option>
				<?php 
				}
				?>
			</select>			
		</div>
		<div class="row">
			<label class="addlabel">Type of Leave</label>
			<select name="type_of_leave" id="type_of_leave">
				<option value="">----</option>
				<option value="1" <?php echo ($leave['type_of_leave']==1)?'selected="selected"':'' ?>>Annual</option>	
				<option value="2" <?php echo ($leave['type_of_leave']==2)?'selected="selected"':'' ?>>Personal(sick)</option>
				<option value="3" <?php echo ($leave['type_of_leave']==3)?'selected="selected"':'' ?>>Personal(carer's)</option>
				<option value="4" <?php echo ($leave['type_of_leave']==4)?'selected="selected"':'' ?>>Compassionate</option>
				<option value="5" <?php echo ($leave['type_of_leave']==5)?'selected="selected"':'' ?>>Cancel Previous Leave</option>
				<option value="-1" <?php echo ($leave['type_of_leave']==-1)?'selected="selected"':'' ?>>Other</option>
			</select>
		</div>
		<div class="row" id="backup_leave_div" style="display:<?php echo ($leave['type_of_leave']!=2) ? 'none' : NULL; ?>;">
			<label class="addlabel">If you have no more sick leave, would you like to use <span style="color:red">*</span></label>
			<select name="backup_leave" id="backup_leave" class="<?php echo ($leave['type_of_leave']==2) ? 'g_validate' : NULL; ?>">
				<option  value="">----</option>
				<option <?php echo ($leave['backup_leave']==1)?'selected="selected"':'' ?> value="1">Annual leave</option>	
				<option <?php echo ($leave['backup_leave']==2)?'selected="selected"':'' ?> value="2">Leave without pay</option>
			</select>
		</div>
		<div class="row">
			<label class="addlabel">First Day of Leave</label>
			<?php $fdol = date('d/m/Y',strtotime($leave['lday_of_work'])); ?>
			<input type="text"  class="addinput datepicker lday_of_work" name="lday_of_work" id="lday_of_work" value="<?php echo $fdol; ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Last Day of Leave</label>
			<?php $ldol = date('d/m/Y',strtotime($leave['fday_back'])); ?>
			<input type="text"  class="addinput datepicker fday_back" name="fday_back" id="fday_back" value="<?php echo $ldol; ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Number of days </label>
			<input type="text"  class="addinput" name="num_of_days" id="num_of_days" value="<?php echo $leave['num_of_days']; ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Reason for Leave </label>
			<textarea class="addtextarea reason_for_leave" name="reason_for_leave" id="reason_for_leave"><?php echo $leave['reason_for_leave']; ?></textarea>
		</div>
		
		<div class="row">
			<h2 class="heading">Office Use Only</h2>
		</div>
		<div class="row">
			<label class="addlabel">Line Manager</label>
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
					<option value="<?php echo $staff['staff_accounts_id'] ?>" <?php echo ($staff['staff_accounts_id']==$leave['sa_lm_staff_id'])?'selected="selected"':''; ?>><?php echo $staff['FirstName'].' '.$staff['LastName']; ?></option>
				<?php 
				}
				?>
			</select>
		</div>
		<div class="row">
			<label class="addlabel">HR Approved</label>
			<div class="chk_div">
				<input type="radio"  class="addinput chkbox hr_app" name="hr_app" id="hr_app" value="1" <?php echo ( is_numeric($leave['hr_app']) && $leave['hr_app']==1 )?'checked="checked"':''; ?> /> <span>Yes</span>
			</div>
			<div class="chk_div">
				<input type="radio"  class="addinput chkbox hr_app" name="hr_app" id="hr_app" value="0" <?php echo ( is_numeric($leave['hr_app']) && $leave['hr_app']==0 )?'checked="checked"':''; ?> /> <span>No</span>
			</div>
			<?php 
			// timestamp
			$hlclass = '';
			$timestamp_str = '';
			$updated_by = '';
			if( is_numeric($leave['hr_app']) && $leave['hr_app']==1 ){
				$hlclass = 'approvedHL';
				$timestamp_str = date('d/m/Y H:i',strtotime($leave['hr_app_timestamp']));
				$updated_by = "{$leave['hra_fname']} {$leave['hra_lname']}";
			}else if( is_numeric($leave['hr_app']) && $leave['hr_app']==0 ){
				$hlclass = 'pendingHL';
				$timestamp_str = date('d/m/Y H:i',strtotime($leave['hr_app_timestamp']));
				$updated_by = "{$leave['hra_fname']} {$leave['hra_lname']}";
			}
			?>
			<div style="float: left; margin-left: 13px;" class="<?php echo $hlclass; ?>"><?php echo "{$timestamp_str} {$updated_by}"; ?></div>
		</div>
		<div class="row">
			<label class="addlabel">Line Manager Approved</label>
			<div class="chk_div">
				<input type="radio"  class="addinput chkbox line_manager_app" name="line_manager_app" id="line_manager_app"  value="1" <?php echo ( is_numeric($leave['line_manager_app']) && $leave['line_manager_app']==1 )?'checked="checked"':''; ?> /> <span>Yes</span>
			</div>
			<div class="chk_div">
				<input type="radio"  class="addinput chkbox line_manager_app" name="line_manager_app" id="line_manager_app"  value="0" <?php echo ( is_numeric($leave['line_manager_app']) && $leave['line_manager_app']==0 )?'checked="checked"':''; ?> /> <span>No</span>
			</div>
			<?php 
			// timestamp
			$hlclass = '';
			$timestamp_str = '';
			$updated_by = '';
			if( is_numeric($leave['line_manager_app']) && $leave['line_manager_app']==1 ){
				$hlclass = 'approvedHL';
				$timestamp_str = date('d/m/Y H:i',strtotime($leave['line_manager_app_timestamp']));
				$updated_by = "{$leave['lma_fname']} {$leave['lma_lname']}";
			}else if( is_numeric($leave['line_manager_app']) && $leave['line_manager_app']==0 ){
				$hlclass = 'pendingHL';
				$timestamp_str = date('d/m/Y H:i',strtotime($leave['line_manager_app_timestamp']));
				$updated_by = "{$leave['lma_fname']} {$leave['lma_lname']}";
			}
			?>
			<div style="float: left; margin-left: 13px;" class="<?php echo $hlclass; ?>"><?php echo "{$timestamp_str} {$updated_by}"; ?></div>
		</div>		
		<div class="row">
			<label class="addlabel">Added to Calendar</label>
			<div class="chk_div">
				<input type="checkbox" style="height: auto;" class="addinput chkbox added_to_cal" name="added_to_cal" id="added_to_cal" value="1" <?php echo ( is_numeric($leave['added_to_cal']) && $leave['added_to_cal']==1 )?'checked="checked"':''; ?> /> <span>Yes</span>
				<input type="hidden" name="added_to_cal_changed" id="added_to_cal_changed" />
			</div>	
			<div class="chk_div" style="margin: 0 16px;">
				<a  target="_blank" href="/add_calendar_entry_static.php">
					<img src="/images/calendar.png" class="email_icon" style="width: 20px; cursor: pointer;" />
				</a>
			</div>
			<?php 
			// timestamp
			$hlclass = '';
			$timestamp_str = '';
			$updated_by = '';
			if( is_numeric($leave['added_to_cal']) && $leave['added_to_cal']==1 ){
				$hlclass = 'approvedHL';
				$timestamp_str = date('d/m/Y H:i',strtotime($leave['added_to_cal_timestamp']));
				$updated_by = "{$leave['atc_fname']} {$leave['atc_lname']}";
			}else{
				if($leave['added_to_cal_timestamp']!=""){
					$hlclass = 'pendingHL';
					$timestamp_str = date('d/m/Y H:i',strtotime($leave['added_to_cal_timestamp']));
					$updated_by = "{$leave['atc_fname']} {$leave['atc_lname']}";
				}				
			}
			?>
			<div style="float: left;" class="<?php echo $hlclass; ?>"><?php echo "{$timestamp_str} {$updated_by}"; ?></div>
		</div>		
		<div class="row">
			<label class="addlabel">Staff notified in writing</label>
			<div class="chk_div">
				<input type="checkbox" style="height: auto;" class="addinput chkbox staff_notified" name="staff_notified" id="staff_notified" value="1" <?php echo ( is_numeric($leave['staff_notified']) && $leave['staff_notified']==1 )?'checked="checked"':''; ?> /> <span>Yes</span>
				<input type="hidden" name="staff_notified_changed" id="staff_notified_changed" />
			</div>	
<?php
// MAIL TO
$mail_to_subject = 'Leave request';
$mail_to_cc = "{$leave['lm_email']}";
$employee_name = "{$leave['emp_fname']} {$leave['emp_lname']}";
$logged_user = "{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}";

$mailto_body = "Hi {$employee_name}

Your leave request has been approved!

First Day of leave: {$fdol}
Last day of Leave: {$ldol}
Number of Days: {$leave['num_of_days']}
Reason for leave: {$leave['reason_for_leave']}

Regards,

{$logged_user}
";
?>
			<div class="chk_div" style="margin: 0 16px;">
				<a href="mailto:<?php echo $leave['emp_email']; ?>?cc=<?php echo $mail_to_cc; ?>&Subject=<?php echo $mail_to_subject; ?>&body=<?php echo rawurlencode($mailto_body); ?>">
					<img src="/images/email_button_green.png" class="email_icon" style="width: 20px; cursor: pointer;" />
				</a>
			</div>
			<?php 
			// timestamp
			$hlclass = '';
			$timestamp_str = '';
			$updated_by = '';
			if( is_numeric($leave['staff_notified']) && $leave['staff_notified']==1 ){
				$hlclass = 'approvedHL';
				$timestamp_str = date('d/m/Y H:i',strtotime($leave['staff_notified_timestamp']));
				$updated_by = "{$leave['sn_fname']} {$leave['sn_lname']}";
			}else{
				if($leave['staff_notified_timestamp']!=""){
					$hlclass = 'pendingHL';
					$timestamp_str = date('d/m/Y H:i',strtotime($leave['staff_notified_timestamp']));
					$updated_by = "{$leave['sn_fname']} {$leave['sn_lname']}";					
				}				
			}
			?>
			<div style="float: left;" class="<?php echo $hlclass; ?>"><?php echo "{$timestamp_str} {$updated_by}"; ?></div>
		</div>
		<div class="row">
			<label class="addlabel">Comments</label>
			<textarea class="addtextarea comments" name="comments" id="comments"><?php echo $leave['comments']; ?></textarea>
		</div>
		
		<div class="row">
			<label class="addlabel">&nbsp;</label>
			<input type="hidden" name="leave_id" value="<?php echo $leave['leave_id']; ?>" />
        	<button class="submitbtnImg" id="btn_add_vehicle" type="submit" style="float: left; margin-top: 30px;">Update</button>
        </div>
	</div>
	</form>


    
  </div>

<br class="clearfloat" />



<script>


jQuery(document).ready(function(){

	// leave checkbox changed flag
	jQuery("#added_to_cal").change(function(){
		jQuery("#added_to_cal_changed").val(1);
	});
	
	jQuery("#staff_notified").change(function(){
		jQuery("#staff_notified_changed").val(1);
	});
	
	// form validation
	jQuery("#jform").submit(function(){
		
		var error = "";
		
		// Leave Request Form
		var date = jQuery("#date").val();
		var type_of_leave = jQuery("#type_of_leave").val();
		var lday_of_work = jQuery("#lday_of_work").val();
		var fday_back = jQuery("#fday_back").val();
		var num_of_days = jQuery("#num_of_days").val();
		var reason_for_leave = jQuery("#reason_for_leave").val();
		var backup_leave = jQuery("#backup_leave").val();
		
		// Office Use Only
		var line_manager = jQuery("#line_manager").val();
		
		//console.log(line_manager_app);
		
		
		// The Incident
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
			error += "Reason for Leave  is required\n";
		}
		
		// Office Use Only
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
