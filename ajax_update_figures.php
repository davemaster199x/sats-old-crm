<?php

include('inc/init_for_ajax.php');

$figures_id = mysql_real_escape_string($_POST['figures_id']);

$month = mysql_real_escape_string($_POST['month']);
$year = mysql_real_escape_string($_POST['year']);
$working_days = ($_POST['working_days']!='')?mysql_real_escape_string($_POST['working_days']):'NULL';

$p_actual = ($_POST['p_actual']!='')?mysql_real_escape_string($_POST['p_actual']):'NULL';
$p_last_month = ($_POST['p_last_month']!='')?mysql_real_escape_string($_POST['p_last_month']):'NULL';

$ym = ($_POST['ym']!='')?mysql_real_escape_string($_POST['ym']):'NULL';
$of = ($_POST['of']!='')?mysql_real_escape_string($_POST['of']):'NULL';
$cot = ($_POST['cot']!='')?mysql_real_escape_string($_POST['cot']):'NULL';
$lr = ($_POST['lr']!='')?mysql_real_escape_string($_POST['lr']):'NULL';
$fr = ($_POST['fr']!='')?mysql_real_escape_string($_POST['fr']):'NULL';
$upgrades = ($_POST['upgrades']!='')?mysql_real_escape_string($_POST['upgrades']):'NULL';
$upgrades_income = ($_POST['upgrades_income']!='')?mysql_real_escape_string($_POST['upgrades_income']):'NULL';
$jobs_not_comp = ($_POST['jobs_not_comp']!='')?mysql_real_escape_string($_POST['jobs_not_comp']):'NULL';


$new_sales = ($_POST['new_sales']!='')?mysql_real_escape_string($_POST['new_sales']):'NULL';
$renewals = ($_POST['renewals']!='')?mysql_real_escape_string($_POST['renewals']):'NULL';
$budget = ($_POST['budget']!='')?mysql_real_escape_string($_POST['budget']):'NULL';
$actual = ($_POST['actual']!='')?mysql_real_escape_string($_POST['actual']):'NULL';
$prev_year = ($_POST['prev_year']!='')?mysql_real_escape_string($_POST['prev_year']):'NULL';
$techs = ($_POST['techs']!='')?mysql_real_escape_string($_POST['techs']):'NULL';
$annual = ($_POST['annual']!='')?mysql_real_escape_string($_POST['annual']):'NULL';


$sql_str = "
	UPDATE `figures` 
	SET
		`month` = '{$month}',
		`year` = '{$year}',
		`working_days` = {$working_days},
		
		`p_actual` = {$p_actual},
		`p_last_month` = {$p_last_month},
		
		`ym` = {$ym},
		`of` = {$of},
		`cot` = {$cot},
		`lr` = {$lr},
		`fr` = {$fr},
		`upgrades` = {$upgrades},
		`upgrades_income` = {$upgrades_income},
		`annual` = {$annual},
		
		`jobs_not_comp` = {$jobs_not_comp},
		
		`new_sales` = {$new_sales},
		`renewals` = {$renewals},
		`budget` = {$budget},
		`actual` = {$actual},
		`prev_year` = {$prev_year},
		`techs` = {$techs}
	WHERE `figures_id` = {$figures_id}
";
mysql_query($sql_str);

//header("location: /figures.php?success=1");

?>