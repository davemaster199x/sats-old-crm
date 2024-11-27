<?php

$title = "View Tools";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$item = mysql_real_escape_string($_POST['item']);
$search_vehicle = mysql_real_escape_string($_POST['search_vehicle']);
$search_phrase = mysql_real_escape_string($_POST['search_phrase']);
$country_id = $_SESSION['country_default'];


// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&item={$item}&search_vehicle={$search_vehicle}&search_phrase={$search_phrase}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$jparams = array(
	'assign_to_vehicle' => $search_vehicle,
	'search_phrase' => $search_phrase,
	'item' => $item,
	'country_id' => $country_id,
	'sort_list' => array(
		'order_by' => 't.`item_id`',
		'sort' => 'ASC'
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	)
);
$tools_sql = $crm->getTools($jparams);	

$jparams = array(
	'assign_to_vehicle' => $search_vehicle,
	'search_phrase' => $search_phrase,
	'item' => $item,
	'country_id' => $country_id
);
$ptotal = mysql_num_rows($crm->getTools($jparams));

?>

<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
</style>
<div id="mainContent">    

	<div class="sats-middle-cont">
  
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="<?php echo $title; ?>" href="/view_tools.php"><strong><?php echo $title; ?></strong></a></li>
		  </ul>
		</div>	
		<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
		 <?php
		if($_GET['success']==1){ ?>
			<div class="success">New Tools Added</div>
		<?php
		}
		?>
		
		 <?php
		if($_GET['add_success']==1){ ?>
			<div class="success">New vehicle added</div>
		<?php
		}
		?>
		
		
		<div class="aviw_drop-h" style="border: 1px solid #ccc;">
		<form id="form_search" method="post">
		
		
			<div class="fl-left">
				<label style="margin-right: 9px;">Item:</label>
				<select name="item" id="item">
				<option value="">----</option>
				<?php
				$jparams = array(
					'country_id' => $country_id,
					'distinct' => 't.`item`',
					'sort_list' => array(
						'order_by' => 't.`item`',
						'sort' => 'ASC'
					)
				);
				$item_sql = $crm->getTools($jparams);
				while( $i = mysql_fetch_array($item_sql) ){ ?>
					<option value="<?php echo $i['item']; ?>" <?php echo ( $i['item'] == $item )?'selected="selected"':''; ?>><?php echo $i['item_name']; ?></option>
				<?php	
				}
				?>
			</select>
			</div>
		
		
			<div class="fl-left">
				<label style="margin-right: 9px;">Vehicle:</label>
				<select name="search_vehicle" id="search_vehicle">
				<option value="">----</option>
				<?php
				// get Vehicle
				$jparams = array(
					'country_id' => $_SESSION['country_default'],
					'sort_list' => array(
						'order_by' => 'v.`number_plate`',
						'sort' => 'ASC'
					)
				);
				$v_sql = $crm->getVehicles($jparams);
				while( $v = mysql_fetch_array($v_sql) ){ ?>
					<option value="<?php echo $v['vehicles_id']; ?>" <?php echo ( $v['vehicles_id'] == $search_vehicle )?'selected="selected"':''; ?>><?php echo $v['number_plate']; ?></option>
				<?php	
				}
				?>
			</select>
			</div>
			
			
			
			<div class="fl-left">
				<label style="margin-right: 9px;">Search:</label>
				<input type="text" name="search_phrase" id="search_phrase" style="width: 100px" class="addinput" />
			</div>
			
			<div class="fl-left" style="float:left; margin-left: 10px;">
				<input type="submit" name="btn_submit" class="submitbtnImg" value="Go" />
			</div>	
		</form>
		</div>

		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;">
			<tr class="toprow jalign_left">				
				<th>Item</th>
				<th>Item ID</th>
				<th>Brand</th>
				<th>Description</th>
				<th>Purchase Date</th>
				<th>Purchase Price</th>
				<th>Vehicle</th>
				<th>Last Inspection</th>
				<th>Next Inspection</th>
				<!--<th>Edit</th>-->
			</tr>
			<?php				
			while($t = mysql_fetch_array($tools_sql)){ ?>
				<tr class="body_tr jalign_left">
					<td>
						<span class="txt_lbl"><?php echo $t['item_name']; ?></span>
						<input type="hidden" name="tools_id" class="tools_id" value="<?php echo $t['tools_id']; ?>" />
					</td>
					<td>
						<span class="txt_lbl"><a href="view_tool_details.php?id=<?php echo $t['tools_id']; ?>"><?php echo $t['item_id']; ?></a></span>						
					</td>	
					<td>
						<span class="txt_lbl"><?php echo $t['brand']; ?></span>						
					</td>
					<td>
						<span class="txt_lbl"><?php echo $t['description']; ?></span>						
					</td>
					<td>
						<?php $purchase_date = ( $t['t_purchase_date']!="" && $t['t_purchase_date']!="0000-00-00" && $t['t_purchase_date']!="1970-01-01" )?date('d/m/Y',strtotime($t['t_purchase_date'])):''; ?>
						<span class="txt_lbl"><?php echo $purchase_date; ?></span>						
					</td>
					<td>
						<span class="txt_lbl">$<?php echo $t['t_purchase_price']; ?></span>						
					</td>
					<td>
						<?php echo $t['number_plate']; ?>
					</td>
					<?php
					if( $t['item']==1 || $t['item']==2 || $t['item']==4 ){
						$jparams = array(
							'item' => $t['item'],
							'tools_id' => $t['tools_id']
						);
						//print_r($jparams);
						$tools2_sql = $crm->getToolsLastInspection($jparams);
						if( mysql_num_rows($tools2_sql)>0 ){
							$tools2 = mysql_fetch_array($tools2_sql);
							
							// Age
							$next_insp_last_30days = date('Y-m-d',strtotime($tools2['inspection_due'].' -30 days'));  
							$today = date('Y-m-d');
							//$date1=date_create(date('Y-m-d',strtotime($tools2['inspection_due'])));
							//$date2=date_create(date('Y-m-d'));
							//$diff=date_diff($date1,$date2);
							//$age = $diff->format("%r%a");
							//echo (((int)$age)!=0)?$age:0;
							
							$last_insp = $tools2['date'];
							$last_insp2 = ( $crm->isDateNotEmpty($last_insp) )?$crm->formatDate($last_insp,'d/m/Y'):'';
							$next_insp = $tools2['inspection_due'];
							$next_insp2 = ( $crm->isDateNotEmpty($next_insp) )?$crm->formatDate($next_insp,'d/m/Y'):'';
						}else{
							$last_insp2 = '';
							$next_insp2 = '';
						}
						
					}					
					?>
					<td>
						<span class="txt_lbl"><?php echo $last_insp2; ?></span>
					</td>
					<td>
						<span class="txt_lbl" <?php echo ( ($today>=$next_insp_last_30days) || ($today>=$next_insp) )?'style="color:red;font-weight:bold;"':''; ?>><?php echo $next_insp2; ?></span>
					</td>
					<!--
					<td>			
						<button class="blue-btn submitbtnImg btn_update">Update</button>
						<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
						<button class="submitbtnImg btn_cancel" style="display:none;">Cancel</button>
					</td>	
					-->
				</tr>
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
		
	
     <div class="row" style="display:block; padding-top: 20px; clear: both;">
        <a href="/add_tools.php"><button style="float: left;" type="button" id="btn_add_vehicle" class="submitbtnImg">Add Tools</button></a>
     </div>
	
	</div>
	
</div>

<br class="clearfloat" />

<script>
jQuery(document).ready(function(){

	jQuery(".btn_edit").click(function(){
	
		jQuery(this).parents("tr:first").find(".btn_update").show();
		jQuery(this).parents("tr:first").find(".btn_edit").hide();
		jQuery(this).parents("tr:first").find(".btn_cancel").show();
		jQuery(this).parents("tr:first").find(".btn_delete").show();
		jQuery(this).parents("tr:first").find(".txt_hid").show();
		jQuery(this).parents("tr:first").find(".txt_lbl").hide();
	
	});	
	
	jQuery(".btn_cancel").click(function(){
		
		jQuery(this).parents("tr:first").find(".btn_update").hide();
		jQuery(this).parents("tr:first").find(".btn_edit").show();
		jQuery(this).parents("tr:first").find(".btn_cancel").hide();
		jQuery(this).parents("tr:first").find(".btn_delete").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".txt_hid").hide();	
		
	});
	
	jQuery(".btn_update").click(function(){
	
		var vehicles_id = jQuery(this).parents("tr:first").find(".vehicles_id").val();
		var make = jQuery(this).parents("tr:first").find(".make").val();
		var model = jQuery(this).parents("tr:first").find(".model").val();
		var plant_id = jQuery(this).parents("tr:first").find(".plant_id").val();
		var number_plate = jQuery(this).parents("tr:first").find(".number_plate").val();
		var staff_id = jQuery(this).parents("tr:first").find(".staff_id").val();
		var tech_vehicle = jQuery(this).parents("tr:first").find(".tech_vehicle").val();		
		var kms = jQuery(this).parents("tr:first").find(".kms").val();
		var next_service = jQuery(this).parents("tr:first").find(".next_service").val();
		var active = jQuery(this).parents("tr:first").find(".active").val();		
		var error = "";
		
		if(number_plate==""){
			error += "Number Plate is required";
		}
		
		if(error!=""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "test_ajax_update_tools.php",
				data: { 
					vehicles_id: vehicles_id,
					make: make,
					model: model,
					plant_id: plant_id,
					number_plate: number_plate,
					staff_id: staff_id,
					kms: kms,
					next_service: next_service,
					tech_vehicle: tech_vehicle,
					active: active
				}
			}).done(function( ret ) {
				//window.location="/view_vehicles.php?success=1";
			});				
			
		}		
		
	});
	
	jQuery(".btn_delete").click(function(){
	
		var vehicles_id = jQuery(this).parents("tr:first").find(".vehicles_id").val();
	
		if(confirm("Are you sure you want to delete")){
			jQuery.ajax({
				type: "POST",
				url: "test_ajax_delete_tools.php",
				data: { 
					vehicles_id: vehicles_id,
				}
			}).done(function( ret ){
				//window.location = "/view_vehicles.php";
			});	
		}
	});
	
	

});
</script>
</body>
</html>
