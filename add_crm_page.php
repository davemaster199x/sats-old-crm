<?php

include('inc/init_for_ajax.php');

$menu = mysql_real_escape_string($_POST['menu']);
$page_name = mysql_real_escape_string($_POST['page_name']);
$page_url = mysql_real_escape_string($_POST['page_url']);

function getAllStaffClasses(){

	return mysql_query("
		SELECT *
		FROM `staff_classes`
	");
}


// add page
$sql = "
	INSERT INTO 
	`crm_pages`(
		`page_name`,
		`page_url`,
		`menu`
	)
	VALUES(
		'{$page_name}',
		'{$page_url}',
		{$menu}
	)
";
//echo "<br /><br />";

mysql_query($sql);
$page_id = mysql_insert_id();


// add staff class
$sc_sql = getAllStaffClasses();
while( $sc = mysql_fetch_array($sc_sql) ){
	
	$sql = "
		INSERT INTO 
		`crm_page_permission_class`(
			`page`,
			`staff_class`
		)
		VALUES(
			{$page_id},
			{$sc['ClassID']}
		)
	";
	//echo "<br />";

	mysql_query($sql);

}


header("location: menu_manager.php?page_success=1");

?>