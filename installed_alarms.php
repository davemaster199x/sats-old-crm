<?php
$title = "Installed Alarms";

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



$from_date = ($_REQUEST['from_date']!='')?mysql_real_escape_string($_REQUEST['from_date']):'';
if($from_date!=''){
	$from_date2 = $crm->formatDate($from_date);
}
$to_date = ($_REQUEST['to_date']!='')?mysql_real_escape_string($_REQUEST['to_date']):'';
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
$params = "&sort={$sort}&order_by={$order_by}&from_date={$from_date}&to_date={$to_date}&state={$state}&alarm_pwr={$alarm_pwr}&alarm_reason={$alarm_reason}&go_search=1";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;



if( $_REQUEST['go_search']==1 ){
	
	// list
	$list_params = array(
		'paginate' => array(
			'offset' => $offset,
			'limit' => $limit
		),
		'sort_list' => array(
			array(
				'order_by' => 'j.`date`',
				'sort' => 'DESC'
			)
		),
		'filterDate' => array(
			'from' => $from_date2,
			'to' => $to_date2
		),
		'echo_query' => 0,
		'new' => 1,
		'state' => $state,
		'alarm_pwr' => $alarm_pwr,
		'alarm_reason' => $alarm_reason
	);
	$alarm_sql = getNewAlarms($list_params);


	// pagination 
	$list_params = array(
		'filterDate' => array(
			'from' => $from_date2,
			'to' => $to_date2
		),
		'echo_query' => 0,
		'new' => 1,
		'state' => $state,
		'alarm_pwr' => $alarm_pwr,
		'alarm_reason' => $alarm_reason
	);
	
	$all_alarm_sql = getNewAlarms($list_params);
	// get alarm list total count
	$ptotal = mysql_num_rows($all_alarm_sql);
	
	
	// get buy and sell price total
	$list_params = array(
		'filterDate' => array(
			'from' => $from_date2,
			'to' => $to_date2
		),
		'echo_query' => 0,
		'new' => 1,
		'state' => $state,
		'alarm_pwr' => $alarm_pwr,
		'alarm_reason' => $alarm_reason,
		'get_buy_and_sell_price_tot' => 1
	);
	$all_alarm_sql = getNewAlarms($list_params);
	$all_alarm = mysql_fetch_array($all_alarm_sql);
	$alarm_price_tot = $all_alarm['alrm_price_tot'];
	$alarm_price_inc = $all_alarm['alarm_price_inc'];	
	
}





function getNewAlarms($params){
	
	// filters
	$filter_arr = array();
	
	$filter_arr[] = "AND alrm.alarm_id > 0";
	
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
	}else if( $params['get_buy_and_sell_price_tot']==1 ){ // alarm price total
		$sel_str = " SUM(alrm.`alarm_price`) AS alrm_price_tot, SUM(alrm_p.`alarm_price_inc`) as alarm_price_inc ";
	}else{ // normal select
		$sel_str = " 
			*, j.`id` AS jid, 
			j.`date` AS jdate, 
			alrm.`alarm_price` AS alrm_alarm_price,
			alrm_p.`alarm_price_ex`
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
		FROM `alarm` AS alrm
		INNER JOIN `jobs` AS j ON alrm.`job_id` = j.`id`
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		INNER JOIN `alarm_pwr` AS alrm_p ON alrm.`alarm_power_id` = alrm_p.`alarm_pwr_id`
		INNER JOIN `alarm_reason` AS alrm_r ON alrm.`alarm_reason_id` = alrm_r.`alarm_reason_id`
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
.inner_icon{
	position: relative;
	top: 2px;
	margin-right: 3px;
}
.toprow{
	text-align:center;
}
.colorItGreen{
	color: green;
}
.colorItRed{
	color: red;
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
				<label style="margin-right: 9px;">From:</label>
				<input type="text" name="from_date" id="from_date" style="width: 85px;" class="addinput datepicker" value="<?php echo ($_REQUEST['from_date']!='')?$from_date:date('1/m/Y'); ?>" />
			</div>
			
			<div class="fl-left">
				<label style="margin-right: 9px;">To:</label>
				<input type="text" name="to_date" id="to_date" style="width: 85px;" class="addinput datepicker" value="<?php echo($_REQUEST['to_date']!='')?$to_date:date('t/m/Y'); ?>" />
			</div>
			
			<div class="fl-left">
				<label style="margin-right: 9px;">Alarm Type:</label>
				<select name="alarm_pwr">
					<option value="">----</option> 	
					<?php
					$alarm_pwr_sql = getAlarmPower();
					while( $alrm_pwr = mysql_fetch_array($alarm_pwr_sql) ){ ?>
						<option value="<?php echo $alrm_pwr['alarm_pwr_id']; ?>" <?php echo ($alrm_pwr['alarm_pwr_id']==$alarm_pwr)?'selected="selected"':''; ?>><?php echo $alrm_pwr['alarm_pwr']; ?></option> 
					<?php
					}
					?>
				</select>
			</div>
			
			<div class="fl-left">
				<label style="margin-right: 9px;">Reason:</label>
				<select name="alarm_reason">
					<option value="">----</option> 	
					<?php
					$alarm_pwr_sql = getAlarmReason();
					while( $alrm_pwr = mysql_fetch_array($alarm_pwr_sql) ){ ?>
						<option value="<?php echo $alrm_pwr['alarm_reason_id']; ?>" <?php echo ($alrm_pwr['alarm_reason_id']==$alarm_reason)?'selected="selected"':''; ?>><?php echo $alrm_pwr['alarm_reason']; ?></option> 
					<?php
					}
					?>
				</select>
			</div>
			
			<div class="fl-left">
				<label style="margin-right: 9px;">State:</label>
				<select name="state">
					<option value="">----</option> 
					<option value="NSW" <?php echo ( $state == 'NSW' )?'selected="selected"':''; ?>>NSW</option>
					<option value="VIC" <?php echo ( $state == 'VIC' )?'selected="selected"':''; ?>>VIC</option>
					<option value="QLD" <?php echo ( $state == 'QLD' )?'selected="selected"':''; ?>>QLD</option>
					<option value="ACT" <?php echo ( $state == 'ACT' )?'selected="selected"':''; ?>>ACT</option>
					<option value="TAS" <?php echo ( $state == 'TAS' )?'selected="selected"':''; ?>>TAS</option>
					<option value="SA" <?php echo ( $state == 'SA' )?'selected="selected"':''; ?>>SA</option>
					<option value="WA" <?php echo ( $state == 'WA' )?'selected="selected"':''; ?>>WA</option>
					<option value="NT" <?php echo ( $state == 'NT' )?'selected="selected"':''; ?>>NT</option>
				</select>
			</div>
			
			<div class="fl-left" style="float:left; margin-left: 10px;">
				
				<button class="submitbtnImg" id="btn_submit" type="submit">
					<img class="inner_icon" src="images/search-button.png" />
					Search
				</button>
				<input type="hidden" name="go_search" value="1" />
				
			</div>	
			
			
		</form>
		
		
	</div>
	

	<table id="expense_tbl" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;">
			<tr class="toprow">				
				<th>Date</th>
				<th>Alarm Type</th>
				<th>Sell Price</th>
				<th>Buy Price</th>
				<th>State</th>
				<th>Reason</th>
				<th>Job</th>		
			</tr>
			<?php				
			if( mysql_num_rows($alarm_sql)>0 && $_REQUEST['go_search']==1 ){
				$i = 0;
				while($alarm = mysql_fetch_array($alarm_sql)){ 
				$sell_price = $alarm['alarm_price']; // from `alarm` table
				$buy_price = $alarm['alarm_price_inc']; // from `alarm_pwr` table
				?>
					<tr class="body_tr"  <?php echo ($i%2==0)?'':'style="background-color:#eeeeee"'; ?>>						
						<td><?php echo date('d/m/Y',strtotime($alarm['jdate'])) ?></td>
						<td><?php echo $alarm['alarm_pwr']; ?></td>
						<td>$<?php echo $sell_price; ?></td>
						<td>$<?php echo $buy_price; ?></td>
						<td><?php echo $alarm['state']; ?></td>
						<td><?php echo $alarm['alarm_reason']; ?></td>
						<td><a href="/view_job_details.php?id=<?php echo $alarm['jid']; ?>"><?php echo $alarm['jid']; ?></a></td>
					</tr>
				<?php
				$i++;
				}
				?>
				<tr>
					<td><strong>TOTAL</strong></td>
					<td></td>
					<td><strong>$<?php echo $alarm_price_tot; ?></strong></td>
					<td><strong>$<?php echo $alarm_price_inc; ?></strong></td>
					<?php
					$diff_tot = ($alarm_price_tot-$alarm_price_inc);
					$diff_tot_class = ( $diff_tot>0 )?'colorItGreen':'colorItRed';						
					?>
					<td class="<?php echo $diff_tot_class; ?>"><strong>$<?php echo number_format($diff_tot,2); ?></strong></td>
					<td></td>
					<td></td>
				</tr>
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
	
});
</script>
</body>
</html>
