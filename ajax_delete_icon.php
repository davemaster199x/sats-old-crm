<?php

include('inc/init_for_ajax.php');
$crm = new Sats_Crm_Class;

$icon_id = mysql_real_escape_string($_POST['icon_id']);
$doc_root = $_SERVER['DOCUMENT_ROOT'];

$sql = mysql_query("
	SELECT `icon`
	FROM `icons`
	WHERE `icon_id` = {$icon_id}
");
$row = mysql_fetch_array($sql);

$icon_path = $row['icon'];
echo $path_to_file = $doc_root.$icon_path;

// safer delete that's check the doc root
if( $icon_path != '' ){
	$crm->genericDeleteFile($path_to_file);
}

// delete db
mysql_query("
	DELETE 
	FROM `icons`
	WHERE `icon_id` = {$icon_id}
");

?>