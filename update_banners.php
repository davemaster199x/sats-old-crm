<?php

include('inc/init_for_ajax.php');

$banner_id = mysql_real_escape_string($_POST['banner_id']);
$operation = mysql_real_escape_string($_POST['operation']);
$link = mysql_real_escape_string($_POST['link']);
$error = "";

if($operation=='link'){
	$str = "`link` = '{$link}'";
	$success = 1;
}else{
	
	if($_FILES){
		
		$folder = "agency_banners";
		if ($_FILES["file"]["error"] > 0){
			$error .= $_FILES["file"]["error"] . "<br>";	 
			$success = 0;			
		}else{
			// limit file size to 4mb 
			if($_FILES["file"]["size"] > 4000000){
				$error .= "Uploaded file is too large, file size limit is 4mb<br />";
				$success = 0;
			}else{

					$image_path = "{$folder}/{$_FILES['file']['name']}";
					
					$prepend_unique = 'banner_'.rand().date('YmdHis');
					$file_name = "{$prepend_unique}_{$_FILES['file']['name']}";
					$str = "`path` = '{$file_name}'";
				
					// if folder does not exist, make one
					if(!is_dir($folder)){
						mkdir($folder);
					}
					// upload file
					if(move_uploaded_file($_FILES["file"]["tmp_name"],"{$folder}/" . $file_name)){
						
						$success = 1;	
						
						// delete old banner
						$banner_sql = mysql_query("
							SELECT *
							FROM `banners`
							WHERE `banners_id` = {$banner_id}
						");
						$banner = mysql_fetch_array($banner_sql);
																
						if($banner['path']!=""){
							// file to delete
							$del_file = $_SERVER['DOCUMENT_ROOT']."{$folder}/".$banner['path'];	
							unlink($del_file);
						}
						
					}
				
			}
		}
		
	}
	
	
}

if($str!=""){
	// kms
	mysql_query("
		UPDATE `banners`
		SET {$str}
		WHERE `banners_id` = {$banner_id}
	");
}


header("location: banners.php?success={$success}&error={$error}");

?>