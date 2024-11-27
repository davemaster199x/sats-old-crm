<?php

 /* Query to find all duplicate Entries */
	function getDuplicateProperties(){
		return mysql_query("
			SELECT 
				  address_1,
				  address_2,
				  address_3,
				  state,
				  postcode,
				  property_id,
				  deleted,
				  agency_deleted,
				  COUNT(*) AS quantity 
				FROM
				  property 
				  WHERE deleted = 0 
				GROUP BY address_1,
				  address_2,
				  address_3,
				  state,
				  postcode 
				HAVING quantity > 1;
		");		
	}			
	

	function getDuplicatePropertiesDetails($id){
		
		return mysql_query("
			SELECT 
		  p1.*,
		  a.`agency_name`,
		  a.`address_1` AS a_address_1,
		  a.`address_2` AS a_address_2,
		  a.`address_3` AS a_address_3,
		  a.`state` AS a_state,
		  a.`postcode` AS a_postcode
		FROM
		  property p1
		  LEFT JOIN `agency` AS a ON p1.`agency_id` = a.`agency_id`
		WHERE REPLACE(UPPER(CONCAT(
			TRIM(UPPER(p1.address_1)),
			TRIM(UPPER(p1.address_2)),
			TRIM(UPPER(p1.address_3)),
			TRIM(UPPER(p1.state)),
			TRIM(UPPER(p1.postcode))
		  )),' ','')IN 
		  (SELECT 
			REPLACE(CONCAT(
			  TRIM(UPPER(address_1)),
			  TRIM(UPPER(address_2)),
			  TRIM(UPPER(address_3)),
			  TRIM(UPPER(state)),
			  TRIM(UPPER(postcode))
			),' ','') 
		  FROM
			property p 
		  WHERE p.`property_id` = ". $id .")
		");
	
	}

	function getDuplicateProperties2($count,$start,$limit,$distinct_agency,$phrase,$agency){
	
		
		$str = "";
		
		
		if($count==1){
			$count_str .= "count( t2.`property_id` ) AS cnt";
		}else{
		
			if($distinct_agency==1){
				$count_str .= "DISTINCT( a.`agency_id` ), a.`agency_name`";
			}else{
				$count_str .= "t2.`property_id` , t2.`address_1` , t2.`address_2` , t2.`address_3` , t2.`state` , t2.`postcode` , a.`agency_id` , a.`agency_name`";
			}	

			if($phrase!=""){
				$str .= " AND CONCAT_WS(' ', LOWER(t2.`address_1`), LOWER(t2.`address_2`), LOWER(t2.`address_3`), LOWER(t2.`state`), LOWER(t2.`postcode`) ) LIKE '%".strtolower(trim($phrase))."%' ";
			}
				
			if($agency!=""&&$agency!="Any"){
				$str .= " AND a.`agency_id` = {$agency} ";
			}	
			
			if($distinct_agency==1){
				$str .= " ORDER BY a.`agency_name` ";
			}else{
				$str .= " ORDER BY t2.`address_2` ";
			}
			
			if(is_numeric($start) && is_numeric($limit)){
				$str .= " LIMIT {$start}, {$limit}";
			}			
		}
		
		
		
		$sql = "
			SELECT {$count_str}
			FROM (
			SELECT p.`address_1` , p.`address_2` , p.`address_3` , p.`state` , p.`postcode` , p.`deleted` , p.`agency_deleted` , count( * ) AS cnt
			FROM `property` AS p
			WHERE p.`deleted` =0
			AND p.`agency_deleted` =0
			AND p.`address_1` != ''
			AND p.`address_2` != ''
			AND p.`address_3` != ''
			AND p.`state` != ''
			AND p.`postcode` !=0
			GROUP BY p.`address_1` , p.`address_2` , p.`address_3` , p.`state` , p.`postcode`
			AND p.`deleted` =0
			AND p.`agency_deleted` =0
			HAVING cnt >1
			) AS t1
			JOIN `property` AS t2 ON t1.address_1 = t2.address_1
			AND t1.address_2 = t2.address_2
			AND t1.address_3 = t2.address_3
			AND t1.state = t2.state
			AND t1.postcode = t2.postcode
			LEFT JOIN `agency` AS a ON t2.`agency_id` = a.`agency_id`
			{$str}
		";
		return mysql_query($sql);
	}
	
	function getDuplicatePropertiesDistinctAgencies2(){
		
		$sql = "
			SELECT DISTINCT(p.`agency_id`), p.`property_id` , p.`address_1` , p.`address_2` , p.`address_3` , p.`state` , p.`postcode` , p.`deleted` , p.`agency_id` , a.`agency_name`, a.`status` , count( * )
			FROM `property` AS p
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			GROUP BY p.`address_1` , p.`address_2` , p.`address_3` , p.`state` , p.`postcode`
			HAVING count( * ) >1
			AND p.`deleted` =0
			AND a.`status` = 'active'
			ORDER BY a.`agency_name`
		";
		
		return mysql_query($sql);
	}
	
	function duplicate_counter(){
		return mysql_query("
			SELECT count( t2.property_id ) AS cnt2
			FROM (
				SELECT p.`address_1` , p.`address_2` , p.`address_3` , p.`state` , p.`postcode` , p.`deleted` , p.`agency_deleted` , count( * ) AS cnt
				FROM `property` AS p
				WHERE p.`deleted` =0
				AND p.`agency_deleted` =0
				AND p.`address_1` != ''
				AND p.`address_2` != ''
				AND p.`address_3` != ''
				AND p.`state` != ''
				AND p.`postcode` !=0
				GROUP BY p.`address_1` , p.`address_2` , p.`address_3` , p.`state` , p.`postcode`
				AND p.`deleted` =0
				AND p.`agency_deleted` =0
				HAVING cnt >1
			) AS t1
			JOIN `property` AS t2 ON t1.address_1 = t2.address_1
			AND t1.address_2 = t2.address_2
			AND t1.address_3 = t2.address_3
			AND t1.state = t2.state
			AND t1.postcode = t2.postcode
			LEFT JOIN `agency` AS a ON t2.`agency_id` = a.`agency_id`
			GROUP BY t2.`address_1` , t2.`address_2` , t2.`address_3` , t2.`state` , t2.`postcode`
			HAVING cnt2 >1
		");
	}
	
	// Find duplicate properties
	function jFindDupProp($start,$limit){
		
		// paginate
		if(is_numeric($start) && is_numeric($limit)){
			$str = "LIMIT {$start}, {$limit}";
		}
		
		return mysql_query("
			SELECT 
				p.property_id, 
				p.`address_1`, 
				p.`address_2`, 
				p.`address_3`, 
				p.`state`, 
				p.`postcode`, 
				p.`deleted`, 
				COUNT( * ) AS jcount,
				
				a.`agency_id`,
				a.`agency_name`
				
			FROM `property` AS p
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE p.`address_1` != ''
			AND p.`address_2` != ''
			AND p.`address_3` != ''
			AND a.`country_id` = {$_SESSION['country_default']}
			GROUP BY TRIM( p.`address_1` ) , TRIM( p.`address_2` ) , TRIM( p.`address_3` ) , TRIM( p.`state` ) , TRIM( p.`postcode` )
			HAVING jcount >1
			{$str}
		");
	}
	
	// find the other duplicate property
	function jGetOtherDupProp($property_id,$address_1,$address_2,$address_3,$state,$postcode){
		return mysql_query("
			SELECT 
				p.property_id, 
				p.`address_1`, 
				p.`address_2`, 
				p.`address_3`, 
				p.`state`, 
				p.`postcode`, 
				p.`deleted`,
				
				a.`agency_id`,
				a.`agency_name`
			FROM `property` AS p 
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE TRIM(LCASE(p.`address_1`)) = LCASE('". trim($address_1) ."') 
			  AND TRIM(LCASE(p.`address_2`)) = LCASE('". trim($address_2) ."') 
			  AND TRIM(LCASE(p.`address_3`)) = LCASE('". trim($address_3) ."') 
			  AND TRIM(LCASE(p.`state`)) = LCASE('". trim($state) ."') 
			  AND TRIM(LCASE(p.`postcode`)) = LCASE('". trim($postcode) ."')
			AND p.`property_id` != {$property_id}
			AND a.`country_id` = {$_SESSION['country_default']}
		");
	}
	
	function jDupPropCount(){
		return mysql_query("
			SELECT p.property_id, p.`address_1`, p.`address_2`, p.`address_3`, p.`state`, p.`postcode`, p.`deleted`, COUNT( * ) AS jcount
			FROM `property` AS p
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE p.`address_1` != ''
			AND p.`address_2` != ''
			AND p.`address_3` != ''
			AND a.`country_id` = {$_SESSION['country_default']}
			GROUP BY TRIM( p.`address_1` ) , TRIM( p.`address_2` ) , TRIM( p.`address_3` ) , TRIM( p.`state` ) , TRIM( p.`postcode` )
			HAVING jcount >1
		");
	}

?>