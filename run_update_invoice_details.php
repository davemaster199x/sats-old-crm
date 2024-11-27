<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;
$country_id = $_SESSION['country_default'];

// main query
echo $sql_str = "
	SELECT j.`id` AS jid, j.`job_price`, j.`job_type`, j.`status`
	FROM `jobs` AS j
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	WHERE j.`del_job` = 0
	AND p.`deleted` =0
	AND a.`status` = 'active'
	AND a.`country_id` = {$country_id}
	AND (
		j.`status` != 'Completed' AND 
		j.`status` != 'Cancelled'
	)
	AND(
		j.`invoice_balance` IS NULL
	)
";
echo "<br /><br />";
$sql = mysql_query($sql_str);
$num_rows = mysql_num_rows($sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>Run Update Invoice Details</title>
<style>
.jtable{
	border-collapse: collapse;
}
.jtable tr,
.jtable td,
.jtable th{
	border: 1px solid;
	padding: 5px;
}
</style>
</head>

<body>
Num Jobs To be Processed: <?php echo $num_rows; ?><br /><br />
<?php

if( $num_rows > 0 ){ ?>

	<table class="jtable">
		<tr>
			<th>Job ID</th>
			<th>Price</th>
			<th>Job Type</th>
			<th>Job Status</th>
		</tr>
		<?php
		while( $job = mysql_fetch_array($sql) ){
			
			$job_id = $job['jid'];
			$job_price = $job['job_price'];
			$job_type = $job['job_type'];
			$status = $job['status'];
			
			if( $job_id > 0 ){		
			?>
				<tr>
					<td><?php echo $job_id; ?></td>
					<td>$<?php echo $job_price; ?></td>
					<td><?php echo $job_type; ?></td>
					<td><?php echo $status; ?></td>
				</tr>
			<?php
				// AUTO - UPDATE INVOICE DETAILS
				$crm->updateInvoiceDetails($job_id);
			}
			
		}
		?>		
	</table>

<?php
	
}

?>
</body>

</html>