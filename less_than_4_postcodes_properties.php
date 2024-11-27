<?php

include('inc/init_for_ajax.php');

echo $sql_str = "
SELECT 
	`property_id`,
	`address_1`,
	`address_2`,
	`address_3`,
	`state`,
	`postcode`
FROM `property` 
WHERE LENGTH(`postcode`) < 4";
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
		<th>Property ID</th>
		<th>Address</th>
	</tr>
	<?php
	while( $row = mysql_fetch_array($sql) ){ ?>
		<tr>
			<td>
				<a target="__blank" href="/view_property_details.php?id=<?php echo $row['property_id'] ?>">
					<?php echo $row['property_id'] ?>
				</a>
			</td>
			<td>
				<?php echo "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']} {$row['postcode']}"; ?>
			</td>
		</tr>
	<?php	
	}
	?>	
</table>