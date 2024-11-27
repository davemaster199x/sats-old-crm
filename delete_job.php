<?

$title = "Delete Job";
$onload = 1;
$onload_txt = "zxcSelectSort('agency',1)";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>

  <div id="mainContent">
  	<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>

<?php

// init the variables

$job_id = $_REQUEST['id'];
$doaction = $_REQUEST['doaction'];




// get job type
$jsql = mysql_query("
	SELECT *
	FROM `jobs` 
	WHERE `id` = {$job_id}
");
$j = mysql_fetch_array($jsql);
$job_type = $j['job_type'];


if ($doaction == "delete")
	{
	// update the details in the database.

   // (1) Open the database connection and use the winestore
   // database
   


   /*
   // (2) Run the query on the winestore through the
   //  connection
   $Query = "DELETE FROM jobs WHERE (id=$job_id);";
   
	mysql_query ($Query, $connection);
	*/
	
	
	// mark job as deleted
	mysql_query("
		UPDATE `jobs`
		SET 
				`del_job` = 1,
				`deleted_date` = '".date('Y-m-d')."'
		WHERE `id` = {$job_id}
	");
	
	// insert logs
	mysql_query("
		INSERT INTO 
		`job_log` (
			`contact_type`,
			`eventdate`,
			`comments`,
			`job_id`, 
			`staff_id`,
			`eventtime`
		) 
		VALUES (
			'Job Deleted',
			'".date('Y-m-d')."',
			'Job <strong>Deleted</strong>',
			{$job_id}, 
			'".$_SESSION['USER_DETAILS']['StaffID']."',
			'".date('H:i')."'
		)
	");	
	
	
	// get service 
	$service = $_REQUEST['service'];
	$s_sql = mysql_query("
		SELECT *
		FROM `alarm_job_type` 
		WHERE `id` = {$service}
	");
	$s = mysql_fetch_array($s_sql);
	
	// add job logs
	$property_id = $_REQUEST['property_id'];
	$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
	$service_name = $s['type'];	
	
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
			".$property_id.",
			".$staff_id.",
			'{$service_name} Job Deleted',
			'".$job_type." Job <strong>({$job_id})</strong> Deleted',
			'".date('Y-m-d H:i:s')."'
		)
	");
	
	if (mysql_affected_rows($connection) == 1)
		{
		?>
		<div class="success">Job Sucessfully Deleted. <a href="/view_property_details.php?id=<?php echo $_REQUEST['property_id'] ?>">Back</a> to Property</div>
		<?php
		}
		else
		{
		echo "An error has occurred, it looks like the record may have already been deleted! (please check)<br><br>\n";
		echo "<a href='" . URL . "view_jobs.php'>Click Here</a> to Return to the Jobs page.<br>\n";
		}
	
	
	} // if $doaction
	    			
?>

</tr>
</table>
</td></tr></table>



</form>



    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>
      <!-- end #mainContent -->
    </p>
  </div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />

<!-- end #container --></div>
</body>
</html>
