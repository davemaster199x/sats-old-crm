<?

$title = "Unserviced v2";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

//include('inc/unserviced_functions.php');


// sort
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'j.date';
$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'DESC';
					
// pagination script
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;
$this_page = $_SERVER['PHP_SELF'];

$params = "&sort={$sort}&order_by={$order_by}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

// get unserviced list	
$jparams = array(
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'sort_list' => array(
		'order_by' => $order_by,
		'sort' => $sort
	),
	'display_query' => 0
);
$u_sql = getUnservicedv2($jparams);
//$ptotal = mysql_num_rows(getUnservicedProperties(getExcludedProperties(),'',''));
$jparams = [];
$ptotal = mysql_num_rows(getUnservicedv2($jparams));



function getUnservicedv2($params){
		
	// sort
	if( $params['sort_list']!="" ){
		if( $params['sort_list']['order_by']!="" && $params['sort_list']['sort']!='' ){
			$sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
		}
	}		
	
	// paginate
	if($params['paginate']!=""){
		if(is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])){
			$pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
		}
	}
	
	$sql = "
		SELECT 
			max(j.`date`) AS latestDate, 
			j.`property_id`, 
			p.`address_1` AS p_address1, 
			p.`address_2` AS p_address2, 
			p.`address_3` AS p_address3,
			p.`state` AS p_state, 
			p.`postcode` AS p_postcode,
			a.`agency_id`,
			a.`agency_name`
		FROM `jobs` AS j 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 		
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE j.`job_type` = 'Yearly Maintenance' 
		AND j.`status` = 'Completed' 
		AND a.`status` = 'active'
		AND j.`del_job` = 0 
		AND p.`deleted` = 0 
		GROUP BY j.`property_id`
		HAVING latestDate < '".date('Y-m-d',strtotime("-365 days"))."'
		{$sort_str}
		{$pag_str}
	";
	
	if( $params['display_query']==1 ){
		echo $sql;
	}	
	
	return mysql_query($sql);
	
}



?> 

<div id="mainContent">
  
  <div class="sats-middle-cont">
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Unserviced" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Unserviced</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
	
	<?php
	if($_GET['success']==1){
		echo '<div class="success">Import Successful</div>';
	}
	?>

	<!--
	<div class="aviw_drop-h" style="border: 1px solid #cccccc;">		 
		<div class="fl-left">
			<a href="export_unserviced.php"><button type="button" class="submitbtnImg">Export</button></a>
		</div>	
	</div>
	-->
   
   
   <table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px; text-align: left;" id="tbl_sms_msg">
				<tr class="toprow jalign_left">
					<th>Property ID</th>
					<th>Address</th>
					<th>Agency</th>
					<th>Last Job</th>
				</tr>
					<?php									
									
					
					if(mysql_num_rows($u_sql)>0){
						$i = 0;
						while($u = mysql_fetch_array($u_sql)){
							$bg_color = ($i%2==0)?'':'style="background-color:#eeeeee"';
					?>
							<tr class="body_tr jalign_left" <?php echo $bg_color; ?>>
								<td>
									<span class="txt_lbl">
										<?php echo $u['property_id']; ?>
									</span>
								</td>
								<td>
									<span class="txt_lbl">
										<a href="/view_property_details.php?id=<?php echo $u['property_id']; ?>">
											<?php echo "{$u['p_address1']} {$u['p_address2']} {$u['p_address3']} {$u['p_state']} {$u['p_postcode']}"; ?>
										</a>
									</span>
								</td>
								<td style="border-right: 1px solid #cccccc;">
									<span class="txt_lbl">
										<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$u['agency_id']}"); ?>
										<a href="<?php echo $ci_link ?>">
											<?php echo $u['agency_name']; ?>
										</a>
									</span>
								</td>
								<td style="border-right: 1px solid #cccccc;">
									<span class="txt_lbl">
										<?php echo ($u['latestDate']!="")?date("d/m/Y",strtotime($u['latestDate'])):''; ?>
									</span>
								</td>
							<tr>
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
