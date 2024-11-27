<?php
$title = "Subscription Billing";

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
$subscription = mysql_real_escape_string($_REQUEST['subscription']);





// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort={$sort}&order_by={$order_by}&search={$search}&subscription={$subscription}";


// list
$custom_select = '
	a.`agency_id`,
	a.`agency_name`,
	a.`allow_upfront_billing`,
	a.`subscription_notes`,
	a.`subscription_notes_update_ts`,
	a.`subscription_notes_update_by`,

	snub.`FirstName` AS snub_fname,
	snub.`LastName` AS snub_lname
';
$list_params = array(
	'custom_select' => $custom_select,
    'status' => 'active',
    	
    'phrase' => $search,
    'subscription' => $subscription,

	'country_id' => $country_id,
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'sort_list' => array( 
		array(
			'order_by' => 'a.`allow_upfront_billing`',
			'sort' => 'DESC'
		)
    ),
	'state' => 'QLD',
	'join_table' => 'subscription_notes_update_by',

	'display_echo' => 0
);
$a_sql = $crm->getAgency($list_params);

$custom_select = '
	COUNT(a.`agency_id`) AS acount
';
$list_params = array(
	'custom_select' => $custom_select,
    'status' => 'active',
    	
    'phrase' => $search,
    'subscription' => $subscription,

    'country_id' => $country_id,
	'state' => 'QLD',
	'join_table' => 'subscription_notes_update_by',
);
$ptotal_sql = $crm->getAgency($list_params);
$arow = mysql_fetch_array($ptotal_sql);
$ptotal = $arow['acount'];





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

            <div class="fl-left">
				<label style="margin-right: 9px;">Subscription:</label>
				<select name="subscription">											
                    <option value="">ALL</option> 
                    <option value="1" <?php echo ( $subscription == 1 )?'selected="selected"':null; ?>>Yes</option> 
                    <option value="0" <?php echo ( is_numeric($subscription) && $subscription == 0 )?'selected="selected"':null; ?>>No</option> 
                </select>
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
				<th>Subscription</th>
                <th>Notes</th>
				<th>Timestamp</th>
				<th>Who</th>
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
						<td><?php echo ( $a['allow_upfront_billing'] == 1 )?'<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>'; ?></td>
                        <td>
                            <input type="text" name="subscription_notes" class="addinput subscription_notes" value="<?php echo $a['subscription_notes']; ?>" />	
                            <input type="hidden" name="agency_id" class="addinput agency_id" value="<?php echo $a['agency_id']; ?>" />
                            <img src="/images/check_icon2.png" class="img_check" style="" />
                        </td>
						<td><?php echo $crm->isDateNotEmpty($a['subscription_notes_update_ts'])?date("d/m/Y H:i",strtotime($a['subscription_notes_update_ts'])):''; ?></td>
						<td><?php echo $crm->formatStaffName($a['snub_fname'],$a['snub_lname']); ?></td>
					</tr>
				<?php
				$i++;
				}
			}else{ ?>
				<tr><td colspan="3" align="left">Empty</td></tr>
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
<style>
.addinput.subscription_notes {    
    width: 85%;
}
.img_check{
    display:none;
    margin-left: 15px;
}
</style>
<script>
jQuery(document).ready(function(){

    jQuery(".subscription_notes").change(function(){

        var obj = jQuery(this);
        var subscription_notes = obj.val();
        var agency_id = obj.parents("tr:first").find(".agency_id").val();

        jQuery.ajax({
            type: "POST",
            url: "ajax_update_agency_subscription_notes.php",
            data: { 
                agency_id: agency_id,
                subscription_notes: subscription_notes                
            }
        }).done(function( ret ){
            
            obj.parents("tr:first").find(".img_check").fadeIn();
			location.reload();
            
        });

    });

    	

});
</script>
</body>
</html>
