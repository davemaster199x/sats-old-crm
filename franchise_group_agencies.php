<?

$title = "Franchise Groups Agencies";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$fg_id = $_REQUEST['fg_id'];

$state = "";
$salesrep = "";
$region = "";
if($_POST){
	$state = $_POST['searchstate'];
	$salesrep = $_POST['searchsalesrep'];
	$region = $_POST['searchregion'];
}


	function get_agency($get_all,$offset,$limit,$sort,$order_by,$state,$salesrep,$region,$phrase,$franchise_groups_id){

		// state
		if($state!=""){
			$str .= " AND LOWER(a.state) LIKE '%{$state}%' ";
		}
		
		// sales rep
		if($salesrep!=""){
			$str .= " AND (CONCAT_WS(' ',LOWER(s.FirstName), LOWER(s.LastName)) LIKE '%{$salesrep}%') ";
		}
		
		// region
		if($region!=""){
			$str .= " AND (LOWER(ar.agency_region_name) LIKE '%{$region}%') ";
		}
		
		// phrase
		if($phrase!=""){
			$str .= " AND ( CONCAT_WS( ' ', LOWER(a.agency_name), LOWER(a.contact_first_name), LOWER(a.contact_last_name), LOWER(s.FirstName), LOWER(s.LastName), LOWER(a.state), LOWER(ar.agency_region_name) ) LIKE '%{$phrase}%') ";
		}
		
		
		// pagination limit
		if($get_all==1){
			$str .= "";
		}else{
			$str .= " ORDER BY {$sort} {$order_by} LIMIT {$offset}, {$limit}";
		}
				
		$sql = "
			SELECT *
			FROM
			  agency a
			LEFT JOIN  agency_regions ar USING (agency_region_id)
			LEFT JOIN staff_accounts s ON (a.salesrep = s.StaffID)
			WHERE a.`status` = 'active'
			AND `franchise_groups_id` = {$franchise_groups_id}
			{$str}
	   ";
	   
	   return mysql_query ($sql);
	
	}
	
	// header sort parameters
	$sort = ($_REQUEST['sort'])?$_REQUEST['sort']:'a.agency_name';
	$order_by = ($_REQUEST['order_by'])?$_REQUEST['order_by']:'ASC';
	
	// phrase script
	$phrase = $_REQUEST['phrase'];
	
	// pagination script
	$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
	$limit = 50;
	$this_page = $_SERVER['PHP_SELF'];
	$next_link = "{$this_page}?fg_id={$fg_id}&offset=".($offset+$limit)."&sort={$sort}&order_by={$order_by}&search={$search}&state={$state}&salesrep={$salesrep}&region={$region}&phrase={$phrase}";
	$prev_link = "{$this_page}?fg_id={$fg_id}&offset=".($offset-$limit)."&sort={$sort}&order_by={$order_by}&search={$search}&state={$state}&salesrep={$salesrep}&region={$region}&phrase={$phrase}";
	
	$result = get_agency(0,$offset,$limit,$sort,$order_by,$state,$salesrep,$region,$phrase,$fg_id);
	$ptotal = mysql_num_rows(get_agency(1,'','',$sort,$order_by,$state,$salesrep,$region,$phrase,$fg_id));
	

?>

<div id="mainContent"> 

<div class="sats-middle-cont">


    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="Franchise Groups" href="/franchise_groups.php">Franchise Groups</a></li>
        <li class="other first"><a title="Franchise Group Agencies" href="/franchise_group_agencies.php?fg_id=<?php echo $fg_id;; ?>"><strong>Franchise Group Agencies</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

<table border=1 cellpadding=0 cellspacing=0>
<tr class="tbl-view-prop">
<td>


	<form method=POST action="/franchise_group_agencies.php?fg_id=<?php echo $fg_id; ?>" class="searchstyle">

    	<div class="ap-vw-reg agn-prop aviw_drop-h">
  <div class="fl-left">
    <label>State:</label>
    <?php //$allstates = $user->getAllStates();?>
	<select name="searchstate">
				<option value="">----</option>
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
  
  <div class="fl-left">
    <label>Sales Rep:</label>
    <?php 
				$sr_sql = mysql_query("
					SELECT DISTINCT (
					sa.`StaffID`
					), sa.`StaffID` , sa.`FirstName` , sa.`LastName`
					FROM `agency` AS a
					LEFT JOIN `staff_accounts` AS sa ON a.`salesrep` = sa.`StaffID`
					WHERE a.`franchise_groups_id` ={$fg_id}
					AND sa.`StaffID` IS NOT NULL
					AND sa.`Deleted` = 0 
					AND sa.`active` = 1 
					ORDER BY sa.`FirstName` ASC;
				");
			?>
			<select name="searchsalesrep">
				<option value="">----</option>
			<?php while($sr = mysql_fetch_array($sr_sql)){
				$sales_rep_name = $sr['FirstName']." ".$sr['LastName'];
			?>
				<option value="<?php echo $sales_rep_name; ?>" <?php  echo ($sales_rep_name==$salesrep)?'selected="selected"':''; ?>><?php echo $sales_rep_name; ?></option>
			<?php }?>
			</select>
  </div>

  <div class="fl-left">
    <label>Region:</label>
    <?php
				$query_region = "SELECT * FROM agency_regions WHERE `agency_region_name` != ''";
				$result_region = mysqlMultiRows($query_region);
			?>
			<select name="searchregion">
				<option value="">----</option>
			<?php foreach($result_region as $regions){?>
				<option <?php echo $region == $regions['agency_region_name'] ? 'selected="selected"': '';?> value="<?=$regions['agency_region_name'];?>" ><?=$regions['agency_region_name'];?></option>
			<?php }?>
			</select>
  </div>
  
  
  <div class="fl-left">
    <label>Phrase:</label>
    <input type="text" value="" size="10" name="phrase" class="addinput searchstyle">
  </div>
  
  <div class="fl-left last">
   <input class="searchstyle submitbtnImg" type="submit" value="Search">
  </div>
  
  
  <div class="fl-left last">
  <a class="submitbtnImg export" href="/export_franchise_group_agencies.php?fg_id=<?php echo $fg_id;; ?>&sort=<?php echo $sort; ?>&order_by=<?php echo $order_by; ?>&state=<?php echo $state; ?>&salesrep=<?php echo $salesrep; ?>&region=<?php echo $region; ?>&phrase=<?php echo $phrase; ?>">Export</a>
  </div>
  
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

		
<style>
#table_fg td {
    padding: 5px;
}
</style>		
<table id="table_fg" border=0 cellspacing=1 cellpadding=5 width=100% class="table-left tbl-fr-red">
<tr bgcolor="#b4151b">
<th><b><a href="<?php echo $_SERVER['PHP_SELF']; ?>?fg_id=<?php echo $fg_id; ?>&sort=a.agency_name&order_by=<?php echo ($_GET['sort']=='a.agency_name')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Office</div> <?php echo ($_GET['sort']=='a.agency_name')?$sort_arrow:'<div class="arw-std-up '.$active.'"></div>'; ?></a></b></th>
<th><b><a href="<?php echo $_SERVER['PHP_SELF']; ?>?fg_id=<?php echo $fg_id; ?>&sort=a.state&order_by=<?php echo ($_GET['sort']=='a.state')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">State</div> <?php echo ($_GET['sort']=='a.state')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></b></th>
<th><b><a href="<?php echo $_SERVER['PHP_SELF']; ?>?fg_id=<?php echo $fg_id; ?>&sort=a.tot_properties&order_by=<?php echo ($_GET['sort']=='a.tot_properties')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Properties</div> <?php echo ($_GET['sort']=='a.tot_properties')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></b></th>
<?php
// get alarm job type
$ajt_sql = mysql_query("
	SELECT *
	FROM `alarm_job_type`
	WHERE `active` = 1
");
while($ajt = mysql_fetch_array($ajt_sql)){ 

	switch($ajt['id']){
		case 2:
			$serv_color = 'b4151b';
			$serv_icon = 'smoke_white.png';
		break;
		case 5:
			$serv_color = 'f15a22';
			$serv_icon = 'safety_white.png';
		break;
		case 6:
			$serv_color = '00ae4d';
			$serv_icon = 'corded_white.png';
		break;
		case 7:
			$serv_color = '00aeef';
			$serv_icon = 'pool_white.png';
		break;
		case 8:
			$serv_color = '9b30ff';
			$serv_icon = 'sa_ss_white.png';
		break;
		case 9:
			$serv_color = '9b30ff';
			$serv_icon = 'sa_cw_ss_white.png';
		break;
	}

?>
	<th><img src="images/serv_img/<?php echo $serv_icon; ?>" /></th>
<?php	
}
?>
</tr>
<?php
	
	
	
	

  
	//$user->prepareStateString('AND')
   //status 'target' is not active agency but stored in database

	$odd=0;

	// get services numbers
	function get_serv_num($agency_id,$alarm_job_type_id,$service=""){
	
		$str = "";
	
		if($service !== ""){
			$str .= " AND ps.`service` = {$service} ";
		}
	
		$sql = "
			SELECT COUNT( * ) AS num_serv
			FROM `property_services` AS ps
			LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE p.`agency_id` ={$agency_id}
			AND p.`deleted` =0
			AND ps.`alarm_job_type_id` ={$alarm_job_type_id}
			{$str}
		";
	
		$serv_sql = mysql_query($sql);
		
		if(mysql_num_rows($serv_sql)>0){		
			$serv = mysql_fetch_array($serv_sql);		
			return $serv['num_serv'];		
		}else{
			return 0;	
		}	
	
	}
	
	
   
	$prop_tot = 0;
	$sa_tot = 0;
	$cw_tot = 0;
	$sw_tot = 0;
	$pb_tot = 0;
	
   while ($row = mysql_fetch_array($result))

   {

   $odd++;

	if (is_odd($odd)) {

		echo "<tr bgcolor=#FFFFFF>";		

		} else {

		echo "<tr bgcolor=#eeeeee>";

   		}

		

      echo "\n";

	  	  

  

     // (4) Print out each element in $row, that is,

     // print the values of the attributes



		echo "<td>";		
		$ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); 
		echo "<a href='{$ci_link}'>{$row['agency_name']}</a>";
		echo "</td>";

		
		

		
		echo "<td>";		
		echo $row['state'];
		echo "</td>";
		

		

		

		echo "<td>";		
		echo $row['tot_properties'];
		echo "</td>";		
	
		?>
		
		
		
		
		
		<?php
						// get alarm job type
						$ajt_sql = mysql_query("
							SELECT *
							FROM `alarm_job_type`
							WHERE `active` = 1
						");
						while($ajt = mysql_fetch_array($ajt_sql)){ 
						?>
						<td>
							<?php							
								// service count
								echo $serv_count = get_serv_num($row['agency_id'],$ajt['id'],1);
								$serv_tot[$ajt['id']] += $serv_count;
							?>
						</td>							
						<?php
						}
							
						/*
						$smoke += $smoke;
						$window_tot += $window;
						$switch_tot += $switch;
						$tot_tot += $tot;
						*/						
						?>
		
		
	
	
		<?php

      // Print a carriage return to neaten the output

	  echo "</tr>";

      echo "\n";
	  
	  ?>

		
		
		
		
	  <?php
	  
	  $prop_tot += $row['tot_properties'];
	  /*
	  $sa_tot += $sa;
	  $cw_tot += $cw;
	  $ss_tot += $ss;
	  $pb_tot += $pb;
	  */

   }

   // (5) Close the database connection

   

?>



<tr>
	<td colspan="2">Total:</td>
	<td><?php echo $prop_tot; ?></td>
	<?php
	$x = 0;
	foreach($serv_tot as $index=>$val){ ?>
	<td>
		<strong>
		<?php 
		echo $val;								
		?>
		</strong>
	</td>
	<?php	
	$x++;
	}
	?>			
</tr>


<tr>
<td colspan="<?php echo $x+3; ?>" class="padding-none">
	<div class="sats-pg-navigation">
		<div class="sats-inner-pagination">
			<div class="sats-inner-pagination">
			<?php
				if($offset!=0&&$offset!=""){ ?>
				<a href="<?php echo $prev_link; ?>" class="left">&lt;</a>
			<?php
				}
			?>			
			 <div class="sats-pagination-view">Viewing <?php echo (mysql_num_rows($result)>0)?$offset+1:"0"; ?> to <?php echo ($offset+mysql_num_rows($result)); ?> of <?php echo $ptotal; ?></div> 
			<?php
				if(mysql_num_rows($result)==$limit){ ?>
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

</td>
</tr>
      </table>
      
  </div>
  
</div>


<br class="clearfloat" />


<style>
.serv_td{
	padding: 0 10px!important;
    width: 115px!important;
}
</style>
<script>
jQuery(document).ready(function(){
	// service script
	jQuery(".serv_link").click(function(){
		jQuery(this).parents("tr:first").next().toggle();
	});	
});
</script>
</body>
</html>
