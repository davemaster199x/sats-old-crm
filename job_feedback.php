<?php
$title = "Job Feedback";

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



$from_date = ($_REQUEST['from_date']!='')?mysql_real_escape_string($_REQUEST['from_date']):date('01/m/Y');
if($from_date!=''){
	$from_date2 = $crm->formatDate($from_date);
}
$to_date = ($_REQUEST['to_date']!='')?mysql_real_escape_string($_REQUEST['to_date']):date('t/m/Y');
if($to_date!=''){
	$to_date2 = $crm->formatDate($to_date);
}

$show_all = mysql_real_escape_string($_REQUEST['show_all']);
//$sms_type = mysql_real_escape_string($_REQUEST['sms_type']);
$sms_type = 18; // SMS (Thank You)
$sent_by = mysql_real_escape_string($_REQUEST['sent_by']);
$tech = mysql_real_escape_string($_REQUEST['tech']);

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort={$sort}&order_by={$order_by}&from_date={$from_date}&to_date={$to_date}&sms_type={$sms_type}&sent_by={$sent_by}&tech={$tech}&show_all={$show_all}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;




// list
$cust_sel = "
sas.`sent_by`,
sas.`job_id`, 

sar.`sms_api_replies_id`, 
sar.`created_date` AS sar_created_date, 
sar.`mobile` AS sar_mobile, 
sar.`response`,
sar.`saved`, 
sar.`unread`, 

sat.`sms_api_type_id`, 

sa.`FirstName`, 
sa.`LastName`, 

sat.`type_name`, 
sat.`sms_api_type_id`, 

p.`property_id`,

ass_tech.`FirstName` AS t_fname, 
ass_tech.`LastName` AS t_lname
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
	'sms_type' => $sms_type,
	'sms_page' => 'incoming',
	'sent_by' => $sent_by,
	'tech' => $tech,
	'custom_select' => $cust_sel
);
$cr_sql = $crm->getSMSrepliesMergedData($list_params);


// pagination 
$list_params = array(
	'filterDate' => array(
		'from' => $from_date2,
		'to' => $to_date2
	),
	'sms_type' => $sms_type,
	'sms_page' => 'incoming',
	'sent_by' => $sent_by,
	'tech' => $tech,
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
	}else if($_GET['update_success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Update Successful</div>
	<?php	
	}
	?>
	
	
	<div class="aviw_drop-h" style="border: 1px solid #ccc;">
	
		<form id="form_search" method="post" action="<?php echo $current_page; ?>">			
			<div class="fl-left">
				<label style="margin-right: 9px;">From:</label>
				<input type="text" name="from_date" id="from_date" style="width: 85px;" class="addinput datepicker" value="<?php echo $from_date; ?>" />
			</div>
			
			<div class="fl-left">
				<label style="margin-right: 9px;">To:</label>
				<input type="text" name="to_date" id="to_date" style="width: 85px;" class="addinput datepicker" value="<?php echo $to_date; ?>" />
			</div>
			
			<!--
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
			-->
			
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
			
			
			<div class="fl-left">
				<label style="margin-right: 9px;">Technician:</label>
				<select name="tech">
					<option value="">--- Select ---</option>
					<?php
					// list
					$list_params = array(	
						'custom_select' => 'DISTINCT j.`assigned_tech`, ass_tech.`StaffID`, ass_tech.`FirstName`, ass_tech.`LastName`',
						'filterDate' => array(
							'from' => $from_date2,
							'to' => $to_date2
						),
						'echo_query' => 0,
						'sms_type' => $sms_type,
						'sms_page' => 'incoming'
					);
					$sql = $crm->getSMSrepliesMergedData($list_params);
					while($row = mysql_fetch_array($sql)){ ?>
						<option value="<?php echo $row['StaffID']; ?>" <?php echo ( $row['id'] == $tech )?'selected="selected"':''; ?>><?php echo $crm->formatStaffName($row['FirstName'],$row['LastName']); ?></option>
					<?php 
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

			<?php 
			//$cs_sql = $crm->getCrmSettings($country_id);
			//$cs = mysql_fetch_array($cs_sql);
			?>
			<!--
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
			-->
			
		</form>
		
		
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
				<th>Technician</th>				
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
						<td class="mob_td"><?php echo $mob_num = '0'.substr($cr['sar_mobile'],2); ?></td>	
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
						<td><?php echo $crm->formatStaffName($cr['t_fname'],$cr['t_lname']); ?></td>
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
