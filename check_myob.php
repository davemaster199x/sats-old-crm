<?php 

include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');

// include myob class
include($_SERVER['DOCUMENT_ROOT'].'inc/myob_class.php');


// developer key
$client_key = '6bjp39s5bxhbvd7u6722g6un';
$secret_key = '7DWjQY5HyyxbmbCv76uk4Hre';

// tokens
$refresh_token = '568J!IAAAAFL90DnwCinfW0_FDhXA_R3jGtR-7UWd2gDNCq_6PhK4sQAAAAECwzf29BPZ-Zeh1POiTQuKRKrqlxzfrfGPX_oixTHgG04S9K11a3U5_QvZXcxBVvFnddxFj-CDbc9ShFUxmj0eDfNnRNyBYG6jWZrLqMmAVMD1H4D4aQPDQtH03U0j00c0EoRkKmmNEke8jykDhDhZMlSuWEHosLczxpj0x4PGUti8PpEPj3mnxiosOa27k4SX-eFn3JLwBMv5Njnd8fMDbvv2IgRQEZJ6TrOeqmvKlA';

// --- COMPANY FILE ---
// Smoke Alarm Testing Services Pty Ltd
//$cf_guid = '347e25eb-0091-43cf-972b-04332d2bed9a';

// SATS NZ Limited
//$cf_guid = 'b4abd991-eda5-4fb8-b8b3-ec9d0c21dd2e';

// API Sandbox Demo 183
$cf_guid = 'b227e581-c4b1-4e79-a474-a9636d2a5e39';

// admin - sandbox
$company_user = 'Administrator';
$company_pass = '';

$isSandBox = 1;

$filename = 'test_myob_file';
$uploaddir = $_SERVER['DOCUMENT_ROOT'].'myob_temp/';
$file_path = $uploaddir.$filename.".".csv;

echo "<h1>MYOB import test run</h1>";


// Intiate MYOB
$myob = new MYOB_Class($client_key,$secret_key,$refresh_token,$cf_guid,$company_user,$company_pass,$file_path,$isSandBox);

/*
echo $myob;
echo "<br />";
*/

/*
echo "<pre>";
print_r($myob->extractMYOBCsvFile());
echo "</pre>";
*/


/*
echo "<pre>";
print_r($myob->runChecks());
echo "</pre>";
*/


// run import
//$myob->import();


$myob->checkMyobForMarks();

header("location:/merged_jobs.php");


?>