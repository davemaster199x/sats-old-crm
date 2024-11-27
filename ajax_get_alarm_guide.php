<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$search = mysql_real_escape_string($_REQUEST['search']);
$country_id = $_SESSION['country_default'];

// pagination
$offset = ($_REQUEST['offset']!="")?mysql_real_escape_string($_REQUEST['offset']):0;
$limit = mysql_real_escape_string($_REQUEST['order_by']);

$this_page = $_SERVER['PHP_SELF'];
$params = "&search={$search}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$order_by = mysql_real_escape_string($_REQUEST['order_by']);
$sort = mysql_real_escape_string($_REQUEST['sort']);

$jparams = array(
	'search' => $search,
	'country_id' => $country_id,
	'sort_list' => array(
		array(
			'order_by' => $order_by,
			'sort' => $sort
		)
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	)
);
$sa_sql = $crm->getSmokeAlarms($jparams);	

$jparams = array(
	'search' => $search,
	'country_id' => $country_id
);
$ptotal = mysql_num_rows($crm->getSmokeAlarms($jparams));



// get live notificationss
require_once 'inc_alarm_guide.php';

?>