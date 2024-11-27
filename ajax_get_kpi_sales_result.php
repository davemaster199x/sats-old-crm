<?php	
include('inc/init_for_ajax.php');
// intiate class
$crm = new Sats_Crm_Class;
$country_id = CURRENT_COUNTRY;

$from = date('Y-m-01');
$to = date('Y-m-t');

// distint sales rep
$sr_sql = mysql_query("
	SELECT DISTINCT a.`salesrep` , sa.`FirstName` , sa.`LastName`, a.`salesrep`
	FROM `property_services` AS ps
	LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	LEFT JOIN `staff_accounts` AS sa ON sa.`StaffID` = a.`salesrep`
	WHERE a.`salesrep` !=0
	AND p.`deleted` = 0 
	AND a.`status` = 'active'
	AND a.`salesrep` IS NOT NULL
	AND p.`property_id` != 0
	AND p.`property_id` IS NOT NULL
	AND a.`country_id` = {$country_id}
	ORDER BY sa.`FirstName` ASC
");

$i = 1;
$max_row = mysql_num_rows($sr_sql);	
while($sr = mysql_fetch_array($sr_sql)){ 

	// date
	//$country_id = 1;	
	$sales_result_tot = 0;
	
	$sales_arr[] = array(
		'saleperson_id' => $sr['salesrep'],
		'salesperson_name' => "{$sr['FirstName']} {$sr['LastName']}"
	);
	
}

function get_num_services($salesrep,$ajt,$from,$to,$country_id){
	
	
	$str = "";					
	if( $from!='all' && $to!='all' ){
		$from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
		$to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
		$str = "AND CAST(ps.`status_changed` AS DATE) BETWEEN '{$from2}' AND '{$to2}'";
	}
	
	
	$sql_str = "
		SELECT COUNT(ps.`property_services_id`) AS ps_count
		FROM `property_services` AS ps
		LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE a.`salesrep` ={$salesrep}
		AND ps.`alarm_job_type_id` ={$ajt}
		AND ps.`service` = 1
		AND a.`country_id` = {$country_id}
		AND a.`agency_id` != 3712
		{$str}
	";
	
	$sql = mysql_query($sql_str);
	$row = mysql_fetch_array($sql);

	return $row['ps_count'];
	
}

function getDynamicServices(){
	return mysql_query("
		SELECT *
		FROM `alarm_job_type`
		WHERE `active` =1
	");
}
?>

<div class='jdiv'>
<?php
// services
$ajt_sql2 = getDynamicServices();
$ajt_arr = [];
while($ajt2 = mysql_fetch_array($ajt_sql2)){
	
	switch($ajt2['id']){
		case 8:
			$ajt_name = 'SA SS';
		break;
		case 9:
			$ajt_name = 'SA SS CW';
		break;
		case 11:
			$ajt_name = 'SA WM';
		break;
		case 12:
			$ajt_name = 'SA (IC)';
		break;
		case 13:
			$ajt_name = 'SA SS (IC)';
		break;
		case 14:
			$ajt_name = 'SA CW SS (IC)';
		break;
		default:
			$ajt_name = $ajt2['short_name'];
	}
	
	$ajt_arr[] = array(
		'id' => $ajt2['id'],
		'type' => $ajt2['type'],
		'short_name' => $ajt2['short_name'],
		'short_name_wspace' => $ajt_name
	);

}

$row_count = mysql_num_rows($ajt_sql2);
?>


<?php
// SALES RESULT
?>
<table id="jtable1" border=0 cellspacing=0 cellpadding=5 class='table-center tbl-fr-red jtable' style="width:auto;">
	<tr>					
		<th colspan="<?php echo (($row_count)+2); ?>" class="row_bg_color">Sales Results</th>			
	</tr>
	<tr style="background-color: #eeeeee;">
		<td><strong>Staff</strong></td>
		<?php
		foreach( $ajt_arr as $ajt ){ ?>
			<td><strong><?php echo $ajt['short_name_wspace']; ?></strong></td>
		<?php	
		}
		?>
		<td><strong>Total</strong></td>	
	</tr>
	<?php	
	foreach( $sales_arr as $sales ){
	$sales_result_tot = 0;
	?>
		<tr>
			<td>
				<?php  echo $sales['salesperson_name']; ?>
			</td>
			<?php
			foreach( $ajt_arr as $ajt ){ ?>
				<td>
					<?php 
					$sa = get_num_services($sales['saleperson_id'],$ajt['id'],$from,$to,$country_id);
					echo ($sa>0)?$sa:'';
					$sales_result_tot += $sa;
					?>
				</td>
			<?php	
			}
			?>
			<td>
				<?php
				echo  ( $sales_result_tot>0 )?$sales_result_tot:'';
				$sales_result_overall_tot += $sales_result_tot;
				?>
			</td>
		</tr>
	<?php	
	}?>
	<tr style="background-color: #eeeeee;">
		<td><strong>TOTAL</strong></td>
		<?php
		foreach( $ajt_arr as $ajt ){ ?>
			<td>&nbsp;</td>
		<?php
		}
		?>
		<td><strong><?php echo $sales_result_overall_tot; ?></strong></td>
	</tr>	
</table>

</div>