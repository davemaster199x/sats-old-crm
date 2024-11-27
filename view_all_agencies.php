<?php
$title = "View All Agencies";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$country_id = $_SESSION['country_default'];

$status = mysql_real_escape_string($_REQUEST['status']);
$region_ms = mysql_real_escape_string($_REQUEST['region_ms']);
$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$state = mysql_real_escape_string($_REQUEST['state']);
$sales_rep = mysql_real_escape_string($_REQUEST['sales_rep']);
if($_POST['postcode_region_id']){
	$region_postcode = implode(",",$_POST['postcode_region_id']);
}else if($_GET['postcode_region_id']){
	$region_postcode = $_GET['postcode_region_id'];
}
$agency_status = 'all';

//$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';

// sort
$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'p.`postcode`';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort=".urlencode($sort)."&order_by=".urlencode($order_by)."&status=".urlencode($status)."&region_ms=".urlencode($region_ms)."&phrase=".urlencode($phrase)."&state=".urlencode($state)."&sales_rep=".urlencode($sales_rep)."&postcode_region_id=".$region_postcode;

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$aparams = array(
	'country_id' => $country_id,
	'join_table' => 'sales_rep',
	
	'status' => $status,
	'phrase' => $phrase,
	'state' => $state,
	'sales_rep' => $sales_rep,
	'region_postcode' => $region_postcode,
	
	'sort_list' => array(
		array(
			'order_by' => 'a.`agency_name`',
			'sort' => 'ASC'
		)
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'join_table' => '`postcode_regions`',
	'display_echo' => 0
);
$plist = $crm->getAgency($aparams);


$aparams = array(
	'country_id' => $country_id,
	'join_table' => 'sales_rep',
	
	'status' => $status,
	'phrase' => $phrase,
	'state' => $state,
	'sales_rep' => $sales_rep,
	'region_postcode' => $region_postcode,
	'return_count' => 1
);
$ptotal = $crm->getAgency($aparams);
	
?>

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
	if( $_REQUEST['agency_deleted']==1 ){ ?>	
		<div class="success">Agency Deleted</div>
	<?php
	}
	?>
   

<? if(isset($message)): ?>
<p><?=$message;?></p>
<? endif; ?>

<table cellspacing="0" cellpadding="0">
<tbody><tr class="tbl-view-prop">
<td>
<form method='post' class="vw-trg-agnc" style="margin:0px;">
<div class="ap-vw-reg aviw_drop-h" style="height: 60px;">

	<div class="fl-left">
	<label>Status:</label>
	<select name="status">
		<option value="">---- Select ----</option>
		<option value="active" <?php echo ( $status == 'active' )?'selected="selected"':''; ?>>Active</option>															
		<option value="target" <?php echo ( $status == 'target' )?'selected="selected"':''; ?>>Target</option>
		<option value="deactivated" <?php echo ( $status == 'deactivated' )?'selected="selected"':''; ?>>Deactivated</option>
	</select>
	</div>

	<!-- REGION -->
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
			$jstate_sql = mysql_query("
				SELECT DISTINCT (
					`state`
				)
				FROM `agency`
				WHERE `country_id` = {$_SESSION['country_default']}
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
					$pc_temp = str_replace(',,',',',$pc_str); // sanitize
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
			
			
		});
		</script>
	</div>




	<div class="fl-left">
	<label>Phrase:</label>
	<input type="text" name="phrase" class="addinput phrase" value="<?php echo $phrase; ?>" />
	</div>


	<?php
	// state
	if(ifCountryHasState($_SESSION['country_default'])==true){ ?>
	<div class="fl-left">
		<label><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?>:</label>
		<select name="state">
			<option value="">---- Select ----</option>
			<option <?php echo $state == 'NSW'? 'selected="selected"': '';?> value='NSW'>NSW</option>
			<option <?php echo $state == 'VIC'? 'selected="selected"': '';?> value='VIC'>VIC</option>
			<option <?php echo $state == 'QLD'? 'selected="selected"': '';?> value='QLD'>QLD</option>
			<option <?php echo $state == 'ACT'? 'selected="selected"': '';?> value='ACT'>ACT</option>
			<option <?php echo $state == 'TAS'? 'selected="selected"': '';?> value='TAS'>TAS</option>
			<option <?php echo $state == 'SA'? 'selected="selected"': '';?> value='SA'>SA</option>
			<option <?php echo $state == 'WA'? 'selected="selected"': '';?> value='WA'>WA</option>
			<option <?php echo $state == 'NT'? 'selected="selected"': '';?> value='NT'>NT</option>
		</select>
	  </div>
	<?php	
	}
	?>

	<div class="fl-left">
	<label>Sales Rep:</label>
	<?php
	$aparams = array(
		'country_id' => $country_id,
		'join_table' => 'sales_rep',	
		
		'status' => $status,
		'phrase' => $search,
		'state' => $state,
		'region_postcode' => $region_postcode,
		
		'custom_select' => ' DISTINCT a.`salesrep`, sr_sa.`FirstName`, sr_sa.`LastName` ',
		'custom_filter' => ' AND  a.`salesrep`>0 ',
		'display_echo' => 0
	);
	$salerep_sql = $crm->getAgency($aparams);
	?>
	<select name="sales_rep">
		<option value="">---- Select ----</option>
		<?php
		while( $sr = mysql_fetch_array($salerep_sql) ){ ?>
			<option value="<?php echo $sr['salesrep']; ?>" <?php echo ( $sr['salesrep'] == $sales_rep )?'selected="selected"':''; ?>><?php echo $crm->formatStaffName($sr['FirstName'],$sr['LastName']) ?></option>
		<?php
		}
		?>
	</select>
	</div>











	<div class="fl-left" style="float: left;">
		<label>&nbsp;</label>
		<input type="hidden" value="<?php echo $start;?>" id="start" name="start">
		<input type="hidden" name="search_flag" value="1" />
		<button type="submit" class="submitbtnImg blue-btn">
			<img class="inner_icon" src="images/button_icons/search-button.png">
			Search
		</button>
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
            

<table width="100%" cellspacing="1" cellpadding="5" border="0" class="table-left tbl-fr-red">	
	<tr bgcolor="#b4151b">
		<th>Agency Name</th>
		<th>Sub-Region</th>
		<th>Status</th>
		<th>Sales Rep</th>
	</tr>	 
	<?php
	if( mysql_num_rows($plist)>0 ){
		$i = 0;
		while ($row = mysql_fetch_array($plist)){ 
		
		$row_color = ($i%2==0)?"":"style='background-color:#eeeeee;'";
		?>
		<tr <?php echo $row_color; ?>>
			<td>
				<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
				<a href='<?php echo $ci_link; ?>'>
					<?php echo $row['agency_name']; ?>
				</a>
			</td>
			<td><?php echo $row['postcode_region_name']; ?></td>
			<td><?php echo ucfirst($row['a_status']); ?></td>
			<td><?php echo $crm->formatStaffName($row['FirstName'],$row['LastName']); ?></td>
		</tr>
		<?php
		$i++;
		}
	}else{ ?>
		<tr><td>Please press search to display results</td></tr>
	<?php
	}
	?>	
</table>

            
</td>
</tr>
</tbody>
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
