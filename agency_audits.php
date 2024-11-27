<?php

$title = "Agency Audits";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

// Initiate job class
$crm = new Sats_Crm_Class;

$page_url = 'agency_audits.php';
$country_id = $_SESSION['country_default'];


$logged_user = $_SESSION['USER_DETAILS']['StaffID'];

// use array incase they add more people
//$vip = array(2025); // sir dan
//$vip2 = array(2025,11);
//$vip = array(11); // ness

$date = mysql_real_escape_string($_REQUEST['date']);
$date2 = ($date!="")?$crm->formatDate($date):'';
$submitted_by = mysql_real_escape_string($_REQUEST['submitted_by']);
$ad_status = ($_REQUEST['ad_status'])?mysql_real_escape_string($_REQUEST['ad_status']):1;


// sort
$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'p.`postcode`';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort=".urlencode($sort)."&order_by=".urlencode($order_by)."&date=".urlencode($date)."&user=".urlencode($user)."&status=".urlencode($status);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$custom_select = "
	ad.`agency_audit_id`,
	ad.`date_created` AS ad_date_created,
	ad.`submitted_by`,
	ad.`comments` AS ad_comments,
	ad.`status` AS ad_status,
	ad.`completion_date`,

	a.`agency_id`,
	a.`agency_name`,
	
	sb.`StaffID` AS sb_staff_id,
	sb.`FirstName` AS sb_FirstName,
	sb.`LastName` AS sb_LastName,
	
	at.`StaffID` AS at_staff_id,
	at.`FirstName` AS at_FirstName,
	at.`LastName` AS at_LastName
";

$jparams = array(
	'custom_select' => $custom_select,
	'status' => $ad_status,
	'submitted_by' => $submitted_by,
	'active' => 1,
	'sort_list' => array(
		array(
			'order_by' => 'ad.`date_created`',
			'sort' => 'DESC'
		)
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'echo_query' => 0,
);
$plist = getAgencyAudits($jparams);

$jparams = array(
	'custom_select' => $custom_select,
	'status' => $ad_status,
	'submitted_by' => $submitted_by,
	'active' => 1,
	'echo_query' => 0,
);
$ptotal = mysql_num_rows(getAgencyAudits($jparams));




// get button_icons db data
function getAgencyAudits($params){
	

	// filters
	$filter_arr = array();
	
	if($params['active']!=""){
		$filter_arr[] = "AND ad.`active` = {$params['active']}";
	}
	
	if($params['status']!=""){
		$filter_arr[] = "AND ad.`status` = {$params['status']}";
	}
	
	if($params['agency_audits_id']!=""){
		$filter_arr[] = "AND ad.`agency_audits_id` = {$params['agency_audits_id']}";
	}
	
	if($params['date']!=""){
		$filter_arr[] = "AND CAST( ad.`date_created` AS Date ) = '{$params['date']}'";
	}

	if($params['submitted_by']!=""){
		$filter_arr[] = "AND ad.`submitted_by` = {$params['submitted_by']}";
	}
	
	
	// combine all filters
	if( count($filter_arr)>0 ){
		$filter_str = " WHERE ad.`agency_audit_id` > 0 ".implode(" ",$filter_arr);
	}
	

	//custom query
	if( $params['custom_filter']!='' ){
		$custom_filter_str = $params['custom_filter'];
	}
	
	if($params['custom_select']!=''){
		$sel_str = " {$params['custom_select']} ";
	}else if($params['return_count']==1){
		$sel_str = " COUNT(*) AS jcount ";
	}else if($params['distinct_sql']!=""){
		
		$sel_str = " DISTINCT {$params['distinct_sql']} ";	
		
	}else{
		$sel_str = " 
			*
		";
	}
	
	
	
	
	// sort
	if( $params['sort_list']!='' ){
		
		$sort_str_arr = array();
		foreach( $params['sort_list'] as $sort_arr ){
			if( $sort_arr['order_by']!="" && $sort_arr['sort']!='' ){
				$sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
			}
		}
		
		$sort_str_imp = implode(", ",$sort_str_arr);
		$sort_str = "ORDER BY {$sort_str_imp}";
		
	}		
	
	
	// GROUP BY
	if($params['group_by']!=''){
		$group_by_str = "GROUP BY {$params['group_by']}";
	}
	
	
	// paginate
	if($params['paginate']!=""){
		if(is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])){
			$pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
		}
	}
	

	$sql = "		
		SELECT {$sel_str}
		FROM `agency_audits` AS ad
		LEFT JOIN `agency` AS a ON ad.`agency_id` = a.`agency_id`
		LEFT JOIN `staff_accounts` AS sb ON ad.`submitted_by` = sb.`StaffID`
		LEFT JOIN `staff_accounts` AS at ON ad.`assigned_to` = at.`StaffID`
		{$custom_table_join}
		{$filter_str}	
		{$custom_filter_str}
		{$group_by_str}
		{$sort_str}
		{$pag_str}
		
	";		
	
	if($params['echo_query']==1){
		echo $sql;
	}
	
	if($params['return_count']==1){
		$j_sql = mysql_query($sql);
		$row = mysql_fetch_array($j_sql);
		return $row['jcount'];
	}else{
		return mysql_query($sql);
	}
	
	
	
}



function getStatusName($status){
	
	switch($status){
		case 1:
			$status_name = 'Pending';
		break;
		case 2:
			//$status_name = 'Declined';
		break;
		case 3:
			$status_name = 'In Progress';
		break;
		case 4:
			$status_name = 'Completed';
		break;
	}
	
	return $status_name;
	
}



$agency_list_arr = [];
$jparams = array(
	'status' => 'active',
	'country_id' => $country_id,
	'sort_list' => array(
		array(
			'order_by' => 'a.`agency_name`',
			'sort' => 'ASC'
		)
	),
	'display_echo' => 0,
);
$a_sql = $crm->getAgency($jparams); 
while( $a = mysql_fetch_array($a_sql) ){
	$agency_list_arr[] = array(
		'agency_id' => $a['agency_id'],
		'agency_name' => $a['agency_name']
	);
}


//print_r($agency_list_arr);



$staff_list_arr = [];
$jparams = array(
	'status' => 'active',
	'country_id' => $country_id,
	'sort_list' => array(
		'order_by' => 'sa.`FirstName`',
		'sort' => 'ASC'
	),
	'display_echo' => 1,
);
$sa_sql = $crm->getStaffAccount($jparams); 
while( $sa = mysql_fetch_array($sa_sql) ){
	$staff_list_arr[] = array(
		'StaffID' => $sa['StaffID'],
		'FirstName' => $sa['FirstName'],
		'LastName' => $sa['LastName']
	);
}


//print_r($staff_list_arr);


?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .action_div{
	display:none;
}
.yello_mark{
	background-color: #ffff9d;
}
.green_mark{
	background-color: #c2ffa7;
}
#add_task_div{
	display: none;
	margin: 60px 0;
}
.response{	
	margin: 0; 
	height: 70px;
	padding: 8px;
}
.response_div{
	display: none;
}
</style>




<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo "<div class='success'>Submission Successful</div>";
		}

		
		if($_GET['update_success']==1){
			echo "<div class='success'>Update Successful</div>";
		}
		
		if($_GET['delete_success']==1){
			echo "<div class='success'>Delete Successful</div>";
		}
		
		if($_GET['response_success']==1){
			echo "<div class='success'>Response Sent</div>";
		}
		

			
		// no sort yet
		if($_REQUEST['sort']==""){
			$sort_arrow = 'up';
		}
		
		?>
		
		
		<form method="POST" name='example' id='example'>
			

			<table border=1 cellpadding=0 cellspacing=0 width="100%">
				<tr class="tbl-view-prop">
				<td>

				<div class="aviw_drop-h aviw_drop-vp" id="view-jobs">
				
										
					
					<div class='fl-left'>
						<label>Submitted By:</label>
						<select name="submitted_by">
						<option value=''>--- Select ---</option>
						<?php				
						$jparams = array(							
							'distinct_sql' => 'ad.`submitted_by`, sb.`FirstName`, sb.`LastName`',
							'status' => $ad_status,
							'active' => 1,
							'sort_list' => array(
								array(
									'order_by' => 'sb.`FirstName`',
									'sort' => 'DESC'
								)
							),
							'echo_query' => 1
						);
						$sb_sql = getAgencyAudits($jparams);
						while( $sb = mysql_fetch_array($sb_sql) ){ ?>
							<option value="<?php echo $sb['submitted_by']; ?>" <?php echo ($sb['submitted_by']==$submitted_by)?'selected="selected"':''; ?>><?php echo $crm->formatStaffName($sb['FirstName'],$sb['LastName']); ?></option>
						<?php	
						}
						
						?>
						</select>
					</div>
					
					
					<div class='fl-left'>
						<label>Status:</label>
						<select name="ad_status">
							<option value="1" <?php echo ($status == 1)?'selected="selected"':''; ?>>Pending</option>
							<!--<option value="2" <?php echo ($status == 2)?'selected="selected"':''; ?>>Declined</option>-->
							<option value="3" <?php echo ($status == 3)?'selected="selected"':''; ?>>In Progress</option>
							<option value="4" <?php echo ($status == 4)?'selected="selected"':''; ?>>Completed</option>
						</select>
					</div>
					
					
					<div class='fl-left' style="float:left;">				
						<button type='submit' class='submitbtnImg' id="btn_search">
							<img class="inner_icon" src="images/button_icons/search-button.png">
							Search
						</button>
					</div>
	
	
				</td>
				</tr>
			</table>	  
				  
			</form>

		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
			
			<tr class="toprow jalign_left">
				<th>Date Submitted</th>
				<th>Agency Name</th>
				<th>Submitted By</th>
				<th>Assigned To</th>
				<th>Comments</th>
				<th>Status</th>
				<th>Target Completion Date</th>
				<th>Edit</th>
			</tr>
			<?php	
			$i= 0;
			if(mysql_num_rows($plist)>0){
				while($row = mysql_fetch_array($plist)){
					
				// grey alternation color
				$row_color = ($i%2==0)?"":"style='background-color:#eeeeee;'";
	
				// data
				$ad_date_created = ( $crm->isDateNotEmpty($row['ad_date_created']) )?date('d/m/Y',strtotime($row['ad_date_created'])):'';
				$ad_comp_date = ( $crm->isDateNotEmpty($row['completion_date']) )?date('d/m/Y',strtotime($row['completion_date'])):'';
				$agency_name = $row['agency_name'];
				$submitted_by = $crm->formatStaffName($row['sb_FirstName'],$row['sb_LastName']);
				$assigned_to = $crm->formatStaffName($row['at_FirstName'],$row['at_LastName']);
				$ad_comments = $row['ad_comments'];
				
				$response = $row['response'];
				$status_name = getStatusName($row['ad_status']);

				
			?>
					<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
						
						<td>
							<span class="txt_lbl"><?php echo $ad_date_created; ?></span>
							<span class="txt_hid"><?php echo $ad_date_created; ?></span>
						</td>	
						<td>
							<span class="txt_lbl"><?php echo $row['agency_name']; ?></span>
							<span class="txt_hid">
								<select class="agency_id" style="width: 215px;">
									<option value="">---</option>	
									<?php
									foreach( $agency_list_arr as $a ){ ?>
										<option value="<?php echo $a['agency_id']; ?>" <?php echo ( $a['agency_id'] == $row['agency_id'] )?'selected="selected"':''; ?>><?php echo $a['agency_name']; ?></option>
									<?php
									}
									?>
								</select>
							</span>
						</td>
						<td>
							<span class="txt_lbl"><?php echo $submitted_by; ?></span>
							<span class="txt_hid"><?php echo $submitted_by; ?></span>
						</td>
						<td>
							<span class="txt_lbl"><?php echo $assigned_to; ?></span>
							<span class="txt_hid">
								<select class="assigned_to">
									<option value="">---</option>	
									<?php
									foreach( $staff_list_arr as $sa ){ ?>
										<option value="<?php echo $sa['StaffID']; ?>" <?php echo ( $sa['StaffID'] == $row['at_staff_id'] )?'selected="selected"':''; ?>><?php echo $crm->formatStaffName($sa['FirstName'],$sa['LastName']); ?></option>
									<?php
									}
									?>
								</select>
							</span>
						</td>
						<td>
							<span class="txt_lbl"><?php echo $ad_comments; ?></span>
							<textarea class="addtextarea txt_hid ad_comments" style="height: auto; margin: 0;"><?php echo $ad_comments; ?></textarea>
						</td>						
						<td>
							<span class="txt_lbl"><?php echo $status_name; ?></span>
							<span class="txt_hid">
								<select class="ad_status">		
									<option value="1" <?php echo ($row['ad_status'] == 1)?'selected="selected"':''; ?>>Pending</option>
									<!--<option value="2" <?php echo ($row['ad_status'] == 2)?'selected="selected"':''; ?>>Declined</option>-->
									<option value="3" <?php echo ($row['ad_status'] == 3)?'selected="selected"':''; ?>>In Progress</option>
									<option value="4" <?php echo ($row['ad_status'] == 4)?'selected="selected"':''; ?>>Completed</option>
								</select>
							</span>
						</td>
						<td>
							<span class="txt_lbl"><?php echo $ad_comp_date; ?></span>
							<span class="txt_hid">
								<input type='text' class="ad_comp_date datepicker" />
							</span>
						</td>
						<td>
							<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
							<div class="action_div">
								<button class="blue-btn submitbtnImg btn_update">
									<img class="inner_icon" src="images/button_icons/save-button.png">
									Update
								</button>	
								<button class="blue-btn submitbtnImg btn_delete">
									<img class="inner_icon" src="images/button_icons/cancel-button.png">
									Delete
								</button>								
								<button class="submitbtnImg btn_cancel">
									<img class="inner_icon" src="images/button_icons/back-to-tech.png">
									Cancel
								</button>

								<?php
								if ( in_array($logged_user, $vip2) && $response == '' ){ ?>
									<button class="blue-btn submitbtnImg btn_response" style="margin: 5px 0;">
										<img class="inner_icon" src="images/button_icons/email.png">
										<span class="inner_icon_txt">Response</span>
									</button>
								<?php	
								}
								?>							
								
							
								<div class="response_div">
									<textarea class="addtextarea response"></textarea><br />
									<button class="blue-btn submitbtnImg btn_response_send" style="margin: 5px 0;">
										<img class="inner_icon" src="images/button_icons/email.png">
										<span class="inner_icon_txt">Send</span>
									</button>
								</div>
								
								
								<input type="hidden" class="au_id" value="<?php echo $row['agency_audit_id']; ?>" />
							</div>							
						</td>	
						
					</tr>
					
			<?php
				$i++;
				}
			}else{ ?>
				<td colspan="12" align="left">Empty</td>
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

	<button type='button' class='jfloatleft submitbtnImg blue-btn' id="add_task_btn">
		<img class="inner_icon" src="images/button_icons/add-button.png">
		<span class="inner_icon_span">List</span>
	</button>
	
	<div id="add_task_div" class="addproperty formholder">
		<form id="add_task_form" method="post" action="/add_agency_audit.php" enctype="multipart/form-data">
			
			<div class="row">
				<label class="addlabel" for="title">Agency</label>
				<select id="agency_id" name="agency_id">
					<option value="">---</option>	
					<?php
					foreach( $agency_list_arr as $a ){ ?>
						<option value="<?php echo $a['agency_id']; ?>"><?php echo $a['agency_name']; ?></option>
					<?php
					}
					?>
				</select>
			</div>
			<div class="row">
				<label class="addlabel" for="title">Comments</label>
				<textarea class="addtextarea" name="comments" id="comments" style="height: 150px;"></textarea>
			</div>
			<div class="row">
				<label class="addlabel" for="title">Added By:</label>
				<select name="added_by">
					<option value="">---</option>	
					<?php
					foreach( $staff_list_arr as $sa ){ ?>
						<option value="<?php echo $sa['StaffID']; ?>" <?php echo ( $sa['StaffID'] == $logged_user )?'selected="selected"':''; ?>><?php echo $crm->formatStaffName($sa['FirstName'],$sa['LastName']); ?></option>
					<?php
					}
					?>
				</select>
			</div>

			<div style="padding-top: 15px; text-align:left;" class="row clear">
				<button type="submit" class="submitbtnImg" id="btn_save">
					<img class="inner_icon" src="images/button_icons/save-button.png">
					Save
				</button>
			 </div>
		</form>
	</div>
		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	
	// invoke fancybox
	jQuery('.fancybox').fancybox();
	
	
	
	/*
	// multiple screenshot
	jQuery("#btn_add_file").click(function(){
		
		var last_photo_elem = jQuery(".screenshot_file_div:last");
		var photo_elem = last_photo_elem.clone();
		photo_elem.find(".screenshot").val("");
		last_photo_elem.after(photo_elem);
		
	});
	*/
	
	
	// save response
	jQuery(".btn_response_send").click(function(){
	
		var ct_id = jQuery(this).parents("tr:first").find(".ct_id").val();
		var page_link = jQuery(this).parents("tr:first").find(".page_link").val();
		var describe_issue = jQuery(this).parents("tr:first").find(".describe_issue").val();
		var response = jQuery(this).parents("tr:first").find(".response").val();
		var email = jQuery(this).parents("tr:first").find(".email").val();
		var error = "";
		
		if( response == "" ){
			error += "Response is required";
		}
		
		if(error != ""){
			alert(error);
		}else{			
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_crm_task_response.php",
				data: { 
					ct_id: ct_id,
					page_link: page_link,
					describe_issue: describe_issue,
					response: response,
					email: email
				}
			}).done(function( ret ) {
				window.location="crm_tasks.php?response_success=1";
			});	
						
		}		
		
	});
	
	
	// response toggle
	jQuery(".btn_response").click(function(){
		
		var btn_txt = jQuery(this).find(".inner_icon_txt").html();
		var orig_btn_txt = 'Response';
		var orig_btn_icon = 'images/button_icons/email.png';
		var cancel_btn_icon = 'images/button_icons/cancel-button.png';
		
		if( btn_txt == orig_btn_txt ){
			jQuery(this).removeClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html('Cancel');
			jQuery(this).find(".inner_icon").attr("src",cancel_btn_icon)
			jQuery(this).parents("tr:first").find(".response_div").show();
		}else{
			jQuery(this).addClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html(orig_btn_txt);
			jQuery(this).find(".inner_icon").attr("src",orig_btn_icon)
			jQuery(this).parents("tr:first").find(".response_div").hide();
		}
		
		
	});
	
	
	// update
	jQuery(".btn_update").click(function(){
	
		var au_id = jQuery(this).parents("tr:first").find(".au_id").val();
		var agency_id = jQuery(this).parents("tr:first").find(".agency_id").val();
		var assigned_to = jQuery(this).parents("tr:first").find(".assigned_to").val();
		var ad_comments = jQuery(this).parents("tr:first").find(".ad_comments").val();
		var ad_status = jQuery(this).parents("tr:first").find(".ad_status").val();
		var ad_comp_date = jQuery(this).parents("tr:first").find(".ad_comp_date").val();
		var error = "";
		
		/*
		if( describe_issue == "" ){
			error += "Describe Page is required";
		}
		*/
		
		if(error != ""){
			alert(error);
		}else{			
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_agency_audit.php",
				data: { 
					au_id: au_id,
					agency_id: agency_id,
					assigned_to: assigned_to,
					ad_comments: ad_comments,
					ad_status: ad_status,
					ad_comp_date: ad_comp_date
				}
			}).done(function( ret ) {
				window.location="<?php echo $page_url; ?>?update_success=1";
			});	
						
		}		
		
	});
	
	
	// delete script
	jQuery(".btn_delete").click(function(){
	
		var au_id = jQuery(this).parents("tr:first").find(".au_id").val();
	
		if(confirm("Are you sure you want to delete?")){			
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_agency_audit.php",
				data: { 
					au_id: au_id
				}
			}).done(function( ret ){
				window.location="<?php echo $page_url; ?>?delete_success=1";
			});						
			
		}
	});
	
	// inline edit toggle
	jQuery(".btn_edit").click(function(){
		
		var btn_txt = jQuery(this).html();
		
		jQuery(this).hide();
		
		if( btn_txt == 'Edit' ){			
			jQuery(this).parents("tr:first").find(".action_div").show();
			jQuery(this).parents("tr:first").find(".txt_hid").show();
			jQuery(this).parents("tr:first").find(".txt_lbl").hide();
		}else{
			jQuery(this).parents("tr:first").find(".action_div").hide();
		}
				
	});
	
	
	// cancel
	jQuery(".btn_cancel").click(function(){
		jQuery(this).parents("tr:first").find(".action_div").hide();
		jQuery(this).parents("tr:first").find(".btn_edit").show();		
	});
	
	
	// add task toggle
	jQuery("#add_task_btn").click(function(){
		
		
		
		var btn_txt = jQuery(this).find(".inner_icon_span").html();
		var default_btn_txt = 'List';
		var add_icon_src ='images/button_icons/add-button.png';
		var cancel_icon_src = 'images/button_icons/cancel-button.png';
		
		if( btn_txt == default_btn_txt ){
			jQuery("#bulk_payment_details_div").show();	
			jQuery(this).find(".inner_icon_span").html("Cancel");
			jQuery(this).find(".inner_icon").attr("src",cancel_icon_src);
			jQuery("#add_task_div").show();
		}else{
			jQuery("#bulk_payment_details_div").hide();	
			jQuery(this).find(".inner_icon_span").html(default_btn_txt);
			jQuery(this).find(".inner_icon").attr("src",add_icon_src);
			jQuery("#add_task_div").hide();
		}
		
	});
	
	
	// validation
	jQuery("#add_task_form").submit(function(){
	
		var agency_id = jQuery("#agency_id").val();
		var describe_issue = jQuery("#describe_issue").val();
		var error = "";
		
		if(agency_id==""){
			error += "Agency is required\n";
		}
		
		/*
		if(describe_issue==""){
			error += "Describe Issue is required\n";
		}
		*/

		if( error != "" ){
			alert(error);
			return false
		}else{
			return true;
		}
		
	});
	
	
});
</script>
</body>
</html>