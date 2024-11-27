<?php
include('inc/init_for_ajax.php'); 

$from = date('Y-m-01');
$to = date('Y-m-t');
$country_id = $_SESSION['country_default'];

echo "<pre>";
echo $sql_str = 
"SELECT 
    ps.`status_changed`,

    ajt.`type`,

    p.`property_id`,
    p.`address_1`,
    p.`address_2`,
    p.`address_3`,
    p.`state`,
    p.`postcode`,
    p.`deleted`,
    p.`nlm_timestamp`,

    sa.`FirstName`,
    sa.`LastName`
FROM `property_services` AS ps
LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
LEFT JOIN `staff_accounts` AS sa ON a.`salesrep` = sa.`StaffID`
WHERE ps.`service` = 1
AND a.`country_id` = {$country_id}
AND NOT CAST(ps.`status_changed` AS DATE) BETWEEN '{$from}' AND '{$to}'
AND CAST(p.`nlm_timestamp` AS DATE) BETWEEN '{$from}' AND '{$to}'
ORDER BY p.`address_2` ASC, p.`address_1` ASC
";
echo "</pre>";

echo "<br /><br />";

$sql = mysql_query($sql_str);
?>
<div>
	<table class="jtable">
		<tr>
            <td>#</td>
			<td><strong>Property Address</strong></td>
            <td><strong>Service Type</strong></td>
            <td><strong>Status Changed</strong></td>
            <td><strong>NLM timestamp</strong></td>
            <td><strong>Sales rep</strong></td>
            <td><strong>Property Status</strong></td>
		</tr>
<?php
$i = 1;
while( $row = mysql_fetch_array($sql) ){ ?>
    <tr>
        <td><?php echo $i; ?></td>
        <td>
            <a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>">
                <?php echo "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']} {$row['postcode']}"; ?>
            </a>
        </td>
        <td><?php echo $row['type'] ?></td>
        <td><?php echo date('d/m/Y H:i:s',strtotime($row['status_changed'])); ?></td>
        <td><?php echo date('d/m/Y H:i:s',strtotime($row['nlm_timestamp'])); ?></td>
        <td><?php echo "{$row['FirstName']} {$row['LastName']}" ?></td>
        <td><?php echo ( $row['deleted'] == 1 )?'inactive':'active';  ?></td>
    </tr>
<?php	
    $i++;
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