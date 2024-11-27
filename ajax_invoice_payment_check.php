<?php

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$property_id = mysql_real_escape_string($_POST['property_id']);

$job_filter = '';
$property_filter = '';

if( $job_id > 0 ){

    $job_filter = "
    AND j.`id` = {$job_id}
    ";

}

if( $property_id > 0 ){

    $property_filter = "
    AND p.`property_id` = {$property_id}
    ";
}


$inv_pay_sql_str = "
    SELECT COUNT(inv_pay.`invoice_payment_id`) AS inv_pay_count
    FROM `invoice_payments` AS inv_pay
    LEFT JOIN payment_types AS pt ON inv_pay.`type_of_payment` = pt.`payment_type_id`
    LEFT JOIN `jobs` AS j ON inv_pay.`job_id` = j.`id`
    LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
    WHERE j.`del_job` = 0
    AND inv_pay.`active` = 1
    {$job_filter}
    {$property_filter}
";

$inv_pay_sql = mysql_query($inv_pay_sql_str);
$inv_pay_row = mysql_fetch_array($inv_pay_sql);
echo $inv_pay_row['inv_pay_count'];

?>