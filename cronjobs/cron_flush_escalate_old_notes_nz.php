<?php

include('server_hardcoded_values.php');

include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');

$country_id = 2;
$crm = new Sats_Crm_Class;

echo $a_sql_str = "
    SELECT 
        `agency_id`, 
        `save_notes`, 
        `escalate_notes` 
    FROM `agency` 
    WHERE `save_notes` = 1 
    AND `escalate_notes` != ''
    AND `status` = 'active' 
";
echo "<br /><br />";

$a_sql = mysql_query($a_sql_str);

while( $a_row = mysql_fetch_array($a_sql) ){

    $agency_id = $a_row['agency_id'];

    echo $j_sql_str = "            
        SELECT COUNT(j.`id`) AS jcount 
        FROM `jobs` AS `j` 
        LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id` 
        LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id` 
        WHERE `j`.`del_job` = 0 
        AND `p`.`deleted` = 0
        AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
        AND `a`.`status` = 'active' 
        AND `a`.`country_id` = {$country_id} 
        AND `a`.`agency_id` = {$agency_id}
        AND `j`.`status` = 'Escalate'
    ";
    echo "<br /><br />";

    $j_sql = mysql_query($j_sql_str);
    $j_row = mysql_fetch_array($j_sql);

    if( $j_row['jcount'] == 0 ){

        echo $update_agency_str = "
            UPDATE `agency`
            SET 
                `save_notes` = NULL,
                `escalate_notes` = NULL,
                `escalate_notes_ts` = NULL
            WHERE `agency_id` = {$agency_id}
        ";
        echo "<br /><br />";

        mysql_query($update_agency_str);

        echo $dele_sql_str = "
            DELETE
            FROM `escalate_agency_info`
            WHERE `agency_id` = {$agency_id}
        ";
        echo "<br /><br />";

        mysql_query($dele_sql_str);

    }        
    
}

?>