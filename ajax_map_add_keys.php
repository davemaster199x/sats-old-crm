<?php 

include('inc/init_for_ajax.php');

// data
$keys_action = $_POST['keys_action'];
$keys_agency = $_POST['keys_agency'];
$tech_id = $_POST['tech_id'];
$date = $_POST['date'];
$sub_regions = $_POST['sub_regions'];

$job_count = getJobsTotalRoutes2($tech_id,$date,$sub_regions,$_SESSION['country_default']);
$key_count = getTotalKeyRoutes($tech_id,$date);

$last_index = ($job_count+$key_count)+2;

if($keys_action=="Pick Up & Drop Off"){
	
	$keys_array = array(
		'Pick Up',
		'Drop Off'
	);
	
	//$k = 2;
	foreach($keys_array as $val){
		
		mysql_query("
			INSERT INTO
			`key_routes`(
				`tech_id`,
				`date`,
				`action`,
				`agency_id`,
				`sort_order`
			)
			VALUES(
				{$tech_id},
				'{$date}',
				'{$val}',
				'{$keys_agency}',
				{$last_index}
			)
		");	

		$last_index++;
		
	}	
	
}else{
	
	mysql_query("
		INSERT INTO
		`key_routes`(
			`tech_id`,
			`date`,
			`action`,
			`agency_id`,
			`sort_order`
		)
		VALUES(
			{$tech_id},
			'{$date}',
			'{$keys_action}',
			'{$keys_agency}',
			{$last_index}
		)
	");
	
}

//$keys_success = 1;

?>