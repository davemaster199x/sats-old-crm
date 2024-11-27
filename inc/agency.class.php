<?

# The main user functions are within here

class Agency {

	function getAlarmPrices($agency_id)
	{
		# First check how many alarms there should be
		$query = "SELECT COUNT(*) as num_alarms FROM alarm_pwr";
		$result = mysqlSingleRow($query);
		
		$num_alarms = $result['num_alarms'];
		
		
		# First see if agency has their own pricing set
		$query = "SELECT apwr.alarm_pwr, apwr.alarm_pwr_id, ap.alarm_price FROM alarm_pwr apwr, alarm_price ap
					WHERE ap.alarm_pwr_id = apwr.alarm_pwr_id 
					AND ap.agency_id = $agency_id";
					
		$alarms = mysqlMultiRows($query);
		
		if(sizeof($alarms) != $num_alarms)		
		{
		
			# Otherwise fall back to default pricing
			$query = "SELECT apwr.alarm_pwr, apwr.alarm_pwr_id, ap.alarm_price FROM alarm_pwr apwr, alarm_price ap
						WHERE ap.alarm_pwr_id = apwr.alarm_pwr_id 
						AND ap.agency_id = 0";
						
			$alarms = mysqlMultiRows($query);			
		}

		return $alarms;
	}
	
	function getAlarmPwrIDs()
	{
		$query = "SELECT alarm_pwr_id FROM alarm_pwr";
		
		$result = mysqlMultiRows($query);
		
		$return_array = array();
		
		foreach($result as $alarm) $return_array[$alarm['alarm_pwr_id']] = 1;

		return $return_array;
	}
	
	function wipeAlarms($agency_id)
	{
		$query = "DELETE FROM alarm_price WHERE agency_id = {$agency_id}";
		if(mysql_query($query))
		{
			return true;
		}
		else 
		{
			return false;
		}
	}
}

?>
