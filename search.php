<?php

$title = "Search";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

//include('inc/no_id_properties_functions.php');



// search for property id, job id, workorder and property address
function search($search,$offset,$limit){

	$str = "";
	
	$mobile = str_replace(' ', '', $search);
	
	if(is_numeric($offset) && is_numeric($limit))
	{	
		$str .= " LIMIT {$offset}, {$limit}";			
	}
	
	
	$sql = "
		SELECT 
			*, 
			j.`status` AS jstatus,
			p.`address_1` AS p_address_1,
			p.`address_2` AS p_address_2,
			p.`address_3` AS p_address_3,
			p.`state` AS p_state,
			p.`postcode` AS p_postcode,
			p.`is_sales`
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE a.`country_id` = {$_SESSION['country_default']}
		AND (
			j.`id` = '{$search}' OR
			p.`property_id` =  '{$search}'			 
		)
		AND p.deleted = 0
		{$str}
	";
	return mysql_query($sql);
	

}



$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;
$this_page = $_SERVER['PHP_SELF'];
$search = trim($_REQUEST['search']);


$params = "&submit=1&search={$search}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


if($_REQUEST['submit']){
	
	$plist = search($search,$offset,$limit);
	$ptotal = mysql_num_rows(search($search,'',''));
}




?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
tr td:last-child {
    border-right: 1px solid #cccccc
}
</style>




<div id="mainContent">

   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="Search" href="/search.php"><strong>Search</strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Agency Update Successfull</div>';
		}
		
		
		?>

		
		
		<div style="border: 1px solid #ccc; padding: 9px;" class="aviw_drop-h vpd-tp-h">
        <form method="post">
				<input type="text" name="search" style="width: 85px; margin-left: 5px; margin-right: 12px;" placeholder="Job/Prop ID"  value="<?php echo $search; ?>" />
				<input type="submit" class="submitbtnImg" name="submit" value="Search" style="position: relative; top: 2px;" />
			</form>
		</div>
		
		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">
			<tr class="toprow jalign_left">
				<th>Job ID</th>
				<th>Property ID</th>
				<th>Address</th>
				<th>Work Order #</th>
				<th>Status</th>
				<th>Invoice Number</th>
				<th>Invoice Amount</th>
				<th>Agency</th>
			</tr>
				<?php
				
				
				
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){

					// sales property
					$sales_txt = ( $row['is_sales'] == 1 )?'(Sales)':null;
					
					// get invoice number
				   if(isset($row['tmh_id']))
					{
						$invoice_num = $row['tmh_id'];
					}
					else
					{
						$invoice_num = $row['id'];
					}
					
					// get job price
					$j_sql = mysql_query("
						SELECT *
						FROM `jobs`
						WHERE `id`  = {$row['id']}	
					");
					$j = mysql_fetch_array($j_sql);
					$grand_total = $j['job_price'];
			
					// get alarms
					$a_sql = mysql_query("
						SELECT *
						FROM `alarm`
						WHERE `job_id`  = {$row['id']}	
					");
					while($a = mysql_fetch_array($a_sql))
					{		
						$grand_total += $a['alarm_price'];
					}
					
				?>
						<tr class="body_tr jalign_left">
							<td>
								<span><a href="/view_job_details.php?id=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></span>
							</td>
							<td>
								<span><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo $row['property_id']; ?></a></span>
							</td>
							<td>
								<span><?php echo "{$row['p_address_1']} {$row['p_address_2']} {$row['p_address_3']} {$row['p_state']} {$sales_txt}"; ?></span>
							</td>
							<td>
								<?php echo $row['work_order']; ?>
							</td>
							<td><?php echo $row['jstatus']; ?></td>
							<td>
								#<?php echo $invoice_num; ?>
							</td>
							<td>
								<?php echo number_format($grand_total, 2); ?>
							</td>	
							<td>
								<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
								<span><a href="<?php echo $ci_link ?>"><?php echo $row['agency_name']; ?></a></span>
							</td>
						</tr>
						
				<?php
					}
				}else{ ?>
					<td colspan="100%" align="left">Empty</td>
				<?php
				}
				?>
				<tr>
					<td colspan='100%' class="padding-none">
					 <div class="sats-pg-navigation">
						<div class="sats-inner-pagination">
							<div class="sats-inner-pagination">
							<?php
								if($offset!=0&&$offset!=""){ ?>
								<a href="<?php echo $prev_link; ?>" class="left">&lt;</a>
							<?php
								}
							?>			
							 <div class="sats-pagination-view">Viewing <?php echo (mysql_num_rows($plist)>0)?$offset+1:"0"; ?> to <?php echo ($offset+mysql_num_rows($plist)); ?> of <?php echo $ptotal; ?></div> 
							<?php
								if(($offset+mysql_num_rows($plist))<$ptotal){ ?>
								<a href="<?php echo $next_link; ?>" class="right">&gt;</a>
							<?php
								}
							?>
							</div>
						</div>
					</div>
					</td>
				 </tr>
		</table>	

		
		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){


	function is_numeric(num){
		if(num.match( /^\d+([\.,]\d+)?$/)==null){
			return false
		}
	}

	function validate_email(email){
		var atpos = email.indexOf("@");
		var dotpos = email.lastIndexOf(".");
		if ( atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length ){
		  return false
		}
	}


	jQuery(".btn_edit").click(function(){
	
		jQuery(this).parents("tr:first").find(".btn_update").show();
		jQuery(this).parents("tr:first").find(".btn_edit").hide();
		jQuery(this).parents("tr:first").find(".btn_cancel").show();
		jQuery(this).parents("tr:first").find(".btn_delete").show();
		jQuery(this).parents("tr:first").find(".txt_hid").show();
		jQuery(this).parents("tr:first").find(".txt_lbl").hide();
	
	});	
	
	jQuery(".btn_cancel").click(function(){
		
		jQuery(this).parents("tr:first").find(".btn_update").hide();
		jQuery(this).parents("tr:first").find(".btn_edit").show();
		jQuery(this).parents("tr:first").find(".btn_cancel").hide();
		jQuery(this).parents("tr:first").find(".btn_delete").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".txt_hid").hide();	
		
	});
	
	jQuery(".btn_update").click(function(){
	
		var property_id = jQuery(this).parents("tr:first").find(".property_id").val();
		var agency_id = jQuery(this).parents("tr:first").find(".agency_id").val();
		var error = "";
		
		if(agency_id==""){
			error += "Please Select Agency\n";
		}
		
	
		
		
		if(error!=""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_property_agency_id.php",
				data: { 
					property_id: property_id,
					agency_id: agency_id
				}
			}).done(function( ret ) {
				window.location="/no_id_properties.php?success=1";
			});				
			
		}		
		
	});


	jQuery("#btn_add_new").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#form_accomodation").slideDown();
	},function(){
		jQuery(this).html("Add New");		
		jQuery("#form_accomodation").slideUp();
	});
});
</script>
</body>
</html>