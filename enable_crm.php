<?php

echo $orig_path = "{$_SERVER['DOCUMENT_ROOT']}/inc";
echo "<br />";
echo $hide_path = "{$_SERVER['DOCUMENT_ROOT']}/inc/swiftmailer/lib/classes/Swift/Mailer";

rename("{$hide_path}/init_disabled.php","{$orig_path}/init.php");
rename("{$hide_path}/init_for_ajax_disabled.php","{$orig_path}/init_for_ajax.php");
rename("{$hide_path}/config_disabled.php","{$orig_path}/config.php");

?>
<h1>Enable CRM!</h1>