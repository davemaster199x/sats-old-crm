<?php
include('inc/init_for_ajax.php'); 

$crm = new Sats_Crm_Class;

$from = date('Y-m-01');
$to = date('Y-m-t');
$country_id = $_SESSION['country_default'];

echo "<pre>";
echo $sql_str = 
"
SELECT 
    j.`id` AS jid,
    j.`del_job`,
    j.`date` AS jdate,
    j.`created` AS jcreated,

    ajt.`id`,
    ajt.`type`, 
    

    p.`property_id`,
    p.`address_1`,
    p.`address_2`,
    p.`address_3`,
    p.`state`,
    p.`postcode`,
    p.`deleted` AS pdeleted,

    a.`agency_id`,
    a.`agency_name`,
    a.`status` AS a_status
FROM `jobs` AS j 
LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
WHERE (
    j.`status` = '' OR
    j.`status` IS NULL
)
ORDER BY j.`created` DESC
";
echo "</pre>";

echo "<br /><br />";

$sql = mysql_query($sql_str);
?>
<div>
	<table class="jtable">
		<tr>
            <td><strong>#</strong></td>
            <td><strong>Job ID</strong></td>
			<td><strong>Service Type</strong></td>
            <td><strong>Job Created Date</strong></td>
            <td><strong>Job Deleted</strong></td>
            <td><strong>Property ID</strong></td>
            <td><strong>Property Address</strong></td>
            <td><strong>Property Deleted</strong></td>
            <td><strong>Agency ID</strong></td>
            <td><strong>Agency Name</strong></td>
            <td><strong>Agency Status</strong></td>
		</tr>
<?php
$i = 1;
while( $row = mysql_fetch_array($sql) ){ 
    
    ?>
    <tr>
        <td><?php echo $i; ?></td>
        <td><?php echo $row['jid']; ?></td>
        <td>
            <a href="/view_job_details.php?id=<?php echo $row['jid']; ?>">
                <?php echo $row['type']; ?>
            </a>
        </td>
        <td><?php echo $crm->isDateNotEmpty($row['jcreated'])?date("d/m/Y",strtotime($row['jcreated'])):''; ?></td>
        <td><?php echo ( $row['del_job'] == 1 )?'<span style="color:red">Yes</span>':'No'; ?></td>
        <td><?php echo $row['property_id']; ?></td>
        <td>
            <a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>">
                <?php echo "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']} {$row['postcode']}"; ?>
            </a>
        </td>
        <td><?php echo ( $row['pdeleted'] == 1 )?'<span style="color:red">Yes</span>':'No'; ?></td>
        <td><?php echo $row['agency_id']; ?></td>
        <td>
         <?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
            <a href="<?php echo $ci_link; ?>">
                <?php echo $row['agency_name']; ?>
            </a>
        </td>
        <td><?php echo $row['a_status']; ?></td>
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