<?php
$title = "Alarm Pricing Page";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');
//include('inc/ws_sms_class.php');

$crm = new Sats_Crm_Class;
//$crm->displaySession();

$current_page = $_SERVER['PHP_SELF'];

$user_type = $_SESSION['USER_DETAILS']['ClassID'];
$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$loggedin_staff_name = "{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}";
$country_id = $_SESSION['country_default'];



$from_date = ($_POST['from_date']!='')?mysql_real_escape_string($_POST['from_date']):'';
if($from_date!=''){
	$from_date2 = $crm->formatDate($from_date);
}
$to_date = ($_POST['to_date']!='')?mysql_real_escape_string($_POST['to_date']):'';
if($to_date!=''){
	$to_date2 = $crm->formatDate($to_date);
}

$state = mysql_real_escape_string($_REQUEST['state']);
$alarm_pwr = mysql_real_escape_string($_REQUEST['alarm_pwr']);
$alarm_reason = mysql_real_escape_string($_REQUEST['alarm_reason']);



// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort={$sort}&order_by={$order_by}&from_date={$from_date}&to_date={$to_date}&state={$state}&alarm_pwr={$alarm_pwr}&alarm_reason={$alarm_reason}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;



// list
$list_params = array(
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'echo_query' => 0
);
$alarm_sql = getNewAlarms($list_params);


// pagination 
$list_params = array(
	'echo_query' => 0
);

$all_alarm_sql = getNewAlarms($list_params);
// get alarm list total count
$ptotal = mysql_num_rows($all_alarm_sql);





function getNewAlarms($params){
	
	// filters
	$filter_arr = array();
	
	$filter_arr[] = "AND alrm_p.alarm_pwr_id > 0";
	
	if($params['new']!=""){
		$filter_arr[] = "AND alrm.`new` = {$params['new']}";
	}	
	
	if($params['state']!=""){
		$filter_arr[] = "AND p.`state` = '{$params['state']}'";
	}
	
	if($params['alarm_pwr']!=""){
		$filter_arr[] = "AND alrm_p.`alarm_pwr_id` = '{$params['alarm_pwr']}'";
	}
	
	if($params['alarm_reason']!=""){
		$filter_arr[] = "AND alrm_r.`alarm_reason_id` = '{$params['alarm_reason']}'";
	}
	
	// date filter
	if($params['filterDate']!=''){
		if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
			$filter_arr[] = "AND j.`date` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
		}			
	}
	
	// combine all filters
	if( count($filter_arr)>0 ){
		$filter_str = " WHERE ".substr(implode(" ",$filter_arr),3);
	}
	
	//custom query
	if( $params['custom_filter']!='' ){
		$custom_filter_str = $params['custom_filter'];
	}
	
	// select
	if($params['return_count']==1){ // return count
		$sel_str = " COUNT(*) AS jcount ";
	}else if($params['distinct']!=""){
		switch($params['distinct']){ // distinct		
			/*
			case 'p.`state`':
				$sel_str = " DISTINCT p.`state` ";
			break;	
			*/
		}			
	}else if( $params['sum_alarm_price']==1 ){ // alarm price total
		$sel_str = " SUM(alrm.`alarm_price`) AS alrm_price_tot ";
	}else if( $params['sum_ap_alarm_price']==1 ){ // alarm price total
		$sel_str = " SUM(alrm_p.`alarm_price`) AS alrm_p_price_tot ";
	}else{ // normal select
		$sel_str = " 
			*
		";
	}
	
	// sort
	if( $params['sort_list']!='' ){
		
		$sort_str_arr = array();
		foreach( $params['sort_list'] as $sort_arr ){
			if( $sort_arr['order_by']!="" && $sort_arr['sort']!='' ){
				$sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
			}
		}
		
		$sort_str_imp = implode(", ",$sort_str_arr);
		$sort_str = "ORDER BY {$sort_str_imp}";
		
	}	
	
	// paginate
	if($params['paginate']!=""){
		if(is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])){
			$pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
		}
	}
	
	
	
	$sql = "
		SELECT {$sel_str}
		FROM `alarm_pwr` AS alrm_p
		{$filter_str}	
		{$custom_filter_str}
		{$sort_str}
		{$pag_str}
	";
	
	
	if( $params['echo_query']==1 ){
		echo $sql;
	}
	
	return mysql_query($sql);
	
}


function getAlarmPower(){
	return mysql_query("
		SELECT * 
		FROM `alarm_pwr` 
	");
}

function getAlarmReason(){	
	return mysql_query("
		SELECT * 
		FROM `alarm_reason` 
		WHERE `active` = 1
	");
}

?>
<style>
.toprow{
	text-align:left;
}
.txt_hid, .btn_update, .btn_cancel, #add_alarm_pricing_div{
	display:none;
}
.inner_icon{
	position: relative;
	top: 2px;
	margin-right: 3px;
}
.btn_update{
	margin-bottom: 3px;
}
</style>


    
    <div id="mainContent">
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title; ?></strong></a></li>				
		</ul>
	</div>
      
    
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">New Alarm Pricing Added</div>
	<?php
	}else if($_GET['del_success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Delete Successful</div>
	<?php	
	}else if($_GET['update_success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Update Successful</div>
	<?php	
	}
	?>
	
	
	<div style="margin: 40px 0 0;text-align: left;">
		<h2 class="heading">IMPORTANT</h2>
		<ul>
			<li>The Alarms entered into this page are the alarms that are available on the Tech Sheet for the Technicians to install. The pricing on this page is used to control the pricing on the Installed Alarms Report. The prices on this page will not update the alarm prices on the purchase order page. They will need to be adjusted from the Stock Items page.</li>						
		</ul>
	</div>

	<table id="alarm_pricing_tbl" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;">
			<tr class="toprow">				
				<th>Name</th>
				<th>Make</th>
				<th>Model</th>
				<th>Expiry</th>	
				<th>Buy Price EX GST</th>
				<th>Buy Price INC GST</th>
				<th>Active</th>
				<th>Edit</th>				
			</tr>
			<?php				
			if( mysql_num_rows($alarm_sql)>0 ){
				$i = 0;
				while($alarm = mysql_fetch_array($alarm_sql)){ 


				?>
					<tr class="body_tr jalign_left"  <?php echo ($i%2==0)?'':'style="background-color:#eeeeee"'; ?>>						
						<td>
							<span class="txt_lbl"><?php echo $alarm['alarm_pwr']; ?></span>
							<input type="text" name="alarm_pwr" class="txt_hid alarm_pwr" value="<?php echo $alarm['alarm_pwr']; ?>" />
						</td>
						<td>
							<span class="txt_lbl"><?php echo $alarm['alarm_make']; ?></span>
							<input type="text" name="alarm_make" class="txt_hid alarm_make" value="<?php echo $alarm['alarm_make']; ?>" />
						</td>
						<td>
							<span class="txt_lbl"><?php echo $alarm['alarm_model']; ?></span>
							<input type="text" name="alarm_model" class="txt_hid alarm_model" value="<?php echo $alarm['alarm_model']; ?>" />
						</td>
						<td>
							<span class="txt_lbl"><?php echo $alarm['alarm_expiry']; ?></span>
							<input type="text" name="alarm_expiry" class="txt_hid alarm_expiry" value="<?php echo $alarm['alarm_expiry']; ?>" />
						</td>	
						<td>
							<span class="txt_lbl">$<?php echo $alarm['alarm_price_ex']; ?></span>
							<input type="text" name="alarm_price_ex" class="txt_hid alarm_price_ex" value="<?php echo $alarm['alarm_price_ex']; ?>" />
						</td>
						<td>
							<span class="txt_lbl">$<?php echo $alarm['alarm_price_inc']; ?></span>
							<input type="text" name="alarm_price_inc" class="txt_hid alarm_price_inc" value="<?php echo $alarm['alarm_price_inc']; ?>" />
						</td>
						<td>
							<span class="txt_lbl"><?php echo ($alarm['active']==1)?'Yes':'<span style="color:red;">No</span>'; ?></span>
							<select class="txt_hid active" name="active" id="active" style="width: auto !important;">
								<option value="">--Select--</option>
								<option value="1" <?php echo ($alarm['active']==1)?'selected="selected"':''; ?>>Active</option>
								<option value="0" <?php echo ($alarm['active']==0)?'selected="selected"':''; ?>>Inactive</option>
							</select>
						</td>
						<td>
							<a href="javascript:void(0);" class="btn_edit">Edit</a>
							<button class="blue-btn submitbtnImg btn_update">Update</button>							
							<button class="submitbtnImg btn_cancel">Cancel</button>		
							<input type="hidden" name="alarm_pwr_id" class="alarm_pwr_id" value="<?php echo $alarm['alarm_pwr_id']; ?>" />
						</td>
					</tr>
				<?php
				$i++;
				}
				?>
			<?php	
			}else{ ?>
				<tr><td colspan="100%" align="left">Empty</td></tr>
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
		

	<div style="float:left; text-align: left;">
		
			<button type="button" id="btn_add_alarm" class="submitbtnImg blue-btn">
				<img class="inner_icon" id="btn_add_alarm_icon" src="images/add-button.png" /> <span id="btn_add_alarm_txt">Add Alarm</span>									
			</button>
			
            <div style="padding-top: 20px;" id="add_alarm_pricing_div" class="addproperty formholder">
				<form id="add_alarm_pricing_form" method="post" action="/add_alarm_pricing.php">
					<div class="row">
						<label class="addlabel">Name</label>
						<input type="text" name="name" id="name" class="name" />
					</div>         			
					<div class="row">
						<label class="addlabel">Make</label>
						<input type="text" name="make" id="make" class="make" />
					</div>
					<div class="row">
						<label class="addlabel">Model</label>
						<input type="text" name="model" id="model" class="model" />
					</div>
					<div class="row">
						<label class="addlabel">Expiry</label>
						<input type="text" name="expiry" id="expiry" class="expiry" />
					</div>
					<div class="row">
						<label class="addlabel">Price EX GST</label>
						<input type="text" name="price_ex_gst" id="price_ex_gst" class="price_ex_gst" />
					</div>					
					<div class="row">
						<label class="addlabel">Price INC GST</label>
						<input type="text" name="price_inc_gst" id="price_inc_gst" class="price_inc_gst" />
					</div>
					<div style="padding-top: 15px; text-align:left;" class="row clear">
						<button type="submit" class="submitbtnImg blue-btn" style="width: auto;" name="btn_submit">Submit</button>
					</div>
				</form>
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
		jQuery(this).parents("tr:first").find(".txt_hid").show();
		jQuery(this).parents("tr:first").find(".txt_lbl").hide();
	
	});	
	
	jQuery(".btn_cancel").click(function(){
		
		jQuery(this).parents("tr:first").find(".btn_update").hide();
		jQuery(this).parents("tr:first").find(".btn_edit").show();
		jQuery(this).parents("tr:first").find(".btn_cancel").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".txt_hid").hide();	
		
	});
	
	jQuery(".btn_update").click(function(){
	
		var alarm_pwr_id = jQuery(this).parents("tr:first").find(".alarm_pwr_id").val();
		var alarm_pwr = jQuery(this).parents("tr:first").find(".alarm_pwr").val();
		var alarm_make = jQuery(this).parents("tr:first").find(".alarm_make").val();
		var alarm_model = jQuery(this).parents("tr:first").find(".alarm_model").val();
		var alarm_expiry = jQuery(this).parents("tr:first").find(".alarm_expiry").val();
		var alarm_price_ex = jQuery(this).parents("tr:first").find(".alarm_price_ex").val();
		var alarm_price_inc = jQuery(this).parents("tr:first").find(".alarm_price_inc").val();
		var active = jQuery(this).parents("tr:first").find(".active").val();
	
		var error = "";
		
		if(alarm_pwr_id==""){
			error += "Number is required";
		}
		
		if(error!=""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_alarm_pricing.php",
				data: { 
					alarm_pwr_id: alarm_pwr_id,
					alarm_pwr: alarm_pwr,
					alarm_make: alarm_make,
					alarm_model: alarm_model,
					alarm_expiry: alarm_expiry,
					alarm_price_ex: alarm_price_ex,
					alarm_price_inc: alarm_price_inc,
					active: active
				}
			}).done(function( ret ) {
				window.location="alarm_pricing_page.php?update_success=1";
			});				
			
		}		
		
	});
	
	jQuery("#btn_add_alarm").toggle(function(){
		jQuery(this).removeClass("blue-btn");
		jQuery("#btn_add_alarm_icon").attr("src","images/cancel-button.png");
		jQuery("#btn_add_alarm_txt").html("Cancel");
		jQuery("#add_alarm_pricing_div").slideDown();
	},function(){
		jQuery(this).addClass("blue-btn");
		jQuery("#btn_add_alarm_icon").attr("src","images/add-button.png");
		jQuery("#btn_add_alarm_txt").html("Add Alarm");		
		jQuery("#add_alarm_pricing_div").slideUp();
	});
	
	
	jQuery("#add_alarm_pricing_form").submit(function(){
		
	
		var name = jQuery("#add_alarm_pricing_form #name").val();
		var error = "";
		
		if(name==""){
			error += "Alarm Name is Required\n";
		}
		
		if( error!='' ){
			alert(error);
			return false;
		}else{
			return true;
		}
		
		
	});
	
	
});
</script>
</body>
</html>
