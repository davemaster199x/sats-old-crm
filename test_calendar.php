<?php 
include($_SERVER['DOCUMENT_ROOT'].'/inc/init_for_ajax.php');
include($_SERVER['DOCUMENT_ROOT'].'/inc/ws_sms_class.php');

$crm = new Sats_Crm_Class;
$country_id = $_SESSION['country_default'];
?>
<h1>Test Calendar</h1>
<?php
$current_date = ($_REQUEST['current_date']!='')?$_REQUEST['current_date']:date("Y-m-d");
$this_month_first_day = date('Y-m-1',strtotime($current_date));
$this_month_first_day_txt = date('l',strtotime($this_month_first_day));
$this_month_last_day = date('t',strtotime($current_date));
$this_month_year = date('Y',strtotime($current_date));
$this_month = date('m',strtotime($current_date));
$this_month_txt = date('F',strtotime($current_date));
$prev_cal = date('Y-m-1',strtotime($current_date." -1 month"));
$next_cal = date('Y-m-1',strtotime($current_date." +1 month"));
$today = date('Y-m-d');

$todays_day = date('D');
$saturday = date("Y-m-d",strtotime("+1 day"));
$next_monday = date("Y-m-d",strtotime("+3 day"));

echo "Todays Day: {$todays_day}<br />";
echo $sql_date_text = " AND j.`date` BETWEEN '{$saturday}' AND '{$next_monday}' ";
?>

<h2>
	<a href="test_calendar.php?current_date=<?php echo $prev_cal; ?>">prev</a> 
	<?php echo "{$this_month_txt}  {$this_month_year}"; ?> 
	<a href="test_calendar.php?current_date=<?php echo $next_cal; ?>">next</a>
</h2>

<a href="test_calendar.php"><p>Back to Current Month</p></a>
<?
$day_arr = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
?>

<table id="calendar_tbl">
	<tr>
	<?php
	foreach( $day_arr as $day ){ ?>
		<td><?php echo $day; ?></td>
	<?php
	}
	?>
	</tr>
	<?
	$day_num = 1;
	$start_cal = false;
	for( $i=1;$i<=6;$i++ ){ ?>
	<tr>
	<?php
	foreach( $day_arr as $day ){
		if( $day == $this_month_first_day_txt ){
			$start_cal = true;			
		}
		if( $day_num == $this_month_last_day+1 ){
			$start_cal = false;			
		}
		
		// days
		if( $start_cal==true ){
			$this_date = date('Y-m-d',strtotime("{$this_month_year}-{$this_month}-{$day_num}"));
			$day_txt = $day_num;
			$day_num++;
		}else{
			$this_date = '';
			$day_txt = '&nbsp;';			
		} ?>	
		<td <?php echo ( $this_date == $today )?'class="today"':''; ?>><?php echo $day_txt; ?></td>		
	<?php
	}
	?>
	</tr>
	<?php
	}
	?>
</table>

<style>
#calendar_tbl{
	border-collapse: collapse;
}
#calendar_tbl td{
	border: 1px solid;
	padding: 20px;
}
.today{
	background-color: #00AEEF;
	color: #FFFFFF;
}
</style>

