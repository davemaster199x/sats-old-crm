<?php
// THIS PAGE HAS CRON, ANY UPDATES SHOULD ALSO BE DONE THERE
include('server_hardcoded_values.php');

include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');
//include($_SERVER['DOCUMENT_ROOT'].'inc/ws_sms_class.php');

// $booked_with_new = '14';
$booked_with_new = 'a8ff0014-d0e0-473c-a060-46860913125e';
$jprop_id = '25';
$jid = '69';

function searcharray($value, $key, $array) {
   foreach ($array as $k => $val) {
       if ($val[$key] == $value) {
           return $k;
       }
   }
   return null;
}


$sqlGetTenant = mysql_query("SELECT * FROM `property_tenants` WHERE `id` = ".$booked_with_new." AND `property_id`=".$jprop_id);
if(mysql_num_rows($sqlGetTenant) > 0){
	$res = mysql_fetch_array($sqlGetTenant);
	$mobile = $res['tenant_mobile'];
} else {
	$sats_query = new Sats_query();
	$res = $sats_query->getTenantsFromPM_Job($jid)['ContactPersons'];
	$rsmobile = searcharray($booked_with_new, 'Id', $res);
	$mobile = $res[$rsmobile]['CellPhone'];
	$name = $res[$rsmobile]['FirstName'];

}

echo $mobile." - ".$name;
echo '<pre>'.print_r($res, TRUE).'</pre>';