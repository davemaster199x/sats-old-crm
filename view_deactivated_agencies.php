<?php
$title = "View Deactivated Agencies";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

include ('inc/agency_class.php');
// instantiate class
$agency = new Agency_Class();

// Initiate job class
$jc = new Job_Class();	

$agency_status = 'deactivated';

$search_flag = mysql_real_escape_string($_REQUEST['search_flag']);

# Process Deletion
if(isset($_GET['del_id']) && is_numeric($_GET['del_id']))
{
	
	$query = "DELETE FROM agency WHERE status = '{$agency_status}' AND agency_id = '" . $_GET['del_id'] . "' LIMIT 1";
	mysql_query($query) or die(mysql_error());
	$message = "<div class='class'>Agency deleted successfully</div>";

}

# Process Activation
if( isset($_GET['act_id']) && is_numeric($_GET['act_id']) && $_GET['activate']==1 )
{
	// send email
	// get agency
	$agen_sql = mysql_query("
		SELECT *, 
			fg.`name` AS fg_name, 
			ar.`agency_region_name` AS ar_name,
			c.`country` AS c_name
		FROM `agency` AS a
		LEFT JOIN `franchise_groups` AS fg ON a.`franchise_groups_id` = fg.`franchise_groups_id`
		LEFT JOIN `agency_regions` AS ar ON a.`agency_region_id` = ar.`agency_region_id`
		LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
		WHERE a.`agency_id` = {$_GET['act_id']}
		AND a.`country_id` = {$_SESSION['country_default']}
	");
	$agen = mysql_fetch_array($agen_sql);
	
	// recipients
	//$to  = 'vaultdweller123@gmail.com';
	//$to  = 'danielk@sats.com.au';
	$to  = 'accounts@sats.com.au';
	// subject
	$subject = 'Agency Now Active';
	
	
	$agency_name = $agen['agency_name'];
	$street_number = $agen['address_1'];
	$street_name = $agen['address_2'];
	$suburb = $agen['address_3'];
	$phone = $agen['phone'];
	$state = $agen['state'];
	$postcode = $agen['postcode'];
	$region = $agen['postcode_region_id'];
	$totprop = $agen['tot_properties'];
	$ac_fname = $agen['contact_first_name'];
	$ac_lname = $agen['contact_last_name'];
	$ac_phone = $agen['contact_phone'];
	$ac_email = $agen['contact_email'];
	$agency_emails = $agen['agency_emails'];
	$account_emails = $agen['account_emails'];
	$send_emails = $agen['send_emails'];
	$combined_invoice = $agen['send_combined_invoice'];
	$send_entry = $agen['send_entry_notice'];
	$workorder_required = $agen['require_work_order'];
	$allow_indiv_pm = $agen['allow_indiv_pm'];
	$auto_renew = $agen['agency_name'];
	$key_allowed = $agen['street_number'];
	$key_email_req = $agen['agency_name'];
	$salesrep = $agen['salesrep'];
	$phone_call_req = $agen['phone_call_req'];
	$legal_name = $agen['legal_name'];
	$abn = $agen['abn'];
	$acc_name = $agen['accounts_name'];
	$acc_phone = $agen['accounts_phone'];
	$allow_dk = $agen['allow_dk'];
	$allow_en = $agen['allow_en'];

	$agency->send_mail(
		$agency_name,
		$street_number,
		$street_name,
		$suburb,
		$phone,
		$state,
		$postcode,
		$region,
		$totprop,
		$ac_fname,
		$ac_lname,
		$ac_phone,
		$ac_email,
		$agency_emails,
		$account_emails,
		$send_emails,
		$combined_invoice,
		$send_entry,
		$workorder_required,
		$allow_indiv_pm,
		$auto_renew,
		$key_allowed,
		$key_email_req,
		$salesrep,
		$phone_call_req,
		$legal_name,
		$abn,
		$acc_name,
		$acc_phone,
		$allow_dk,
		$allow_en
	);
	
	
	$query = "
	UPDATE agency 
	SET 
		status = 'active', 
		`send_emails` = 1,
		`send_combined_invoice` = 1,
		`send_entry_notice` = 0,
		`require_work_order` = 0,
		`allow_indiv_pm` = 1,
		`auto_renew` = 1,
		`key_allowed` = 1,
		`key_email_req` = 0,
		`phone_call_req` = 1,
		`allow_dk` = 1,
		`allow_en` = -1,
		`new_job_email_to_agent` = 0
	WHERE agency_id = '" . $_GET['act_id'] . "' 
	LIMIT 1";
	mysql_query($query) or die(mysql_error());
	$message = "<div class='success'>Agency Status is now Active</div>";
	
	
}
$state = '';
$region = '';
$start = 0;
if($_REQUEST){
	$state = $_REQUEST['searchstate'];
	//$region = $_REQUEST['postcode_region_id'];
	$start = (intval($_REQUEST['start']) > 0 ? intval($_REQUEST['start']) : 0);
}


if($_POST['postcode_region_id']){
	$region = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$region = $_GET['postcode_region_id'];
	//echo $filterregion;
}




	

	
function get_deactivated_agency_list($start="",$limit="",$state="",$region="",$sales_rep="",$phrase="",$sort="",$order_by="",$agency_using_id){

	$appendquery = '';
	
	if($state != ''){
		$appendquery .= " AND (a.state='{$state}') ";
	}

	/*
	if($region != ''){
		$appendquery .= " AND (a.agency_region_id='{$region}') ";
	}	
	*/
	if($region!=""){
		$appendquery .= " AND a.`postcode` IN ( {$region} ) ";
	}
	
	if($sales_rep != ''){
		$appendquery .= " AND a.`salesrep` = {$sales_rep}";
	}

	if($phrase != ''){
		$appendquery .= " AND CONCAT_WS(' ',LOWER(a.`agency_name`),LOWER(a.`address_3`)) LIKE '%{$phrase}%'";
	}
	
	if($agency_using_id != ''){
		$appendquery .= " AND a.`agency_using_id` = {$agency_using_id}";
	}
	
	if($sort !== ''){
		$sort_str = " ORDER BY {$sort} {$order_by}";
	}
	
	$limit_str = '';
	if($start!==""&&$limit!==""){
		$limit_str = " LIMIT {$start}, {$limit}";
	}

	$sql = "
			SELECT *, a.agency_name, a.address_1, a.address_2, a.address_3, a.state, a.postcode, a.status, a.agency_id, DATE_FORMAT(MAX(c.eventdate),'%d/%m/%Y') as logdate, a.tot_properties, c.`next_contact` 
			FROM agency AS a
			LEFT JOIN `agency_event_log` AS c ON a.agency_id = c.agency_id 
			LEFT JOIN `postcode_regions` AS pr ON a.`postcode_region_id` = pr.`postcode_region_id`
			WHERE  (a.status='deactivated') ".$appendquery." AND a.`country_id` = {$_SESSION['country_default']} GROUP BY a.agency_id {$sort_str} {$limit_str}
	";
	return mysql_query($sql);

}


$odd=0;

$sales_rep = $_REQUEST['sales_rep'];
$phrase = $_REQUEST['phrase'];
$agency_using = $_REQUEST['agency_using'];

// phrase 


// header sort parameters
$sort = $_REQUEST['sort'];
$order_by = $_REQUEST['order_by'];

$sort = ($sort)?$sort:'a.`agency_name`';
$order_by = ($order_by)?$order_by:'ASC';

// pagination script
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;
$this_page = $_SERVER['PHP_SELF'];
$params = "&searchstate={$state}&postcode_region_id={$region}&phrase={$phrase}&sales_rep={$sales_rep}&agency_using={$agency_using}&search_flag=1";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


	
$result = get_deactivated_agency_list($offset,$limit,$state,$region,$sales_rep,$phrase,$sort,$order_by,$agency_using);
$ptotal = mysql_num_rows(get_deactivated_agency_list('','',$state,$region,$sales_rep,$phrase,$sort,$order_by,$agency_using));
	



$totalFound = getFoundRows();
$pagination_tabs = ceil($totalFound / PER_PAGE);

$start_display = $start + 1;

	
?>

  <div id="mainContent">
  
  <div class="sats-middle-cont">
  
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

	
	<?php
	if( $_REQUEST['agency_deleted']==1 ){ ?>	
		<div class="success">Agency Deleted</div>
	<?php
	}
	?>
   

<? if(isset($message)): ?>
<p><?=$message;?></p>
<? endif; ?>

<table cellspacing="0" cellpadding="0">
        <tbody><tr class="tbl-view-prop">
          <td>
          <form action='<?=URL;?>view_deactivated_agencies.php' method='post' name='search' id='search' class="vw-trg-agnc" style="margin:0px;">
          	<div class="ap-vw-reg aviw_drop-h" style="height: 60px;">
			
			<?php
			if(ifCountryHasState($_SESSION['country_default'])==true){ ?>
				<div class="fl-left">
					<label><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?>:</label>
					<select name="searchstate">
							<option <?php echo $state == ''? 'selected="selected"': '';?> value=''></option>
							<option <?php echo $state == 'NSW'? 'selected="selected"': '';?> value='NSW'>NSW</option>
							<option <?php echo $state == 'VIC'? 'selected="selected"': '';?> value='VIC'>VIC</option>
							<option <?php echo $state == 'QLD'? 'selected="selected"': '';?> value='QLD'>QLD</option>
							<option <?php echo $state == 'ACT'? 'selected="selected"': '';?> value='ACT'>ACT</option>
							<option <?php echo $state == 'TAS'? 'selected="selected"': '';?> value='TAS'>TAS</option>
							<option <?php echo $state == 'SA'? 'selected="selected"': '';?> value='SA'>SA</option>
							<option <?php echo $state == 'WA'? 'selected="selected"': '';?> value='WA'>WA</option>
							<option <?php echo $state == 'NT'? 'selected="selected"': '';?> value='NT'>NT</option>
							<?php //foreach($allstates as $states){?>
								<!--<option value="<?//=$states['name'];?>" ><?//=$states['name'];?></option>-->
							<?php //}?>
							
						</select>
				  </div>
			<?php	
			}
			?>
			
			  
			  
			  <?php
			  $sr_sql = getAgencySalesRep($agency_status);			  
			  ?>
			  <div class="fl-left">
				<label>Sales Rep:</label>
				<select name="sales_rep">
					<option value="">----</option>
					<?php
					while($sr = mysql_fetch_array($sr_sql)){ ?>
						<option value="<?php echo $sr['salesrep'] ?>"><?php echo $sr['FirstName'] ?> <?php echo $sr['LastName'] ?></option>
					<?php
					}
					?>
				</select>
			  </div>
			
			
			<div class="fl-left">
				<label><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?>:</label>
				<input type="text" readonly="readonly" name='region_ms' id='region_ms' class='addinput searchstyle vwjbdtp' style='width: 100px !important;' />
				<style>
					#region_dp_div{
						width:auto; 
						border-radius: 5px;
						padding: 7px;
						position: absolute;
						top: 112px;
						background: #ffffff;
						border: 1px solid #cccccc;
						display: none;
						z-index: 99999;
					}
					.region_dp_header{
						background: #b4151b none repeat scroll 0 0;
						border-radius: 10px;
						color: #ffffff;
						padding: 6px;
						text-align: left;
					}
					#region_dp_div ul{
						list-style: outside none none;	
						padding: 0;
						margin: 0;
						text-align: left !important;
					}	
					.reg_db_main_reg{
						color: #b4151b;
						cursor: pointer;
						font-weight: bold;
						text-align: center;
					}
					#region_dp_div input{
						width:auto;
						float:none;
					}
					.region_wrapper{
						border-bottom: 1px solid;
						color: #b4151b;
					}
					</style>
					<div id="region_dp_div">
					<div class="region_dp_header">
						<ul>
						<?php
						
					
						
						// get state
						$jstate_sql = mysql_query("
							SELECT DISTINCT (
								`state`
							)
							FROM `agency`
							WHERE `status` =  '{$agency_status}'
							AND `country_id` = {$_SESSION['country_default']}
							AND `state` != ''
							ORDER BY `state`
						");
						while($jstate =  mysql_fetch_array($jstate_sql)){ 
						
						// get state regions
						$main_reg_pc = [];
						$pc_temp = '';
						$pc_str = '';
						$temp_sql = mysql_query("
							SELECT * 	
							FROM  `regions`
							WHERE `region_state` = '{$jstate['state']}'
							AND `country_id` = {$_SESSION['country_default']}
							AND `status` = 1
						");
						while( $temp = mysql_fetch_array($temp_sql) ){
							$pc_str = jGetPostcodeViaRegion($temp['regions_id']);
							if( $pc_str != '' ){
								$pc_temp = str_replace(',,',',',jGetPostcodeViaRegion($temp['regions_id'])); // sanitize
								$main_reg_pc[] = $pc_temp;
							}
						}
						$main_region_postcodes = implode(",",$main_reg_pc);
						$region_count = getAgencyFilterRegionCount($_SESSION['country_default'],$main_region_postcodes,$agency_status);
							if( $region_count>0 ){
							?>
								<li>
									<input type="checkbox" name="state_ms[]" class="state_ms" value="<?php echo $jstate['state']; ?>" /> <span><?php echo $jstate['state']; ?> (<?php echo $region_count; ?>)</span>
									<input type="hidden" value="<?php echo $main_region_postcodes; ?>" />
								</li>
							<?php	
							}
						} 
						?>
						</ul>
					</div>
					<div class="region_dp_body">								
					</div>
					</div>
					<script>
					jQuery(document).ready(function(){
						
						// clicking out the container script :)
						jQuery(document).mouseup(function (e)
						{
							var container = jQuery("#region_dp_div");

							if (!container.is(e.target) // if the target of the click isn't the container...
								&& container.has(e.target).length === 0) // ... nor a descendant of the container
							{
								container.hide();
							}
						});
						
						jQuery("#region_ms").click(function(){

						  jQuery("#region_dp_div").show();

						});
						
						/*
						jQuery(document).on("click",".reg_db_main_reg",function(){
							
							var sub_reg_vis = jQuery(this).parents("li:first").find(".reg_db_sub_reg").css("display");
							if(sub_reg_vis=='block'){
								jQuery(this).parents("li:first").find(".reg_db_sub_reg").hide();
							}else{
								jQuery(this).parents("li:first").find(".reg_db_sub_reg").show();
							}
						
						});
						*/
						
					});
					</script>
			</div>
  
  
			  <div class="fl-left">
				<label>Phrase:</label>
				<input type="text" name="phrase" class="addinput phrase" />
			  </div>
			  
			  
			  <div class="fl-left">
				<label>Using:</label>
				<select name="agency_using">
					<option value="">----</option>
					<?php 
					$au_sql = mysql_query("
						SELECT *
						FROM `agency_using`
						ORDER BY `name` ASC
					");
					while($au = mysql_fetch_array($au_sql)){ ?>
						<option value="<?php echo $au['agency_using_id']; ?>"><?php echo $au['name']; ?></option>
					<?php
					}
					?>
					</select>		
				</select>
			  </div>
  
  
  <div class="fl-left" style="float: left; margin-left: 26px; margin-top: 16px;">
  <input type="hidden" value="<?php echo $start;?>" id="start" name="start">
  <input type="hidden" name="search_flag" value="1" />
  <input class="submitbtnImg" type="submit" value="Search">
  </div>
  
  <!--
  <div class="fl-right" style="float: right; margin-left: 18px; margin-top: 16px;">
    
  <a href="/export_target_agencies.php?sort=<?php echo $sort; ?>&order_by=<?php echo $order_by; ?>&state=<?php echo $state; ?>&salesrep=<?php echo $salesrep; ?>&region=<?php echo $region; ?>&phrase=<?php echo $phrase; ?>">
	<button type="button" class="submitbtnImg">Export</button>
  </a>
  
  </div>
  -->
  
</div>
</form> 

<?php

if($_GET['order_by']){
		if($_GET['order_by']=='ASC'){
			$ob = 'DESC';
			$sort_arrow = '<div class="arw-std-up arrow-top-active"></div>';
		}else{
			$ob = 'ASC';
			$sort_arrow = '<div class="arw-std-dwn arrow-dwn-active"></div>';
		}
	}else{
		$sort_arrow = '<div class="arw-std-up"></div>';
		$ob = 'ASC';
	}
	
	// default active
	$active = ($_GET['sort']=="")?'arrow-top-active':''; 

?>           
            

<table width="100%" cellspacing="1" cellpadding="5" border="0" class="table-left tbl-fr-red">	
	<tr bgcolor="#b4151b">
	<th><a href="<?php echo $_SERVER['PHP_SELF'] ?>?&sort=a.agency_name&order_by=<?php echo ($_GET['sort']=='a.agency_name')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Agency Name</div> <?php echo ($_GET['sort']=='a.agency_name')?$sort_arrow:'<div class="arw-std-up '.$active.'"></div>'; ?></a></th>
	<th><a href="<?php echo $_SERVER['PHP_SELF'] ?>?&sort=a.address_2&order_by=<?php echo ($_GET['sort']=='a.address_2')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Address</div> <?php echo ($_GET['sort']=='a.address_2')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></th>
	<th><a href="<?php echo $_SERVER['PHP_SELF']; ?>?sort=a.state&order_by=<?php echo ($_GET['sort']=='a.state')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold"><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></div> <?php echo ($_GET['sort']=='a.state')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></th>
	<th><a href="<?php echo $_SERVER['PHP_SELF']; ?>?sort=ar.agency_region_name&order_by=<?php echo ($_GET['sort']=='ar.agency_region_name')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold"><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?></div> <?php echo ($_GET['sort']=='ar.agency_region_name')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></th>
	<th><a href="<?php echo $_SERVER['PHP_SELF']; ?>?sort=a.tot_properties&order_by=<?php echo ($_GET['sort']=='a.tot_properties')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Properties</div> <?php echo ($_GET['sort']=='a.tot_properties')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></th>
	<th>Last Contact</th>
	<th>Next Contact</th>
	</tr>
	 
	<?php
	
		if( mysql_num_rows($result)>0 ){
			
		
		
	   while ($row = mysql_fetch_array($result))
	   {
	   $odd++;
		if (is_odd($odd)) {
			echo "<tr class='bg-white'>";		
			} else {
			echo "<tr class='bg-grey-light'>";
			}
			
		  echo "\n";
		 // (4) Print out each element in $row, that is,
		 // print the values of the attributes

			echo "<td>";	
			$ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}");
			echo "<a href='{$ci_link}'>{$row['agency_name']}</a>";
			echo "</td>\n";

			// address
			echo "<td>";		
			echo $row['address_1'] . " " . $row['address_2'] . " " . $row['address_3'];
			echo "</td>\n";

			// state
			echo "<td>";		
			echo $row['state'];
			echo "</td>\n";

			// region
			echo "<td>";		
			echo ( $row['postcode_region_id']!="" )?$row['postcode_region_name']:'';
			echo "</td>\n";
			
			echo "<td>";		
			echo $row['tot_properties'];
			echo "</td>\n";
			
			//echo "<td>";		
			//echo $row[6];
			//echo "</td>\n";
			
			echo "<td>";		
			echo $row['logdate'];
			echo "</td>\n";
			
	
			$c_sql = mysql_query("
				SELECT `next_contact`
				FROM `agency_event_log`
				WHERE `agency_id` = {$row['agency_id']}
				ORDER BY `next_contact` DESC
				LIMIT 0,1
			");
			$c = mysql_fetch_array($c_sql);
			
			
			// next contact
			echo "<td>";		
			echo ($c['next_contact']!="0000-00-00"&&$c['next_contact']!="")?date("d/m/Y",strtotime($c['next_contact'])):'';
			echo "</td>\n";
			
		  // Print a carriage return to neaten the output
		  echo "</tr>\n";
		  echo "\n";
	   }
	   
	   }else{
		   echo "<tr><td>Please press search to display results</td></tr>";
	   }
	   // (5) Close the database connection
	?>
	
</table>

            
            </td>
        </tr>
      </tbody>
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
<script type="text/javascript">


jQuery(document).ready(function(){
	
	
	
	// region multi select - region check all sub
	jQuery(document).on("click",".region_check_all",function(){
		var chk_state = jQuery(this).prop("checked");
		if(chk_state==true){
			jQuery(this).parents("li:first").find(".reg_db_sub_reg input").prop("checked",true);			
		}else{
			jQuery(this).parents("li:first").find(".reg_db_sub_reg input").prop("checked",false);
		}
		
	});
	
	// region multi select script
	jQuery(".state_ms").click(function(){
		
		var state = jQuery(this).val();
		var state_chk = jQuery(this).prop("checked");
		
		//console.log(state_sel);
		
		if(state_chk==true){
			
			jQuery("#load-screen").show();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_getAgencyMainRegion.php",
				data: { 
					state: state,
					agency_status: '<?php echo $agency_status; ?>'
				}
			}).done(function( ret ){	
				jQuery("#load-screen").hide();
				jQuery(".region_dp_body").append(ret);
			});
			
		}else{
			jQuery("."+state+"_regions").remove();
		}
			
				
	});
	
	
	// region multiselect - get sub region
	jQuery(document).on("click",".reg_db_main_reg",function(){
		
		var obj = jQuery(this);
		var region = obj.parents("li:first").find(".regions_id").val();
		var sub_reg_space = obj.parents("li:first").find(".reg_db_sub_reg").html();
		var check_all = obj.parents("li.main_region_li").find(".check_all_sub_region").prop("checked");
		
		
		if(sub_reg_space==""){
			
			jQuery("#load-screen").show();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_getAgencySubRegion.php",
				data: { 
					region: region,
					agency_status: '<?php echo $agency_status; ?>'
				}
			}).done(function( ret ){	
				jQuery("#load-screen").hide();
				obj.parents("li:first").find(".reg_db_sub_reg").html(ret);
				if( check_all == true ){
					obj.parents("li.main_region_li").find(".postcode_region_id").prop("checked",true);
				}
			});
			
		}else{
			obj.parents("li:first").find(".reg_db_sub_reg").html("");
		}
					
	});
	
	
	
	
	// datepicker
	jQuery(".datepicker").datepicker({ dateFormat: "dd/mm/yy" });
	


	$(function(){
		$('.next').click(function(){
			$('#start').val(<?php echo $to_display; ?>);
			$('#search').submit();
			return false;
		});
		
		$('.previous').click(function(){
			$('#start').val(<?php echo $prev_display;?>);
			$('#search').submit();
			return false;
		});
		
		// $('#all_agencies').click(function(){
			// $.ajax({
				// type: "GET",
				// url : 'get_all_agencies.php',
				// success: function(value) {
							// $("#agencies_all").append(value);
						// }
			// });
			
			// return false;
		// });
	});
	
</script>
</html>
