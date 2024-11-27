<?php

include('inc/init.php');

$sucess = "";
$error = "";

if($_FILES){

	$vehicles_id = $_POST['vehicles_id']; 
	
	$folder = "vehicle_files";
	$full_path = $folder."/".$vehicles_id;
	
	if ($_FILES["file"]["error"] > 0){
		$error .= "Return Code: " . $_FILES["file"]["error"] . "<br>";	  
	}else{
		// limit file size to 4mb 
		if($_FILES["file"]["size"] > 4000000){
			$error .= "Uploaded file is too large, file size limit is 4mb<br />";
		}else{
			if(file_exists("{$full_path}/" . $_FILES["file"]["name"])){
				$error .= $_FILES["file"]["name"] . " already exists. ";
			}else{
				
				mysql_query("
					INSERT INTO
					`vehicle_files`(
						`vehicles_id`,
						`filename`,
						`path`,
						`date`
					)
					VALUES(
						'{$vehicles_id}',
						'{$_FILES['file']['name']}',
						'{$full_path}',						
						'".date("Y-m-d H:i:s")."'
					)
				");
			
				// if folder does not exist, make one
				if(!is_dir($folder)){
					mkdir($folder);
				}
				if(!is_dir($full_path)){
					mkdir($full_path);
				}
				// upload file
				if(move_uploaded_file($_FILES["file"]["tmp_name"],"{$full_path}/" . $_FILES["file"]["name"])){	
					$success = 3;
				}
			}
		}
	}
	
}

header("Location: /view_vehicle_details.php?id={$vehicles_id}&success={$success}&error={$error}");

?>