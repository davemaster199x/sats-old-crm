<?php

include('inc/init_for_ajax.php');

$image_data = $_POST['image_data'];


//Show the image
echo '<img src="'.$image_data.'" />';



//Get the base-64 string from data
$filteredData=substr($image_data, strpos($image_data, ",")+1);
 
//Decode the string
$unencodedData=base64_decode($filteredData);

// unique ID
$unique_id = uniqid();
 
//Save the image
file_put_contents("images/screenshot/vts_screenshot{$unique_id}.png", $unencodedData);


?>