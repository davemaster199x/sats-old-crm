<?
$title = "SATS Users";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class();

$logged_user_id = $_SESSION['USER_DETAILS']['StaffID'];
$logged_user_class = $_SESSION['USER_DETAILS']['ClassID'];

// global and full access
$allowed_user_class_to_edit_arr = array(2,9);
if( $_SESSION['country_default'] == 1 ){ // AU
	// Sarah Guthrie 
	$allowed_user_to_edit_arr = array(2226);
}else if( $_SESSION['country_default'] == 2 ){ // NZ
	$allowed_user_to_edit_arr = [];
}


$class_type = mysql_real_escape_string($_REQUEST['class_type']);
$show_all = mysql_real_escape_string($_REQUEST['show_all']);

// techs
$custom_select = '
	sa.`StaffID`,
	sa.`FirstName`,
	sa.`LastName`,
	sa.`sa_position`,
	sa.`ContactNumber`,
	sa.`Email`,
	sa.`Password`,
	sa.`password_new`,
	sa.`dha_card`,
	sa.`active`,

	cc.`FirstName` AS cc_fname,
	cc.`LastName` AS cc_lname
';
$sort_query = 'sa.`FirstName` ASC, sa.`LastName` ASC';

$tech_params = array(
	'custom_select' => $custom_select,
	'sort_query' => $sort_query,
	'class_id' => 6,
	'join_table' => 'cc',
	'display_echo' => 0
);

if( $show_all != 1 ){ // active
	$tech_params['active'] = 1;
	$tech_params['deleted'] = 0;
}

$tech_sql = $crm->getStaffAccountsData($tech_params);


// admin
$custom_select = '
	sa.`StaffID`,
	sa.`ClassID`,
	sa.`FirstName`,
	sa.`LastName`,
	sa.`sa_position`,
	sa.`ContactNumber`,
	sa.`Email`,
	sa.`active`,
	sa.`Password`,
	sa.`password_new`,

	sc.`ClassName`
';
$custom_where = 'AND sa.`ClassID` != 6';
$sort_query = 'sa.`FirstName` ASC, sa.`LastName` ASC';

$admin_params = array(
	'custom_select' => $custom_select,
	'sort_query' => $sort_query,
	'custom_where' => $custom_where,
	'display_echo' => 0
);

// class filter
if( $class_type != '' ){
	$admin_params['class_id'] = $class_type;
}

if( $show_all != 1 ){ // active
	$admin_params['active'] = 1;
	$admin_params['deleted'] = 0;
}
$admin_sql = $crm->getStaffAccountsData($admin_params);

?>
<style>
.hid_txt, .inactive_user{
	display:none;
}
.main_tr:hover{
	background-color: #fcbdb6 !important;
}
</style>
  <div id="mainContent">
  
   <div class="sats-middle-cont">
  
<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="SATS Users" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>SATS Users</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
   <?php
   if($_GET['success']==1){ ?>
		<div class="success">Update Successful</div>
   <?php
   }
   ?>
   
   <?php
   if($_GET['link_success']==1){ ?>
		<div class="success">CI Link Successful</div>
   <?php
   }
   ?>
   
	
   
   



<div id="tabs" class="c-tabs no-js">

	<div class="c-tabs-nav">
		<a href="#" data-tab_index="0" data-tab_name="techs" class="c-tabs-nav__link is-active">Techs</a>	
		<a href="#" data-tab_index="1" data-tab_name="admin" class="c-tabs-nav__link">Admin</a>		
	</div>

	<!--- TECHS --->
	<div class="c-tab is-active" data-tab_cont_name="techs">
		<div class="c-tab__content">
			
			<table cellspacing=1 cellpadding=5 width=100% class="table-left tbl-fr-red">
				<thead>
					<tr style="background-color:#b4151b;">
						<th>Name</th>
						<th>Position</th>
						<th>Schedule</th>
						<th>Mobile</th>
						<th>Email</th>
						<th>Vehicle</th>
						<th>ID</th>
						<th>Status</th>
						<th>Call Centre</th>
						<th>CI linked</th>
						<?php
						if( in_array($logged_user_class, $allowed_user_class_to_edit_arr) || in_array($logged_user_id, $allowed_user_to_edit_arr) ){ ?>
							<th>Edit</th>
						<?php
						}
						?>						
					</tr>
				</thead>
				<tbody>
				<?php
				while( $tech_row = mysql_fetch_array($tech_sql) ){ 
					$currentmonth = date("n");
					$currentyear = date("Y");
					?>
					<tr class="main_tr">
						<td>
							<?php echo "{$tech_row['FirstName']} {$tech_row['LastName']}"; ?>
						</td>
						<td>
							<?php echo $tech_row['sa_position']; ?>
						</td>
						<td>
						<?php 
						$crm_ci_page = "calendar/monthly_schedule_admin/{$tech_row['StaffID']}";
						$view_tech_url = $crm->crm_ci_redirect($crm_ci_page);
						?>
							<a href="<?php echo $view_tech_url ?>">
								View Schedule
							</a>
						</td>
						<td>
							<?php echo $tech_row['ContactNumber']; ?>
						</td>
						<td>
							<?php echo $tech_row['Email']; ?>
						</td>
						<td>
							<?php
								// vehicle
									$v_sql = mysql_query("
									SELECT *
									FROM `vehicles`
									WHERE `StaffID` = {$tech_row['StaffID']}
									LIMIT 0,1
								");

								if(mysql_num_rows($v_sql)>0){
									$v = mysql_fetch_array($v_sql);
									$v_str = '<a href="/view_vehicle_details.php?id='.$v['vehicles_id'].'">'.$v['number_plate'].'</a>';
								}else{
									$v_str = null;
								}
								echo $v_str;
							?>
						</td>
						<td>
							<?php echo $tech_row['StaffID']; ?>
						</td>
						<td>
							<?php echo ( $tech_row['active'] == 1 )?'Active':'Inactive'; ?>
						</td>
						<td>
							<?php echo "{$tech_row['cc_fname']} {$tech_row['cc_lname']}"; ?>
						</td>

						<td>
							<?php
							if( $tech_row['password_new'] != '' ){ ?>
								<span style="color:green;">Yes<span>
							<?php
							}else{ ?>
									<span style="color:red;">No<span>
							<?php 	
							}					
								$decryped_pass = $crm->descryptPassword($tech_row['Password']);
								$staff_id = $tech_row['StaffID'];
								$crm_ci_url =  $crm->getDynamicCiDomain()."/sys/link_user_to_ci?staff_id={$staff_id}&password=".rawurlencode($decryped_pass);
							?>
							<a href="<?php echo $crm_ci_url; ?>">
								<button type="button" id="btn_link_to_ci" class="submitbtnImg blue-btn">Link</button>
							</a>
						</td>

						<?php
						if( in_array($logged_user_class, $allowed_user_class_to_edit_arr) || in_array($logged_user_id, $allowed_user_to_edit_arr) ){ ?>
							<td>
								<a href="/sats_users_details.php?id=<?php echo $tech_row['StaffID'] ?>" class="view_more">Edit</a>
							</td>
						<?php
						}
						?>							
					</tr>
				<?php
				}
				?>					
				</tbody>
			</table>
			
		</div>
	</div>

	<!--- ADMINS -->
	<div class="c-tab accounts_tab_div"  data-tab_cont_name="admin">
		<div class="c-tab__content">
			
			
			<div class="aviw_drop-h aviw_drop-vp" style="border: 1px solid #ccc; border-bottom: none;">	
				<form method="post">
					<div class="fl-left">
						<label>Class:</label>
						<select name="class_type" class="class_type">
						<option value="">--- Select ---</option>
						<?php
						$custom_select = 'DISTINCT sa.`ClassID`, sc.`ClassName`';
						$sort_query = 'sc.`ClassName` ASC';
						$custom_where = 'AND sa.`ClassID` != 6';

						$tech_params = array(
						'custom_select' => $custom_select,
						'sort_query' => $sort_query,
						'custom_where' => $custom_where,
						'active' => 1,
						'deleted' => 0,
						'display_echo' => 0
						);
						$staff_class_sql = $crm->getStaffAccountsData($tech_params);
						while( $sc = mysql_fetch_array($staff_class_sql) ){ ?>
						<option value="<?php echo $sc['ClassID'] ?>" <?php echo ( $sc['ClassID'] == $class_type )?'selected="selected"':null; ?>><?php echo $sc['ClassName']; ?></option>
						<?php
						}
						?>
						</select>
					</div>							

					<div class="fl-left" style="float:left;">
						<input type="submit" class="submitbtnImg" value="Search" name="btn_search">
					</div>
					
				</form>			
			</div>
			

			<table cellspacing=1 cellpadding=5 width=100% class="table-left tbl-fr-red">
			<tr style="background-color:#b4151b;">
				<th>Name</th>
				<th>Position</th>
				<th>Phone</th>
				<th>Email</th>
				<th>Class</th>
				<th>ID</th>
				<th>Status</th>
				<th>Technicians</th>
				<th>CI linked</th>
				<?php
				if( in_array($logged_user_class, $allowed_user_class_to_edit_arr) || in_array($logged_user_id, $allowed_user_to_edit_arr) ){ ?>
					<th>Edit</th>
				<?php
				}
				?>		
			</tr>
			<?php
				$odd=0;

			   // (3) While there are still rows in the result set,
			   // fetch the current row into the array $row
			   while( $admin_row = mysql_fetch_array($admin_sql) ){

			   $odd++;
				if (is_odd($odd)) {
					$bgcolor = "bgcolor=#FFFFFF";		
					} else {
					//$bgcolor = "bgcolor=#eeeeee";
					}
					
				
				
				?>
				<tr <?php echo $bgcolor; ?> class="main_tr">
					<td>						
						<?php echo $admin_row['FirstName']." ".$admin_row['LastName'];?>								
					</td>
					<td>
						<?php echo $admin_row['sa_position'];?>
					</td>
					<td>
						<?php echo $admin_row['ContactNumber'];?>
					</td>
					<td>
						<?php echo $admin_row['Email'];?>
					</td>	
					<td>
						<?php echo $admin_row['ClassName'];?>
					</td>
					<td>
						<?php echo $admin_row['StaffID'];?>
					</td>
					<td>
						<?php echo ($admin_row['active']==1)?'Active':'Inactive'; ?>
					</td>
					<td>
						<?php
						// Call centre and OS call centre only
						if( $admin_row['ClassID'] == 7 || $admin_row['ClassID'] == 8 ){
						// get assigned techs
						$custom_select = '
						sa.`StaffID`,
						sa.`FirstName`,
						sa.`LastName`
						';
						$sort_query = 'sa.`FirstName` ASC, sa.`LastName` ASC';

						$ass_tech_params = array(
						'custom_select' => $custom_select,
						'sort_query' => $sort_query,
						'class_id' => 6,
						'active' => 1,
						'deleted' => 0,
						'assigned_cc' => $admin_row['StaffID'],
						'display_echo' => 0
						);
						$ass_tech_sql = $crm->getStaffAccountsData($ass_tech_params);						
						?>
						<ol class="assigned_tech_ol">
							<?php 
							while( $ass_tech_row = mysql_fetch_array($ass_tech_sql) ){ ?>
								<li>
									<?php echo $crm->formatStaffName($ass_tech_row['FirstName'],$ass_tech_row['LastName']); ?>	
								</li>
							<?php
							}
							?>							
						<ol>
						<?php
			   			}
						?>
					</td>		
					<td>
						<?php
						if( $admin_row['password_new'] != '' ){ ?>
							<span style="color:green;">Yes<span>
						<?php
						}else{ ?>
								<span style="color:red;">No<span>
						<?php 	
						}					
							$decryped_pass = $crm->descryptPassword($admin_row['Password']);
							$staff_id = $admin_row['StaffID'];
							$crm_ci_url =  $crm->getDynamicCiDomain()."/sys/link_user_to_ci?staff_id={$staff_id}&password=".rawurlencode($decryped_pass);
						?>
						<a href="<?php echo $crm_ci_url; ?>">
					  	<button type="button" id="btn_link_to_ci" class="submitbtnImg blue-btn">Link</button>
						</a>
					</td>			
					<?php
					if( in_array($logged_user_class, $allowed_user_class_to_edit_arr) || in_array($logged_user_id, $allowed_user_to_edit_arr) ){ ?>
						<td>
							<a href="/sats_users_details.php?id=<?php echo $admin_row['StaffID'] ?>" class="view_more">
								Edit
							</a>
						</td>
					<?php
					}
					?>		
				</tr>

				<? } ?>

			</table>


			
		</div>
	</div>
</div>


<div class="bottom-row" style="float: left; margin-right: 10px;">
	<a href="<?php $_SERVER['PHP_SELFT'] ?>?show_all=1">
		<button type="button" id="toggle_active_disp" class="submitbtnImg">
			Display All SATS Users
		</button>
	</a>
</div>


<div class="bottom-row" style="float:left;">
	<a href="/add_sats_user.php">
		<button type="button" class="submitbtnImg">
			Add SATS User
		</button>
	</a>
</div>


<script src="js/responsive_tabs.js"></script>
<script>
var myTabs = tabs({
el: '#tabs',
tabNavigationLinks: '.c-tabs-nav__link',
tabContentContainers: '.c-tab'
});

myTabs.init();
</script>


<div style="clear:both;">&nbsp;</div>

</div>

</div>

<br class="clearfloat">

<style>
.class_type{
	margin-left: 7px;
	height: auto;
}
.c-tab__content{
	height: auto !important;
}
.aviw_drop-h.aviw_drop-vp {
	padding: 10px;
}
.assigned_tech_ol{
	margin: 0;
    padding: 0 0 0 12px;
}
</style>
<script>
jQuery(document).ready(function(){


	// COOKIE 
	// selects the previous tab on load
	var curr_tab = $.cookie('user_details_tab_index');
	if( curr_tab!='' ){
		
		if(curr_tab!=''){
			myTabs.goToTab(curr_tab);
		}else{
			myTabs.goToTab(0);
		}
		
	}
	
	// keep tab script
	jQuery(".c-tabs-nav__link").click(function(){
		
		var tab_index = jQuery(this).attr('data-tab_index');
		//console.log(tab_index);
		$.cookie('user_details_tab_index', tab_index);
		
	});

});
</script>

</body>
</html>
