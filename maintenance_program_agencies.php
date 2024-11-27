<?php
$title = "Maintenance Program Agencies";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;
//$crm->displaySession();


function getMaintenanceProgramAgencies($params){
	
	// filters
	$filter_arr = array();
	
	$filter_arr[] = "AND a.`status` = 'active'";
	$filter_arr[] = "AND am.`status` = 1";
	$filter_arr[] = "AND m.`status` = 1";
	
	
	if($params['agency_id']!=""){
		$filter_arr[] = "AND a.`agency_id` = {$params['agency_id']}";
	}
	
	if($params['mm_id']!=""){
		$filter_arr[] = "AND m.`maintenance_id` = {$params['mm_id']}";
	}
	
	if($params['search']!=""){
		$filter_arr[] = "AND a.`agency_name` LIKE '%{$params['search']}%'";
	}
	
	
	// combine all filters
	if( count($filter_arr)>0 ){
		$filter_str = " WHERE ".substr(implode(" ",$filter_arr),3);
	}
	
	//custom query
	if( $params['custom_filter']!='' ){
		$custom_filter_str = $params['custom_filter'];
	}
	
	
	
	
	if($params['return_count']==1){
		$sel_str = " COUNT(*) AS jcount ";
	}else if($params['distinct']!=""){
		
		switch($params['distinct']){			
			case 'a.`agency_id`':
				$sel_str = " DISTINCT a.`agency_id`, a.`agency_name` ";
			break;	
			case 'm.`maintenance_id`':
				$sel_str = " DISTINCT m.`maintenance_id`, m.`name` ";
			break;	
		}	
		
	}else{
		$sel_str = "*, m.`name` AS m_name";
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
		FROM `agency_maintenance` AS am		
		LEFT JOIN `agency` AS a ON am.`agency_id` = a.`agency_id`
		LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
		{$filter_str}	
		{$custom_filter_str}
		{$group_by_str}
		{$sort_str}
		{$pag_str}		
	";
	
	// GROUP BY
	if($params['echo_query']==1){
		echo $sql;
	}

	return mysql_query($sql);

}


$current_page = $_SERVER['PHP_SELF'];

$user_type = $_SESSION['USER_DETAILS']['ClassID'];
$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$loggedin_staff_name = "{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}";
$country_id = $_SESSION['country_default'];


$search = mysql_real_escape_string($_REQUEST['search']);
$mm_id = mysql_real_escape_string($_REQUEST['software']);



/*
$from_date = ($_POST['from_date']!='')?mysql_real_escape_string($_POST['from_date']):'';
if($from_date!=''){
	$from_date2 = $crm->formatDate($from_date);
}
$to_date = ($_POST['to_date']!='')?mysql_real_escape_string($_POST['to_date']):'';
if($to_date!=''){
	$to_date2 = $crm->formatDate($to_date);
}
*/




// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort={$sort}&order_by={$order_by}&search={$search}&software={$mm_id}";


// list
$list_params = array(
	'search' => $search,
	'mm_id' => $mm_id,
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'sort_list' => array( 
		array(
			'order_by' => 'a.`agency_name`',
			'sort' => 'ASC'
		)
	),
	'echo_query' => 0
);
//$cr_sql = $crm->getSMSrepliesMergedData($list_params);
$cr_sql = getMaintenanceProgramAgencies($list_params);


$list_params = array(
	'search' => $search,
	'mm_id' => $mm_id,
);
$ptotal = mysql_num_rows(getMaintenanceProgramAgencies($list_params));





?>
<style>
.addproperty input, .addproperty select {
    width: 350px;
}
.addproperty label {
   width: 230px;
}
.tbl_chkbox td{
	text-align: left;
}

.tbl_chkbox tr{
	border: none !important;
}

.tbl_chkbox tr.tr_last_child{
	border-bottom: medium none !important;
}
.chkbox {
    width: auto !important;
}
.chk_div{
	float: left;
}
.chk_div input, .chk_div span{
	float: left;
}
.chk_div input{
	margin-top: 3px;
}
.chk_div span{
    margin: 0 5px 0 5px;
}
textarea.description{
	height: 79px;
    margin: 0;
    width: 340px;
}
input#amount{
	display: inline;
    margin-left: 4px;
    width: 338px;
}

table#expense_tbl td, table#expense_tbl th{
	text-align: left;
}

.approvedHLstatus {
    color: green;
    font-weight: bold;
}
.pendingHLstatus {
    color: red;
    font-style: italic;
}
.declinedHLstatus {
    color: red;
	font-weight: bold;
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
		<div class="success" style="margin-bottom: 12px;">Submission Successful</div>
	<?php
	}else if($_GET['del_success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Delete Successful</div>
	<?php	
	}else if($_GET['update_success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Update Successful</div>
	<?php	
	}
	?>
	
	
	<div class="aviw_drop-h" style="border: 1px solid #ccc;">
	
		<form id="form_search" method="post" action="<?php echo $current_page; ?>">			
			
			<div class="fl-left">
				<label style="margin-right: 9px;">Agency Search:</label>
				<input type="text" name="search" id="search" style="width: 200px;" class="addinput" value="<?php echo $search; ?>" />
			</div>

			
			<div class="fl-left">
				<label style="margin-right: 9px;">Software</label>
				<select name="software">
					<option value="">--- Select ---</option>
					<?php
					$list_params = array(
						'distinct' => 'm.`maintenance_id`',
						'sort_list' => array( 
							array(
								'order_by' => 'm.`name`',
								'sort' => 'ASC'
							)
						)
					);
					$sms_type_sql2 = getMaintenanceProgramAgencies($list_params);
					while($sms_type2 = mysql_fetch_array($sms_type_sql2)){ ?>
						<option value="<?php echo $sms_type2['maintenance_id']; ?>" <?php echo ( $sms_type2['maintenance_id'] == $mm_id )?'selected="selected"':''; ?>><?php echo $sms_type2['name']; ?></option>
					<?php 
					}
					?>
				</select>
			</div>
			
			
			
			<div class="fl-left" style="float:left; margin-left: 10px;">
				<input type="submit" name="btn_submit" class="submitbtnImg" value="Go" />				
			</div>	
			
			
		</form>
		
		<!--
		<div style="float: right;">
			<a href="/export_expense_summary.php?from_date=<?php echo $from_date ?>&to_date=<?php echo $to_date ?>">
				<button type="button" name="btn_submit" class="submitbtnImg">Export</button>
			</a>
		</div>
		-->
		
	</div>
	

	<table id="expense_tbl" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;">
			<tr class="toprow jalign_left">
				<th>Agency</th>
				<th>Software</th>
				<th>Surcharge</th>
				<th>Message</th>		
			</tr>
			<?php				
			if( mysql_num_rows($cr_sql)>0 ){
				$i = 0;
				while($cr = mysql_fetch_array($cr_sql)){ 


				?>
					<tr class="body_tr jalign_left"  <?php echo ($i%2==0)?'':'style="background-color:#eeeeee"'; ?>>
						<td>
							<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$cr['agency_id']}"); ?>
							<a href="<?php echo $ci_link; ?>"><?php echo $cr['agency_name']; ?></a></td>
						<td><?php echo $cr['m_name']; ?></td>
						<td><?php echo ($cr['price']>0)?'$'.$cr['price']:''; ?></td>
						<td><?php echo $cr['surcharge_msg']; ?></td>
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
		
		

		
		
    
  </div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	jQuery("#sms_replies_chk_main").change(function(){
	
		var obj = jQuery(this);
		var chk_state = obj.prop("checked");
		
		if( chk_state == true ){
			jQuery("#sms_chk_hdr").html("ALL");
			var url = "/incoming_sms.php?show_all=1";
		}else{
			jQuery("#sms_chk_hdr").html("Unread");
			var url = "/incoming_sms.php";
		}
		
		window.location = url;
		
	});


	jQuery(".sms_replies_chk").change(function(){
		
		var obj = jQuery(this);
		var chk_state = obj.prop("checked");
		var sar_id = obj.parents("tr:first").find(".sar_id").val();
		
		if(chk_state==true){
			var unread = 1;
		}else{
			var unread = 0;
		}
			
		
		
		if( confirm("Are you sure you want to continue?") ){
				
				jQuery("#load-screen").show();
				jQuery.ajax({
						type: "POST",
						url: "ajax_toggle_sms_replies.php",
						data: { 
							sar_id: sar_id,
							unread: unread
						}
					}).done(function( ret ){
						jQuery("#load-screen").hide();
						//window.location="/incoming_sms.php";
						location.reload();
					});
				
			}

		
	});
	
});
</script>
</body>
</html>
