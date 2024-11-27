<?

$title = "Multiple Jobs";

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');

$count = 0;


// pagination script
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;
$this_page = $_SERVER['PHP_SELF'];
$next_link = "{$this_page}?offset=".($offset+$limit);
$prev_link = "{$this_page}?offset=".($offset-$limit);

// get list					
$dup_sql = getMultipleJobs($offset,$limit);
$ptotal = mysql_num_rows(getMultipleJobs('',''));

?>
<div id="mainContentCalendar">

 <div class="sats-middle-cont">
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="/multiple_jobs.php"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>



        <table border="0" cellpadding="5" cellspacing="0" width="100%" class="table-left tbl-fr-red">
            <tr bgcolor="#b4151b">
				<th><b>Property ID</b></th>
				<th><b>Job #</b></th>
				<th><b>Job Type</b></th>
				<th><b>Status</b></th>
				<th><b>Service</b></th>
                <th><b>Address</b></th>				
                <th><b><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></b></th>
				<th><b>Agency Name</b></th>
            </tr>
            <?php
            /* spit out all the duplicates */
			//$dup_sql = jFindDupProp();
			//echo mysql_num_rows(getDuplicateProperties());
			$i = 0;
            while($d=mysql_fetch_array($dup_sql)) {
			?>
				<tr <?php echo ($i%2==0)?'style="border-right: 1px solid #cccccc; background-color: #efefef;"':''; ?>>
					<td><a href="/view_property_details.php?id=<?php echo $d['property_id']; ?>"><?php echo $d['property_id']; ?></a></td>
					<td><a href="/view_job_details.php?id=<?php echo $d['id']; ?>"><?php echo $d['id']; ?></a></td>
					<td><?php echo $d['job_type']; ?></td>
					<td><?php echo $d['status']; ?></td>
					<td>
						<?php
						// if job type is 'IC Upgrade' show IC upgrade icon
						$show_ic_icon = ( $d['job_type'] == 'IC Upgrade' )?1:0;
						?>
						<img src="images/serv_img/<?php echo getServiceIcons($d['service'],'',$show_ic_icon); ?>" />
					</td>
					<td><?php echo "{$d['address_1']} {$d['address_2']}, {$d['address_3']}"; ?></td>
					<td><?php echo $d['state']; ?></td>
					<td>
					<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$d['agency_id']}"); ?>
					<a href="<?php echo $ci_link; ?>"><?php echo $d['agency_name']; ?></a></td>
				</tr>
				<?php
				$dup_sql2 = getOtherMultipleJobs($d['property_id'],$d['id'],$d['service']);
				//echo mysql_num_rows(getDuplicatePropertiesDetails($d['property_id']));
				while($d2 = mysql_fetch_array($dup_sql2)){ ?>
					<tr <?php echo ($i%2==0)?'style="border-right: 1px solid #cccccc; background-color: #efefef;"':''; ?>>
						<td><a href="/view_property_details.php?id=<?php echo $d2['property_id']; ?>"><?php echo $d2['property_id']; ?></a></td>
						<td><a href="/view_job_details.php?id=<?php echo $d2['id']; ?>"><?php echo $d2['id']; ?></a></td>
						<td><?php echo $d2['job_type']; ?></td>
						<td><?php echo $d2['status']; ?></td>
						<td>	
							<?php
							// if job type is 'IC Upgrade' show IC upgrade icon
							$show_ic_icon2 = ( $d2['job_type'] == 'IC Upgrade' )?1:0;
							?>
							<img src="images/serv_img/<?php echo getServiceIcons($d2['service'],'',$show_ic_icon2); ?>" />
						</td>
						<td><?php echo "{$d2['address_1']} {$d2['address_2']}, {$d2['address_3']}"; ?></td>
						<td><?php echo $d2['state']; ?></td>
						<td>
						<?php $ci_link2 = $crm->crm_ci_redirect("/agency/view_agency_details/{$d2['agency_id']}"); ?>
						<a href="<?php echo $ci_link2; ?>"><?php echo $d2['agency_name']; ?></a></td>
					</tr>
				<?php
				}
				?>
			<?php 
			$i++;
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

        <!-- end #mainContent -->
</div>

</div>

<br class="clearfloat" />

</body>

</html> 
