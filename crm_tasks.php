<?php

$title = "CRM tasks";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

// Initiate job class
$crm = new Sats_Crm_Class;

$logged_user = $_SESSION['USER_DETAILS']['StaffID'];

// use array incase they add more people
$vip = array(2025); // sir dan
$vip2 = array(2025,11);
//$vip = array(11); // ness

$date = mysql_real_escape_string($_REQUEST['date']);
$date2 = ($date!="")?$crm->formatDate($date):'';
$user = mysql_real_escape_string($_REQUEST['user']);
$status = ($_REQUEST['status'])?mysql_real_escape_string($_REQUEST['status']):1;


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


$jparams = array(
	'active' => 1,
	'date' => $date2,
	'user' => $user,
	'status' => $status,
	'sort_list' => array(
		array(
			'order_by' => 'ct.`date_created`',
			'sort' => 'DESC'
		)
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'echo_query' => 0,
);
$plist = getCrmTasks($jparams);

$jparams = array(
	'active' => 1,
	'date' => $date2,
	'user' => $user,
	'status' => $status,
	'return_count' => 1
);
$ptotal = getCrmTasks($jparams);




// get button_icons db data
function getCrmTasks($params){
	

	// filters
	$filter_arr = array();
	
	if($params['active']!=""){
		$filter_arr[] = "AND ct.`active` = {$params['active']}";
	}
	
	if($params['ct_id']!=""){
		$filter_arr[] = "AND ct.`crm_task_id` = {$params['ct_id']}";
	}
	
	if($params['date']!=""){
		$filter_arr[] = "AND CAST( ct.`date_created` AS Date ) = '{$params['date']}'";
	}

	if($params['user']!=""){
		$filter_arr[] = "AND ct.`requested_by` = {$params['user']}";
	}
	
	if($params['status']!=""){
		$filter_arr[] = "AND ct.`status` = {$params['status']}";
	}
	
	/*	
	if($params['filterDate']!=''){
		if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
			$filter_arr[] = "AND CAST(sar.`created_date` AS DATE) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
		}			
	}
		
	if($params['phrase']!=''){
		$filter_arr[] = "AND (
			bn.`notes` LIKE '%{$params['phrase']}%' OR
			a.`agency_name` LIKE '%{$params['phrase']}%'
		 )";
	}
	*/
	
	
	// combine all filters
	if( count($filter_arr)>0 ){
		$filter_str = " WHERE ct.`crm_task_id` > 0 ".implode(" ",$filter_arr);
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
		FROM `crm_tasks` AS ct
		LEFT JOIN `staff_accounts` AS rb ON ct.`requested_by` = rb.`StaffID`
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
			$status_name = 'Declined';
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
			echo "<div class='success'>New Crm Task Added</div>";
		}
		
		if( is_numeric($_GET['success']) && $_GET['success']==0 ){
			echo "<div class='error'>".$_GET['error_msg']."</div>";
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
						<label>Date:</label><input type=label name='date' value='<?php echo ( $date != '' )?$date:''; ?>' class='addinput searchstyle datepicker vwjbdtp' style="width:85px!important;">		
					</div>
										
					
					<div class='fl-left'>
						<label>User:</label>
						<select name="user">
						<option value=''>--- Select ---</option>
						<?php				
						$jparams = array(
							'distinct_sql' => 'ct.`requested_by`, rb.`FirstName`, rb.`LastName`',
							'active' => 1,
							'sort_list' => array(
								array(
									'order_by' => 'rb.`LastName`',
									'sort' => 'ASC'
								),
								array(
									'order_by' => 'rb.`FirstName`',
									'sort' => 'ASC'
								)
							),
							'echo_query' => 0,
						);
						$rb_sql = getCrmTasks($jparams);
						while( $rb = mysql_fetch_array($rb_sql) ){ ?>
							<option value="<?php echo $rb['requested_by']; ?>" <?php echo ($rb['requested_by']==$user)?'selected="selected"':''; ?>><?php echo $crm->formatStaffName($rb['FirstName'],$rb['LastName']); ?></option>
						<?php	
						}
						
						?>
						</select>
					</div>
					
					
					<div class='fl-left'>
						<label>Status:</label>
						<select name="status">
							<option value="1" <?php echo ($status == 1)?'selected="selected"':''; ?>>Pending</option>
							<option value="2" <?php echo ($status == 2)?'selected="selected"':''; ?>>Declined</option>
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
				<th>Date</th>
				<th>Page Link</th>
				<th>Describe Issue</th>
				<th>Screenshot</th>
				<th>Response</th>
				<th>User</th>
				<th>Status</th>
				<th>Delete</th>
			</tr>
			<?php	
			$i= 0;
			if(mysql_num_rows($plist)>0){
				while($row = mysql_fetch_array($plist)){
					
				// grey alternation color
				$row_color = ($i%2==0)?"":"style='background-color:#eeeeee;'";
	
				// data
				$date = date('d/m/Y',strtotime($row['date_created']));
				$page_link = $row['page_link'];
				$describe_issue = $row['describe_issue'];
				$user = $crm->formatStaffName($row['FirstName'],$row['LastName']);
				$response = $row['response'];
				$status_name = getStatusName($row['status']);

				
			?>
					<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
						
						<td>
							<span class="txt_lbl"><?php echo $date; ?></span>
							<span class="txt_hid"><?php echo $date; ?></span>
						</td>	
						<td>
							<span class="txt_lbl"><?php echo $page_link; ?></span>
							<input type="text" class="txt_hid page_link" value="<?php echo $page_link; ?>" />
						</td>
						<td>
							<span class="txt_lbl"><?php echo $describe_issue; ?></span>
							<textarea class="addtextarea txt_hid describe_issue" style="height: auto;"><?php echo $describe_issue; ?></textarea>
						</td>
						<td>
							<?php
							if( $row['screenshot']!='' ){ ?>
								<a href="/images/crm_task_screenshots/<?php echo $row['screenshot']; ?>" class="fancybox">
									<img src="/images/camera_red.png">
								</a>
							<?php
							}
							?>							
						</td>
						<td>
							<span class="txt_lbl"><?php echo $response; ?></span>
							<span class="txt_hid">
								<?php
								if ( in_array($logged_user, $vip2) ){ ?>
									<input type="text" class="txt_hid response" value="<?php echo $response; ?>" />
								<?php
								}else{ ?>
									<span class="txt_hid"><?php echo $response; ?></span>
								<?php
								}
								?>
								
							</span>
						</td>
						<td>
							<span class="txt_lbl"><?php echo $user; ?></span>
							<span class="txt_hid"><?php echo $user; ?></span>
						</td>
						<td>
							<span class="txt_lbl"><?php echo $status_name; ?></span>
							<span class="txt_hid">
								<?php
								if ( in_array($logged_user, $vip2) ){ ?>
									<select class="status">		
										<option value="1" <?php echo ($row['status'] == 1)?'selected="selected"':''; ?>>Pending</option>
										<option value="2" <?php echo ($row['status'] == 2)?'selected="selected"':''; ?>>Declined</option>
										<option value="3" <?php echo ($row['status'] == 3)?'selected="selected"':''; ?>>In Progress</option>
										<option value="4" <?php echo ($row['status'] == 4)?'selected="selected"':''; ?>>Completed</option>
									</select>
								<?php
								}else{ ?>
									<span class="txt_hid"><?php echo $status_name; ?></span>
									<input type="hidden" class="status" value="<?php echo $row['status']; ?>" />
								<?php
								}
								?>
								
							</span>
						</td>
						<td>
							<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
							<div class="action_div">
								<button class="blue-btn submitbtnImg btn_update">
									<img class="inner_icon" src="images/button_icons/save-button.png">
									Update
								</button>	
								<?php
								if ( in_array($logged_user, $vip) ){ ?>
									<button class="blue-btn submitbtnImg btn_delete">
										<img class="inner_icon" src="images/button_icons/cancel-button.png">
										Delete
									</button>
								<?php	
								}
								?>								
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
								
								<input type="hidden" class="email" value="<?php echo $row['Email']; ?>" />
								<input type="hidden" class="ct_id" value="<?php echo $row['crm_task_id']; ?>" />
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
		<span class="inner_icon_span">Task</span>
	</button>
	
	<div id="add_task_div" class="addproperty formholder">
		<form id="add_task_form" method="post" action="/add_crm_task.php" enctype="multipart/form-data">
			<div class="row">
				<label class="addlabel" for="title">Page Link</label>
				<input type='text' class="addinput" name="page_link" id="page_link" />
			</div>
			<div class="row">
				<label class="addlabel" for="title">Describe Issue</label>
				<textarea class="addtextarea" name="describe_issue" id="describe_issue" style="height: 150px;"></textarea>
			</div>

			<div class="row screenshot_file_div">
				<label class="addlabel" for="title">Screenshot</label>					
					<input type='file' class="addinput screenshot" name="screenshot" />								
			</div>
			<!--
			<div class="row">
				<label class="addlabel" for="title"></label>
				<button type="button" class="submitbtnImg" id="btn_add_file" style="float: left;">
					<img class="inner_icon" src="images/button_icons/add-button.png">
					Add
				</button>
			</div>	
			-->
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
	
		var ct_id = jQuery(this).parents("tr:first").find(".ct_id").val();
		var page_link = jQuery(this).parents("tr:first").find(".page_link").val();
		var describe_issue = jQuery(this).parents("tr:first").find(".describe_issue").val();
		var response = jQuery(this).parents("tr:first").find(".response").val();
		var status = jQuery(this).parents("tr:first").find(".status").val();
		var error = "";
		
		if( page_link == "" ){
			error += "Page Link is required";
		}
		
		if( describe_issue == "" ){
			error += "Describe Page is required";
		}
		
		if(error != ""){
			alert(error);
		}else{			
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_crm_task.php",
				data: { 
					ct_id: ct_id,
					page_link: page_link,
					describe_issue: describe_issue,
					response: response,
					status: status
				}
			}).done(function( ret ) {
				window.location="crm_tasks.php?update_success=1";
			});	
						
		}		
		
	});
	
	
	// delete script
	jQuery(".btn_delete").click(function(){
	
		var ct_id = jQuery(this).parents("tr:first").find(".ct_id").val();
	
		if(confirm("Are you sure you want to delete?")){			
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_crm_task.php",
				data: { 
					ct_id: ct_id
				}
			}).done(function( ret ){
				window.location="crm_tasks.php?delete_success=1";
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
		var default_btn_txt = 'Task';
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
	
		var page_link = jQuery("#page_link").val();
		var describe_issue = jQuery("#describe_issue").val();
		var error = "";
		
		if(page_link==""){
			error += "Page Link is required\n";
		}
		
		if(describe_issue==""){
			error += "Describe Issue is required\n";
		}

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