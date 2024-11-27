<?php

include('inc/init.php');

// init the variables
//print_r ($_POST);
$tech_id = $_GET['id'];

//id 	first_name 	last_name 	position 	ph_mob1 	ph_mob2 	ph_home 	email 	alert_email
//$first_name = addslashes($_POST['first_name']);   
//$last_name = htmlspecialchars($_POST['last_name'], ENT_QUOTES);
$position = htmlspecialchars($_POST['position'], ENT_QUOTES);
$ph_mob1 = htmlspecialchars($_POST['ph_mob1'], ENT_QUOTES);
$ph_mob2 = htmlspecialchars($_POST['ph_mob2'], ENT_QUOTES);
$ph_home = htmlspecialchars($_POST['ph_home'], ENT_QUOTES);
$email = htmlspecialchars($_POST['email'], ENT_QUOTES);
$active = htmlspecialchars($_POST['active'], ENT_QUOTES);
//$alert_email = htmlspecialchars($_POST['alert_email'], ENT_QUOTES);

   // (1) Open the database connection
   


   // (2) Run the query

   $Query = "UPDATE techs SET position='$position', ph_mob1='$ph_mob1', ph_mob2='$ph_mob2', ph_home='$ph_home', email='$email', active='$active' WHERE (id='".$tech_id."');";
 
	$result = mysql_query($Query, $connection);

	if (mysql_affected_rows() > 0){
		//echo "Update Successful!<br>";
	}
	else{
		echo "Update Failed!<br>";
		echo "<a href='" . URL . "view_tech_details.php?id=$tech_id'>back</a>";
	}
		
   // (3) redirect back to tech details page
   //header("Location: http://localhost/view_tech_details.php?id=$tech_id");
   header("Location: " . URL . "view_tech_details.php?id=$tech_id&success=1");
	
   // (5) Close the database connection
   
 
?>