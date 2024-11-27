<?php
include('inc/init_for_ajax.php');
include('inc/fpdf/fpdf.php');
include('inc/fpdf_override.php');

$job_arr = $_POST['job_id'];
if(isset($pdf)) unset($pdf);

foreach( $job_arr as $job_val ){
	
	// job id
	$job_id = $job_val;
	
	// pdf
	include('inc/pdfInvoiceCertComb.php');	
	include('inc/pdf_combined_template.php');	
	
	
	// then mark as printed
	$sql2 = "
		UPDATE `jobs`
		SET `is_printed` = 1
		WHERE `id` = {$job_id}
	";
	mysql_query($sql2);
	
	// job log
	$sql3_str = "
		INSERT INTO 
		`job_log` (
			`contact_type`,
			`eventdate`,
			`comments`,
			`job_id`, 
			`staff_id`,
			`eventtime`
		) 
		VALUES (
			'To Be Printed',
			'".date('Y-m-d')."',
			'Job has been printed',
			{$job_id}, 
			'".$_SESSION['USER_DETAILS']['StaffID']."',
			'".date('H:i')."'
		)
	";
	mysql_query($sql3_str);
	
	
}   

$pdf->Output();

?>
