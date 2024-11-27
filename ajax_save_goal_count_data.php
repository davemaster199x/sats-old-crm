<?php

include('inc/init_for_ajax.php');
$crm = new Sats_Crm_Class;



$logged_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$country_id = $_SESSION['country_default'];
if($logged_staff_id){
    if(isset($_POST['to-be-booked'])){
        $name = 'to-be-booked';
        $total = mysql_real_escape_string($_POST['to-be-booked']);
        updateGoal($name, $total);
    }

    if(isset($_POST['fix-or-replace'])){
        $name = 'fix-or-replace';
        $total = mysql_real_escape_string($_POST['fix-or-replace']);
        updateGoal($name, $total);
    }

    if(isset($_POST['sales-properties-to-be-booked'])){
        $name = 'sales-properties-to-be-booked';
        $total = mysql_real_escape_string($_POST['sales-properties-to-be-booked']);
        updateGoal($name, $total);
    }

    if(isset($_POST['dha-to-be-booked'])){
        $name = 'dha-to-be-booked';
        $total = mysql_real_escape_string($_POST['dha-to-be-booked']);
        updateGoal($name, $total);
    }

    if(isset($_POST['dha-completed-last-365-days'])){
        $name = 'dha-completed-last-365-days';
        $total = mysql_real_escape_string($_POST['dha-completed-last-365-days']);
        updateGoal($name, $total);
    }

    if(isset($_POST['jobs-since-june-2021'])){
        $name = 'jobs-since-june-2021';
        $total = mysql_real_escape_string($_POST['jobs-since-june-2021']);
        updateGoal($name, $total);
    }

    if(isset($_POST['240v-rebook'])){
        $name = '240v-rebook';
        $total = mysql_real_escape_string($_POST['240v-rebook']);
        updateGoal($name, $total);
    }

    if(isset($_POST['electrician-only'])){
        $name = 'electrician-only';
        $total = mysql_real_escape_string($_POST['electrician-only']);
        updateGoal($name, $total);
    }

    if(isset($_POST['upgrade-booked'])){
        $name = 'upgrade-booked';
        $total = mysql_real_escape_string($_POST['upgrade-booked']);
        updateGoal($name, $total);
    }

    if(isset($_POST['upgrade-completed'])){
        $name = 'upgrade-completed';
        $total = mysql_real_escape_string($_POST['upgrade-completed']);
        updateGoal($name, $total);
    }

    if(isset($_POST['upgrade-to-be-booked'])){
        $name = 'upgrade-to-be-booked';
        $total = mysql_real_escape_string($_POST['upgrade-to-be-booked']);
        updateGoal($name, $total);
    }

    if(isset($_POST['nsw-overdue'])){
        $name = 'nsw-overdue';
        $total = mysql_real_escape_string($_POST['nsw-overdue']);
        updateGoal($name, $total);
    }

    if(isset($_POST['Monday'])){
        $name = 'Monday';
        $total = mysql_real_escape_string($_POST['Monday']);
        updateGoal($name, $total);
    }

    if(isset($_POST['Tuesday'])){
        $name = 'Tuesday';
        $total = mysql_real_escape_string($_POST['Tuesday']);
        updateGoal($name, $total);
    }

    if(isset($_POST['Wednesday'])){
        $name = 'Wednesday';
        $total = mysql_real_escape_string($_POST['Wednesday']);
        updateGoal($name, $total);
    }

    if(isset($_POST['Thursday'])){
        $name = 'Thursday';
        $total = mysql_real_escape_string($_POST['Thursday']);
        updateGoal($name, $total);
    }

    if(isset($_POST['Friday'])){
        $name = 'Friday';
        $total = mysql_real_escape_string($_POST['Friday']);
        updateGoal($name, $total);
    }

    if(isset($_POST['Saturday'])){
        $name = 'Saturday';
        $total = mysql_real_escape_string($_POST['Saturday']);
        updateGoal($name, $total);
    }
}

function updateGoal($name, $total){
    $sql_str = "UPDATE `main_page_total` SET `total_goal` = {$total} WHERE `name`='{$name}'";
    mysql_query($sql_str);
}
?>