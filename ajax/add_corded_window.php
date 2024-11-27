<?

# Basic check - ensure referring script was within domain.

$domain = $_SERVER['HTTP_HOST'];
$referer = $_SERVER['HTTP_REFERER'];

if(!stristr($referer, $domain)) exit();

# Include config
include('../inc/config.php');
include('../inc/functions.php');
include('../inc/alarm_functions.php');


function addSingleSafetySwitch2($data)
{
    $data = addSlashesData($data);

    $job_id = $data['job_id'];
	$new_location = $data['new_location'];    

    $query = "
	INSERT INTO 
	`corded_window` (
        `job_id`, 
		`location`
    ) 
	VALUES (
        {$job_id}, 
		'{$new_location}'
	)";

    if(mysql_query($query) or die(mysql_error()))
    {
        return true;
    }
    else
    {
        return false;
    }
}

# Connect to Database
$connection = mysql_connect(HOST, USERNAME, PASSWORD) or die("Could not connect to database");
mysql_select_db(DATABASE, $connection) or die("Unable to Select database");

if(is_numeric($_POST['job_id']))
{
    $_POST['appliance_number'] = getNextItemNumber($_POST['job_id'],4);

    if(addSingleSafetySwitch2($_POST))
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
