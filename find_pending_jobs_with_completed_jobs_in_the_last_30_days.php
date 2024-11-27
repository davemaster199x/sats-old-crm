<?php
include('inc/init_for_ajax.php'); 

$sql_str = "SELECT j.`id` AS jid, p.`property_id`
FROM `jobs` AS j
LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
WHERE j.`status` = 'Pending'
AND j.`del_job` = 0
AND p.`deleted` = 0
AND a.`status` = 'active'
AND a.`country_id` = {$_SESSION['country_default']}
";

echo "<br /><br />";

$sql = mysql_query($sql_str);
?>
<div>
	<table class="jtable">
		<tr>
			<td><strong>Job ID</strong></td>
			<td><strong>Property Address</strong></td>
		</tr>
<?php
while( $row = mysql_fetch_array($sql) ){
	
	$sql_str2 = "SELECT 
		j.`id` AS jid, 
		p.`property_id`, 
		p.`address_1` AS p_address_1, 
		p.`address_2` AS p_address_2, 
		p.`address_3` AS p_address_3,
		p.`state` AS p_state,	
		p.`postcode` AS p_postcode
	FROM `jobs` AS j
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	WHERE j.`status` = 'Completed'
	AND j.`del_job` = 0
	AND p.`deleted` = 0
	AND a.`status` = 'active'
	AND a.`country_id` = {$_SESSION['country_default']}
	AND j.`date` >= '".date('Y-m-d',strtotime('-30 days'))."'
	AND p.`property_id` = {$row['property_id']}
	";
	
	$sql2 = mysql_query($sql_str2);	
	if( mysql_num_rows($sql2) > 0 ){
		while( $row2 = mysql_fetch_array($sql2) ){?>
			<tr>
				<td>
					<a href="/view_job_details.php?id=<?php echo $row2['jid']; ?>">
						<?php echo $row2['jid']; ?>
					</a>
				</td>
				<td>
					<a href="/view_property_details.php?id=<?php echo $row2['property_id']; ?>">
						<?php echo "{$row2['p_address_1']} {$row2['p_address_2']} {$row2['p_address_3']} {$row2['p_state']} {$row2['postcode']}"; ?>
					</a>
				</td>
			</tr>
		<?php	
		}
	}
	
}
?>
	</table>
</div>
<style>
.jtable{
	border-collapse: collapse;
}
.jtable td {
    border: 1px solid;
	padding: 5px;
}
</style>