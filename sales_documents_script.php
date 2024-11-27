<?php

include('inc/init.php');

$sucess = "";
$error = "";

if($_FILES){

	$title = $_POST['title'];
	$states = implode(',',$_POST['states']);
	$country_folder = "/".strtolower($_SESSION['country_iso']);
	
	$folder = "sales_documents{$country_folder}";
	if ($_FILES["file"]["error"] > 0){
		$error .= "Return Code: " . $_FILES["file"]["error"] . "<br>";	  
	}else{
		// limit file size to 4mb 
		if($_FILES["file"]["size"] > 4000000){
			$error .= "Uploaded file is too large, file size limit is 4mb<br />";
		}else{
			if(file_exists("{$folder}/" . $_FILES["file"]["name"])){
				$error .= $_FILES["file"]["name"] . " already exists. ";
			}else{
				
				if(ifCountryHasState($_SESSION['country_default'])==true){
					$state_field = '`states`,';
					$state_val = "'{$states}',";
				}else{
					$state_field = '';
					$state_val = '';
				}
				
				mysql_query("
					INSERT INTO
					`sales_documents`(
						`filename`,
						`path`,
						`title`,
						{$state_field}
						`date`,
						`country_id`
					)
					VALUES(
						'{$_FILES['file']['name']}',
						'{$folder}',
						'{$title}',
						{$state_val}
						'".date("Y-m-d H:i:s")."',
						{$_SESSION['country_default']}
					)
				");
			
				// if folder does not exist, make one
				if(!is_dir($folder)){
					mkdir($folder);
				}
				// upload file
				if(move_uploaded_file($_FILES["file"]["tmp_name"],"{$folder}/" . $_FILES["file"]["name"])){	
					$success = 1;
				}
				
			}
		}
	}
	
}

header("Location: sales_documents.php?success={$success}&error={$error}");

?>