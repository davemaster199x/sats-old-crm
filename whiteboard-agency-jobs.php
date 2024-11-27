<?

$title = "View Agency Job Details: To Be Booked";
$onload = 1;
$onload_txt = "zxcSelectSort('agency',1)";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$agency_id = $_GET['agency_id'];


?>
<div id="mainContent">


	<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Whiteboard" href="/whiteboard.php">Whiteboard</a></li>
		<li class="other first"><a title="Whiteboard Jobs" href="<?php echo $_SERVER['REQUEST_URI']; ?>"><strong>Whiteboard Jobs</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

	
	<?php

		$rows = array();
		$sql = "SELECT 
				  a.agency_name,
				  p.address_1,
				  p.address_2,
				  p.address_3,
				  j.id,
				  j.`job_type`,
				  ajt.`type`
				FROM
				  jobs j 
				  JOIN property p USING (property_id) 
				  JOIN agency a USING (agency_id) 
				  LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
				WHERE j.status = 'To Be Booked' 
				  AND a.agency_id = '". $agency_id ."'
				  AND p.deleted = 0
				  AND j.`assigned_tech` = 1
				ORDER BY address_2 ;";
		$query = mysql_query($sql, $connection);
		while($result = mysql_fetch_assoc($query)) {
			$rows[] = $result;
		}
		

	
	
	
		
	?>

	
	
	<table class="whiteboard table-left tbl-fr-red" cellspacing="0" cellpadding="3">
		<tbody>
			<tr bgcolor="#b4151b">
				<th>Address</th>
				<th>Job Type</th>
				<th>Service</th>
			</tr>
			<?php 
			foreach($rows as $row){ ?>
				<tr>
					<td>
						<a href="view_job_details.php?id=<?php echo $row['id'] ?>">
							<?php echo $row['address_1'].' '.$row['address_2'] .', '.  $row['address_3']; ?>
						</a>
					</td>
					<td><?php echo $row['job_type']; ?></td>
					<td><?php echo $row['type']; ?></td>
				</tr>
			<?php
			}
			?>			
		</tbody>
	</table>
	
    <p>
      <!-- end #mainContent -->
    </p>
  </div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />
  <div id="footer">
  
    <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
