<?php

$title = "Printing Tracker";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');


// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class;

$country_id = $_SESSION['country_default'];


$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$agency_id = mysql_real_escape_string($_REQUEST['agency_id']);
$phrase = mysql_real_escape_string($_REQUEST['phrase']);


// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'p.`postcode`';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort=".urlencode($sort)."&order_by=".urlencode($order_by)."&date=".urlencode($date)."&phrase=".urlencode($phrase)."&agency_id=".urlencode($agency_id);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


$jparams = array(
	'status' => 'active',	
	'country_id' => $country_id,
	'phrase' => $phrase,
	'sort_list' => array(
		array(
			'order_by' => 'a.`agency_name`',
			'sort' => 'ASC'
		)
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'display_echo' => 0,
	'custom_filter' => " AND a.`agency_id` != 1 "
);
$plist = $crm->getAgency($jparams);

$jparams = array(
	'status' => 'active',	
	'country_id' => $country_id,
	'phrase' => $phrase,
	'return_count' => 1,
	'custom_filter' => " AND a.`agency_id` != 1 "
);
$ptotal = $crm->getAgency($jparams);




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
.checkbox_header_width{
	width: 155px;
}
.highlight_row{
	background-color: #ff06 !important;
}
.greyBgRow{
	background-color:#eeeeee;
}
.phrase_lbl{
	float: left !important; 
	margin: 8px 5px 0 0 !important;
}
</style>





<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title ?>" href="printing_tracker.php"><strong><?php echo $title ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['cleared']==1){
			echo '<div class="success">Printing Tracker markers has been cleared</div>';
		}
		
		
		//echo date('Y-m-d', strtotime("-30 days"));
		
		
		?>
		
		
		<form method="POST" name='example' id='example'>
			<input type='hidden' name='status' value='<?php echo $status ?>'>

			<table border=1 cellpadding=0 cellspacing=0 width="100%">
				<tr class="tbl-view-prop">
				<td>

				<div class="aviw_drop-h aviw_drop-vp" id="view-jobs">

				 
					
					
					<div class='fl-left'>
						<label class="phrase_lbl">Phrase:</label>
						<input type=label name='phrase' value="<?php echo $_REQUEST['phrase']; ?>" class='addinput searchstyle vwjbdtp' style='width: 100px !important;'>
					</div>
					
					
					
					
					<div class='fl-left' style="float: left;">				
						<button type='submit' class='submitbtnImg' id="btn_search" style="margin: 0;">
							<img class="inner_icon" src="images/search.png">
							Search
						</button>
					</div>

					
			
					  
					  
				</td>
				</tr>
			</table>	  
				  
			</form>
			
			
			<?php
			
			// no sort yet
			if($_REQUEST['sort']==""){
				$sort_arrow = 'up';
			}
			
			?>

			<form id="jform" action="print_all_jobs_to_be_printed.php" method="post">
			<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
				
				<tr class="toprow jalign_left">
					<th>Agency</th>					
					<th class="checkbox_header_width">No Statement Needed</th>
					<th class="checkbox_header_width">Sent to VA</th>
					<th class="checkbox_header_width">Completed</th>
				</tr>
				<?php	
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
				?>
					<tr class="body_tr jalign_left <?php echo ( $row['pt_no_statement_needed']==1 || $row['pt_sent_to_va']==1 )?'greyBgRow':''; ?>">
						<td>
							<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
							<a href="<?php echo $ci_link; ?>"><?php echo $row['agency_name']; ?></a>
							<input type="hidden" class="agency_id" value="<?php echo $row['agency_id']; ?>" />
						</td>															
						<td><input type="checkbox" class="pt_field pt_no_statement_needed" data-pt_field_val="pt_no_statement_needed" <?php echo ($row['pt_no_statement_needed']==1)?'checked="checked"':''; ?> /></td>							
						<td><input type="checkbox" class="pt_field pt_sent_to_va" data-pt_field_val="pt_sent_to_va" <?php echo ($row['pt_sent_to_va']==1)?'checked="checked"':''; ?> /></td>							
						<td><input type="checkbox" class="pt_field pt_completed" data-pt_field_val="pt_completed" <?php echo ($row['pt_completed']==1)?'checked="checked"':''; ?> /></td>
					</tr>
						
				<?php
					$i++;
					}
				}else{ ?>
					<td colspan="12" align="left">Empty</td>
				<?php
				}
				?>
					
			</table>
			</form>
			
		
		
		<?php

		// Initiate pagination class
		$jp = new jPagination();
		
		$per_page = $limit;
		$page = ($_GET['page']!="")?$_GET['page']:1;
		$offset = ($_GET['offset']!="")?$_GET['offset']:0;	
		
		echo $jp->display($page,$ptotal,$per_page,$offset,$params);
		
		?>
		

	
		<button type='button' style="float: right; margin-left: 10px;" class='submitbtnImg blue-btn' id="btn_clear_all">
			<img class="inner_icon" src="images/cancel-button.png">
			Clear All
		</button>

		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	function highlightIt(obj){
		
		var pt_no_statement_needed = obj.parents("tr:first").find(".pt_no_statement_needed").prop("checked");
		var pt_sent_to_va = obj.parents("tr:first").find(".pt_sent_to_va").prop("checked");
		
		if( pt_no_statement_needed == true || pt_sent_to_va ){
			return true;
		}else{
			return false;
		}
		
	}
	
	jQuery(".pt_field").change(function(){
		
		var obj = jQuery(this);
		var pt_field = obj.attr("data-pt_field_val")
		var chk_val = ( obj.prop("checked") == true )?1:0;	
		var agency_id =  obj.parents("tr:first").find(".agency_id").val();
		
		if( highlightIt(obj) ){			
			obj.parents("tr:first").addClass("highlight_row");
		}else{
			obj.parents("tr:first").removeClass("highlight_row");
		}
		
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_agency_printing_tracker_fields.php",
			data: { 
				agency_id: agency_id,
				pt_field: pt_field,
				chk_val: chk_val				
			}
		}).done(function( ret ){			
			//window.location = "to_be_printed.php?print_clear=1";
		});
	
	});

	
	
	jQuery("#btn_clear_all").click(function(){
		
		if(confirm('Are you sure you sure you want to continue?')){
	
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_clear_all_agency_printing_tracking_fields.php"
			}).done(function( ret ){			
				window.location = "printing_tracker.php?cleared=1";
			});
			
			
		}		
				
	});
	

	
	
	
});
</script>
</body>
</html>