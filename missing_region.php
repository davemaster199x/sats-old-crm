<?php

$title = "Missing Regions";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

// data
$state = $_REQUEST['searchstate'];
$salesrep = $_REQUEST['searchsalesrep'];
$region = $_REQUEST['searchregion'];
$phrase = $_REQUEST['phrase'];
//$postcode = getPostCode();

// pagination script
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;
$this_page = $_SERVER['PHP_SELF'];
$next_link = "{$this_page}?offset=".($offset+$limit);
$prev_link = "{$this_page}?offset=".($offset-$limit);



$sql = getMissingRegionProperty($offset,$limit);
$ptotal = mysql_num_rows(getMissingRegionProperty('',''));

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
				<li class="other first"><a title="Service Due" href="/missing_region.php"><strong>Missing Region</strong></a></li>
			  </ul>
		</div>
		  
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Service Due</div>';
		}
		
		
		?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		

		<?php
		//echo getPostCode();
		?>
		
	
		
		<form action="sms2.php" method="post">
			<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">
				<tr class="toprow jalign_left">
					<th>Property</th>
					<th>Agency</th>
					<th>Postcode</th>
				</tr>
					<?php
					
				
				
					
					
									
					
					if(mysql_num_rows($sql)>0){
						$i = 0;
						while($row = mysql_fetch_array($sql)){
					?>
							<tr class="body_tr jalign_left">
								<td>
									<a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['address_1']} {$row['address_2']} {$row['address_3']}, {$row['state']} {$row['postcode']}"; ?></a>
								</td>
								<td>
									<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
									<a href="<?php echo $ci_link; ?>"><?php echo $row['agency_name']; ?></a>
								</td>
								<td>
									<?php echo $row['postcode']; ?>
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





</body>
</html>