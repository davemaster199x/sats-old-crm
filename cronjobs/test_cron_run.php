<?php
include('server_hardcoded_values.php');
include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');

$country_id = 1;

$to = "vaultdweller123@gmail.com, danielk@sats.com.au, digipac.test@gmail.com";
//$to = "vaultdweller123@gmail.com";
$subject = "Test Cron";

$message = "
<html>
<head>
<title>HTML email</title>
</head>
<body>
	<p>Test Cron Run</p>
</body>
</html>
";

// Always set content-type when sending HTML email
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
$headers .= 'From: info@sats.com.au' . "\r\n";
//$headers .= 'Cc: myboss@example.com' . "\r\n";

mail($to,$subject,$message,$headers);
?>


