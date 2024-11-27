<?php

$title = "Accounts Logs";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;


function getAccountsLogs($params){
    $staff_id = $_SESSION['USER_DETAILS']['StaffID'];
    $user_type = $_SESSION['USER_DETAILS']['ClassID'];

    // 2189 - NZ staff
    $vip = array(11,12,58,2025,2056,2070,2124,2156,2178,2189,2190,2239,2259);
	
	$filter_str = '';
	
	// search agency
	if($params['agency']!=""){
		$filter_str .= " AND ael.`agency_id` = {$params['agency']} ";
	}
    
    // search staff
    if($params['staff']!=""){
		$filter_str .= " AND ael.`staff_id` = {$params['staff']} ";
	}
	
	
	// date filter
	if( $params['search_date']['from']!="" && $params['search_date']['to']!="" ){
		$filter_str .= " 
			AND CAST( ael.`eventdate` AS Date )  BETWEEN '{$params['search_date']['from']}' AND '{$params['search_date']['to']}'
		";
	}
	
	if( $params['sort_query'] != '' ){
		$sort_str = "ORDER BY {$params['sort_query']}";
	}
	
	// pagination
	if(is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])){
		$limit = " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
	}
    
    
    /*if (in_array($staff_id, $vip)){
        $temp_str = '';
    }else{
        $temp_str = " 
            AND 
            (ael.`contact_type` = 'Phone Call'
            OR ael.`contact_type` = 'Email'
            OR ael.`contact_type` = 'Other')
        ";						
    }*/
					
	
	$sql = "
		SELECT {$params['sel_query']}
		FROM `agency_event_log` AS ael
        LEFT JOIN `staff_accounts` AS sa ON ael.`staff_id` = sa.`StaffID`
        LEFT JOIN `agency` AS a ON ael.`agency_id` = a.`agency_id`
        WHERE 
        (ael.`contact_type` = 'Phone Call - Accounts'
        OR ael.`contact_type` = 'Email - Accounts'
        OR ael.`contact_type` = 'Other - Accounts')
		{$filter_str}
		{$sort_str}
		{$limit}
	";
	
	if( $params['echo_query'] == 1 ){
		echo $sql;
	}	
	
	return mysql_query($sql);
}



$agency = mysql_real_escape_string($_REQUEST['agency']);
$staff = mysql_real_escape_string($_REQUEST['staff']);
$dateFrom = ($_REQUEST['dateFrom']!="")?jFormatDateToBeDbReady($_REQUEST['dateFrom']):null;
$dateTo = ($_REQUEST['dateTo']!="")?jFormatDateToBeDbReady($_REQUEST['dateTo']):null;

$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;
$this_page = $_SERVER['PHP_SELF'];

$params = "&dateFrom={$dateFrom}&dateTo=".urlencode($dateTo)."&agency=".urlencode($agency)."&staff=".$staff;
$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


// select query
$sel_query = '
   ael.`contact_type`, 
   ael.`eventdate`, 
   ael.`comments`,
   ael.`agency_event_log_id`,
   sa.`FirstName`,
   sa.`LastName`,
   ael.`next_contact`,
   ael.`important`,
   a.`agency_name`
';

// get paginated result
$func_params = array(
	'sel_query' => $sel_query,
    'agency' => $agency,
    'staff' => $staff,
	'search_date' => array(
		'from' => $dateFrom,
		'to' => $dateTo
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'sort_query' => 'ael.`agency_event_log_id` DESC',
	'echo_query' => 0
);

$plist = getAccountsLogs($func_params);


$func_params2 = array(
	'sel_query' => $sel_query,
     'agency' => $agency,
	'search_date' => array(
		'from' => $dateFrom,
		'to' => $dateTo
	),
   
);
$ptotal = mysql_num_rows(getAccountsLogs($func_params2));

?>




<div id="mainContent">

    <div class="sats-middle-cont">
		
	<div class="sats-breadcrumb">
		<ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
	
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		<form method="post" name='account_logs_form' id='account_logs_form' action='/accounts_logs.php' style="margin:0;">
			<input type='hidden' name='status' value='<?php echo $status ?>'>

			<table border=1 cellpadding=0 cellspacing=0 width="100%">
				<tr class="tbl-view-prop">
				<td>

				<div class="aviw_drop-h aviw_drop-vp" id="view-jobs">
  
					<div class="fl-left">
						<label>Date From:</label>
						<input type="text" class="addinput searchstyle datepicker" name="dateFrom" >		
					</div>
				  
					<div class="fl-left">
						<label>Date To:</label>
						<input type="text" class="addinput searchstyle datepicker" name="dateTo" >
					</div>
				  
					<?php
					//if(ifCountryHasState($_SESSION['country_default'])==true){ 
						$func_params = array(
                            'sel_query' => 'DISTINCT ael.`staff_id`, sa.`FirstName`, sa.`LastName`',
                        );
                        $staff_sql = getAccountsLogs($func_params);
					?>
						<div class="fl-left">
							<label>Staff Member</label>
							<select id="staff" name="staff" style="width: 170px;">
								<option value="">ALL</option> 
								<?php
								while($staffList = mysql_fetch_array($staff_sql)){ 
								?>
									<option value="<?php echo $staffList['staff_id']; ?>" <?php echo ($staffList['staff_id']==$staff)?'selected="selected"':''; ?>><?php echo $staffList['FirstName']." ".$staffList['LastName'] ?></option>
								<?php
									
								}
								?>
							 </select>
						</div>
					<?php	
					//}
					?>
					
					
					<?php
					$func_params = array(
                            'sel_query' => 'DISTINCT ael.`agency_id`, a.`agency_name`',
                        );
                        $agen_sql = getAccountsLogs($func_params);
					?>
					
					<div class="fl-left">
						<label>Agency:</label>
						<select id="agency" name="agency" style="width: 170px;">
						<option value="">Any</option> 			
						<?php
						while($agen =  mysql_fetch_array($agen_sql)){ ?>
							<option value="<?php echo $agen['agency_id']; ?>" <?php echo ($agen['agency_id']==$agency) ? 'selected="selected"':''; ?>><?php echo $agen['agency_name']; ?></option>
						<?php	
						} 
						?>
					 </select>
					</div>
					
					
					<div class='fl-left' style="float:left;"><input type='submit' class='submitbtnImg' value='Search'></div>       
										
				</div>

				<!-- duplicated filter here --> 
					  
				</td>
				</tr>
			</table>	  
				  
			</form>
			
		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;text-align:left;">			
			<tr class="toprow jalign_left">
				<th>Date</th>
				<th>Contact Type</th>
				<th>Comments</th>
                <th>Staff Member</th>
                <th>Agency</th>
                </tr>
			
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
				?>
						<tr class="body_tr jalign_left" style="background-color:<?php echo ($i%2!=0)?'#eeeeee':'' ?>">
							<td><?php echo date('d/m/Y',strtotime($row['eventdate'])); ?></td>
							<td><?php echo $row['contact_type']; ?></td>		
							<td><?php echo $row['comments']; ?></td>	
							<td><?php echo "{$row['FirstName']} {$row['LastName']}"; ?></td>
                            <td><?php echo $row['agency_name'] ?></td>
						</tr>
						
				<?php
					$i++;
					}
				}else{ ?>
            <tr>
					<td colspan="100%" align="left">No Data</td>
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
		
	
		
	</div>
</div>

<script src="//code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>
<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	
	// datepicker
	jQuery(".datepicker").datepicker({ dateFormat: "dd/mm/yy" });
	
	
});
</script>
</body>
</html>