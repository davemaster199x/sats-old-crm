<?php
$title = "Incoming SMS";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');
//include('inc/ws_sms_class.php');

$crm = new Sats_Crm_Class;
//$crm->displaySession();

$current_page = $_SERVER['PHP_SELF'];

$user_type = $_SESSION['USER_DETAILS']['ClassID'];
$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$loggedin_staff_name = "{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}";
$country_id = $_SESSION['country_default'];



$from_date = ($_POST['from_date']!='')?mysql_real_escape_string($_POST['from_date']):'';
if($from_date!=''){
	$from_date2 = $crm->formatDate($from_date);
}
$to_date = ($_POST['to_date']!='')?mysql_real_escape_string($_POST['to_date']):'';
if($to_date!=''){
	$to_date2 = $crm->formatDate($to_date);
}

$show_all = mysql_real_escape_string($_REQUEST['show_all']);
$unread = ($show_all==1)?'':1;
$sms_type = mysql_real_escape_string($_REQUEST['sms_type']);
$sent_by = mysql_real_escape_string($_REQUEST['sent_by']);


// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort={$sort}&order_by={$order_by}&from_date={$from_date}&to_date={$to_date}&sms_type={$sms_type}&sent_by={$sent_by}&show_all={$show_all}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;




// list
$cust_sel = "
sas.`sms_api_sent_id`, 
sas.`sent_by`, 
sas.`sms_type`,  
sas.`job_id`,

sar.`sms_api_replies_id`, 
sar.`message_id`, 
sar.`created_date` AS sar_created_date, 
sar.`mobile` AS sar_mobile, 
sar.`response`, 
sar.`saved`, 
sar.`unread`,

sa.`FirstName`, 
sa.`LastName`,

sat.`type_name`, 
sat.`sms_api_type_id`, 

p.`property_id`
";

$list_params = array(
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'sort_list' => array(
		array(
			'order_by' => 'sar.`datetime_entry`',
			'sort' => 'DESC'
		)
	),
	'filterDate' => array(
		'from' => $from_date2,
		'to' => $to_date2
	),
	'echo_query' => 0,
	'unread' => $unread,
	'sms_type' => $sms_type,
	'sms_page' => 'incoming',
	'sent_by' => $sent_by,
	'custom_select' => $cust_sel
);
$cr_sql = $crm->getSMSrepliesMergedData($list_params);


// pagination 
$list_params = array(
	'filterDate' => array(
		'from' => $from_date2,
		'to' => $to_date2
	),
	'unread' => $unread,
	'sms_type' => $sms_type,
	'sms_page' => 'incoming',
	'sent_by' => $sent_by,
	'custom_select' => $cust_sel
);
$ptotal = mysql_num_rows($crm->getSMSrepliesMergedData($list_params));


$ws_sms = new WS_SMS($country_id);	

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
.inner_icon{
	position: relative;
	top: 2px;
	margin-right: 3px;
}
.jfadeIt{
	opacity: 0.5;
}
#expense_tbl .submitbtnImg{
	margin-bottom: 3px;
}
</style>

	
    
    <div id="mainContent">
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong><?php echo $title; ?></strong></a></li>				
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
				<input type="text" name="from_date" id="from_date" style="width: 85px;" class="addinput datepicker" value="<?php echo ($_POST['from_date']!='')?$from_date:date('d/m/Y',strtotime("-10 days")); ?>" />
			</div>
			
			<div class="fl-left">
				<label style="margin-right: 9px;">To:</label>
				<input type="text" name="to_date" id="to_date" style="width: 85px;" class="addinput datepicker" value="<?php echo($_POST['to_date']!='')?$to_date:date('d/m/Y'); ?>" />
			</div>
			
			<div class="fl-left">
				<label style="margin-right: 9px;">SMS Type:</label>
				<select name="sms_type">
					<option value="" style="color:red;">ALL</option>	
					<?php
					$sms_type_sql2 = $ws_sms->getSMStype();
					while($sms_type2 = mysql_fetch_array($sms_type_sql2)){ ?>
						<option value="<?php echo $sms_type2['sms_api_type_id']; ?>" <?php echo ( $sms_type2['sms_api_type_id'] == $sms_type )?'selected="selected"':''; ?>><?php echo $sms_type2['type_name']; ?></option>
					<?php 
					}
					?>
				</select>
			</div>
			
			<div class="fl-left">
				<label style="margin-right: 9px;">Sent By:</label>
				<select name="sent_by">
					<option value="" style="color:red;">ALL</option>
					<?php
					// list
					$list_params = array(	
						'distinct' => 'sas.`sent_by`',
						'filterDate' => array(
							'from' => $from_date2,
							'to' => $to_date2
						),
						'echo_query' => 0,
						'unread' => $unread,
						'sms_type' => $sms_type,
						'sms_page' => 'incoming'
					);
					$sql = $crm->getSMSrepliesMergedData($list_params);
					while($row = mysql_fetch_array($sql)){ 
						if($row['StaffID']!=''){
						?>
							<option value="<?php echo $row['StaffID']; ?>" <?php echo ( $row['StaffID'] == $sent_by )?'selected="selected"':''; ?>><?php echo $crm->formatStaffName($row['FirstName'],$row['LastName']); ?></option>
						<?php 
						}
					}
					?>										
				</select>
			</div>
			
			<div class="fl-left" style="float:left; margin-left: 10px;">
				
				<button class="submitbtnImg" id="btn_submit" type="submit">
					<img class="inner_icon" src="images/search-button.png" />
					Search
				</button>
				
			</div>	
			
			<div class="fl-left" style="margin-top: 6px; margin-left: 20px;">
				<input type="checkbox" style="width: auto!important;" name="show_all" id="sms_replies_chk_main" <?php echo ($show_all==1)?'checked="checked"':'' ?> value="1" /> 
				<span id="sms_chk_hdr"><?php echo ($show_all==1)?'Display Unread Only':'Display ALL' ?></span>
			</div>
			
			<?php 
			$cs_sql = $crm->getCrmSettings($country_id);
			$cs = mysql_fetch_array($cs_sql);
			?>
			<div class="fl-left">
				<label style="margin-right: 9px;">
					<span style="color:red;"><?php echo $cs['sms_credit']; ?></span> 
					SMS Credits - Last Updated: <span class="timestampTextColor"><?php echo date('d/m/Y H:i',strtotime($cs['sms_credit_update_ts'])); ?></span> 
				</label>	
				<button type="button" class="submitbtnImg blue-btn" id="btn_check_credit">
					<img class="inner_icon" src="images/rebook.png">
					Check Credit
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
				<th>Sent By</th>
				<th>SMS Type</th>
				<th>Date</th>
				<th>Time</th>
				<th>From</th>
				<th>Tenant</th>
				<th>Message</th>
				<th>STR</th>
				<th></th>
				<th>Unread</th>			
			</tr>
			<?php				
			if( mysql_num_rows($cr_sql)>0 ){
				$i = 0;
				while($cr = mysql_fetch_array($cr_sql)){ 

				// -3 is CRON
				$sent_by_name = ( $cr['sent_by'] == -3 )?"CRM":"{$cr['FirstName']} {$cr['LastName']}";

				?>
					<tr class="body_tr jalign_left"  <?php echo ($i%2==0)?'':'style="background-color:#eeeeee"'; ?>>						
						<td><?php echo $sent_by_name; ?></td>
						<td><?php echo $cr['type_name']; ?></td>
						<td><?php echo date('d/m/Y',strtotime($cr['sar_created_date'])); ?></td>
						<td><?php echo date('H:i',strtotime($cr['sar_created_date'])); ?></td>
						<td class="mob_td"><?php echo $mob_num = '0'.substr($cr['sar_mobile'],2);	?></td>	
						<td>
						<?php
						$tenant_name = '';

						$pt_params = array( 
							'property_id' => $cr['property_id'],
							'active' => 1
						 );
						$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
						
						while( $pt_row = mysql_fetch_array($pt_sql) ){
							
							$tenants_num = str_replace(' ', '', trim($pt_row["tenant_mobile"]));							
							if( $tenants_num != '' && $tenants_num == $mob_num ){
								$tenant_name = $pt_row["tenant_firstname"];
							}
							
						}
						
						echo $tenant_name;
						?>
						</td>
						<td><?php echo $cr['response']; ?></td>
						<td>
							<button type="button" class="submitbtnImg blue-btn btn_show_str">Show STR</button>
							<span class="display_str_div"></span>
						</td>
						<td>
							<?php 
							// SMS (Thank You) or SMS Reply (Booking Confirmed) or SMS (Reminder)
							if ( $cr['sms_api_type_id']==18 || $cr['sms_api_type_id']==16 || $cr['sms_api_type_id']==19 ){ 
							?>
								<button type="button" class="submitbtnImg btn_function btn_process" data-btn_type='process'>Process</button>
							<?php
							}	
							// No Answer (Yes/No SMS Reply)
							//if ( $cr['sms_api_type_id']==2 ){ ?>
							<a href="view_job_details.php?id=<?php echo $cr['job_id']; ?>" target="__blank"  class="insert_n_open_job_link">
								<button type="button" class="submitbtnImg btn_function btn_insert_n_open_job" data-btn_type='insert_n_open_ob'>Insert & Open Job</button>
							</a>
							<?php
							//}
							?>	
							<?php
							// SMS block
							if( $cr['saved']==1 ){ // if sms already sent today
								$disabled_txt = 'disabled="disabled"';
								$add_class = 'jfadeIt';
							}else{
								$disabled_txt = '';
								$add_class = '';
							}
							?>
							<!--<button type="button" <?php echo $disabled_txt; ?> class="submitbtnImg <?php echo $add_class; ?> btn_function btn_save" data-btn_type='save'>Save</button>-->
						</td>
						<td style="border-right: 1px solid #cccccc !important;">
							<input type="checkbox" class="sms_replies_chk" <?php echo ($cr['unread']==1)?'checked="checked"':''; ?> />
							<input type="hidden" class="job_id" value="<?php echo $cr['job_id']; ?>" />
							<input type="hidden" class="message_id" value="<?php echo $cr['message_id']; ?>" />
							<input type="hidden" class="sas_id" value="<?php echo $cr['sms_api_sent_id']; ?>" />
							<input type="hidden" class="sar_id" value="<?php echo $cr['sms_api_replies_id']; ?>" />
							<input type="hidden" class="tenant_name" value="<?php echo $tenant_name; ?>" />
							<input type="hidden" class="reply_msg" value="<?php echo $cr['response']; ?>" />
							<input type="hidden" class="sms_type" value="<?php echo $cr['sms_type']; ?>" />							
						</td>
					</tr>
				<?php
				$i++;
				}
				?>
			

				
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
		
		

		
		
    
  </div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	
	// process button
	jQuery(".btn_show_str").click(function(){
		
		var obj = jQuery(this);
		var job_id = obj.parents("tr:first").find(".job_id").val();
		
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_get_job_future_str.php",
			data: { 
				job_id: job_id,
				country_id: <?php echo $country_id; ?>
			}
		}).done(function( ret ){			
			obj.hide();
			obj.parents("tr:first").find(".display_str_div").html(ret);
			jQuery("#load-screen").hide();
		});
		
	});
	
	
	
	// update credit via requesting SMS API
	jQuery("#btn_check_credit").click(function(){
		
		if( confirm("This will update to the latest SMS credit? Are you sure you want to continue?") ){
				
			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "ajax_fetch_sms_credit.php",
				data: { 
					country_id: <?php echo $country_id; ?>
				}
			}).done(function( ret ){
				jQuery("#load-screen").hide();
				location.reload();
			});
			
		}
		
	});
	
	
	
	
	// process button
	jQuery(".btn_save").click(function(){
		
		var obj = jQuery(this);
		var job_id = obj.parents("tr:first").find(".job_id").val();
		var sar_id = obj.parents("tr:first").find(".sar_id").val();
		var tenant_name = obj.parents("tr:first").find(".tenant_name").val();
		var reply_msg = obj.parents("tr:first").find(".reply_msg").val();
		var btn_type = obj.attr("data-btn_type");
		
		//console.log("button type: "+btn_type);
		
		if( confirm("Are you sure you want to continue?") ){
				
			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "ajax_process_sms_api_replies.php",
				data: { 
					job_id: job_id,
					sar_id: sar_id,
					tenant_name: tenant_name,
					reply_msg: reply_msg,
					btn_used: 'save'
				}
			}).done(function( ret ){
				jQuery("#load-screen").hide();
				//window.location="/incoming_sms.php";
				location.reload();
			});
			
		}
		
	});
	
	
	
	jQuery(".insert_n_open_job_link").click(function(){
		
		var obj = jQuery(this);
		var job_id = obj.parents("tr:first").find(".job_id").val();
		var sar_id = obj.parents("tr:first").find(".sar_id").val();
		var sas_id = obj.parents("tr:first").find(".sas_id").val();
		var message_id = obj.parents("tr:first").find(".message_id").val();
		var tenant_name = obj.parents("tr:first").find(".tenant_name").val();
		var reply_msg = obj.parents("tr:first").find(".reply_msg").val();
		var sms_type = obj.parents("tr:first").find(".sms_type").val();
		
		var btn_type = obj.attr("data-btn_type");
		
		if( confirm("Are you sure you want to continue?") ){
							
			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "ajax_process_sms_api_replies.php",
				data: { 
					job_id: job_id,
					sar_id: sar_id,
					sas_id: sas_id,
					message_id: message_id,
					tenant_name: tenant_name,
					reply_msg: reply_msg,
					sms_type: sms_type
				}
			}).done(function( ret ){
				jQuery("#load-screen").hide();
				//window.location="/incoming_sms.php";
				//location.reload();	
				//return true;
				//location.reload();
			});
		
			
			// disable button to prevent double processing
			obj.parents("tr:first").find(".btn_insert_n_open_job").addClass('fadeIt');
			obj.parents("tr:first").find(".btn_insert_n_open_job").attr('disabled','disabled');
			obj.parents("tr:first").find(".btn_process").addClass('fadeIt');
			obj.parents("tr:first").find(".btn_process").attr('disabled','disabled');
			
		}else{
			return false;
		}
		
	});
	
	
	// process button
	jQuery(".btn_process").click(function(){
		
		var obj = jQuery(this);
		var job_id = obj.parents("tr:first").find(".job_id").val();
		var sar_id = obj.parents("tr:first").find(".sar_id").val();
		var sas_id = obj.parents("tr:first").find(".sas_id").val();
		var message_id = obj.parents("tr:first").find(".message_id").val();
		var tenant_name = obj.parents("tr:first").find(".tenant_name").val();
		var reply_msg = obj.parents("tr:first").find(".reply_msg").val();		
		var btn_type = obj.attr("data-btn_type");
		var sms_type = obj.parents("tr:first").find(".sms_type").val();
		
		//console.log("button type: "+btn_type);
		
		if( confirm("Are you sure you want to continue?") ){
						
			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "ajax_process_sms_api_replies.php",
				data: { 
					job_id: job_id,
					sar_id: sar_id,
					sas_id: sas_id,
					message_id: message_id,
					tenant_name: tenant_name,
					reply_msg: reply_msg,
					sms_type: sms_type
				}
			}).done(function( ret ){
				jQuery("#load-screen").hide();
				//window.location="/incoming_sms.php";
				//location.reload();
			});			
			
			// disable button to prevent double processing
			obj.parents("tr:first").find(".btn_insert_n_open_job").addClass('fadeIt');
			obj.parents("tr:first").find(".btn_insert_n_open_job").attr('disabled','disabled');
			obj.parents("tr:first").find(".btn_process").addClass('fadeIt');
			obj.parents("tr:first").find(".btn_process").attr('disabled','disabled');
			
		}
		
	});
	
	
	jQuery("#sms_replies_chk_main").change(function(){
	
		var obj = jQuery(this);
		var chk_state = obj.prop("checked");
		
		if( chk_state == true ){
			jQuery("#sms_chk_hdr").html("Display Unread Only");
			var url = "/incoming_sms.php?show_all=1";
		}else{
			jQuery("#sms_chk_hdr").html("Display ALL");
			var url = "/incoming_sms.php";
		}
		
		window.location = url;
		
	});
	


	jQuery(".sms_replies_chk").change(function(){
		
		var obj = jQuery(this);
		var chk_state = obj.prop("checked");
		var sar_id = obj.parents("tr:first").find(".sar_id").val();
		
		if(chk_state==true){
			var unread = 1;
		}else{
			var unread = 0;
		}

		if( confirm("Are you sure you want to continue?") ){
				
			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "ajax_toggle_sms_replies.php",
				data: { 
					sar_id: sar_id,
					unread: unread
				}
			}).done(function( ret ){
				jQuery("#load-screen").hide();
				//window.location="/incoming_sms.php";
				location.reload();
			});
			
		}
		
	});
	
});
</script>
</body>
</html>
