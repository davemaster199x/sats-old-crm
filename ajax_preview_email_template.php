<?php
include('inc/init_for_ajax.php');
$crm = new Sats_Crm_Class;

$job_id = $_POST['job_id'];
$agency_id = $_POST['agency_id'];
$subject = $_POST['subject'];
$body = $_POST['body'];

// parse tags
if( $agency_id!='' ){
	$jparams = array( 'agency_id' => $agency_id );
}else if( $job_id!='' ){
	$jparams = array( 'job_id' => $job_id );
}


$subject_parsed = $crm->parseEmailTemplateTags($jparams,$subject);
$body_parsed = $crm->parseEmailTemplateTags($jparams,$body);	

// PHP (server side)
$arr = array(
	"subject" => $subject_parsed,
	"body" => $body_parsed		
);
echo json_encode($arr);



?>