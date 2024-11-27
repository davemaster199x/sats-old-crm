<?

$title = "Agency Logins";
$onload = 1;
//$onload_txt = "zxcSelectSort('agency',1)";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

function get_agency_accounts($get_all,$offset,$limit,$search){
		
	if($search!=""){
		$str .= "AND `agency_name` LIKE '%{$search}%' ";
	}

	if($get_all==1){
		$str .= "";
	}else{
		$str .= "ORDER BY agency_name ASC LIMIT {$offset}, {$limit}";
	}
	
	$sql = "
		SELECT agency_id, agency_name, initial_setup_done
		FROM agency 
		WHERE status = 'active' 
		AND `country_id` = {$_SESSION['country_default']}
		{$str}
		";
	
	return mysql_query($sql);

}


function getAgencyAdmin($params){
	
	$filter_str = '';
	
	// filter
	if($params['agency']!=""){
		$filter_str .= " AND a.`agency_id` = {$params['agency']} ";
	}
	
	if($params['user_type']!=""){
		$filter_str .= " AND aua.`user_type` = {$params['user_type']} ";
	}
	
	if($params['active']!=""){
		$filter_str .= " AND aua.`active` = {$params['active']} ";
	}
	
	// date filter
	if( $params['search_date']['from']!="" && $params['search_date']['to']!="" ){
		$filter_str .= " 
			AND CAST( aua.`date_created` AS Date )  BETWEEN '{$params['search_date']['from']}' AND '{$params['search_date']['to']}'
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
		FROM `agency_user_accounts` AS aua 
		LEFT JOIN `agency` AS a ON aua.`agency_id` = a.`agency_id`
		WHERE aua.`agency_user_account_id` > 0
		{$filter_str}
		{$sort_str}
		{$limit}
	";
	
	if( $params['echo_query'] == 1 ){
		echo $sql;
	}	
	
	return mysql_query($sql);
}



function getAgencySite(){
	
	$url = $_SERVER['SERVER_NAME'];

	if($_SESSION['country_default']==1){ // AU

		if( strpos($url,"crmdev")===false ){ // live
			$agency_site = "//agency.sats.com.au{$url_params}";
		}else{ // dev
			$agency_site = "//agencydev.sats.com.au{$url_params}";
		}
		
	}else if($_SESSION['country_default']==2){ // NZ
		
		if( strpos($url,"crmdev")===false ){ // live
			$agency_site = "//agency.sats.co.nz{$url_params}";
		}else{ // dev
			$agency_site = "//agencydev.sats.co.nz{$url_params}";
		}
		
		
	}
	
	return $agency_site;
	
}



$search = $_REQUEST['search'];
	
// pagination script
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;
$this_page = $_SERVER['PHP_SELF'];
$next_link = "{$this_page}?offset=".($offset+$limit)."&search={$search}";
$prev_link = "{$this_page}?offset=".($offset-$limit)."&search={$search}";


$result = get_agency_accounts(0,$offset,$limit,$search);
$ptotal = mysql_num_rows(get_agency_accounts(1,'','',$search));

?>


 
  <div id="mainContent">
  
  <div class="sats-middle-cont">
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Manage Agency Logins" href="/user_manager.php"><strong>Manage Agency Logins</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

    <h1 class="heading" style="display: none;"><?php echo ucwords($tab); ?> Agencies</h1>







<table cellspacing="0" cellpadding="0">
	<tbody>
		<tr class="tbl-view-prop">
			<td>
			
				<form>

				<div class="ap-vw-reg aviw_drop-h">
				
					<div class="fl-left" style="float: left;">
						<label>Search Agency Name:</label>
						<input type="text" value="" name="search" class="addinput searchstyle">
					</div>
					<div class="fl-left pull-left">
						<input type="submit" class="submitbtnImg usrsrch" value="Search" name="btn_search">
					</div>
					
				</div>

				</form>

				<table border=0 cellspacing=1 cellpadding=5 width=100% class="table-left tbl-fr-red non-bold">
				
					<tr bgcolor="#b4151b">
					<th><b>Agency</b></th>
					<th><b>Email</b></th>
					<th><b>Login</b></th>
					<th><b>Last Changed</b></th>

					<?php
					while ($agency_row = mysql_fetch_array($result)){

					
					// select query
					$sel_query = '					
						aua.`email`,
						aua.`password`,
						aua.`reset_password_code_ts`,
                        aua.`password_changed_ts`,

						a.`agency_id`,
						a.`agency_name`
					';

					// get paginated result
					$admin_params = array(
						'sel_query' => $sel_query,
						'agency' => $agency_row['agency_id'],
						'user_type' => 1,
						'active' => 1,
						'sort_query' => 'aua.`agency_user_account_id` DESC',
						'paginate' => array(
							'offset' => 0,
							'limit' => 1
						),
						'echo_query' => 0
					);
					$admin_sql = getAgencyAdmin($admin_params);
					$admin_row = mysql_fetch_array($admin_sql);
					?>
					
					<tr style="background-color:">
						<td>
						<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$agency_row['agency_id']}"); ?>
						<a href="<?php echo $ci_link; ?>"><?php echo $agency_row['agency_name']; ?></a></td>
						<td><?php echo $admin_row['email']; ?></td>
						<td>
							<?php
							if( $agency_row['initial_setup_done'] == 1 ){ ?>
							
								<a href="
								<?php echo getAgencySite(); ?>
								?user=<?php echo $admin_row['email']; ?>
								&agency_id=<?php echo $agency_row['agency_id']; ?>
								&pass=<?php echo $admin_row['password'] ?>&crm_login=1
								"" target="__blank">
									<img src='/images/agency_login.png' class="login_icon" />
								</a>
							
							<?php	
							}else{ 
								echo "Set up not done";
							}
							?>							
						</td>
						<td><?php echo ( $crm->isDateNotEmpty($admin_row['password_changed_ts']) )?date('d/m/Y H:i',strtotime($admin_row['password_changed_ts'])):''; ?></td>
					</tr> 
					
					<?php
					}
					?>

				</table>

			</td>
		</tr>
	</tbody>
</table>



 </div>

</div>

<?php

// Initiate pagination class
$jp = new jPagination();

$per_page = $limit;
$page = ($_GET['page']!="")?$_GET['page']:1;
$offset = ($_GET['offset']!="")?$_GET['offset']:0;	

echo $jp->display($page,$ptotal,$per_page,$offset,$params);

?>

	<br class="clearfloat" />

</body>

</html>

