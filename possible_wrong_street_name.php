<?php

include('inc/init_for_ajax.php');
$country_id = $_SESSION['country_default'];

$crm = new Sats_Crm_Class;
$agency_api = new Agency_api;



function getStreetAbrvFullName($street) {

    $result = $street;

    $find_arr = ['Ally','Arc','Ave','Bvd','Bypa','Cct','Cl','Crn','Ct','Cir','Cres','Cds','Dr','Esp','Grn','Gr','Hwy','Jnc','Pde','Pl','Rdge','Rd','Sq','St','Tce'];
    $replace_arr = ['Alley','Arcade','Avenue','Boulevard','Bypass','Circuit','Close','Corner','Court','Circle','Crescent','Cul-de-sac','Drive','Esplanade','Green','Grove','Highway','Junction','Parade','Place','Ridge','Road','Square','Street','Terrace'];

    foreach ($find_arr as $index => $find) {

        if (preg_match("/\b{$find}\b/i", $street)) {

            $replace = $replace_arr[$index];
            $result = preg_replace("/\b{$find}\b/i", $replace, $street);
        }
    }

    return $result;
}



echo "<h1>Possible Wrong Street Name</h1>";

// find these street
$street_arr = ['Circuit', 'Close', 'Corner', 'Court', 'Crescent', 'Cul-de-sac', 'Drive', 'Esplanade', 'Green', 'Grove', 'Highway', 'Junction', 'Parade', 'Place', 'Ridge', 'Road', 'Square', 'Street', 'Terrace'];
foreach( $street_arr as $street_name ){
    $street_arr_sql_like[] = "p.`address_2` LIKE '%{$street_name}%'";
}
$street_imp = implode(" OR ",$street_arr_sql_like);


// pagination script
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
//$limit = 50;
$limit = 200;
$this_page = $_SERVER['PHP_SELF'];

$params = "&sort={$sort}&order_by={$order_by}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


// paginate
if(is_numeric($offset) && is_numeric($limit)){
    $pag_str = " LIMIT {$offset}, {$limit} ";
}

// paginated list
$sql_str = "
SELECT 
    p.`property_id`,
    p.`address_2`,
    p.`propertyme_prop_id`,

    a.`agency_id`
FROM `property` AS p 
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
WHERE p.`propertyme_prop_id` != ''
AND (
    {$street_imp}
)
{$pag_str}
";
$sql = mysql_query($sql_str);

// all list
$sql_tot_str = "
SELECT COUNT(p.`property_id`) AS p_count
FROM `property` AS p 
WHERE p.`propertyme_prop_id` != ''
AND (
    {$street_imp}
)
";
$sql_tot_sql= mysql_query($sql_tot_str);
$sql_tot_row = mysql_fetch_array($sql_tot_sql);
$ptotal = $sql_tot_row['p_count'];
echo "<br /><br />";
?>
<!DOCTYPE html>
<html>
<head>
<meta content="text/html;charset=utf-8" http-equiv="Content-Type">
<meta content="utf-8" http-equiv="encoding">
<title>Title of the document</title>
<script
  src="https://code.jquery.com/jquery-3.4.1.js"
  integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU="
  crossorigin="anonymous">
</script>
<style>
.redRowBg {
    background-color: #ffcccc;
    background-color: #fac3c373;
}
</style>
</head>
<body>


<table>
	<tr>
        <th>CRM Address</th>
        <th>PMe Address</th>
	</tr>
    <?php
     $i = 1;
     while( $row = mysql_fetch_array($sql) ){ 

        // get pme tenants
        $agency_api_params = array(
            'prop_id' =>  $row['propertyme_prop_id'],
            'agency_id' => $row['agency_id']
        );
        $pme_prop_json = $agency_api->get_property_pme($agency_api_params);
        $pme_prop_json_enc = json_decode($pme_prop_json);

        $pme_street_name = $pme_prop_json_enc->Address->Street;
        $pme_street_name_long = getStreetAbrvFullName($pme_street_name);
    
        ?>
            <tr class="<?php echo ( $row['address_2'] != $pme_street_name_long  )?'redRowBg':null; ?>">
                <td>
                    <a target="__blank" href="/view_property_details.php?id=<?php echo $row['property_id'] ?>">
                        <?php echo $row['address_2']; ?>
                    </a>
                </td>
                <td>
                    <?php echo $pme_street_name; ?>
                </td>
            </tr>
        <?php	
        $i++;

    }
	?>	
</table>


<div>
<?php
// Initiate pagination class
$jp = new jPagination();
			
$per_page = $limit;
$page = ($_GET['page']!="")?$_GET['page']:1;
$offset = ($_GET['offset']!="")?$_GET['offset']:0;	

echo $jp->display($page,$ptotal,$per_page,$offset,$params);
?>
</div>


<style>
td, th {

    padding: 3px 36px 3px 0;
    text-align: left;

}
</style>

</body>
</html>