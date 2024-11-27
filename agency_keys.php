<?php

$title = "Agency Keys";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

//include('inc/precompleted_jobs_functions.php'); 

$crm = new Sats_Crm_Class();

function jgetAgencyKeys($start,$limit,$sort,$order_by,$agency,$tech_id,$from_date,$to_date,$distinct=''){

	$str = "";
	$sel_str = "";
	
	//echo $distinct;
	
	if($distinct!=""){
		
		switch($distinct){
			case 'p.`agency_id`':
				$sel_str = " DISTINCT p.`agency_id`, a.`agency_name` ";
			break;
			case 'j.`assigned_tech`':
				$sel_str = " DISTINCT sa.`StaffID`, sa.`FirstName`, sa.`LastName` ";
			break;
		}
		
	}else{
		$sel_str = "
			*, 
			
			j.`id` AS jid, 
			j.`created` AS jcreated, 
			j.`service` AS jservice, 			
			j.`status` AS jstatus, 
			j.`date` AS jdate, 
			
			p.`address_1` AS p_address_1, 
			p.`address_2` AS p_address_2, 
			p.`address_3` AS p_address_3, 
			p.`state` AS p_state, 			
			
			jr.`name` AS jr_name, 
			
			a.`phone` AS a_phone
		";
	}
	
	if($agency!=""){
		$str .= " AND p.`agency_id` = '{$agency}' ";  
	}
	
	if($tech_id!=""){
		$str .= " AND sa.`StaffID` = '{$tech_id}' ";  
	}
	
	if( $from_date!="" && $to_date!="" ){
		$from_date_str = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
		$to_date_str = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
	}else{
		$from_date_str = date('Y-m-d');
		$to_date_str = date('Y-m-d');
	}

	if( $sort!="" && $order_by!="" ){
		$str .= " ORDER BY {$sort} {$order_by} ";
	}

	if(is_numeric($start) && is_numeric($limit))
	{
		$str .= " LIMIT {$start}, {$limit}";
	}
	
	$sql = "
		SELECT 
			{$sel_str}
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `job_reason` AS jr ON j.`job_reason_id` = jr.`job_reason_id`
		LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
		WHERE p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`phone_call_req` = 1
		AND a.`key_allowed` = 1
		AND j.`key_access_required` = 1
		AND j.`date` BETWEEN '{$from_date_str}' AND '{$to_date_str}'
		AND a.`country_id` = {$_SESSION['country_default']}		
		{$str}
	";
	return mysql_query($sql);

}

$agency = mysql_real_escape_string($_REQUEST['agency']);
$tech = mysql_real_escape_string($_REQUEST['tech']);
$from_date = ($_REQUEST['from_date']!="")?mysql_real_escape_string($_REQUEST['from_date']):date('d/m/Y');
$to_date = ($_REQUEST['to_date']!="")?mysql_real_escape_string($_REQUEST['to_date']):date('d/m/Y');

// sort
$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'a.`agency_name`';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&job_type=".urlencode($job_type)."&service=".urlencode($service)."&date=".urlencode($date)."&date=".urlencode($phrase);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$plist = jgetAgencyKeys($offset,$limit,$sort,$order_by,$agency,$tech,$from_date,$to_date);
$ptotal = mysql_num_rows(jgetAgencyKeys('','',$sort,$order_by,$agency,$tech,$from_date,$to_date));




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
					
					<div class="fl-left">
						<label>Date:</label>
						<input type="label" style="float:none;" class="addinput searchstyle datepicker" name="from_date" value="<?php echo ($from_date!="")?$from_date:''; ?>" />		
						 - <input type="label" style="float:none;" class="addinput searchstyle datepicker" name="to_date" value="<?php echo ($to_date!="")?$to_date:''; ?>" />
					</div>
					
					<?php
						$ajt_sql = jgetAgencyKeys('','','a.`agency_name`','ASC',null,$tech,$from_date,$to_date,'p.`agency_id`');
					?>
					<div class="fl-left">
						<label>Agency:</label>
						<select name="agency" style="width: 125px;">
							<option value="">Any</option>
							<?php				
							while($ajt=mysql_fetch_array($ajt_sql)){ ?>
							<option value="<?php echo $ajt['agency_id']; ?>" <?php echo ($agency==$ajt['agency_id']) ? 'selected="selected"':''; ?>><?php echo $ajt['agency_name']; ?></option>					
							<?php
							}
							?>
						</select>
					</div>

					<?php
						$ajt_sql = jgetAgencyKeys('','','sa.`FirstName`','ASC',$agency,null,$from_date,$to_date,'j.`assigned_tech`');
					?>
					<div class="fl-left">
						<label>Tech:</label>
						<select name="tech" style="width: 125px;">
							<option value="">Any</option>
							<?php				
							while($ajt=mysql_fetch_array($ajt_sql)){ ?>
							<option value="<?php echo $ajt['StaffID']; ?>" <?php echo ($tech==$ajt['StaffID']) ? 'selected="selected"':''; ?>><?php echo "{$ajt['FirstName']} {$ajt['LastName']}"; ?></option>					
							<?php
							}
							?>		
						</select>
					</div>
					
					

					
					<div class='fl-left' style="float:left;">
						<input type='submit' class='submitbtnImg' value='Search' />
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
			
				<th>Date</th>
				
				<th>Address</th>
				
				<th>Service</th>
				
				<th>Tech</th>

				<th>Job #</th>
				
				<th>Booked With</th>
				<th>Agency</th>
				<th>Phone</th>
				
			</tr>
				<?php
				
				
				
				$old_agen = "";
				$i = 0;
					
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
					// tech color altenate
					if($row['agency_id']!=$old_agen){
						$old_agen = $row['agency_id'];
						$i++;
					}
					
					if ($i%2==0) {
						$row_color = 'style="background-color:#eeeeee;"';
					}else{
						$row_color = 'style="background-color:#ffffff;"';
					}
					
					if($row['ts_completed']==1){
						$row_color = 'style="background-color:#dfffa5;"';
					}
						
					?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							<td><?php echo ($row['jdate']!="" && $row['jdate']!="0000-00-00")?date("d/m/Y",strtotime($row['jdate'])):''; ?></td>
							
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
		
							<td><img src="images/serv_img/<?php echo getServiceIcons($row['jservice']); ?>" /></td>
							
							<td><a href="/view_tech_schedule_day.php?id=<?php echo $row['StaffID']; ?>&day=<?php echo date("d",strtotime($row['jdate'])); ?>&month=<?php echo date("m",strtotime($row['jdate'])); ?>&year=<?php echo date("Y",strtotime($row['jdate'])); ?>"><?php echo $crm->formatStaffName($row['FirstName'],$row['LastName']); ?></a></td>
							
							
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $row['jid']; ?></a></td>							
							
							<td><?php echo $row['booked_with']; ?></td>
							<td>
								<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
								<a href="<?php echo $ci_link; ?>"><?php echo $row['agency_name']; ?></a>
							</td>
							<td><?php echo $row['a_phone']; ?></td>
							
						</tr>
						
					<?php
					// $tot_price += $row['job_price'];
					// $i++;
					}
					?>
					
					
					
					<?php
				}else{ ?>
					<td colspan="9" align="left">Empty</td>
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

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	

	
	// REBOOKS
	// 240v
	jQuery("#btn_create_240v_rebook").click(function(){
		
		if(confirm("Are you sure you want to continue?")==true){
			
			var job_id = new Array();
			jQuery(".chkbox:checked").each(function(){
				job_id.push(jQuery(this).val());
			});
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_rebook_script.php",
				data: { 
					job_id: job_id,
					is_240v: 1
				}
			}).done(function( ret ){
				window.location="/precompleted_jobs.php";
			});				
			
		}
		
	});
	
	// rebook
	jQuery("#btn_create_rebook").click(function(){
		
		if(confirm("Are you sure you want to continue?")==true){
			
			var job_id = new Array();
			jQuery(".chkbox:checked").each(function(){
				job_id.push(jQuery(this).val());
			});
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_rebook_script.php",
				data: { 
					job_id: job_id,
					is_240v: 0
				}
			}).done(function( ret ){
				window.location="/precompleted_jobs.php";
			});				
			
		}
		
	});
	
	// merged certificate
	jQuery("#btn_move_to_merged").click(function(){
		
		if(confirm("Are you sure you want to continue?")==true){
			
			var job_id = new Array();
			var has_yellow_mark = 0;
			jQuery(".chkbox:checked").each(function(){
				if(jQuery(this).parents("tr:first").hasClass("yello_mark")==true){
					has_yellow_mark = 1;
				}else{
					job_id.push(jQuery(this).val());
				}
				
			});
			
			if(has_yellow_mark==0){
				
				jQuery.ajax({
					type: "POST",
					url: "ajax_move_to_merged.php",
					data: { 
						job_id: job_id,
						is_240v: 0
					}
				}).done(function( ret ){
					window.location="/precompleted_jobs.php";
				});	
				
			}else{
				alert("Yellow highlighted row canot be moved to merged");
			}
						
			
		}
		
	});
	
	
	// toggle 240v job type dropdown
	jQuery(".btn_240v").click(function(){
		
		jQuery(this).parents("tr:first").find(".240v_jt_lbl").toggle();
		jQuery(this).parents("tr:first").find(".240v_change_jt").toggle();
		
	});
	
	// update 240v job type
	jQuery(".240v_change_jt").change(function(){
		
		var job_id = jQuery(this).parents("tr:first").find(".hid_job_id").val();
		var job_type = jQuery(this).val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_job_type.php",
			data: { 
				job_id: job_id,
				job_type: job_type
			}
		}).done(function( ret ){
			window.location="/precompleted_jobs.php";
		});	
		
	});
	
	
	// check all toggle
	jQuery("#maps_check_all").click(function(){
		
		if(jQuery(this).prop("checked")==true){
			jQuery(".chkbox").prop("checked",true);
			jQuery("#rebook_div").show();
		}else{
			jQuery(".chkbox").prop("checked",false);
			jQuery("#rebook_div").hide();
		}
	  
	});
	
	// toggle hide/show remove button
	jQuery(".chkbox").click(function(){

	  var chked = jQuery(".chkbox:checked").length;
	  
	  console.log(chked);
	  
	  if(chked>0){
		jQuery("#rebook_div").show();
	  }else{
		jQuery("#rebook_div").hide();
	  }

	});
	
});
</script>
</body>
</html>