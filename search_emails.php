<?php

$title = "Search";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

//include('inc/no_id_properties_functions.php');



// search for property id, job id, workorder and property address
function search($search,$offset,$limit){
	
	$str = "";
	$search_str = "";
	
	if( $search!='' ){

		$search_str = "AND pt.`tenant_email` LIKE '%".trim($search)."%'";

	}	
	
	if(is_numeric($offset) && is_numeric($limit))
	{	
		$str .= " LIMIT {$offset}, {$limit}";			
	}
	
	
	$sql = "
		SELECT 
			DISTINCT(
				p.`property_id`
			), 
			p.`address_1` AS p_address_1,
			p.`address_2` AS p_address_2,
			p.`address_3` AS p_address_3,
			p.`state` AS p_state,
			p.`postcode` AS p_postcode,

			a.`agency_id`,
			a.`agency_name`
		FROM `property_tenants` AS pt
		LEFT JOIN `property` AS p ON pt.`property_id` = p.`property_id`	
		LEFT JOIN `property_managers` AS pm ON p.`property_managers_id` = pm.`property_managers_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`		
		WHERE a.`country_id` = {$_SESSION['country_default']}
		{$search_str}
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
</style>




<div id="mainContent">



    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="Search Emails" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong>Search</strong></a></li>
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
				<input type="text" name="search" style="margin-right: 10px; width: 200px;" value="<?php echo $search; ?>" />
				<input type="submit" class="submitbtnImg" name="submit" value="Search" />
			</form>
</div>
		
		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">
			<tr class="toprow jalign_left">
				<th>Address</th>
				<th>Agency</th>
			</tr>
				<?php
				
				
				
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){														
				?>
						<tr class="body_tr jalign_left">
							<td>
								<span><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']} {$row['p_address_3']} {$row['p_state']}"; ?></a></span>
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
					<td colspan='7' class="padding-none">
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

</body>
</html>