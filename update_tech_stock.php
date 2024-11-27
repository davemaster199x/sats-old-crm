<?php

$title = "Update Tech Stock";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$staff_id = $_GET['id'];
$tech_stock_id = $_GET['tech_stock_id'];

function getStock(){
	return mysql_query("
		SELECT *
		FROM `stocks`
		WHERE `country_id` = {$_SESSION['country_default']}
		AND `status` = 1
		AND `show_on_stocktake` = 1
		ORDER BY `item` ASC
	");
}

function getTechStockItems($tech_stock_id,$stocks_id){
	return mysql_query("
		SELECT *
		FROM `tech_stock_items` 
		WHERE `tech_stock_id` = {$tech_stock_id}
		AND `stocks_id` = {$stocks_id}
	");
}

function getLatestStocktake($staff_id,$stocks_id){
	return mysql_query("
		SELECT ts_main. * , tsi. * 
		FROM  `tech_stock` AS ts_main
		INNER JOIN (
			SELECT MAX(  `date` ) AS latestDate,  `staff_id` 
			FROM  `tech_stock` 
			WHERE  `country_id` =1
			AND  `staff_id` ={$staff_id}
			GROUP BY  `staff_id`
		) AS ts ON ts_main.`staff_id` = ts.`staff_id` 
		AND ts_main.`date` = ts.latestDate
		INNER JOIN  `tech_stock_items` AS tsi ON ts_main.`tech_stock_id` = tsi.`tech_stock_id` 
		WHERE ts_main.`staff_id` ={$staff_id}
		AND tsi.`stocks_id` = {$stocks_id}
	");
}

function staffVehicle($staff_id){
	return mysql_query("
		SELECT *
		FROM `vehicles` AS v
		LEFT JOIN `staff_accounts` AS sa ON sa.`StaffID` = v.`StaffID`
		WHERE v.`country_id` = {$_SESSION['country_default']}
		AND v.`StaffID` = {$staff_id}
	");	
}

function getTechstockSelectedVehicle($tech_stock_id){
	return mysql_query("
		SELECT *
		FROM `tech_stock` AS ts
		LEFT JOIN `vehicles` AS v ON ts.`vehicle` = v.`vehicles_id`
		WHERE ts.`country_id` = {$_SESSION['country_default']}
		AND ts.`tech_stock_id` = {$tech_stock_id}
	");	
}

?>
<style>
.addproperty input, .addproperty select {
    width: 30%;
}
.addproperty label {
   width: 230px;
}
.jtable{
   margin-bottom: 36px;
}

.jtable th,
.jtable td{
   text-align: left;
}
</style>


<?php
	  if($_SESSION['USER_DETAILS']['ClassID']==6){ ?>  
		<div style="clear:both;"></div>
	  <?php
	  }  
	  ?>


<div id="mainContent">


	

	

	
	
		 <?php
		  if($_SESSION['USER_DETAILS']['ClassID']==6){ 
		  
		 $tech_id = $_SESSION['USER_DETAILS']['StaffID'];
		  
		  $day = date("d");
		  $month = date("m");
		  $year = date("y");
		  
		  include('inc/tech_breadcrumb.php');
		  
		  }else{ ?>
		  
			<div class="sats-breadcrumb">
			  <ul>
				<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
				<li class="other first"><a title="Update Tech Stock" href="/update_tech_stock.php?id=<?php echo $staff_id; ?>"><strong><?php echo $title; ?></strong></a></li>
			  </ul>
			</div>
		  
		  <?php
		  }  
		  ?>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Tech Stock Updated</div>';
		}
		
		
		?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		<form action="update_tech_stock_process.php" method="post" id="jform" style="font-size: 14px; margin-top: 30px;">
			<div class="addproperty">
			
				

				<?php
				// view more
				if($tech_stock_id!=""){ ?>
				
				
					<?php
					// get stocks
					$sql = getStock();
					
					if(mysql_num_rows($sql)>0){
						while($row = mysql_fetch_array($sql)){ ?>
							<div class="row">
								<input type="hidden" name="stocks[]" value="<?php echo $row['stocks_id']; ?>" />
								<label class="addlabel" for="item"><?php echo $row['item']; ?></label>
								<?php
								$tsi_sql = getTechStockItems($tech_stock_id,$row['stocks_id']);
								$tsi = mysql_fetch_array($tsi_sql);								
								?>
								<input type="text"  class="addinput" name="quantity[]" value="<?php echo $tsi['quantity']; ?>" />
							</div>
						<?php
						}
					}
					
					?>
				
					<div class="row">
						<label class="addlabel" for="vehicle">Vehicle</label>
						<select name="vehicle">
							<option value="">----</option>	
							<?php
							
							// get staff vehicles
							$sel_v_sql = getTechstockSelectedVehicle($tech_stock_id);
							$sel_v = mysql_fetch_array($sel_v_sql);
							$staff_vehicle = $sel_v['vehicle'];
							
							$v_sql = mysql_query("
								SELECT *
								FROM `vehicles` AS v
								LEFT JOIN `staff_accounts` AS sa ON sa.`StaffID` = v.`StaffID`
								WHERE `country_id` = {$_SESSION['country_default']}
								ORDER BY v.`plant_id`
							");
							while($v = mysql_fetch_array($v_sql)){ ?>
								<option value="<?php echo $v['vehicles_id']; ?>" <?php echo ($v['vehicles_id']==$staff_vehicle)?'selected="selected"':''; ?>><?php echo $v['number_plate']; ?></option>
							<?php	
							}
							?>
						</select>
					</div>
					
				<?php	
				// update tech stock
				}else{ ?>
				
					<?php
					// get stocks
					$sql = getStock();
					/*
					if(mysql_num_rows($sql)>0){
						while($row = mysql_fetch_array($sql)){ ?>
							<div class="row">
								<input type="hidden" name="stocks[]" class="stocks" value="<?php echo $row['stocks_id']; ?>" />
								<label class="addlabel" for="item"><?php echo $row['item']; ?></label>
								<?php
								$tsi_sql = getLatestStocktake($staff_id,$row['stocks_id']);
								$tsi = mysql_fetch_array($tsi_sql);								
								?>
								<input type="text"  class="addinput quantity" name="quantity[]" value="<?php echo $tsi['quantity']; ?>" />
							</div>
						<?php
						}
					}
					*/
					?>


					<table class="table jtable">
						<thead>
							<th>Code</th>
							<th>Name</th>
							<th>Qty</th>
						</thead>
						<?php
						while( $row = mysql_fetch_array($sql) ){ ?>
							<tr>
								<td>
									<input type="hidden" name="stocks[]" value="<?php echo $row['stocks_id']; ?>" />
									<?php echo $row['code'] ?>
								</td>
								<td>
									<?php echo $row['item'] ?>
								</td>
								<td>
									<?php
									$tsi_sql = getLatestStocktake($staff_id,$row['stocks_id']);
									$tsi = mysql_fetch_array($tsi_sql);		
									?>    
									<input type="text"  class="addinput" name="quantity[]" value="<?php echo $tsi['quantity']; ?>" />                                 
								</td>
							</tr>
						<?php
						}
						?>
					</table>
				
				
					<div class="row">
						<label class="addlabel" for="vehicle"><strong>Vehicle</strong></label>
						<select name="vehicle" id="vehicle">
							<option value="">----</option>	
							<?php
							
							// get staff vehicles
							$sel_v_sql = staffVehicle($staff_id);
							$sel_v = mysql_fetch_array($sel_v_sql);
							$staff_vehicle = $sel_v['vehicles_id'];
							
							$v_sql = mysql_query("
								SELECT *
								FROM `vehicles` AS v
								LEFT JOIN `staff_accounts` AS sa ON sa.`StaffID` = v.`StaffID`
								WHERE `country_id` = {$_SESSION['country_default']}
								ORDER BY v.`plant_id`
							");
							while($v = mysql_fetch_array($v_sql)){ ?>
								<option value="<?php echo $v['vehicles_id']; ?>" <?php echo ($v['vehicles_id']==$staff_vehicle)?'selected="selected"':''; ?>><?php echo $v['number_plate']; ?></option>
							<?php	
							}
							?>
						</select>
					</div>
					
					
					<div class="row" style="margin-top: 35px;">
						<label class="addlabel"></label>
						<input type="hidden" name="staff_id" id="staff_id" value="<?php echo $staff_id; ?>" />
						<input type="submit" class="submitbtnImg" name="submit" value="Submit" />
					</div>
					
				<?php	
				}
				?>
				

				
				
			</div>
		</form>


	
</div>

<br class="clearfloat" />
<script>
jQuery("#vehicle").change(function(){
	

	var vehicle = jQuery(this).val();
	
	// update tech stock
	jQuery.ajax({
		type: "POST",
		url: "ajax_get_tech_stock_details.php",
		data: { 
			vehicle: vehicle,
			country_id: <?php echo $_SESSION['country_default'] ?>			
		},
		dataType: 'json'
	}).done(function( ret ) {
		var staff_id = ret.staff_id;
		//console.log("Vehicle Driver: "+staff_id);
		window.location="/update_tech_stock.php?id="+staff_id;
	});	
	
});
</script>
</body>
</html>