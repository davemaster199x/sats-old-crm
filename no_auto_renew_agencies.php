<?php
$title = "No Auto Renew Agencies";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;
//$crm->displaySession();

$current_page = $_SERVER['PHP_SELF'];

$user_type = $_SESSION['USER_DETAILS']['ClassID'];
$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$loggedin_staff_name = "{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}";
$country_id = $_SESSION['country_default'];


$search = mysql_real_escape_string($_REQUEST['search']);
$mm_id = mysql_real_escape_string($_REQUEST['software']);





// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort={$sort}&order_by={$order_by}&search={$search}&software={$mm_id}";


// list
$list_params = array(
	'status' => 'active',	
	'phrase' => $search,
	'country_id' => $country_id,
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
	'echo_query' => 1,
	'custom_filter' => ' AND a.`auto_renew` = 0 '
);
$a_sql = $crm->getAgency($list_params);


$list_params = array(
	'status' => 'active',	
	'phrase' => $search,
	'country_id' => $country_id,
	'custom_filter' => ' AND a.`auto_renew` = 0 '
);
$ptotal = mysql_num_rows($crm->getAgency($list_params));





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
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong><?php echo $title; ?></strong></a></li>				
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

			
			
			<div class="fl-left" style="float:left; margin-left: 10px;">
				<button type="submit" class="submitbtnImg">
					<img class="inner_icon" src="images/button_icons/search-button.png">
					Search
				</button>			
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
				<th>Contact Name</th>
				<th>Contact Number</th>
			</tr>
			<?php				
			if( mysql_num_rows($a_sql)>0 ){
				$i = 0;
				while($a = mysql_fetch_array($a_sql)){ 
				?>
					<tr class="body_tr jalign_left"  <?php echo ($i%2==0)?'':'style="background-color:#eeeeee"'; ?>>
						<td>
						<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$a['agency_id']}"); ?>
						<a href="<?php echo $ci_link; ?>"><?php echo $a['agency_name']; ?></a></td>						
						<td><?php echo $a['tenant_details_contact_name']; ?></td>
						<td><?php echo $a['tenant_details_contact_phone']; ?></td>
					</tr>
				<?php
				$i++;
				}
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

</body>
</html>
