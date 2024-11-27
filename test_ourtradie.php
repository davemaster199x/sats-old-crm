<?php 
$start = microtime(true);
include('inc/ourtradie_api_class.php'); 

$agency_id = 4226;
$api_id    = 6;

//echo "Properties";
//exit();

/*
$options = array(
    'client_id'		  => 'br6ucKvcPRqDNA1V2s7x',
    'client_secret'	  => 'd5YOJHb6EYRw5oypl73CJFWGLob5KB9A',
    'redirect_uri'		=> 'https://crmdev.sats.com.au/test_ourtradie.php'
    );

$api = new OurtradieApi($options, $_REQUEST);
$response = $api->authenticate();
*/
//print_r($_GET);
//exit();


$api = new OurtradieApi();
$access_token = "1ced52663bc364a2811cca7080dad17f19721ba5";

$ot_agency_id = 875026;

$token = array('access_token' => $access_token);

echo "<br /><br />";
print_r($agency_name);
echo "<br /><br />";

print_r($token);

//GetAgencies
$params = array(
    'Skip' 	 		=> 'No',
    'Count'     => 'No'
);
$agency = $api->query('GetAgencies', $params, '', $token, true);


$data_agency = array();
$data_agency = json_decode($agency, true);

$data['agency_list'] = array_filter($data_agency, function ($v) {
return $v !== 'OK';
});

echo "<br /><br />";
print_r($data['agency_list']);
echo "<br /><br />";


//GetAllResidentialProperties
$params = array(
    'Skip' 	 		=> 'No',
    'Count'     => 'No',
    'AgencyID'  => $ot_agency_id
);
$property = $api->query('GetAllResidentialProperties', $params, '', $token, true);

$data_property = array();
$data_property = json_decode($property, true);

$data['property_list'] = array_filter($data_property, function ($v) {
return $v !== 'OK';
});

echo "<br /><br />";
echo "<pre>";
print_r($data['property_list']);
echo "</pre>";
echo "<br /><br />";

exit();

/*
//Submit invoice
$params = array(
    'ID' 	 		=> 1134229
);
$status = $api->query('TradieJobSubmitInvoice', $params, '', $token, true);

print_r($status);
*/

/*
foreach ($data['agency_list'] as $key) {
    foreach ($key as $item) {

        if($item['AgencyName'] == $agency_name){
            $ot_agency_id = $item['AgencyID'];
        }
    }
}

echo $ot_agency_id;
//exit();

//GetAllResidentialProperties
$params = array(
    'Skip' 	 		=> 'No',
    'Count'     => 'No',
    'AgencyID'  => $ot_agency_id
);
$property = $api->query('GetAllResidentialProperties', $params, '', $token, true);

$data_property = array();
$data_property = json_decode($property, true);

$data['property_list'] = array_filter($data_property, function ($v) {
return $v !== 'OK';
});

//1332330
foreach($data['property_list'] as $ot_prop){
    foreach($ot_prop as $row){
        if($row['ID'] == 1332330){
            $tenants = $row['Tenant_Contacts'];
        }
    }
}

foreach($tenants as $item){
    echo "<br /><br />";
    print_r($item);
    echo "<br /><br />";
}

*/

?>
