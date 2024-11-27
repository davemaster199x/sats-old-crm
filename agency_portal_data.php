<?php

$title = "Agency Portal Data";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

function getAgencyUserLogins($params){
	
	$filter_str = '';
	
	// search agency
	if($params['agency']!=""){
		$filter_str .= " AND a.`agency_id` = {$params['agency']} ";
	}
	
	// search user
	if($params['user']!=""){
		$filter_str .= " AND aul.`user` = {$params['user']} ";
	}
	
	// date filter
	if( $params['search_date']['from']!="" && $params['search_date']['to']!="" ){
		$filter_str .= " 
			AND CAST( aul.`date_created` AS Date )  BETWEEN '{$params['search_date']['from']}' AND '{$params['search_date']['to']}'
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
		FROM `agency_user_logins` AS aul
		LEFT JOIN `agency_user_accounts` AS aua ON aul.`user` = aua.`agency_user_account_id`
		LEFT JOIN `agency` AS a ON aua.`agency_id` = a.`agency_id`
		WHERE aul.`agency_user_login_id` > 0
		{$filter_str}
		{$sort_str}
		{$limit}
	";
	
	if( $params['echo_query'] == 1 ){
		echo $sql;
	}	
	
	return mysql_query($sql);
}


$today = date('Y-m-d');

$agency = mysql_real_escape_string($_REQUEST['agency']);
$user = mysql_real_escape_string($_REQUEST['user']);
$from = ($_REQUEST['from']!="")?jFormatDateToBeDbReady($_REQUEST['from']):null;
$to = ($_REQUEST['to']!="")?jFormatDateToBeDbReady($_REQUEST['to']):null;


// sort
$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.start_date';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$pagi_limit = 50;

$this_page = $_SERVER['PHP_SELF'];
$pagi_params = "&agency={$agency}&from={$from}&to={$to}";

$next_link = "{$this_page}?offset=".($offset+$pagi_limit).$pagi_params;
$prev_link = "{$this_page}?offset=".($offset-$pagi_limit).$pagi_params;

// select query
$sel_query = '
	aul.`agency_user_login_id`,
	aul.`ip`,
	aul.`date_created`,

	aua.`fname`,
	aua.`lname`,

	a.`agency_id`,
	a.`agency_name`
';

// get paginated result
$func_params = array(
	'sel_query' => $sel_query,
	'agency' => $agency,
	'user' => $user,
	'search_date' => array(
		'from' => $from,
		'to' => $to
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $pagi_limit
	),
	'sort_query' => 'aul.`date_created` DESC',
	'echo_query' => 0
);
$plist = getAgencyUserLogins($func_params);

// get all
$func_params = array(
	'sel_query' => $sel_query,
	'agency' => $agency,
	'user' => $user,
	'search_date' => array(
		'from' => $from,
		'to' => $to
	),
	'echo_query' => 0
);
$ptotal = mysql_num_rows(getAgencyUserLogins($func_params));


?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.yello_mark{
	background-color: #ffff9d;
}
.green_mark{
	background-color: #c2ffa7;
}
.greyBgRow{
	background-color:#eeeeee;
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
		
		
		
		<form method="POST" name='example' id='example' style="margin: 0;">
			<input type='hidden' name='status' value='<?php echo $status ?>'>

			<table border=1 cellpadding=0 cellspacing=0 width="100%">
				<tr class="tbl-view-prop">
				<td>

				<div class="aviw_drop-h aviw_drop-vp" id="view-jobs">


					<?php
					// agency filter
					$func_params = array(
						'sel_query' => 'DISTINCT a.`agency_id`, a.`agency_name`',
						'search_date' => array(
							'from' => $from,
							'to' => $to
						),
						'user' => $user,
						'echo_query' => 0
					);
					$dist_agency_sql = getAgencyUserLogins($func_params);
					?>
					<div class="fl-left">
						<label>Agency:</label>
						<select id="agency" name="agency">
							<option value="">Any</option>
							<?php						
							while( $dist_agency_row =  mysql_fetch_array($dist_agency_sql)){ ?>
								<option value="<?php echo $dist_agency_row['agency_id']; ?>" <?php echo ($dist_agency_row['agency_id']==$agency) ? 'selected="selected"':''; ?>><?php echo $dist_agency_row['agency_name']; ?></option>
							<?php	
							} 
							?>							
						</select>
					</div>
					
					
					<?php
					// User filter
					$func_params = array(
						'sel_query' => 'DISTINCT aua.`agency_user_account_id`, aua.`fname`, aua.`lname`',
						'search_date' => array(
							'from' => $from,
							'to' => $to
						),
						'agency' => $agency,
						'echo_query' => 0
					);
					$dist_aua_sql = getAgencyUserLogins($func_params);
					?>
					<div class="fl-left">
						<label>User:</label>
						<select id="user" name="user">
							<option value="">Any</option>
							<?php						
							while( $dist_aua_row =  mysql_fetch_array($dist_aua_sql)){ ?>
								<option value="<?php echo $dist_aua_row['agency_user_account_id']; ?>" <?php echo ($dist_aua_row['agency_user_account_id']==$user) ? 'selected="selected"':''; ?>><?php echo "{$dist_aua_row['fname']} {$dist_aua_row['lname']}"; ?></option>
							<?php	
							} 
							?>							
						</select>
					</div>

					
					<div class='fl-left'>
						<label>From:</label>
						<input type="text" name='from' value='<?php echo ($from)?date('d/m/Y',strtotime($from)):''; ?>' class='addinput searchstyle datepicker'>		
					</div>
					
					<div class='fl-left'>
						<label>To:</label>
						<input type="text" name='to' value='<?php echo ($to)?date('d/m/Y',strtotime($to)):''; ?>' class='addinput searchstyle datepicker'>		
					</div>

					
					
					<div class='fl-left' style="float:left;">
						<input type='submit' class='submitbtnImg' value='Search' />
					</div>

					  
				</td>
				</tr>
			</table>	  
				  
		</form>
		

		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
			<tr class="toprow jalign_left">				
				<th>Agency</th>	
				<th>User</a>
				<th>Date</th>
				<th>Logged in</th>
				<th>IP</th>										
			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){	
				?>
					<tr class="body_tr jalign_left <?php echo ($i%2==0)?'':'greyBgRow'; ?>">					
						<td><?php echo $row['agency_name']; ?></td>
						<td><?php echo "{$row['fname']} {$row['lname']}"; ?></td>
						<td><?php echo ( $crm->isDateNotEmpty($row['date_created']) )?date('d/m/Y',strtotime($row['date_created'])):''; ?></td>
						<td><?php echo ( $crm->isDateNotEmpty($row['date_created']) )?date('H:i',strtotime($row['date_created'])):''; ?></td>													
						<td><?php echo $row['ip']; ?></td>						
					</tr>
						
				<?php
					$i++;
					}
				}else{ ?>
					<td colspan="4" align="left">Empty</td>
				<?php
				}
				?>
				
		</table>	
		
		<?php

		// Initiate pagination class
		$jp = new jPagination();
		
		$per_page = $pagi_limit;
		$page = ($_GET['page']!="")?$_GET['page']:1;
		$offset = ($_GET['offset']!="")?$_GET['offset']:0;	
		
		echo $jp->display($page,$ptotal,$per_page,$offset,$pagi_params);
		
		?>

		
	</div>
</div>

<br class="clearfloat" />

</body>
</html>