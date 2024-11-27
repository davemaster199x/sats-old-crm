<?php

include('inc/init_for_ajax.php');

$tech_id = $_GET['tech_id'];
$job_id = $_GET['tbl_maps'];

echo "sort order";

$i = 2;
foreach($job_id as $val){
	
	$temp = explode(":",$val);
	$map_type = $temp[0];
	$id = $temp[1];
	
	if($val!=""){
		
		// jobs
		if($map_type=="jobs_id"){
			
			if($id!=""){
				
				echo $sql = "
					UPDATE `jobs`
					SET `sort_order` = {$i}
					WHERE `id` = {$id}
				";
				mysql_query($sql);
				
			}
			
			
		}else{
				
			// key routes
			if($id!=""){
				
				echo $sql = "
					UPDATE `key_routes`
					SET `sort_order` = {$i}
					WHERE `key_routes_id` = {$id}
					AND `tech_id` = {$tech_id}
				";
				mysql_query($sql);
				
			}
					
		}
		
		$i++;
		
	}
	
	
	

}

?>