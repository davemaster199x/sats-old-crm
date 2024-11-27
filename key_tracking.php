<?php

$title = "Key Tracking";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

//$region = $_REQUEST['region'];
if($_POST['region']){
	$region2 = implode(",",$_POST['region']);
	//print_r($region2);
}else if($_GET['region']){
	$region2 = $_GET['region'];
	//echo $region2;
}

$country_id = $_SESSION['country_default'];

$date = mysql_real_escape_string($_REQUEST['date']);
$date2 = ( $date != '' )?$crm->formatDate($date):'';
$agency = mysql_real_escape_string($_REQUEST['agency']);
$tech = mysql_real_escape_string($_REQUEST['tech']);

$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;
$this_page = $_SERVER['PHP_SELF'];
$params = "&date=".urlencode($date)."&agency=".urlencode($agency)."&tech=".urlencode($tech);
$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;



$jparams = array(
	'country_id' => $country_id,
	'completed' => 1,
	'date' => $date2,
	'agency_id' => $agency,
	'tech_id' => $tech,
	'sort_list' => array(
		array(
			'order_by' => 'kr.`date`',
			'sort' => 'DESC'
		)
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'echo_query' => 0
);
$keys_sql = getKeyMapRoutes_v2($jparams);
$jparams = array(
	'country_id' => $country_id,
	'completed' => 1,
	'date' => $date2,
	'agency_id' => $agency,
	'tech_id' => $tech,
	'return_count' => 1
);
$ptotal = getKeyMapRoutes_v2($jparams);

// get Invoice Credit
function getKeyMapRoutes_v2($params){
	

	// filters
	$filter_arr = array();		
	
	
	if( $params['date'] != "" ){
		$filter_arr[] = "AND kr.`date` = '{$params['date']}'";
	}
	
	if( $params['agency_id'] != "" ){
		$filter_arr[] = "AND kr.`agency_id` = {$params['agency_id']}";
	}
	
	if( $params['tech_id'] != "" ){
		$filter_arr[] = "AND kr.`assigned_tech` = '{$params['tech_id']}'";
	}
	
	if(  is_numeric($params['completed']) ){
		$filter_arr[] = "AND kr.`completed` = {$params['completed']}";
	}
	
	if(  $params['country_id'] != '' ){
		$filter_arr[] = "AND a.`country_id` = {$params['country_id']}";
	}
	
	/*
	if($params['filterDate']!=''){
		if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
			$filter_arr[] = " AND ( kr.`date` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ) ";
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
	$filter_str = " WHERE kr.`tech_run_keys_id` > 0 ".implode(" ",$filter_arr);
	

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
		FROM `tech_run_keys` AS kr 
		LEFT JOIN `agency` AS a ON kr.`agency_id` = a.`agency_id`
		LEFT JOIN `staff_accounts` AS sa ON kr.`assigned_tech` = sa.`StaffID`
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
.txt_hid, .btn_update{
	display:none;
}
</style>





<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Agency Update Successfull</div>';
		}
		
		
		//echo date('Y-m-d', strtotime("-30 days"));
		
		
		?>
		
		
		<form method="POST" name='example' id='example'>
			<input type='hidden' name='status' value='<?php echo $status ?>'>

			<table border=1 cellpadding=0 cellspacing=0 width="100%">
				<tr class="tbl-view-prop">
				<td>

				<div class="aviw_drop-h aviw_drop-vp" id="view-jobs">

				 
				  
				  <?php
				  //if(ifCountryHasState($_SESSION['country_default'])==true){ 
				  
				 
				  
				  ?>
				  
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

		
		<div class="fl-left">
			<label>Date:</label>
			<input type="text" name="date" class="datepicker" value="<?php echo $date; ?>" />
		</div>


	  <div class="fl-left">

		<label>Agency:</label>
		<select name="agency" id="agency">
					<option value="">Any</option>
					<?php
							$jparams = array(
								'country_id' => $country_id,
								'completed' => 1,
								'date' => $date2,
								'agency_id' => $agency,
								'tech_id' => $tech,
								'distinct_sql' => 'a.`agency_id`, a.`agency_id`, a.`agency_name`',
								'sort_list' => array(
									array(
										'order_by' => 'a.`agency_name`',
										'sort' => 'ASC'
									)
								)
							);
							$agency_sql = getKeyMapRoutes_v2($jparams);
							while($a = mysql_fetch_array($agency_sql)) { ?>
								<option value="<?php echo $a['agency_id']; ?>" <?php echo ($a['agency_id']==$agency)?'selected="selected"':'';  ?>><?php echo $a['agency_name']; ?></option>
							<?php								
							} 
							?>
				  </select>
	  
	  </div>
	  
	  
	  
	  <div class="fl-left">

		<label>Tech:</label>
		<select name="tech" id="tech">
					<option value="">Any</option>
					<?php
							$jparams = array(
								'country_id' => $country_id,
								'completed' => 1,
								'date' => $date2,
								'agency_id' => $agency,
								'tech_id' => $tech,
								'distinct_sql' => 'sa.`StaffID`, sa.`FirstName`, sa.`LastName`',
								'sort_list' => array(
									array(
										'order_by' => 'sa.`FirstName`',
										'sort' => 'ASC'
									),
									array(
										'order_by' => 'sa.`LastName`',
										'sort' => 'ASC'
									)
								)
							);
							$tech_sql = getKeyMapRoutes_v2($jparams);
							while($tech = mysql_fetch_array($tech_sql)) { ?>
								<option value="<?php echo $tech['StaffID']; ?>" <?php echo ($tech['StaffID']==$tech)?'selected="selected"':'';  ?>><?php echo "{$tech['FirstName']} {$tech['LastName']}"; ?></option>
							<?php								
							} 
							?>
				  </select>
	  
	  </div>
				  
				
				   
				  
					
				  
				
					
					
					
					
					<div class='fl-left' style="float:left;"><input type='submit' class='submitbtnImg' value='Go'></div>       
					
					
					
				</div>

				

				<!-- duplicated filter here -->

					  
					  
				</td>
				</tr>
			</table>	  
				  
			</form>

		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
			<tr class="toprow jalign_left">
				<th>Date</th>
				<th>Agency</th>
				<th>Technician</th>
				<th>Action</th>
				<th>Time</th>
				<th>Number of Keys</th>
				<th>Agency Staff</th>
				<th>Signature</th>
			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($keys_sql)>0){
					while($key = mysql_fetch_array($keys_sql)){
				?>
						<tr class="body_tr jalign_left" <?php echo ($i%2==0)?'style="background-color:#eeeeee"':''; ?>>
							<td><?php echo date("d/m/Y",strtotime($key['date'])); ?></td>
							<td>
								<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$key['agency_id']}"); ?>
								<a href="<?php echo $ci_link; ?>"><?php echo $key['agency_name']; ?></a></td>
							<td><?php echo "{$key['FirstName']} {$key['LastName']}"; ?></td>
							<td style="color:<?php echo ($key['action']=="Pick Up")?'green"':'red'; ?>"><?php echo $key['action']; ?></td>
							<td><?php echo ($key['completed_date']!="")?date("H:i",strtotime($key['completed_date'])):''; ?></td>
							<td><?php echo $key['number_of_keys']; ?></td>
							<td><?php echo $key['agency_staff']; ?></td>
							<td>
								<?php
								if( $key['signature_svg']!='' ){ ?>
									<a id="inline" href="#data<?php echo $i; ?>" class="fancybox">show</a>
									<div style="display:none;">
										<div id="data<?php echo $i; ?>">
											<img style="width:300px;" src="<?php echo $key['signature_svg'] ?>" />
										</div>
									</div>
								<?php	
								}
								?>								
							</td>
						</tr>
						
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
	
	// invoke fancybox
	jQuery('.fancybox').fancybox();
	
});
</script>
</body>
</html>