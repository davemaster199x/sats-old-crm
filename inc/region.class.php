<?

# The main user functions are within here

class Regions {

	function getAllRegions()
	{		
		$query = "
		SELECT * 
		FROM `postcode_regions` AS pr
		LEFT JOIN `countries` AS c ON pr.`country_id` = c.`country_id`
		WHERE pr.`deleted` = 0 
		AND pr.`country_id` = {$_SESSION['country_default']}
		ORDER BY pr.`postcode_region_name` ASC";
			
		$result = mysqlMultiRows($query);
		
		return $result;
		
	}
	
	function getRegionData($id)
	{
		$query = "SELECT * FROM postcode_regions WHERE deleted = 0 AND postcode_region_id = {$id} LIMIT 1";
			
		$result = mysqlSingleRow($query);
		
		return $result;
	}
	
	function updateRegion($data, $id = 0)
	{
		$country_id = mysql_real_escape_string($data['country_id']);
		if(intval($id) > 0)
		{
			$query = "
			UPDATE postcode_regions 
			SET 
				`region`='".mysql_real_escape_string($data['region'])."', 
				`postcode_region_name` = '" . mysql_real_escape_string($data['postcode_region_name']) . "', 
				`postcode_region_postcodes` = '" . mysql_real_escape_string($data['postcode_region_postcodes']) . "'
			WHERE postcode_region_id = '".mysql_real_escape_string($id)."'
			LIMIT 1";
			mysql_query($query) or die(mysql_error());
		}
		else
		{
			# New Region
			//$query = "INSERT INTO postcode_regions SET postcode_region_name = '" . $data['postcode_region_name'] . "', postcode_region_postcodes = '" . $data['postcode_region_postcodes'] . "'";
			$query = "
			INSERT INTO 
			`postcode_regions` ( 
				`region`,
				`postcode_region_name`,
				`postcode_region_postcodes`,
				`country_id`
			)
			VALUE (
				'".mysql_real_escape_string($data['region'])."',
				'".mysql_real_escape_string($data['postcode_region_name'])."',
				'".mysql_real_escape_string($data['postcode_region_postcodes'])."',
				{$_SESSION['country_default']}
			)
			";
			mysql_query($query) or die(mysql_error());
			$id = mysql_insert_id();
		}
		
		return $id;
	}
	
	function deleteRegion($id)
	{

		$query = "UPDATE postcode_regions SET deleted = 1 WHERE postcode_region_id = {$id} LIMIT 1";
		if(mysql_query($query))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
	function cleanPostcodeString($string)
	{
		# basic cleansing, should result in numbers and commas only
		$string = preg_replace("/([^0-9,]+)/", "", $string);
		$string = preg_replace("/[,]+/",",", $string);
		$string = trim($string, ",");
		
		return $string;
	}
	
}

?>
