<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;

$sucess = "";
$error = "";

if($_FILES){

	$icon = $_FILES['icon'];
	$page = mysql_real_escape_string($_POST['page']);
	$description = mysql_real_escape_string($_POST['description']);

	// upload
	$params = array(
		'files' => $icon,
		'upload_folder' => 'uploads/icons/',
		'filename_prefix' => 'icons_img',
		'image_only' => 1,
		'go_upload' => 1
	);
	$upload_ret = $crm->standardBasicUpload($params);	
	$icon_path = $upload_ret['server_upload_path'];
	$error_msg = $upload_ret['error_msg'];
	$error_arr_str = http_build_query(array('error' => $error_msg));
	$success = $upload_ret['upload_success'];
	$upload_msg = $upload_ret['upload_msg'];

	if( $upload_ret['upload_success'] == 1 ){
		mysql_query("
			INSERT INTO
			`icons`(
				`icon`,
				`page`,
				`description`,
				`date_created`
			)
			VALUES(
				'{$icon_path}',
				'{$page}',
				'{$description}',
				'".date("Y-m-d H:i:s")."'
			)
		");
	}
	
	
	
}

header("Location: /icons.php?success={$success}&{$error_arr_str}");

?>