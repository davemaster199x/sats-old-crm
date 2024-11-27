<?php

$title = "To Be Printed";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');


// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class;


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
	'to_be_printed' => 1,	
	'date' => $date,
	'agency_id' => $agency_id,
	'phrase' => $phrase,
	'sort_list' => array(
		array(
			'order_by' => 'j.`date`',
			'sort' => 'ASC'
		)
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'display_echo' => 0,
	'remove_deleted_filter' => 1
);
$plist = $crm->getJobsData($jparams);

$jparams = array(
	'to_be_printed' => 1,	
	'date' => $date,
	'agency_id' => $agency_id,
	'phrase' => $phrase,
	'return_count' => 1,
	'remove_deleted_filter' => 1
);
$ptotal = $crm->getJobsData($jparams);




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
</style>




<div id="mainContent">


   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title ?>" href="to_be_printed.php"><strong><?php echo $title ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['print_clear']==1){
			echo '<div class="success">Printed jobs has been cleared</div>';
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
						<label>Date:</label><input type=label name='date' value='<?php echo $_REQUEST['date']; ?>' class='addinput searchstyle datepicker vwjbdtp' style="width:85px!important;">		
					</div>
					
					<div class='fl-left'>
						<label>Agency:</label>
						<select name="agency_id">
						<option value=''>--- Select ---</option>
						<?php
						$jparams = array(
							'to_be_printed' => 1,
							'distinct' => 'a.`agency_id`',
							'remove_deleted_filter' => 1
						);
						$am_sql = $crm->getJobsData($jparams);										
						while( $am = mysql_fetch_array($am_sql) ){ ?>
							<option value="<?php echo $am['agency_id']; ?>" <?php echo ($am['agency_id']==$agency_id)?'selected="selected"':''; ?>><?php echo $am['agency_name']; ?></option>
						<?php	
						}
						
						?>
						</select>
					</div>
					
					
					<div class='fl-left'>
						<label>Phrase:</label>
						<input type=label name='phrase' value="<?php echo $_REQUEST['phrase']; ?>" class='addinput searchstyle vwjbdtp' style='width: 100px !important;'>
					</div>
					
					
					
					
					
					<div class='fl-left'>				
						<button type='submit' class='submitbtnImg' id="btn_search">
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
					<th>Date</th>
					<th>Agency</th>
					<th>Invoice Number</th>
					<th>Address</th>
					<th>Invoice Amount</th>
					<th>Printed</th>
					<th><input type="checkbox" id="check_all" /></th>	
				</tr>
				<?php	
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
					// grey alternation color
					$row_color = ($i%2==0)?"style='background-color:#eeeeee;'":"";

					
				?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							<td><?php echo ($crm->isDateNotEmpty($row['jdate'])==true)?$crm->formatDate($row['jdate'],'d/m/Y'):''; ?></td>						
							<td>
							<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['a_id']}"); ?>
							<a href="<?php echo $ci_link; ?>"><?php echo $row['agency_name']; ?></a></td>			
							<td><a href="/view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $crm->getInvoiceNumber($row['jid']); ?></a></td>
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							<td>$<?php echo number_format($crm->getInvoiceTotal($row['jid']),2); ?></td>							
							<td><?php echo ( $row['is_printed']==1 )?"<span class='green'>Yes</span>":"<span class='red'>No</span>"; ?></td>
							<td><input type="checkbox" name="job_id[]" class="job_id" data-is_printed="<?php echo $row['is_printed']; ?>" value="<?php echo $row['jid']; ?>" /></td>							
					
						
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
		

		<button type='button' style="float: right; margin-left: 10px;" class='submitbtnImg blue-btn' id="btn_print">
			<img class="inner_icon" src="images/pdf_white.png">
			PRINT
		</button>
		<button type='button' style="float: right; margin-left: 10px;" class='submitbtnImg blue-btn' id="btn_clear_all_printed">
			<img class="inner_icon" src="images/cancel-button.png">
			Clear All Printed
		</button>

		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	
	// check all only printed = no
	jQuery("#check_all").change(function(){
		
		var checked_state = jQuery(this).prop("checked");
		
		if( checked_state == true ){
			
			jQuery(".job_id").each(function(){
			
				var is_printed = parseInt(jQuery(this).attr("data-is_printed"));
				
				if( is_printed != 1 ){ // only check printed = no
					jQuery(this).prop("checked",true);
				}
				
			});
			
		}else{
			
			jQuery(".job_id").prop("checked",false);
			
		}
		
		
	});
	
	
	
	
	
	
	jQuery("#btn_print").click(function(){
		
		var job_arr = [];
		jQuery(".job_id:checked").each(function(){
			
			var job_id = jQuery(this).val();
			if( job_id != '' ){
				job_arr.push(job_id);
			}			
			
		});
		
		if( job_arr.length > 0 ){
			
			if( confirm("Are you sure you want to continue?") ){
				jQuery("#jform").submit();
			}						
			
		}else{
			console.log("Please select job to print");
		}
		
	});
	
	
	
	jQuery("#btn_clear_all_printed").click(function(){
		
		if(confirm('Are you sure you sure you want to continue?')){
	
			jQuery.ajax({
				type: "POST",
				url: "ajax_clear_all_printed_jobs.php"
			}).done(function( ret ){			
				window.location = "to_be_printed.php?print_clear=1";
			});
			
		}		
				
	});
	

	
	
	
});
</script>
</body>
</html>