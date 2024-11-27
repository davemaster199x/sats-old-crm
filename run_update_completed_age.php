<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;
$country_id = $_SESSION['country_default'];

$from = date('Y-m-01');
$to = date('Y-m-t');

echo $job_sql_str = "
	SELECT `id`, CAST(`created` AS Date) AS j_created, `date`
	FROM `jobs` 
	WHERE `status` = 'Completed'
	AND `del_job` = 0
	AND `date` BETWEEN '{$from}' AND '{$to}'
	AND `completed_age` = 0
";
echo "<br /><br /><br />";


$job_sql = mysql_query($job_sql_str);
$num_row = mysql_num_rows($job_sql);

echo "Num Rows: {$num_row}<br /><br />";

while( $job = mysql_fetch_array($job_sql) ){
	
	$job_id = $job['id'];
	$jcreated = $job['j_created'];
	$job_date = $job['date'];
	
	echo "
	Job Created: {$jcreated}<br />
	Job Date: {$job_date}<br />
	";
	
	
	
	$date1=date_create(date('Y-m-d',strtotime($jcreated)));
	$date2=date_create(date('Y-m-d',strtotime($job_date)));
	

	
	
	$diff=date_diff($date1,$date2);
	$age = $diff->format("%r%a");
	$age2 = (((int)$age)!=0)?$age:0;
	
	echo $update_sql_str = "
		UPDATE `jobs` 
		SET `completed_age` = '{$age2}'
		WHERE `status` = 'Completed'
		AND `del_job` = 0
		AND `id` = {$job_id}
	";
	echo "<br /><br />";
	
	mysql_query($update_sql_str);
	
}

?>