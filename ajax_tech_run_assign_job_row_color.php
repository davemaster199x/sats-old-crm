<?php

include('inc/init_for_ajax.php');

// data
$tr_id = mysql_real_escape_string($_POST['tr_id']);
$trr_id_arr = $_POST['trr_id_arr'];
$trr_hl_color = mysql_real_escape_string($_POST['trr_hl_color']);

assignTechRunPinColors($trr_id_arr,$trr_hl_color,$tr_id)

?>