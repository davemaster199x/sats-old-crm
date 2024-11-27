<?php

include('inc/init_for_ajax.php');
$country_id = $_SESSION['country_default'];

echo "<h1>No YM Completed Properties</h1>";

$fg = 14; // Defence Housing
//$fg_filter = "AND a.`franchise_groups_id` != {$fg}";

echo $sql_str = "
SELECT 
	p.`property_id`,
	p.`address_1`,
	p.`address_2`,
	p.`address_3`,
	p.`state`,
    p.`postcode`,
    
    a.`agency_id`,
    a.`agency_name`
FROM `property` AS p
LEFT JOIN `agency` AS a ON  p.`agency_id` = a.`agency_id`
INNER JOIN `property_services` AS ps ON p.`property_id` = ps.`property_id`
WHERE p.`deleted` = 0
AND a.`status` = 'active'
AND a.`country_id` = {$country_id}
{$fg_filter} 
AND ps.`service` = 1
AND p.`staff_marked_done` = 0
GROUP BY p.`property_id`
ORDER BY a.`agency_name` ASC
";
$sql = mysql_query($sql_str);

echo "<br />";
echo "<br />";

$num_rows = mysql_num_rows($sql);

echo "Total Properties: <strong>{$num_rows}</strong>";
echo "<br />";
echo "<br />";

function findYmCompletedJob($property_id){

	$sql_str = "
	SELECT COUNT(j.`id`) AS jcount
	FROM `jobs` AS `j`
	WHERE `j`.`del_job` = 0
	AND `j`.`job_type` = 'Yearly Maintenance'
    AND `j`.`status` = 'Completed'
	AND j.`property_id` = {$property_id}
	";
	$sql = mysql_query($sql_str);
	$row = mysql_fetch_array($sql);
	return $row['jcount'];
    
}
?>
<!DOCTYPE html>
<html>
<head>
<meta content="text/html;charset=utf-8" http-equiv="Content-Type">
<meta content="utf-8" http-equiv="encoding">
<title>Title of the document</title>
<script
  src="https://code.jquery.com/jquery-3.4.1.js"
  integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU="
  crossorigin="anonymous"></script>
</head>
<body>

<p>Legend:</p>
<ul>
	<li><span class="hl_row_yellow">Marked as done by You</span></li>
	<li><span class="hl_row_green">Marked as done by Others</span></li>
</ul>

<table>
	<tr>
        <th>#</th>
		<th>Property ID</th>
        <th>Address</th>
		<th>Agency</th>
		<th>Mark as done</th>
	</tr>
    <?php
    $i = 1;
	while( $row = mysql_fetch_array($sql) ){ 

		if( findYmCompletedJob($row['property_id']) == 0 ){
		?>
			<tr>
                <td><?php echo $i; ?></td>
				<td>
					<a target="__blank" href="/view_property_details.php?id=<?php echo $row['property_id'] ?>">
						<?php echo $row['property_id'] ?>
					</a>
				</td>
				<td>
					<?php echo "{$row['address_1']} {$row['address_2']}, {$row['address_3']} {$row['state']}, {$row['postcode']}"; ?>
                </td>
                <td>
					<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
                    <a target="__blank" href="<?php echo $ci_link ?>">
                        <?php echo $row['agency_name']; ?>
                    </a>
				</td>
				<td>
					<input type="checkbox" class="staff_marked_done" />
					<input type="hidden" class="property_id" value="<?php echo $row['property_id'] ?>" />
				</td>
			</tr>
        <?php	
        $i++;
		}
	}
	?>	
</table>

<p><b>Number of Properties with No YM Completed Jobs: <?php echo $i; ?></b></p>

<style>
td, th {

    padding: 3px 36px 3px 0;
    text-align: left;

}
.hl_row_yellow{
	background-color: yellow;
}
.hl_row_green{
	background-color: green;
}
</style>
<script>
jQuery(document).ready(function(){

	jQuery(".staff_marked_done").change(function(){
		
		var obj = jQuery(this);
		var row = obj.parents("tr:first");

		var chk = obj.prop("checked");
		var property_id = row.find('.property_id').val();
		
		if( chk == true ){

			jQuery.ajax({
				type: "POST",
				url: "ajax_property_mark_as_done.php",
				data: {
					property_id: property_id
				}
			}).done(function( ret ){			

				if( ret != '' ){
					alert(ret);
					row.addClass("hl_row_green");
				}else{
					row.addClass("hl_row_yellow");
				}
				

			});


		}
				
	});

});
</script>
</body>
</html>