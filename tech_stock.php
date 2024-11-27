<?php

$title = "Tech Stock Report";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

function getTechStock($offset,$limit,$distinct='',$filter=''){

	if($distinct!=""){
		
		switch($distinct){
			case 'ts_main.`vehicle`':
				$sel_str = "SELECT DISTINCT ts_main.`vehicle`, v.`number_plate`";
				$order_by_str = ' ORDER BY v.`number_plate` ASC ';
			break;
			case 'ts_main.`staff_id`':
				$sel_str = "SELECT DISTINCT ts_main.`staff_id`, sa.`FirstName`, sa.`LastName`";
				$order_by_str = ' ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC ';
			break;
		}
		
		
		
		
	}else{
		
		$sel_str = "
			SELECT 
				ts_main.date, 
				ts_main.`tech_stock_id` , 
				ts_main.`staff_id` , 
				
				sa.`FirstName` , 
				sa.`LastName` , 
				
				v.`number_plate`,
				
				sa.`is_electrician`
		";
		
		$order_by_str = ' ORDER BY ts_main.`date` DESC  ';
		
	}
	
	/*
	if(empty($filter)){
		echo "filter empty";
	}else{
		echo "filter not empty";
	}
	
	echo "<pre>";
	print_r($filter);
	echo "</pre>";
	*/
	
	//echo "Count".count($filter);
	
	if(count($filter)>0){
		if($filter['date']!=""){
			$filter_str .= " AND CAST( ts_main.`date` AS Date ) = '{$filter['date']}' ";
		}
		if($filter['tech']!=""){
			$filter_str .= " AND ts_main.`staff_id` = '{$filter['tech']}' ";
		}
		if($filter['vehicle']!=""){
			$filter_str .= " AND ts_main.`vehicle` = '{$filter['vehicle']}' ";
		}
		$join_str = "";
	}else{
		$join_str = "
			INNER JOIN (
				SELECT MAX(  `date` ) AS latestDate,  `vehicle` 
				FROM  `tech_stock` 
				WHERE  `country_id` ={$_SESSION['country_default']}
				GROUP BY  `vehicle`
			) AS ts ON ts_main.`vehicle` = ts.`vehicle` 
			AND ts_main.`date` = ts.latestDate
		";
	}
	
	if(is_numeric($offset) && is_numeric($limit))
	{
		$limit_str = " LIMIT {$offset}, {$limit}";
	}
		
	$sql = "
		{$sel_str}
		FROM  `tech_stock` AS ts_main
		{$join_str}
		LEFT JOIN `staff_accounts` AS sa ON ts_main.`staff_id` = sa.`StaffID` 
		LEFT JOIN  `vehicles` AS v ON ts_main.`vehicle` = v.`vehicles_id` 
		WHERE ts_main.`country_id` = {$_SESSION['country_default']}
		{$filter_str}
		AND v.`tech_vehicle` = 1
		AND v.`active` = 1
		{$order_by_str}
		{$limit_str}
	";
	return mysql_query($sql);
}

function getStocks(){
	return mysql_query("
		SELECT *
		FROM `stocks` 
		WHERE `country_id` = {$_SESSION['country_default']}
		AND display = 1
		ORDER BY `sort_index` ASC
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

$date = ($_REQUEST['date']!="")?date('Y-m-d',strtotime(str_replace('/','-',mysql_real_escape_string($_REQUEST['date'])))):'';
$tech = mysql_real_escape_string($_REQUEST['tech']);
$vehicle = mysql_real_escape_string($_REQUEST['vehicle']);

/*
$filter = array(
	'date' => $date,
	'tech' => $tech,
	'vehicle' => $vehicle
);
*/

if($date!=""){
	$filter['date'] = $date;
}

if($tech!=""){
	$filter['tech'] = $tech;
}

if($vehicle!=""){
	$filter['vehicle'] = $vehicle;	
}


/*
echo "<pre>";
print_r($filter);
echo "</pre>";
*/

// pagination script
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;
$this_page = $_SERVER['PHP_SELF'];

$params = "&vehicle={$vehicle}&driver={$driver}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$ts_sql = getTechStock($offset,$limit,'',$filter);
$ptotal = mysql_num_rows(getTechStock('','','',$filter));

?>

<style>
.jalign_left{
	text-align:left;
}

.txt_hid, .btn_update{
	display:none;
}

.jRedColorBold{
	color: red;
    font-weight: bold;
}
</style>
<div id="mainContent">    

	<div class="sats-middle-cont">
  
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="<?php echo $title ?>" href="/tech_stock.php"><strong><?php echo $title; ?></strong></a></li>
		  </ul>
		</div>	
		<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
		 <?php
		if($_GET['ts_sub']==1){ ?>
			<div class="success">Tech Stock Submitted</div>
		<?php
		}
		?>

		<div class="aviw_drop-h aviw_drop-vp" style="border: 1px solid #ccc; border-bottom: none;">	
			<form method="post">
				<div class="fl-left">
					<label>Date:</label>
					<input type="text" name="date" class="datepicker" value="<?php echo ($filter['date'])?date('d/m/Y',strtotime($filter['date'])):''; ?>" />
				</div>							
				<div class="fl-left" style="float:left;">
					<?php $t_sql = getTechStock('','','ts_main.`staff_id`',$filter); ?>
					<label>Technician:</label>
					<select name="tech">
						<option value="">----</option>
						<?php						
						while($t = mysql_fetch_array($t_sql)){ ?>
						<option value="<?php echo $t['staff_id'] ?>" <?php echo ($t['staff_id']==$filter['tech'])?'selected="selected"':''; ?>><?php echo $t['FirstName'].' '.$t['LastName']; ?></option>
						<?php
						}
						?>
					</select>
				</div>
				<div class="fl-left" style="float:left;">
					<?php $v_sql = getTechStock('','','ts_main.`vehicle`',$filter); ?>
					<label>Vehicle:</label>
					<select name="vehicle">
						<option value="">----</option>
						<?php						
						while($v = mysql_fetch_array($v_sql)){ ?>
						<option value="<?php echo $v['vehicle'] ?>" <?php echo ($v['vehicle']==$filter['vehicle'])?'selected="selected"':''; ?>><?php echo $v['number_plate']; ?></option>
						<?php
						}
						?>
					</select>
				</div>				
				<div class="fl-left" style="float:left;">
					<input type="submit" class="submitbtnImg" value="Search" name="btn_search">
				</div>
			</form>			
		</div>
		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;">
			<tr class="toprow jalign_left">
				<th>Technician</th>
				<th>Vehicle</th>
				<th>Day</th>
				<th>Date</th>
				<?php
				$stock_sql = getStocks();
				while( $s = mysql_fetch_array($stock_sql) ){ ?>
					<th><?php echo $s['display_name']; ?></th>
				<?php	
				}
				?>				
				<th>Details</th>
			</tr>
			<?php
			while($ts = mysql_fetch_array($ts_sql)){ ?>
				<tr class="body_tr jalign_left">
					<td>
						<span class="txt_lbl"><?php echo $ts['FirstName'].' '.strtoupper(substr($ts['LastName'],0,1))."."; ?></span>
					</td>
					<td>
						<span class="txt_lbl"><?php echo $ts['number_plate']; ?></span>
					</td>
					<td>
						<span class="txt_lbl"><?php echo date("l",strtotime($ts['date'])); ?></span>
					</td>
					<td>
						<span class="txt_lbl"><?php echo date("d/m/Y H:i",strtotime($ts['date'])); ?></span>
					</td>
					<?php
					$stock_sql = getStocks();
					while( $s = mysql_fetch_array($stock_sql) ){ ?>
						<td>
							<?php
							$ts_sql2 = getTechStockItems($ts['tech_stock_id'],$s['stocks_id']);
							$ts2 = mysql_fetch_array($ts_sql2);
							$ts2['quantity'];
							$tot_array[$s['stocks_id']] = $tot_array[$s['stocks_id']]+$ts2['quantity'];
							?>
							<span class="txt_lbl <?php 
							echo ( 
								( $s['stocks_id']==7 && $ts2['quantity']<250 ) ||
								( $s['stocks_id']==2 && $ts['electrician']==0 && $ts2['quantity']<40 ) ||
								( $s['stocks_id']==1 && $ts['electrician']==1 && $ts2['quantity']<40 ) ||
								( $s['stocks_id']==4 && $ts['electrician']==1 && $ts2['quantity']<15 ) ||
								( $s['stocks_id']==5 && $ts2['quantity']<10 )
							)?'jRedColorBold':''; 
							?>">	
							<?php echo $ts2['quantity']; ?>
							</span>
						</td>
					<?php	
					}
					?>						
					<td style="border-right: 1px solid #ccc;">
						<span class="txt_lbl"><a href="/update_tech_stock.php?id=<?php echo $ts['staff_id']; ?>&tech_stock_id=<?php echo $ts['tech_stock_id']; ?>">More</a></span>
					</td>									
				</tr>
			<?php
			}
			?>
			<tr class="aviw_drop-h">
				<td colspan="4" style="text-align:left;"><strong>TOTAL</strong></td>
				<?php
				foreach($tot_array as $val){ ?>
					<td style="text-align:left;"><strong><?php echo $val; ?></strong></td>
				<?php	
				}
				?>
				<td>&nbsp;</td>
			</tr>
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
