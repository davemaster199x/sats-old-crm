<?php 

    include('init.php');
	
	$job_id = 111;
	
	$absolute_path = $_SERVER['DOCUMENT_ROOT'].'phpqrcode/temp/';
	$file_name = "invoice_{$job_id}_qr_code.png";
	
	echo $fin_path = $absolute_path.$file_name;
	
	echo "<br />";
	
	echo $data = "getpaidfaster.com.au/p 1=032513 2=281406 3=99.9 4=23122015 5=281406 6=12345 7=9.99 8=25593 9=54321";

    QRcode::png($data, $fin_path);
	
	
?>
    