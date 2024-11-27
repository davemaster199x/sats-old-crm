<?php

$title = "Tech Keys";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$page_url = $_SERVER['PHP_SELF'];

$tech_id = mysql_real_escape_string($_REQUEST['tech_id']);
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$tr_id = mysql_real_escape_string($_REQUEST['tr_id']);


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
	SELECT DISTINCT a.`agency_id`, a.`agency_name`, a.`agency_specific_notes`
	FROM `tech_run_keys` AS kr
	LEFT JOIN `agency` AS a ON kr.`agency_id` = a.`agency_id`
	WHERE kr.`date` = '{$date}'
	AND ( 
		kr.`deleted` = 0 
		OR kr.`deleted` IS NULL 
	)
	AND a.`country_id` =  {$_SESSION['country_default']}
	AND kr.`assigned_tech` ={$tech_id}
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
	OR(
		j.`trk_kar` = 1
		AND j.`trk_tech` ={$tech_id}
		AND j.`trk_date` = '{$date}'	
		AND j.`rebooked_no_show` = 1
		AND a.`agency_id` = {$agency_id}
	)
	OR(
		j.`trk_kar` = 1
		AND j.`trk_tech` ={$tech_id}
		AND j.`trk_date` = '{$date}'	
		AND j.`rebooked_show_on_keys` = 1
		AND a.`agency_id` = {$agency_id}
	)
	";
	return $plist = mysql_query($p_sql_str);
}


function getKeyRoutesPerAgency($tech_id,$date,$country_id,$agency_id){
	$sql = "
		SELECT 
			kr.`tech_run_keys_id`, kr.`action`, kr.`number_of_keys`, kr.`agency_staff`, kr.`completed`, kr.`completed_date`, kr.`sort_order`,
			a.`agency_id`, a.`agency_name`, a.`address_1`, a.`address_2`, a.`address_3`, a.`state`, a.`postcode`, a.`phone`, a.`agency_hours`, a.`lat`, a.`lng`
		FROM `tech_run_keys` AS kr
		LEFT JOIN `agency` AS a ON kr.`agency_id` = a.`agency_id`
		WHERE kr.`date` = '{$date}'
		AND ( 
			kr.`deleted` = 0 
			OR kr.`deleted` IS NULL 
		)
		AND a.`country_id` = {$country_id}
		AND kr.`assigned_tech` ={$tech_id}
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
.no_keys_div{
	display: none;
}
</style>


<?php
	  if($_SESSION['USER_DETAILS']['ClassID']==6){ ?>  
		<div style="clear:both;"></div>
	  <?php
	  }  
	  ?>


<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
		 <?php
		  if($_SESSION['USER_DETAILS']['ClassID']==6){ 
		  
		 $tech_id = $_SESSION['USER_DETAILS']['StaffID'];
		  
		  $day = date("d");
		  $month = date("m");
		  $year = date("y");
		  
		  include('inc/tech_breadcrumb.php');
		  
		  }else{ ?>
		  
			<div class="sats-breadcrumb">
			  <ul>
				<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
				<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $page_url; ?>?tech_id=<?php echo $tech_id; ?>&date=<?php echo $date; ?>&tr_id=<?php echo $tr_id; ?>"><strong><?php echo $title; ?></strong></a></li>
			  </ul>
			</div>
		  
		  <?php
		  }  
		  ?>
	
		
	
		
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
				
				
			if( mysql_num_rows($a_sql)>0 ){
					$sig_ctr = 0;
					while($a_row = mysql_fetch_array($a_sql)){ 
						
						
					?>
						<div class="key_listing_div">
						<h2 class="heading" style="float: left;"><?php echo str_replace('*do not use*','',$a_row['agency_name']); ?> <span class="list_count"></span></h2>
						<div style="float: left; margin: 12px 0 0 18px;">
							<input type="text" class="addinput" readonly="readonly" style="display: inline; float: none; width: 400px;" value="<?php echo $a_row['agency_specific_notes']; ?>" />
						</div>
						
						<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
						<tr class="toprow jalign_left">
							<th class="prop_address">Address</th>
							<th class="prop_key_num">Key Number</th>
							<th class="prop_key_num">Approved By</th>
							<th class="prop_key_num">Agency Staff</th>
							<th class="prop_key_num">Number of Keys</th>
						</tr>
						
						<?php
		
						$row_sql = getPropertyKeysPerAgency($tech_id,$date,$_SESSION['country_default'],$a_row['agency_id']);
						$num_of_keys = mysql_num_rows($row_sql);
						//$nok_txt = ($num_of_keys>1)?"{$num_of_keys} KEYS":"{$num_of_keys} KEY";
						
						$i = 0;
						$job_id_arr = [];
						$row_count = 0;
						$total_num_keys = 0;
						
						while( $row = mysql_fetch_array($row_sql) ){
							
							
							
							$job_id_arr[] = $row['jid'];
						
							//$row_color = ($i%2==0)?"":"style='background-color:#eeeeee;'";
						
							?>
							
							<tr class="body_tr jalign_left prop_row" <?php echo $row_color; ?>>							
								<td class="prop_address">
									<?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?>
								</td>
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
						$row_count++;
						$total_num_keys++;
						}
						?>
						
	
						
						<?php
						// KEYS
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
									<input type="hidden" class="tech_run_keys_id" value="<?php echo $key['tech_run_keys_id']; ?>" />
									<input type="hidden" class="key_completed" value="<?php echo $key['completed']; ?>" />
									<input type="hidden" class="key_action" value="<?php echo $key['action']; ?>" />
									<a href="javascript:void(0);" class="link_keys"><?php echo $kr_action; ?></a>
									
									<div class="agency_staff_div" style="display:none;">
										Agency Staff: <input type="text" class="agency_staff" style="margin-bottom: 3px;" value="<?php echo $kr_arr[$j]['agency_staff']; ?>" /><br />
										Number of Keys: <input type="text" class="number_of_keys" style="margin-bottom: 3px;" value="<?php echo $kr_arr[$j]['number_of_keys']; ?>" /><br />
										<?php
										if( $key['action']=='Drop Off' ){ ?>
											<h1 style="text-align: center; color: red; font-style: italic;">Please sign on the line below</h1> 
											<div id="signature<?php echo $sig_ctr; ?>" class="signature" style="border: 1px solid red; margin-bottom: 5px;"></div>											
											<button type="button" class="blue-btn submitbtnImg btn_clear_signature" style="margin-left: 10px;">Clear Signature</button>
										<?php	
										$sig_ctr++;
										}
										?>
										<button type="button" class="blue-btn submitbtnImg btn_submit_key_sig" style="margin-left: 10px;">Submit</button>										
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
						
						<input type="hidden" class="row_count" value="<?php echo $row_count; ?>" />
						<input type="hidden" class="total_num_keys" value="<?php echo $total_num_keys; ?>" />
						<input type="hidden" class="agency_id" value="<?php echo $a_row['agency_id']; ?>" />
						
						</div>
						
						
				<?php
				
				} ?>
			<div class="no_keys_div">There are No key jobs booked on today's schedule</div>	
			<?php	
			}else{
				echo '<div style="padding: 14px; text-align: left;">No Key jobs booked</div>';
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
<script src="https://code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>
<br class="clearfloat" />
<script>
var sigdiv;

// clear signature
function clearSignature(sigdiv){
	sigdiv.jSignature("reset"); // clears the canvas and rerenders the decor on it.
}
// save signature
function saveSignature(sigdiv){
	// Getting signature as SVG and rendering the SVG within the browser. 
	// (!!! inline SVG rendering from IMG element does not work in all browsers !!!)
	// this export plugin returns an array of [mimetype, base64-encoded string of SVG of the signature strokes]
	var datapair = sigdiv.jSignature("getData", "svgbase64") 
	var i = new Image();
	var svg_txt = "data:" + datapair[0] + "," + datapair[1];
	i.src = svg_txt;
	return svg_txt;
}


function rowCount(){

	

	
	
	//var tot_row_count = 0;
	jQuery(".row_count").each(function(){

		var row_count = parseInt(jQuery(this).val());
		var agency_id = parseInt(jQuery(this).parents("div.key_listing_div:first").find(".agency_id").val());

		//console.log("fn_agency_main: "+fn_agency_main);
		//console.log("fn_agency_sub: "+fn_agency_sub);
		//console.log("agency_id: "+agency_id);
		
		var key_txt = (row_count>1)?'KEYS':'KEY';
		var raw_count_txt = '('+row_count+' '+key_txt+')';
		
		jQuery(this).parents("div.key_listing_div:first").find(".list_count").html(raw_count_txt);


		<?php
		if ( CURRENT_COUNTRY == 1 ){ // AU only

			// FN script
			$fn_agency_arr = $crm->get_fn_agencies();			
			$fn_agency_sub =  $fn_agency_arr['fn_agency_sub'];			
			$fn_agency_sub_to_js = json_encode($fn_agency_sub);
		?>			
			var fn_agency_sub = <?php echo $fn_agency_sub_to_js; ?>;

			if( row_count == 0 && fn_agency_sub.includes(agency_id) == false ){
				jQuery(this).parents("div.key_listing_div:first").hide();		
			}

		<?php
		}else{ ?>
			if( row_count == 0  ){
				jQuery(this).parents("div.key_listing_div:first").hide();		
			}
		<?php
		}	
		?>	
		
		
		

		//tot_row_count++;

	});
	
	var total_num_keys = parseInt(jQuery(".total_num_keys").val());
	if( total_num_keys == 0 ){
		jQuery('.no_keys_div').show();
	}
	

}



jQuery(document).ready(function(){
	
	// row count
	rowCount();
	
	// invoke digital signature
	sigdiv = jQuery(".signature");
	sigdiv.jSignature({'width': 800, 'height': 300});	
	//console.log(sigdiv);
	
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
			window.location="<?php echo $page_url; ?>?tech_id=<?php echo $tech_id; ?>&date=<?php echo $date; ?>";
		});
		
		//console.log('on blur');
		//jQuery(this).parents("tr:first").find(".key_num_link").show();
		//jQuery(this).parents("tr:first").find(".key_num_hid").hide();
		
		
	});
	
	// keys link
	jQuery(".link_keys").click(function(){
		
		var obj = jQuery(this);
		var kr_id = obj.parents("tr:first").find(".tech_run_keys_id").val();
		var key_completed = obj.parents("tr:first").find(".key_completed").val();
		var key_action = obj.parents("tr:first").find(".key_action").val();
		
		if(key_completed==0 ){
			obj.parents("tr:first").find(".agency_staff_span").hide();
			obj.parents("tr:first").find(".agency_staff_div").show();
		}else{
			markKeysCompleted(obj,kr_id,key_completed,'','','');
		}
		
	});
	
	function markKeysCompleted(obj,kr_id,key_completed,agency_staff,number_of_keys,sigdiv){
		
		if(sigdiv==''){
			var signature_svg = '';
		}else{
			var signature_svg = saveSignature(sigdiv);
		}
		
		//console.log(signature_svg);
	
		jQuery.ajax({
			type: "POST",
			url: "ajax_mark_tech_run_key_as_completed.php",
			data: { 
				kr_id: kr_id,
				key_completed: key_completed,
				agency_staff: agency_staff,
				number_of_keys: number_of_keys,
				signature_svg: signature_svg
			}
		}).done(function( ret ) {			
			//window.location="<?php echo $page_url; ?>?tech_id=<?php echo $tech_id; ?>&date=<?php echo $date; ?>";
			window.location="tech_day_schedule.php?tr_id=<?php echo $tr_id; ?>";				
		});	
				
	}
	
	jQuery(".btn_submit_key_sig").click(function(){
		
		var obj = jQuery(this);
		var kr_id = obj.parents("tr:first").find(".tech_run_keys_id").val();
		var key_completed = obj.parents("tr:first").find(".key_completed").val();
		var key_action = obj.parents("tr:first").find(".key_action").val();
		var agency_staff = obj.parents("tr:first").find(".agency_staff").val();
		var number_of_keys = obj.parents("tr:first").find(".number_of_keys").val();
		
		if( confirm("Are you sure you want to proceed?") ){
			
			if(key_action=='Drop Off'){
				var sigdiv = jQuery(this).parents(".agency_staff_div:first").find(".signature");
			}else{
				var sigdiv = '';
			}			
			markKeysCompleted(obj,kr_id,key_completed,agency_staff,number_of_keys,sigdiv);
			
			
		}		
		
	});
	
	
	jQuery(".btn_clear_signature").click(function(){
		
		var sigdiv = jQuery(this).parents(".agency_staff_div:first").find(".signature");
		clearSignature(sigdiv);
		
	});
	
	
});
</script>
</body>
</html>