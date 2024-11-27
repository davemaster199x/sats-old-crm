<?

$title = "Add Technician";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>


  <div id="mainContent">
  
    <div class="sats-middle-cont">
  
  <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Add a Technician" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Add a Technician</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>


<h2 class="heading">Add a Technician</h2>
  

<?php

$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$position = $_POST['position'];
$ph_mob1 = $_POST['ph_mob1'];
$ph_mob2 = $_POST['ph_mob2'];
$ph_home = $_POST['ph_home'];
$email = $_POST['email'];
$electrician = $_POST['electrician'];
$license_number = $_POST['license_number'];
$active = '1';
$ret = '';
try{
	$first = strtolower($first_name);
	$last = strtolower($last_name);
	$name = $first.$last;
	//$duplicatequery = "SELECT id FROM techs WHERE first_name='".$first_name."' AND last_name='".$first_name."'";
	$duplicatequery = "SELECT * FROM techs WHERE CONCAT_WS(' ', LOWER(first_name), LOWER(last_name)) LIKE '{$name}'";
	$duplicateResult = mysql_query($duplicatequery, $connection);

	while ($row = mysql_fetch_row($duplicateResult))
	   {
			$duplicates[] = $row[0];
			echo 'Exist record';
			
	   }

	$isDuplicate = $duplicates;
	if(!$isDuplicate){
		$insertQuery = "INSERT INTO techs (first_name, last_name, position, ph_mob1, ph_mob2, ph_home, email, active, electrician, license_number) VALUES
						(" .
						"'" . $first_name . "', ".
						"'" . $last_name . "', ".
						"'" . $position . "', ".
						"'" . $ph_mob1 . "', ".
						"'" . $ph_mob2 . "', ".
						"'" . $ph_home . "', ".
						"'" . $email . "', ".
						"'" . $active . "'," .
						"'" . $electrician . "'," . 
						"'" . $license_number . "');";
		//$result = mysql_query ($insertQuery,$connection);
		  if ((@ mysql_query ($insertQuery,$connection)) && @ mysql_affected_rows(  ) == 1)
			 $ret = true;
		  else
			 //echo "" . $insertQuery;
			 $ret = false;
	}		 
}
catch(Exception $ex){
	echo $ex->getMessage();
}		
?>
<?php if($ret):?>
		<div class="success">Technician successfully added. (<a href="view_techs.php" >View Technicians</a>)  (<a href="add_tech_static.php" >Add another Technician</a>)</div>
<?php else: ?>
		<div class='success'>A fatal error occurred</div>
<?php endif;?>


  </div>
  
    </div>


<br class="clearfloat" />

</body>
</html>
