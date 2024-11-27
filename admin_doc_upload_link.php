<?php

include('inc/init.php');

$sucess = "";
$error = "";

$url = mysql_real_escape_string($_POST['url']);
$title = mysql_real_escape_string($_POST['title']);
$header = $_POST['header'];


mysql_query("
    INSERT INTO
    `admin_documents`(
        `type`,
        `admin_doc_header_id`,
        `url`,
        `title`,
        `date`
    )
    VALUES(
        2,
        '{$header}',
        '{$url}',
        '{$title}',
        '".date("Y-m-d H:i:s")."'
    )
");



header("Location: /admin_doc.php?success=1");

?>