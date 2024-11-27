<?

$title = "Undelete Property";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];

?>

  
  <div id="mainContent">
  
  <div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Undelete Property" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Undelete Property</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
  
  <div style="margin-bottom: 20px;">
<?php

// init the variables

$id = $_GET['id'];

   if( $_GET['del_tenant']==1  && $id > 0 ){

		// clear tenants
		mysql_query("
			UPDATE `property_tenants` 
			SET `active` = 0
			WHERE `property_id` = {$id}
			AND `active` = 1
		");

   }

   if( $id > 0 ){

		$log_detail = '';
		$query = mysql_query("
		SELECT ps.service, ajt.type, ps.is_payable, ps.property_services_id FROM property_services AS ps INNER JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id` WHERE property_id = $id ORDER BY ps.property_services_id DESC LIMIT 1
		");
		$data = mysql_fetch_assoc($query);
		mysql_query("
			UPDATE property_services
			SET
				service=2
			WHERE `property_id`={$id} AND property_services_id = {$data['property_services_id']};
			");
		$log_detail = '| '.$data['type'].' Service updated from <strong>SATS</strong> to <strong>No Response</strong>';
		// clear is_payable conditions, must be placed before property update bec nlm_timestamp gets cleared	
		$this_month_start = date("Y-m-01");
		$this_month_end = date("Y-m-t");
		$is_payable_log = '';

		$sixty_days_ago = date("Y-m-d",strtotime("-61 days"));

		// get NLM date
		$prop_sql_str = "
		SELECT `nlm_timestamp`
		FROM `property`
		WHERE `property_id`= {$id}
		";		
		$prop_sql = mysql_query($prop_sql_str); 
		$prop_row = mysql_fetch_object($prop_sql);
		$nlm_date = date('Y-m-d',strtotime($prop_row->nlm_timestamp));

		// if status change is within 60 days ago but not within this month
		if(  $nlm_date > $sixty_days_ago && !( $nlm_date >= $this_month_start && $nlm_date <= $this_month_end ) ){
			// clear is_payable
			$update_ps_sql_str = "
			UPDATE `property_services`
			SET `is_payable` = 0   
			WHERE `property_id` = {$id}            
			";
			mysql_query($update_ps_sql_str);
			$is_payable_log = '| Property unmarked <strong>payable</strong>';

		}else{

			// update active service to is_payable to 1 and updated status changed to today

			##al: get current PS values
			$ps_tt_sql = mysql_query("
			SELECT ajt.`type` AS ajt_type_name
			FROM `property_services` as ps
			LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
			WHERE ps.`property_id` = {$id}
			AND ps.`service` = 1  
			");
			//$ps_tt_row = mysql_fetch_array($ps_tt_sql);

			$update_ps_sql_str = "
			UPDATE `property_services`
			SET 
				`is_payable` = 1,
				`status_changed` = '".date('Y-m-d H:i:s')."'   
			WHERE `property_id` = {$id}   
			AND `service` = 1   
			";
			mysql_query($update_ps_sql_str);

			## Al > add is_payable log
			$mark_unmark = "marked";
			while( $ps_tt_sql_row = mysql_fetch_array($ps_tt_sql) ){

				mysql_query("
				INSERT INTO
				`property_event_log`
				(
					`property_id`,
					`staff_id`,
					`event_type`,
					`event_details`,
					`log_date`,
					`hide_delete`
				)
				VALUES(
					{$id},
					{$_SESSION['USER_DETAILS']['StaffID']},
					'Property Sales Commission',
					'Property Service <b>{$ps_tt_sql_row['ajt_type_name']}</b> {$mark_unmark} <b>payable</b>',
					'".date('Y-m-d H:i:s')."',
					1
				)
				");

			}
			## Al > add is_payable log end

		}

		/*
		$Query = "
		UPDATE property 
		SET 
			deleted=0, 
			agency_deleted=0, 
			`is_nlm` = 0, 
			`nlm_display` = NULL, 
			`nlm_timestamp` = NULL, 
			`nlm_by_sats_staff` = NULL, 
			`nlm_by_agency` = NULL,
			`propertyme_prop_id` = NULL  
		WHERE `property_id`={$id};
		";
		mysql_query ($Query, $connection);
		*/
		$Query = "
		UPDATE property 
		SET 
			deleted=0, 
			agency_deleted=0, 
			`is_nlm` = 0, 
			`nlm_display` = NULL, 
			`nlm_timestamp` = NULL, 
			`nlm_by_sats_staff` = NULL, 
			`nlm_by_agency` = NULL
		WHERE `property_id`={$id};
		";
		mysql_query ($Query, $connection);

   }


	if (mysql_affected_rows($connection) == 1)
		{
		$property_id = $id;
		$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
		
		$sa_sql = mysql_query("
			SELECT *
			FROM `staff_accounts`
			WHERE `StaffID` ={$staff_id}
		");
		$s = mysql_fetch_array($sa_sql);
		
		$insertLogQuery = "INSERT INTO property_event_log (property_id, staff_id, event_type, event_details, log_date) 
						VALUES (".$property_id.", ".$staff_id.", 'Property Restored', 'By {$staff_name} {$log_detail} {$is_payable_log}', NOW())";
		mysql_query($insertLogQuery, $connection);

		##New > clear propery api id from new generic table
		$QueryAPD = "
		UPDATE api_property_data 
		SET 
			api_prop_id= NULL, 
			active=0
		WHERE `crm_prop_id`={$property_id};
		";
		mysql_query ($QueryAPD);
		##New > clear propery api id from new generic table end

		echo "<div class='success'>
			Property Successfully Restored<br />
			<a href='/view_property_details.php?id={$id}'>Return to Property</a>
			</div>";
			
		/*
		// update status changed
		mysql_query("
			UPDATE `property_services`
			SET `status_changed` = '".date("Y-m-d H:i:s")."'
			WHERE `property_id` = '{$id}'
		");		
		*/
			
		}
		else
		{
		echo "An error has occurred, it looks like the property may have already been restored! (please check)<br>\n";
		}	

		//echo "<a href='" . URL . "view_properties.php'>Click Here</a> to Return to the Properties page.<br>\n";	
	    			
?>
</div>


  </div>
  
  </div>

<br class="clearfloat" />

</body>
</html>
