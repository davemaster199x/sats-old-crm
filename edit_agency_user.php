<?

$title = "Agency Details";
$onload = 1;
$onload_txt = "zxcSelectSort('agency',1)";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$encrypt = new cast128();
$encrypt->setkey(SALT);

?>
  <div id="mainContent">
  
  <div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Create Password" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Create Password</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>



<?php
// init the variables
$agency_id = $_GET['agency_id'];
$doaction = $_GET['doaction'];

if ($doaction == "update") {
	// update the details in the database.
	$login_id = $_POST['login_id'];
	$password = addslashes(utf8_encode($encrypt->encrypt($_POST['password'])));

   

   // (2) Run the query
   $insertQuery = "UPDATE agency set login_id='$login_id', password='$password', `pass_timestamp` = '".date("Y-m-d H:i:s")."' WHERE (agency_id = $agency_id);";

	// echo $insertQuery;
    $result = mysql_query ($insertQuery, $connection);
	if (mysql_affected_rows($connection) == 1)
	{
	  echo "<div class='success'>Password Updated</div>";
	}
	else{
		echo "Unable to updates details! <br> Possibly caused by duplicated login id found in the system.\n";
	}

    
	//	echo $insertQuery;

} // if update


echo "<form action='edit_agency_user.php?agency_id=$agency_id&doaction=update' method=post name='example' id='example'>";

   // (1) Open the database connection and use the winestore
   // database
   

   // (2) Run the query
   $Query = "SELECT agency_name, login_id, password FROM agency WHERE (agency_id = '$agency_id');";
   $result = mysql_query ($Query, $connection);

   // (3) While there are still rows in the result set,
   // fetch the current row into the array $row
   $row = mysql_fetch_row($result);

	$row[2] = $encrypt->decrypt(utf8_decode($row[2]));

	
	

echo "<div class='addproperty'>

<h2 class='heading'>$row[0]</h2>
		<div class='row'>
       	 <label class='addlabel' for'login_id'>Login ID</label>
		 <input class='addinput' type='text' value='$row[1]' name='login_id'>
		</div>
		<div class='row'>
       	 <label class='addlabel' for='password'>Password</label>
		 <input class='addinput' type='text' value='$row[2]' name='password'>
		</div>
		<div class='row'>
		 <input type='submit' value='Update Login' class='submitbtnImg' style='width:140px;'>
		</div>

</div>";


  echo "</form>";

    
?>

</form>

  </div>
  
  </div>

<br class="clearfloat" />

</body>
</html>
