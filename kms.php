<?php

$title = "Kms";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

function get_kms($vehicle,$driver,$offset,$limit){

	if($vehicle!=""){
		$str .= "AND k.`vehicles_id` = {$vehicle}";
	}
	if($driver!=""&&$vehicle!=""){
		$str .= "AND v.`StaffID` = {$driver}";
	}else if($driver!=""&&$vehicle==""){
		$str .= "AND v.`StaffID` = {$driver}";
	}
	
	$str .= " ORDER BY k.`kms_updated` DESC";
	if(is_numeric($offset) && is_numeric($limit))
	{
		$str .= " LIMIT {$offset}, {$limit}";
	}
		
	$sql = "
		SELECT *
		FROM `kms` AS k
		LEFT JOIN `vehicles` AS v ON k.`vehicles_id` = v.`vehicles_id`
		LEFT JOIN `staff_accounts` AS sa ON v.`StaffID` = sa.`StaffID`
		WHERE v.`country_id` = {$_SESSION['country_default']}
		{$str}
	";
	return mysql_query($sql);
}


$vehicle = $_REQUEST['vehicle'];
$driver = $_REQUEST['driver'];

// pagination script
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;
$this_page = $_SERVER['PHP_SELF'];

$params = "&vehicle={$vehicle}&driver={$driver}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

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
			<li class="other first"><a title="Kms" href="/kms.php"><strong>Kms</strong></a></li>
		  </ul>
		</div>	
		<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
		 <?php
		if($_GET['success']==1){ ?>
			<div class="success">Update Successful</div>
		<?php
		}
		?>

		<div class="aviw_drop-h aviw_drop-vp" style="border: 1px solid #ccc; border-bottom: none;">	
			<form method="post">
				<div class="fl-left">
					<label>Vehicle:</label>
					<select name="vehicle">
						<option value="">----</option>
						<?php
						$v_sql = mysql_query("
							SELECT DISTINCT (
							k.`vehicles_id`
							), v.`number_plate`
							FROM `kms` AS k
							INNER JOIN `vehicles` AS v ON k.`vehicles_id` = v.`vehicles_id`
						");
						while($v = mysql_fetch_array($v_sql)){ ?>
						<option value="<?php echo $v['vehicles_id'] ?>"><?php echo $v['number_plate']; ?></option>
						<?php
						}
						?>
					</select>
				</div>							
				<div class="fl-left" style="float:left;">
					<label>Driver:</label>
					<select name="driver">
						<option value="">----</option>
						<?php
						$v_sql = mysql_query("
							SELECT DISTINCT (
							k.`vehicles_id`
							), sa.`StaffID`, sa.`FirstName` , sa.`LastName`
							FROM `kms` AS k
							INNER JOIN `vehicles` AS v ON k.`vehicles_id` = v.`vehicles_id`
							INNER JOIN `staff_accounts` AS sa ON v.`StaffID` = sa.`StaffID`
						");
						while($v = mysql_fetch_array($v_sql)){ ?>
						<option value="<?php echo $v['StaffID'] ?>"><?php echo $v['FirstName'].' '.$v['LastName']; ?></option>
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
				<th>Driver</th>
				<th>Vehicle</th>
				<th>Kms</th>
				<th>Updated</th>
			</tr>
			<?php
			$kms_sql = get_kms($vehicle,$driver,$offset,$limit);
			$ptotal = mysql_num_rows(get_kms($vehicle,$driver,'',''));
			while($kms = mysql_fetch_array($kms_sql)){ ?>
				<tr class="body_tr jalign_left">
					<td>
						<span class="txt_lbl"><?php echo ($kms['FirstName']!="")?$kms['FirstName'].' '.$kms['LastName']:'----'; ?></span>
						<input type="hidden" name="kms_id" class="kms_id" value="<?php echo $kms['kms_id']; ?>" />
					</td>
					<td>
						<span class="txt_lbl"><?php echo ($kms['number_plate']!="")?$kms['number_plate']:'----'; ?></span>
					</td>
					<td>
						<span class="txt_lbl"><?php echo ($kms['kms']!="")?$kms['kms']:'----'; ?></span>
					</td>
					<td style="border-right: 1px solid #ccc;">
						<span class="txt_lbl"><?php echo ($kms['kms_updated']!="")?date("d/m/Y",strtotime($kms['kms_updated'])):'----'; ?></span>
					</td>									
				</tr>
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
