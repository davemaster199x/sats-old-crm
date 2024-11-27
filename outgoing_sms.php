<?php
$title = "Outgoing SMS";

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



$from_date = ($_REQUEST['from_date']!='')?mysql_real_escape_string($_REQUEST['from_date']):'';
if($from_date!=''){
	$from_date2 = $crm->formatDate($from_date);
}
$to_date = ($_REQUEST['to_date']!='')?mysql_real_escape_string($_REQUEST['to_date']):'';
if($to_date!=''){
	$to_date2 = $crm->formatDate($to_date);
}

$show_all = mysql_real_escape_string($_REQUEST['show_all']);
$unread = ($show_all==1)?'':1;
$sms_type = mysql_real_escape_string($_REQUEST['sms_type']);
$sent_by = mysql_real_escape_string($_REQUEST['sent_by']);
$cb_status = mysql_real_escape_string($_REQUEST['cb_status']);


// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort={$sort}&order_by={$order_by}&from_date={$from_date}&to_date={$to_date}&sms_type={$sms_type}&sent_by={$sent_by}&show_all={$show_all}&cb_status={$cb_status}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;




// list
$cust_sel = "
sas.`sms_api_sent_id`, 
sas.`sent_by`,sa.`FirstName`, 
sas.`job_id`, 
sas.`cb_status`,
sas.`created_date` AS sas_created_date,
sas.`mobile` AS sas_mobile,
sas.`message`,   

sat.`type_name`, 
sat.`sms_api_type_id`, 

p.`property_id`,

sa.`LastName`
";
if( $from_date2 != '' && $to_date2 != '' ){
	$cust_filt = "AND CAST(sas.`created_date` AS DATE) BETWEEN '{$from_date2}' AND '{$to_date2}'";
}


$list_params = array(
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'sort_list' => array(
		array(
			'order_by' => 'sas.`created_date`',
			'sort' => 'DESC'
		)
	),
	'echo_query' => 0,
	'sms_type' => $sms_type,
	'sms_page' => 'outgoing',
	'sent_by' => $sent_by,
	'cb_status' => $cb_status,
	'custom_select' => $cust_sel,
	'custom_filter' => $cust_filt
);
$cr_sql = $crm->getSMSrepliesMergedData($list_params);


// pagination 
$list_params = array(
	'sms_type' => $sms_type,
	'sms_page' => 'outgoing',
	'sent_by' => $sent_by,
	'cb_status' => $cb_status,
	'custom_select' => $cust_sel,
	'custom_filter' => $cust_filt
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
</style>

    
    <div id="mainContent">
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $current_page; ?>"><strong><?php echo $title; ?></strong></a></li>				
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
				<input type="text" name="from_date" id="from_date" style="width: 85px;" class="addinput datepicker" value="<?php echo ($_REQUEST['from_date']!='')?$from_date:date('d/m/Y',strtotime("-10 days")); ?>" />
			</div>
			
			<div class="fl-left">
				<label style="margin-right: 9px;">To:</label>
				<input type="text" name="to_date" id="to_date" style="width: 85px;" class="addinput datepicker" value="<?php echo($_REQUEST['to_date']!='')?$to_date:date('d/m/Y'); ?>" />
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
				<label style="margin-right: 9px;">Status</label>
				<select name="cb_status">
					<option value="">ALL</option>
					<option value="pending">Pending</option>
					<option value="delivered">Delivered</option>
					<option value="hard-bounce">Hard-bounce</option>
				</select>
			</div>
			
			
			
			<div class="fl-left" style="float:left; margin-left: 10px;">
				
				<button class="submitbtnImg" id="btn_submit" type="submit">
					<img class="inner_icon" src="images/search-button.png" />
					Search
				</button>
				
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
				<th>To</th>
				<th>Tenant</th>
				<th>Message</th>
				<th>Status</th>
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
						<td><?php echo date('d/m/Y',strtotime($cr['sas_created_date'])); ?></td>
						<td><?php echo date('H:i',strtotime($cr['sas_created_date'])); ?></td>
						<td class="mob_td"><?php echo $mob_num = '0'.substr($cr['sas_mobile'],3); ?></td>	
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
						<td><?php echo $cr['message']; ?></td>
						<td>
							<?php echo $cr['cb_status']; ?>
							<input type="hidden" class="job_id" value="<?php echo $cr['job_id']; ?>" />
							<input type="hidden" class="sas_id" value="<?php echo $cr['sms_api_sent_id']; ?>" />
							<input type="hidden" class="tenant_name" value="<?php echo $tenant_name; ?>" />
							<input type="hidden" class="reply_msg" value="<?php echo $cr['response']; ?>" />
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
</script>
</body>
</html>
