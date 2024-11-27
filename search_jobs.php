<?php
$title = "Search Jobs";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

// Initiate job class
$jc = new Job_Class();

$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$job_type = mysql_real_escape_string(urldecode($_REQUEST['job_type']));
$service = mysql_real_escape_string($_REQUEST['service']);
$state = mysql_real_escape_string($_REQUEST['state']);
$agency = mysql_real_escape_string($_REQUEST['agency']);
$search_type = mysql_real_escape_string($_REQUEST['search_type']);
//echo "search_type: {$search_type}";


if($_POST['postcode_region_id']){
	$filterregion = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$filterregion = $_GET['postcode_region_id'];
	//echo $filterregion;
}
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$is_urgent = ($_REQUEST['is_urgent']!="")?mysql_real_escape_string($_REQUEST['is_urgent']):'';
$job_status = 'To be Booked';


// sort
$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.job_type';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&phrase=".$phrase;

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$no_filter = mysql_real_escape_string($_REQUEST['no_filter']);




// search function
function jSearchJobs($offset,$limit,$phrase,$no_filter,$search_type){
	
	if( $search_type == 1 ){ // Phone Number

		$phone_number = str_replace(' ', '', trim($phrase));
		$tenant_join_table = "INNER JOIN `property_tenants` AS pt ON p.`property_id` = pt.`property_id`";

		$jsql_str = "
			SELECT
				p.`property_id`,
				p.`address_1` AS p_address_1, 
				p.`address_2` AS p_address_2, 
				p.`address_3` AS p_address_3, 
				p.`state` AS p_state,
				p.`postcode` AS p_postcode,
				p.`is_sales`
			FROM `property_tenants` AS pt 
			LEFT JOIN `property` AS p ON pt.`property_id` =  p.`property_id`
			WHERE (
				REPLACE(pt.`tenant_mobile`, ' ', '') LIKE '%{$phone_number}%' OR 
				REPLACE(pt.`tenant_landline`, ' ', '') LIKE '%{$phone_number}%'
			)
			AND p.deleted = 0
		";

		return mysql_query($jsql_str);

	}else if( $search_type == 2 || $search_type == 3 ){ // Address OR landord

		$phrase_search_str = null;

		if( $search_type == 2 ){ // Address

			$address = strtolower(trim($phrase));
			$phrase_search_str = "
				AND CONCAT_WS( 
					' ', 
					LOWER(p.`address_1`), 
					LOWER(p.`address_2`), 
					LOWER(p.`address_3`), 
					LOWER(p.`state`), 
					LOWER(p.`postcode`)
				) 
				LIKE '%{$address}%'
			";

		}else if( $search_type == 3 ){ // Landlord

			$landlord = strtolower(trim($phrase));
			$phrase_search_str = "
				AND CONCAT_WS( 
					' ', 
					LOWER(p.`landlord_firstname`), 
					LOWER(p.`landlord_lastname`)
				) 
				LIKE '%{$landlord}%'
			";

		}

		$jsql_str = "
		SELECT
			p.`property_id`,
			p.`address_1` AS p_address_1,
			p.`landlord_firstname` AS p_landlord_firstname,
			p.`landlord_lastname` AS p_landlord_lastname,
			p.`address_2` AS p_address_2, 
			p.`address_3` AS p_address_3, 
			p.`state` AS p_state,
			p.`postcode` AS p_postcode,
			p.`is_sales`
		FROM `property` AS p 
		WHERE p.`property_id` > 0
		AND p.deleted = 0
		{$phrase_search_str}
		";

		return mysql_query($jsql_str);

	}
	else if( $search_type == 4){
		$jsql_str = "
			SELECT
				p.`property_id`,
				p.`address_1` AS p_address_1, 
				p.`address_2` AS p_address_2, 
				p.`address_3` AS p_address_3, 
				p.`landlord_firstname` AS p_landlord_firstname,
				p.`landlord_lastname` AS p_landlord_lastname,
				p.`state` AS p_state,
				p.`postcode` AS p_postcode,
				p.`is_sales`
			FROM `property` AS p
			LEFT JOIN `other_property_details` AS opd ON p.`property_id` =  opd.`property_id`
			WHERE opd.`building_name` LIKE '%{$phrase}%'
			AND p.deleted = 0
		";
		return mysql_query($jsql_str);
	}
	
}


if($phrase!=""){
	$plist = jSearchJobs($offset,$limit,$phrase,$no_filter,$search_type);
	//$ptotal = mysql_num_rows(jSearchJobs('','',$phrase,$no_filter,$search_type));	
}



?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.yello_mark{
	background-color: #ffff9d !important;
}
.green_mark{
	background-color: #c2ffa7;
}
<?php 
if($filterregion!=""){ ?>
.pagination li, .pagination_range{
	display:none!important;
}
<?php	
}
?>
.sj_notes{
	width: 35%;
}
</style>


<div id="mainContent">

   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="/search_jobs.php"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Agency Update Successfull</div>';
		}
		
		
		//echo date('Y-m-d', strtotime("-30 days"));
		
		
		?>
		
		
		
		
		
			
			
			<?php
			
			// no sort yet
			if($_REQUEST['sort']==""){
				$sort_arrow = 'up';
			}
			
			?>
			
			
			
	<form method="POST" action="search_jobs.php" name='example' id='example' style="margin: 0;">
			<input type='hidden' name='status' value='<?php echo $status ?>'>

			<table border=1 cellpadding=0 cellspacing=0 width="100%">
				<tr class="tbl-view-prop">
				<td>

				<div class="aviw_drop-h aviw_drop-vp" id="view-jobs">

				 
	
					<div class="fl-left">
						<label>Search:</label>
						<select name="search_type" style="margin-right: 10px;">
							<option value="1" <?php echo ( $search_type == 1 )?'selected="selected"':''; ?>>Phone Number</option>
							<option value="2" <?php echo ( $search_type == 2 )?'selected="selected"':''; ?>>Address</option>
							<option value="3" <?php echo ( $search_type == 3 )?'selected="selected"':''; ?>>Landlord</option>
							<option value="4" <?php echo ( $search_type == 4 )?'selected="selected"':''; ?>>Building Name</option>
						</select>
						<input type="text" style="float:none; width: 130px;" value="<?php echo $phrase; ?>" size="10" name="phrase" class="addinput">
					</div>
				  
				
			
					
					
				
					
					
				
					<div class='fl-left' style="float:left;">
						<input type='submit' class='submitbtnImg' value='Search' />
					</div>

					
				<!-- duplicated filter here -->

					  
					  
				</td>
				</tr>
			</table>	  
				  
			</form>

		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
			
			<tr class="toprow jalign_left">

				<th>Address</th>
				<th>Suburb</th>
				<th>Landlord Name</th>
				<?php
				for($i=1;$i<=4;$i++){ ?>
					<th>Tenant <?php echo $i ?></th>
					<th>Tenant <?php echo $i ?> Mobile</th>
					<th>Tenant <?php echo $i ?> Landline</th>
				<?php	
				}
				?>				

			</tr>
				<?php
				
				
				$i= 0;
				if( mysql_num_rows($plist)>0 && $phrase!="" ){
					while($row = mysql_fetch_array($plist)){

						// sales property
						$sales_txt = ( $row['is_sales'] == 1 )?'(Sales)':null;
						
						// grey alternation color
						$row_color = ($i%2==0)?"style='background-color:#eeeeee;'":"";
						
						
						
					
				?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>

							<td>
								<a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>">
									<?php echo "{$row['p_address_1']} {$row['p_address_2']} {$sales_txt}"; ?>
								</a>
							</td>	
							<td><?php echo $row['p_address_3']; ?></td>
							<td><?php echo $row['p_landlord_firstname'] . ' ' . $row['p_landlord_lastname']; ?></td>			
							<?php
							// new tenants
							$pt_params = array( 
								'property_id' => $row['property_id'],
								'active' => 1,
								'paginate' => array(
									'offset' => 0,
									'limit' => 4
								)
							 );
							$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
							
							$tent_name = [];
							$tenant_mobile = [];
							$tenant_landline = [];
							while( $pt_row = mysql_fetch_array($pt_sql) ){
								
								$tent_name[] = trim($pt_row['tenant_firstname']) . " " .  trim($pt_row['tenant_lastname']);
								$tenant_mobile[] =  trim($pt_row['tenant_mobile']);
								$tenant_landline[] =  trim($pt_row['tenant_landline']);
								
							}
							
							$num_tenants = 4;
							for( $pt_i=0; $pt_i<$num_tenants; $pt_i++ ){ ?>
								<td><?php echo $tent_name[$pt_i]; ?></td>
								<td><?php echo $tenant_mobile[$pt_i]; ?></td>
								<td><?php echo $tenant_landline[$pt_i]; ?></td>
							<?php	
							}
							?>							
							
							
						</tr>
						
				<?php
					$i++;
					}
				}else{ ?>
					<td colspan="100%" align="left">No active job found</td>
				<?php
				}
				?>
				
		</table>
		
		<?php
		/*
		if(mysql_num_rows($plist)==0){ ?>
		<form method="POST" action="search_jobs.php" name='example' id='example' style="margin: 0;">
			<div class="fl-left">
				<label style="float: left; position: relative; top: 6px;">Search all Jobs</label>
				<input type="text" style="float:left; width: 130px; margin-right: 11px;" value="<?php echo $phrase; ?>" size="10" name="phrase" class="addinput"> <input type='submit' class='submitbtnImg' value='Search' style="float:left;" />
				<input type="hidden" name="no_filter" value="1" />
			</div>
		</form>
		<div style="clear:both;"></div>
		<?php	
		}
		*/
		?>
		

		<?php
		
		
			/*
			// Initiate pagination class
			$jp = new jPagination();
			
			$per_page = $limit;
			$page = ($_GET['page']!="")?$_GET['page']:1;
			$offset = ($_GET['offset']!="")?$_GET['offset']:0;	
			
			echo $jp->display($page,$ptotal,$per_page,$offset,$params);
			*/
		

		
		
		?>

		
	</div>
</div>




<br class="clearfloat" />
<script>
</script>
</body>
</html>