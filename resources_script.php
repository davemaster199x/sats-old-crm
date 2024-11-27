<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;

$sucess = "";
$error = "";

if($_FILES){

	$title = mysql_real_escape_string($_POST['title']);
	$heading = $_POST['heading'];
	$states = implode(',',$_POST['states']);
	$country_folder = "/".strtolower($_SESSION['country_iso']);
	$due_date = mysql_real_escape_string($_POST['due_date']);
	$due_date2 = ($crm->isDateNotEmpty($due_date)==true)?"'".$crm->formatDate($due_date)."'":'NULL';
	
	$folder = "resources{$country_folder}";
    
    if($heading==""){
        $error .= "Heading must not be empty <br>";	 
    }else{
    
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
                        `resources`(
                            `type`,
                            `filename`,
                            `path`,
                            `title`,
                            {$state_field}
                            `date`,
                            `resources_header_id`,
                            `due_date`,
                            `country_id`
                        )
                        VALUES(
                            1,
                            '{$_FILES['file']['name']}',
                            '{$folder}',
                            '{$title}',
                            {$state_val}
                            '".date("Y-m-d H:i:s")."',
                            '{$heading}',
                            {$due_date2},
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
    
    
    
	
}

header("Location: resources.php?success={$success}&error={$error}");

?>