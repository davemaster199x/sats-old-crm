<?php

$title = "Warranty Report";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$url = 'warranty_report.php';

// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class;

$tech_staff_id = mysql_real_escape_string($_REQUEST['tech_staff_id']);

/*
$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$from = ($_REQUEST['from']!='')?mysql_real_escape_string($_REQUEST['from']):date('d/m/Y');
$from2 = ( $from != '' )?$crm->formatDate($from):'';
$to = ($_REQUEST['to'])?mysql_real_escape_string($_REQUEST['to']):date('d/m/Y');
$to2 = ( $to != '' )?$crm->formatDate($to):'';
$search_flag = mysql_real_escape_string($_REQUEST['search_flag']);
*/





// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'p.`postcode`';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort=".urlencode($sort)."&order_by=".urlencode($order_by)."&from=".urlencode($from)."&to=".urlencode($to)."&agency_id=".urlencode($agency_id)."&phrase=".urlencode($phrase);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;



$jparams = array(
	'tech_staff_id' => $tech_staff_id,
	'sort_list' => array(
		array(
			'order_by' => 'w.`date_created`',
			'sort' => 'DESC'
		)
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'echo_query' => 0
);
$plist = getWarranties($jparams);


$jparams = array(
	'tech_staff_id' => $tech_staff_id,
	'return_count' => 1
);
$ptotal = getWarranties($jparams);



// get warranties
function getWarranties($params){
	

	// filters
	$filter_arr = array();		
	
	
	if( $params['warranty_id'] != "" ){
		$filter_arr[] = "AND w.`warranty_id` = {$params['warranty_id']} ";
	}
	
	if( $params['make'] != "" ){
		$filter_arr[] = "AND w.`make` = {$params['make']} ";
	}
	
	if( $params['model'] != "" ){
		$filter_arr[] = "AND w.`model` = {$params['model']} ";
	}
	
	if( $params['tech_staff_id'] != "" ){
		$filter_arr[] = "AND w.`tech_staff_id` = {$params['tech_staff_id']} ";
	}
	
	/*
	if($params['filterDate']!=''){
		if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
			$filter_arr[] = " AND ( j.`date` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ) ";
		}			
	}

	if($params['phrase']!=''){
		$filter_arr[] = "
		AND (
			(CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
			(a.`agency_name` LIKE '%{$params['phrase']}%')
		 )
		 ";
	}
	*/
	
	// combine all filters
	$filter_str = " WHERE w.`warranty_id` > 0 ".implode(" ",$filter_arr);
	

	//custom query
	if( $params['custom_filter']!='' ){
		$custom_filter_str = $params['custom_filter'];
	}
	
	if($params['custom_select']!=''){
		$sel_str = " {$params['custom_select']} ";
	}else if($params['return_count']==1){
		$sel_str = " COUNT(*) AS jcount ";
	}else if($params['distinct_sql']!=""){
		
		$sel_str = " DISTINCT {$params['distinct_sql']} ";	
		
	}else{
		$sel_str = " * ";
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
	
	
	// GROUP BY
	if($params['group_by']!=''){
		$group_by_str = "GROUP BY {$params['group_by']}";
	}
	
	
	// paginate
	if($params['paginate']!=""){
		if(is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])){
			$pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
		}
	}
	
	
	if( $params['custom_join_table'] != '' ){
			$custom_table_join = $params['custom_join_table'];
	}
	
	

	$sql = "		
		SELECT {$sel_str}
		FROM `warranties` AS w
		LEFT JOIN `staff_accounts` AS sa ON w.`tech_staff_id` = sa.`StaffID`
		{$join_table_imp}
		{$custom_table_join}
		{$filter_str}	
		{$custom_filter_str}
		{$group_by_str}
		{$sort_str}
		{$pag_str}
		
	";		
	
	if($params['echo_query']==1){
		echo $sql;
	}
	
	if($params['return_count']==1){
		$j_sql = mysql_query($sql);
		$row = mysql_fetch_array($j_sql);
		return $row['jcount'];
	}else{
		return mysql_query($sql);
	}
	
	
	
}

?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update, .action_div{
	display:none;
}
.yello_mark{
	background-color: #ffff9d;
}
.green_mark{
	background-color: #c2ffa7;
}
.payment_details_table td {
    padding: 5px;
	border: none;
	text-align: left;
}
.payment_details_table tr {
	border: none;
}
.save_div{
	float:right; 
	margin-bottom: 20px; 
	position: relative; 
	bottom: 85px;
	display:none;
}
.jcolorItRed{
	color: red;
}
.jcolorItGreen{
	color: green;
}
</style>




<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title ?>" href="<?php echo $url; ?>"><strong><?php echo $title ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['print_clear']==1){
			echo '<div class="success">Printed jobs has been cleared</div>';
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
						<label>Technician</label>
						<select name="tech_staff_id">
						<option value=''>--- Select ---</option>
						<?php				
						$jparams = array(
							'distinct_sql' => 'sa.`StaffID`, sa.`FirstName`, sa.`LastName`',
							'echo_query' => 0
						);
						$w_sql = getWarranties($jparams);
						while( $row = mysql_fetch_array($w_sql) ){ ?>
							<option value="<?php echo $row['StaffID']; ?>" <?php echo ($row['StaffID']==$tech_staff_id)?'selected="selected"':''; ?>><?php echo $crm->formatStaffName($row['FirstName'],$row['LastName']); ?></option>
						<?php	
						}						
						?>
						</select>
					</div>
			
					<!--
					<div class="fl-left">
						<label>Phrase:</label>
						<input name="phrase" value="<?php echo $phrase; ?>" class="addinput searchstyle vwjbdtp" style="width: 100px !important;" type="label">
					</div>
					-->
					
					
					
					<div class='fl-left' style="float:left;">	
						<input type="hidden" name="search_flag" value="1" />
						<button type='submit' class='submitbtnImg' id="btn_search">
							<img class="inner_icon" src="images/button_icons/search-button.png">
							Search
						</button>
					</div>
					
					
				
					
					
					
					<div style="clear:both;"></div>

					
					
					  
					  
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
					<th>Technician</th>
					<th>Date</th>
					<th>Make</th>
					<th>Model</th>
					<th>Amount Replaced</th>
					<th>Amount Discarded</th>
					<th>Edit</th>
				</tr>
				<?php	
				$i= 0;
				if(mysql_num_rows($plist)>0){
					
					while($row = mysql_fetch_array($plist)){
						
						
						$make = $row['make'];	
						$model = $row['model'];
						$amount_replaced = $row['amount_replaced'];
						$amount_discarded = $row['amount_discarded'];

					
				?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							<td><?php echo $crm->formatStaffName($row['FirstName'],$row['LastName']); ?></td>
							<td><?php echo date('d/m/Y',strtotime($row['date_created'])); ?></td>
							<td>
								<span class="txt_lbl"><?php echo $make; ?></span>
								<span class="txt_hid">
									<input type="text" class="addinput make" value="<?php echo $make; ?>" />
								</span>
							</td>
							<td>
								<span class="txt_lbl"><?php echo $model; ?></span>
								<span class="txt_hid">
									<input type="text" class="addinput model" value="<?php echo $model; ?>" />
								</span>
							</td>
							<td>
								<span class="txt_lbl"><?php echo $amount_replaced; ?></span>
								<span class="txt_hid">
									<input type="text" class="addinput amount_replaced" value="<?php echo $amount_replaced; ?>" />
								</span>
							</td>
							<td>
								<span class="txt_lbl"><?php echo $amount_discarded; ?></span>
								<span class="txt_hid">
									<input type="text" class="addinput amount_discarded" value="<?php echo $amount_discarded; ?>" />
								</span>
							</td>	
							<td>
								<input type="hidden" class="addinput warranty_id" value="<?php echo $row['warranty_id']; ?>" />
								<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
								<div class="action_div">
								
									<button class="blue-btn submitbtnImg btn_update_page">
										<img class="inner_icon" src="images/button_icons/save-button.png">
										Update
									</button>									
									<button class="submitbtnImg btn_cancel">
										<img class="inner_icon" src="images/button_icons/back-to-tech.png">
										Cancel
									</button>
									

								</div>
							</td>
						
						</tr>
						
				<?php
				
					$invoice_amount_tot += $row['invoice_amount'];
					$invoice_payments_tot += $row['invoice_payments'];
					$invoice_credits_tot += $row['invoice_credits'];
				
					$i++;
					} 
				?>
					
					
					
				<?php
				}else{ ?>
					<td colspan="100%" align="left">Empty</td>
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
		
		
		<div class="save_div">				
			<button type='submit' class='submitbtnImg blue-btn' id="btn_save">
				<img class="inner_icon" src="images/button_icons/save-button.png">
				SAVE
			</button>
		</div>

		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	// inline edit toggle
	jQuery(".btn_edit").click(function(){
		
		var btn_txt = jQuery(this).html();
		
		jQuery(this).hide();
		
		if( btn_txt == 'Edit' ){			
			jQuery(this).parents("tr:first").find(".action_div").show();
			jQuery(this).parents("tr:first").find(".txt_hid").show();
			jQuery(this).parents("tr:first").find(".txt_lbl").hide();
		}else{
			jQuery(this).parents("tr:first").find(".action_div").hide();
		}
				
	});
	
	
	// cancel
	jQuery(".btn_cancel").click(function(){
		jQuery(this).parents("tr:first").find(".action_div").hide();
		jQuery(this).parents("tr:first").find(".txt_hid").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".btn_edit").show();		
	});
	
	
	
	// update
	jQuery(".btn_update_page").click(function(){
	
		var warranty_id = jQuery(this).parents("tr:first").find(".warranty_id").val();
		var make = jQuery(this).parents("tr:first").find(".make").val();	
		var model = jQuery(this).parents("tr:first").find(".model").val();
		var amount_replaced = jQuery(this).parents("tr:first").find(".amount_replaced").val();
		var amount_discarded = jQuery(this).parents("tr:first").find(".amount_discarded").val();
		
		var error = "";
		
		if( make == "" ){
			error += "Make is required";
		}
		
		if( model == "" ){
			error += "Model is required";
		}
		
		if( amount_replaced == "" ){
			error += "Amount Replaced is required";
		}
		
		if( amount_discarded == "" ){
			error += "Amount Discarded field is required";
		}
		
		if(error != ""){
			alert(error);
		}else{			
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_warranty.php",
				data: { 
					warranty_id: warranty_id,
					make: make,
					model: model,
					amount_replaced: amount_replaced,
					amount_discarded: amount_discarded
				}
			}).done(function( ret ) {
				window.location="<?php echo $url; ?>?page_update=1";
			});	
						
		}		
		
	});
	
	
});
</script>
</body>
</html>