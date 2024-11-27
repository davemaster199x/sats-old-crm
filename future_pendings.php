<?
$month_text = ( $_REQUEST['from'] != "" )?date("F",strtotime("{$_REQUEST['from']}")):date("F",strtotime("+1 month"));

$title = "{$month_text} Service Due";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

include('inc/future_pendings_functions.php');

// Initiate job class
$jc = new Job_Class();

$from = ( $_REQUEST['from'] != "" )?$_REQUEST['from']:date("Y-m-01",strtotime("+1 month"));
$to = ( $_REQUEST['to'] != "" )?$_REQUEST['to']:date("Y-m-t",strtotime("+1 month"));

// agenyc id	
$agency = mysql_real_escape_string($_REQUEST['agency']);	
$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$state_srch	 = mysql_real_escape_string($_REQUEST['state_srch']);

if($_POST['postcode_region_id']){
	$region2 = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$region2 = $_GET['postcode_region_id'];
	//echo $region2;
}
					
// pagination script
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;
$this_page = $_SERVER['PHP_SELF'];

$params = "&agency={$agency}&phrase={$phrase}&state_srch={$state_srch}&search_flag=".$_REQUEST['search_flag']."&postcode_region_id=".$region2."";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$ptotal = 0;
if($_REQUEST['search_flag']==1){
	
	$jparams = array(
		'paginate' => array(
			'offset' => $offset,
			'limit' => $limit
		),
		'region_postcodes' => $region2,
		'agency' => $agency,
		'phrase' => $phrase,
		'state' => $state_srch,
		'distinct' => '',
		'from' => $from,
		'to' => $to
	);
	$u_sql = getFuturePendings_v2($jparams);
	$jparams = array(
		'region_postcodes' => $region2,
		'agency' => $agency,
		'phrase' => $phrase,
		'state' => $state_srch,
		'distinct' => '',
		'from' => $from,
		'to' => $to
	);
	$ptotal = mysql_num_rows(getFuturePendings_v2($jparams));
	
}					




?> 

<div id="mainContent">
  
  <div class="sats-middle-cont">
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?> " href="/future_pendings.php"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
	
	<?php
	if($_GET['success']==1){
		echo '<div class="success">Import Successful</div>';
	}
	?>

	<!--
	<div class="aviw_drop-h" style="border: 1px solid #ccc;">		 
		<div class="fl-left">
			<a href="export_unserviced.php"><button type="button" class="submitbtnImg">Export</button></a>
		</div>	
	</div>
	-->
	
	
   
   
   <div class="aviw_drop-h aviw_drop-vp" style="border: 1px solid #cccccc;">
   
		
			
			<form method="post">
			
				<div class="fl-left">
					<style>
						button.ui-multiselect {
							background: white none repeat scroll 0 0;
							box-shadow: 0 0 2px #404041 inset;
							color: #000000;
							font-family: arial,sans-serif;
							font-size: 13.3333px;
							font-style: normal;
							font-weight: 400;
							line-height: 22px;
							padding: 5px;	
						}
						.ui-multiselect-checkboxes span{
							font-family: arial,sans-serif;
							font-size: 13.3333px;
							font-style: normal;
							font-weight: 400;
						}	
					</style>
	
					<link rel="stylesheet" type="text/css" href="/jquery_multiselect/css/jquery.multiselect.css" />
					<script type="text/javascript" src="/jquery_multiselect/js/jquery.multiselect.js"></script>
					<label>Region:</label>
						<input type="text" readonly="readonly" name='region_ms' id='region_ms' class='addinput searchstyle vwjbdtp' style='width: 100px !important;' />
						<input type="hidden" name="future_pending_post_codes" value="" />
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
								if($_REQUEST['search_flag']==1){
									// get future pendings list									
									$jparams = array(
										'agency' => $agency,
										'phrase' => $phrase,
										'state' => $state_srch,
										'distinct' => 'state',
										'from' => $from,
										'to' => $to
									);
									$jstate_sql = getFuturePendings_v2($jparams);
								}
								
								// loop state
								while($jstate =  mysql_fetch_array($jstate_sql)){ 
								
								/*
								// get regions
								$main_reg_pc = "";
								$temp_sql = mysql_query("
									SELECT * 	
									FROM  `regions`
									WHERE `region_state` = '{$jstate['state']}'
									AND `country_id` = {$_SESSION['country_default']}
									AND `status` = 1
								");
								// loop regions
								while( $temp = mysql_fetch_array($temp_sql) ){
									$main_reg_pc .= ','.$jc->getSubRegionPostcodes($temp['regions_id']);
								}
								$main_region_postcodes = str_replace(',,',',',substr($main_reg_pc,1));	
								*/
								
								$jparams = array(
									'agency' => $agency,
									'phrase' => $phrase,
									'state' => $jstate['state'],
									'from' => $from,
									'to' => $to,
									'getCount' => 1
								);
								$state_count_sql = getFuturePendings_v2($jparams);
								
								$state_count = mysql_fetch_array($state_count_sql);
								$jcount_txt = "(".$state_count['jcount'].")";
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
								
								<?php
								if($_REQUEST['search_flag']==1){ ?>
									jQuery("#region_ms").click(function(){

									  jQuery("#region_dp_div").show();

									});
								<?php	
								}
								?>
								
								
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
			
				<div class="fl-left">				
						<label>Agency:</label>
						<select name="agency" id="agency">
							<option id="Any">Any</option>
							<?php
									if($_REQUEST['search_flag']==1){
										$fpa_sql = getFuturePendings('','',$region2,$agency,$phrase,$state_srch,'agency',$from,$to);
									}
									
									$odd = 0;

									while($curr_agency = mysql_fetch_array($fpa_sql)) {

										echo "<option value='" . $curr_agency['agency_id'] . "' " . ($agency == $curr_agency['agency_id'] ? "selected=\"selected\"" : "s") . ">";
										echo $curr_agency['agency_name'];
										echo "</option>";

									
									}

									?>
						  </select>				
				  </div>
				
				
			  <div class="fl-left">
				<label>Phrase:</label>
				<input class="addinput searchstyle" type="text" name="phrase" size=10 value="<?=$phrase;?>">
			  </div>
			  
			  <?php
			  if($_SESSION['country_default']==1){ ?>
				<div class="fl-left">
			   <?php
			   if($_REQUEST['search_flag']==1){
				   $curr_state_sql = getFuturePendings('','',$region2,$agency,$phrase,$state_srch,'state',$from,$to);
			   }
			   
			   ?>
				<label>State:</label>
				<select style="width: 70px;" name="state_srch" id="state">
					<option value="">----</option> 
					<?php	
					while($curr_state = mysql_fetch_array($curr_state_sql)){ ?>
						<option value="<?php echo $curr_state['state']; ?>" <?php echo ($curr_state['state']==$state)?'selected="selected"':''; ?>><?php echo $curr_state['state']; ?></option>
					<?php	
					}
					?>
				 </select>
				</div>
			  <?php	  
			  }
			  ?>
			   
			  
			  
			  <div class="fl-left">
				<input type="hidden" name="search_flag" value="1" />
				<input type="submit" name="btn_search" value="Search" class="submitbtnImg">
			  </div>
			  
			  <div class="fl-left">
			  <a href="/export_future_pendings.php?agency=<?php echo $agency; ?>&phrase=<?php echo urlencode($phrase); ?>" class="submitbtnImg export">Export</a>
			  </div>
		  
		  </form>
		  
		  
		  
		</div>
		
		
	<?php 
	$next_month = date("Y-m-1",strtotime("{$_REQUEST['from']} +1 month"));
	?>
	
		<div class="aviw_drop-h qlnk">

			<div class="float-left content-black"><a href="/future_pendings.php?from=<?php echo date("Y-m-01",strtotime("{$from} -1 month")); ?>&to=<?php echo date("Y-m-t",strtotime("{$from} -1 month")); ?>&search_flag=1"><div class="arw-lft2">&nbsp;</div></a> Previous Month</div>
			
			Quick Links
			<?php
			for($i=0;$i<=3;$i++){ 
				$m = date("F",strtotime("{$from} +{$i} month")); 
				$from_link = date("Y-m-01",strtotime("{$from} +{$i} month"));
				$to_link = date("Y-m-t",strtotime("{$from} +{$i} month"));
			?>
				| <a href="/future_pendings.php?from=<?php echo $from_link; ?>&to=<?php echo $to_link; ?>&search_flag=1" <?php echo ($from_link==$from)?'style="font-weight: bold;"':''; ?>><?php echo $m; ?></a>
			<?php
			}
			?>
					
			<div class="float-right pg-tp-rg content-black">Next Month <a href="/future_pendings.php?from=<?php echo date("Y-m-01",strtotime("{$from} +1 month")); ?>&to=<?php echo date("Y-m-t",strtotime("{$from} +1 month")); ?>&search_flag=1"><div class="arw-rgt2">&nbsp;</div></a></div>		
		
		</div>
		
		
   
	<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px; text-align: left;" id="tbl_sms_msg">
		<tr class="toprow jalign_left">
			<th>Property ID</th>
			<th>Address</th>
			<th>Agency</th>
			<th>Next Service Due</th>
		</tr>
			<?php									
							
			
			if(mysql_num_rows($u_sql)>0){
				$i = 0;
				while($u = mysql_fetch_array($u_sql)){
					$bgcolor = ($i%2==0)?'':'#eeeeee';
			?>
					<tr class="body_tr jalign_left" style="background-color:<?php echo $bgcolor; ?>">
						<td>
							<span class="txt_lbl">
								<?php echo $u['property_id']; ?>
							</span>
						</td>
						<td>
							<span class="txt_lbl">
								<a href="/view_property_details.php?id=<?php echo $u['property_id']; ?>">
									<?php echo "{$u['p_address1']} {$u['p_address2']}, {$u['p_address3']} {$u['p_state']} {$u['p_postcode']}"; ?>
								</a>
							</span>
						</td>
						<td>
							<span class="txt_lbl">
								<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$u['agency_id']}"); ?>
								<a href="<?php echo $ci_link; ?>">
									<?php echo $u['agency_name']; ?>
								</a>
							</span>
						</td>
						<td style="border-right: 1px solid #ccc;">
							<span class="txt_lbl">
								<?php echo date("F Y",strtotime($u['jdate'].' +1 year')); ?>
							</span>
						</td>
						
						</td>
					<tr>
			<?php
				$i++;
				}
			}else{ ?>
				<td colspan="100%" align="left">Empty</td>
			<?php
			}
			?>
			
	</table>
	
	<?php
	
	if($_REQUEST['search_flag']==1){
		
		// Initiate pagination class
		$jp = new jPagination();
		
		$per_page = $limit;
		$page = ($_GET['page']!="")?$_GET['page']:1;
		$offset = ($_GET['offset']!="")?$_GET['offset']:0;	
		
		echo $jp->display($page,$ptotal,$per_page,$offset,$params);
		
	}

	
	
	?>

  </div>
  
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
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
		
		
		
		if(state_chk==true){
			
			jQuery("#load-screen").show();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_futurePendingsGetMainRegion.php",
				data: { 
					state: state,
					from: '<?php echo $from; ?>',
					to: '<?php echo $to; ?>'
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
		var regions_state = obj.parents("li:first").find(".regions_state").val();
		var check_all = obj.parents("li.main_region_li").find(".check_all_sub_region").prop("checked");
		
		
		if(sub_reg_space==""){
			
			jQuery("#load-screen").show();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_futurePendingsGetSubRegion.php",
				data: { 
					region: region,
					state: regions_state,
					from: '<?php echo $from; ?>',
					to: '<?php echo $to; ?>'
				}
			}).done(function( ret ){	
				jQuery("#load-screen").hide();
				obj.parents("li:first").find(".reg_db_sub_reg").html(ret);
				if( check_all == true ){
					obj.parents("li.main_region_li").find(".postcode_region_id").prop("checked",true);
				}
			});
			
		}else{
			obj.parents("li:first").find(".reg_db_sub_reg").html("");
		}

			
				
	});
	
});
</script>
</body>
</html>
