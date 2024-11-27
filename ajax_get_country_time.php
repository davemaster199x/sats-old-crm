<?php

include($_SERVER['DOCUMENT_ROOT'].'/inc/init_for_ajax.php');

// Set Timezone

// AU - QLD
$date = new DateTime('Australia/Brisbane');
$au_qld = $date->format('H:i');

// AU - NSW
$date = new DateTime('Australia/Sydney');
$au_nsw = $date->format('H:i');

// AU - SA
$date = new DateTime('Australia/Adelaide');
$au_sa = $date->format('H:i');

// AU - VIC
$date = new DateTime('Australia/Melbourne');
$au_vic = $date->format('H:i');

// NZ
$date = new DateTime('Pacific/Auckland');
$nz = $date->format('H:i');



$json_arr = array(
	"au_qld"=>$au_qld,
	"au_nsw"=>$au_nsw,
	"au_sa"=>$au_sa,
	"au_vic"=>$au_vic,
	"nz"=>$nz
);
echo json_encode($json_arr);

?>