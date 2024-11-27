<?
include('../inc/init_for_ajax.php');

if(is_numeric($_POST['job_id']))
{
    $alarm_id = addSingleAlarm($_POST);

    if(is_numeric($alarm_id))
    {
        $response = array("status" => "success", "data" => array("alarm_id" => $alarm_id, "appliance_number" => $_POST['appliance_number']));
    }
    else
    {
        $response = array("status" => "error", "message" => "Invalid Request");
    }
}
else
{
    $response = array("status" => "error", "message" => "Invalid Request");
}

echo json_encode($response);
?>
