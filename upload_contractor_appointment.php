<?php

include('inc/init.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override.php');

$crm = new Sats_Crm_Class;

// data
$agency_id = mysql_real_escape_string($_REQUEST['hid_agency_id']);
$upload_file = $_FILES['upload_cont_app_frm'];
$country_id = $_SESSION['country_default'];


// UPLOAD
$file_type = ($upload_file['type']=='application/pdf')?'pdf':'image';
$uparams = array(
	'files' => $upload_file,
	'id' => $agency_id,
	'upload_folder' => 'agency_files',
	'file_type' => $file_type,
	'image_size' => 760,
	'offset_file_name' => 'caf'
);
// re-use expense file upload since this uses to upload image or pdf which is also the allowed file types in CAF
$upload_ret = $crm->ExpensesFileUpload($uparams);



$sql_str = "
	INSERT INTO
	`contractor_appointment` (
		`file_name`,
		`file_path`,
		`agency_id`,
		`country_id`
	)
	VALUES (
		'{$upload_ret['file_name']}',
		'{$upload_ret['path_to_file']}',
		'{$agency_id}',
		'{$country_id}'
	)
";

mysql_query($sql_str);


header("location: /view_agency_details.php?id={$agency_id}&ca_upload_success=1");


?>