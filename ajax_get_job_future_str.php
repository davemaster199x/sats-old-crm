<?php
include('inc/init_for_ajax.php');
$job_id = mysql_real_escape_string($_POST['job_id']);
$country_id = mysql_real_escape_string($_POST['country_id']);
$today = date('Y-m-d');

// fetch all future STR
$other_str_txt = "
	SELECT *, tr.`date` AS tr_date 
	FROM `tech_run_rows` AS trr
	LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id` 
	LEFT JOIN `staff_accounts` AS sa ON tr.`assigned_tech` = sa.`StaffID`
	LEFT JOIN `jobs` AS j ON j.`id` = trr.`row_id` 
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	AND trr.`row_id_type` =  'job_id'
	WHERE j.`id` = {$job_id}
	AND tr.`date` >=  '{$today}'
	AND trr.`hidden` = 0
	AND j.`del_job` = 0
	AND tr.`country_id` = {$country_id}
	AND a.`country_id` = {$country_id}
";
$other_str_sql = mysql_query($other_str_txt);

$fcount = 1;

if( mysql_num_rows($other_str_sql)>0 ){ ?>
				
<table style="border-collapse: initial;">
	<?php
	while( $other_str = mysql_fetch_array($other_str_sql) ){ 
	?>
	<tr>									
		<td style="font-size: 13px;">
			<a target="__blank" class="str_link" href="/set_tech_run.php?tr_id=<?php echo $other_str['tech_run_id']; ?>">
				<?php echo date('D d/m',strtotime($other_str['tr_date'])); ?>
			</a>									
		</td>									
	</tr>
	<?php	
	$fcount++;
	}	
	?>	
</table>
	
<?php							
}	
?>
						