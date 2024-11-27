<?php
$title = "STR-less Jobs";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;
$this_page = $_SERVER['PHP_SELF'];

//$params = "&sort={$sort}&order_by={$order_by}&job_type=".urlencode($job_type)."&state=".urlencode($state)."&agency=".urlencode($agency)."&postcode_region_id=".$region2;
$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

function findBookedJobsNotOnAnySTR($start, $limit){
	
	$today = date('Y-m-d');
	$next_2_days = date('Y-m-d',strtotime('+2 days'));
	
	if(is_numeric($start) && is_numeric($limit)){
		$limit_str = " LIMIT {$start}, {$limit}";
	}
	
	$sql_str = "SELECT 
	j.`id` AS jid, 
	j.`created` AS jcreated, 
	j.`date` AS jdate, 
	j.`service` AS jservice, 
	j.`job_type`,

	p.`property_id`,
	p.`address_1` AS p_address_1, 
	p.`address_2` AS p_address_2, 
	p.`address_3` AS p_address_3, 
	p.`state` AS p_state, 
	p.`postcode` AS p_postcode,   

	a.`agency_id`,
	a.`agency_name`,

	sa.`FirstName`,
	sa.`LastName`
	FROM `jobs` AS j 
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
	WHERE j.`status` = 'Booked' 
	AND j.`date` = '{$next_2_days}'
	{$limit_str}
	";
	
	return mysql_query($sql_str);
	
}

$plist = findBookedJobsNotOnAnySTR($offset,$limit);
//$ptotal = mysql_num_rows(findBookedJobsNotOnAnySTR());


function findJobsOnSTR($job_id){

	// fetch all future STR
	$sql_str = "
		SELECT COUNT(trr.`tech_run_rows_id`) AS trr_count
		FROM `tech_run_rows` AS trr
		LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id` 
		LEFT JOIN `jobs` AS j ON ( j.`id` = trr.`row_id` AND trr.`row_id_type` =  'job_id' )
		WHERE j.`id` = {$job_id}	
		AND trr.`hidden` = 0
		AND j.`del_job` = 0
		AND tr.`country_id` = {$_SESSION['country_default']}
		AND tr.`date` >= '".date('Y-m-d')."'
	";
	$sql = mysql_query($sql_str);
	$row = mysql_fetch_array($sql);
	if( 	$row['trr_count'] >0 ){
		return true;
	}else{
		return false;
	}
	
}

?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
</style>




<div id="mainContent">

    <div class="sats-middle-cont">

	<div class="sats-breadcrumb">
			  <ul>
				<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
				<li class="other first"><a title="<?php echo $title; ?>" href="/str_less_jobs.php"><strong><?php echo $title; ?></strong></a></li>
			  </ul>
			</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		if($_GET['success']==1){
			echo '<div class="success">Agency Update Successfull</div>';
		}
		
		// no sort yet
		if($_REQUEST['order_by']==""){
			$sort_arrow = 'up';
		}
		
		?>
		
		
		

		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
			<tr class="toprow jalign_left">
				<th>Date</th>
				<th>Job Type</th>
				<th>Tech</th>
				<th>Address</th>
				<th>Agency</th>
				<th>Job #</th>
			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){

						if( findJobsOnSTR($row['jid']) == false ){

				?>
						<tr class="body_tr jalign_left" style="background-color:<?php echo ($i%2!=0)?'#eeeeee':'' ?>">
							<td><?php echo ( $row['jdate']!='' && $row['jdate']!='0000-00-00' )?date("d/m/Y",strtotime($row['jdate'])):''; ?></td>
							<td><?php echo getJobTypeAbbrv($row['job_type']); ?></td>
							<td>
								<?php echo "{$row['FirstName']} {$row['LastName']}"; ?>
							</td>
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							<td>
								<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
								<a href="<?php echo $ci_link; ?>">
									<?php echo $row['agency_name']; ?>
								</a>
							</td>
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $row['jid']; ?></a></td>							
						</tr>
						
				<?php
					$i++;
					}

					}
				}else{ ?>
					<td colspan="100%" align="left">Empty</td>
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
		

		
	</div>
	
</div>


</body>
</html>