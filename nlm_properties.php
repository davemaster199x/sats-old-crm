<?php

$title = "No Longer Managed Properties";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class;

$country_id = $_SESSION['country_default'];

$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$agency_id = mysql_real_escape_string($_REQUEST['agency']);
$show_filter = mysql_real_escape_string($_REQUEST['show_filter']);

// sort
$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'p.`postcode`';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort=".urlencode($sort)."&order_by=".urlencode($order_by)."&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase)."&postcode_region_id=".$filterregion;

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

// dont filter write off if searched
if( $show_filter != 'write_off' ){
	$custom_filter = "AND ( p.`write_off` IS NULL OR p.`write_off` = 0 )";
}

$jparams = array(
	'nlm_display' => 1,
	'nlm_owing' => ( $show_filter=='money_owing' )?1:'',
	'write_off' => ( $show_filter=='write_off' )?1:'',
	'country_id' => $country_id,
	'phrase' => $phrase,
	'agency_id' => $agency_id,
	'custom_filter' => $custom_filter,
	'sort_list' => array(
		array(
			'order_by' => 'p.`nlm_timestamp`',
			'sort' => 'DESC'
		)
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'echo_query' => 0
);
$plist = $crm->getPropertyOnly($jparams);

$jparams = array(
	'nlm_display' => 1,
	'nlm_owing' => ( $show_filter=='money_owing' )?1:'',
	'write_off' => ( $show_filter=='write_off' )?1:'',
	'country_id' => $country_id,
	'phrase' => $phrase,
	'agency_id' => $agency_id,
	'custom_filter' => $custom_filter,
	'return_count' => 1
);
$ptotal = $crm->getPropertyOnly($jparams);


function this_mostRecentInvoice($property_id){
	
	return mysql_query("
		SELECT *
		FROM `jobs`
		WHERE `property_id` = {$property_id}
		AND `ts_completed` = 1
		ORDER BY `date` DESC
		LIMIT 1
	");
	
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
					
					<div class='fl-left'>
						<label>Phrase:</label>
						<input type=label name='phrase' value="<?php echo $_REQUEST['phrase']; ?>" class='addinput searchstyle vwjbdtp' style='width: 100px !important;'>
					</div>
					
					<div class="fl-left">
						<label style="margin-right: 9px;">Agency:</label>
						<select name="agency">
							<option value="">--- Select ---</option>	
							<?php
							//$sms_type_sql2 = $ws_sms->getSMStype();
							$jparams = array(
								'distinct' => 'a.`agency_id`',
								'nlm_display' => 1,								
								'country_id' => $country_id,
								'sort_list' => array(
									array(
										'order_by' => 'a.`agency_name`',
										'sort' => 'ASC'
									)
								)
							);
							$sms_type_sql2 = $crm->getPropertyOnly($jparams);
							while($sms_type2 = mysql_fetch_array($sms_type_sql2)){ ?>
								<option value="<?php echo $sms_type2['agency_id']; ?>" <?php echo ( $sms_type2['agency_id'] == $agency_id )?'selected="selected"':''; ?>><?php echo $sms_type2['agency_name']; ?></option>
							<?php 
							}
							?>
						</select>
					</div>
					
					
					<div class="fl-left">
						<label style="margin-right: 9px;">Show:</label>
						<select name="show_filter">
							<option value="">All</option>	
							<option value="money_owing">$ Owing</option>
							<option value="verified_paid">Verified Paid</option>
							<option value="write_off">Write Off</option>
						</select>
					</div>
					
					<div class='fl-left' style="float:left;">
						<input type='submit' class='submitbtnImg' id="btn_search" value='Search' />
					</div>

				<!-- duplicated filter here -->

					  
					  
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
				<th>Recent Invoice</th>
				<th>Date</th>
				<th>Amount</th>
				<th>Job Type</th>				
				<th>Agency</th>
				<th>Address</th>							
				<th>Date NLM</th>
				<th>NLM By</th>
				<th>Verify PAID</th>
				<th>$ Owing</th>
				<th>Write Off</th>
			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){	

						// get most recent completed job
						$j_sql = this_mostRecentInvoice($row['property_id']);
						$job = mysql_fetch_array($j_sql);
						$no_jobs_completed = 0;
						$row_color = '';
						
						if( $job['id']=='' ){
							$no_jobs_completed = 1;
							$row_color = "#eeeeee";
						}
						
						
						if( $row['nlm_owing']==1 ){
							$row_color = "#FFCCCB";
						}
						
						
						
				?>
						<tr class="body_tr jalign_left" style="background-color:<?php echo $row_color; ?>;">
							
							<td>
								<?php
								if( $no_jobs_completed==1 ){ ?>
									<em>No Jobs Completed</em>
								<?php	
								}else{ ?>
									<a href="view_job_details.php?id=<?php echo $job['id']; ?>"><?php echo $job['id']; ?></a>									
								<?php	
								}
								?>
								
							</td>
							<td><?php echo ($job['date']!='')?date('d/m/Y',strtotime($job['date'])):''; ?></td>							
							<td>
								<?php 
									echo ($job['job_price']>0)?'$'.$job['job_price']:""; 
								?>
							</td>
							<td><?php echo getJobTypeAbbrv($job['job_type']); ?></td>
							<td>
								<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
								<a href="<?php echo $ci_link; ?>">
									<?php echo $row['agency_name']; ?>
								</a>
							</td>
							<td>
								<a href="view_property_details.php?id=<?php echo $row['property_id']; ?>">
									<?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']} {$row['p_state']}"; ?>
								</a>
							</td>									
							<td><?php echo ($row['nlm_timestamp']!='')?date('d/m/Y',strtotime($row['nlm_timestamp'])):''; ?></td>
							<td>
							<?php
							if($row['nlm_by_sats_staff']!=''){
								//$nlm_by = "{$row['FirstName']} {$row['LastName']}";
								$nlm_by = 'SATS';
							}else if($row['nlm_by_agency']!=''){
								//$nlm_by = $row['agency_name'];
								$nlm_by = 'Agency';
							}
							echo $nlm_by;
							?>
							</td>
							<td>
								<input type="checkbox" class="nlm_prop_id" <?php echo ($row['nlm_display']==1)?'checked="checked"':''; ?> value="<?php echo $row['property_id']; ?>" />
							</td>
							<td>
								<input type="checkbox" class="nlm_owing" <?php echo ($row['nlm_owing']==1)?'checked="checked"':''; ?> value="<?php echo $row['property_id']; ?>" />
							</td>
							<td>
								<input type="checkbox" class="write_off" <?php echo ($row['write_off']==1)?'checked="checked"':''; ?> value="<?php echo $row['property_id']; ?>" />
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

		
		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	
	jQuery(".nlm_prop_id").change(function(){
		
		var prop_id = jQuery(this).val();
		var nlm_display = (jQuery(this).prop("checked")==true)?1:0;
		
		if( confirm("Are you sure you want to continue?") ){
			jQuery.ajax({
				type: "POST",
				url: "ajax_nlm_prop_toggle_display.php",
				data: { 
					prop_id: prop_id,
					nlm_display: nlm_display
				}
			}).done(function( ret ){			
				location.reload();
			});
		}		
		
	});
	
	
	jQuery(".nlm_owing").change(function(){
		
		var prop_id = jQuery(this).val();
		var nlm_owing = (jQuery(this).prop("checked")==true)?1:0;
		
		if( confirm("Are you sure you want to continue?") ){
			jQuery.ajax({
				type: "POST",
				url: "ajax_nlm_prop_toggle_owing.php",
				data: { 
					prop_id: prop_id,
					nlm_owing: nlm_owing
				}
			}).done(function( ret ){			
				location.reload();
			});
		}		
		
	});
	
	
	
	jQuery(".write_off").change(function(){
		
		var prop_id = jQuery(this).val();
		var write_off = (jQuery(this).prop("checked")==true)?1:0;
		
		if( confirm("Are you sure you want to continue?") ){
			jQuery.ajax({
				type: "POST",
				url: "ajax_nlm_prop_toggle_write_off.php",
				data: { 
					prop_id: prop_id,
					write_off: write_off
				}
			}).done(function( ret ){			
				location.reload();
			});
		}		
		
	});
	
	
});
</script>
</body>
</html>