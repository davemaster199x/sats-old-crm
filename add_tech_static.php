<?

$title = "Add Technician";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>

  <?php

   // $insertQuery = "select count(id) FROM jobs where (status='To Be Booked');";

   // $result = mysql_query ($insertQuery, $connection);

   // $row = mysql_fetch_row($result);

   // echo "<a href='" . URL . "view_jobs.php?status=tobebooked'>There are currently $row[0] Jobs To Be Booked in the System</a><br><br>\n";



   // $insertQuery = "select count(id) FROM jobs where (status='Send Letters' AND letter_sent=0);";

   // $result = mysql_query ($insertQuery, $connection);

   // $row = mysql_fetch_row($result);

   // echo "<a href='" . URL . "view_jobs.php?status=sendletters'>There is currently $row[0] Jobs waiting for letters to be sent</a><br><br>\n";

   ?>

  <div id="mainContent">

  <div class="sats-middle-cont">
   
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Add Technician" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Add Technician</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>


	<div class="addproperty">

    <form id="form1" name="form1" method="POST" action="<?=URL;?>add_tech.php">
		
        <div class="row">
        <label class="addlabel" for="first_name">First Name</label>
        <input class="addinput" type="text" name="first_name" id="first_name">
		</div>
        <div class="row">
        <label class="addlabel" for="last_name">Last Name</label>
        <input class="addinput" type="text" name="last_name" id="last_name">
		</div>
        <div class="row">
        <label class="addlabel" for="position">Position</label>
        <input class="addinput" type="text" name="position" id="position">
		</div>
        <div class="row">
        <label class="addlabel" for="ph_mob1">Mobile</label>
        <input class="addinput" type="text" name="ph_mob1" id="ph_mob2">
		</div>
        <div class="row">
        <label class="addlabel" for="ph_mob2">Fax</label>
        <input class="addinput" type="text" name="ph_mob2" id="ph_mob2">
		</div>
        <div class="row">
        <label class="addlabel" for="ph_home">Mobile (Other)</label>
        <input class="addinput" type="text" name="ph_home" id="ph_home">
		</div>
        <div class="row">
        <label class="addlabel" for="email">Email</label>
        <input class="addinput" type="text" name="email" id="email">
		</div>
        <div class="row">
        <label class="addlabel" for="license_number">License Number</label>
        <input class="addinput" type="text" name="license_number" id="license_number">
		</div>
        <div class="row">
        <label class="addlabel" for="email">Electrician</label>
        <select name="electrician" id="electrician">
          <option value="0" selected>No</option>
          <option value="1">Yes</option>
        </select>
		</div>
		<div class="row">
        <input class="addinput submitbtnImg submit" type="submit" name="submit" id="submit" value="Add Technician">
		</div>

    </form>


</div>


  </div>

</div>

<br class="clearfloat" />

</body>

</html>

