<?php

include('inc/init_for_ajax.php');

$menu_name = mysql_real_escape_string($_POST['menu_name']);

function getAllStaffClasses(){

	return mysql_query("
		SELECT *
		FROM `staff_classes`
	");
}


// add menu
$sql = "
	INSERT INTO 
	`menu`(
		`menu_name`
	)
	VALUES(
		'{$menu_name}'
	)
";
//echo "<br /><br />";

mysql_query($sql);
$menu_id = mysql_insert_id();


// add staff class
$sc_sql = getAllStaffClasses();
while( $sc = mysql_fetch_array($sc_sql) ){
	
	$sql = "
		INSERT INTO 
		`menu_permission_class`(
			`menu`,
			`staff_class`
		)
		VALUES(
			{$menu_id},
			{$sc['ClassID']}
		)
	";
	//echo "<br />";

	mysql_query($sql);

}


header("location: menu_manager.php?menu_success=1");

?>