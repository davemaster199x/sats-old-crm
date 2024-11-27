<h1>Test Route Efficiency Ajax</h1>
<br />
<?php

include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');

// get accomdation address
function RE_getAccomodationAddress($accomodation_id){
	$sql = mysql_query("
		SELECT *
		FROM `accomodation`
		WHERE `accomodation_id` = {$accomodation_id}
	");
	$row = mysql_fetch_array($sql);
	return $row['address'];
}

// get distance
function test_getGoogleMapDistance($orig,$dest){
	
	// init curl object        
	$ch = curl_init();

	// API key
	// LIVE - SATS gmail
	//$API_key = 'AIzaSyBqYJ80rXXfOv5qrbQxXwIpU4H_WHctHHM';
	// TEST - my personal gmail
	$API_key = 'AIzaSyAlg-wLGSmPTbQ1Fgi5UXOPOhdLLtcbkdY';

	$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".rawurlencode($orig)."&destinations=".rawurlencode($dest)."&key={$API_key}";

	// define options
	$optArray = array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false
	);

	// apply those options
	curl_setopt_array($ch, $optArray);

	// execute request and get response
	$result = curl_exec($ch);


	$result_json = json_decode($result);


	curl_close($ch);

	return $result_json;

}


// get closest distance
function RE_getClosest($prop_arr){
	$closest_add = array();
	$smallest = 0;
	foreach($prop_arr as $index=>$val){
		if($index==0){
			$smallest = $val['distance'];
			$closest_add['smallest'] = $smallest;
			$closest_add['trr_id']= $val['trr_id'];
			$closest_add['job_id'] = $val['job_id'];
			$closest_add['property'] = $val['property'];
			$closest_add['distance'] = $val['distance'];
		}else{
			if($val['distance']<$smallest){
				$smallest = $val['distance'];
				$closest_add['smallest'] = $smallest;
				$closest_add['trr_id'] = $val['trr_id'];
				$closest_add['job_id'] = $val['job_id'];
				$closest_add['property'] = $val['property'];
				$closest_add['distance'] = $val['distance'];
			}
		}
	}
	return $closest_add;
}



$tr_id = mysql_real_escape_string($_POST['tr_id']);
$country_id = $_SESSION['country_default'];

// get start and end point
$mp_sql = mysql_query("
	SELECT *
	FROM `tech_run`
	WHERE `tech_run_id` = {$tr_id}
");
$mp = mysql_fetch_array($mp_sql);

$date = $mp['date'];
$tech_id = $mp['assigned_tech'];

$start = $mp['start'];
$start_add = RE_getAccomodationAddress($mp['start']);

$end = $mp['end'];
$end_add = RE_getAccomodationAddress($mp['end']);

// start and end point
echo "Start: ".$start_add;
echo "<br />";
echo "End: ".$end_add;
echo "<br />";





$p_sql = getTechRunRows($tr_id,$country_id);
$j = 2;
$hiddenRowsCount = 0;
$hideChk = 0;
$jobCount = 0;

while( $p= mysql_fetch_array($p_sql) ){
	
	$hiddenText = "";
	$showRow = 1;
	$isUnavailable = 0;
	$isHidden = 0;
	$isPriority = 0;
	
	if( $p['row_id_type'] == 'job_id' ){
		
		$jr_sql = getJobRowData($p['row_id'],$country_id);
			$row2 = mysql_fetch_array($jr_sql);
			
			// only show 240v rebook to electrician 
			if( $row2['job_type']=='240v Rebook' && $isElectrician==false ){
				$hiddenText .= '240v<br />';
				$showRow = 0;
			}else{
				$showRow = 1;
			}
			
			if( $p['hidden']==1 ){
				$hiddenText .= 'User<br />';
			}
			
			if( $row2['unavailable']==1 && $row2['unavailable_date']==$date ){
				$isUnavailable = 1;
				$hiddenText .= 'Unavailable<br />';
			}
			
			$startDate = date('Y-m-d',strtotime($row2['start_date']));
			
			if( $row2['job_type'] == 'Lease Renewal' && ( $row2['start_date']!="" && $date < $startDate ) ){
				$hiddenText .= 'LR<br />';
			}
			
			if( $row2['job_type'] == 'Change of Tenancy' && ( $row2['start_date']!="" && $date < $startDate  ) ){
				$hiddenText .= 'COT<br />';
			}
			
			if( $row2['j_status'] == 'DHA' && ( $row2['start_date']!="" && $date < $startDate ) ){
				$hiddenText .= 'DHA<br />';
			}
			
			if( $show_hidden==0 && $hiddenText!="" && $row2['j_status']!='Booked' ){
				$showRow = 0;
			}else{
				$showRow = 1;
			}
			
			
			
			
			$bgcolor = "#FFFFFF";
			if($row2['job_reason_id']>0){
				$bgcolor = "#fffca3";
			}else if($row2['ts_completed']==1){
				//$bgcolor = "#c2ffa7";
			}
			
			
			
			
			$j_created = date("Y-m-d",strtotime($row2['created']));
			$last_60_days = date("Y-m-d",strtotime("-60 days"));

			
			if( $p['dnd_sorted']==0 ){
				$bgcolor = '#FFFF00';
			}
			
			if( $hiddenText!="" ){
				$hiddenRowsCount++;
				//$bgcolor = "#ADD8E6";
				$isHidden = 1;
			}
			
			if( $show_hidden==1 && ( $p['hidden']==1 || $isUnavailable==1 ) ){
				$hideChk = 0;
			}else if( $show_hidden==1 ){
				$hideChk = 1;
			}else{
				$hideChk = 0;
			}
			
			
			if( $p['highlight_color']!="" ){
				//$bgcolor = $p['highlight_color'];
			}
			
			
			// priority jobs
			if( 
				$row2['job_type'] == "Change of Tenancy" || 
				$row2['job_type'] == "Lease Renewal" || 
				$row2['job_type'] == "Fix or Replace" || 
				$row2['job_type'] == "240v Rebook" || 
				$row2['j_status'] == 'DHA' ||
				$row2['urgent_job'] == 1 
			){
				$isPriority = 1;
			}else{
				$isPriority = 0;
			}
				
			
				
			if( $showRow==1 ){
				
				$trr_id_temp = $p['tech_run_rows_id'];
				$job_id_temp = $p['jid'];
				$prop_add_temp = "{$p['p_address_1']} {$p['p_address_2']} {$p['p_address_3']} {$p['p_state']} {$p['p_postcode']}";
				
				// get the distance
				$gm_dist = test_getGoogleMapDistance($start_add,$prop_add_temp);
				$distance = $gm_dist->rows[0]->elements[0]->distance->value;
				
				$prop_arr[] = array(
					"trr_id"=>$trr_id_temp,
					"job_id"=>$job_id_temp, 
					"property"=>$prop_add_temp, 
					"distance"=>$distance
				);
				
			}
		
		
		
	}
	
}

sleep(1);

echo "<br />";
$prop_count = count($prop_arr);
echo "<h1>Unsorted: {$prop_count}</h1>";
echo "<pre>";
print_r($prop_arr);
echo "</pre>";

// SORT
// get the closest
$smallest = 0;
$closest = RE_getClosest($prop_arr);

$prop_sorted[] = array(
	"trr_id"=>$closest['trr_id'],
	"job_id"=>$closest['job_id'], 
	"property"=>$closest['property'], 
	"distance"=>$closest['distance']
);
$smallest = $closest['smallest'];
$property = $closest['property'];

echo "Closest Distance: ".$smallest.", Property: {$property}<br />";

$prop_sort_count = count($prop_sorted);
echo "<h1>Sorted: {$prop_sort_count}</h1>";
echo "<pre>";
print_r($prop_sorted);
echo "</pre>";

echo "<br />Last Job Stack: ".$prop_sorted[$prop_sort_count-1]['job_id'];










$unsorted_prop_left_count = count($prop_arr)-1;	
while( $unsorted_prop_left_count > 0 ){
	
	unset($prop_left_unsorted);

	$prop_sort_count = count($prop_sorted);
	$last_job = $prop_sorted[$prop_sort_count-1];

	echo "<br />Last property on stack to run distance against: ".$last_job['property'];

	// loop through all properties
	foreach($prop_arr as $index=>$val){
		
		foreach($prop_sorted as $pst_val){
			$prop_sorted_job_id[] = $pst_val['job_id'];
		}
		
		if( !in_array($val['job_id'], $prop_sorted_job_id) ){
			
			$trr_id = $val['trr_id'];
			$job_id = $val['job_id'];
			$prop_add = $val['property'];
			
			// get the distance
			$gm_dist = test_getGoogleMapDistance($last_job['property'],$prop_add);
			$distance = $gm_dist->rows[0]->elements[0]->distance->value;
			
			$prop_left_unsorted[] = array(
				"trr_id"=>$trr_id,
				"job_id"=>$job_id, 
				"property"=>$prop_add, 
				"distance"=>$distance
			);
			
		}
		
	}
	
	sleep(1);

	$unsorted_prop_left_count = count($prop_left_unsorted);
	
	if( $unsorted_prop_left_count>0 ){
		
		echo "<h1>Run sort against {$unsorted_prop_left_count} unsorted properties</h1>";
		echo "<pre>";
		print_r($prop_left_unsorted);
		echo "</pre>";

		// get the closest
		$smallest = 0;
		$closest2 = RE_getClosest($prop_left_unsorted);

		$prop_sorted[] = array(
			"trr_id"=>$closest2['trr_id'],
			"job_id"=>$closest2['job_id'], 
			"property"=>$closest2['property'], 
			"distance"=>$closest2['distance']
		);
		$smallest = $closest2['smallest'];
		$property = $closest2['property'];

		echo "Closest distance: ".$smallest.", Property: {$property}<br />";

		$prop_sort_count = count($prop_sorted);
		echo "<h1>Sorted: {$prop_sort_count}</h1>";
		echo "<pre>";
		print_r($prop_sorted);
		echo "</pre>";
		
	}
	
	sleep(1);
	
}










	
	
echo "<br />";
echo "<br />";

// update sort index on db 
$sort_num = 2;
foreach( $prop_sorted AS $val3 ){

	// update
	$update_sql_str = "
		UPDATE `tech_run_rows`
		SET 
			`sort_order_num` = '{$sort_num}',
			`dnd_sorted` = 0
		WHERE `tech_run_rows_id` = {$val3['trr_id']}
		AND `tech_run_id` = {$tr_id}
		";
	mysql_query($update_sql_str);
	echo "<br />";
	$sort_num++;
}
	


	
?>