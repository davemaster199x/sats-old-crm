<?php
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$today = date('Y-m-d H:i:s');
$agency_id = mysql_real_escape_string($_REQUEST['agency_id']);
$run_query = mysql_real_escape_string($_REQUEST['run_query']);
$job_date = mysql_real_escape_string($_REQUEST['job_date']);
$job_date2 = ( $job_date != '' )?$crm->formatDate($job_date):'';
$submit = mysql_real_escape_string($_REQUEST['submit']);

$url = $_SERVER['SERVER_NAME'];
if($_SESSION['country_default']==1){ // AU

	if( strpos($url,"crmdev")===false ){ // live 
		$compass_fg_id = 39;
	}else{ // dev 
		$compass_fg_id = 34;
	}
	
}

function getCompassAgencies($compass_fg_id){

	$sql_str = "
		SELECT *
		FROM `agency`
		WHERE `franchise_groups_id` = {$compass_fg_id}
	";
	return mysql_query($sql_str);
	
}


if( $submit && $agency_id != '' ){
	
	$query_filter = '';
	
	if( $agency_id != '' ){
		$query_filter .= " AND a.`agency_id` = {$agency_id} ";
	}
	

	echo $prop_sql_str = "
	SELECT p.`property_id`
	FROM `property_services` AS ps
	LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	WHERE a.`franchise_groups_id` = {$compass_fg_id}
	AND p.`deleted` = 0
	AND ps.`service` = 1
	AND ps.`alarm_job_type_id` = 2
	{$query_filter}
	";
	echo "<br /><br /><br />";

	$prop_sql = mysql_query($prop_sql_str);
	$num_of_rows = mysql_num_rows($prop_sql);
	
	echo "Number of jobs to be processed: {$num_of_rows}<br />";
	
	
	if( $run_query == 1 ){
		
		while( $row = mysql_fetch_array($prop_sql) ){
		
			$job_type = 'Once-off';
			$property_id = $row['property_id'];
			$status = 'Merged Certificates';
			$ajt_id = 2; // SA
			$price = 79;
			
			echo $job_sql_str = "
			INSERT INTO 
			jobs (
				`job_type`, 
				`date`,
				`property_id`, 
				`status`,
				`service`,
				`job_price`,
				`client_emailed`,
				`sms_sent_merge`
			) 
			VALUES (
				'{$job_type}', 
				'{$job_date2}',
				'{$property_id}', 
				'{$status}',
				'{$ajt_id}',
				'{$price}',
				'{$today}',
				'{$today}'
			)
			";
			
			mysql_query($job_sql_str);
			
			// job id
			$job_id = mysql_insert_id();
			
			
			// AUTO - UPDATE INVOICE DETAILS
			$crm->updateInvoiceDetails($job_id);
			
			
			echo "<br /><br />";
			

			// insert job logs
			echo $job_log_str = "
				INSERT INTO 
				`job_log` (
					`contact_type`,
					`eventdate`,
					`eventtime`,
					`comments`,
					`job_id`,
					`staff_id`,
					`created_date`
				) 
				VALUES (
					'<strong>{$job_type}</strong> Job Created',
					'" . date('Y-m-d') . "',
					'" . date('H:i') . "',
					'Compass Job Created', 
					'{$job_id}',
					'{$_SESSION['USER_DETAILS']['StaffID']}',
					'".date('Y-m-d H:i:s')."'
				)
			";
			mysql_query($job_log_str);	

			echo "<br /><br />";
			

		}
		
	}
	
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Compass</title>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<link href="/inc/css/blitzer/jquery-ui-1.8.23.custom.css" type="text/css" rel="stylesheet">
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>

</head>

<body>

	<form>
		<table>
			<tr>
				<td>Agency:</td>
				<td>
					<select name="agency_id">
						<option value="">----</option>
						<?php
						$a_sql = getCompassAgencies($compass_fg_id);
						while( $row = mysql_fetch_array($a_sql ) ){ ?>
							<option value="<?php echo $row['agency_id']; ?>"><?php echo $row['agency_name']; ?></option>
						<?php
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Job Date:</td>
				<td>
					<input type="text" name="job_date" class="datepicker" />
				</td>
			</tr>
			<tr>
				<td>Create Jobs</td>
				<td>
					<select name="run_query">
						<option value="0">NO</option>
						<option value="1">YES</option>
					</select>
				</td>
			</tr>
		</table>
		<input type="submit" name="submit" value="Submit" style="margin-top: 30px;" />
		<a href="/run_compass_create_jobs.php" >
			<button type="button" id="clear">Clear</button>
		</a>
		
	</form>
	
	<script>
		// datepicker
		jQuery(".datepicker").datepicker({ dateFormat: "dd/mm/yy" });
	</script>

</body>

</html>

