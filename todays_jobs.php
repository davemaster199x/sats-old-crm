<?php

$title = "Todays Jobs";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

//include('inc/precompleted_jobs_functions.php'); 

// Initiate job class
$crm = new Sats_Crm_Class();

$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$job_status = mysql_real_escape_string($_REQUEST['job_status']);
$agency = mysql_real_escape_string($_REQUEST['agency']);
//$date = date('Y-m-d');
//$job_status = 'Pre Completion';
$country_id = $_SESSION['country_default'];


$created_date = date('Y-m-d');

// sort


$order_by = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.status';
$sort = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;

$this_page = $_SERVER['PHP_SELF'];
$params = "&job_status=".urlencode($job_status);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$jparams = array(
	'job_created' => date('Y-m-d'),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'sort_list' => array(
		array(
			'order_by' => $order_by,
			'sort' => $sort
		)
	),
	'country_id' => $country_id
);
$plist = $crm->getJobsData($jparams);

$jparams = array(
	'job_created' => date('Y-m-d'),
	'country_id' => $country_id
);
$ptotal = mysql_num_rows($crm->getJobsData($jparams));


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
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong><?php echo $title; ?></strong></a></li>
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
				
				
					

				 
	
				
					
			
					
					
				
					
					<!--
					<div class='fl-left'>
						<label>Date:</label><input type=label name='date' value='<?php echo $_REQUEST['date']; ?>' class='addinput searchstyle datepicker vwjbdtp' style="width:85px!important;">		
					</div>
					-->
					
					
					<!--
					<div class="fl-left">
						<label>Job Status:</label>
						<select name="job_status" style="width: 125px;">
							<option value="">Any</option>
							<?php
							//$jt_sql = $jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'','','','',$agency,'',0,'','','','','','','','',$created_date,'j.`status`');
							while($jt =  mysql_fetch_array($jt_sql)){ ?>
								<option value="<?php echo $jt['jstatus']; ?>" <?php echo ($jt['jstatus'] == $job_status)?'selected="selected"':''; ?>><?php echo $jt['jstatus']; ?></option>
							<?php	
							}
							?>	
						</select>
					</div>
					
					
					<div class="fl-left">
						<label>Agency</label>
						<select name="agency" style="width: 125px;">
							<option value="">Any</option>
							<?php
							//$jt_sql = $jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'','','','',$agency,'',0,'','','','','','','','',$created_date,'a.`agency`');
							while($jt =  mysql_fetch_array($jt_sql)){ ?>
								<option value="<?php echo $jt['agency_id']; ?>" <?php echo ($jt['agency_id'] == $agency_id)?'selected="selected"':''; ?>><?php echo $jt['agency_name']; ?></option>
							<?php	
							}
							?>	
						</select>
					</div>
					-->
					
					
					<div class='fl-left'><label>Phrase:</label><input type=label name='phrase' value="<?php echo $_REQUEST['phrase']; ?>" class='addinput searchstyle vwjbdtp' style='width: 100px !important;'></div>
					
					<div class='fl-left' style="float:left;"><input type='submit' class='submitbtnImg' value='Search' />
					
    
					
					
					
				</div>
				
				</div>

				

				<!-- duplicated filter here -->
				
				
				
					  
					  
				</td>
				</tr>
			</table>


			
				  
			</form>
			
			
			<?php
			
			/*
			if($_REQUEST['order_by']){
				if($_REQUEST['order_by']=='ASC'){
					$ob = 'DESC';
					$sort_arrow = '<div class="arw-std-up arrow-top-active"></div>';
				}else{
					$ob = 'ASC';
					$sort_arrow = '<div class="arw-std-dwn arrow-dwn-active"></div>';
				}
			}else{
				$sort_arrow = '<div class="arw-std-up"></div>';
				$ob = 'ASC';
			}
			
			// default active
			$active = ($_REQUEST['sort']=="")?'arrow-top-active':'';
			*/
			
			// no sort yet
			if($_REQUEST['sort']==""){
				$sort_arrow = 'up';
			}
			
			?>
			
			
			

		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
			<tr class="toprow jalign_left">
			
				<th>Address</th>
				
				<th>Job ID</th>
			
				<th>Job Status</th>
			
				<th>Booked Date</th>
				
				<th>Agency</th>
				
				<th>Start Date</th>
				
				<th>End Date</th>
				
				<th>Job Comments</th>
				
				<th>Property Comments</th>
				
				<th>Preferred Time</th>
				
	
			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
						$row_color = '';
						
						if( $row['jstatus'] == 'Booked' ){
							$row_color = '#ececec';
						}
						
						
				?>
						<tr class="body_tr jalign_left" style="background-color:<?php echo $row_color; ?>">
						
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
						
							<td><a href="/view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $row['jid']; ?></a></td>
						
							<td><?php echo $row['jstatus']; ?></td>
							
							<td><?php echo ($row['jdate']!="" && $row['jdate']!="0000-00-00")?date("d/m/Y",strtotime($row['jdate'])):''; ?></td>
							
							<td>
							<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
								<a href="<?php echo $ci_link; ?>">
									<?php echo $row['agency_name']; ?>
								</a>
							</td>
							
							<td><?php echo ($row['start_date']!="" && $row['start_date']!="0000-00-00")?date("d/m/Y",strtotime($row['start_date'])):''; ?></td>
							
							<td><?php echo ($row['due_date']!="" && $row['due_date']!="0000-00-00")?date("d/m/Y",strtotime($row['due_date'])):''; ?></td>
						
							
							<td><?php echo $row['comments']; ?></td>
							
							<td><?php echo $row['p_comments']; ?></td>
							
							<td><?php echo $row['preferred_time']; ?></td>
							
				
														
			
						</tr>
						
				<?php
					$i++;
					}
				}else{ ?>
					<td colspan="14" align="left">Empty</td>
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

		<div style="margin-top: 15px; float: right; display:none;" id="rebook_div">
			<button type="button" id="btn_create_240v_rebook" class="blue-btn submitbtnImg" onclick="return confirm('Are you sure you want to create a Rebook?')">Create 240v Rebook</button>
			<button type="button" id="btn_create_rebook" class="blue-btn submitbtnImg" onclick="return confirm('Are you sure you want to create a Rebook?')">Create Rebook</button>
			<button type="button" id="btn_move_to_merged" class="submitbtnImg" style="background-color:green">Move to Merged</button>
		</div>
		
	</div>
</div>
<script src="http://code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>
<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	// datepicker
	jQuery(".datepicker").datepicker({ dateFormat: "dd/mm/yy" });
	
</script>
</body>
</html>