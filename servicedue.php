<?php

$title = "Service Due";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

include('inc/servicedue_functions.php');

// data
$state = $_REQUEST['searchstate'];
$salesrep = $_REQUEST['searchsalesrep'];
$region = $_REQUEST['searchregion'];
$phrase = $_REQUEST['phrase'];

// pagination script
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;
$this_page = $_SERVER['PHP_SELF'];

$params = "&search={$search}&state={$state}&salesrep={$salesrep}&region={$region}&phrase={$phrase}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$sql = getAgencies($offset,$limit,$state,$salesrep,$region,$phrase);
$ptotal = mysql_num_rows(getAgencies('','',$state,$salesrep,$region,$phrase));

?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
</style>


<?php
	  if($_SESSION['USER_DETAILS']['ClassID']==6){ ?>  
		<div style="clear:both;"></div>
	  <?php
	  }  
	  ?>


<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
		<div class="sats-breadcrumb">
			  <ul>
				<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
				<li class="other first"><a title="Service Due" href="/servicedue.php"><strong>Service Due</strong></a></li>
			  </ul>
		</div>
		  
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Service Due</div>';
		}
		
		
		?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		

		
		<form method=POST action="<?=URL;?>servicedue.php" class="searchstyle">

    	<div class="ap-vw-reg agn-prop aviw_drop-h" id="vad-tp" style="border: 1px solid #ccc;">
  <div class="fl-left dv1fle">
    <label>State:</label>
    <?php //$allstates = $user->getAllStates();?>
	<select name="searchstate" class="a-region">
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
  <div class="fl-left dv2fle">
    <label>Sales Rep:</label>
    <?php 
				$sr_sql = mysql_query("
					SELECT 
						DISTINCT a.`salesrep`,
						sa.`StaffID`, 
						sa.`FirstName`, 
						sa.`LastName` 
					FROM `agency` AS a
					LEFT JOIN `staff_accounts` AS sa ON sa.`StaffID` = a.`salesrep`
					LEFT JOIN `agency_regions` AS ar ON ar.`agency_region_id` = a.`agency_region_id`
					WHERE a.`status` = 'active'
					AND sa.`Deleted` = 0  
					AND sa.`active` = 1 
					ORDER BY sa.`FirstName` ASC;
				");
			?>
			<select name="searchsalesrep" class="b-region">
				<option value=""></option>
				<?php
				while($sr = mysql_fetch_array($sr_sql)){ ?>
					<option value="<?php echo $sr['StaffID']; ?>"><?php echo "{$sr['FirstName']} {$sr['LastName']}"; ?></option>
				<?php
				}
				?>
				<option value=""></option>
			</select>
  </div>
  <div class="fl-left dv3fle">
    <label>Region:</label>
    <?php
				$query_region = "SELECT * FROM agency_regions";
				$result_region = mysqlMultiRows($query_region);
			?>
			<select name="searchregion" class="s-region">
				<option <?php echo $region == ''? 'selected="selected"': '';?> value=''></option>
			<?php foreach($result_region as $regions){?>
				<option <?php echo $region == $regions['agency_region_name'] ? 'selected="selected"': '';?> value="<?=$regions['agency_region_name'];?>" ><?=$regions['agency_region_name'];?></option>
			<?php }?>
			</select>
  </div>
  
  
  <div class="fl-left dv4fle">
    <label>Phrase:</label>
    <input type="text" value="" size="10" name="phrase" class="addinput searchstyle">
  </div>
  
  <div class="fl-left last">
   <input class="searchstyle submitbtnImg" type="submit" value="Search">
  </div>
  
  
  <div class="fl-left last">
  <a class="submitbtnImg export" href="/export_servicedue.php?&state=<?php echo $state; ?>&salesrep=<?php echo $salesrep; ?>&region=<?php echo $region; ?>&phrase=<?php echo $phrase; ?>">Export</a>
  </div>
  
</div>
    
		</form>
		
		<form action="sms2.php" method="post">
			<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">
				<tr class="toprow jalign_left">
					<th>Agency Name</th>
					<th>Sales Rep</th>
					<th>Service Due</th>
				</tr>
					<?php
					
				
				
					
					
									
					
					if(mysql_num_rows($sql)>0){
						$i = 0;
						while($row = mysql_fetch_array($sql)){
					?>
							<tr class="body_tr jalign_left">
								<td>
									<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
									<a href="<?php echo $ci_link; ?>"><?php echo $row['agency_name']; ?></a>
								</td>
								<td>
									<?php echo "{$row['FirstName']} {$row['LastName']}" ?>
								</td>
								<td style="border-right: 1px solid #ccc;">
									<?php echo $row['jcount']; ?>
								</td>
							</tr>
					<?php
						$i++;
						}
					}else{ ?>
						<td colspan="5" align="left">Empty</td>
					<?php
					}
					?>
					<tr>
					<td colspan='10' class="padding-none">
					 <div class="sats-pg-navigation">
						<div class="sats-inner-pagination">
							<div class="sats-inner-pagination">
							<?php
								if($offset!=0&&$offset!=""){ ?>
								<a href="<?php echo $prev_link; ?>" class="left">&lt;</a>
							<?php
								}
							?>			
							 <div class="sats-pagination-view">Viewing <?php echo (mysql_num_rows($sql)>0)?$offset+1:"0"; ?> to <?php echo ($offset+mysql_num_rows($sql)); ?> of <?php echo mysql_num_rows($ptotal); ?></div> 
							<?php
								if(($offset+mysql_num_rows($sql)) < mysql_num_rows($ptotal)){ ?>
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

		</form>
		
		
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



<script>
jQuery(document).ready(function(){

	// get sms ID
	function getSmsMsgId(){
		var msg = jQuery("#sms_msg_id").val();
		jQuery("#hid_sms_msg_id").val(msg);
	}
	
	getSmsMsgId();
	
	jQuery("#sms_msg_id").change(function(){
		getSmsMsgId();
	});


	
	// preview sms msg
	jQuery(".preview").click(function(){
		var job_id = jQuery(this).parents("tr:first").find(".job_id").val();
		var sms_msg_id = jQuery("#sms_msg_id").val();
		jQuery.ajax({
			type: "POST",
			url: "ajax_preview_sms_message.php",
			data: { 
				job_id: job_id,
				sms_msg_id: sms_msg_id
			}
		}).done(function( ret ){
			alert(ret);
		});	
	});
	

	// check all toggle
	jQuery("#chk_all").click(function(){
		if(jQuery(this).prop("checked") == true){
			jQuery(".job_chk").prop("checked",true);
		}else{
			jQuery(".job_chk").prop("checked",false);
		}
	});
});
</script>


</body>
</html>