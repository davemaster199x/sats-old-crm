<?php
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

echo $sql_str = "
	SELECT 
		j.`id` AS jid, 
		j.`job_type`, 
		j.`status`,
		
		p.`address_1` AS p_address_1,
		p.`address_2` AS p_address_2,
		p.`address_3` AS p_address_3,
		p.`state` AS p_state,
		p.`postcode` AS p_postcode,
		
		sa.`FirstName`,
		sa.`LastName`
	FROM `jobs` AS j 
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
	LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id` 
	LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
	WHERE p.`deleted` =0 
	AND a.`status` = 'active' 
	AND j.`del_job` = 0 
	AND a.`country_id` = {$_SESSION['country_default']}	
	AND j.`job_type` != '240v Rebook'
	AND j.`status` NOT IN('Completed','Cancelled')
";

$jsql = mysql_query($sql_str);

echo "<br /><br />";
?>
<table>
	<thead>
		<tr>
			<th>ID</th>
			<th>Job Type</th>
			<th>Job Status</th>
			<th>Address</th>
			<th>Tech</th>
		</tr>
	</thead>
	<tbody>
		<?php
		
		while( $row = mysql_fetch_array($jsql) ){
	
			$job_id = $row['jid'];
			$p_address = "{$row['p_address_1']} {$row['p_address_2']} {$row['p_address_3']} {$row['p_state']} {$row['p_postcode']}";

			
			// find an expired 240v alarm
			if( $crm->findExpired240vAlarm($job_id) == true ){ ?>
				
				<tr>
					<td><a href="/view_job_details.php?id=<?php echo $job_id; ?>" target="blank"><?php echo $job_id; ?></a></td>
					<td><?php echo $row['job_type']; ?></td>
					<td><?php echo $row['status']; ?></td>
					<td><?php echo $p_address; ?></td>
					<td><?php echo "{$row['FirstName']} {$row['LastName']}"; ?></td>
				</tr>
				
			<?php	
			}

		}
		?>		
	</tbody>
</table>
<style>
th, td{
    padding: 5px 10px;
}
</style>