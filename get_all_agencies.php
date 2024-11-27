<?php
	include('inc/init.php');
	$odd= 0;
	$agencies_html = '';
	
	$agencies_html .= '<tr bgcolor="#DDDDDD">
						<td><b>Agency Name</b></td>
						<td><b>Address</b></td>
						<td><b>State</b></td>
						<td><b>Region</b></td>
						<td><b>Properties</b></td>
						<td><b>Last Contact</b></td>
						<td><b>Next Contact</b></td>
					</tr>';
	
	$results = mysql_query("SELECT a.agency_name, a.address_1, a.address_2, a.address_3, a.state, a.postcode, a.status, a.agency_id, DATE_FORMAT(MAX(c.eventdate),'%d/%m/%Y') as logdate, ar.agency_region_name, a.tot_properties 
				FROM agency a LEFT JOIN  agency_regions ar USING (agency_region_id) LEFT JOIN crm c ON a.agency_id = c.agency_id 
				WHERE a.status IN ('target','active') GROUP BY a.agency_id  ORDER BY agency_name", $connection);
				
	while ($row = mysql_fetch_row($results))
	{
		$odd++;
		if (is_odd($odd)) {
			$agencies_html .= "<tr bgcolor=#FFFFFF>";		
		} else {
			$agencies_html .= "<tr bgcolor=#efebef>";
		}
		
		$agencies_html .= "<td>";		
		$agencies_html .= "<a href='view_target_details.php?id=$row[7]'>$row[0]</a>";
		$agencies_html .= "</td>\n";
		
		$agencies_html .= "<td>";		
		$agencies_html .= $row[1] . " " . $row[2] . " " . $row[3];
		$agencies_html .= "</td>\n";

		$agencies_html .= "<td>";		
		$agencies_html .= $row[4];
		$agencies_html .= "</td>\n";

		$agencies_html .= "<td>";		
		$agencies_html .= $row[9];
		$agencies_html .= "</td>\n";
		
		$agencies_html .= "<td>";		
		$agencies_html .= $row[10];
		$agencies_html .= "</td>\n";
		
		//echo "<td>";		
		//echo $row[6];
		//echo "</td>\n";
		
		$agencies_html .= "<td>";		
		$agencies_html .= $row[8];
		$agencies_html .= "</td>\n";
		
		$agencies_html .= "<td>";		
		//echo $row[8];
		$agencies_html .= "</td>\n";
		
		$agencies_html .= '</tr>';
	}
	
	echo $agencies_html;

?>