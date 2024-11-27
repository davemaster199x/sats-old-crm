<?

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

$title = "Sales Activity Report";

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');




// date
$all = $_REQUEST['all'];
$from = ($_REQUEST['from'])?$_REQUEST['from']:date("01/m/Y");
$to = ($_REQUEST['to'])?$_REQUEST['to']:date("t/m/Y");




$prev_day = array(
	'from' => date('d/m/Y', strtotime('-1 day', strtotime(str_replace("/","-",$from)))),
	'to' => date('d/m/Y', strtotime('-1 day', strtotime(str_replace("/","-",$from)))),
	'title' => '<span class="arw-lft2">&nbsp;</span> Previous Day'
);

$next_day = array(
	'from' => date('d/m/Y', strtotime('+1 day', strtotime(str_replace("/","-",$from)))),
	'to' => date('d/m/Y', strtotime('+1 day', strtotime(str_replace("/","-",$from)))),
	'title' => 'Next Day <span class="arw-rgt2">&nbsp;</span>',
	'css' => 'float: right;'
);


# Create predefined date ranges
$today = date('d/m/Y');

$date_ranges = array();

$date_ranges[] = array(
'title' => 'All',
'from' => 'all',
'to' => 'all'
);

$date_ranges[] = array(
'title' => 'Today',
'from' => date('d/m/Y'),
'to' => date('d/m/Y')
);

$date_ranges[] = array(
'title' => 'Yesterday',
'from' => date('d/m/Y', (strtotime('-1 days'))),
'to' => date('d/m/Y', (strtotime('-1 days')))
);

$date_ranges[] = array(
'title' => 'Last Week',
'from' => date('d/m/Y', strtotime('previous week Monday') ),
'to' => date('d/m/Y', strtotime('previous week Sunday') )
);

$date_ranges[] = array(
'title' => 'Next Week',
'from' => $today,
'to' => date('d/m/Y', (strtotime('+7 days')))
);



$date_ranges[] = array(
'title' => date("F",mktime(0,0,0, (date("n") - 1 + 12) % 12, 1)),
'from' => date("01/m/Y",mktime(0,0,0, (date("n") - 1 + 12) % 12, 1)),
'to' => date("t/m/Y",mktime(0,0,0, (date("n") - 1 + 12) % 12, 1))
);


$date_ranges[] = array(
'title' => date("F",mktime(0,0,0, (date("n") - 2 + 12) % 12, 1)),
'from' => date("01/m/Y",mktime(0,0,0, (date("n") - 2 + 12) % 12, 1)),
'to' => date("t/m/Y",mktime(0,0,0, (date("n") - 2 + 12) % 12, 1))
);

$date_ranges[] = array(
'title' => date("F",mktime(0,0,0, (date("n") - 3 + 12) % 12, 1)),
'from' => date("01/m/Y",mktime(0,0,0, (date("n") - 3 + 12) % 12, 1)),
'to' => date("t/m/Y",mktime(0,0,0, (date("n") - 3 + 12) % 12, 1))
);


# Display dates in dd/mm/yyyy
$to_display = convertDateAus($to);
$from_display = convertDateAus($from);


$staff_id = (isset($_GET['sid']) ? (int)$_GET['sid']: "z");
$tech_id = (isset($_GET['tid']) ? (int)$_GET['tid']: "z");

# Get Staff details for display if needed
if($staff_id === 0)
{
	$staff_details['FirstName'] = "SATS System";
}
elseif(is_int($staff_id))
{
	$staff_details = $user->getUserDetails($staff_id);
}

# Get Tech details for display if needed
if($tech_id === 0)
{
	$tech_details['first_name'] = "Unassigned";
}
elseif(is_int($tech_id))
{
	$tech_details = $user->getTechDetails($tech_id);
}


# Staff and tech id's to filter
$staff_filter = array(
'staff_id' => $staff_id,
'tech_id' => $tech_id
);

$report_params = array('from' => $from, 'to' => $to, 'staff_id' => $staff_id, 'tech_id' => $tech_id);

$report = new Report();





function getSalesRepAgencyLogs($offset,$limit,$from,$to,$salesrep,$state){
	
	
	if( $from!='' && $to!='' ){
		
		$from2 = date('Y-m-d', strtotime( str_replace( "/", "-", $from ) ) );
		$to2 = date('Y-m-d', strtotime( str_replace( "/", "-", $to ) ) );
		
		$filter .= " AND ael.`eventdate` BETWEEN '{$from2}' AND '{$to2}' ";
	}
	
	if( $salesrep!='' ){		
		$filter .= " AND ael.`staff_id` = {$salesrep} ";
	}
	
	if( $state!='' ){		
		$filter .= " AND a.`state` = '{$state}' ";
	}
	
	if( is_numeric($offset) && is_numeric($limit) ){
		$limit_str = "LIMIT {$offset}, {$limit}";
	}
	
	// Sales, Brad Hay, Gavin Coulson and Shaquille smith
	$sql_str = "
		SELECT *, ael.`comments` AS ael_comments, a.`status` AS a_status
		FROM `agency_event_log` AS ael
		LEFT JOIN `agency` AS a ON ael.`agency_id` = a.`agency_id`
		LEFT JOIN `staff_accounts` AS sa ON ael.`staff_id` = sa.`StaffID`
		WHERE sa.deleted =0
		AND sa.active =1
		AND (
			sa.`ClassID` = 5 OR
			sa.`StaffID` = 2165 OR
			sa.`StaffID` = 2189 OR 
			sa.`StaffID` = 2296
		)
		AND ael.`contact_type` !=  'Agency Update'
		AND a.`country_id` ={$_SESSION['country_default']} 
		{$filter}
		{$limit_str}
	";
	return mysql_query($sql_str);
}



$salesrep = mysql_real_escape_string($_GET['salesrep']);
$state = mysql_real_escape_string($_GET['state']);


// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;

$this_page = $_SERVER['PHP_SELF'];
$params = "&from=".$_GET['from']."&to=".$_GET['to']."&salesrep=".$salesrep."&state=".$state."&get_sats=1";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$plist = getSalesRepAgencyLogs($offset,$limit,$from,$to,$salesrep,$state);
$ptotal = mysql_num_rows(getSalesRepAgencyLogs('','',$from,$to,$salesrep,$state));

?>

<div id="mainContent">

<div class="sats-middle-cont">
	
	<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
   <table cellpadding=0 cellspacing=0 >
    <tr class="tbl-view-prop">
        <td>
          <form action="" id="date_range" method="get" style="margin: 0;">
            <div class="aviw_drop-h">
            
			  <div class="fl-left">
					<label>Report from:</label>
					<input type="text" name="from" class="datepicker addinput searchstyle" style="width:85px;" value="<?php echo $from; ?>">
			  </div>
			
			  <div class="fl-left">
				   <label>to:</label>
				   <input type="text" name="to" class="datepicker addinput searchstyle" style="width:85px;" value="<?php echo $to; ?>">
			  </div>
			  
				<div class="fl-left">
				   <label>Sales Rep:</label>				  
				  <select name="salesrep" class="addinput">
						<option value="">--- Select ---</option>
						<?php
							$salesrep_sql = mysql_query("
								SELECT DISTINCT(ca.`staff_accounts_id`), sa.`FirstName`, sa.`LastName`
								FROM staff_accounts AS sa
								INNER JOIN `country_access` AS ca ON (
									sa.`StaffID` = ca.`staff_accounts_id` 
									AND ca.`country_id` ={$_SESSION['country_default']}
								)
								WHERE sa.deleted =0
								AND sa.active =1											
								AND  sa.`ClassID` = 5
								ORDER BY sa.`FirstName`
							");
						while($salesrep_row = mysql_fetch_array($salesrep_sql)){ ?>
							<option value="<?php echo $salesrep_row['staff_accounts_id'] ?>" <?php echo ($salesrep_row['staff_accounts_id']==$salesrep)?'selected="selected"':''; ?>><?php echo $salesrep_row['FirstName'] .' '. $salesrep_row['LastName'] ?></option>
						<?php 
						}
						?>
					</select>
				</div>
			  
			   <div class="fl-left">
				   <label>State:</label>
				   <select name="state">
						<option value="">--- Select ---</option>
						<?php
							$salesrep_sql = mysql_query("
							SELECT DISTINCT a.`state`
							FROM `agency` AS a 
							WHERE a.`country_id` ={$_SESSION['country_default']}
							AND a.`state` != ''
							ORDER BY a.`state`
						");
						while($salesrep = mysql_fetch_array($salesrep_sql)){ ?>
							<option value="<?php echo $salesrep['state']; ?>" <?php echo ($salesrep['state']==$state)?'selected="selected"':''; ?>><?php echo $salesrep['state']; ?></option>
						<?php 
						}
						?>
				   <select>
			  </div>
  
  <div class="fl-left pull-left">
  <?php if(is_int($staff_id)): ?>
	<input type="hidden" name="sid" value="<?php echo $staff_id; ?>" class="submitbtnImg">	
	<?php endif; ?>	
	<?php if(is_int($tech_id)): ?>
	<input type="hidden" name="tid" value="<?php echo $tech_id; ?>" class="submitbtnImg">	
	<?php endif; ?>
	<input type="hidden" name="get_sats" value="1" />
	<input type="submit" value="Get Stats" class="submitbtnImg">
  </div>
                       
  <div class="fl-left">
    <a href="export_sales_activity.php?from=<?php echo $from ?>&to=<?php echo $to ?>&salesrep=<?php echo $_GET['salesrep'] ?>&state=<?php echo $state ?>" class="submitbtnImg export">Export</a>
  </div>
  
</div>

<div class="aviw_drop-h qlnk">
<span class="float-left content-black"><?php echo $report->generateLink($prev_day, $staff_filter); ?></span>
Quick Links&nbsp;|&nbsp;<?php foreach($date_ranges as $index=>$range): ?>
		<?php echo $report->generateLink($range, $staff_filter); ?>
		<? if($index < sizeof($date_ranges) - 1): ?>
		&nbsp;|&nbsp;
		<? endif; ?>		
		<?php endforeach; ?>	
        <span class="float-right pg-tp-rg content-black"><?php echo $report->generateLink($next_day, $staff_filter); ?></span>		
</div>
            
 </form>           
            
     </td>
  </tr>
</table>

<?php
// REPORT starts here
if( $_GET['get_sats']==1 ){ ?>





	 <div class="report">
    
		<h2 class="heading">
			Report <?php echo $from; ?> for <?php echo $to; ?>
		</h2>
			<?php
		# Alert that viewing Staff or Tech indivudal reports if neded, and offer to reset
		if(is_int($staff_id))
		{
			echo "<div class='success'>Currently viewing statistics for staff member: " . $staff_details['FirstName'] . " " . $staff_details['LastName'] . " " . $report->generateLink(array('from' => $from, 'to' => $to, 'title' => 'back to all')) . "</div>";
		}
		if(is_int($tech_id))
		{
			echo "<div class='success'>Currently viewing statistics for technician: " . $tech_details['first_name'] . " " . $tech_details['last_name'] . " " . $report->generateLink(array('from' => $from, 'to' => $to, 'title' => 'back to all')) . "</div>";
		}
		
		?>

		
	

	</div>

		<table border=0 cellspacing=0 cellpadding=5 width=100% class='tbl-sd jtable' style="margin-top: 30px; margin-top: 30px;">
			<thead>
				<tr class="toprow">				
					<th>Date</th>
					<th>Sales Rep</th>
					<th>Type</th>
					<th>Agency</th>
					<th>Status</th>
					<th>Comment</th>
					<th>Next Contact</th>					
				</tr>
			</thead>	
			<tbody>
			<?php	
			if( mysql_num_rows($plist)>0 ){
				while( $srl = mysql_fetch_array($plist) ){ ?>
					<tr>
						<td><?php echo date('d/m/Y',strtotime($srl['eventdate'])); ?></td>
						<td><?php echo $srl['FirstName'] .' '. $srl['LastName'] ?></td>
						<td><?php echo $srl['contact_type']; ?></td>
						<td><?php echo $srl['agency_name']; ?></td>
						<td><?php echo $srl['a_status']; ?></td>
						<td><?php echo $srl['ael_comments']; ?></td>
						<td><?php echo ( $srl['next_contact']!='0000-00-00' && $srl['next_contact']!='' && $srl['next_contact']!='1970-01-01' )?date('d/m/Y',strtotime($srl['next_contact'])):''; ?></td>
					</tr>
				<?php	
					}
			}else{ ?>
				<tr><td colspan="100%">Empty</td></tr>
			<?php	
			}
			?>
			</tbody>
		</table>
		<?php
									
		// Initiate pagination class
		$jp = new jPagination();
		
		$per_page = $limit;
		$page = ($_GET['page']!="")?$_GET['page']:1;
		$offset = ($_GET['offset']!="")?$_GET['offset']:0;	
		
		echo $jp->display($page,$ptotal,$per_page,$offset,$params);
		
		?>





<?php	
}else{ ?>

	<h2 style="text-align:left;">Press 'Get Stats' to Display Results</h2>

<?php	
}
?>	
    
    
 
	
</div>

</div>

<br class="clearfloat" />

<style>
.jtable tr td, .jtable tr th{
	border: 1px solid #cccccc;
	text-align:left;
}
</style>
<script>
jQuery(document).ready(function(){
	
	// hide users that has no data
	jQuery(".td_gross_tot").each(function(){
		var gross_tot = parseInt(jQuery(this).html());
		if(gross_tot==0){
			jQuery(this).parents("tr:first").hide();
		}		
	});
	
});
</script>
</body>
</html> 
