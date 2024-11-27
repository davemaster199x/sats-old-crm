<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;">
	<tr class="toprow jalign_left">				
		<th>Make</th>
		<th>Model</th>
		<th>Power Type</th>
		<th>Detection Type</th>
		<th>Date Location</th>
	</tr>
	<?php				
	while($sa = mysql_fetch_array($sa_sql)){ ?>
		<tr class="body_tr jalign_left">
			<td><span class="txt_lbl"><?php echo $sa['make']; ?></span></td>	
			<td><span class="txt_lbl"><a href="view_alarm_details.php?id=<?php echo $sa['smoke_alarm_id']; ?>"><?php echo $sa['model']; ?></a></span></td>
			<td><span class="txt_lbl"><?php echo $crm->getSaPowerType($sa['power_type']); ?></span></td>
			<td><span class="txt_lbl"><?php echo $crm->getSaDetectionType($sa['detection_type']); ?></span></td>
			<td><span class="txt_lbl"><?php echo $sa['loc_of_date']; ?></span></td>
		</tr>
	<?php
	}
	?>			
</table>


<?php
// Initiate pagination class
$jp = new jPagination();

$per_page = $limit;
$page = ($_GET['page']!="")?$_GET['page']:1;
$offset = ($_GET['offset']!="")?$_GET['offset']:0;	

echo $jp->display($page,$ptotal,$per_page,$offset,$params);
?>