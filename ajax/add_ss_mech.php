<?

# Basic check - ensure referring script was within domain.

$domain = $_SERVER['HTTP_HOST'];
$referer = $_SERVER['HTTP_REFERER'];

if(!stristr($referer, $domain)) exit();

# Include config
include('../inc/config.php');
include('../inc/functions.php');
include('../inc/alarm_functions.php');

# Connect to Database
$connection = mysql_connect(HOST, USERNAME, PASSWORD) or die("Could not connect to database");
mysql_select_db(DATABASE, $connection) or die("Unable to Select database");

if(is_numeric($_POST['job_id']))
{
    $_POST['appliance_number'] = getNextItemNumber($_POST['job_id'],4);

    if(addSingleSafetySwitch($_POST))
    {
        $insert_id = mysql_insert_id();
        $response = array("status" => "success", "data" => array("alarm_id" => $insert_id, "appliance_number" => $_POST['appliance_number']));
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
