<?php
$title = "BNE to call";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

//include('inc/precompleted_jobs_functions.php'); 

// Initiate job class
$jc = new Job_Class();

$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$job_type = mysql_real_escape_string($_REQUEST['job_type']);
$service = mysql_real_escape_string($_REQUEST['service']);
$state = mysql_real_escape_string($_REQUEST['state']);
$agency = mysql_real_escape_string($_REQUEST['agency']);

if($_POST['postcode_region_id']){
	$filterregion = implode(",",$_POST['postcode_region_id']);
	//print_r($filterregion);
}else if($_GET['postcode_region_id']){
	$filterregion = $_GET['postcode_region_id'];
	//echo $filterregion;
}
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
//$job_status = 'To Be Booked';

// sort
// urgent first, no dates 2nd sort
$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.no_dates_provided';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'DESC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort=".urlencode($sort)."&order_by=".urlencode($order_by)."&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase)."&postcode_region_id=".$filterregion;

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

// quick solution for custom query vacant jobs
$custom_query = " AND p.`bne_to_call` = 1 AND j.status NOT IN('Completed','Cancelled','Merged Certificates','Booked','Pre Completion') ";

$plist = $jc->getJobs($offset,$limit,$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'','','','',$agency,$filterregion,0,'','','','','','','','','',$custom_query);
$ptotal = mysql_num_rows($jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'','','','',$agency,$filterregion,0,'','','','','','','','','',$custom_query));




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
.tick_icon {
    width: 20px;
    position: relative;
    left: 7px;
    top: 4px;
    display: none;
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
		
		
		<form method="POST" name='example' id='example' style="margin-bottom: 0;">
			<input type='hidden' name='status' value='<?php echo $status ?>'>

			<table border=1 cellpadding=0 cellspacing=0 width="100%">
				<tr class="tbl-view-prop">
				<td>

				<div class="aviw_drop-h aviw_drop-vp" id="view-jobs">
				
				
				
					<div class="fl-left">
						<label>Agency:</label>
						<select id="agency" name="agency" style="width: 70px;">
						<option value="">Any</option>
						<?php
						$jt_sql = $jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'p.`agency_id`','','','',$agency,$filterregion,0,'','','','','','','','','',$custom_query);
						while($jt =  mysql_fetch_array($jt_sql)){ ?>
							<option value="<?php echo $jt['agency_id']; ?>" <?php echo ($jt['agency_id'] == $agency)?'selected="selected"':''; ?>><?php echo $jt['agency_name']; ?></option>
						<?php	
						}
						?>	
					 </select>
					</div>

				 
	
					<div class="fl-left">
						<label>Job Type:</label>
						<select name="job_type" style="width: 125px;">
							<option value="">Any</option>
							<?php
							$jt_sql = $jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'j.`job_type`','','','',$agency,$filterregion,0,'','','','','','','','','',$custom_query);
							while($jt =  mysql_fetch_array($jt_sql)){ ?>
								<option value="<?php echo $jt['job_type']; ?>" <?php echo ($jt['job_type'] == $job_type)?'selected="selected"':''; ?>><?php echo $jt['job_type']; ?></option>
							<?php	
							}
							?>	
						</select>
					</div>
				  
					 <?php
					$ajt_sql = $jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'j.`service`','','','',$agency,$filterregion,0,'','','','','','','','','',$custom_query);
				  ?>
					<div class="fl-left">
						<label>Service:</label>
						<select name="service" style="width: 125px;">
							<option value="">Any</option>
							<?php
							while($ajt=mysql_fetch_array($ajt_sql)){ ?>
								<option <?php echo ($ajt['id']==$service) ? 'selected="selected"':''; ?> value="<?php echo $ajt['id']; ?>" ><?php echo $ajt['type']; ?></option>
							<?php
							}
							?>
						</select>
					</div>
					
			
					<div class="fl-left">
						<label><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?>:</label>
						<select id="state" name="state" style="width: 70px;">
						<option value="">Any</option> 			
						<?php
						$jstate_sql = $jc->getJobs('','',$sort,$order_by,$job_type,$job_status,'','','','','p.`state`','','','',$agency,$filterregion,0,'','','','','','','','','',$custom_query);
						while($jstate =  mysql_fetch_array($jstate_sql)){ ?>
							<option value="<?php echo $jstate['state']; ?>" <?php echo ($jstate['state']==$state) ? 'selected="selected"':''; ?>><?php echo $jstate['state']; ?></option>
						<?php	
						} 
						?>
					 </select>
					</div>
					
					
					<div class="fl-left">
						<label><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?>:</label>
						<!--Region filter not working yet-->
						<input type="text" readonly="readonly" name='region_ms' id='region_ms' class='addinput searchstyle vwjbdtp' style='width: 100px !important;' />
						<style>
							#region_dp_div{
								width:auto; 
								border-radius: 5px;
								padding: 7px;
								position: absolute;
								top: 112px;
								background: #ffffff;
								border: 1px solid #cccccc;
								display: none;
								z-index: 99999;
							}
							.region_dp_header{
								background: #b4151b none repeat scroll 0 0;
								border-radius: 10px;
								color: #ffffff;
								padding: 6px;
								text-align: left;
							}
							#region_dp_div ul{
								list-style: outside none none;	
								padding: 0;
								margin: 0;
								text-align: left !important;
							}	
							.reg_db_main_reg{
								color: #b4151b;
								cursor: pointer;
								font-weight: bold;
								text-align: center;
							}
							#region_dp_div input{
								width:auto;
								float:none;
							}
							.region_wrapper{
								border-bottom: 1px solid;
								color: #b4151b;
							}
							</style>
							<div id="region_dp_div">
							<div class="region_dp_header">
								<ul>
								<?php
								
						
								
								// get state
								$jstate_sql = $jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'p.`state`','','','',$agency,$filterregion,0,'','','','','','','','','',$custom_query);
								while($jstate =  mysql_fetch_array($jstate_sql)){ 
								
								// get state regions
								$main_reg_pc = "";
								$temp_sql = mysql_query("
									SELECT * 	
									FROM  `regions`
									WHERE `region_state` = '{$jstate['state']}'
									AND `country_id` = {$_SESSION['country_default']}
									AND `status` = 1
								");
								while( $temp = mysql_fetch_array($temp_sql) ){
									$main_reg_pc .= ','.$jc->getSubRegionPostcodes($temp['regions_id']);
								}
								$main_region_postcodes = str_replace(',,',',',substr($main_reg_pc,1));
								$jcount_txt = "(".$jc->getTobeBookedSubRegionCount($_SESSION['country_default'],$main_region_postcodes,$job_type,$job_status,$custom_query).")";
								?>
									<li>
										<input type="checkbox" name="state_ms[]" class="state_ms" value="<?php echo $jstate['state']; ?>" /> <span><?php echo $jstate['state']; ?> <?php echo $jcount_txt ?></span>
										<input type="hidden" value="<?php echo $main_region_postcodes; ?>" />
									</li>
								<?php	
								} 
								?>
								</ul>
							</div>
							<div class="region_dp_body">								
							</div>
							</div>
							<script>
							jQuery(document).ready(function(){
								
								// clicking out the container script :)
								jQuery(document).mouseup(function (e)
								{
									var container = jQuery("#region_dp_div");

									if (!container.is(e.target) // if the target of the click isn't the container...
										&& container.has(e.target).length === 0) // ... nor a descendant of the container
									{
										container.hide();
									}
								});
								
								jQuery("#region_ms").click(function(){

								  jQuery("#region_dp_div").show();

								});
								
								/*
								jQuery(document).on("click",".reg_db_main_reg",function(){
									
									var sub_reg_vis = jQuery(this).parents("li:first").find(".reg_db_sub_reg").css("display");
									if(sub_reg_vis=='block'){
										jQuery(this).parents("li:first").find(".reg_db_sub_reg").hide();
									}else{
										jQuery(this).parents("li:first").find(".reg_db_sub_reg").show();
									}
								
								});
								*/
								
							});
							</script>
					</div>
					
					
					<div class='fl-left'>
						<label>Date:</label><input type=label name='date' value='<?php echo $_REQUEST['date']; ?>' class='addinput searchstyle datepicker vwjbdtp' style="width:85px!important;">		
					</div>
					
					
					<div class='fl-left'><label>Phrase:</label><input type=label name='phrase' value="<?php echo $_REQUEST['phrase']; ?>" class='addinput searchstyle vwjbdtp' style='width: 100px !important;'></div>
					
					<div class='fl-left' style="float:left;"><input type='submit' class='submitbtnImg' value='Search' />
					
    
					
					
					
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

				<th>Age</th>
				<th>Start Date</th>
				<th>End Date</th>
				
				
				<th><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?></th>

			
				<th>Job Type</th>
				
				<th>
					<div class="tbl-tp-name colorwhite bold">Service</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=j.service&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&job_type=<?php echo $job_type; ?>&service=<?php echo $service; ?>&date=<?php echo $date; ?>&phrase=<?php echo $phrase; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='j.service')?'active':''; ?>"></div>
					</a>
				</th>
				
				
				
				<th>Address</th>
		
				<th><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></th>
				<th>Agency</th>

				<th>Job #</th>
				<th>Notes</th>
				

			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
						// grey alternation color
						$row_color = ($i%2==0)?"style='background-color:#eeeeee;'":"";
						
						
					
				?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							<td>
								<?php
								// Age
								$date1=date_create(date('Y-m-d',strtotime($row['jcreated'])));
								$date2=date_create(date('Y-m-d'));
								$diff=date_diff($date1,$date2);
								$age = $diff->format("%r%a");
								echo (((int)$age)!=0)?$age:0;
								
								$age_tot += (int)$age;
								
								?>
							</td>
							<td><?php echo ($row['start_date']!="" && $row['start_date']!="0000-00-00" && $row['start_date']!="1970-01-01" )?date("d/m/Y",strtotime($row['start_date'])):(($row['no_dates_provided']==1)?'<div style="text-align: center;">N/A</div>':''); ?></td>
							<td><?php echo ($row['due_date']!="" && $row['due_date']!="0000-00-00" && $row['due_date']!="1970-01-01" )?date("d/m/Y",strtotime($row['due_date'])):(($row['no_dates_provided']==1)?'<div style="text-align: center;">N/A</div>':''); ?></td>
						
							
							
							
							<td>
							<?php 
							// region				
							$pr_sql = mysql_query("
								SELECT *
								FROM `postcode_regions` 
								WHERE `postcode_region_postcodes` LIKE '%{$row['p_postcode']}%'
								AND `deleted` = 0
								AND `country_id` = {$_SESSION['country_default']}
							");
							$pr = mysql_fetch_array($pr_sql);
							
							echo $pr['postcode_region_name'];
							?>
							</td>
							
							
							
							<td><?php echo getJobTypeAbbrv($row['job_type']); ?></td>
							
							<td><img src="images/serv_img/<?php echo getServiceIcons($row['jservice']); ?>" /></td>
						
							
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							
							<td><?php echo $row['p_state']; ?></td>
							<td><?php echo $row['agency_name']; ?></td>
						
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $row['jid']; ?></a></td>							
							
					        <td>
                                <input data-jobID="<?php echo $row['jid']; ?>" type="text" value="<?php echo $row['bne_to_call_notes']; ?>" class="addinput bne_notes" style="width:200px;margin:0;">
                                <img src="/images/check_icon2.png" class="tick_icon" style="display: none;">
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
    
    // BNE to call notes ajax update
    jQuery(".bne_notes").change(function(){
	
        var obj = jQuery(this);
        var bne_note = obj.val();
        var job_id = obj.attr('data-jobid');

        // show loader
        jQuery("#load-screen").show();
        // ajax call	
        jQuery.ajax({
            type: "POST",
            url: "ajax_update_job_bne_notes.php",
            data: { 
                job_id: job_id,
                bne_note: bne_note
            }
        }).done(function( ret ){

            // hide loader
            jQuery("#load-screen").hide();
            // show tick
            obj.parents("tr:first").find(".tick_icon").fadeIn();
            // fade
            setTimeout(function(){ 
                obj.parents("tr:first").find(".tick_icon").fadeOut();
            }, 10000);

        });
			
    });
   
	
	// region multi select - region check all sub
	jQuery(document).on("click",".region_check_all",function(){
		var chk_state = jQuery(this).prop("checked");
		if(chk_state==true){
			jQuery(this).parents("li:first").find(".reg_db_sub_reg input").prop("checked",true);			
		}else{
			jQuery(this).parents("li:first").find(".reg_db_sub_reg input").prop("checked",false);
		}
		
	});
	
	// region multi select script
	jQuery(".state_ms").click(function(){
		
		var state = jQuery(this).val();
		var state_chk = jQuery(this).prop("checked");
		
		//console.log(state_sel);
		//alert('test trigger');
		
		
		if(state_chk==true){
			
			jQuery("#load-screen").show();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_getMainRegionsViaState.php",
				data: { 
					state: state,
					custom_query_flag: 1
				}
			}).done(function( ret ){	
				jQuery("#load-screen").hide();
				jQuery(".region_dp_body").append(ret);
			});
			
		}else{
			jQuery("."+state+"_regions").remove();
		}

			
				
	});
	
	
	// region multiselect - get sub region
	jQuery(document).on("click",".reg_db_main_reg",function(){
		
		var obj = jQuery(this);
		var region = obj.parents("li:first").find(".regions_id").val();
		var sub_reg_space = obj.parents("li:first").find(".reg_db_sub_reg").html();
		
		
		
		if(sub_reg_space==""){
			
			jQuery("#load-screen").show();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_getSubRegionsViaRegion.php",
				data: { 
					region: region,
					custom_query_flag: 1
				}
			}).done(function( ret ){	
				jQuery("#load-screen").hide();
				obj.parents("li:first").find(".reg_db_sub_reg").html(ret);
			});
			
		}else{
			obj.parents("li:first").find(".reg_db_sub_reg").html("");
		}
	
				
	});
	
	
	
});
</script>
</body>
</html>