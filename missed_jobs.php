<?

$title = "Missed Jobs";

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');

$crm = new Sats_Crm_Class();

function getJobsNotCompleted($offset,$limit,$from_date,$to_date,$dk="",$distinct='',$reason='',$tech=''){
	
	if($distinct=="jl.`staff_id`"){
		$sel_str = " DISTINCT jl.`staff_id`, sa.`FirstName`, sa.`LastName` ";
	}else if($distinct=="jl.`contact_type`"){
		$sel_str = " DISTINCT jl.`contact_type` ";
	}else{
		$sel_str = "
			j.`id` AS jid, 
			j.`door_knock`,
			j.`created` AS jcreated,
			
			jl.`log_id`,
			jl.`staff_id`,
			jl.`eventdate`,
			jl.`eventtime`,
			jl.`comments`,
			jl.`contact_type`,

			sa.`StaffID` AS jl_staff_id,
			sa.`FirstName` AS jl_staff_fname,
			sa.`LastName` AS jl_staff_lname,
			
			ass_tech.`FirstName`, 
			ass_tech.`LastName`,    
			
			jr.`name` AS jr_name,  
			
			p.`address_1`, 
			p.`address_2`, 
			p.`address_3`,

			a.`agency_name`
		";
	}
	
	if( $from_date!="" && $to_date!="" ){
		$from_date_str = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
		$to_date_str = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
	}else{
		$from_date_str = date('Y-m-d');
		$to_date_str = date('Y-m-d');
	}
	
	if($tech!=""){
		$str .= " AND jl.`staff_id` = {$tech} ";
	}
	
	if($dk==0){
		$str .= " AND jl.`contact_type` NOT LIKE '%DK%' ";
	}
	
	$str .= " ORDER BY sa.`FirstName`, sa.`LastName` ";
	
	# Add Limit if Necessary
	if(is_numeric($offset) && is_numeric($limit))
	{
		$str .= " LIMIT {$offset}, {$limit}";
	}
	
	$jr_str = "";
	$jr_sql = mysql_query("
		SELECT * 
		FROM `job_reason` 
		".(($reason!='')?" WHERE `name` = '{$reason}' ":"")."
	");
	while($jr = mysql_fetch_array($jr_sql)){
		$jr_str .= ",'{$jr['name']}', '{$jr['name']} DK'";
	}
	
	$fr_filter = substr($jr_str,1);
	
	// get job log ONLY from not completed due to on techsheet
	// add this filter 'Status Changed from 240v Rebook' to distinguish 240v rebook of not completed logs to 240v rebook job type update in view job details
	$sql = "
		SELECT 
			{$sel_str}
		FROM  `job_log` AS jl
		LEFT JOIN `jobs` AS j ON jl.`job_id` = j.`id` 
		LEFT JOIN `job_reason` AS jr ON j.`job_reason_id` = jr.`job_reason_id` 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		LEFT JOIN `staff_accounts` AS ass_tech ON j.`assigned_tech` = ass_tech.`StaffID`
		LEFT JOIN `staff_accounts` AS sa ON jl.`staff_id` = sa.`StaffID`
		WHERE jl.`contact_type` 
		IN (
			{$fr_filter}
		)
		AND a.`status` =  'active'
		AND p.`deleted` =0
		AND j.`del_job` = 0
		AND jl.`deleted` = 0
		AND jl.`eventdate` BETWEEN '{$from_date_str}' AND '{$to_date_str}'
		AND a.`country_id` ={$_SESSION['country_default']}	
		AND jl.`comments` NOT LIKE '%Status Changed from 240v Rebook%'
		{$str}
	";

	echo "<div style='display:none;'>{$sql}</div>";
	
	return mysql_query($sql);
}

$search = "";
$agency = "";
$from_date = ($_REQUEST['from_date']!="")?mysql_real_escape_string($_REQUEST['from_date']):date('d/m/Y');
$to_date = ($_REQUEST['to_date']!="")?mysql_real_escape_string($_REQUEST['to_date']):date('d/m/Y');
$reason = mysql_real_escape_string($_REQUEST['reason']);
$tech = mysql_real_escape_string($_REQUEST['tech']);
$dk = ($_REQUEST['dk']!="")?mysql_real_escape_string($_REQUEST['dk']):0;


$start = (intval($_REQUEST['start']) > 0 ? intval($_REQUEST['start']) : 0);
$ts_safety_switch = $_REQUEST['ts_safety_switch'];


if($_POST['postcode_region_id']){
	$filterregion = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$filterregion = $_GET['postcode_region_id'];
	//echo $filterregion;
}

// header sort parameters
$sort = $_REQUEST['sort'];
$order_by = $_REQUEST['order_by'];

$sort = ($sort)?$sort:'a.agency_name';
$order_by = ($order_by)?$order_by:'ASC';

// pagination script
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;
$this_page = $_SERVER['PHP_SELF'];

$params = "&from_date={$from_date}&to_date={$to_date}&dk={$dk}&reason={$reason}&tech={$tech}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

if($_POST['search_flag']==1){
	
	$propertylist = getJobsNotCompleted($offset,$limit,$from_date,$to_date,$dk,'',$reason,$tech);
	$ptotal = mysql_num_rows(getJobsNotCompleted('','',$from_date,$to_date,$dk,'',$reason,$tech));
	
}


//$propertylist = getPropertyList($agency, $search, PER_PAGE, $start, 0 , $ts_safety_switch);
$totalFound = getFoundRows();
$pagination_tabs = ceil($totalFound / PER_PAGE);

$start_display = $start + 1;


$export_link = "export_all_properties.php?searchsuburb={$search}&agency={$agency}";

?>


<div id="mainContentCalendar">
  <div class="sats-middle-cont">
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="/missed_jobs.php"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
   <?php
   if($_GET['perm_del']==1){ ?>
		<div class="success">Property Delete Successful</div>
   <?php
   }
   ?>
   
   <div id="view-jobs" class="aviw_drop-h aviw_drop-vp" style="border: 1px solid #cccccc;">

		<form method="POST">		 
		<div class="fl-left">
			<label>Date:</label>
			<input type="label" style="width:85px!important; float:none;" class="addinput searchstyle datepicker" name="from_date" value="<?php echo ($from_date!="")?$from_date:''; ?>" />		
			 - <input type="label" style="width:85px!important; float:none;" class="addinput searchstyle datepicker" name="to_date" value="<?php echo ($to_date!="")?$to_date:''; ?>" />
		</div>
		
		<?php
			if($_POST['search_flag']==1){
				$ajt_sql = getJobsNotCompleted('','',$from_date,$to_date,'','jl.`staff_id`');
			}
			
		  ?>
		<div class="fl-left">
			<label>Tech:</label>
			<select name="tech">
				<option value="">Any</option>
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
				
				<option value="<?php echo $tech['StaffID']; ?>" <?php echo ($tech['StaffID']==$tech_id)?'selected="selected"':''; ?>>
					<?php 
						echo $crm->formatStaffName($tech['FirstName'],$tech['LastName']).( ( $tech['is_electrician'] == 1 )?' [E]':null ); 
					?>
				</option>
				
				<?php	
				}
				?>
			</select>
		</div>
		
		 <?php
			 if($_POST['search_flag']==1){
				$ajt_sql = getJobsNotCompleted('','',$from_date,$to_date,'','jl.`contact_type`');
			 }			
		  ?>
		<div class="fl-left">
			<label>Reason:</label>
			<select name="reason" style="width: 125px;">
				<option value="">Any</option>
				<?php	
				$job_res_sql =  mysql_query("
					SELECT * 
					FROM  `job_reason` 
				");
				while($job_res=mysql_fetch_array($job_res_sql)){ ?>
					<option value="<?php echo $job_res['name']; ?>" <?php echo ($reason==$job_res['name']) ? 'selected="selected"':''; ?>><?php echo $job_res['name']; ?></option>
				<?php
				}
				?>
			</select>
		</div>
		
		<?php
		if($_POST['search_flag']==1){ ?>
			<div class="fl-left">
				<label>DK:</label>
				<select name="dk" id="dk">
					<option value="1" <?php echo ($dk==1) ? 'selected="selected"':''; ?>>Show DK</option>
					<option value="0" <?php echo ($dk==0) ? 'selected="selected"':''; ?>>Hide DK</option>
				</select>
			</div>
		<?php	
		}
		?>
		
			
		<div style="float:left;" class="fl-left">
			<input type="hidden" name="search_flag" value="1" />
			<input type="submit" value="Search" class="submitbtnImg">
		</div>
		</form>
  
	</div>
  
<?php

if($_POST['search_flag']==1){ ?>


<form method="POST" action="<?=URL;?>active_properties.php" class="searchstyle">
<table cellpadding=0 cellspacing=0 >
<tr class="tbl-view-prop">
  <td>
  
   
	
	<table border=0 cellspacing=1 cellpadding=5 width="100%" class="table-left tbl-fr-red">
	  <tr bgcolor="#b4151b">
		<th style="width:73px;"><b>Date</b></th>
		<th><b>Age</b></th>
		<th style="width:73px;"><b>Time</b></th>
		<th><b>Technician</b></th> 
		<th style="width: 300px;"><b>Property</b></th>
		<th style="width: 300px;"><b>Agency</b></th>				
		<th style="width: 80px;"><b>Door Knock</b></th>
		<th style="width: 150px;"><b>Reason</b></th>
		<th><b>Comments</b></th>				
	  </tr>
	  <?php
		
			$odd = 0;
			$old_staff = "";
			$i = 0;

			if($_POST['search_flag']==1){
				
				while($row=mysql_fetch_array($propertylist)){
					// tech color altenate
					if($row['staff_id']!=$old_staff){
						$old_staff = $row['staff_id'];
						$i++;
					}
					
														
					if ($i%2==0) {

						echo "<tr bgcolor=#FFFFFF>";

					} else {

						echo "<tr bgcolor=#eeeeee>";

					}
					?>
					<td style="width:73px;">
					<input type="hidden" class="log_id" value="<?php echo $row['log_id']; ?>" />
					<?php 
					echo ($row['eventdate']!="")?date("d/m/Y",strtotime($row['eventdate'])):'';
					?>
					</td>
					<td>
					<?php
					// Age
					$date1=date_create($row['jcreated']);
					$date2=date_create(date('Y-m-d'));
					$diff=date_diff($date1,$date2);
					$age = $diff->format("%r%a");
					$age_val = (((int)$age)!=0)?$age:0;
					echo $age_val;
					$age_val_tot += $age_val;
					?>
					</td>
					<td style="width:73px;"><?php echo ($row['eventtime']!="")?$row['eventtime']:''; ?></td>
					<td style="padding-left: 5px;"><?php echo "{$row['jl_staff_fname']} ".strtoupper(substr($row['jl_staff_lname'],0,1))."."; ?></td>
					<td style="width: 300px;"><a href="/view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo "{$row['address_1']} {$row['address_2']}, {$row['address_3']}"; ?></a></td>
					<td style="width: 300px;"><?php echo $row['agency_name']; ?></td>
					<td style="width: 80px;">
					<?php 
					// DK
					echo (strpos($row['contact_type'],"DK"))?'Yes':'';
					?>
					</td>
					
					<td style="width: 150px;">
					<?php 
					// contact type
					echo (strpos($row['contact_type'],"DK"))?str_replace("DK",'',$row['contact_type']):$row['contact_type'];
					?>
					</td>
					<td><?php echo $row['comments']; ?></td>
					
					<?php
					echo "</tr>\n" ;
					
					if($row['staff_id']!=$old_staff){
						
					}

				}
				
			}					

			// (5) Close the database connection
			?>
	  
	</table>
	
	</td>
</tr>
</table>
</form>


<?php

if($_POST['search_flag']==1){
	
	// Initiate pagination class
	$jp = new jPagination();
	
	$per_page = $limit;
	$page = ($_GET['page']!="")?$_GET['page']:1;
	$offset = ($_GET['offset']!="")?$_GET['offset']:0;	
	
	echo $jp->display($page,$ptotal,$per_page,$offset,$params);
	
}


?>


<?php	
}else{ ?>

	<h2 style="text-align:left;">Press 'Search' to Display Results</h2>

<?php	
}
?>	
  
    
	
  </div>

</div>

  <br class="clearfloat" />
  

</body></html>