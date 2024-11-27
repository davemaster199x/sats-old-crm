<?php

include('inc/init_for_ajax.php');

$tr_id = $_GET['tr_id'];
$trw_ids = $_GET['tbl_maps'];

techRunDragAndDropSort($tr_id,$trw_ids);

?>