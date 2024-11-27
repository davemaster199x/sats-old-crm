<?php

$title = "Reports v2";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$url = 'reports_v2.php';

// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class;

$from = mysql_real_escape_string($_REQUEST['from']);
$from2 = ( $from != '' )?$crm->formatDate($from):'';
$to = mysql_real_escape_string($_REQUEST['to']);
$to2 = ( $to != '' )?$crm->formatDate($to):'';

$sc_id = $_SESSION['USER_DETAILS']['ClassID'];
$sa_id = $_SESSION['USER_DETAILS']['StaffID'];	

function getCrmPages($page_id_arr){
	
	$pages_id_imp = implode(",",$page_id_arr);

	$sql = "
		SELECT * FROM `crm_pages`
		WHERE `crm_page_id` IN({$pages_id_imp})
		ORDER BY `page_name` ASC
	";
	return mysql_query($sql);
	
}

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
.reports_tbl{
	margin-top: 0px; 
	margin-bottom: 13px; 
	margin-right: 25px;
}
.reports_column{
	float: left;
	width: 45%;
}
.reports_col1{
	margin-right: 15px;
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
		
	
			
			
			<?php
			
			// no sort yet
			if($_REQUEST['sort']==""){
				$sort_arrow = 'up';
			}
			
			?>

	
			
			
			<div class="reports_column reports_col1">
			
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
				
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/house_icon.png">
							Property Reports
						</th>
						<?php
						// page id
						$page_id_arr = array(1,2,6,57);
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){
							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							)
							{ ?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page['page_url']; ?>"><?php echo $page['page_name']; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>
					</tr>	
										
				</table>
				
				
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
	
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/employee_icon.png">
							Employee Reports
						</th>					
						<?php
						// page id
						$page_id_arr = array(96,98,86);
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){
							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							)
							{ ?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page['page_url']; ?>"><?php echo $page['page_name']; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>
					</tr>				
						
				</table>
				
				
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
	
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/notes-button.png">
							Job Reports
						</th>	
						<?php
						// page id
						$page_id_arr = array(9,10,43,11,45,47,14,56,62,61,24,27,28);
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){
							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							)
							{ ?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page['page_url']; ?>"><?php echo $page['page_name']; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>
					</tr>			
						
				</table>
				
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
	
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/notes-button.png">
							Operations Report
						</th>
						<?php
						// page id
						$page_id_arr = array(66,48,51,52,53,54,68,59,63,69,117,72);
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){
							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							)
							{ ?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page['page_url']; ?>"><?php echo $page['page_name']; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>	
					</tr>			
						
				</table>
				

			</div>
			
			
			
			
			
			<!-- RIGHT COLUMN -->
			<div class="reports_column reports_col2">
			
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
	
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/dollar_icon.png">
							Accounts Reports
						</th>
						<?php
						// page id
						$page_id_arr = array(131,132,133,134);
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){
							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							)
							{ ?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page['page_url']; ?>"><?php echo $page['page_name']; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>
					</tr>

						
				</table>
				
				
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
	
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/car_icon.png">
							Vehicle Reports
						</th>
						<?php
						// page id
						$page_id_arr = array(127,129,128);
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){
							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							)
							{ ?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page['page_url']; ?>"><?php echo $page['page_name']; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>
					</tr>
						
				</table>
				
				
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
	
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/notes-button.png">
							General Reports
						</th>
						<?php
						// page id
						$page_id_arr = array(41,44,46,49,50);
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){
							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							)
							{ ?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page['page_url']; ?>"><?php echo $page['page_name']; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>					
					</tr>									
						
				</table>
				
				
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
	
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/agency_icon.png">
							Agency Reports
						</th>	
						<?php
						// page id
						$page_id_arr = array(42,78,79,80,81,55,58,64);
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){
							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							)
							{ ?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page['page_url']; ?>"><?php echo $page['page_name']; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>	
					</tr>
						
				</table>
				
				
				<table border=0 cellspacing=0 cellpadding=5 class="tbl-sd reports_tbl">			
	
					<tr class="toprow jalign_left">
						<th>
							<img class="inner_icon" src="images/button_icons/notes-button.png">
							Sales Reports
						</th>
						<?php
						// page id
						$page_id_arr = array(88,60,91,92,93);
						$pages_sql = getCrmPages($page_id_arr);
						while( $page = mysql_fetch_array($pages_sql) ){
							if( 
								$crm->canViewMenu($page['menu'],$sa_id,$sc_id) == true &&
								$crm->canViewPage($page['crm_page_id'],$sa_id,$sc_id) == true 
							)
							{ ?>
								<tr class="body_tr jalign_left">					
									<td><a href="<?php echo $page['page_url']; ?>"><?php echo $page['page_name']; ?></a></td>	
								</tr>
							<?php
							}
						}
						?>		
					</tr>
						
				</table>
				
				
			</div>


		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	// hide empty script
	jQuery(".reports_tbl").each(function(){
		
		var obj = jQuery(this);
		var num_pages = obj.find(".body_tr").length;
		
		console.log("num_pages: "+num_pages);
		if( num_pages <= 0 ){
			obj.hide();
		}
		
	});
	
	
	
});
</script>
</body>
</html>