<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$logged_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$country_id = $_SESSION['country_default'];

if($logged_staff_id){
    $data = array();
    $exclude = "'upgrades-brooks', 'upgrades-cavius', 'upgrades-emerald'";
    $result = mysql_query("SELECT `name`,`total_goal` FROM `main_page_total` WHERE `name` NOT IN($exclude)");
    while( $row = mysql_fetch_assoc($result) ){
        $name = $row['name'];
        if($name == 'upgrade-booked'){
            $label = 'Upgrades (Booked)';
        } else if($name == 'upgrade-completed'){
            $label = 'Upgrades (Completed) February';
        } else if($name == 'upgrade-to-be-booked'){
            $label = 'Upgrades (To be booked)';
        } else{
            $label = $name;
        }

        $data[] = [
            'name' => $row['name'],
            'total_goal' => $row['total_goal'],
            'label' => str_replace('-', ' ', ucwords($label))
        ];
    }
    echo json_encode($data);
}
?>