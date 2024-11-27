<?

$title = "Active Job Properties";

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');

$crm = new Sats_Crm_Class();
$jc = new Job_Class();

$search = "";
$agency = "";
$search = trim($_REQUEST['searchsuburb']);
$agency = $_REQUEST['agency'];


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
$limit = 50;
$this_page = $_SERVER['PHP_SELF'];

$params = "&sort={$sort}&order_by={$order_by}&agency={$agency}&searchsuburb={$search}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$propertylist = getPropertyListWithActiveService($agency, $search, $offset , $limit, 0, $sort, $order_by,$filterregion);
$ptotal = mysql_num_rows(getPropertyListWithActiveService($agency, $search, '', '', 0, $sort, $order_by,$filterregion));
/*
$propertylist = getPropertyList2($offset , $limit, $sort, $order_by,$agency, $search);
$ptotal = mysql_num_rows(getPropertyList2('', '', $sort, $order_by,$agency, $search));
*/




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
        <li class="other first"><a title="<?php echo $title; ?>" href="/active_properties.php"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
   <?php
   if($_GET['perm_del']==1){ ?>
		<div class="success">Property Delete Successful</div>
   <?php
   }
   ?>
  
  
    <form method="POST" action="<?=URL;?>active_properties.php" class="searchstyle">
      <table cellpadding=0 cellspacing=0 >
        <tr class="tbl-view-prop">
          <td>
          
            <div class="aviw_drop-h aviw_drop-vp">
			
			
			<div class="fl-left">
				<label>Agency:</label>
				<select name="agency" id="agency">
					<option id="Any">Any</option>
					<?php
							
							 $query ="SELECT agency.agency_id, agency.`agency_name` FROM agency, property
										 WHERE property.`agency_id` = agency.`agency_id` AND agency.`status` != 'target'
										 AND property.`deleted` = 0
										 AND agency.`country_id` = {$_SESSION['country_default']}
										 GROUP BY agency.`agency_id`
										 ORDER BY agency_name ASC";
							
							#$result = mysql_query("SELECT agency_id, agency_name, address_3 FROM agency", $connection);
							$agencies = mysqlMultiRows($query);
					

							$odd = 0;

							// (3) While there are still rows in the result set,

							// fetch the current row into the array $row

							foreach($agencies as $curr_agency) {

								// (4) Print out each element in $row, that is,

								// print the values of the attributes

								echo "<option value='" . $curr_agency['agency_id'] . "' " . ($agency == $curr_agency['agency_id'] ? "selected=\"selected\"" : "s") . ">";

								echo $curr_agency['agency_name'];

								echo "</option>\n";

								// Print a carriage return to neaten the output

								echo "\n";

							}

							// (5) Close the database connection
							?>
				  </select>
			  </div>
			  
			  
			 <div class="fl-left">
				<label><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?>:</label>
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
						$jparams = array(
							'country_id' => $_SESSION['country_default'],
							'distinct' => 'p.`state`',
							'sort_list' => array(
								array(
									'order_by' => 'p.`state`',
									'sort' => 'ASC'
								)
							),
							'group_by' => 'ps.`property_id`',
							'custom_filter' => " AND p.`state` != '' "
						);
						$jstate_sql = $crm->getPropertyServicedToSats($jparams);
						while($jstate =  mysql_fetch_array($jstate_sql)){ 
						
							// get state regions
							$main_reg_pc = [];
							$temp_sql = mysql_query("
								SELECT * 	
								FROM  `regions`
								WHERE `region_state` = '{$jstate['state']}'
								AND `country_id` = {$_SESSION['country_default']}
								AND `status` = 1
							");
							while( $temp = mysql_fetch_array($temp_sql) ){
								$main_reg_pc[] = str_replace(',,',',',$jc->getSubRegionPostcodes($temp['regions_id']));	
							}
							
							$pc_merge_arr2 = array_filter($main_reg_pc);
							$pc_merge = implode(",",$pc_merge_arr2);
							$main_region_postcodes = $pc_merge;
							
							if( $main_region_postcodes!='' ){
							$jparams = array(
								'region_postcodes' => $main_region_postcodes,
								'country_id' => $_SESSION['country_default'],
								'distinct' => 'ps.`property_id`',
								'group_by' => 'ps.`property_id`'
							);
							$region_count_sql = $crm->getPropertyServicedToSats($jparams);
							$region_count = mysql_num_rows($region_count_sql);
							$jcount_txt = "(".$region_count.")";

						if($region_count>0){
						?>
							<li>
								<input type="checkbox" name="state_ms[]" class="state_ms" value="<?php echo $jstate['state']; ?>" /> <span><?php echo $jstate['state']; ?> <?php echo $jcount_txt ?></span>
								<input type="hidden" value="<?php echo $main_region_postcodes; ?>" />
							</li>
						<?php	
								}
							}
						
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
			
			
  <div class="fl-left">
    <label>Phrase:</label>
    <input class="addinput searchstyle" type=text name="searchsuburb" size=10 value="<?=$search;?>">
  </div>
  
  <div class="fl-left">
    <input type="submit" name="btn_search" value="Search" class="submitbtnImg">
  </div>
  <div class="fl-left">
  <a href="<?php echo $export_link ;?>" class="submitbtnImg export">Export</a>
  </div>
</div>





<p>This report shows all jobs that are active for an Agency that are SATS not DIY etc and are on a YM program</p>



            
            <table border=0 cellspacing=1 cellpadding=5 width="100%" class="table-left chr-tbl">
              <?php
					
					
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
					
					?>
              <tr bgcolor="#b4151b">
                <td width="350"><b><a href="/view_properties.php?sort=p.address_2&order_by=<?php echo ($_REQUEST['sort']=='p.address_2')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Address</div> <?php echo ($_REQUEST['sort']=='p.address_2')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></b></td>
                <td width="150"><b><a href="/view_properties.php?sort=p.address_3&order_by=<?php echo ($_REQUEST['sort']=='p.address_3')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Suburb</div> <?php echo ($_REQUEST['sort']=='p.address_3')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></b></td>
                <td width="135"><b><a href="/view_properties.php?sort=p.state&order_by=<?php echo ($_REQUEST['sort']=='p.state')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold"><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></div> <?php echo ($_REQUEST['sort']=='p.state')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></b></td>
         
				<?php 
				
				// get dynamic services
				$dserv_sql = mysql_query("
					SELECT *
					FROM `alarm_job_type`
					WHERE `active` = 1
				");
				
				while($dserv = mysql_fetch_array($dserv_sql)){ 
				
				switch($dserv['id']){
					case 2:
						$serv_color = 'b4151b';
						$serv_icon = 'smoke_white.png';
					break;
					case 5:
						$serv_color = 'f15a22';
						$serv_icon = 'safety_white.png';
					break;
					case 6:
						$serv_color = '00ae4d';
						$serv_icon = 'corded_white.png';
					break;
					case 7:
						$serv_color = '00aeef';
						$serv_icon = 'pool_white.png';
					break;
					case 8:
						$serv_color = '9b30ff';
						$serv_icon = 'sa_ss_white.png';
					break;
					case 9:
						$serv_color = '9b30ff';
						$serv_icon = 'sa_cw_ss_white.png';
					break;
				}
				
				?>
				
					<td width="100"><img src="images/serv_img/<?php echo $serv_icon; ?>" /></td>
				
				<?php
				}
				
				// default active
				$active = ($_REQUEST['sort']=="")?'arrow-top-active':''; 
				?>
                <td width="300"><b><a href="/view_properties.php?sort=a.agency_name&order_by=<?php echo ($_REQUEST['sort']=='a.agency_name')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Agency</div> <?php echo ($_REQUEST['sort']=='a.agency_name')?$sort_arrow:'<div class="arw-std-up '.$active.'"></div>'; ?></a></b></td>
              </tr>
              <?php
				
					$odd = 0;

					while($row=mysql_fetch_array($propertylist))
					{

						$odd++;
															
						if (is_odd($odd)) {

							echo "<tr bgcolor=#FFFFFF>";

						} else {

							echo "<tr bgcolor=#eeeeee>";

						}

						echo "\n";

						// (4) Print out each element in $row, that is,

						// print the values of the attributes

						echo "<td>";

						echo "<a href='" . URL . "view_property_details.php?id=" . $row['property_id'] . "'>{$row['address_1']} {$row['address_2']}</a>";

						echo "</td>";

						echo "<td>";

						echo $row['address_3'] . " ";

						echo "</td>";

						echo "<td>";
						
						$state = (ifCountryHasState($_SESSION['country_default'])==true)?$row['state']:'N/A';
						echo $state . " ";

						echo "</td>";
						
						
						// get dynamic services
						$dserv_sql = mysql_query("
							SELECT *
							FROM `alarm_job_type`
							WHERE `active` = 1
						");
						while($dserv = mysql_fetch_array($dserv_sql)){
							echo "<td>";
							echo getPropertyServiceStatus($row['property_id'],$dserv['id']);
							echo "</td>";
						}
						
					
						

						if ($row[9] == "") {

							$comma = "";

						} else {

							$comma = ", ";

						}
						$ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}");
						echo "<td><a href='" . $ci_link . "'>{$row['agency_name']}</a></td>";
						echo "</tr>\n" ;

					}

					// (5) Close the database connection
					?>
              
            </table>
            
            </td>
        </tr>
      </table>
    </form>
	
	
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
	
	// region multi select script
	jQuery(".state_ms").click(function(){
		
		var state = jQuery(this).val();
		var state_chk = jQuery(this).prop("checked");
		
		//console.log(state_sel);	
		
		if(state_chk==true){
			
			jQuery("#load-screen").show();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_AJPregionFilterMainRegion.php",
				data: { 
					state: state,
					job_status: '<?php echo $job_status ?>'
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
		var check_all = obj.parents("li.main_region_li").find(".check_all_sub_region").prop("checked");
		
		
		if(sub_reg_space==""){
			
			jQuery("#load-screen").show();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_AJPregionFilterSubRegion.php",
				data: { 
					region: region,
					job_status: '<?php echo $job_status ?>'
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
</body></html>