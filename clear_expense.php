<?php

include('inc/init_for_ajax.php');

// Leave Request Form
$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];

$sql_str = "
	DELETE
	FROM `expenses`
	WHERE `entered_by` ={$loggedin_staff_id}
	AND `expense_summary_id` IS NULL 
";
mysql_query($sql_str);

header("location: /expense.php?cleared=1");

?>