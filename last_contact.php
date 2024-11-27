<?php

$title = "Last Contact";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

//include('inc/last_contact_class.php'); 

// Initiate job class
$jc = new Last_Contact_Class();

$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$job_type = mysql_real_escape_string($_REQUEST['job_type']);
$service = mysql_real_escape_string($_REQUEST['service']);
$state = mysql_real_escape_string($_REQUEST['state']);
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$job_status = 'Escalate';

// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'last_contact';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;

$this_page = $_SERVER['PHP_SELF'];
$params = "&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


$plist = $jc->getJobs($offset,$limit,$sort,$order_by,$state);
$ptotal = mysql_num_rows($jc->getJobs('','','','',$state));

?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.yello_mark{
	background-color: #ffff9d;
}
.green_mark{
	background-color: #c2ffa7;
}
</style>





<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Agency Update Successfull</div>';
		}
		
		
		//echo date('Y-m-d', strtotime("-30 days"));
		
		
		?>
		
		
		
		<form method="POST" name='example' id='example'>
			<input type='hidden' name='status' value='<?php echo $status ?>'>

			<table border=1 cellpadding=0 cellspacing=0 width="100%">
				<tr class="tbl-view-prop">
				<td>

				<div class="aviw_drop-h aviw_drop-vp" id="view-jobs">

				 
	
				 <?php
				 // get distinct state
				 $state_arr = [];
				 $jstate_sql = $jc->getJobs('','',$sort,$order_by,$state);
				 while( $jstate =  mysql_fetch_array($jstate_sql) ){ 							
					if( !in_array($jstate['p_state'], $state_arr) ){
						$state_arr[] = $jstate['p_state'];
					}
				 }
				 ?>
				
					
					<div class="fl-left">
						<label><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?>:</label>
						<select id="state" name="state" style="width: 70px;">
						<option value="">Any</option> 			
						<?php
						foreach( $state_arr as $state_val ){ 
						?>
							<option value="<?php echo $state_val; ?>" <?php echo ($state_val==$state) ? 'selected="selected"':''; ?>><?php echo $state_val; ?></option>
						<?php	
						} 
						?>
					 </select>
					</div>
				
					<div class='fl-left' style="float:left;">
						<input type='submit' class='submitbtnImg' value='Search' />
					</div>
				
					  
					  
				</td>
				</tr>
			</table>	  
				  
			</form>
		
		
		
			
			
			<?php
			
			// no sort yet
			if($_REQUEST['sort']==""){
				$sort_arrow = 'up';
			}
			
			?>

		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
			<tr class="toprow jalign_left">

				<th>Last Contact</th>
				<th>Days</th>
			
				<th>
					<div class="tbl-tp-name colorwhite bold">Date</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=j.date&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&job_type=<?php echo $job_type; ?>&service=<?php echo $service; ?>&date=<?php echo $date; ?>&phrase=<?php echo $phrase; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='j.date')?'active':''; ?>"></div>
					</a>
				</th>
			
				<th>Job Type</th>
				
				<th>
					<div class="tbl-tp-name colorwhite bold">Service</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=j.service&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&job_type=<?php echo $job_type; ?>&service=<?php echo $service; ?>&date=<?php echo $date; ?>&phrase=<?php echo $phrase; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='j.service')?'active':''; ?>"></div>
					</a>
				</th>
				
				
				<th>
					<div class="tbl-tp-name colorwhite bold">Address</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=p.address_3&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&job_type=<?php echo $job_type; ?>&service=<?php echo $service; ?>&date=<?php echo $date; ?>&phrase=<?php echo $phrase; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='p.address_3')?'active':''; ?>"></div>
					</a>
				</th>
		
				<th><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></th>
				<th>Agency</th>
				<th style="width:20%">Comments</th>
				<th>Job #</th>
				
				<th>Created</th>
				
				<th><div class="tbl-tp-name colorwhite bold"><input type="checkbox" id="maps_check_all" /></div></th>
				
				
			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
					// grey alternation color
					$row_color = ($i%2==0)?"style='background-color:#eeeeee;'":"";	
					
				?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							<td><?php echo date("d/m/Y",strtotime($row['last_contact'])); ?></td>
							<td>
							<?php
							  $now = time(); // or your date as well
							 $your_date = strtotime($row['last_contact']);
							 $datediff = $now - $your_date;
							 echo floor($datediff/(60*60*24));
							?>
							</td>
							
							<td><?php echo ($row['jdate']!="" && $row['jdate']!="0000-00-00")?date("d/m/Y",strtotime($row['jdate'])):''; ?></td>
							
							
							
							<td><?php echo getJobTypeAbbrv($row['job_type']); ?></td>
							
							<td><img src="images/serv_img/<?php echo getServiceIcons($row['jservice']); ?>" /></td>
	
							
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							
							<td><?php echo $row['p_state']; ?></td>
							<td><?php echo $row['agency_name']; ?></td>
							<td><?php echo $row['comments']; ?></td>
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $row['jid']; ?></a></td>							
							
							<td><?php echo ($row['jcreated']!="" && $row['jcreated']!="0000-00-00")?date("d/m/Y",strtotime($row['jcreated'])):''; ?></td>
							
							<td>
								<input type="checkbox" class="maps_chk_box" value="<?php echo $row['jid']; ?>" />
								<input type="hidden" class="hid_job_id" value="<?php echo $row['jid']; ?>" />
							</td>
						
						</tr>
						
				<?php
					$i++;
					}
				}else{ ?>
					<td colspan="12" align="left">Empty</td>
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
		
		
		<div style="margin-top: 15px; float: right; display:none;" id="map_div">
			Tech:
			<select id="maps_tech">
				<option value="">-- select --</option>
				<?php
				$tech_sql = mysql_query("
					SELECT sa.`StaffID`, sa.`FirstName`, sa.`LastName`, sa.`is_electrician`, sa.`active` AS sa_active
					FROM `staff_accounts` AS sa
					LEFT JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
					WHERE ca.`country_id` ={$_SESSION['country_default']}
					AND sa.`Deleted` = 0
					AND sa.`ClassID` = 6
					AND sa.`active` = 1
					ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
				");
				while($tech = mysql_fetch_array($tech_sql)){ ?>
				
					<option value="<?php echo $tech['StaffID']; ?>">
						<?php 
							echo $crm->formatStaffName($tech['FirstName'],$tech['LastName']).( ( $tech['is_electrician'] == 1 )?' [E]':null ); 
						?>
					</option>
				
				<?php	
				}
				?>
			</select>
			Date:
			<input type="text" id="maps_date" class="datepicker" />
			<button type="button" id="btn_assign" class="blue-btn submitbtnImg">Assign</button>
			
		</div>
		
	</div>
</div>


<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	

	
	// check all toggle
	jQuery("#maps_check_all").click(function(){
  
	  if(jQuery(this).prop("checked")==true){
		jQuery(".maps_chk_box:visible").prop("checked",true);
		jQuery("#map_div").show();
	  }else{
		jQuery(".maps_chk_box:visible").prop("checked",false);
		jQuery("#map_div").hide();
	  }
	  
	});
	
	// toggle hide/show remove button
	jQuery(".maps_chk_box").click(function(){

	  var chked = jQuery(".maps_chk_box:checked").length;
	  
	  if(chked>0){
		jQuery("#map_div").show();
	  }else{
		jQuery("#map_div").hide();
	  }

	});
	
	// move to maps 
	jQuery("#btn_assign").click(function(){
		
		var job_id = new Array();
		var tech_id = jQuery("#maps_tech").val();
		var date = jQuery("#maps_date").val();
		
		jQuery(".maps_chk_box:checked").each(function(){
			job_id.push(jQuery(this).val());
		});
		jQuery.ajax({
			type: "POST",
			url: "ajax_move_to_maps.php",
			data: { 
				job_id: job_id,
				tech_id: tech_id,
				date: date
			}
		}).done(function( ret ){
			//window.location='/maps.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>';
			location.reload();
		});	
				
	});
	
});
</script>


</body>
</html>