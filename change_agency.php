<?

$title = "Change property to new agency";

$onload = 1;
$onload_txt = "zxcSelectSort('agency',1)";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

// init the variables
$id = $_POST['agency'];
$orig_agency_id = $_POST['orig_agency_id'];
$property_id = $_POST['property_id'];
$pm_id_new = $_POST['pm_id_new'];

$pm_id_new2 = ($pm_id_new!="") ? $pm_id_new : 'NULL';

?>
  <div id="mainContent">
  
	<div class="sats-middle-cont">
  
	<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="View Properties" href="<?=URL;?>view_properties.php">View Properties</a></li>
        <li class="other second"><a title="View Property Details" href="<?=URL;?>view_property_details.php?id=<?php echo $property_id; ?>">View Property Details</a></li>
        <li class="other third"><a title="Change property to new agency" href="/change_agency_static.php?id=<?php echo $property_id; ?>"><strong>Change Agency</strong></a></li>
      </ul>
    </div>
	<div id="time"><?php echo date("l jS F Y"); ?></div>
  
  
 


<?php


//print_r($_POST);

if($property_id!="" AND $id!=""){

		// clear console property
		mysql_query("
		DELETE 
		FROM `console_properties`	
		WHERE `crm_prop_id` = {$property_id}
		");
   
		$Query = "
		UPDATE property 
		SET 
			`agency_id` = {$id}, 
			`pm_id_new` = NULL,
			`propertyme_prop_id` = NULL,
			`palace_prop_id` = NULL, 
			`pm_id_new` = {$pm_id_new2}
			WHERE (property_id=$property_id);
		";
   
	$result = mysql_query ($Query, $connection);

	$QueryApd = "
		UPDATE api_property_data
		SET 
			`api_prop_id` = NULL, 
			`active` = 0
			WHERE (crm_prop_id=$property_id);
		";
   
	$result = mysql_query ($QueryApd, $connection);

	// deactivate properties_from_other_company
	mysql_query("
	UPDATE `properties_from_other_company`
	SET `active` = 0
	WHERE `property_id` = {$property_id}
	");

}
	
	/*if (mysql_affected_rows($connection) == 1)
	{*/
		//$property_id = $id;
		$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
		
		$sa_sql = mysql_query("
			SELECT *
			FROM `staff_accounts`
			WHERE `StaffID` ={$staff_id}
		");
		$s = mysql_fetch_array($sa_sql);
		
		/*
		$insertLogQuery = "INSERT INTO property_event_log (property_id, staff_id, event_type, event_details, log_date) 
						VALUES (".$property_id.", ".$staff_id.", 'Change property's agency', 'Agency changed by {$s['FirstName']} {$s['LastName']} @ ".date("H:i")." on ".date("d/m/Y")."', NOW())";
		mysql_query($insertLogQuery, $connection);
		*/

		// original agency
		$prop_sql_str = "
			SELECT 
				property_id,
				address_1,
				address_2,
				address_3,
				state,
				postcode
			FROM `property`
			WHERE `property_id` = {$property_id}
		";
		$prop_sql = mysql_query($prop_sql_str);
		$prop_row = mysql_fetch_array($prop_sql);

		$prop_address = "{$prop_row['address_1']} {$prop_row['address_2']} {$prop_row['address_3']} {$prop_row['state']} {$prop_row['postcode']}";
		
		// original agency
		$oa_sql = mysql_query("
			SELECT `agency_name`
			FROM `agency`
			WHERE `agency_id` = {$orig_agency_id}
		");
		$oa = mysql_fetch_array($oa_sql);
		
		// current selected agency
		$ca_sql = mysql_query("
			SELECT `agency_name`
			FROM `agency`
			WHERE `agency_id` = {$id}
		");
		$ca = mysql_fetch_array($ca_sql);
		
		mysql_query("
			INSERT INTO 
			`property_event_log` (
				`property_id`, 
				`staff_id`, 
				`event_type`, 
				`event_details`, 
				`log_date`
			) 
			VALUES (
				{$property_id}, 
				".$staff_id.", 
				'Agency Changed', 
				'Changed From {$oa['agency_name']} to {$ca['agency_name']}', 
				NOW()
			)
		");
		$ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$id}");
		echo "<div class='success'><a href='/view_property_details.php?id={$property_id}'>{$prop_address}</a> Successfully Changed to <a href='{$ci_link}'>{$ca['agency_name']}</a></div>";
	/*}
	else
	{
		echo "An error has occurred, record not found! (please check)<br><br>\n";
	}	*/

		
	
	    
?>


	</div>
</div>

<br class="clearfloat" />

</body>
</html>
