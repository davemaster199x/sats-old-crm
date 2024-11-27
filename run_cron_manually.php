<?php
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$cron_str = "
	SELECT *
	FROM `cron_types` AS ct 
";

$cron_sql = mysql_query($cron_str);
?>
<!DOCTYPE html>
<html>
<head>
<title>TRIGGER MANUAL CRONS</title>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
</head>

<body>

	<a href="/main.php">Home</a>
	<h1>TRIGGER MANUAL CRONS</h1>
	<table id="cron_tbl">
		<thead>
			<tr>
				<th>Cron ID</th>
				<th>Cron Name</th>
				<th>Description</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
			<?php		
			while( $row = mysql_fetch_array($cron_sql) ){
			?>				
				<tr>
					<td><?php echo $row['cron_type_id']; ?></td>
					<td><?php echo $row['type_name']; ?></td>
					<td><?php echo $row['description']; ?></td>
					<td>
						<a class="cron_link" href="/cronjobs/<?php echo $row['cron_file']; ?>" target="_blank">
							Run it
						</a>	
					</td>
				</tr>			
			<?php			
			}
			?>		
		</tbody>
	</table>
	
	<style>
	#cron_tbl td,
	#cron_tbl th{
		padding: 30px 22px;
		text-align: left;
		border: 1px solid;
	}
	#cron_tbl{
		border-collapse: collapse;
	}
	</style>
	
	<script>
	jQuery(document).ready(function(){
		
		jQuery(".cron_link").click(function(e){
			
			 if(confirm('Are you sure you want to run cron?')){
				// The user pressed OK
				// Do nothing, the link will continue to be opened normally
			} else {
				// The user pressed Cancel, so prevent the link from opening
				e.preventDefault();
			}
			
		});
		
	});
	</script>
	
</body>

</html>
