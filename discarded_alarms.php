<?php

$title = "Discarded Alarms";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

function getDiscardedAlarms($params){
	
	$filter_str = '';
	
	// search reason
	if($params['reason']!=""){
		$filter_str .= " AND a.`ts_discarded_reason` = {$params['reason']} ";
	}
    
    // search state
    if($params['state']!=""){
		$filter_str .= " AND p.`state` = '{$params["state"]}' ";
	}
	
	
	// date filter
	if( $params['search_date']['from']!="" && $params['search_date']['to']!="" ){
		$filter_str .= " 
			AND CAST( j.`date` AS Date )  BETWEEN '{$params['search_date']['from']}' AND '{$params['search_date']['to']}'
		";
	}
	
	if( $params['sort_query'] != '' ){
		$sort_str = "ORDER BY {$params['sort_query']}";
	}
	
	// pagination
	if(is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])){
		$limit = " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
	}
    
	$sql = "
		SELECT {$params['sel_query']}
		FROM `alarm` AS a
        INNER JOIN `jobs` AS j ON a.`job_id` = j.`id`
        LEFT JOIN `alarm_discarded_reason` AS adr ON a.`ts_discarded_reason` = adr.`id`
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `alarm_pwr` AS ap ON a.`alarm_power_id` = ap.`alarm_pwr_id`
        LEFT JOIN `alarm_type` AS at ON a.`alarm_type_id` = at.`alarm_type_id`
        WHERE a.`ts_discarded` = 1
		{$filter_str}
		{$sort_str}
		{$limit}
	";
	
	if( $params['echo_query'] == 1 ){
		echo $sql;
	}	
	
	return mysql_query($sql);
}


$dateFrom = ($_REQUEST['dateFrom']!="")?jFormatDateToBeDbReady($_REQUEST['dateFrom']):date("Y-m-01");
$dateTo = ($_REQUEST['dateTo']!="")?jFormatDateToBeDbReady($_REQUEST['dateTo']):date("Y-m-t");
$reason = ($_REQUEST['reason']!="")?$_REQUEST['reason']:null;
$state = ($_REQUEST['state']!="")?$_REQUEST['state']:null;

$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;
$this_page = $_SERVER['PHP_SELF'];

$params = "&dateFrom={$dateFrom}&dateTo=".urlencode($dateTo)."&reason=".urlencode($reason)."&state=".$state;
$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

// shortcut links tweak date
$currDate = date('Y-m-d');
$currMonth = (isset($_REQUEST['dateFrom']) && $_REQUEST['dateFrom']!="")?$_REQUEST['dateFrom']:date('Y-m-01');
$nextMonth = date('Y-m-d', strtotime('+1 month', strtotime($currMonth)));
$nextMonthTo = date('Y-m-t', strtotime('+1 month', strtotime($currMonth)));
$prevMonth = date('Y-m-d', strtotime('-1 month', strtotime($currMonth)));
$prevMontTo = date('Y-m-t', strtotime('-1 month', strtotime($currMonth)));

$paramsToday = "?dateFrom={$currDate}&dateTo=".urlencode($currDate)."&reason=".urlencode($reason)."&state=".$state;
$paramsThisMonth = "?dateFrom=".date('Y-m-01')."&dateTo=".urlencode(date('Y-m-t'))."&reason=".urlencode($reason)."&state=".$state;
$paramsNextMonth = "?dateFrom={$prevMonth}&dateTo=".urlencode($prevMontTo)."&reason=".urlencode($reason)."&state=".$state;



// select query
$sel_query = '
   a.`alarm_id`, 
   a.`job_id`, 
   a.`make`, 
   a.`model`, 
   a.`expiry`, 
   a.`ts_discarded_reason`, 
   a.`ts_required_compliance`, 
   adr.`reason`, 
   j.`date`,
   j.`ts_rfc`,
   p.`rfc`,
   p.`state`,
   ap.`alarm_pwr`,
   at.`alarm_type`
';

// get paginated result
$func_params = array(
	'sel_query' => $sel_query,
	'search_date' => array(
		'from' => $dateFrom,
		'to' => $dateTo
	),
    'reason' => $reason,
    'state' => $state,
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'sort_query' => 'a.`alarm_id` DESC',
	'echo_query' => 0
);

$plist = getDiscardedAlarms($func_params);

$func_params2 = array(
	'sel_query' => $sel_query,
    'reason' => $reason,
    'state' => $state,
	'search_date' => array(
		'from' => $dateFrom,
		'to' => $dateTo
	)
   
);
$ptotal = mysql_num_rows(getDiscardedAlarms($func_params2));

?>

<style>
.jalign_left{
	text-align:left;
}

.txt_hid, .btn_update{
	display:none;
}

.jRedColorBold{
	color: red;
    font-weight: bold;
}
</style>
<div id="mainContent">    

	<div class="sats-middle-cont">
  
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="<?php echo $title ?>" href="/discarded_alarms.php"><strong><?php echo $title; ?></strong></a></li>
		  </ul>
		</div>	
		<div id="time"><?php echo date("l jS F Y"); ?></div>
	

		<div class="aviw_drop-h aviw_drop-vp" style="border: 1px solid #ccc; border-bottom: none;">	
			<form method="post">
				<div class="fl-left">
					<label>Date From:</label>
					<input type="text" name="dateFrom" class="datepicker" value="<?php echo date('d/m/Y', strtotime($dateFrom)) ?>"  />
				</div>		
                
                <div class="fl-left">
					<label>Date To:</label>
					<input type="text" name="dateTo" class="datepicker" value="<?php echo date('d/m/Y', strtotime($dateTo)) ?>" />
				</div>	
                
				<div class="fl-left" style="float:left;">
					<?php
                        $func_params = array(
                            'sel_query' => 'DISTINCT a.`ts_discarded_reason`, adr.`reason`',
                            'search_date' => array(
                                'from' => $dateFrom,
                                'to' => $dateTo
                            )
                        );
                        $reason_sql = getDiscardedAlarms($func_params);
                    ?>
					<label>Reason:</label>
					<select name="reason">
						<option value="">----</option>
						<?php
				            while($reason_sql_row = mysql_fetch_array($reason_sql)){ 
                                if($reason_sql_row['ts_discarded_reason']!=0){
				        ?>
                                <option <?php echo ($reason_sql_row['ts_discarded_reason']==$reason)?'selected="selected"':''; ?> value="<?php echo $reason_sql_row['ts_discarded_reason'] ?>"><?php echo $reason_sql_row['reason'] ?></option>
                        <?php 
                                }
                            }
                        ?>
					</select>
				</div>
				<div class="fl-left" style="float:left;">
				<?php
                        $func_params = array(
                            'sel_query' => 'DISTINCT p.`state`',
                            'search_date' => array(
                                'from' => $dateFrom,
                                'to' => $dateTo
                            )
                        );
                        $state_sql = getDiscardedAlarms($func_params);
                    ?>
					<label>State:</label>
					<select name="state">
						<option value="">----</option>
						<?php
				            while($state_sql_row = mysql_fetch_array($state_sql)){ 
                                if(!empty($state_sql_row['state'])){
				        ?>
                            <option <?php echo ($state_sql_row['state']==$state)?'selected="selected"':''; ?> value="<?php echo $state_sql_row['state'] ?>"><?php echo $state_sql_row['state'] ?></option>
                        <?php 
                                }
                            }
                        ?>
					</select>
				</div>				
				<div class="fl-left" style="float:left;">
					<input type="submit" class="submitbtnImg" value="Search" name="btn_search">
				</div>
			</form>			
		</div>
        
        
        <!-- DATE SHORTCUT LINKS START HERE -->
        <div class="aviw_drop-h qlnk" style="padding-top:15px;">
            Quick Links | <a href="<?php echo $paramsToday ?>">Today</a> | <a href="<?php echo $paramsThisMonth ?>">This Month</a> | <a href="<?php echo $paramsNextMonth ?>">Last Month</a>
        </div>
        <!-- DATE SHORTCUT LINKS END HERE -->
		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;">
			<tr class="toprow jalign_left">
				<th>Job Date</th>
				<th>Make</th>
				<th>Model</th>
				<th>Type</th>			
				<th>Power</th>			
				<th>Expiry</th>			
				<th>Reason</th>
				<th>RFC</th>
			</tr>
			<?php
            if(mysql_num_rows($plist)>0){
			while($row = mysql_fetch_array($plist)){ ?>
				<tr class="body_tr jalign_left" data-jobid="<?php echo $row['job_id']; ?>" data-alarmid="<?php echo $row['alarm_id']; ?>">
					<td>
						<?php echo ($row['date']!=NULL)?date('d/m/Y', strtotime($row['date'])):NULL ?>
					</td>
					<td>
						<?php echo $row['make'] ?>
					</td>
					<td>
						<?php echo $row['model'] ?>
					</td>
					<td>
						<?php echo $row['alarm_type'] ?>
					</td>
										
                    <td><?php echo $row['alarm_pwr'] ?></td>
                    <td><?php echo $row['expiry'] ?></td>
                    <td><?php echo $row['reason'] ?></td>
                    <td><?php echo ($row['ts_required_compliance']==1)?'Yes':'No' ?></td>
				</tr>
			<?php
			}
            }else{
                echo "<tr><td align='left' colspan='8'>No Data</td></tr>";
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



</body>
</html>
