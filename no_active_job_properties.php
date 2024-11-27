<?

$title = "No Active Job Properties";

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');

$search = "";
$agency = "";
$search = trim($_REQUEST['searchsuburb']);
$agency = $_REQUEST['agency'];


$start = (intval($_REQUEST['start']) > 0 ? intval($_REQUEST['start']) : 0);
$ts_safety_switch = $_REQUEST['ts_safety_switch'];


if($_POST['postcode_region_id']){
	$filterregion = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$filterregion = $_GET['postcode_region_id'];
	//echo $filterregion;
}

// header sort parameters
$sort = $_REQUEST['sort'];
$order_by = $_REQUEST['order_by'];

$sort = ($sort)?$sort:'a.agency_name';
$order_by = ($order_by)?$order_by:'ASC';

// pagination script
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;
$this_page = $_SERVER['PHP_SELF'];

$params = "&sort={$sort}&order_by={$order_by}&agency={$agency}&searchsuburb={$search}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$propertylist = noActiveJobPropperties($offset,$limit);
$ptotal = mysql_num_rows(noActiveJobPropperties('',''));


//$propertylist = getPropertyList($agency, $search, PER_PAGE, $start, 0 , $ts_safety_switch);
$totalFound = getFoundRows();
$pagination_tabs = ceil($totalFound / PER_PAGE);

$start_display = $start + 1;


$export_link = "export_all_properties.php?searchsuburb={$search}&agency={$agency}";

?>


<div id="mainContentCalendar">
  <div class="sats-middle-cont">
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="/no_active_job_properties.php"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
   <?php
   if($_GET['perm_del']==1){ ?>
		<div class="success">Property Delete Successful</div>
   <?php
   }
   ?>
  
  
    <form method="POST" action="<?=URL;?>active_properties.php" class="searchstyle">
      <table cellpadding=0 cellspacing=0 >
        <tr class="tbl-view-prop">
          <td>
          
           
            
            <table border=0 cellspacing=1 cellpadding=5 width="100%" class="table-left tbl-fr-red">
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
				<th width="100"><b>Property ID</b></th>
                <th><b>Address</b></th>
               <th width="70"><b>Service</b></th>
         
				
				
				
				
				<?php
				// default active
				$active = ($_REQUEST['sort']=="")?'arrow-top-active':''; 
				?>
                <th><b>Agency</b></th>
				<th width="100"><b>Created</b></th>
              </tr>
              <?php
				
					$odd = 0;

					while($row=mysql_fetch_array($propertylist))
					{

						$odd++;
															
						if (is_odd($odd)) {

							echo "<tr bgcolor=#FFFFFF>";

						} else {

							echo "<tr bgcolor=#eeeeee>";

						}
						
						

						// property id
						echo "<td><a href='" . URL . "view_property_details.php?id=" . $row['property_id'] . "'>{$row['property_id']}</a></td>";

						// (4) Print out each element in $row, that is,

						// print the values of the attributes

						echo "<td>";

						echo "<a href='" . URL . "view_property_details.php?id=" . $row['property_id'] . "'>{$row['address_1']} {$row['address_2']}, {$row['address_3']}</a>";

						echo "</td>";

						
						
						// service
						//echo '<td><img src="images/serv_img/'.getServiceIcons($row['ajt']).'" /></td>';
						echo "<td></td>";
						

						if ($row[9] == "") {

							$comma = "";

						} else {

							$comma = ", ";

						}
						$ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}");
						echo "<td><a href='" . $ci_link . "'>{$row['agency_name']}</a></td>";
						
						echo "<td>".(($row['created']!="")?date('d/m/Y',strtotime($row['created'])):'')."</td>";
						
						echo "</tr>\n" ;

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

</div>

  <br class="clearfloat" />

</body></html>