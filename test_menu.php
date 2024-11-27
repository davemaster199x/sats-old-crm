<?php

$title = "Test Menu";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$url = 'test_menu.php';

$crm = new Sats_Crm_Class;


$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$staff_class = $_SESSION['USER_DETAILS']['ClassID'];

function getMenus(){

	return mysql_query("
		SELECT *
		FROM `menu` 
		WHERE `active` = 1
	");
}	


function menuPermission_staff_class($menu_id,$staff_class){
	
	$sql_str = "
		SELECT *
		FROM `menu_permission_class` 
		WHERE `active` = 1
		AND `menu` = {$menu_id}
		AND `staff_class` = {$staff_class}
	";
	$sql = mysql_query($sql_str);
	if( mysql_num_rows($sql)>0 ){
		return true;
	}else{
		return false;
	}
	
}


function menuPermission_user($menu_id,$staff_id){
	
	$sql_str = "
		SELECT *
		FROM `menu_permission_user` 
		WHERE `active` = 1
		AND `menu` = {$menu_id}
		AND `user` = {$staff_id}
	";
	$sql = mysql_query($sql_str);
	
	if( mysql_num_rows($sql)>0 ){
		return true;
	}else{
		return false;
	}
	
}



function getAllStaffClasses(){

	return mysql_query("
		SELECT *
		FROM `staff_classes`
	");

}




?>
<style>
.display_menu_dev{
	margin: 18px 0 1px 0;
}
.test_menu li:first-child{
	border: none;
}
.test_menu .menu-icon {
     margin: 0 5px 0 0;
}
.test_menu li.has-sub {
    border: 1px solid #cccccc !important;
	padding: 3px 4px !important;
	cursor: pointer;
}
.users_div{
	text-align: left;
}
.menu-icon_lbl{
	position: relative;
	top: 2px;
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
		
	
			
			
			<?php
			
			// no sort yet
			if($_REQUEST['sort']==""){
				$sort_arrow = 'up';
			}
			
			
			
			
			
			
			
			?>

	
<h2 class="heading">Menu Permission by Staff Class:</h2>			  
			
	

<div style="text-align: left;">		
<!-- STAFF CLASS -->				
<?php
$sql = getAllStaffClasses();
while( $row = mysql_fetch_array($sql) ){ ?>

	
	
		<button class="blue-btn submitbtnImg staff_class_btn" data-sc_id="<?php echo $row['ClassID']; ?>" type="button"><?php echo $row['ClassName'] ?></button>
		


<?php
}
?>	
</div>


<!-- MENU -->				
<div class="display_menu_dev staff_class_display_menu"></div>




<h2 class="heading">Menu Permission by User:</h2>
<?php
$staff_sql = mysql_query("
	SELECT * 
	FROM `staff_accounts`
	WHERE `Deleted` = 0
	AND `active` = 1
	ORDER BY `FirstName` ASC, `LastName` ASC  
");
?>
<div class="users_div">
	<label>Users: </label>
	<select id="staff_accounts">
		<option value="">--- Select ---</option>
		<?php
		while( $staff = mysql_fetch_array($staff_sql) ){ ?>
			<option value="<?php echo $staff['StaffID'] ?>"><?php echo $crm->formatStaffName($staff['FirstName'],$staff['LastName']) ?></option>
		<?php
		}
		?>
	</select>
</div>



<!-- MENU -->				
<div class="display_menu_dev users_display_menu"></div>
			

		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	jQuery(".staff_class_btn").click(function(){
		
		var staff_class_id = jQuery(this).attr("data-sc_id");
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_menu_dynamic_display.php",
			data: { 
				type: 'staff_class',
				staff_class_id: staff_class_id
			}
		}).done(function( ret ) {
			//window.location="/main.php";
			jQuery(".staff_class_display_menu").html(ret);
		});					
		
	});
	
	
	
	
	jQuery("#staff_accounts").change(function(){
		
		var staff_account_id = jQuery(this).val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_menu_dynamic_display.php",
			data: { 
				type: 'staff_account',
				staff_account_id: staff_account_id
			}
		}).done(function( ret ) {
			//window.location="/main.php";
			jQuery(".users_display_menu").html(ret);
		});					
		
	});
	
	
});
</script>
</body>
</html>