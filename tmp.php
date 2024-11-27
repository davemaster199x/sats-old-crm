<?

include('inc/init.php');

echo "<pre>";

$query = "SELECT * FROM pages";

$result = mysqlMultiRows($query);

print_r($result);

# Populate Admin permissions

$classID = 2;

foreach($result as $index=>$array)
{
	$query = "INSERT INTO page_permissions (PageID, ClassID) VALUES ('" . $array['PageID'] . "',  '3')";
	
	mysql_query($query) or die();
}

//

?>
