<?php

$title = "Tech Keys";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');



$tech_id = mysql_real_escape_string($_REQUEST['tech_id']);
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';

// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.`due_date`';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'DESC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort=".urlencode($sort)."&order_by=".urlencode($order_by)."&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase)."&postcode_region_id=".$filterregion;

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

// distinct agency_id`
$a_sql_str = "
	SELECT DISTINCT a.`agency_id`, a.`agency_name`
	FROM jobs AS j
	LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
	LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
	LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
	WHERE p.`deleted` =0
	AND a.`status` = 'active'
	AND j.`del_job` = 0
	AND a.`country_id` = {$_SESSION['country_default']}
	AND j.`key_access_required` = 1
	AND j.`assigned_tech` ={$tech_id}
	AND j.`date` = '{$date}'		
";
$a_sql = mysql_query($a_sql_str);
//$ptotal = mysql_num_rows($jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'','','','','',$filterregion));


function getPropertyKeysPerAgency($tech_id,$date,$country_id,$agency_id){
	// jobs
	$p_sql_str = "
	SELECT 
		j.`id` AS jid, 
		j.`service` AS j_service, 
		j.`key_access_details`,

		p.`property_id`, 
		p.`address_1` AS p_address_1, 
		p.`address_2` AS p_address_2, 
		p.`address_3` AS p_address_3, 
		p.`state` AS p_state, 
		p.`postcode` AS p_postcode, 
		p.`key_number`, 
		p.`lat` AS p_lat, 
		p.`lng` AS p_lng,

		a.`agency_id`, 
		a.`agency_name`, 
		a.`address_1` AS a_address_1, 
		a.`address_2` AS a_address_2, 
		a.`address_3` AS a_address_3, 
		a.`state` AS a_state, 
		a.`postcode` AS a_postcode, 
		a.`phone` AS a_phone,
		a.`allow_dk`
	FROM jobs AS j
	LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
	LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
	LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
	WHERE p.`deleted` =0
	AND a.`status` = 'active'
	AND j.`del_job` = 0
	AND a.`country_id` = {$country_id}
	AND j.`key_access_required` = 1
	AND j.`assigned_tech` ={$tech_id}
	AND j.`date` = '{$date}'
	AND a.`agency_id` = {$agency_id}
	";
	return $plist = mysql_query($p_sql_str);
}


function getKeyRoutesPerAgency($tech_id,$date,$country_id,$agency_id){
	$sql = "
		SELECT 
			kr.`key_routes_id`, kr.`action`, kr.`number_of_keys`, kr.`agency_staff`, kr.`completed`, kr.`completed_date`, kr.`sort_order`,
			a.`agency_id`, a.`agency_name`, a.`address_1`, a.`address_2`, a.`address_3`, a.`state`, a.`postcode`, a.`phone`, a.`agency_hours`, a.`lat`, a.`lng`
		FROM `key_routes` AS kr
		LEFT JOIN `agency` AS a ON kr.`agency_id` = a.`agency_id`
		WHERE kr.`date` = '{$date}'
		AND ( 
			kr.`deleted` = 0 
			OR kr.`deleted` IS NULL 
		)
		AND a.`country_id` = {$country_id}
		AND kr.`tech_id` ={$tech_id}
		AND kr.`agency_id` = {$agency_id}
	";
	return mysql_query($sql);
}

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
.prop_address{
	width:30%;
}
.prop_key_num{
	width:20%;
}
</style>



<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="/tech_keys.php?tech_id=<?php echo $tech_id; ?>&date=<?php echo $date; ?>"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Agency Update Successfull</div>';
		}
		
		
		//echo date('Y-m-d', strtotime("-30 days"));
		
		
		?>
		
		
		
			
			
			<?php
			
			// no sort yet
			if($_REQUEST['sort']==""){
				$sort_arrow = 'up';
			}
			
			?>

		
		
				<?php
				
				
			
					while($a_row = mysql_fetch_array($a_sql)){ 
						
						$row_sql = getPropertyKeysPerAgency($tech_id,$date,$_SESSION['country_default'],$a_row['agency_id']);
						$num_of_keys = mysql_num_rows($row_sql);
						$nok_txt = ($num_of_keys>1)?"{$num_of_keys} KEYS":"{$num_of_keys} KEY";
					?>
						
						<h2 class="heading"><?php echo $a_row['agency_name']; ?> (<?php echo $nok_txt; ?>)</h2>
						
						<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
						<tr class="toprow jalign_left">
							<th class="prop_address">Address</th>
							<th class="prop_key_num">Key Number</th>
							<th class="prop_key_num">Approved By</th>
							<th class="prop_key_num">Agency Staff</th>
							<th class="prop_key_num">Number of Keys</th>
						</tr>
						
						<?php
						
						// grey alternation color
						
						$i = 0;
						while( $row = mysql_fetch_array($row_sql) ){
					
						//$row_color = ($i%2==0)?"":"style='background-color:#eeeeee;'";
					
						?>
						
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>							
							<td class="prop_address"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></td>
							<td class="prop_key_num">
								<a href="javascript:void(0);" class="key_num_link"><?php echo ($row['key_number']!="")?$row['key_number']:'+ Key Number'; ?></a>
								<input type="text" style="display:none;" class="addinput key_num_hid" value="<?php echo $row['key_number']; ?>" />
								<input type="hidden" class="property_id" value="<?php echo $row['property_id']; ?>" />
							</td>
							<td><?php echo $row['key_access_details']; ?></td>
							<td></td>									
							<td></td>
						</tr>
						
						
						<?php	
						}
						?>
						
						<?php
						$keys_sql = getKeyRoutesPerAgency($tech_id,$date,$_SESSION['country_default'],$a_row['agency_id']);
						while( $key = mysql_fetch_array($keys_sql) ){ 
						
							if($key['completed']==1){
								$bgcolor = "#c2ffa7";
								if($key['action']=='Pick Up'){
									$kr_action = 'Picked Up';
								}else if($key['action']=='Drop Off'){
									$kr_action = 'Dropped Off';
								}
							}else{
								$bgcolor = "#ffffff";
								if($key['action']=='Pick Up'){
									$kr_action = 'Pick Up';
								}else if($key['action']=='Drop Off'){
									$kr_action = 'Drop Off';
								}
							}
						
						?>
							<tr class="body_tr jalign_left" style="background-color:<?php echo $bgcolor; ?>">
								<td class="prop_address">
									<input type="hidden" class="key_routes_id" value="<?php echo $key['key_routes_id']; ?>" />
									<input type="hidden" class="key_completed" value="<?php echo $key['completed']; ?>" />
									<input type="hidden" class="key_action" value="<?php echo $key['action']; ?>" />
									<a href="javascript:void(0);" class="link_keys"><?php echo $kr_action; ?></a>
									
									<div class="agency_staff_div" style="display:none;">
										Agency Staff: <input type="text" class="agency_staff" style="margin-bottom: 3px;" value="<?php echo $kr_arr[$j]['agency_staff']; ?>" /><br />
										Number of Keys: <input type="text" class="number_of_keys" style="margin-bottom: 3px;" value="<?php echo $kr_arr[$j]['number_of_keys']; ?>" />
										<button type="button" class="blue-btn submitbtnImg btn_agency_staff" style="margin-left: 10px;">Go</button>
									</div>
								</td>
								<td></td>
								<td></td>
								<td class="prop_key_num"><?php echo $key['agency_staff']; ?></td>								
								<td><?php echo $key['number_of_keys']; ?></td>								
							</tr>
						
						<?php
						
						$i++;
						
						}
						
						?>
						
						</table>
						
				<?php
				
				}
				?>
				
				
				
				
		

		<?php

		/*
		// Initiate pagination class
		$jp = new jPagination();
		
		$per_page = $limit;
		$page = ($_GET['page']!="")?$_GET['page']:1;
		$offset = ($_GET['offset']!="")?$_GET['offset']:0;	
		
		echo $jp->display($page,$ptotal,$per_page,$offset,$params);
		*/
		
		?>

		
		
	</div>
</div>
<script src="http://code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>
<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	// Key Number Update
	jQuery(".prop_key_num").click(function(){
		
		jQuery(this).parents("tr:first").find(".key_num_link").hide();
		jQuery(this).parents("tr:first").find(".key_num_hid").show();
		
	});
	
	jQuery(".key_num_hid").blur(function(){
		
		var property_id = jQuery(this).parents("tr:first").find(".property_id").val();
		var key_number = jQuery(this).val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_tech_keys_update_key_number.php",
			data: { 
				property_id: property_id,
				key_number: key_number
			}
		}).done(function( ret ){
			window.location="/tech_keys.php?tech_id=<?php echo $tech_id; ?>&date=<?php echo $date; ?>";
		});
		
		//console.log('on blur');
		//jQuery(this).parents("tr:first").find(".key_num_link").show();
		//jQuery(this).parents("tr:first").find(".key_num_hid").hide();
		
		
	});
	
	// keys link
	jQuery(".link_keys").click(function(){
		
		var obj = jQuery(this);
		var kr_id = obj.parents("tr:first").find(".key_routes_id").val();
		var key_completed = obj.parents("tr:first").find(".key_completed").val();
		var key_action = obj.parents("tr:first").find(".key_action").val();
		
		if(key_completed==0 ){
			obj.parents("tr:first").find(".agency_staff_span").hide();
			obj.parents("tr:first").find(".agency_staff_div").show();
		}else{
			markKeysCompleted(obj,kr_id,key_completed);
		}
		
	});
	
	function markKeysCompleted(obj,kr_id,key_completed,agency_staff,number_of_keys){
	
		jQuery.ajax({
			type: "POST",
			url: "ajax_mark_key_as_completed.php",
			data: { 
				kr_id: kr_id,
				key_completed: key_completed,
				agency_staff: agency_staff,
				number_of_keys: number_of_keys
			}
		}).done(function( ret ) {
			
			/*
			var k_comp = parseInt(ret);
			if(k_comp==1){
				var k_color = "#c2ffa7";
				var key_acction = obj.parents("tr:first").find(".key_action").val();
				if(key_acction=='Pick Up'){
					var k_txt = 'Picked Up';
				}else if(key_acction=='Drop Off'){
					var k_txt = 'Dropped Off';
				}
				obj.parents("tr:first").find(".agency_staff_span").show();
				obj.parents("tr:first").find(".agency_staff_div").hide	();
			}else{
				var k_color = "#FFFFFF";
				var key_acction = obj.parents("tr:first").find(".key_action").val();
				if(key_acction=='Pick Up'){
					var k_txt = 'Pick Up';
				}else if(key_acction=='Drop Off'){
					var k_txt = 'Drop Off';
				}
			}
			obj.parents("tr:first").find(".key_completed").val(ret);
			obj.parents("tr:first").attr("bgcolor",k_color);
			obj.parents("tr:first").find(".link_keys").html(k_txt);
			if(agency_staff!=""){
				obj.parents("tr:first").find(".agency_staff_span").html("Agency Staff: "+agency_staff+"<br /> Number of Keys:"+number_of_keys);
			}
			*/
			
			window.location="/tech_keys.php?tech_id=<?php echo $tech_id; ?>&date=<?php echo $date; ?>";
		
		});	
				
	}
	
	jQuery(".btn_agency_staff").click(function(){
		
		var obj = jQuery(this);
		var kr_id = obj.parents("tr:first").find(".key_routes_id").val();
		var key_completed = obj.parents("tr:first").find(".key_completed").val();
		var key_action = obj.parents("tr:first").find(".key_action").val();
		var agency_staff = obj.parents("tr:first").find(".agency_staff").val();
		var number_of_keys = obj.parents("tr:first").find(".number_of_keys").val();
		
		markKeysCompleted(obj,kr_id,key_completed,agency_staff,number_of_keys);
		
	});
	
});
</script>
</body>
</html>