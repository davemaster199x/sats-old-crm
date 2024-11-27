<?php

$title = "Menu Manager";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$url = 'menu_manager.php';

// Initiate job class
$crm = new Sats_Crm_Class;

$page_display = mysql_real_escape_string($_REQUEST['page_display']);
if( is_numeric($page_display) && $page_display == 0 ){
	$page_active = $page_display;
}else if( $page_display == 1 ){
	$page_active = $page_display;
}else if( $page_display == -1 ){
	$page_active = '';
}else if( $page_display == '' ){
	$page_active = 1; // default to active
}

/*
echo "
page_display: {$page_display}<br />
page_active: {$page_active}
";
*/

// MENU
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

function isMenuAllowed_StaffClass($menu_id,$staff_class){
	
	$sql_str = "
		SELECT *
		FROM `menu_permission_class` AS mpc
		WHERE mpc.`menu` = {$menu_id}
		AND mpc.`staff_class` = {$staff_class}
	";
	$sql = mysql_query($sql_str);
	if( mysql_num_rows($sql) > 0 ){
		return true;
	}else{
		return false;
	}
	
}



// PAGE 
function getPageAllowedStaffAccounts($crm_page_id,$denied){
	$sql_str = "
		SELECT *
		FROM `crm_page_permission_user` AS cppu
		LEFT JOIN `staff_accounts` AS sa ON cppu.`user` = sa.`StaffID`
		WHERE cppu.`page` = {$crm_page_id}
		AND cppu.`denied` = {$denied}
	";
	return mysql_query($sql_str);
}

function isPageAllowed_StaffClass($crm_page_id,$staff_class){
	
	$sql_str = "
		SELECT *
		FROM `crm_page_permission_class` AS cppc
		WHERE cppc.`page` = {$crm_page_id}
		AND cppc.`staff_class` = {$staff_class}
	";
	$sql = mysql_query($sql_str);
	if( mysql_num_rows($sql) > 0 ){
		return true;
	}else{
		return false;
	}
	
}






$sa_arr = [];
$sa_sql = $crm->getAllStaffAccountsRegardlessOfCountry();
while( $sa = mysql_fetch_array($sa_sql) ){
	$sa_arr[] = array(
		'StaffID' =>  $sa['StaffID'],
		'FirstName' =>  $sa['FirstName'],
		'LastName' =>  $sa['LastName']
	);
}


//print_r($sa_arr);

?>
<style>
#site_map_tbl{
	text-align: left;
}

.add_page_tbl{
	width: auto;
	text-align: left;
}
.add_page_tbl tr{
	border: none !important;
}
.add_page_tbl tr td{
	border: none !important;
}
.add_page_tbl input[type="text"]{
	width: 266px;
}


.page_tbl{
	width: auto;
	text-align: left;
}
.page_tbl tr{
	border: none !important;
}
.page_tbl tr td{
	border: none !important;
}


.header_menu{
	background-color: #ECECEC;
}
#add_crm_page_div, #add_crm_menu_div{
	display: none;
}
#site_map_tbl .page_name_td{
	padding-left: 25px;
}
.edit_div_hidden{
	display: none;

}
.jtable{
	width:auto;
	text-align: left;
	margin-bottom: 30px;
}
.jtable tr{
	border: none;
}
.jtable tr td{
	border: none;
}
.jtable .inner_icon{
	margin-right: 0;
}
.allowed_users_td{
	width: 80%;
}
.staff_div{
	width: 200px;
	padding: 10px;
}
.staff_div_header{
	text-align: left;
	margin-top: 0px;
}
.jcolorItGreen{
	color: green !important;
}
.txt_hid, .action_div{
	display:none;
}
.crm_menu_row .header_menu{
	cursor: pointer;
}
.crm_page_tbody{
	display: none;
}
.jredHighlightRow{
	background-color: #fcbdb6 !important;
}
.page_menu,
.page_isActive{
	width: auto; 
	margin: 0; 
	width: 95%;
}
.crm_pages_row:hover,
.crm_menu_row:hover{
	background-color: #fcbdb6 !important;
}
.page_status_span{
	float: left; 
	padding-top: 5px;
}
.saa_dp{
	margin: 3px 0;
}
.sa_dp_group_div{
	margin: 13px 0;
}
.green_check{
	display: none;
	width: 15px; 
	margin-left: 6px;
}
.triangle_icon{
	float: right;
	width: 11px;
	position: relative;
	top: 1px;
}
.sa_chk_td{
	text-align: center;
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
	
		
		
		if($_GET['page_success']==1){
			echo '<div class="success">New Page Added</div>';
		}
		
		if($_GET['menu_success']==1){
			echo '<div class="success">New Menu Added</div>';
		}
		
		if($_GET['menu_update']==1){
			echo '<div class="success">Menu Name Updated</div>';
		}
		if($_GET['page_update']==1){
			echo '<div class="success">Page Name Updated</div>';
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
						<span class="page_status_span">Page Status:</span>
						<select name="page_display" class="addinput">	
							<option value="1" <?php echo ( $page_display == 1 )?'selected="selected"':''; ?>>Active</option>								
							<option value="0" <?php echo ( is_numeric($page_display) && $page_display == 0 )?'selected="selected"':''; ?>>Inactive</option>								
							<option value="-1" <?php echo ( $page_display == -1 )?'selected="selected"':''; ?>>ALL</option>
						</select>
					</div>
					
					<div class='fl-left' style="float:left;">	
						<input type="hidden" name="search_flag" value="1" />
						<button type='submit' class='submitbtnImg' id="btn_search" style="margin:0;">
							<img class="inner_icon" src="images/button_icons/search-button.png">
							Go
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

			
			<table id="site_map_tbl" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin: 0px;">			
				
				<thead>
					<tr class="toprow jalign_left">
						<th>Menu/Page</th>	
						<?php
						// HEADER - STAFF CLASS
						$sc_sql = $crm->getAllStaffClasses();
						while( $sc = mysql_fetch_array($sc_sql) ){ ?>
							<th class="sa_chk_td"><?php echo $sc['ClassName'] ?></th>
						<?php
						}
						?>	
						<th>Allowed Staff</th>
						<th>Denied Staff</th>
						<th>Edit</th>
					</tr>
				</thead>
				
				
				<?php
				// MENU
				$menu_sql = $crm->getMenus();
				if(mysql_num_rows($menu_sql)>0){ 
					while( $menu = mysql_fetch_array($menu_sql) ){ 
					$jtable_id = "jtable_menu_{$menu['menu_id']}";
					?>
					<tbody class="crm_menu_tbody">
						<tr class="toprow jalign_left jmain_row crm_menu_row">
							<td class="header_menu">
								<span class="txt_lbl">
									<img src="images/triangle_down.png" class="triangle_icon" data-icon_val="down" />
									<strong><?php echo $menu['menu_name']; ?></strong>									
								</span>
								<span class="txt_hid"><input type="text" style="margin: 0;" class="addinput menu_name" value="<?php echo $menu['menu_name']; ?>" /></span>
								<input type="hidden" class="menu_id" value="<?php echo $menu['menu_id']; ?>" />
							</td>
							<?php
							$sc_sql = $crm->getAllStaffClasses();
							while( $sc = mysql_fetch_array($sc_sql) ){ ?>
								<td class="sa_chk_td">
									<input type="checkbox" title="<?php echo $sc['ClassName'] ?>" class="staff_class_chk menu_staff_class_chk" <?php echo ( isMenuAllowed_StaffClass($menu['menu_id'],$sc['ClassID']) == true )?'checked="checked"':''; ?> value="<?php echo $sc['ClassID']; ?>" />
									<img class="green_check" src="/images/check_icon2.png">
								</td>
							<?php
							}
							?>
							<td>
							
								
								<?php
								// ALLOWED STAFF
								$denied = 0;								
								$sa_sql = getMenuAllowedStaffAccounts($menu['menu_id'],$denied);
								$exclude_sa_id_arr = [];
								if( mysql_num_rows($sa_sql)>0 ){ // list								
								?>
								<div style="display:none">								
									<div class="staff_div" id="<?php echo $jtable_id; ?>_allowed">
									
										<h2 class="staff_div_header">Allowed Staff</h2>
										<table class="jtable">
			
											<?php
											while( $sa = mysql_fetch_array($sa_sql) ){ ?>
												<tr>
													<td class="allowed_users_td"><?php echo "{$sa['FirstName']} {$sa['LastName']}"; ?></td>
													<td>
														<button type="button" class="submitbtnImg btn_remove_sa_menu">
															<img class="inner_icon" src="images/button_icons/cancel-button.png" /> 												
														</button>
														<input type="hidden" class="mpu_id" value="<?php echo $sa['mpu_id']; ?>" />
													</td>
												</tr>
											<?php
												$exclude_sa_id_arr[] = $sa['StaffID'];
											}
											?>
								
										</table>
										
										<div style="text-align: left;" class="add_staff_lb">
											<button type="button" class="blue-btn submitbtnImg saa_btn add_user_accounts_perm_btn">
												<img class="inner_icon" src="images/button_icons/add-button.png" /> 
												<span class="inner_icon_txt">Add</span>
											</button>
											<span class="edit_div_hidden allowed_staff_hidden_div">	

												<div class="sa_dp_group_div">
													<select class="saa_dp sa_dp_sel">
														<option value="">--- Select ---</option>
														<?php
														foreach( $sa_arr as $sc_dp ){ 
															if ( !in_array($sc_dp['StaffID'], $exclude_sa_id_arr) ){
														?>
															<option value="<?php echo $sc_dp['StaffID'] ?>"><?php echo $crm->formatStaffName($sc_dp['FirstName'],$sc_dp['LastName']); ?></option>
														<?php		
															}
														}
														?>
													</select>
												</div>
												
												<button type="button" class="blue-btn submitbtnImg add_more_sa">
													<img class="inner_icon" src="images/button_icons/add-button.png" /> 
													<span class="inner_icon_txt">More</span>
												</button>
												<button type="button" class="blue-btn submitbtnImg btn_save_menu_sa_perm" data-menu_id="<?php echo $menu['menu_id']; ?>" data-denied="<?php echo $denied; ?>">
													<img class="inner_icon" src="images/button_icons/save-button.png" /> 
													Save
												</button>
											</span>
										</div>
										
									</div>						
								</div>
								
								
															
								<?php
									$link_txt = 'View ('.count($exclude_sa_id_arr).')';
									$link_class = 'jcolorItGreen';
								}else{ // empty
									$link_txt = 'Add'; 
									$link_class = '';
								?>
									
									<div style="display:none">								
										<div class="staff_div" id="<?php echo $jtable_id; ?>_allowed">
										
											<h2 class="staff_div_header">Allowed Staff</h2>											
											
											<div style="text-align: left;" class="add_staff_lb">
												<button type="button" class="blue-btn submitbtnImg saa_btn add_user_accounts_perm_btn">
													<img class="inner_icon" src="images/button_icons/add-button.png" /> 
													<span class="inner_icon_txt">Add</span>
												</button>
												<span class="edit_div_hidden allowed_staff_hidden_div">	

													<div class="sa_dp_group_div">
														<select class="saa_dp sa_dp_sel">
															<option value="">--- Select ---</option>
															<?php
															foreach( $sa_arr as $sc_dp ){ 
																if ( !in_array($sc_dp['StaffID'], $exclude_sa_id_arr) ){
															?>
																<option value="<?php echo $sc_dp['StaffID'] ?>"><?php echo $crm->formatStaffName($sc_dp['FirstName'],$sc_dp['LastName']); ?></option>
															<?php		
																}
															}
															?>
														</select>
													</div>
													
													<button type="button" class="blue-btn submitbtnImg add_more_sa">
														<img class="inner_icon" src="images/button_icons/add-button.png" /> 
														<span class="inner_icon_txt">More</span>
													</button>
													<button type="button" class="blue-btn submitbtnImg btn_save_menu_sa_perm" data-menu_id="<?php echo $menu['menu_id']; ?>" data-denied="<?php echo $denied; ?>">
														<img class="inner_icon" src="images/button_icons/save-button.png" /> 
														Save
													</button>
												</span>
											</div>
											
										</div>						
									</div>
									
								<?php
								}
								?>
								
								
								<a class="view_lightbox view_users_span <?php echo $link_class; ?>" href="#<?php echo $jtable_id; ?>_allowed"><?php echo $link_txt; ?></a>	
								
								
							</td>
							<td>
								
								<?php
								// DENIED STAFF
								$denied = 1;
								$sa_sql = getMenuAllowedStaffAccounts($menu['menu_id'],$denied);
								$exclude_sa_id_arr = [];
								if( mysql_num_rows($sa_sql)>0 ){ // list								
								?>
								<div style="display:none">								
									<div class="staff_div" id="<?php echo $jtable_id; ?>_denied">
									
										<h2 class="staff_div_header">Denied Staff</h2>
										<table class="jtable">
			
											<?php
											while( $sa = mysql_fetch_array($sa_sql) ){ ?>
												<tr>
													<td class="allowed_users_td"><?php echo "{$sa['FirstName']} {$sa['LastName']}"; ?></td>
													<td>
														<button type="button" class="submitbtnImg btn_remove_sa_menu">
															<img class="inner_icon" src="images/button_icons/cancel-button.png" /> 												
														</button>
														<input type="hidden" class="mpu_id" value="<?php echo $sa['mpu_id']; ?>" />
													</td>
												</tr>
											<?php
												$exclude_sa_id_arr[] = $sa['StaffID'];
											}
											?>
								
										</table>
										
										<div style="text-align: left;" class="add_staff_lb">
											<button type="button" class="blue-btn submitbtnImg saa_btn add_user_accounts_perm_btn">
												<img class="inner_icon" src="images/button_icons/add-button.png" /> 
												<span class="inner_icon_txt">Add</span>
											</button>
											<span class="edit_div_hidden allowed_staff_hidden_div">
											
												<div class="sa_dp_group_div">
													<select class="saa_dp">
														<option value="">--- Select ---</option>
														<?php
														foreach( $sa_arr as $sc_dp ){ 
															if ( !in_array($sc_dp['StaffID'], $exclude_sa_id_arr) ){ ?>
															<option value="<?php echo $sc_dp['StaffID'] ?>"><?php echo $crm->formatStaffName($sc_dp['FirstName'],$sc_dp['LastName']); ?></option>
														<?php	
															}
														}
														?>
													</select>
												</div>
												
												<button type="button" class="blue-btn submitbtnImg add_more_sa">
													<img class="inner_icon" src="images/button_icons/add-button.png" /> 
													<span class="inner_icon_txt">More</span>
												</button>
												<button type="button" class="blue-btn submitbtnImg btn_save_menu_sa_perm" data-menu_id="<?php echo $menu['menu_id']; ?>" data-denied="<?php echo $denied; ?>">
													<img class="inner_icon" src="images/button_icons/save-button.png" /> 
													Save
												</button>
											</span>
										</div>
										
									</div>						
								</div>
								
								
															
								<?php
									$link_txt = 'View ('.count($exclude_sa_id_arr).')';
									$link_class = 'jcolorItGreen';
								}else{ // empty
									$link_txt = 'Add'; 
									$link_class = '';
								?>
									
									<div style="display:none">								
										<div class="staff_div" id="<?php echo $jtable_id; ?>_denied">
										
											<h2 class="staff_div_header">Denied Staff</h2>											
											
											<div style="text-align: left;" class="add_staff_lb">
												<button type="button" class="blue-btn submitbtnImg saa_btn add_user_accounts_perm_btn">
													<img class="inner_icon" src="images/button_icons/add-button.png" /> 
													<span class="inner_icon_txt">Add</span>
												</button>
												
												
												<span class="edit_div_hidden allowed_staff_hidden_div">
												
													<div class="sa_dp_group_div">
														<select class="saa_dp">
															<option value="">--- Select ---</option>
															<?php
															foreach( $sa_arr as $sc_dp ){ 
															if ( !in_array($sc_dp['StaffID'], $exclude_sa_id_arr) ){ ?>
																<option value="<?php echo $sc_dp['StaffID'] ?>"><?php echo $crm->formatStaffName($sc_dp['FirstName'],$sc_dp['LastName']); ?></option>
															<?php		
																}
															}
															?>
														</select>
													</div>
													
													<button type="button" class="blue-btn submitbtnImg add_more_sa">
														<img class="inner_icon" src="images/button_icons/add-button.png" /> 
														<span class="inner_icon_txt">More</span>
													</button>
													<button type="button" class="blue-btn submitbtnImg btn_save_menu_sa_perm" data-menu_id="<?php echo $menu['menu_id']; ?>" data-denied="<?php echo $denied; ?>">
														<img class="inner_icon" src="images/button_icons/save-button.png" /> 
														Save
													</button>
												</span>
											</div>
											
										</div>						
									</div>
									
								<?php
								}
								?>
								
								
								<a class="view_lightbox view_users_span <?php echo $link_class ?>" href="#<?php echo $jtable_id; ?>_denied"><?php echo $link_txt; ?></a>	
								
								
							</td>
							
							<td>
								<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
								<div class="action_div">
									<button class="blue-btn submitbtnImg btn_update_menu">
										<img class="inner_icon" src="images/button_icons/save-button.png">
										Update
									</button>		
									<button class="submitbtnImg btn_cancel">
										<img class="inner_icon" src="images/button_icons/back-to-tech.png">
										Cancel
									</button>

								</div>
							</td>
							
						</tr>
						
						</tbody>
						<tbody class="crm_page_tbody">
						<?php
						// PAGES PER MENU
						$pages_sql = $crm->getPagesPerMenu($menu['menu_id'],$page_active);
						while( $page = mysql_fetch_array($pages_sql) ){
							$jtable_id = "jtable_page_{$page['crm_page_id']}";
						?>
						<tr class="toprow jalign_left crm_pages_row <?php echo ( is_numeric($page['active']) && $page['active']==0 )?'fadeOutText':''; ?>">
							<td class="page_name_td">
								<span class="txt_lbl"><?php echo $page['page_name']; ?></span>
								<span class="txt_hid">
									<table class="page_tbl">
										<tr>
											<td>Name</td>
											<td>
												<input type="text" style="margin:0; width: 88%;" class="addinput page_name" value="<?php echo $page['page_name']; ?>" />
											</td>
										</tr>
										<tr>
											<td>URL</td>
											<td>
												<input type="text" style="margin:0; width: 88%;" class="addinput page_url" value="<?php echo $page['page_url']; ?>" />
											</td>
										</tr>
										<tr>
											<td>Menu</td>
											<td>
												<select class="addinput page_menu">
													<option value="">--- Select ----</option>	
													<?php
													$page_menu_sql = $crm->getMenus();
													while( $page_menu = mysql_fetch_array($page_menu_sql) ){ ?>
														<option value="<?php echo $page_menu['menu_id']; ?>" <?php echo ( $page_menu['menu_id'] == $page['menu'] )?'selected="selected"':''; ?>><?php echo $page_menu['menu_name']; ?></option>
													<?php
													}
													?>
												</select>
											</td>
										</tr>
										<tr>
											<td>Active</td>
											<td>
												<select class="addinput page_isActive">
													<option value="">--- Select ----</option>
													<option value="0" <?php echo ( is_numeric($page['active']) && $page['active'] == 0 )?'selected="selected"':''; ?>>No</option>	
													<option value="1" <?php echo ( $page['active'] == 1 )?'selected="selected"':''; ?>>Yes</option>	
												</select>
											</td>
										</tr>
									</table>
								</span>
								<input type="hidden" class="crm_page_id" value="<?php echo $page['crm_page_id']; ?>" />
							</td>
							<?php
							$sc_sql = $crm->getAllStaffClasses();
							while( $sc = mysql_fetch_array($sc_sql) ){ ?>
								<td class="sa_chk_td">
									<input type="checkbox" title="<?php echo $sc['ClassName'] ?>" class="staff_class_chk page_staff_class_chk" <?php echo ( isPageAllowed_StaffClass($page['crm_page_id'],$sc['ClassID']) == true )?'checked="checked"':''; ?> value="<?php echo $sc['ClassID']; ?>" />
									<img class="green_check" src="/images/check_icon2.png">
								</td>
							<?php
							}
							?>
							
							
							<td>
							
								
								<?php
								// ALLOWED STAFF
								$denied = 0;								
								$sa_sql = getPageAllowedStaffAccounts($page['crm_page_id'],$denied);
								$exclude_sa_id_arr = [];
								if( mysql_num_rows($sa_sql)>0 ){ // list								
								?>
								<div style="display:none">								
									<div class="staff_div" id="<?php echo $jtable_id; ?>_allowed">
									
										<h2 class="staff_div_header">Allowed Staff</h2>
										<table class="jtable">
			
											<?php
											while( $sa = mysql_fetch_array($sa_sql) ){ ?>
												<tr>
													<td class="allowed_users_td"><?php echo "{$sa['FirstName']} {$sa['LastName']}"; ?></td>
													<td>
														<button type="button" class="submitbtnImg btn_remove_sa_page">
															<img class="inner_icon" src="images/button_icons/cancel-button.png" /> 												
														</button>
														<input type="hidden" class="cppu_id" value="<?php echo $sa['cppu_id']; ?>" />
													</td>
												</tr>
											<?php
												$exclude_sa_id_arr[] = $sa['StaffID'];
											}
											?>
								
										</table>
										
										<div style="text-align: left;" class="add_staff_lb">
											<button type="button" class="blue-btn submitbtnImg saa_btn add_user_accounts_perm_btn">
												<img class="inner_icon" src="images/button_icons/add-button.png" /> 
												<span class="inner_icon_txt">Add</span>
											</button>
											<span class="edit_div_hidden allowed_staff_hidden_div">
											
												<div class="sa_dp_group_div">
													<select class="saa_dp">
														<option value="">--- Select ---</option>
														<?php
														foreach( $sa_arr as $sc_dp ){ 
															if ( !in_array($sc_dp['StaffID'], $exclude_sa_id_arr) ){
														?>
															<option value="<?php echo $sc_dp['StaffID'] ?>"><?php echo $crm->formatStaffName($sc_dp['FirstName'],$sc_dp['LastName']); ?></option>
														<?php	
															}
														}
														?>
													</select>
												</div>
												
												<button type="button" class="blue-btn submitbtnImg add_more_sa">
													<img class="inner_icon" src="images/button_icons/add-button.png" /> 
													<span class="inner_icon_txt">More</span>
												</button>
												<button type="button" class="blue-btn submitbtnImg btn_save_page_sa_perm" data-page_id="<?php echo $page['crm_page_id']; ?>" data-denied="<?php echo $denied; ?>">
													<img class="inner_icon" src="images/button_icons/save-button.png" /> 
													Save
												</button>
											</span>
										</div>
										
									</div>						
								</div>
								
								
															
								<?php
									$link_txt = 'View ('.count($exclude_sa_id_arr).')';
									$link_class = 'jcolorItGreen';
								}else{ // empty
									$link_txt = 'Add'; 
									$link_class = '';
								?>
									
									<div style="display:none">								
									<div class="staff_div" id="<?php echo $jtable_id; ?>_allowed">
									
										<h2 class="staff_div_header">Allowed Staff</h2>			
										
										<div style="text-align: left;" class="add_staff_lb">
											<button type="button" class="blue-btn submitbtnImg saa_btn add_user_accounts_perm_btn">
												<img class="inner_icon" src="images/button_icons/add-button.png" /> 
												<span class="inner_icon_txt">Add</span>
											</button>
											<span class="edit_div_hidden allowed_staff_hidden_div">
											
												<div class="sa_dp_group_div">
													<select class="saa_dp">
														<option value="">--- Select ---</option>
														<?php
														foreach( $sa_arr as $sc_dp ){ 
															if ( !in_array($sc_dp['StaffID'], $exclude_sa_id_arr) ){
														?>
															<option value="<?php echo $sc_dp['StaffID'] ?>"><?php echo $crm->formatStaffName($sc_dp['FirstName'],$sc_dp['LastName']); ?></option>
														<?php	
															}
														}
														?>
													</select>
												</div>
												
												<button type="button" class="blue-btn submitbtnImg add_more_sa">
													<img class="inner_icon" src="images/button_icons/add-button.png" /> 
													<span class="inner_icon_txt">More</span>
												</button>
												<button type="button" class="blue-btn submitbtnImg btn_save_page_sa_perm" data-page_id="<?php echo $page['crm_page_id']; ?>" data-denied="<?php echo $denied; ?>">
													<img class="inner_icon" src="images/button_icons/save-button.png" /> 
													Save
												</button>
											</span>
										</div>
										
									</div>						
								</div>
									
								<?php
								}
								?>
								
								
								<a class="view_lightbox view_users_span <?php echo $link_class; ?>" href="#<?php echo $jtable_id; ?>_allowed"><?php echo $link_txt; ?></a>	
								
								
							</td>
							<td>
							
								
								<?php
								// Denied STAFF
								$denied = 1;								
								$sa_sql = getPageAllowedStaffAccounts($page['crm_page_id'],$denied);
								$exclude_sa_id_arr = [];
								if( mysql_num_rows($sa_sql)>0 ){ // list								
								?>
								<div style="display:none">								
									<div class="staff_div" id="<?php echo $jtable_id; ?>_denied">
									
										<h2 class="staff_div_header">Denied Staff</h2>
										<table class="jtable">
			
											<?php
											while( $sa = mysql_fetch_array($sa_sql) ){ ?>
												<tr>
													<td class="allowed_users_td"><?php echo "{$sa['FirstName']} {$sa['LastName']}"; ?></td>
													<td>
														<button type="button" class="submitbtnImg btn_remove_sa_page">
															<img class="inner_icon" src="images/button_icons/cancel-button.png" /> 												
														</button>
														<input type="hidden" class="cppu_id" value="<?php echo $sa['cppu_id']; ?>" />
													</td>
												</tr>
											<?php
												$exclude_sa_id_arr[] = $sa['StaffID'];
											}
											?>
								
										</table>
										
										<div style="text-align: left;" class="add_staff_lb">
											<button type="button" class="blue-btn submitbtnImg saa_btn add_user_accounts_perm_btn">
												<img class="inner_icon" src="images/button_icons/add-button.png" /> 
												<span class="inner_icon_txt">Add</span>
											</button>
											<span class="edit_div_hidden allowed_staff_hidden_div">
											
												<div class="sa_dp_group_div">
													<select class="saa_dp">
														<option value="">--- Select ---</option>
														<?php
														foreach( $sa_arr as $sc_dp ){ 
															if ( !in_array($sc_dp['StaffID'], $exclude_sa_id_arr) ){ ?>
															<option value="<?php echo $sc_dp['StaffID'] ?>"><?php echo $crm->formatStaffName($sc_dp['FirstName'],$sc_dp['LastName']); ?></option>
														<?php	
															}
														}
														?>
													</select>
												</div>	
												
												<button type="button" class="blue-btn submitbtnImg add_more_sa">
													<img class="inner_icon" src="images/button_icons/add-button.png" /> 
													<span class="inner_icon_txt">More</span>
												</button>												
												<button type="button" class="blue-btn submitbtnImg btn_save_page_sa_perm" data-page_id="<?php echo $page['crm_page_id']; ?>" data-denied="<?php echo $denied; ?>">
													<img class="inner_icon" src="images/button_icons/save-button.png" /> 
													Save
												</button>
											</span>
										</div>
										
									</div>						
								</div>
								
								
															
								<?php
									$link_txt = 'View ('.count($exclude_sa_id_arr).')';
									$link_class = 'jcolorItGreen';
								}else{ // empty
									$link_txt = 'Add'; 
									$link_class = '';
								?>
									
									<div style="display:none">								
									<div class="staff_div" id="<?php echo $jtable_id; ?>_denied">
									
										<h2 class="staff_div_header">Denied Staff</h2>			
										
										<div style="text-align: left;" class="add_staff_lb">
											<button type="button" class="blue-btn submitbtnImg saa_btn add_user_accounts_perm_btn">
												<img class="inner_icon" src="images/button_icons/add-button.png" /> 
												<span class="inner_icon_txt">Add</span>
											</button>
											<span class="edit_div_hidden allowed_staff_hidden_div">
											
												<div class="sa_dp_group_div">
													<select class="saa_dp">
														<option value="">--- Select ---</option>
														<?php
														foreach( $sa_arr as $sc_dp ){ 
															if ( !in_array($sc_dp['StaffID'], $exclude_sa_id_arr) ){ ?>
															<option value="<?php echo $sc_dp['StaffID'] ?>"><?php echo $crm->formatStaffName($sc_dp['FirstName'],$sc_dp['LastName']); ?></option>
														<?php	
															}
														}
														?>
													</select>
												</div>	
												
												<button type="button" class="blue-btn submitbtnImg add_more_sa">
													<img class="inner_icon" src="images/button_icons/add-button.png" /> 
													<span class="inner_icon_txt">More</span>
												</button>												
												<button type="button" class="blue-btn submitbtnImg btn_save_page_sa_perm" data-page_id="<?php echo $page['crm_page_id']; ?>" data-denied="<?php echo $denied; ?>">
													<img class="inner_icon" src="images/button_icons/save-button.png" /> 
													Save
												</button>
											</span>
										</div>
										
									</div>						
								</div>
									
								<?php
								}
								?>
								
								
								<a class="view_lightbox view_users_span <?php echo $link_class; ?>" href="#<?php echo $jtable_id; ?>_denied"><?php echo $link_txt; ?></a>	
								
								
							</td>
							
							<td>
								<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
								<div class="action_div">
								
									<button class="blue-btn submitbtnImg btn_update_page">
										<img class="inner_icon" src="images/button_icons/save-button.png">
										Update
									</button>									
									<button class="submitbtnImg btn_cancel">
										<img class="inner_icon" src="images/button_icons/back-to-tech.png">
										Cancel
									</button>
									

								</div>
							</td>
							
						</tr>
					<?php
						} 
					?>
					</tbody>
				<?php
					}
				}else{ ?>
					<tr class="toprow jalign_left"><td colspan="100%">Empty</td></tr>
				<?php
				}
				?>
				
					
			</table>
			
			<!-- ADD MENU FORM -->
			<div>
				<button type="button" id="add_menu_btn" class="blue-btn submitbtnImg sc_btn add_menu_btn" style="float: left; margin: 10px 0;">
					<img class="inner_icon" src="images/button_icons/add-button.png" /> 
					<span class="inner_icon_txt">Menu</span>
				</button>
			</div>
			
			<div style="clear:both;"></div>
			
			
			<div id="add_crm_menu_div">
			<form id="add_crm_menu_form" action="add_crm_menu.php" method="post">
			
				<table class="add_page_tbl">					
					<tr>
						<td>Menu Name</td>
						<td>
							<input type="text"  class="addinput menu_name" name="menu_name" id="menu_name" />
						</td>
					</tr>
				</table>
				
				
				<button type="button" id="save_menu_btn" class="blue-btn submitbtnImg sc_btn save_menu_btn" style="float: left; margin: 10px 0;">
					<img class="inner_icon" src="images/button_icons/save-button.png" /> 
					<span class="inner_icon_txt">Save</span>
				</button>
				
			</form>
			</div>
			
			<div style="clear:both;"></div>
			
			
			<!-- ADD PAGE FORM -->
			<div>
				<button type="button" id="add_page_btn" class="blue-btn submitbtnImg sc_btn add_page_btn" style="float: left; margin: 10px 0;">
					<img class="inner_icon" src="images/button_icons/add-button.png" /> 
					<span class="inner_icon_txt">Page</span>
				</button>
			</div>
			
			<div style="clear:both;"></div>
			
			
			<div id="add_crm_page_div">
			<form id="add_crm_page_form" action="add_crm_page.php" method="post">
			
				<table class="add_page_tbl">
					<tr>
						<td>Menu</td>
						<td>
							<select name="menu" id="menu" class="addinput" style="width: auto;">
								<option value="">--- Select ----</option>	
								<?php
								$menu_sql = $crm->getMenus();
								while( $menu = mysql_fetch_array($menu_sql) ){ ?>
									<option value="<?php echo $menu['menu_id']; ?>"><?php echo $menu['menu_name']; ?></option>
								<?php
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Page Name</td>
						<td>
							<input type="text"  class="addinput page_name" name="page_name" id="page_name" />
						</td>
					</tr>
					<tr>
						<td>Page Url</td>
						<td>
							<input type="text"  class="addinput page_url" name="page_url" id="page_url" />
						</td>
					</tr>
				</table>
				
				
				<button type="button" id="save_page_btn" class="blue-btn submitbtnImg sc_btn save_page_btn" style="float: left; margin: 10px 0;">
					<img class="inner_icon" src="images/button_icons/save-button.png" /> 
					<span class="inner_icon_txt">Save</span>
				</button>
				
			</form>
			</div>
		
		
		
	</div>
</div>

<br class="clearfloat" />

<script>
jQuery(document).ready(function(){
	
	
	
	
	jQuery(".header_menu").click(function(){
		
		var obj = jQuery(this);
		var tri_icon = obj.find(".triangle_icon");
		
		var tri_val = tri_icon.attr("data-icon_val");
		
		if( tri_val == 'down' ){
			tri_icon.attr("data-icon_val","up")
			tri_icon.attr("src","images/triangle_up.png");
		}else{
			tri_icon.attr("data-icon_val","down")
			tri_icon.attr("src","images/triangle_down.png");
		}
		
	});
	
	
	
	jQuery(".add_more_sa").click(function(){
		
		var obj = jQuery(this);
		var parent_div = obj.parents("div.add_staff_lb");
		var last_sa_dp = parent_div.find(".saa_dp:last");
		var sa_dp_clone = last_sa_dp.clone();
		
		parent_div.find(".sa_dp_group_div:first").append(sa_dp_clone);
		
	});
	
	
	
	<?php
	if( is_numeric($page_display) && $page_display == 0 ){ ?>
		jQuery(".crm_page_tbody").show();
	<?php
	}
	?>
	
	
	// menu page toggle
	jQuery(".crm_menu_row .header_menu").click(function(){
		
		jQuery(this).parents("tbody.crm_menu_tbody:first").next("tbody.crm_page_tbody").toggle();
		
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
		jQuery(this).parents("tr:first").find(".txt_hid").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".btn_edit").show();		
	});
	
	
	// update
	jQuery(".btn_update_menu").click(function(){
	
		var menu_id = jQuery(this).parents("tr:first").find(".menu_id").val();
		var menu_name = jQuery(this).parents("tr:first").find(".menu_name").val();		
		var error = "";
		
		if( menu_name == "" ){
			error += "Menu Name is required";
		}
		
		if(error != ""){
			alert(error);
		}else{			
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_menu.php",
				data: { 
					menu_id: menu_id,
					menu_name: menu_name
				}
			}).done(function( ret ) {
				window.location="<?php echo $url; ?>?menu_update=1";
			});	
						
		}		
		
	});
	
	
	// update
	jQuery(".btn_update_page").click(function(){
	
		var page_id = jQuery(this).parents("tr:first").find(".crm_page_id").val();
		var page_name = jQuery(this).parents("tr:first").find(".page_name").val();	
		var page_url = jQuery(this).parents("tr:first").find(".page_url").val();
		var menu = jQuery(this).parents("tr:first").find(".page_menu").val();
		var active = jQuery(this).parents("tr:first").find(".page_isActive").val();
		
		var error = "";
		
		if( page_name == "" ){
			error += "Page Name is required";
		}
		
		if( page_url == "" ){
			error += "Page Url is required";
		}
		
		if( menu == "" ){
			error += "Menu is required";
		}
		
		if( active == "" ){
			error += "Active field is required";
		}
		
		if(error != ""){
			alert(error);
		}else{			
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_crm_page.php",
				data: { 
					page_id: page_id,
					page_name: page_name,
					page_url: page_url,
					menu: menu,
					active: active
				}
			}).done(function( ret ) {
				window.location="<?php echo $url; ?>?page_update=1";
			});	
						
		}		
		
	});
	
	
	
	
	
	// trigger fancybox
	jQuery(".view_lightbox").fancybox();
	
	
	
	// Edit button
	jQuery(".add_user_accounts_perm_btn").click(function(){
		
		var btn_txt = jQuery(this).find(".inner_icon_txt").html();
		var orig_btn_txt = 'Add';
		var orig_btn_icon = 'images/button_icons/add-button.png';
		var cancel_btn_icon = 'images/button_icons/cancel-button.png';
		
		if( btn_txt == orig_btn_txt ){
			jQuery(this).removeClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html('Cancel');
			jQuery(this).find(".inner_icon").attr("src",cancel_btn_icon)
			jQuery(this).parents(".staff_div").find(".allowed_staff_hidden_div").show();
		}else{
			jQuery(this).addClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html(orig_btn_txt);
			jQuery(this).find(".inner_icon").attr("src",orig_btn_icon)
			jQuery(this).parents(".staff_div").find(".allowed_staff_hidden_div").hide();
		}
		
		
	});
	
	
	// MENU
	// STAFF ACCOUNTS
	// save staff class permission
	jQuery(".btn_save_menu_sa_perm").click(function(){
		
		var obj = jQuery(this);
		var staff_account_arr = [];
		
		obj.parents(".edit_div_hidden").find('.saa_dp').each(function(){
			
			var staff_account = jQuery(this).val();
			if( staff_account != '' ){
				staff_account_arr.push(staff_account);
			}			
			
		});
		
		//console.log(staff_account_arr);
		
		
		var menu_id = obj.attr('data-menu_id');
		var denied = obj.attr('data-denied');
		
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_saveMenuStaffAccountPermission.php",
			data: { 
				staff_account_arr: staff_account_arr,
				menu_id: menu_id,
				denied: denied
			}
		}).done(function( ret ){	
			window.location="<?php echo $url; ?>";
		});
		
		
		
	});
	
	
	// delete staff class permission
	jQuery(".btn_remove_sa_menu").click(function(){
		
		if( confirm("Are you sure you want to delete?") ){
			
			
			var obj = jQuery(this);
			var row = obj.parents("tr:first");
			
			var mpu_id = row.find('.mpu_id').val();
			
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
	
	
	
	
	
	// PAGE
	// STAFF ACCOUNTS
	// save staff class permission
	jQuery(".btn_save_page_sa_perm").click(function(){
		
		var obj = jQuery(this);
		var staff_account_arr = [];
		
		obj.parents(".edit_div_hidden").find('.saa_dp').each(function(){
			
			var staff_account = jQuery(this).val();
			if( staff_account != '' ){
				staff_account_arr.push(staff_account);
			}			
			
		});
		var page_id = obj.attr('data-page_id');
		var denied = obj.attr('data-denied');
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_savePageStaffAccountPermission.php",
			data: { 
				staff_account_arr: staff_account_arr,
				page_id: page_id,
				denied: denied
			}
		}).done(function( ret ){	
			window.location="<?php echo $url; ?>";
		});
		
		
	});
	
	
	// delete staff class permission
	jQuery(".btn_remove_sa_page").click(function(){
		
		if( confirm("Are you sure you want to delete?") ){
			
			
			var obj = jQuery(this);
			var row = obj.parents("tr:first");
			
			var cppu_id = row.find('.cppu_id').val();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_removePageStaffAccountPermission.php",
				data: { 
					cppu_id: cppu_id			
				}
			}).done(function( ret ){	
				window.location="<?php echo $url; ?>";
			});
			
			
		}
		
		
	});
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	// update menu staff class permission
	jQuery(".menu_staff_class_chk").change(function(){
		
		var obj = jQuery(this);
		var row = obj.parents("tr:first");
		
		var allow = ( obj.prop("checked") == true )?1:0;
		var staff_class = obj.val();
		var menu_id = row.find('.menu_id').val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_menu_staff_class_permission.php",
			data: { 
				staff_class: staff_class,
				menu_id: menu_id,	
				allow: allow
			}
		}).done(function( ret ){	
			obj.parents("td:first").find('.green_check').show();
			//window.location="<?php echo $url; ?>";
		});
		
		
	});
	
	
	
	// update page staff class permission
	jQuery(".page_staff_class_chk").change(function(){
		
		var obj = jQuery(this);
		var row = obj.parents("tr:first");
		
		var allow = ( obj.prop("checked") == true )?1:0;
		var staff_class = obj.val();
		var page_id = row.find('.crm_page_id').val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_page_staff_class_permission.php",
			data: { 
				staff_class: staff_class,
				page_id: page_id,	
				allow: allow
			}
		}).done(function( ret ){
			obj.parents("td:first").find('.green_check').show();
			//window.location="<?php echo $url; ?>";
		});
		
		
	});
	
	
	
	
	// add menu
	jQuery("#add_menu_btn").click(function(){
		
		var btn_txt = jQuery(this).find(".inner_icon_txt").html();
		var orig_btn_txt = 'Menu';
		var orig_btn_icon = 'images/button_icons/add-button.png';
		var cancel_btn_icon = 'images/button_icons/cancel-button.png';
		
		if( btn_txt == orig_btn_txt ){
			jQuery(this).removeClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html('Cancel');
			jQuery(this).find(".inner_icon").attr("src",cancel_btn_icon)
			jQuery("#add_crm_menu_div").show();
		}else{
			jQuery(this).addClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html(orig_btn_txt);
			jQuery(this).find(".inner_icon").attr("src",orig_btn_icon)
			jQuery("#add_crm_menu_div").hide();
		}
		
		
	});
	
	
	// save 
	jQuery("#save_menu_btn").click(function(){
		
		var menu_name = jQuery("#add_crm_menu_form #menu_name").val();
		var error = "";
		
		if( menu_name == "" ){
			error += "Menu Name is Required\n";
		}
		
		if( error != '' ){
			alert(error);
		}else{
			jQuery("#add_crm_menu_form").submit();
		}
		
	});
	
	
	
	
	// add page
	jQuery("#add_page_btn").click(function(){
		
		var btn_txt = jQuery(this).find(".inner_icon_txt").html();
		var orig_btn_txt = 'Page';
		var orig_btn_icon = 'images/button_icons/add-button.png';
		var cancel_btn_icon = 'images/button_icons/cancel-button.png';
		
		if( btn_txt == orig_btn_txt ){
			jQuery(this).removeClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html('Cancel');
			jQuery(this).find(".inner_icon").attr("src",cancel_btn_icon)
			jQuery("#add_crm_page_div").show();
		}else{
			jQuery(this).addClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html(orig_btn_txt);
			jQuery(this).find(".inner_icon").attr("src",orig_btn_icon)
			jQuery("#add_crm_page_div").hide();
		}
		
		
	});
	
	
	// save 
	jQuery("#save_page_btn").click(function(){
		
		var menu = jQuery("#add_crm_page_form #menu").val();
		var page_name = jQuery("#add_crm_page_form #page_name").val();
		var page_url = jQuery("#add_crm_page_form #page_url").val();
		var error = "";
		
		if( menu == "" ){
			error += "Menu is Required\n";
		}
		
		if( page_name == "" ){
			error += "Page Name is Required\n";
		}
		
		if( page_url == "" ){
			error += "Page Url is Required\n";
		}
		
		if( error != '' ){
			alert(error);
		}else{
			jQuery("#add_crm_page_form").submit();
		}
		
	});
	
});
</script>
</body>
</html>