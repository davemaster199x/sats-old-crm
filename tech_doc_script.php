<?php

include('inc/init.php');

$sucess = "";
$error = "";

if($_FILES){

	$title = $_POST['title'];
	$header = $_POST['header'];
	$country_folder = "/".strtolower($_SESSION['country_iso']);
	
	$folder = "technician_documents{$country_folder}";
	if ($_FILES["file"]["error"] > 0){
		$error .= "Return Code: " . $_FILES["file"]["error"] . "<br>";	  
	}else{
		// limit file size to 4mb 
		if($_FILES["file"]["size"] > 50000000){
			$error .= "Uploaded file is too large, file size limit is 50mb<br />";
		}else{
			if(file_exists("{$folder}/" . $_FILES["file"]["name"])){
				$error .= $_FILES["file"]["name"] . " already exists. ";
			}else{
				
				mysql_query("
					INSERT INTO
					`technician_documents`(
						`tech_doc_header_id`,
						`type`,
						`filename`,
						`path`,
						`title`,
						`date`
					)
					VALUES(
						'{$header}',
						1,
						'{$_FILES['file']['name']}',
						'{$folder}',
						'{$title}',
						'".date("Y-m-d H:i:s")."'
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

// is tech?
$is_tech = $_POST['is_tech'];
$page = ($is_tech==1)?'tech_doc_tech.php':'tech_doc.php';

header("Location: /{$page}?success={$success}&error={$error}");

?>