<?

$title = "Delete Property";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$arr = getHomeTotals(); 

$id = $_GET['id'];

?>


<div id="mainContent">
    
	<div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="View Property Details" href="/view_property_details.php?id=<?php echo $id; ?>">View Property Details</a></li>
		<li class="other first"><a title="Deactivate Property" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Deactivate Property</strong></a></li>
      </ul>
    </div>
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	




<?php

// init the variables



   

   $Query = "UPDATE property SET deleted=1, `agency_deleted`=0, `deleted_date` = '".date('Y-m-d H:i:s')."' WHERE (property_id=$id);";
   
	mysql_query ($Query, $connection);
	if (mysql_affected_rows($connection) == 1){
	
			// cancel all jobs
			mysql_query("
				UPDATE jobs 
				SET 
					`status` = 'Cancelled',
					`cancelled_date` = '".date('Y-m-d')."',
				  `comments` = 'This property was marked Deactivated by SATS on ".date("d/m/Y")." and all jobs cancelled'
				WHERE `status` != 'Completed' 
				AND property_id = {$id}
			");
		
			$property_id = $id;
			$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
			
			$sa_sql = mysql_query("
				SELECT *
				FROM `staff_accounts`
				WHERE `StaffID` ={$staff_id}
			");
			$s = mysql_fetch_array($sa_sql);
			
			// add logs
			$insertLogQuery = "INSERT INTO property_event_log (property_id, staff_id, event_type, event_details, log_date) 
							VALUES (".$property_id.", ".$staff_id.", 'Property Deactivated', 'Property Deactivated', NOW())";
			mysql_query($insertLogQuery, $connection);
			
			/*
			// update status changed
			mysql_query("
				UPDATE `property_services`
				SET `status_changed` = '".date("Y-m-d H:i:s")."'
				WHERE `property_id` = '{$id}'
			");
			*/
			
			
			echo "<div class='success'>Property Successfully Deactivated</div>";
	}
	else
	{
	echo "An error has occurred, it looks like the record may have already been deleted! (please check)<br><br>\n";
	}	



	// delete all the jobs associated with this property.
	#$Query = "DELETE FROM jobs WHERE (property_id = '$id');";
	#mysql_query ($Query, $connection);
	
	    			


?>

	</div>
</div>

<br class="clearfloat" />

</body>
</html>
