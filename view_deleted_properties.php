<?

$title = "View Properties";

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');

$crm = new Sats_Crm_Class();


$phrase = trim($_REQUEST['phrase']);
$agency = $_REQUEST['agency'];
$from = $_REQUEST['from'];
$to = $_REQUEST['to'];

$start = (intval($_REQUEST['start']) > 0 ? intval($_REQUEST['start']) : 0);

// header sort parameters
$sort = $_REQUEST['sort'];
$order_by = $_REQUEST['order_by'];

$sort = ($sort)?$sort:'p.`deleted_date`';
$order_by = ($order_by)?$order_by:'DESC';

// pagination script
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;
$this_page = $_SERVER['PHP_SELF'];
$params = "&sort={$sort}&order_by={$order_by}&from={$from}&to={$to}&agency={$agency}&phrase={$phrase}&postcode_region_id={$filterregion}";
$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


$jparams = array(
	'custom_select' => '
		p.`property_id`,
		p.`address_1` AS p_address_1,
		p.`address_2` AS p_address_2,
		p.`address_3` AS p_address_3,
		p.`state` AS p_state,
		p.`postcode` AS p_postcode,
		p.`agency_deleted`,
		p.`deleted_date`,

		a.`agency_id`,
		a.`agency_name`
	',
	'country_id' => $country_id,
	'agency_id' => $agency,
	'phrase' => $phrase,
	'deleted_date_from' => $from,
	'deleted_date_to' => $to,
	'p_deleted' => 1,
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'custom_sort' => 'p.`address_2` ASC, p.`address_1` ASC',
	'echo_query' => 0
);
$propertylist = $crm->getPropertyOnly($jparams);

$jparams = array(
	'custom_select' => '
		p.`property_id`
	',
	'country_id' => $country_id,
	'agency_id' => $agency,
	'phrase' => $phrase,
	'deleted_date_from' => $from,
	'deleted_date_to' => $to,	
	'p_deleted' => 1
);
$ptotal = mysql_num_rows($crm->getPropertyOnly($jparams));


//$propertylist = getPropertyList2($agency, $phrase, $offset, $limit,  1, $sort, $order_by, '', $from, $to);
//$ptotal = mysql_num_rows(getPropertyList2($agency, $phrase, '', '', 1, $sort, $order_by, '', $from, $to));

//$propertylist = getPropertyList($agency, $phrase, PER_PAGE, $start, 1);
$totalFound = getFoundRows();
$pagination_tabs = ceil($totalFound / PER_PAGE);

$start_display = $start + 1;



$export_link = "export_deleted_properties.php?".$params;






?>
<div id="mainContentCalendar">

 <div class="sats-middle-cont">
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Deleted Properties" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Deactivated Properties</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>


	<form method='POST' action="<?=URL; ?>view_deleted_properties.php" class="searchstyle">
		<table border=1 cellpadding=0 cellspacing=0 >
			<tr class="tbl-view-prop">
				<td>
                
                <div class="ap-del-prop aviw_drop-h" style="height:auto;">
				
				
				<div class="fl-left">
					<label>From:</label>
					<input class="addinput searchstyle datepicker" type="text" name="from" value="<?php echo $from; ?>" />
				</div>
				
				<div class="fl-left">
					<label>To:</label>
					<input class="addinput searchstyle datepicker" type="text" name="to" value="<?php echo $to; ?>" />
				</div>
				
				
				
				<div class="fl-left">
					<label>Agency:</label>
					<select name="agency" id="agency">
						<option value="">--- Select ---</option>
						<?php

						$jparams = array(
							'country_id' => $country_id,
							'p_deleted' => 1,								
							'custom_select' => 'DISTINCT a.`agency_id`, a.`agency_name`',
							'custom_sort' => 'a.`agency_name`',
							'echo_query' => 1
						);
						$agencies_sql = $crm->getPropertyOnly($jparams);

						while( $curr_agency = mysql_fetch_array($agencies_sql) ) {

							echo "<option value='" . $curr_agency['agency_id'] . "' " . ( ( $agency == $curr_agency['agency_id'] )?'selected="selected"' : null ) . ">";
								echo $curr_agency['agency_name'];
							echo "</option>";

						}
						?>
					</select>
				</div>
								
				<div class="fl-left">
					<label>Phrase:</label>
					<input style="margin: 0;" class="addinput searchstyle" type=text name="phrase" size=10 value="<?=$phrase;?>" />
				</div>
				
				<div class="fl-left">
					<input type="submit" name="btn_search" value="Search" class="submitbtnImg">
				</div>
				<div class="fl-left">
				<a href="<?php echo $export_link ;?>" class="submitbtnImg export">Export</a>
				</div>
				</div>
                
				<table border=0 cellspacing=0 cellpadding=5 width=100% class="table-left">
					
					
					<?php
					
					
					if($_REQUEST['order_by']){
						if($_REQUEST['order_by']=='ASC'){
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
					
					?>

					<tr bgcolor="#b4151b">
						<td width="120"><b><a href="/view_deleted_properties.php?sort=p.address_2&order_by=<?php echo ($_REQUEST['sort']=='p.address_2')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Address</div> <?php echo ($_REQUEST['sort']=='p.address_2')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></b></td>
						<td width="70"><b><a href="/view_deleted_properties.php?sort=p.address_3&order_by=<?php echo ($_REQUEST['sort']=='p.address_3')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Suburb</div> <?php echo ($_REQUEST['sort']=='p.address_3')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></b></td>
						<td width="40"><b><a href="/view_deleted_properties.php?sort=p.state&order_by=<?php echo ($_REQUEST['sort']=='p.state')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold"><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></div> <?php echo ($_REQUEST['sort']=='p.state')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></b></td>
						<td width="30"><b><a href="/view_deleted_properties.php?sort=a.agency_name&order_by=<?php echo ($_REQUEST['sort']=='a.agency_name')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Agency</div> <?php echo ($_REQUEST['sort']=='a.agency_name')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></b></td>						
						<td width="30"><b><a href="/view_deleted_properties.php?sort=p.agency_deleted&order_by=<?php echo ($_REQUEST['sort']=='p.agency_deleted')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Deleted By</div> <?php echo ($_REQUEST['sort']=='p.agency_deleted')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></b></td>
						<?php 
						// default active
						$active = ($_REQUEST['sort']=="")?'arrow-top-active':''; 
						?>
						<td width="30"><b><a href="/view_deleted_properties.php?sort=p.deleted_date&order_by=<?php echo ($_REQUEST['sort']=='p.deleted_date')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Deleted Date</div> <?php echo ($_REQUEST['sort']=='p.deleted_date')?$sort_arrow:'<div class="arw-std-up '.$active.'"></div>'; ?></a></b></td>
						<td width="50" style="color: #FFFFFF;"><b>Restore</b></td>
					</tr>
					<?php

					$odd = 0;
					
					

					while ( $row = mysql_fetch_array($propertylist) ) {

						$odd++;

						if (is_odd($odd)) {

							echo "<tr bgcolor=#FFFFFF>";

						} else {

							echo "<tr bgcolor=#eeeeee>";

						}

						echo "\n";

						// (4) Print out each element in $row, that is,

						// print the values of the attributes

						echo "<td><a href='view_property_details.php?id={$row['property_id']}'>{$row['p_address_1']} {$row['p_address_2']}</a></td>";
						echo "<td>{$row['p_address_3']}</td>";
						echo "<td>{$row['p_state']}</td>";
						
						?>
						
						<td>
						<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
						<a href="<?php echo $ci_link; ?>"><?php echo $row['agency_name']; ?></a></td>
						<td><?php echo ($row['agency_deleted']==1)?'Agency':'SATS'; ?></td>
						<td><?php echo ($row['deleted_date']!="0000-00-00 00:00:00")?date("d/m/Y",strtotime($row['deleted_date'])):'----'; ?></td>
						
						<?php

						if ($row[9] == "") {

							$comma = "";

						} else {

							$comma = ", ";

						}



						// Restore
						echo "<td><a href='" . URL . "undelete_property.php?id=" . $row['property_id'] . "'>Click to Restore</a></td></tr>";

						echo "\n";

					}

					// (5) Close the database connection
					?>

					

					

				</table>
                
				</td>
			</tr>
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
<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats -->


<!-- end #container --></div>

</body>

</html> 
