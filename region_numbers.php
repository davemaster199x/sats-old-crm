<?

$title = "Region Numbers";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

// Initiate job class
$crm = new Sats_Crm_Class();

$state = mysql_real_escape_string($_POST['state']);
$postcode_region_id = $_POST['postcode_region_id'];
$postcode_region_id2 = implode(",",$postcode_region_id);
$btn_submit = mysql_real_escape_string($_POST['btn_submit']);


if( $btn_submit ){
		
	$fparams = array(
		'sort_list' => array(
			'order_by' => 'r.`region_name`',
			'sort' => 'ASC'
		),
		'state' => $state,
		'postcode_region_id' => $postcode_region_id2
	);
	$regions = $crm->getRegion($fparams);
	
}


function this_getPropertyCount($postcode){
		
	$sql_str = "
		SELECT count( ps.`property_services_id` ) AS ps_count
		FROM `property_services` AS ps
		LEFT JOIN `property` AS p ON p.`property_id` = ps.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE ps.`service` =1
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND p.`postcode` IN({$postcode})
		AND a.`country_id` = {$_SESSION['country_default']}
	";
	
   $sql = mysql_query($sql_str);
	 $sql_row = mysql_fetch_array($sql);
	 return $sql_row['ps_count'];
	
}

function this_getTotPropertyServicePrice($postcode){
		
	$sql_str = "
		SELECT SUM( ps.`price` ) AS tot_ps_price
		FROM `property_services` AS ps
		LEFT JOIN `property` AS p ON p.`property_id` = ps.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE ps.`service` =1
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND p.`postcode` IN({$postcode})
		AND a.`country_id` = {$_SESSION['country_default']}
	";
	
	$sql = mysql_query($sql_str);
	$sql_row = mysql_fetch_array($sql);
	return $sql_row['tot_ps_price'];
	
}

?>
  <div id="mainContent">
  <div class="sats-middle-cont">
  <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="/region_numbers.php"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>


<?php
if($message)
{
	echo "<div class='success'>" . $message . "</div>";	
}
?>


<div class="aviw_drop-h aviw_drop-vp" id="view-jobs" style="border: 1px solid #cccccc;">

	<form method='post'>
	<!--
	<div class="fl-left">
		<label>test:</label>
		<input type="text" name="date" class="datepicker" />
	</div>
	-->

	<div class="fl-left">
		<label>State:</label>
		<select name="state" id="state">
			<option value="">Any</option>
			<?php			
			$state_arr = $crm->getState();
			foreach( $state_arr as $filt_state ) { ?>
				<option value="<?php echo $filt_state; ?>" <?php echo ( $filt_state == $state )?'selected="select"':null; ?>>
					<?php echo $filt_state; ?>
				</option>
			<?php								
			} 
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
				$fparams = array(
					'sort_list' => array(
						'order_by' => 'r.`region_state`',
						'sort' => 'ASC'
					),
					'distinct' => 'r.`region_state`'
				);
				$jstate_sql = $crm->getRegion($fparams);
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
				
				$reg_arr1 = explode(",",$main_reg_pc);
				$reg_arr2 = array_filter($reg_arr1);
				$main_region_postcodes = implode(",",$reg_arr2);
				//$main_region_postcodes = substr($main_reg_pc,1);
				//$jcount = $jc->getMainRegionCount($_SESSION['country_default'],$main_region_postcodes,'',$job_status);
				?>
					<li>
						<input type="checkbox" name="state_ms[]" class="state_ms" value="<?php echo $jstate['region_state']; ?>" /> <span><?php echo $jstate['region_state']; ?> <?php echo ($jcount>0)?"({$jcount})":''; ?></span>
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

	<div class='fl-left' style="float:left;">
		<input type='submit' name='btn_submit' class='submitbtnImg' value='Go'>
	</div>  
	</form>
		
</div>


<table border=0 cellspacing=0 cellpadding=5 width=100% class="table-left tbl-fr-red">

	<tr bgcolor="#b4151b">
		<th style="width:120px;"><b><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></b></th>
		<th><div style="width: 200px;"><b><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?></b></div></th>
		<th><div style="width: 200px;"><b>Sub Region</b></div></th>
		<th><b>Postcodes</b></th>
		<th><b>Properties</b></th>
		<th><b>Service Price</b></th>
		<th><b>Average</b></th>
	</tr>
 
	<?php

	$odd=0;

   // (3) While there are still rows in the result set,
   // fetch the current row into the array $row
   if( mysql_num_rows($regions)>0 ){
	   $prop_count_total = 0;
	   while( $region = mysql_fetch_array($regions) ){

	   $odd++;
		if (is_odd($odd)) {
			echo "<tr bgcolor=#FFFFFF>";		
			} else {
			echo "<tr bgcolor=#eeeeee>";
			}
		?>
		<tr>
			<td><?=$region['region_state'];?></td>
			<td><a href="/edit_main_region.php?id=<?=$region['regions_id'];?>"><?=$region['region_name'];?></a></td>	
			<td><a href="<?=URL;?>edit_region.php?id=<?=$region['postcode_region_id'];?>"><?=$region['postcode_region_name'];?></a></td>
			<td><?=str_replace(",",", ",$region['postcode_region_postcodes']);?></td>	
			<?php
			$p_count = this_getPropertyCount($region['postcode_region_postcodes']);
			$tot_ps_price = this_getTotPropertyServicePrice($region['postcode_region_postcodes']);
			?>
			<td><?php echo $p_count; ?></td>
			<td><?php echo ( $tot_ps_price > 0 )?'$'.number_format($tot_ps_price,2):null; ?></td>
			<td>
				<?php 
					$average_price = ($tot_ps_price/$p_count); 
					echo ( $average_price > 0 )?'$'.number_format($average_price, 2, '.', ''):null;				
				?>
			</td>
		</tr>		
	   <? 
			$prop_count_total += $p_count;
			$tot_ps_price_total += $tot_ps_price;
	   }?>
	   <tr>
		 	<td colspan='4'>&nbsp;</td>
			<td><strong><?php echo $prop_count_total; ?></strong></td>
			<td><strong><?php echo ( $tot_ps_price_total > 0 )?'$'.number_format($tot_ps_price_total,2):null; ?></strong></td>
			<?php
			$average_price_fin = ($tot_ps_price_total/$prop_count_total); 
			?>
			<td><strong><?php echo ( $average_price_fin > 0 )?'$'.number_format($average_price_fin,2, '.', ''):null; ?></strong></td>
		 </tr>
   <?php
   }else{
	   echo '<tr><td colspan=2>Select Region Above</td><td colspan="100%"></td></tr>';
   }
   ?>
	
</table>         



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
				url: "ajax_regionNumbers_getMainRegion.php",
				data: { 
					state: state,
					job_status: '<?php echo $job_status ?>'
				}
			}).done(function( ret ){	
				jQuery("#load-screen").hide();
				jQuery(".region_dp_body").append(ret);
			});
			
		}else{
			var state2 = state.replace(/ /g,"_");
			jQuery("."+state2+"_regions").remove();
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
				url: "ajax_regionNumbers_getSubRegion.php",
				data: { 
					region: region,
					job_status: '<?php echo $job_status ?>',
					return_type: 'region_id'
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
