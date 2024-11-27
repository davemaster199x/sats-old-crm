<?

$title = "Add/Edit Calendar Entry";
$onload = 1;
$onload_txt = "zxcSelectSort('agency',1)";

$bodyclass = "popup";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');


?>
<div id="mainContent">

<div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Add/Edit Calendar Entry" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Add/Edit Calendar Entry</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

<form id="form1" name="form1" method="POST" action="<?=URL;?>add_calendar_entry.php">
        
        
        <?php 
        
        $id = $_GET['id'];
        
        
        $existing = array();

        if($id) {
            $existing = mysql_query ("SELECT staff_id, date_start, date_finish, region, details, booking_target FROM calendar WHERE calendar_id = $id", $connection);
            $existing = mysql_fetch_assoc($existing);
            
            $startdate = str_replace('/', "-", $existing[date_start]);
            $startdate = date('d-m-Y', strtotime($startdate));
            
            $finishdate = str_replace('/', "-", $existing[date_finish]);
            $finishdate = date('d-m-Y', strtotime($finishdate));
        }
        else
        {
            $existing["staff_id"] = $_GET['staff_id'];
            $startdate = $_GET['startdate'];
            $finishdate = $_GET['startdate'];
        }
        
        
        
        ?>
        <input type="hidden" name="id" value="<?php echo $id; ?>" />
        <table>
            <tr style="border: 1px solid #CCCCCC !important;">
                <td style='vertical-align: top;'><label>Tech:</label>
        <br/>
        <?php 
        
        if($id)
        {
            echo "<select name='staff_id'>\n";
        }
        else
        {
            // Allow multiple for new
            echo "<select multiple style='height: 200px;' name='staff_id[]'>\n";
        }

       // Grab all the techs and put them in an array.
        
           // (a) Run the query
           $techs = mysql_query ("SELECT StaffID, FirstName, LastName FROM staff_accounts WHERE deleted = 0 AND active = 1;", $connection);
        
           // (b) While there are still rows in the result set,
           // fetch the current row into the array $row
           while ($row = mysql_fetch_row($techs))
           {
            
           echo "<option value='$row[0]' ";
           if($existing["staff_id"] == $row[0]) {
                echo 'selected="selected"';         
           }
           echo ">$row[1] $row[2]</option>\n";
        }
        
            echo "</select>\n";     
        ?>
        
        <br/>
        <br/></td>
                <td style='vertical-align: top;'>        <label>Start Date:</label>
        <br/>
        <input type="text" name="start_date" id="start_date" value="<?php if($startdate): echo $startdate; endif; ?>">
        <br/>
        <br/>
        <label>Finish Date:</label>
        <br/>
        <input type="text" name="finish_date" id="finish_date" value="<?php if($finishdate): echo $finishdate; endif; ?>">
        <br/>
        <br/>
        <label>Region / Type of Leave:</label>
        <br/>
        <input type="text" name="region" id="region" value="<?php echo $existing[region]; ?>">
        <br/>
        <br/>
		<label>Booking Target:</label>
        <br/>
        <input type="text" name="booking_target" id="booking_target" value="<?php echo $existing['booking_target']; ?>" />
        <br/>
        <br/>
		</td>
            </tr>
        </table>
        

        <label>Details:</label>
        <br/>
        <textarea cols="45" rows="10" name="details" id="details" ><?php echo $existing[details]; ?></textarea>
        <br/>   
        <input class="submitbtnImg" type="submit" name="submit" id="submit" value="Add / Edit Calendar Entry">      
</p>
    </form>

  </div>

</div>

<br class="clearfloat">


     <script type="text/javascript" src="inc/js/jquery-ui-1.8.23.custom.min.js"></script>
     
    <script>
    $(function() {
        $( "#start_date" ).datepicker({
            changeMonth: true,
            dateFormat: "dd-mm-yy",
            onSelect: function( selectedDate ) {
                $( "#finish_date" ).datepicker( "option", "minDate", selectedDate );
            }
        });
        $( "#finish_date" ).datepicker({
            dateFormat: "dd-mm-yy",
            changeMonth: true,
            onSelect: function( selectedDate ) {
                $( "#start_date" ).datepicker( "option", "maxDate", selectedDate );
            }
        });
    });
    </script>    

</body>
</html>
