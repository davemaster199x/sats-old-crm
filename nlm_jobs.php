<?php

include('inc/init_for_ajax.php');

echo $sql_str = "
SELECT 
	j.`id` AS jid, 
	j.`created`,

	p.`property_id`,
	p.`address_1` AS p_address_1,
	p.`address_2` AS p_address_2,
	p.`address_3` AS p_address_3,
	p.`state` AS p_state,
	p.`postcode` AS p_postcode,

	a.`agency_id`,
	a.`agency_name`
FROM `jobs` AS j
LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`  
WHERE j.`comments` = 'This property was marked No Longer Managed by Scarlett Mackay on 18/03/2019 and all jobs cancelled'
ORDER BY j.`created` DESC";
echo "<br />";
echo "<br />";
$sql = mysql_query($sql_str);

$num_rows = mysql_num_rows($sql);

echo "Total: <strong>{$num_rows}</strong>";
echo "<br />";
echo "<br />";
?>
<table>
	<tr>
		<th>Job ID</th>
		<th>Created Date</th>
		<th>Property ID</th>
		<th>Property Address</th>
		<th>Agency</th>
	</tr>
	<?php
	while( $row = mysql_fetch_array($sql) ){ ?>
		<tr>
			<td>
				<a target="__blank" href="/view_job_details.php?id=<?php echo $row['jid'] ?>">
					<?php echo $row['jid'] ?>
				</a>
			</td>
			<td>
				<?php echo date('d/m/Y',strtotime($row['created'])) ?>
			</td>
			<td>
				<?php echo $row['property_id'] ?>
			</td>
			<td>
				<a target="__blank" href="/view_property_details.php?id=<?php echo $row['property_id'] ?>">
					<?php echo "{$row['p_address_1']} {$row['p_address_2']} {$row['p_address_3']} {$row['p_state']} {$row['p_postcode']}"; ?>
				</a>
			</td>
			<td>
				<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
				<a target="__blank" href="<?php echo $ci_link ?>">
					<?php echo $row['agency_name'] ?>
				</a>
			</td>
		</tr>
	<?php	
	}
	?>	
</table>
<style>
th, td {
    padding: 5px 12px;
    text-align: left;
}
</style>