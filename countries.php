<?php

$title = "Countries";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$region = mysql_real_escape_string($_REQUEST['region']);
$job_type = mysql_real_escape_string($_REQUEST['job_type']);
$state = mysql_real_escape_string($_REQUEST['state']);

$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;
$this_page = $_SERVER['PHP_SELF'];
$params = "&region=".urlencode($region)."&job_type=".urlencode($job_type)."&state=".urlencode($state);
$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$plist = getCountries($offset,$limit);
$ptotal = mysql_num_rows(getCountries('',''));




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
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Agency Update Successfull</div>';
		}
		
		
		//echo date('Y-m-d', strtotime("-30 days"));
		
		
		?>
		
		
		

		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
			<tr class="toprow jalign_left">
				<th>Name</th>
				<th>Code</th>
				<th>Agent Number</th>
				<th>Tenant Number</th>
				<th>Email Signature</th>
				<th>Letterhead Footer</th>
				<th>Trading Name</th>
				<th>Outgoing Email</th>
				<th>Bank</th>				
				<th>AC Name</th>
				<th>BSB</th>
				<th>AC Number</th>
			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
				?>
						<tr class="body_tr jalign_left" style="background-color:<?php echo ($i%2!=0)?'#eeeeee':'' ?>">
							<td><a href="country_details.php?id=<?php echo $row['country_id']; ?>"><?php echo $row['country']; ?></a></td>
							<td><?php echo $row['iso']; ?></td>
							<td><?php echo $row['agent_number']; ?></td>
							<td><?php echo $row['tenant_number']; ?></td>
							<td><a href="http://sats.com.au/images/<?php echo $row['email_signature']; ?>"><?php echo $row['email_signature']; ?></a></td>
							<td><a href="http://sats.com.au/documents/<?php echo $row['letterhead_footer']; ?>"><?php echo $row['letterhead_footer']; ?></a></td>
							<td><?php echo $row['trading_name']; ?></td>
							<td><?php echo $row['outgoing_email']; ?></td>
							<td><?php echo $row['bank']; ?></td>							
							<td><?php echo $row['ac_name']; ?></td>
							<td><?php echo $row['bsb']; ?></td>
							<td><?php echo $row['ac_number']; ?></td>
						</tr>
						
				<?php
					$i++;
					}
				}else{ ?>
					<td colspan="100%" align="left">Empty</td>
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
</div>

<br class="clearfloat" />
</body>
</html>