<?php
include('inc/init_for_ajax.php'); 

$from = date('Y-m-01');
$to = date('Y-m-t');
$country_id = $_SESSION['country_default'];

echo "<pre>";
echo $sql_str = 
"SELECT 
ajt.`id`,
ajt.`type`, 

p.`property_id`,
p.`address_1`,
p.`address_2`,
p.`address_3`,
p.`state`,
p.`postcode`,
p.`deleted`,

a.`agency_id`,
a.`agency_name`,

agen_serv.`agency_services_id`,
agen_serv.`service_id`,
agen_serv.`price`
FROM `property_services` AS ps
LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
LEFT JOIN `agency_services` AS agen_serv ON ( agen_serv.`agency_id` = a.`agency_id` AND agen_serv.`service_id` = ps.`alarm_job_type_id` )
WHERE p.`deleted` =0
AND a.`status` = 'active'
AND ps.`service` =1
AND agen_serv.`agency_services_id` IS NULL
ORDER BY a.`agency_name` ASC
";
echo "</pre>";

echo "<br /><br />";

$sql = mysql_query($sql_str);
?>
<div>
	<table class="jtable">
		<tr>
            <td><strong>#</strong></td>
			<td><strong>Service Type</strong></td>
            <td><strong>Property ID</strong></td>
            <td><strong>Property Address</strong></td>
            <td><strong>Agency ID</strong></td>
            <td><strong>Agency Name</strong></td>
		</tr>
<?php
$i = 1;
while( $row = mysql_fetch_array($sql) ){ 
    
    ?>
    <tr>
        <td><?php echo $i; ?></td>
        <td><?php echo $row['type']; ?></td>
        <td><?php echo $row['property_id']; ?></td>
        <td>
            <a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>">
                <?php echo "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']} {$row['postcode']}"; ?>
            </a>
        </td>
        <td><?php echo $row['agency_id']; ?></td>
        <td>
            <?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
            <a href="<?php echo $ci_link ?>">
                <?php echo $row['agency_name']; ?>
            </a>
        </td>
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