<?php

$title = "Cron Report";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

//include('inc/no_id_properties_functions.php');

function getCronLogs($start,$limit,$et,$from,$to){

	$str = "";
	
	if(is_numeric($et)){
		$str .= " AND cl.`type_id` = {$et} ";
	}
	
	if( $from!="" && $to!="" ){
		$str .= " AND CAST( cl.`started` AS Date ) BETWEEN '{$from}' AND '{$to}' ";
	}	
	
	$str .= " ORDER BY cl.`started` DESC ";

	if(is_numeric($start) && is_numeric($limit))
	{
		$str .= " LIMIT {$start}, {$limit} ";
	}
	
	$sql = "
		SELECT *
		FROM `cron_log` AS cl
		LEFT JOIN `cron_types` AS ct ON cl.`type_id` = ct.`cron_type_id`
		WHERE cl.`country_id` = {$_SESSION['country_default']}
		AND  ct.`active` = 1
		{$str}
	";

	return mysql_query($sql);

}


function getCronTypes(){
	
	return mysql_query("
		SELECT *
		FROM `cron_types`
		WHERE `active` = 1
		ORDER BY `type_name` ASC
	");
	
}

$today = date('Y-m-d');

$from = ($_REQUEST['from']!="")?jFormatDateToBeDbReady($_REQUEST['from']):$today;
$to = ($_REQUEST['to']!="")?jFormatDateToBeDbReady($_REQUEST['to']):$today;

$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;
$this_page = $_SERVER['PHP_SELF'];
$next_link = "{$this_page}?offset=".($offset+$limit);
$prev_link = "{$this_page}?offset=".($offset-$limit);

$et = $_POST['email_type'];

$plist = getCronLogs($offset,$limit,$et,$from,$to);
$ptotal = mysql_num_rows(getCronLogs('','',$et,$from,$to));



?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.greyBgRow{
	background-color:#eeeeee
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
		
		
		?>

		<div class="aviw_drop-h aviw_drop-vp" style="border: 1px solid #cccccc;">
			
			<form method="post">
			
				<div class="fl-left">				
					<label>Cron Type:</label>
					<select id="email_type" name="email_type">
						<option value="">--- Select ---</option>
						<?php
						$ct_sql = getCronTypes();
						while( $row = mysql_fetch_array($ct_sql) ){ ?>
							<option value="<?php echo $row['cron_type_id']; ?>"><?php echo $row['type_name']; ?></option>
						<?php
						}
						?>
						
					</select>				
				</div>
				
				
				<div class='fl-left'>
					<label>From:</label><input type=label name='from' value='<?php echo ($from)?date('d/m/Y',strtotime($from)):''; ?>' class='addinput searchstyle datepicker'>		
				</div>
				
				<div class='fl-left'>
					<label>To:</label><input type=label name='to' value='<?php echo ($to)?date('d/m/Y',strtotime($to)):''; ?>' class='addinput searchstyle datepicker'>		
				</div>
			
			  
			  <div class="fl-left" style="float: left;">
				<input type="submit" class="submitbtnImg" value="Go" name="btn_search">
			  </div>
			  

		  
		  </form>
		  
		  
		  
		</div>
		
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
		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">
			<tr class="toprow jalign_left">
				<th>Name</th>
				<th>Description</th>
				<th>Day</th>
				<th>Date</th>				
				<th>Time</th>
			</tr>
				<?php
				
				
				if(mysql_num_rows($plist)>0){
					$i = 0;
					while($row = mysql_fetch_array($plist)){
				?>
						<tr class="body_tr jalign_left <?php echo ( $i%2 == 0 )?'':'greyBgRow'; ?>">
							<td><?php echo $row['type_name']; ?></td>
							<td><?php echo $row['description']; ?></td>
							<td>
								<?php echo date("l",strtotime($row['started'])); ?>
							</td>
							<td>
								<?php echo date("d/m/Y",strtotime($row['started'])); ?>
							</td>							
							<td>	
								<?php 
								switch($_SESSION['country_default']){
									case 1:
										$local_time = "AEST";
										$plus2h = "";
									break;
									case 2:
										$local_time = "NZST";
										$plus2h = " +2 hours";
									break;
								}	
								echo date("H:i",strtotime($row['started'].$plus2h))." ".$local_time; 
								?>
							</td>
						</tr>
						
				<?php
					$i++;
					}
				}else{ ?>
					<td colspan="4" align="left">Empty</td>
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