<?
# Basic check - ensure referring script was within domain.

$domain = $_SERVER['HTTP_HOST'];
$referer = $_SERVER['HTTP_REFERER'];

if(!stristr($referer, $domain)) exit();

# Include config
include('../inc/init.php');

# Connect to Database
$connection = mysql_connect(HOST, USERNAME, PASSWORD) or die("Could not connect to database");
mysql_select_db(DATABASE, $connection) or die("Unable to Select database");


function addJobLog2($params)
{
	
	if( $params->unavailable ==1 ){
		
		$jcomments = $params->comments.' - <span style="font-weight: bold;">Unavailable '.date('d/m/Y',strtotime($params->unavailable_date)).'</span>';
		// update job
		mysql_query("
			UPDATE `jobs`
			SET 
				`unavailable` = {$params->unavailable},
				`unavailable_date` = '{$params->unavailable_date}'
			WHERE `id` = {$params->job_id}
			
		");
		
	}else{
		$jcomments = $params->comments;
	}
	
	$insertQuery = "INSERT INTO job_log ( contact_type,eventdate,comments,job_id, staff_id, eventtime, `important` ) VALUES ( '{$params->contact_type}','{$params->eventdate}','{$jcomments}','{$params->job_id}', '{$params->staff_id}', '".date('H:i')."', '{$params->important}' )";

	return mysql_query($insertQuery);
	
	
}


# Update tech job table order
if($_POST['UpdateTable'] == 1 && $_POST['Serial'])
{
	preg_match_all("/tech_schedule\[\]=([0-9]+)/", $_POST['Serial'], $output);
	
	if(is_array($output[1]))
	{
		for($x = 0; $x < sizeof($output[1]); $x++)
		{
			$order = $x + 1;
			$query = "UPDATE jobs SET sort_order = " . $order . " WHERE id = " . $output[1][$x] . " LIMIT 1";
			mysql_query($query);
		}
	}

	exit();
}

# Accept a job contact log
if($_POST['JobContactLog'])
{
  $params = new StdClass;
  $params->contact_type = addslashes(stripslashes($_POST['contact_type']));
  $params->eventdate = convertDate($_POST['date']);
  $params->comments = addslashes(stripslashes($_POST['comments']));
  $params->job_id = addslashes(stripslashes($_POST['job_id']));
  $params->staff_id = addslashes(stripslashes($_SESSION['USER_DETAILS']['StaffID']));
  $params->eventtime = addslashes(stripslashes($_POST['time']));
  $params->important = addslashes(stripslashes($_POST['important']));
  $params->unavailable = addslashes(stripslashes($_POST['unavailable']));
  $params->unavailable_date = convertDate($_POST['unavailable_date']);

  if(addJobLog2($params))
  {
    $response = array("success" => 1);
  }
  else
  {
  	$response = array("success" => 0, "message" => "An error occurred creating the event, please report");
  }

  echo json_encode($response);
  exit();
}

# Update cal filter
if($_POST['UpdateCalFilter'])
{
    if(!isset($_SESSION['USER_DETAILS']['StaffID']))
    {
        exit();
    }
    else
    {
        $serialized = addslashes(trim($_POST['serialized'], ","));

        #Update or insert
        $query = "SELECT StaffId FROM cal_filters WHERE StaffId = '" . $_SESSION['USER_DETAILS']['StaffID'] . "' LIMIT 1";
        $result = mysqlSingleRow($query);

        if(isset($result['StaffId']))
        {
            $query = "UPDATE cal_filters SET StaffFilter = '" . $serialized . "' WHERE StaffId = '" . $_SESSION['USER_DETAILS']['StaffID'] . "' LIMIT 1";
        }
        else
        {
            $query = "INSERT INTO cal_filters (StaffId, StaffFilter) VALUES ('" . $_SESSION['USER_DETAILS']['StaffID'] . "', '" . $serialized . "')";
        }

        mysql_query($query);
        $response = array("success" => 1);

        echo json_encode($response);
        exit();
    }
}


if($_POST['UpdateCalStaffClassFilter'])
{
    if(!isset($_SESSION['USER_DETAILS']['StaffID']))
    {
        exit();
    }
    else
    {
		$sc_serialized = addslashes(trim($_POST['sc_serialized'], ","));

        #Update or insert
        $query = "SELECT StaffId FROM cal_filters WHERE StaffId = '" . $_SESSION['USER_DETAILS']['StaffID'] . "' LIMIT 1";
        $result = mysqlSingleRow($query);

        if(isset($result['StaffId']))
        {
            $query = "UPDATE cal_filters SET `staff_class_filter`='".$sc_serialized."' WHERE StaffId = '" . $_SESSION['USER_DETAILS']['StaffID'] . "' LIMIT 1";
        }
        else
        {
            $query = "INSERT INTO cal_filters (StaffId, `staff_class_filter`) VALUES ('" . $_SESSION['USER_DETAILS']['StaffID'] . "', '".$sc_serialized."')";
        }

        mysql_query($query);
        $response = array("success" => 1);

        echo json_encode($response);
        exit();
    }
}


if($_POST['CheckAllStaffClass'])
{
    if(!isset($_SESSION['USER_DETAILS']['StaffID']))
    {
        exit();
    }
    else
    {
		$check_all = mysql_real_escape_string($_POST['check_all']);
		// get all staff classes
		$sc_arr = array();
		$sc_sql = mysql_query("
			SELECT * 
			FROM  `staff_classes` 
		");
		while( $sc = mysql_fetch_array($sc_sql) ){
			$sc_arr[] = $sc['ClassID'];
		}
		$sc_filter = implode(",",$sc_arr);

        #Update or insert
        $query = "SELECT StaffId FROM cal_filters WHERE StaffId = '" . $_SESSION['USER_DETAILS']['StaffID'] . "' LIMIT 1";
        $result = mysqlSingleRow($query);

        if(isset($result['StaffId']))
        {
			if( $check_all == 1 ){
				$query = "UPDATE cal_filters SET `staff_class_filter` = NULL WHERE StaffId = '" . $_SESSION['USER_DETAILS']['StaffID'] . "' LIMIT 1";
			}else{				
				$query = "UPDATE cal_filters SET `staff_class_filter` = '{$sc_filter}' WHERE StaffId = '" . $_SESSION['USER_DETAILS']['StaffID'] . "' LIMIT 1";
			}            
        }
        else
        {
			if( $check_all == 1 ){
				$query = "INSERT INTO cal_filters (StaffId, `staff_class_filter`) VALUES ('" . $_SESSION['USER_DETAILS']['StaffID'] . "', NULL)";
			}else{
				$query = "INSERT INTO cal_filters (StaffId, `staff_class_filter`) VALUES ('" . $_SESSION['USER_DETAILS']['StaffID'] . "', '".$sc_filter."')";
			}
            
        }

		//echo $query;
        mysql_query($query);
        $response = array("success" => 1);

        echo json_encode($response);
        exit();
    }
}


?>
