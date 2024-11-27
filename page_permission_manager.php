<?php

$title = "Page Permission Manager";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$url = 'page_permission_manager.php';

// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class;

$from = mysql_real_escape_string($_REQUEST['from']);
$from2 = ( $from != '' )?$crm->formatDate($from):'';
$to = mysql_real_escape_string($_REQUEST['to']);
$to2 = ( $to != '' )?$crm->formatDate($to):'';
$agency_id = mysql_real_escape_string($_REQUEST['agency_id']);
$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$search_flag = mysql_real_escape_string($_REQUEST['search_flag']);

// sort

$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'a.`agency_name`';
$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort=".urlencode($sort)."&order_by=".urlencode($order_by)."&from=".urlencode($from)."&to=".urlencode($to)."&agency_id=".urlencode($agency_id)."&phrase=".urlencode($phrase);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


$custom_select = "
	SUM(j.`invoice_balance`) AS invoice_balance_tot, a.`agency_name`, a.`agency_id`
";

// get unpaid jobs and exclude 0 job price
$custom_filter = "
	AND j.`job_price` > 0 
	AND j.`invoice_balance` > 0
	AND j.`status` = 'Completed'
	AND (
		a.`status` = 'Active' OR
		a.`status` = 'Deactivated'
	)
";




$jparams = array(
	'custom_select' => $custom_select,
	'custom_filter' => $custom_filter,
	
	'agency_id' => $agency_id,
	'phrase' => $phrase,
	
	'filterDate' => array(
		'from' => $from2,
		'to' => $to2
	),	
	'group_by' => 'a.`agency_id`',
	'sort_list' => array(
		array(
			'order_by' => $order_by,
			'sort' => $sort
		)
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'echo_query' => 0
);
//$plist = $crm->getUnpaidJobs($jparams);


$jparams = array(
	'custom_filter' => $custom_filter,
	
	'agency_id' => $agency_id,
	'phrase' => $phrase,
	
	'filterDate' => array(
		'from' => $from2,
		'to' => $to2
	),
	'group_by' => 'a.`agency_id`'
);
$ptotal_sql = $crm->getUnpaidJobs($jparams);
//$ptotal = mysql_num_rows($ptotal_sql);



$isAgencyFiltered = ( $agency_id != '' && mysql_num_rows($plist) > 0 )?true:false;
	
	
	
function getMenus(){

	return mysql_query("
		SELECT *
		FROM `menu` 
		WHERE `active` = 1
	");
}	

function getMenuAllowedStaffClass($menu_id){
	$sql_str = "
		SELECT *
		FROM `menu_permission_class` AS mpc
		LEFT JOIN `staff_classes` AS sc ON mpc.`staff_class` = sc.`ClassID`
		WHERE mpc.`menu` = {$menu_id}
	";
	return mysql_query($sql_str);
}	

function getMenuAllowedStaffAccounts($menu_id,$denied){
	$sql_str = "
		SELECT *
		FROM `menu_permission_user` AS mpu
		LEFT JOIN `staff_accounts` AS sa ON mpu.`user` = sa.`StaffID`
		WHERE mpu.`menu` = {$menu_id}
		AND mpu.`denied` = {$denied}
	";
	return mysql_query($sql_str);
}

function getAllStaffClasses($exclude_sc_id_arr){

	if( count($exclude_sc_id_arr)>0 ){
		$exclude_sc_id_str = implode(",",$exclude_sc_id_arr);
		$filter = "WHERE `ClassID` NOT IN({$exclude_sc_id_str})";
	}
	

	return mysql_query("
		SELECT *
		FROM `staff_classes`
		{$filter}
	");
}

function getAllStaffAccounts($exclude_sa_id_arr){

	if( count($exclude_sa_id_arr)>0 ){
		$exclude_sa_id_str = implode(",",$exclude_sa_id_arr);
		$filter = "AND `StaffID` NOT IN({$exclude_sa_id_str})";
	}
	
	return mysql_query("
		SELECT * 
		FROM `staff_accounts`
		WHERE `Deleted` = 0
		AND `active` = 1
		{$filter}
		ORDER BY `FirstName` ASC, `LastName` ASC  		
	");
}



function getPages(){
	$sql = "
		SELECT *
		FROM `crm_pages` AS cp
		LEFT JOIN `menu` AS m ON cp.`menu` = m.`menu_id`
		WHERE cp.`active`
	";
	return mysql_query($sql);
}



// PAGE 

function getPageAllowedStaffClass($crm_page_id){
	$sql_str = "
		SELECT *
		FROM `crm_page_permission_class` AS cppc
		LEFT JOIN `staff_classes` AS sc ON cppc.`staff_class` = sc.`ClassID`
		WHERE cppc.`page` = {$crm_page_id}
	";
	return mysql_query($sql_str);
}	

function getPageAllowedStaffAccounts($menu_id,$denied){
	$sql_str = "
		SELECT *
		FROM `crm_page_permission_user` AS cppu
		LEFT JOIN `staff_accounts` AS sa ON cppu.`user` = sa.`StaffID`
		WHERE cppu.`page` = {$menu_id}
		AND cppu.`denied` = {$denied}
	";
	return mysql_query($sql_str);
}



/*
$sc_arr = [];

$sc_sql = getAllStaffClasses();
while( $sc = mysql_fetch_array($sc_sql) ){
	$sc_arr[] = array(
		'ClassID' =>  $sc['ClassID'],
		'ClassName' =>  $sc['ClassName']
	);
}
*/

//print_r($sc_arr);

?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.yello_mark{
	background-color: #ffff9d;
}
.green_mark{
	background-color: #c2ffa7;
}
.payment_details_table td {
    padding: 5px;
	border: none;
	text-align: left;
}
.payment_details_table tr {
	border: none;
}
.save_div{
	float:right; 
	margin-bottom: 20px; 
	position: relative; 
	bottom: 85px;
	display:none;
}
.jcolorItRed{
	color: red;
}
.jcolorItGreen{
	color: green;
}
.jtblfooter{
	background-color: #eeeeee;
	font-weight: bold;
}
.tbl-sd ul{
	margin: 0;
	padding: 0;
}
.edit_div_hidden{
	display: none;
}
.jtable{
	width:auto;
}
.jtable tr{
	border: none;
}
.jtable tr td{
	border: none;
}
.inner_icon{
	margin-right: 0;
}
</style>




<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
			  <ul>
				<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
				<li class="other first"><a title="<?php echo $title ?>" href="<?php echo $url; ?>"><strong><?php echo $title ?></strong></a></li>
			  </ul>
			</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['print_clear']==1){
			echo '<div class="success">Printed jobs has been cleared</div>';
		}
		
		
		//echo date('Y-m-d', strtotime("-30 days"));
		
		
		?>
		
		
		<form method="POST" name='example' id='example'>
			<input type='hidden' name='status' value='<?php echo $status ?>'>

			<table border=1 cellpadding=0 cellspacing=0 width="100%">
				<tr class="tbl-view-prop">
				<td>

				<div class="aviw_drop-h aviw_drop-vp" id="view-jobs">
					
					<div class="fl-left">
						<label>Phrase:</label>
						<input name="phrase" value="<?php echo $phrase; ?>" class="addinput searchstyle vwjbdtp" style="width: 100px !important;" type="label">
					</div>
					
					<div class='fl-left' style="float:left;">	
						<input type="hidden" name="search_flag" value="1" />
						<button type='submit' class='submitbtnImg' id="btn_search">
							<img class="inner_icon" src="images/button_icons/search-button.png">
							Search
						</button>
					</div>					
					
					<div style="clear:both;"></div>			
					  
				</td>
				</tr>
			</table>	  
				  
			</form>
			
			
			<?php
			
			// no sort yet
			if($_REQUEST['sort']==""){
				$sort_arrow = 'up';
			}
			
			?>

			<h2 class="heading">Menu Permissions</h2>
			<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
				
				<tr class="toprow jalign_left">
					<th>Menu</th>					
					<th>Staff Class</th>
					<th>Allowed Staff</th>
					<th>Denied Staff</th>	
				</tr>
				<?php	
				$i= 0;
				$menu_sql = getMenus();
				if(mysql_num_rows($menu_sql)>0){
					while( $menu = mysql_fetch_array($menu_sql) ){
				?>
						
					<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
								
						<td>
							<?php echo $menu['menu_name']; ?>
							<input type="hidden" class="menu_id" value="<?php echo $menu['menu_id']; ?>" />
						</td>						
						<td>
							
							<?php
							// STAFF CLASS
							$sc_sql = getMenuAllowedStaffClass($menu['menu_id']);
							$exclude_sc_id_arr = [];
							if( mysql_num_rows($sc_sql)>0 ){?>
							<table class="jtable">
							<?php
								while( $sc = mysql_fetch_array($sc_sql) ){ ?>
									<tr>
										<td>
											<?php echo $sc['ClassName']; ?>	
										</td>
										<td>
											<button type="button" class="submitbtnImg btn_remove_sc">
												<img class="inner_icon" src="images/button_icons/cancel-button.png" /> 												
											</button>
											<input type="hidden" class="mpc_id" value="<?php echo $sc['mpc_id']; ?>" />
										</td>
									</tr>
								<?php
								$exclude_sc_id_arr[] = $sc['ClassID'];
								}
								?>
							</table>													
							<?php
							}							
							?>
							
							
							<button type="button" class="blue-btn submitbtnImg sc_btn btn_edit_sc">
								<img class="inner_icon" src="images/button_icons/edit-button.png" /> 
								<span class="inner_icon_txt">Edit</span>
							</button>
							
							
							<span class="edit_div_hidden sc_hidden_div">
								<?php
								$sc_dp_sql = getAllStaffClasses($exclude_sc_id_arr);
								?>
								<select class="sc_dp">
									<option value="">--- Select ---</option>
									<?php
									while( $sc_dp = mysql_fetch_array($sc_dp_sql) ){ ?>
										<option value="<?php echo $sc_dp['ClassID'] ?>"><?php echo $sc_dp['ClassName'] ?></option>
									<?php
									}
									?>
								</select>
								<button type="button" class="blue-btn submitbtnImg btn_save_sc">
									<img class="inner_icon" src="images/button_icons/save-button.png" /> 
									Save
								</button>
							</span>
							
							
						</td>	
						<td>
						
							<?php
							// ALLOWED STAFF
							$sa_sql = getMenuAllowedStaffAccounts($menu['menu_id'],0);
							$exclude_sa_id_arr = [];
							if( mysql_num_rows($sa_sql)>0 ){								
							?>
							<table class="jtable">
							<?php
								while( $sa = mysql_fetch_array($sa_sql) ){ ?>
									<tr>
										<td><?php echo "{$sa['FirstName']} {$sa['LastName']}"; ?></td>
										<td>
											<button type="button" class="submitbtnImg btn_remove_saa">
												<img class="inner_icon" src="images/button_icons/cancel-button.png" /> 												
											</button>
											<input type="hidden" class="approved_mpu_id" value="<?php echo $sa['mpu_id']; ?>" />
										</td>
									</tr>
								<?php
									$exclude_sa_id_arr[] = $sa['StaffID'];
								}
								?>
							</table>
							<?php
							
							}
							?>
							
							<button type="button" class="blue-btn submitbtnImg saa_btn btn_edit_saa">
								<img class="inner_icon" src="images/button_icons/edit-button.png" /> 
								<span class="inner_icon_txt">Edit</span>
							</button>
							
							
							<span class="edit_div_hidden allowed_staff_hidden_div">
								<?php
								$sa_dp_sql = getAllStaffAccounts($exclude_sa_id_arr);								
								?>
								<select class="saa_dp">
									<option value="">--- Select ---</option>
									<?php
									while( $sc_dp = mysql_fetch_array($sa_dp_sql) ){ ?>
										<option value="<?php echo $sc_dp['StaffID'] ?>"><?php echo $crm->formatStaffName($sc_dp['FirstName'],$sc_dp['LastName']); ?></option>
									<?php									
									}
									?>
								</select>
								<button type="button" class="blue-btn submitbtnImg btn_save_saa">
									<img class="inner_icon" src="images/button_icons/save-button.png" /> 
									Save
								</button>
							</span>
							
						
						</td>
						<td>
						
							<?php
							// DENIED STAFF
							$sa_sql = getMenuAllowedStaffAccounts($menu['menu_id'],1);
							$exclude_sa_id_arr = [];
							if( mysql_num_rows($sa_sql)>0 ){?>
							<table class="jtable">
							<?php
								while( $sa = mysql_fetch_array($sa_sql) ){ ?>
									<tr>
										<td>
											<?php echo "{$sa['FirstName']} {$sa['LastName']}"; ?>
										</td>
										<td>
											<button type="button" class="submitbtnImg btn_remove_sad">
												<img class="inner_icon" src="images/button_icons/cancel-button.png" /> 												
											</button>
											<input type="hidden" class="denied_mpu_id" value="<?php echo $sa['mpu_id']; ?>" />
										</td>
									</tr>
								<?php
									$exclude_sa_id_arr[] = $sa['StaffID'];
								}
								?>
							</table>
							
							<?php
							}							
							?>
						
							<button type="button" class="blue-btn submitbtnImg btn_edit_sad">
								<img class="inner_icon" src="images/button_icons/edit-button.png" /> 
								<span class="inner_icon_txt">Edit</span>
							</button>
							
							
							<span class="edit_div_hidden denied_staff_hidden_div">
								<?php
								$sa_dp_sql = getAllStaffAccounts($exclude_sa_id_arr);								
								?>
								<select class="sad_dp">
									<option value="">--- Select ---</option>
									<?php
									while( $sc_dp = mysql_fetch_array($sa_dp_sql) ){ ?>
										<option value="<?php echo $sc_dp['StaffID'] ?>"><?php echo $crm->formatStaffName($sc_dp['FirstName'],$sc_dp['LastName']); ?></option>
									<?php									
									}
									?>
								</select>
								<button type="button" class="blue-btn submitbtnImg btn_save_sad">
									<img class="inner_icon" src="images/button_icons/save-button.png" /> 
									Save
								</button>
							</span>
							
						
						</td>	
					
					</tr>

				<?php
					}
				}else{ ?>
					<td colspan="100%" align="left">Empty</td>
				<?php
				}
				?>
					
			</table>
			
			
			<!-- PAGE -->
			<h2 class="heading">Page Permissions</h2>
			<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
				
				<tr class="toprow jalign_left">
					<th>Page Name</th>
					<th>Page URL</th>
					<th>Menu</th>					
					<th>Staff Class</th>
					<th>Allowed Staff</th>
					<th>Denied Staff</th>	
				</tr>
				<?php	
				$i= 0;
				$plist = getPages();
				if(mysql_num_rows($plist)>0){
				while( $row = mysql_fetch_array($plist) ) {
				?>
						
					<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
						<td><?php echo $row['page_name']; ?></td>
						<td><?php echo $row['page_url']; ?></td>
						<td><?php echo $row['menu_name']; ?></td>
						
						<td>
							
							<?php
							// STAFF CLASS
							$sc_sql = getPageAllowedStaffClass($row['crm_page_id']);
							$exclude_sc_id_arr = [];
							if( mysql_num_rows($sc_sql)>0 ){?>
							<table class="jtable">
							<?php
								while( $sc = mysql_fetch_array($sc_sql) ){ ?>
									<tr>
										<td>
											<?php echo $sc['ClassName']; ?>	
										</td>
										<td>
											<button type="button" class="submitbtnImg btn_remove_sc_page">
												<img class="inner_icon" src="images/button_icons/cancel-button.png" /> 												
											</button>
											<input type="hidden" class="mpc_id" value="<?php echo $sc['mpc_id']; ?>" />
										</td>
									</tr>
								<?php
								$exclude_sc_id_arr[] = $sc['ClassID'];
								}
								?>
							</table>													
							<?php
							}							
							?>
							
							
							<button type="button" class="blue-btn submitbtnImg sc_btn btn_edit_sc_page">
								<img class="inner_icon" src="images/button_icons/edit-button.png" /> 
								<span class="inner_icon_txt">Edit</span>
							</button>
							
							
							<span class="edit_div_hidden sc_hidden_div_page">
								<?php
								$sc_dp_sql = getAllStaffClasses($exclude_sc_id_arr);
								?>
								<select class="sc_dp">
									<option value="">--- Select ---</option>
									<?php
									while( $sc_dp = mysql_fetch_array($sc_dp_sql) ){ ?>
										<option value="<?php echo $sc_dp['ClassID'] ?>"><?php echo $sc_dp['ClassName'] ?></option>
									<?php
									}
									?>
								</select>
								<button type="button" class="blue-btn submitbtnImg btn_save_sc_page">
									<img class="inner_icon" src="images/button_icons/save-button.png" /> 
									Save
								</button>
							</span>
							
						</td>
						
						<td>
							
							
							<?php
							// ALLOWED STAFF
							$sa_sql = getPageAllowedStaffAccounts($row['crm_page_id'],0);
							$exclude_sa_id_arr = [];
							if( mysql_num_rows($sa_sql)>0 ){								
							?>
							<table class="jtable">
							<?php
								while( $sa = mysql_fetch_array($sa_sql) ){ ?>
									<tr>
										<td><?php echo "{$sa['FirstName']} {$sa['LastName']}"; ?></td>
										<td>
											<button type="button" class="submitbtnImg btn_remove_saa_page">
												<img class="inner_icon" src="images/button_icons/cancel-button.png" /> 												
											</button>
											<input type="hidden" class="approved_mpu_id" value="<?php echo $sa['mpu_id']; ?>" />
										</td>
									</tr>
								<?php
									$exclude_sa_id_arr[] = $sa['StaffID'];
								}
								?>
							</table>
							<?php
							
							}
							?>
							
							<button type="button" class="blue-btn submitbtnImg saa_btn btn_edit_saa_page">
								<img class="inner_icon" src="images/button_icons/edit-button.png" /> 
								<span class="inner_icon_txt">Edit</span>
							</button>
							
							
							<span class="edit_div_hidden allowed_staff_hidden_div">
								<?php
								$sa_dp_sql = getAllStaffAccounts($exclude_sa_id_arr);								
								?>
								<select class="saa_dp">
									<option value="">--- Select ---</option>
									<?php
									while( $sc_dp = mysql_fetch_array($sa_dp_sql) ){ ?>
										<option value="<?php echo $sc_dp['StaffID'] ?>"><?php echo $crm->formatStaffName($sc_dp['FirstName'],$sc_dp['LastName']); ?></option>
									<?php									
									}
									?>
								</select>
								<button type="button" class="blue-btn submitbtnImg btn_save_saa_page">
									<img class="inner_icon" src="images/button_icons/save-button.png" /> 
									Save
								</button>
							</span>
							
							
						</td>	
						<td>
							
							<?php
							// DENIED STAFF
							$sa_sql = getPageAllowedStaffAccounts($row['crm_page_id'],1);
							$exclude_sa_id_arr = [];
							if( mysql_num_rows($sa_sql)>0 ){?>
							<table class="jtable">
							<?php
								while( $sa = mysql_fetch_array($sa_sql) ){ ?>
									<tr>
										<td>
											<?php echo "{$sa['FirstName']} {$sa['LastName']}"; ?>
										</td>
										<td>
											<button type="button" class="submitbtnImg btn_remove_sad_page">
												<img class="inner_icon" src="images/button_icons/cancel-button.png" /> 												
											</button>
											<input type="hidden" class="denied_mpu_id" value="<?php echo $sa['mpu_id']; ?>" />
										</td>
									</tr>
								<?php
									$exclude_sa_id_arr[] = $sa['StaffID'];
								}
								?>
							</table>
							
							<?php
							}							
							?>
						
							<button type="button" class="blue-btn submitbtnImg btn_edit_sad_page">
								<img class="inner_icon" src="images/button_icons/edit-button.png" /> 
								<span class="inner_icon_txt">Edit</span>
							</button>
							
							
							<span class="edit_div_hidden denied_staff_hidden_div">
								<?php
								$sa_dp_sql = getAllStaffAccounts($exclude_sa_id_arr);								
								?>
								<select class="sad_dp">
									<option value="">--- Select ---</option>
									<?php
									while( $sc_dp = mysql_fetch_array($sa_dp_sql) ){ ?>
										<option value="<?php echo $sc_dp['StaffID'] ?>"><?php echo $crm->formatStaffName($sc_dp['FirstName'],$sc_dp['LastName']); ?></option>
									<?php									
									}
									?>
								</select>
								<button type="button" class="blue-btn submitbtnImg btn_save_sad_page">
									<img class="inner_icon" src="images/button_icons/save-button.png" /> 
									Save
								</button>
							</span>
							
							
						</td>		
					
					</tr>
				
				<?php
					}
				}else{ ?>
					<td colspan="100%" align="left">Empty</td>
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
		
		
		<div class="save_div">				
			<button type='submit' class='submitbtnImg blue-btn' id="btn_save">
				<img class="inner_icon" src="images/button_icons/save-button.png">
				SAVE
			</button>
		</div>

		
	</div>
</div>

<br class="clearfloat" />

<script>
jQuery(document).ready(function(){
	
	// STAFF CLASSS
	// Edit button
	jQuery(".btn_edit_sc").click(function(){
		
		var btn_txt = jQuery(this).find(".inner_icon_txt").html();
		var orig_btn_txt = 'Edit';
		var orig_btn_icon = 'images/button_icons/edit-button.png';
		var cancel_btn_icon = 'images/button_icons/cancel-button.png';
		
		if( btn_txt == 'Edit' ){
			jQuery(this).removeClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html('Cancel');
			jQuery(this).find(".inner_icon").attr("src",cancel_btn_icon)
			jQuery(this).parents("tr:first").find(".sc_hidden_div").show();
		}else{
			jQuery(this).addClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html(orig_btn_txt);
			jQuery(this).find(".inner_icon").attr("src",orig_btn_icon)
			jQuery(this).parents("tr:first").find(".sc_hidden_div").hide();
		}
		
		
	});
	
	
	
	// save staff class permission
	jQuery(".btn_save_sc").click(function(){
		
		var obj = jQuery(this);
		var row = obj.parents("tr:first");
		
		var staff_class = row.find('.sc_dp').val();
		var menu_id = row.find('.menu_id').val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_saveMenuStaffClassPermission.php",
			data: { 
				staff_class: staff_class,
				menu_id: menu_id			
			}
		}).done(function( ret ){	
			window.location="<?php echo $url; ?>";
		});
		
		
	});
	
	// delete staff class permission
	jQuery(".btn_remove_sc").click(function(){
		
		if( confirm("Are you sure you want to delete?") ){
			
			
			var obj = jQuery(this);
			var row = obj.parents("tr:first");
			
			var mpc_id = row.find('.mpc_id').val();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_removeMenuStaffClassPermission.php",
				data: { 
					mpc_id: mpc_id			
				}
			}).done(function( ret ){	
				window.location="<?php echo $url; ?>";
			});
			
			
		}
		
		
	});
	
	
	
	
	
	// ALLOWED STAFF 
	// Edit button
	jQuery(".btn_edit_saa").click(function(){
		
		var btn_txt = jQuery(this).find(".inner_icon_txt").html();
		var orig_btn_txt = 'Edit';
		var orig_btn_icon = 'images/button_icons/edit-button.png';
		var cancel_btn_icon = 'images/button_icons/cancel-button.png';
		
		if( btn_txt == 'Edit' ){
			jQuery(this).removeClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html('Cancel');
			jQuery(this).find(".inner_icon").attr("src",cancel_btn_icon)
			jQuery(this).parents("tr:first").find(".allowed_staff_hidden_div").show();
		}else{
			jQuery(this).addClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html(orig_btn_txt);
			jQuery(this).find(".inner_icon").attr("src",orig_btn_icon)
			jQuery(this).parents("tr:first").find(".allowed_staff_hidden_div").hide();
		}
		
		
	});
	
	
	
	// save staff class permission
	jQuery(".btn_save_saa").click(function(){
		
		var obj = jQuery(this);
		var row = obj.parents("tr:first");
		
		var staff_account = row.find('.saa_dp').val();
		var menu_id = row.find('.menu_id').val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_saveMenuStaffAccountPermission.php",
			data: { 
				staff_account: staff_account,
				menu_id: menu_id,
				denied: 0
			}
		}).done(function( ret ){	
			window.location="<?php echo $url; ?>";
		});
		
		
	});
	
	
	
	// delete staff class permission
	jQuery(".btn_remove_saa").click(function(){
		
		if( confirm("Are you sure you want to delete?") ){
			
			
			var obj = jQuery(this);
			var row = obj.parents("tr:first");
			
			var mpu_id = row.find('.approved_mpu_id').val();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_removeMenuStaffAccountPermission.php",
				data: { 
					mpu_id: mpu_id			
				}
			}).done(function( ret ){	
				window.location="<?php echo $url; ?>";
			});
			
			
		}
		
		
	});
	
	
	
	// DENIED STAFF 
	// Edit button
	jQuery(".btn_edit_sad").click(function(){
		
		var btn_txt = jQuery(this).find(".inner_icon_txt").html();
		var orig_btn_txt = 'Edit';
		var orig_btn_icon = 'images/button_icons/edit-button.png';
		var cancel_btn_icon = 'images/button_icons/cancel-button.png';
		
		if( btn_txt == 'Edit' ){
			jQuery(this).removeClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html('Cancel');
			jQuery(this).find(".inner_icon").attr("src",cancel_btn_icon)
			jQuery(this).parents("tr:first").find(".denied_staff_hidden_div").show();
		}else{
			jQuery(this).addClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html(orig_btn_txt);
			jQuery(this).find(".inner_icon").attr("src",orig_btn_icon)
			jQuery(this).parents("tr:first").find(".denied_staff_hidden_div").hide();
		}
		
		
	});
	
	
	
	// save staff class permission
	jQuery(".btn_save_sad").click(function(){
		
		var obj = jQuery(this);
		var row = obj.parents("tr:first");
		
		var staff_account = row.find('.sad_dp').val();
		var menu_id = row.find('.menu_id').val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_saveMenuStaffAccountPermission.php",
			data: { 
				staff_account: staff_account,
				menu_id: menu_id,
				denied: 1
			}
		}).done(function( ret ){	
			window.location="<?php echo $url; ?>";
		});
		
		
	});
	
	
	
	// delete staff class permission
	jQuery(".btn_remove_sad").click(function(){
		
		if( confirm("Are you sure you want to delete?") ){
			
			
			var obj = jQuery(this);
			var row = obj.parents("tr:first");
			
			var mpu_id = row.find('.denied_mpu_id').val();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_removeMenuStaffAccountPermission.php",
				data: { 
					mpu_id: mpu_id			
				}
			}).done(function( ret ){	
				window.location="<?php echo $url; ?>";
			});
			
			
		}
		
		
	});
	
	
	
	
	/*
	// PAGE
	
	// STAFF CLASSS
	// Edit button
	jQuery(".btn_edit_sc_page").click(function(){
		
		var btn_txt = jQuery(this).find(".inner_icon_txt").html();
		var orig_btn_txt = 'Edit';
		var orig_btn_icon = 'images/button_icons/edit-button.png';
		var cancel_btn_icon = 'images/button_icons/cancel-button.png';
		
		if( btn_txt == 'Edit' ){
			jQuery(this).removeClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html('Cancel');
			jQuery(this).find(".inner_icon").attr("src",cancel_btn_icon)
			jQuery(this).parents("tr:first").find(".sc_hidden_div_page").show();
		}else{
			jQuery(this).addClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html(orig_btn_txt);
			jQuery(this).find(".inner_icon").attr("src",orig_btn_icon)
			jQuery(this).parents("tr:first").find(".sc_hidden_div_page").hide();
		}
		
		
	});
	
	
	
	// save staff class permission
	jQuery(".sc_hidden_div_page").click(function(){
		
		var obj = jQuery(this);
		var row = obj.parents("tr:first");
		
		var staff_class = row.find('.sc_dp').val();
		var menu_id = row.find('.menu_id').val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_saveMenuStaffClassPermission.php",
			data: { 
				staff_class: staff_class,
				menu_id: menu_id			
			}
		}).done(function( ret ){	
			window.location="<?php echo $url; ?>";
		});
		
		
	});
	*/
	
	/*
	// delete staff class permission
	jQuery(".btn_remove_sc").click(function(){
		
		if( confirm("Are you sure you want to delete?") ){
			
			
			var obj = jQuery(this);
			var row = obj.parents("tr:first");
			
			var mpc_id = row.find('.mpc_id').val();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_removeMenuStaffClassPermission.php",
				data: { 
					mpc_id: mpc_id			
				}
			}).done(function( ret ){	
				window.location="<?php echo $url; ?>";
			});
			
			
		}
		
		
	});
	
	
	
	
	
	// ALLOWED STAFF 
	// Edit button
	jQuery(".btn_edit_saa").click(function(){
		
		var btn_txt = jQuery(this).find(".inner_icon_txt").html();
		var orig_btn_txt = 'Edit';
		var orig_btn_icon = 'images/button_icons/edit-button.png';
		var cancel_btn_icon = 'images/button_icons/cancel-button.png';
		
		if( btn_txt == 'Edit' ){
			jQuery(this).removeClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html('Cancel');
			jQuery(this).find(".inner_icon").attr("src",cancel_btn_icon)
			jQuery(this).parents("tr:first").find(".allowed_staff_hidden_div").show();
		}else{
			jQuery(this).addClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html(orig_btn_txt);
			jQuery(this).find(".inner_icon").attr("src",orig_btn_icon)
			jQuery(this).parents("tr:first").find(".allowed_staff_hidden_div").hide();
		}
		
		
	});
	
	
	
	// save staff class permission
	jQuery(".btn_save_saa").click(function(){
		
		var obj = jQuery(this);
		var row = obj.parents("tr:first");
		
		var staff_account = row.find('.saa_dp').val();
		var menu_id = row.find('.menu_id').val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_saveMenuStaffAccountPermission.php",
			data: { 
				staff_account: staff_account,
				menu_id: menu_id,
				denied: 0
			}
		}).done(function( ret ){	
			window.location="<?php echo $url; ?>";
		});
		
		
	});
	
	
	
	// delete staff class permission
	jQuery(".btn_remove_saa").click(function(){
		
		if( confirm("Are you sure you want to delete?") ){
			
			
			var obj = jQuery(this);
			var row = obj.parents("tr:first");
			
			var mpu_id = row.find('.approved_mpu_id').val();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_removeMenuStaffAccountPermission.php",
				data: { 
					mpu_id: mpu_id			
				}
			}).done(function( ret ){	
				window.location="<?php echo $url; ?>";
			});
			
			
		}
		
		
	});
	
	
	
	// DENIED STAFF 
	// Edit button
	jQuery(".btn_edit_sad").click(function(){
		
		var btn_txt = jQuery(this).find(".inner_icon_txt").html();
		var orig_btn_txt = 'Edit';
		var orig_btn_icon = 'images/button_icons/edit-button.png';
		var cancel_btn_icon = 'images/button_icons/cancel-button.png';
		
		if( btn_txt == 'Edit' ){
			jQuery(this).removeClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html('Cancel');
			jQuery(this).find(".inner_icon").attr("src",cancel_btn_icon)
			jQuery(this).parents("tr:first").find(".denied_staff_hidden_div").show();
		}else{
			jQuery(this).addClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html(orig_btn_txt);
			jQuery(this).find(".inner_icon").attr("src",orig_btn_icon)
			jQuery(this).parents("tr:first").find(".denied_staff_hidden_div").hide();
		}
		
		
	});
	
	
	
	// save staff class permission
	jQuery(".btn_save_sad").click(function(){
		
		var obj = jQuery(this);
		var row = obj.parents("tr:first");
		
		var staff_account = row.find('.sad_dp').val();
		var menu_id = row.find('.menu_id').val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_saveMenuStaffAccountPermission.php",
			data: { 
				staff_account: staff_account,
				menu_id: menu_id,
				denied: 1
			}
		}).done(function( ret ){	
			window.location="<?php echo $url; ?>";
		});
		
		
	});
	
	
	
	// delete staff class permission
	jQuery(".btn_remove_sad").click(function(){
		
		if( confirm("Are you sure you want to delete?") ){
			
			
			var obj = jQuery(this);
			var row = obj.parents("tr:first");
			
			var mpu_id = row.find('.denied_mpu_id').val();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_removeMenuStaffAccountPermission.php",
				data: { 
					mpu_id: mpu_id			
				}
			}).done(function( ret ){	
				window.location="<?php echo $url; ?>";
			});
			
			
		}
		
		
	});
	*/
	
	
	
	
});
</script>
</body>
</html>