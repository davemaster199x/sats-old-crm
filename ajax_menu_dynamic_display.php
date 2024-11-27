<?php

include('inc/init_for_ajax.php');

$type = mysql_real_escape_string($_REQUEST['type']);
$staff_class_id = mysql_real_escape_string($_REQUEST['staff_class_id']);
$staff_account_id = mysql_real_escape_string($_REQUEST['staff_account_id']);

function displayMenuByStaffClass($staff_class_id){
	$sql_str = "
		SELECT DISTINCT m.`menu_id`, m.`icon_class`, m.`menu_name`
		FROM `menu_permission_class` AS mpc
		LEFT JOIN `menu` AS m ON mpc.`menu` = m.`menu_id`
		WHERE mpc.`staff_class` = {$staff_class_id}
	";
	return mysql_query($sql_str);
}

function displayMenuByStaffAccount($staff_account_id){
	$sql_str = "
		SELECT DISTINCT m.`menu_id`, m.`icon_class`, m.`menu_name`
		FROM `menu_permission_user` AS mpu
		LEFT JOIN `menu` AS m ON mpu.`menu` = m.`menu_id`
		WHERE mpu.`user` = {$staff_account_id}
	";
	return mysql_query($sql_str);
}

if( $type == 'staff_class' ){
	$menu_sql = displayMenuByStaffClass($staff_class_id);
}else if( $type == 'staff_account' ){
	$menu_sql = displayMenuByStaffAccount($staff_account_id);
}

?>
<div id="cssmenu" class="sticky test_menu">	
	<ul>				
	<?php
	while( $menu = mysql_fetch_array($menu_sql) ){ 
	?>
		<li class="has-sub">
			<i class="menu-icon <?php echo $menu['icon_class']; ?>">&nbsp;</i>
			<span class="menu-icon_lbl"><?php echo $menu['menu_name']; ?></span>
		</li>
	<?php
		
	}
	?>
	</ul>	
</div>