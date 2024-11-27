<?php
include('server_hardcoded_values.php');
include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');

$country_id = CURRENT_COUNTRY;

// 2 years old
$last_2_years = date('Y-m-d',strtotime('-2 years'));
// 7 days old
$last_7_days = date('Y-m-d',strtotime('-7 days'));
// 30 days old
$last_30_days = date('Y-m-d',strtotime('-30 days'));
// 1 years old
$last_1_year = date('Y-m-d',strtotime('-1 year'));
// 2 months old
$last_2_months = date('Y-m-d',strtotime('-2 months'));

// cron variables
$current_week = intval(date('W'));
$current_year = date('Y');

$run_delete = true;

// calendar
echo $sel_str = "
SELECT * 
FROM `calendar` 
WHERE `date_start` <= '{$last_2_years}'
";
echo "<br />";

// delete
if( $run_delete == true ){

    echo $del_str = "
    DELETE 
    FROM `calendar` 
    WHERE `date_start` <= '{$last_2_years}'
    ";
    echo "<br />";
    mysql_query($del_str);

    if( mysql_affected_rows() > 0 ){

        // insert cron logs
        $cron_type_id = 19; // Calendar Flush
        echo $cron_log = "INSERT INTO 
        cron_log (
            `type_id`, 
            `week_no`, 
            `year`, 
            `started`, 
            `finished`, 
            `country_id`
        ) 
        VALUES (
            {$cron_type_id},
            {$current_week}, 
            {$current_year}, 
            NOW(), 
            NOW(), 
            {$country_id}
        )
        ";
        echo "<br /><br />";
        mysql_query($cron_log);

    }    

}


// colour
echo $sel_str = "
SELECT * 
FROM `colour_table` AS ct 
LEFT JOIN `tech_run` AS tr ON ct.`tech_run_id` = tr.`tech_run_id`
WHERE tr.`date` <= '{$last_7_days}'
";
echo "<br />";
// delete
if( $run_delete == true ){

    echo $del_str = "
    DELETE ct
    FROM `colour_table` AS ct 
    LEFT JOIN `tech_run` AS tr ON ct.`tech_run_id` = tr.`tech_run_id`
    WHERE tr.`date` <= '{$last_7_days}'
    ";
    echo "<br />";
    mysql_query($del_str);

    if( mysql_affected_rows() > 0 ){

        // insert cron logs
        $cron_type_id = 20; // Tech Run Colour Flush
        echo $cron_log = "INSERT INTO 
        cron_log (
            `type_id`, 
            `week_no`, 
            `year`, 
            `started`, 
            `finished`, 
            `country_id`
        ) 
        VALUES (
            {$cron_type_id},
            {$current_week}, 
            {$current_year}, 
            NOW(), 
            NOW(), 
            {$country_id}
        )
        ";
        echo "<br /><br />";
        mysql_query($cron_log);

    }

}

// notifications
echo $sel_str = "
SELECT * 
FROM `notifications` 
WHERE CAST(`date_created` as Date) <= '{$last_30_days}'
";
echo "<br />";
// delete
if( $run_delete == true ){

    echo $del_str = "
    DELETE
    FROM `notifications` 
    WHERE CAST(`date_created` as Date) <= '{$last_30_days}'
    ";
    echo "<br />";
    mysql_query($del_str);

    if( mysql_affected_rows() > 0 ){

        // insert cron logs
        $cron_type_id = 21; // Notifications Flush
        echo $cron_log = "INSERT INTO 
        cron_log (
            `type_id`, 
            `week_no`, 
            `year`, 
            `started`, 
            `finished`, 
            `country_id`
        ) 
        VALUES (
            {$cron_type_id},
            {$current_week}, 
            {$current_year}, 
            NOW(), 
            NOW(), 
            {$country_id}
        )
        ";
        echo "<br /><br />";
        mysql_query($cron_log);

    }

}


// message
echo $sel_str = "
SELECT * 
FROM `message` 
WHERE CAST(`date` AS Date) <= '{$last_2_years}'
";
echo "<br />";
// delete
if( $run_delete == true ){

    echo $del_str = "
    DELETE
    FROM `message` 
    WHERE CAST(`date` AS Date) <= '{$last_2_years}'
    ";
    echo "<br />";
    mysql_query($del_str);

    if( mysql_affected_rows() > 0 ){

        // insert cron logs
        $cron_type_id = 22; // Message Flush
        echo $cron_log = "INSERT INTO 
        cron_log (
            `type_id`, 
            `week_no`, 
            `year`, 
            `started`, 
            `finished`, 
            `country_id`
        ) 
        VALUES (
            {$cron_type_id},
            {$current_week}, 
            {$current_year}, 
            NOW(), 
            NOW(), 
            {$country_id}
        )
        ";
        echo "<br /><br />";
        mysql_query($cron_log);

    }


}


// SMS API sent
echo $sel_str = "
SELECT * 
FROM `sms_api_sent` 
WHERE CAST(`created_date` AS Date) <= '{$last_30_days}'
";
echo "<br />";
// delete
if( $run_delete == true ){

    echo $del_str = "
    DELETE
    FROM `sms_api_sent` 
    WHERE CAST(`created_date` AS Date) <= '{$last_30_days}'
    ";
    echo "<br />";
    mysql_query($del_str);

    if( mysql_affected_rows() > 0 ){

        // insert cron logs
        $cron_type_id = 23; // SMS Sent Flush
        echo $cron_log = "INSERT INTO 
        cron_log (
            `type_id`, 
            `week_no`, 
            `year`, 
            `started`, 
            `finished`, 
            `country_id`
        ) 
        VALUES (
            {$cron_type_id},
            {$current_week}, 
            {$current_year}, 
            NOW(), 
            NOW(), 
            {$country_id}
        )
        ";
        echo "<br /><br />";
        mysql_query($cron_log);

    }

}

// SMS API replies
echo $sel_str = "
SELECT * 
FROM `sms_api_replies` 
WHERE CAST(`created_date` AS Date) <= '{$last_30_days}'
";
echo "<br />";
// delete
if( $run_delete == true ){

    echo $del_str = "
    DELETE 
    FROM `sms_api_replies` 
    WHERE CAST(`created_date` AS Date) <= '{$last_30_days}'
    ";
    echo "<br />";
    mysql_query($del_str);

    if( mysql_affected_rows() > 0 ){

         // insert cron logs
        $cron_type_id = 24; // SMS Replies Flush
        echo $cron_log = "INSERT INTO 
        cron_log (
            `type_id`, 
            `week_no`, 
            `year`, 
            `started`, 
            `finished`, 
            `country_id`
        ) 
        VALUES (
            {$cron_type_id},
            {$current_week}, 
            {$current_year}, 
            NOW(), 
            NOW(), 
            {$country_id}
        )
        ";
        echo "<br /><br />";
        mysql_query($cron_log);

    }

}


// KMS
echo $sel_str = "
SELECT * 
FROM `kms` 
WHERE CAST(`kms_updated` AS Date) <= '{$last_1_year}'
";
echo "<br />";
// delete
if( $run_delete == true ){

    $del_str = "
    DELETE 
    FROM `kms` 
    WHERE CAST(`kms_updated` AS Date) <= '{$last_1_year}'
    ";
    echo "<br />";
    mysql_query($del_str);

    if( mysql_affected_rows() > 0 ){

        // insert cron logs
        $cron_type_id = 25; // KMS Flush
        echo $cron_log = "INSERT INTO 
        cron_log (
            `type_id`, 
            `week_no`, 
            `year`, 
            `started`, 
            `finished`, 
            `country_id`
        ) 
        VALUES (
            {$cron_type_id},
            {$current_week}, 
            {$current_year}, 
            NOW(), 
            NOW(), 
            {$country_id}
        )
        ";
        echo "<br /><br />";
        mysql_query($cron_log);

    }

}



// stocktake
echo $tech_stock_sql_str = "
SELECT * 
FROM `tech_stock` 
WHERE CAST(`date` AS Date) <= '{$last_2_months}'
";
echo "<br />";
$tech_stock_sql = mysql_query($tech_stock_sql_str);

// delete
if( $run_delete == true ){

    while( $teck_stock_row = mysql_fetch_array($tech_stock_sql) ){


        if( $teck_stock_row['tech_stock_id'] > 0 ){

            // delete tech stock
            $del_str = "
            DELETE 
            FROM `tech_stock` 
            WHERE `tech_stock_id` = {$teck_stock_row['tech_stock_id']}
            ";
            echo "<br />";
            mysql_query($del_str);

            // delete tech stock items
            $del_str = "
            DELETE 
            FROM `tech_stock_items` 
            WHERE `tech_stock_id` = {$teck_stock_row['tech_stock_id']}
            ";
            echo "<br />";
            mysql_query($del_str);

        }        

    }
    

    if( mysql_affected_rows() > 0 ){

        // insert cron logs
        $cron_type_id = 26; // Tech Stocktake Flush
        echo $cron_log = "INSERT INTO 
        cron_log (
            `type_id`, 
            `week_no`, 
            `year`, 
            `started`, 
            `finished`, 
            `country_id`
        ) 
        VALUES (
            {$cron_type_id},
            {$current_week}, 
            {$current_year}, 
            NOW(), 
            NOW(), 
            {$country_id}
        )
        ";
        echo "<br /><br />";
        mysql_query($cron_log);

    }

}


?>