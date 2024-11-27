<?php

$title = "Test Visibility";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$url = 'test_visibility.php';

// Initiate job class
$crm = new Sats_Crm_Class;

$sc_id = $_SESSION['USER_DETAILS']['ClassID'];
$sa_id = $_SESSION['USER_DETAILS']['StaffID'];

	
function getMenus(){

	return mysql_query("
		SELECT *
		FROM `menu` 
		WHERE `active` = 1
	");
}	


function getPagesPerMenu($menu_id){
	$sql = "
		SELECT *
		FROM `crm_pages` AS cp
		WHERE cp.`active`
		AND cp.`menu` = {$menu_id}
	";
	return mysql_query($sql);
}

// MENU
function canViewMenuByStaffClass($menu_id,$sc_id){
	$sql_str = "
		SELECT *
		FROM `menu_permission_class`
		WHERE `active`
		AND `menu` = {$menu_id}
		AND `staff_class` = {$sc_id}
	";
	$sql = mysql_query($sql_str);
	if( mysql_num_rows($sql) > 0 ){
		return true;
	}else{
		return false;
	}
}


function canViewMenuByStaffAccounts($menu_id,$sa_id,$denied){
	$sql_str = "
		SELECT *
		FROM `menu_permission_user`
		WHERE `active`
		AND `menu` = {$menu_id}
		AND `user` = {$sa_id}
		AND `denied` = {$denied}
	";
	$sql = mysql_query($sql_str);
	if( mysql_num_rows($sql) > 0 ){
		return true;
	}else{
		return false;
	}
}


// PAGES
function canViewPageByStaffClass($page_id,$sc_id){
	$sql_str = "
		SELECT *
		FROM `crm_page_permission_class`
		WHERE `active`
		AND `page` = {$page_id}
		AND `staff_class` = {$sc_id}
	";
	$sql = mysql_query($sql_str);
	if( mysql_num_rows($sql) > 0 ){
		return true;
	}else{
		return false;
	}
}


function canViewPageByStaffAccounts($page_id,$sa_id,$denied){
	$sql_str = "
		SELECT *
		FROM `crm_page_permission_user`
		WHERE `active`
		AND `page` = {$page_id}
		AND `user` = {$sa_id}
		AND `denied` = {$denied}
	";
	$sql = mysql_query($sql_str);
	if( mysql_num_rows($sql) > 0 ){
		return true;
	}else{
		return false;
	}
}	

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
		
		
		<form method="POST" name='example' id='example' style="display: none;">
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

			
			<table id="site_map_tbl" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin: 0px;">			
				
				<thead>
					<tr class="toprow jalign_left">
						<th>Menu/Pages</th>	
					</tr>
				</thead>
				
				
				<?php
				// MENU
				$menu_sql = getMenus();
				if(mysql_num_rows($menu_sql)>0){ 
					while( $menu = mysql_fetch_array($menu_sql) ){ 
					
					if(  
						canViewMenuByStaffClass($menu['menu_id'],$sc_id) == true ||  
						canViewMenuByStaffAccounts($menu['menu_id'],$sa_id,0) == true ||
						canViewMenuByStaffAccounts($menu['menu_id'],$sa_id,1) == false
					){
					
					$jtable_id = "jtable_menu_{$menu['menu_id']}";
					?>
						<tbody class="crm_menu_tbody">
						<tr class="toprow jalign_left jmain_row crm_menu_row">
							<td class="header_menu">
								<span class="txt_lbl"><strong><?php echo $menu['menu_name']; ?></strong></span>
								<span class="txt_hid"><input type="text" style="margin: 0;" class="addinput menu_name" value="<?php echo $menu['menu_name']; ?>" /></span>
								<input type="hidden" class="menu_id" value="<?php echo $menu['menu_id']; ?>" />
							</td>
			
						</tr>
						
						</tbody>
						<tbody class="crm_page_tbody">
						<?php
						// PAGES PER MENU
						$pages_sql = getPagesPerMenu($menu['menu_id']);
						while( $page = mysql_fetch_array($pages_sql) ){
							
							
							if(  
								canViewPageByStaffClass($page['crm_page_id'],$sc_id) == true ||  
								canViewPageByStaffAccounts($page['crm_page_id'],$sa_id,0) == true || 
								canViewPageByStaffAccounts($page['crm_page_id'],$sa_id,1) == false
							){
							
							$jtable_id = "jtable_page_{$page['crm_page_id']}";
						?>
						<tr class="toprow jalign_left crm_pages_row">
							<td class="page_name_td">
								<span class="txt_lbl"><a href="<?php echo $page['page_url']; ?>" target="_blank"><?php echo $page['page_name']; ?></a></span>
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
									</table>
								</span>
								<input type="hidden" class="crm_page_id" value="<?php echo $page['crm_page_id']; ?>" />
							</td>
	
							
						</tr>
					<?php
							}
						} 
					?>
					</tbody>
					
					
				<?php
						}
					}
				}else{ ?>
					<tr class="toprow jalign_left"><td colspan="100%">Empty</td></tr>
				<?php
				}
				?>
				
					
			</table>
			

		
	</div>
</div>

<br class="clearfloat" />

<script>
jQuery(document).ready(function(){
	
	// row highlight
	setInterval(function(){ 
		jQuery("#site_map_tbl tr").removeClass('jredHighlightRow');
	}, 10000);
	
	
	// row highlight
	jQuery(".staff_class_chk").click(function(){
		
		jQuery(this).parents("tr:first").addClass('jredHighlightRow');
		
	});
	
	
	// menu page toggle
	jQuery(".crm_menu_row .header_menu").click(function(){
		
		jQuery(this).parents("tbody.crm_menu_tbody:first").next("tbody.crm_page_tbody").toggle();
		
	});
	
	
	
});
</script>
</body>
</html>