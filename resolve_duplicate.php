<?

$title = "Resolve Duplicate Properties";

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');

$id = $_GET['id'];

$count = 0;

$query ="SELECT 
          p1.*
        FROM
          property p1
        WHERE REPLACE(UPPER(CONCAT(
            UPPER(p1.address_1),
            UPPER(p1.address_2),
            UPPER(p1.address_3),
            UPPER(p1.state),
            UPPER(p1.postcode)
          )),' ','')IN 
          (SELECT 
            REPLACE(CONCAT(
              UPPER(address_1),
              UPPER(address_2),
              UPPER(address_3),
              UPPER(state),
              UPPER(postcode)
            ),' ','') 
          FROM
            property p 
          WHERE p.`property_id` = ". $id .");";
          
$properties = mysqlMultiRows($query);

?>
<div id="mainContentCalendar">

 <div class="sats-middle-cont">
  
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="View Duplicate Properties" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>View Duplicate Properties</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

        <table border="0" cellpadding="5" cellspacing="0" width="100%" class="table-left tbl-fr-red">
            <tr bgcolor="#b4151b">
            <th><strong>Property ID</strong></th>
            <th><strong>Address</strong></th>
            <th><strong>Suburb</strong></th>
            <th><strong>State</strong></th>
            <th><strong>Postcode</strong></th>
          </tr>
         
            <?php
                foreach($properties as $proptery){
                    if($count&1){
                        echo '<tr bgcolor="#F0F0F0">';
                    } else {
                    echo '<tr>';
                    }
                    echo '<td><a href="view_property_details.php?id='. $proptery['property_id'] .'">'. $proptery['property_id'] .'</a></td>'; 
                    echo '<td>'.  $proptery['address_1'] .' '.  $proptery['address_2'] .'</td>';
                    echo '<td>'.  $proptery['address_3'] .'</td>';  
                    echo '<td>'.  $proptery['state'] .'</td>';
                    echo '<td>'.  $proptery['postcode'] .'</td>';
                    echo '</tr>';
                    $count++;
                }
            ?>

        </table>

</div>

</div>
<br class="clearfloat" />


</body>

</html> 