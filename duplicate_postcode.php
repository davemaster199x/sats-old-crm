<?php

$title = "Duplicate Postcode";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');


$crm = new Sats_Crm_Class;

$country_id = $_SESSION['country_default'];

$duplicate = $crm->getPostcodeDuplicates();

switch($country_id){
	case 1:
		$country_iso = 'AU';
	break;
	case 2:
		$country_iso = 'NZ';
	break;
}

?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}

.green_check{
	display:none;
	width: 30px;
	margin-top: 5px;
}

.region_name, .sub_region_name {
    width: 200px;
}
.region_state{
	width:50px;
}

.jtable .header th{
	padding-left: 7px;
}

#jtable_inner{
	border-collapse: separate;
}
</style>
<div id="mainContent">

	
   
    <div class="sats-middle-cont">
	
		
	
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="<?php echo $title; ?>" href="/duplicate_postcode.php"><strong><?php echo $title; ?></strong></a></li>
		  </ul>
		</div>
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		
		
		<?php 
		
		/*
		echo "<h2>Duplicate Postcode({$country_iso}): ".count($duplicate)."</h2>";
		echo "<pre>";
		print_r($duplicate);
		echo "</pre>";
		*/
		
		?>
		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd jtable" style="margin-top: 0px; margin-bottom: 13px;">
			<tr class="toprow jalign_left header">
				<th style="width: 100px;">Postcode</th>	
				<th class="region_state"><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></th>
				<th class="region_name"><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?></th>
				<th class="sub_region_name">Sub Region</th>
				<th><b>Postcodes</b></th>
			</tr>
			<?php 
			foreach($duplicate as $pc){ ?>
				<tr>
					<td align="left"><a href="/view_regions.php?postcode=<?php echo $pc; ?>"><?php echo $pc; ?></a></td>
					<td colspan="4">
						<?php
						$regions = mysql_query("
							SELECT * 
							FROM `postcode_regions` AS pr
							LEFT JOIN `countries` AS c ON pr.`country_id` = c.`country_id`
							LEFT JOIN `regions` AS r ON pr.`region` = r.`regions_id`
							WHERE pr.`deleted` = 0 
							AND pr.`country_id` = {$_SESSION['country_default']}
							AND pr.`postcode_region_postcodes` LIKE '%{$pc}%'
							ORDER BY r.`region_state` ASC
						");
						?>
						<table class="table-left tbl-fr-red" id="jtable_inner">
							
						
							 
							<?php

								$odd=0;

							   // (3) While there are still rows in the result set,
							   // fetch the current row into the array $row
							   while( $region = mysql_fetch_array($regions) ){

							   $odd++;
								if (is_odd($odd)) {
									$row_color = '#FFFFFF';		
								}else{
									$row_color = '#eeeeee';
								}
								?>
								<tr style="background-color:<?php //echo $row_color; ?>">
								
									<td class="region_state"><?=$region['region_state'];?></td>
									<td class="region_name"><?=$region['region_name'];?></td>	
									<td class="sub_region_name"><?=$region['postcode_region_name'];?></td>
									<td><?=str_replace(",",", ",$region['postcode_region_postcodes']);?></td>									

								</tr>
							
						   <? } ?>						
					</table>
					</td>
				</tr>
			<?php	
			}
			?>		
		</table>
		
		
	</div>
</div>

<br class="clearfloat" />

</body>
</html>