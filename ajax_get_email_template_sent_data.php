<?php
include('inc/init_for_ajax.php');
$crm = new Sats_Crm_Class;

$job_log_id = mysql_real_escape_string($_POST['job_log_id']);
$log_id = mysql_real_escape_string($_POST['log_id']);

$jparams = array( 
	'job_log_id' => $job_log_id,
	'log_id' => $log_id 
);
$ets_sql = $crm->getEmailTemplateSent($jparams);
$ets = mysql_fetch_array($ets_sql);


// PHP (server side)
$arr = array(
	"from_email" => $ets['from_email'],
	"to_email" => $ets['to_email'],
	"cc_email" => $ets['cc_email'],
	"subject" => $ets['subject'],
	"email_body" => $ets['email_body'],	
);
echo json_encode($arr);

?>