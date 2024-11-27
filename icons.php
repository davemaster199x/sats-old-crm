<?php

$title = "Icons";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');


// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class;


$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$agency_id = mysql_real_escape_string($_REQUEST['agency_id']);
$phrase = mysql_real_escape_string($_REQUEST['phrase']);


// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'p.`postcode`';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort=".urlencode($sort)."&order_by=".urlencode($order_by)."&date=".urlencode($date)."&phrase=".urlencode($phrase)."&agency_id=".urlencode($agency_id);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


$jparams = array(
	'active' => 1,
	'sort_list' => array(
		array(
			'order_by' => 'ico.`page`',
			'sort' => 'ASC'
		)
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'echo_query' => 0,
);
$plist = getButtonIcons($jparams);

$jparams = array(
	'active' => 1,
	'return_count' => 1
);
$ptotal = getButtonIcons($jparams);




// get button_icons db data
function getButtonIcons($params){
	

	// filters
	$filter_arr = array();
	
	if($params['active']!=""){
		$filter_arr[] = "AND ico.`active` = {$params['active']}";
	}
	
	if($params['bi_id']!=""){
		$filter_arr[] = "AND ico.`icon_id` = {$params['bi_id']}";
	}

	
	/*	
	if($params['filterDate']!=''){
		if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
			$filter_arr[] = "AND CAST(sar.`created_date` AS DATE) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
		}			
	}
		
	if($params['phrase']!=''){
		$filter_arr[] = "AND (
			bn.`notes` LIKE '%{$params['phrase']}%' OR
			a.`agency_name` LIKE '%{$params['phrase']}%'
		 )";
	}
	*/
	
	
	// combine all filters
	if( count($filter_arr)>0 ){
		$filter_str = " WHERE ico.`icon_id` > 0 ".implode(" ",$filter_arr);
	}
	

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
	

	$sql = "		
		SELECT {$sel_str}
		FROM `icons` AS ico
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
.txt_hid, .action_div{
	display:none;
}
.yello_mark{
	background-color: #ffff9d;
}
.green_mark{
	background-color: #c2ffa7;
}
#add_icon_div{
	display: none;
	margin: 60px 0;
}
</style>





<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title ?>" href="icons.php"><strong><?php echo $title ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo "<div class='success'>Submission Successful</div>";
		}
		
		if($_GET['update_success']==1){
			echo "<div class='success'>Update Successful</div>";
		}
			
		$error_arr = $_GET['error'];
		if( count($error_arr)>0 ){			
			echo "
			<div class='error'>
			<ul>";
			foreach( $error_arr as $error ){ 
				echo "<li>{$error}</li>";
			}
			echo "				
			</ul>
			</div>";
		}
		
		
		
			
		// no sort yet
		if($_REQUEST['sort']==""){
			$sort_arrow = 'up';
		}
		
		?>

		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
			
			<tr class="toprow jalign_left">
				<th>Icon</th>
				<th>Page</th>
				<th>Description</th>
				<th>Edit</th>
			</tr>
			<?php	
			$i= 0;
			if(mysql_num_rows($plist)>0){
				while($row = mysql_fetch_array($plist)){
					
				// grey alternation color
				$row_color = ($i%2==0)?"":"style='background-color:#eeeeee;'";
				//$row_color = "style='background-color:#24B8EF;'";

				
			?>
					<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
						
						<td>
							<span class="txt_lbl"><img src="<?php echo $row['icon']; ?>" /></span>
							<span class="txt_hid"><img src="<?php echo $row['icon']; ?>" /></span>
						</td>	
						<td>
							<span class="txt_lbl"><?php echo $row['page']; ?></span>
							<input type="text" style="width: 95%;" class="txt_hid page" value="<?php echo $row['page']; ?>" />
						</td>
						<td>
							<span class="txt_lbl"><?php echo $row['description']; ?></span>
							<input type="text" style="width: 95%;" class="txt_hid description" value="<?php echo $row['description']; ?>" />
						</td>	
						<td>
							<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
							<div class="action_div">
								<button class="blue-btn submitbtnImg btn_update">
									<img class="inner_icon" src="images/button_icons/save-button.png">
									Update
								</button>							
								<button class="blue-btn submitbtnImg btn_delete">
									<img class="inner_icon" src="images/button_icons/cancel-button.png">
									Delete
								</button>
								<button class="submitbtnImg btn_cancel">
									<img class="inner_icon" src="images/button_icons/back-to-tech.png">
									Cancel
								</button>
								<input type="hidden" class="icon_id" value="<?php echo $row['icon_id']; ?>" />
							</div>							
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

	<button type='button' class='jfloatleft submitbtnImg blue-btn' id="add_icon_btn">
		<img class="inner_icon" src="images/button_icons/add-button.png">
		Icons
	</button>
	
	<div id="add_icon_div" class="addproperty formholder">
		<form id="add_icon_form" method="post" action="/add_icon.php" enctype="multipart/form-data">
			<div class="row">
				<label class="addlabel" for="file">Icons</label>
				<input type="file" name="icon" id="icon" class="fname uploadfile">
			</div>
			<div class="row">
				<label class="addlabel" for="title">Page</label>
				<input type='text' class="addinput" name="page" id="page" />
			</div> 
			<div class="row">
				<label class="addlabel" for="title">Description</label>
				<textarea class="addtextarea" name="description" id="description"></textarea>
			</div>         				
			<div style="padding-top: 15px; text-align:left;" class="row clear">
				<button type="submit" class="submitbtnImg" id="btn_upload">
					<img class="inner_icon" src="images/button_icons/save-button.png">
					Save
				</button>
			 </div>
		</form>
	</div>
		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	// update
	jQuery(".btn_update").click(function(){
	
		var icon_id = jQuery(this).parents("tr:first").find(".icon_id").val();
		var description = jQuery(this).parents("tr:first").find(".description").val();
		var page = jQuery(this).parents("tr:first").find(".page").val();
		var error = "";
		
		if( description == "" ){
			error += "Description is required";
		}
		
		if( page == "" ){
			error += "Page is required";
		}
		
		if(error != ""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_icon.php",
				data: { 
					icon_id: icon_id,
					description: description,
					page: page
				}
			}).done(function( ret ) {
				window.location="/icons.php?update_success=1";
			});				
			
		}		
		
	});
	
	
	// delete script
	jQuery(".btn_delete").click(function(){
	
		var icon_id = jQuery(this).parents("tr:first").find(".icon_id").val();
	
		if(confirm("Are you sure you want to delete")){
			
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_icon.php",
				data: { 
					icon_id: icon_id
				}
			}).done(function( ret ){
				//window.location = "/view_vehicles.php";
			});	
			
			
		}
	});
	
	// inline edit
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
	
	
	// cancel script
	jQuery(".btn_cancel").click(function(){
		jQuery(this).parents("tr:first").find(".action_div").hide();
		jQuery(this).parents("tr:first").find(".btn_edit").show();	
		jQuery(this).parents("tr:first").find(".txt_hid").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
	});
	
	
	// add icons show/hide script
	jQuery("#add_icon_btn").click(function(){
		
		jQuery("#add_icon_div").show();
		
	});
	
	// add header validation
	jQuery("#add_icon_form").submit(function(){
	
		var icon = jQuery("#icon").val();
		var description = jQuery("#description").val();
		var error = "";
		
		if(icon==""){
			error += "Icon is required\n";
		}
		
		if(description==""){
			error += "Description is required\n";
		}

		if( error != "" ){
			alert(error);
			return false
		}else{
			return true;
		}
		
	});
	
	
});
</script>
</body>
</html>