<?

$title = "View Agencies";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$state = "";
$salesrep = "";
$region = "";

$agency_status = 'active';

$show_data = mysql_real_escape_string($_REQUEST['show_data']);
$show_search = mysql_real_escape_string($_REQUEST['show_search']);

if($_REQUEST){
	$state = $_REQUEST['searchstate'];
	$salesrep = $_REQUEST['searchsalesrep'];
	$region = $_REQUEST['searchregion'];
}

	
	// header sort parameters
	$sort = ($_REQUEST['sort'])?$_REQUEST['sort']:'a.agency_name';
	$order_by = ($_REQUEST['order_by'])?$_REQUEST['order_by']:'ASC';
	
	// phrase script
	$phrase = $_REQUEST['phrase'];
	
	if($_POST['postcode_region_id']){
		$filterregion = implode(",",$_POST['postcode_region_id']);
		//print_r($region2);
	}else if($_GET['postcode_region_id']){
		$filterregion = $_GET['postcode_region_id'];
		//echo $filterregion;
	}
	
	// pagination script
	$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
	$limit = 50;
	$this_page = $_SERVER['PHP_SELF'];
	
	$params = "&sort={$sort}&order_by={$order_by}&search={$search}&searchstate={$state}&searchsalesrep={$salesrep}&region={$region}&phrase={$phrase}";
	
	$next_link = "{$this_page}?offset=".($offset+$limit).$params;
	$prev_link = "{$this_page}?offset=".($offset-$limit).$params;
	
	$result = get_agency_list(0,$offset,$limit,$sort,$order_by,$state,$salesrep,$filterregion,$phrase);
	$ptotal = mysql_num_rows(get_agency_list(1,'','',$sort,$order_by,$state,$salesrep,$filterregion,$phrase));


?>

<div id="mainContent"> 

<div class="sats-middle-cont">


    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="View Agencies" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>View Agencies</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
   
	<?php
	if( $_REQUEST['agency_deleted']==1 ){ ?>	
		<div class="success">Agency Deleted</div>
	<?php
	}
	?>

<table border=1 cellpadding=0 cellspacing=0>
<tr class="tbl-view-prop">
<td>


	<form method=POST action="<?=URL;?>view_agencies.php" class="searchstyle">

    	<div class="ap-vw-reg agn-prop aviw_drop-h vw-trg-agnc" id="vad-tp" style="height: auto !important;">

			<?php
			
			if( $show_search == 1 ){
			
			?>
			<div class="fl-left dv1fle">
				<label><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?>:</label>
				<?php //$allstates = $user->getAllStates();
				
				$as_sql = mysql_query("
					SELECT DISTINCT ( a.`state`)
					FROM
					  agency a
					LEFT JOIN  agency_regions ar USING (agency_region_id)
					LEFT JOIN staff_accounts s ON (a.salesrep = s.StaffID)
					WHERE a.status = 'active' 
					AND a.`state` !=  ''
					AND a.`country_id` = {$_SESSION['country_default']}
				");
				
				
				
				?>
				<select name="searchstate" class="a-region">
				<option value=''></option>
				<?php
				while( $as = mysql_fetch_array($as_sql) ){ ?>
					<option <?php echo ($state == $as['state'])?'selected="selected"': ''; ?> value='<?php echo $as['state']; ?>'><?php echo $as['state']; ?></option>
				<?php
				}
				?>						
				<?php //foreach($allstates as $states){?>
					<!--<option value="<?//=$states['name'];?>" ><?//=$states['name'];?></option>-->
				<?php //}?>
					
				</select>
			</div>
	
  
			<div class="fl-left dv2fle">
				<label>Sales Rep:</label>
				<?php 
					//$salesreps = mysqlMultiRows("SELECT StaffID, FirstName, LastName FROM staff_accounts WHERE deleted = 0 AND active = 1 ORDER BY FirstName ASC;");
					$sr_sql = getAgencySalesRep('active');
				?>
				<select name="searchsalesrep" class="b-region">
					<option value=''>----</option>
				<?php while($sr = mysql_fetch_array($sr_sql)){ ?>
					<option value="<?php echo $sr['salesrep']; ?>"><?php echo $sr['FirstName']." ".$sr['LastName']; ?></option>
				<?php } ?>
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
						
						// Initiate job class
						$jc = new Job_Class();
						
						// get state
						$jstate_sql = mysql_query("
							SELECT DISTINCT (
								`state`
							)
							FROM `agency`
							WHERE `status` =  '{$agency_status}'
							AND `country_id` = {$_SESSION['country_default']}
							AND `state` != ''
							ORDER BY `state`
						");
						while($jstate =  mysql_fetch_array($jstate_sql)){ 
						
						// get state regions
						$main_reg_pc = [];
						$pc_temp = '';
						$pc_str = '';
						$temp_sql = mysql_query("
							SELECT * 	
							FROM  `regions`
							WHERE `region_state` = '{$jstate['state']}'
							AND `country_id` = {$_SESSION['country_default']}
							AND `status` = 1
						");
						while( $temp = mysql_fetch_array($temp_sql) ){
							$pc_str = jGetPostcodeViaRegion($temp['regions_id']);
							if( $pc_str != '' ){
								$pc_temp = str_replace(',,',',',jGetPostcodeViaRegion($temp['regions_id'])); // sanitize
								$main_reg_pc[] = $pc_temp;
							}
						}
						$main_region_postcodes = implode(",",$main_reg_pc);
						$region_count = getAgencyFilterRegionCount($_SESSION['country_default'],$main_region_postcodes,$agency_status);
							if( $region_count>0 ){
							?>
								<li>
									<input type="checkbox" name="state_ms[]" class="state_ms" value="<?php echo $jstate['state']; ?>" /> <span><?php echo $jstate['state']; ?> (<?php echo $region_count; ?>)</span>
									<input type="hidden" value="<?php echo $main_region_postcodes; ?>" />
								</li>
							<?php	
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
			  
			  
			<div class="fl-left dv4fle">
			<label>Phrase:</label>
			<input type="text" value="" size="10" name="phrase" class="addinput searchstyle">
			</div>

			<div class="fl-left">
			
			<input type="hidden" name="show_data" value="1" />
				<label>&nbsp;</label>
				<input class="searchstyle submitbtnImg" type="hidden" value="Go" />
				<button class="btn_show_search submitbtnImg" type="submit">
					<img class="inner_icon" src="images/button_icons/search-button.png">
					Search
				</button>
			</div>
  
			<?php
			}else{ ?>
			<div class="fl-left">
				<a href="/view_agencies.php?show_search=1&<?php echo $_SERVER['QUERY_STRING']; ?>">
					<button class="btn_show_search submitbtnImg blue-btn" type="button">
						<img class="inner_icon" src="images/button_icons/search-button.png">
						Display Search
					</button>
				</a>
			</div>
			<?php	
			}
			?>
			
			<div style="float:right; margin: 0 5px">
				<a href="/view_agencies.php?show_data=1&<?php echo $_SERVER['QUERY_STRING']; ?>">
					<button class="btn_show_data submitbtnImg blue-btn" type="button">
						<img class="inner_icon" src="images/button_icons/show-button.png">
						Show Details
					</button>
				</a>
			</div>
			
			
			<div style="float:right; margin: 0 5px">
				<a href="/export_agencies.php?sort=<?php echo $sort; ?>&order_by=<?php echo $order_by; ?>&state=<?php echo $state; ?>&salesrep=<?php echo $salesrep; ?>&region=<?php echo $region; ?>&phrase=<?php echo $phrase; ?>">
					<button class="btn_export submitbtnImg agency_export" type="button">
						<img class="inner_icon" src="images/button_icons/export.png">
						Export
					</button>
				</a>
			</div>
  
  
			<div style="float:right; margin: 0 5px">
				<a href="/mailer_export_agencies.php?sort=<?php echo $sort; ?>&order_by=<?php echo $order_by; ?>&state=<?php echo $state; ?>&salesrep=<?php echo $salesrep; ?>&region=<?php echo $region; ?>&phrase=<?php echo $phrase; ?>">
					<button class="btn_mailer_export submitbtnImg mailer_export blue-btn" type="button">
						<img class="inner_icon" src="images/button_icons/export.png">
						Mailer Export
					</button>
				</a>
			</div>


			
  
			
  
</div>
    
		</form>

		<?php
					
					
			if($_GET['order_by']){
				if($_GET['order_by']=='ASC'){
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
			$active = ($_GET['sort']=="")?'arrow-top-active':'';
					
		?>

<table border=0 cellspacing=1 cellpadding=5 width=100% class="table-left tbl-fr-red">
<tr bgcolor="#b4151b">
<th><b><a href="<?php echo $_SERVER['PHP_SELF']; ?>?sort=a.agency_name&order_by=<?php echo ($_GET['sort']=='a.agency_name')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Agency Name</div> <?php echo ($_GET['sort']=='a.agency_name')?$sort_arrow:'<div class="arw-std-up '.$active.'"></div>'; ?></a></b></th>
<th><b><?php echo $_SESSION['country_default']==1?'ABN Number':'GST Number'; ?></b></th>
<th><b>Phone</b></th>
<th><b><a href="<?php echo $_SERVER['PHP_SELF']; ?>?sort=a.contact_first_name&order_by=<?php echo ($_GET['sort']=='a.contact_first_name')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Contact</div> <?php echo ($_GET['sort']=='a.contact_first_name')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></b></th>
<th><b>Last Contact</b></th>
<th><b><a href="<?php echo $_SERVER['PHP_SELF']; ?>?sort=s.FirstName&order_by=<?php echo ($_GET['sort']=='s.FirstName')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Sales Rep</div> <?php echo ($_GET['sort']=='s.FirstName')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></b></th>



<th><b><a href="<?php echo $_SERVER['PHP_SELF']; ?>?sort=a.state&order_by=<?php echo ($_GET['sort']=='a.state')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold"><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></div> <?php echo ($_GET['sort']=='a.state')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></b></th>





<th><b> <div class="tbl-tp-name colorwhite bold"><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?></div> </b></th>



<th><b><a href="<?php echo $_SERVER['PHP_SELF']; ?>?sort=a.tot_properties&order_by=<?php echo ($_GET['sort']=='a.tot_properties')?$ob:'ASC'; ?>"><img src="images/serv_img/home_icon_white.png" class="vw_agn_img"/> <?php echo ($_GET['sort']=='a.tot_properties')?$sort_arrow:'<div class="arw-std-up extraarw-std-up"></div>'; ?></a></b></th>
<?php
$ajt_sql = mysql_query("
	SELECT *
	FROM `alarm_job_type`
	WHERE `active` = 1
");
while($ajt = mysql_fetch_array($ajt_sql)){ 
?>
	<th><img src="images/serv_img/<?php echo getServiceIcons($ajt['id'],1); ?>" /></th>
<?php
}
?>
<!--
<th><b>SA %</b></th>
<th style="background-color:#00ae4d"><b>CW %</b></th>
<th style="background-color:#f15a22"><b>SS %</b></th>
<th style="background-color:#00aeef"><b>PB %</b></th>
-->
</tr>
<?php
	
	
	
	

  
	//$user->prepareStateString('AND')
   //status 'target' is not active agency but stored in database

	$odd=0;

	// get services numbers
	function get_serv_num($agency_id,$alarm_job_type_id,$service=""){
	
		$str = "";
	
		if($service !== ""){
			$str .= " AND ps.`service` = {$service} ";
		}
	
		$sql = "
			SELECT COUNT( * ) AS num_serv
			FROM `property_services` AS ps
			LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE p.`agency_id` ={$agency_id}
			AND p.`deleted` =0
			AND ps.`alarm_job_type_id` ={$alarm_job_type_id}
			{$str}
		";
	
		$serv_sql = mysql_query($sql);
		
		if(mysql_num_rows($serv_sql)>0){		
			$serv = mysql_fetch_array($serv_sql);		
			return $serv['num_serv'];		
		}else{
			return 0;	
		}	
	
	}
	
	
   
	$serv_total = array();
	
   while ($row = mysql_fetch_array($result))

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

		

		echo "<td ".(($show_data=='')?'colspan="100%"':'').">";		
		$ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}");
		echo "<a href='{$ci_link}' target='blank'>{$row['agency_name']}</a>";
		echo "</td>";
		
		
		if( $show_data == 1 ){

		echo "<td>";		
		echo $row['abn'];
		echo "</td>";
		
		echo "<td>";		
		echo $row['phone'];
		echo "</td>";

		echo "<td>";		
		echo "{$row['contact_first_name']} {$row['contact_last_name']}";
		echo "</td>";
		
		// last contact
		$crm_sql = mysql_query("
			SELECT *
			FROM `agency_event_log`
			WHERE `agency_id` ={$row['agency_id']}
			ORDER BY `eventdate` DESC
			LIMIT 0 , 1
		");
		$crm = mysql_fetch_array($crm_sql);
		
		?>
		
		<td><?php echo ($crm['eventdate']!="")?"<a href='{$ci_link}'>".date("d/m/Y",strtotime($crm['eventdate']))."</a>":''; ?></td>
		
		<?
	
		echo "<td>";		
		echo $row['FirstName'];
		echo "</td>";

	
			echo "<td align='center'>";		
			echo $row['state'];
			echo "</td>";
		
		
		

		echo "<td align='left' style='font-size: 10px;'>";
		
		
		echo $row['postcode_region_name'];
		//echo "{$row['agency_region_name']}";
		echo "</td>";
		
		
		// agency site
		$url = $_SERVER['SERVER_NAME'];
		# Decode password
		$encrypt = new cast128();
		$encrypt->setkey(SALT);
		  if(UTF8_USED)
		  {
			   $pass = $encrypt->decrypt(utf8_decode($row['password']));
			}
		  else
		  {
			  $pass = $encrypt->decrypt($row['password']);
		  }
		$url_params = "?user={$row['login_id']}&pass={$pass}";
		
		$dev_str = (strpos($url,"crmdev")===false)?'':'dev';
		

		if($_SESSION['country_default']==1){ // AU
		
			if( strpos($url,"crmdev")===false ){ // live
				$agency_site = "//agency.sats.com.au{$url_params}";
			}else{ // dev
				$agency_site = "//agencydev.sats.com.au{$url_params}";
			}
			
		}else if($_SESSION['country_default']==2){ // NZ
			
			if( strpos($url,"crmdev")===false ){ // live
				$agency_site = "//agency.sats.co.nz{$url_params}";
			}else{ // dev
				$agency_site = "//agencydev.sats.co.nz{$url_params}";
			}
			
			
		}
		

		

		echo "<td align='center'>";		
		echo $row['tot_properties'];
		echo "</td>";
		$prop_tot += intval($row['tot_properties']);
	
		$ajt_sql = mysql_query("
			SELECT *
			FROM `alarm_job_type`
			WHERE `active` = 1
		");
		$i = 0;
		while($ajt = mysql_fetch_array($ajt_sql)){ 
			echo "<td align='center'>";		
			echo $serv_count = getServiceCount($row['agency_id'],$ajt['id']);
			echo "</td>";
			$serv_total[$i] += intval($serv_count);
			$i++;
		}
		
		
		}

      // Print a carriage return to neaten the output

	  echo "</tr>";

      echo "\n";
	  
	  ?>

		
		
		
		
	  <?php
	  
	
   }

   // (5) Close the database connection

   

?>

<?php
if( $show_data == 1 ){ ?>

	<tr>
		<td align='center' colspan="8"><b>Total</b></td>
		<td align='center'><b><?php echo $prop_tot; ?></b></td>
		<?php 
		foreach($serv_total as $val){ ?>
			<td align='center'><b><?php echo $val; ?></b></td>
		<?php
		}
		?>
	</tr>
	
<?php	
}
?>


    
</table>

</td>
</tr>
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


<style>
.serv_td{
	padding: 0 10px!important;
    width: 115px!important;
}
</style>
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
				url: "ajax_getAgencyMainRegion.php",
				data: { 
					state: state,
					agency_status: '<?php echo $agency_status; ?>'
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
				url: "ajax_getAgencySubRegion.php",
				data: { 
					region: region,
					agency_status: '<?php echo $agency_status; ?>'
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
