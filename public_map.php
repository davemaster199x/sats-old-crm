<?php

// init curl object        
$ch = curl_init();

// data
$tech_id = filter_var($_GET['tech_id'], FILTER_SANITIZE_STRING);
$day = filter_var($_GET['day'], FILTER_SANITIZE_STRING);
$month = filter_var($_GET['month'], FILTER_SANITIZE_STRING);
$year =filter_var($_GET['year'], FILTER_SANITIZE_STRING);
$date = filter_var("{$year}-{$month}-{$day}", FILTER_SANITIZE_STRING);
$country_id = filter_var($_GET['country_id'], FILTER_SANITIZE_STRING);
$domain = $_SERVER['SERVER_NAME'];

//echo date_default_timezone_get();
//date_default_timezone_set('Australia/Melbourne'); //or change to whatever timezone you want

$dev_str = (strpos($domain,"crmdev")==false)?'':'dev';

// GET COUNTRY
// api url
	$url = "http://crm{$dev_str}.sats.com.au/map_api.php?opt=get_country&tech_id={$tech_id}&day={$day}&month={$month}&year={$year}&country_id={$country_id}";

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
$result_json = json_decode($result,true);
$country = $result_json;
// get country name
$country_name = $country['country_name'];

?>
<html>
<head>
<link href="css/mainsite.css" type="text/css" rel="stylesheet">
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<title>SATS - public map</title>
</head>
<body>

<div id="map-canvas" style="width:100%;height:500px;border:1px solid #cccccc;"></div>

<div id="distance"></div>

<?php

$prop_address = array();
$i = 0;
$ctr = 1;
	
?>

<table id="tbl_maps" border=0 cellspacing=0 cellpadding=5 width=100% class="table-left tbl-fr-red">
<tr class="nodrop nodrag" bgcolor="#b4151b" style="border-bottom: 1px solid #B4151B !important;">
<th>#</th>
<th>Created</th>
<th>Notes</th>
<th>Booking Time</th>
<th>Status</th>
<th>Job Type</th>
<th>Service</th>
<th>DK</th>
<th>Address</th>
<th>Agency</th>
<th>Time</th>
<th>Distance</th>
<th>Completed</th>
<th>#</th>
</tr>


<tr class="nodrop nodrag" style="background-color:#ffffff;">
<td colspan="3">
<?php 

	// start point
	echo $ctr; 
	
	// api url
	$url = "http://crm{$dev_str}.sats.com.au/map_api.php?opt=start&tech_id={$tech_id}&day={$day}&month={$month}&year={$year}&country_id={$country_id}";

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
	$result_json = json_decode($result,true);
	// get start point
	$start_acco = $result_json;

	if(count($start_acco)>0){	
	
		$prop_address[$i]['address'] = "{$start_acco['address']}, {$country_name}";
		$prop_address[$i]['lat'] = $start_acco['lat'];
		$prop_address[$i]['lng'] = $start_acco['lng'];
		
		$i++;
		
		$start_agency_name = $start_acco['name'];
		$start_agency_address = $start_acco['address'];
		
	}

?>
</td>
<td colspan="3"><?php echo $start_agency_name; ?></td>
<td><img src="/images/red_house_resized.png" /></td>
<td>&nbsp;</td>
<td><?php echo $start_agency_address; ?></td>
<td>&nbsp;</td>
<td class="time">&nbsp;</td>
<td class="distance">&nbsp;</td>
<td>&nbsp;</td>
<td><?php 
echo $ctr; 
$ctr++; 
?></td>
</tr>

<?php

// JOBS
// api url
$url = "http://crm{$dev_str}.sats.com.au/map_api.php?opt=jobs&tech_id={$tech_id}&day={$day}&month={$month}&year={$year}&country_id={$country_id}";

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
$result_json = json_decode($result,true);

$jr_arr = $result_json;
$jr_count = $jr_arr['count'];

// KEYS
// api url
$url = "http://crm{$dev_str}.sats.com.au/map_api.php?opt=keys&tech_id={$tech_id}&day={$day}&month={$month}&year={$year}&country_id={$country_id}";

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
$result_json = json_decode($result,true);

$kr_arr = $result_json;
$kr_count = $kr_arr['count'];

$total_list = ($jr_count+$kr_count);

$total_map_routes = $total_list+1;
$job_ctr = 0;
for($j=2;$j<=$total_map_routes;$j++){ 

	// KEYS
	if(array_key_exists($j, $kr_arr)){ 

	?>
		<tr id="key_routes_id:<?php echo $kr_arr[$j]['key_routes_id']; ?>" style="background-color:<?php echo ($kr_arr[$j]['completed']==1)?'#c2ffa7':'#eeeeee'; ?>;">
			<td colspan="3"><?php echo $j; ?></td>
			<td>
				<?php 
					if($kr_arr[$j]['completed']==1){
						$kr_act = explode(" ",$kr_arr[$j]['action']);
						$temp2 = ($kr_arr[$j]['action']=="Drop Off")?'p':'';
						$temp = "{$kr_act[0]}{$temp2}ed";
						$action = "{$temp} {$kr_act[1]}";
					}else{
						$action = $kr_arr[$j]['action'];
					}
					echo $action;
				?>
			</td>
			<td colspan="2"><?php echo $kr_arr[$j]['agency_name']; ?></td>
			<td><img src="/images/key_icon.png" /></td>
			<td>&nbsp;</td>
			<td><?php echo "{$kr_arr[$j]['address_1']} {$kr_arr[$j]['address_2']}, {$kr_arr[$j]['address_3']}"; ?></td>			
			<td><?php echo $kr_arr[$j]['agency_name']; ?></td>
			<td class="time">&nbsp;</td>
			<td class="distance">&nbsp;</td>
			<td><?php echo ($kr_arr[$j]['completed_date']!="")?date("H:i",strtotime($kr_arr[$j]['completed_date'])):""; ?></td>			
			<td><?php echo $j; ?></td>
		</tr>
	<?php
	// get gecode
	$prop_address[$i]['address'] = "{$kr_arr[$j]['address_1']} {$kr_arr[$j]['address_2']} {$kr_arr[$j]['address_3']} {$kr_arr[$j]['state']} {$kr_arr[$j]['postcode']}, {$country_name}";
	$prop_address[$i]['is_keys'] = 1;
	$prop_address[$i]['lat'] = $kr_arr[$j]['lat'];
	$prop_address[$i]['lng'] = $kr_arr[$j]['lng'];
	$i++;
	}

	// JOBS
	if(array_key_exists($j, $jr_arr)){ 
	
		$bgcolor = "#FFFFFF";
		if($jr_arr[$j]['job_reason_id']>0){
			$bgcolor = "#fffca3";
		}else  if($jr_arr[$j]['ts_completed']==1){
			$bgcolor = "#c2ffa7";
		}
		
		
		$j_created = date("Y-m-d",strtotime($jr_arr[$j]['created']));
		$last_60_days = date("Y-m-d",strtotime("-60 days"));
		
		if( $jr_arr[$j]['j_status']=='To Be Booked' && $j_created<$last_60_days ){
			$bgcolor = '#7ba6c6';
		}
		
	?>
		<tr id="jobs_id:<?php echo $jr_arr[$j]['jid']; ?>" style="background-color:<?php echo $bgcolor; ?>">
			<td><?php echo $j; ?></td>
			<td><?php echo ( $jr_arr[$j]['created']!="" && $jr_arr[$j]['created']!="0000-00-00" )?date("d/m/Y",strtotime($jr_arr[$j]['created'])):''; ?></td>
			<td><?php echo $jr_arr[$j]['tech_notes']; ?></td>
			<td><?php echo $jr_arr[$j]['time_of_day']; ?></td>	
			<td class="jstatus"><?php echo $jr_arr[$j]['j_status']; ?></td>
			<td>
				<?php
				// job type
				switch($jr_arr[$j]['job_type']){
					case 'Once-off':
						$jt = 'Once-off';
					break;
					case 'Change of Tenancy':
						$jt = 'COT';
					break;
					case 'Yearly Maintenance':
						$jt = 'YM';
					break;
					case 'Fix or Replace':
						$jt = 'FR';
					break;
					case '240v Rebook':
						$jt = '240v';
					break;
					case 'Lease Renewal':
						$jt = 'LR';
					break;
				}	
				?>
				<?php echo $jt; ?>
			</td>
			<td>
				<?php
				switch($jr_arr[$j]['j_service']){
					case 2:
						$serv_color = 'b4151b';
						$serv_icon = 'smoke_colored.png';
					break;
					case 5:
						$serv_color = 'f15a22';
						$serv_icon = 'safety_colored.png';
					break;
					case 6:
						$serv_color = '00ae4d';
						$serv_icon = 'corded_colored.png';
					break;
					case 7:
						$serv_color = '00aeef';
						$serv_icon = 'pool_colored.png';
					break;
					case 8:
						$serv_color = '9b30ff';
						$serv_icon = 'sa_ss_colored.png';
					break;
					case 9:
						$serv_color = '9b30ff';
						$serv_icon = 'sa_cw_ss_colored.png';
					break;
				}
				?>
				<img src="images/serv_img/<?php echo $serv_icon; ?>" />
			</td>
			<td><?php echo ($jr_arr[$j]['door_knock']==1)?'DK':''; ?></td>
			<td><?php echo $jr_arr[$j]['p_address_1']." ".$jr_arr[$j]['p_address_2'].", ".$jr_arr[$j]['p_address_3']; ?></td>						
			<td><?php echo $jr_arr[$j]['agency_name']; ?></td>
			<td class="time">&nbsp;</td>
			<td class="distance">&nbsp;</td>
			<?php
			if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){
				echo "<td>".(($jr_arr[$j]['completed_timestamp']!="")?date("H:i",strtotime($jr_arr[$j]['completed_timestamp'])):'')."</td>";
			}
			?>
			<td><?php echo $j; ?></td>
		</tr>
		<?php	
			// store it on property address array
			$prop_address[$i]['address'] = "{$jr_arr[$j]['p_address_1']} {$jr_arr[$j]['p_address_2']} {$jr_arr[$j]['p_address_3']} {$jr_arr[$j]['p_state']} {$jr_arr[$j]['p_postcode']}, {$country_name}";
			$prop_address[$i]['status'] = $jr_arr[$j]['j_status'];
			$prop_address[$i]['created'] = date("Y-m-d",strtotime($jr_arr[$j]['created']));
			$prop_address[$i]['urgent_job'] = $jr_arr[$j]['urgent_job'];
			$prop_address[$i]['lat'] = $jr_arr[$j]['p_lat'];
			$prop_address[$i]['lng'] = $jr_arr[$j]['p_lng'];
			$i++;
	}

}
?>


<tr class="nodrop nodrag" style="background-color:#ffffff;">
<td colspan="3">
<?php 

// END POINT

if($mp['start']=="" && $mp['end']==""){
	$end_point_index = $total_list+2;
}else{
	$end_point_index = $j; 
}
echo $end_point_index;

// api url
$url = "http://crm{$dev_str}.sats.com.au/map_api.php?opt=end&tech_id={$tech_id}&day={$day}&month={$month}&year={$year}&country_id={$country_id}";

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
$result_json = json_decode($result,true);

$end_acco = $result_json;

if(count($end_acco)>0){	

	$prop_address[$i]['address'] = "{$end_acco['address']}, {$country_name}";
	$prop_address[$i]['lat'] = $end_acco['lat'];
	$prop_address[$i]['lng'] = $end_acco['lng'];

	$i++;
	
	$end_agency_name = $end_acco['name'];
	$end_agency_address = $end_acco['address'];
	
}

?>
</td>
<td colspan="3"><?php echo $end_agency_name; ?></td>
<td><img src="/images/red_house_resized.png" /></td>
<td>&nbsp;</td>
<td><?php echo $end_agency_address; ?>
</td>
<td>&nbsp;</td>	
<td class="time">&nbsp;</td>
<td class="distance">&nbsp;</td>
<td>&nbsp;</td>
<td><?php echo $i;  ?></td>
<?php
$ctr++;
?>
</tr>

<tr class="nodrop nodrag">
<td colspan="10">TOTAL</td>
<td id="tot_time">0</td>
<td id="tot_dis">0</td>
<td colspan="2">&nbsp;</td>
</tr>

</table>





<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAa9QRZRQ3eucZ6OE18rSSi8a7VGJjoXQE"></script>
<script type="text/javascript">



// variables
var markersArray = [];
var map;
var directionsService = new google.maps.DirectionsService();
var distances = "";
var icon = 'images/car.png';
var image;
<?php
if(count($start_acco)>0){ ?>
var jcount = 1;
<?php
}else{ ?>
var jcount = 2;
<?php	
}
?>

var tot_time = 0;
var tot_dis = 0;
var orig_dur = 0;




<?php



$js_array = json_encode($prop_address);
//echo "var prop_address = ". $js_array . "; ";
//echo "var prop_address2 = ". $result . "; ";
?>

var prop_address = <?php echo $js_array.";"; ?>

//var prop_address = <?php echo $result.";"; ?>

//console.log(prop_address);
function jMarkerCustomIcon(image,x,y){
	
	console.log("X: "+x+" Y: "+y);
	
	// custom icon
	var icon = {
		url: image,
		// This marker is 29 pixels wide by 44 pixels tall.
		size: new google.maps.Size(34, 44),
		// The origin for this image is 0,0.
		origin: new google.maps.Point(x,y)
	};
	return icon;
}

function calculateDistances(start,destination) {
  var service = new google.maps.DistanceMatrixService();
  service.getDistanceMatrix(
    {
      origins: [start],
      destinations: [destination],
      travelMode: google.maps.TravelMode.DRIVING,
      unitSystem: google.maps.UnitSystem.METRIC,
      avoidHighways: false,
      avoidTolls: false
    }, callback);
}

function callback(response, status) {
	
var jtext = "";

if (status != google.maps.DistanceMatrixStatus.OK) {
	
	alert('Error was: ' + status);
	
}else{
	
	var origins = response.originAddresses;
	var destinations = response.destinationAddresses;
	//var outputDiv = document.getElementById('outputDiv');
	//outputDiv.innerHTML = '';
	//deleteOverlays();

	for (var i = 0; i < origins.length; i++) {
		var results = response.rows[i].elements;
		//addMarker(origins[i], false);
		for (var j = 0; j < results.length; j++) {
			//addMarker(destinations[j], true);
			/*
			outputDiv.innerHTML += origins[i] + ' to ' + destinations[j]
			+ ': ' + results[j].distance.text + ' in '
			+ results[j].duration.text + '<br>';
			*/
			jtext += 'index: '+jcount+' - '+origins[i] + ' ---- ' + destinations[j]
			+ ': ' + results[j].distance.text + ' in '
			+ results[j].duration.text + ' value: '+results[j].duration.value+'\n';

			console.log(jtext);

			jQuery(".time:eq("+jcount+")").html(results[j].duration.text);
			jQuery(".distance:eq("+jcount+")").html(results[j].distance.text);

			tot_time += parseFloat(results[j].duration.text);
			tot_dis += parseFloat(results[j].distance.text);
			orig_dur += results[j].duration.value;

			var totalSec = orig_dur;
			var hours = parseInt( totalSec / 3600 ) % 24;
			var minutes = parseInt( totalSec / 60 ) % 60;
			var seconds = totalSec % 60;
			var time_str = "";
			if(hours==0){
				time_str = minutes+" mins";				
			}else{
				time_str = hours+" hours "+minutes+" mins";
			}
			jQuery("#tot_time").html(time_str);
			//jQuery("#tot_time").html(tot_time+" mins");
			jQuery("#tot_dis").html(tot_dis.toFixed(1)+" km");

			jcount++;
		}
	}

}
  
}

function addMarker(location, isDestination) {
  var icon;
  if (isDestination) {
    icon = destinationIcon;
  } else {
    icon = originIcon;
  }
  geocoder.geocode({'address': location}, function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      bounds.extend(results[0].geometry.location);
      map.fitBounds(bounds);
      var marker = new google.maps.Marker({
        map: map,
        position: results[0].geometry.location
      });
      markersArray.push(marker);
    } else {
      alert('Geocode was not successful for the following reason: '
        + status);
    }
  });
}

function deleteOverlays() {
  for (var i = 0; i < markersArray.length; i++) {
    markersArray[i].setMap(null);
  }
  markersArray = [];
}

// add markers
function jAddMarkers(position,popupcontent,icon){
	
	//icon = (icon!="")?icon:'images/car.png';
	
	var beachMarker = new google.maps.Marker({
      position: position,
      map: map,
	  icon: icon
  });
  
  // pop up
  jAddPopUpWindow(beachMarker,popupcontent);
  
}

function jAddPopUpWindow(beachMarker,contentString){
  var infowindow = new google.maps.InfoWindow({
      content: contentString
  });
  
  google.maps.event.addListener(beachMarker, 'click', function() {
    infowindow.open(map,beachMarker);
  });
}

function initialize() {

	var center = new google.maps.LatLng(prop_address[0]['lat'], prop_address[0]['lng']);

	// instantiate map properties
	var mapOptions = {
		zoom: 13,  // zoom - 0 for maxed out out of earth 
		center: center // where to focus view
	}

	// create the map
	map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
	
	
	// insert original script here
	  
	 
	  
	  var i = 1;
	  var max_prop = prop_address.length;
	  var last_prop_index = (max_prop-1);
	  var first_index = 0;
	  var last_index = 9;
	  
	  var prop_index = 0;
	  
	  // pin number sprite positions, currently 1-50 max
	  var sprite_num_str = '{ "pins" : [' +
	     // 1 - 10
		'{ "x":10 , "y":5 },' +
		'{ "x":10 , "y":58 },' +
		'{ "x":10 , "y":111 },' +
		'{ "x":10 , "y":165 },' +
		'{ "x":10 , "y":219 },' +
		'{ "x":10 , "y":272 },' +
		'{ "x":10 , "y":326 },' +
		'{ "x":10 , "y":379 },' +
		'{ "x":10 , "y":431 },' +
		'{ "x":10 , "y":483 },' +
		// 11 - 20
		'{ "x":62 , "y":5 },' +
		'{ "x":62 , "y":58 },' +
		'{ "x":62 , "y":111 },' +
		'{ "x":62 , "y":165 },' +
		'{ "x":62 , "y":219 },' +
		'{ "x":62 , "y":272 },' +
		'{ "x":62 , "y":326 },' +
		'{ "x":62 , "y":379 },' +
		'{ "x":62 , "y":431 },' +
		'{ "x":62 , "y":483 },' +
		// 21 - 30
		'{ "x":115 , "y":5 },' +
		'{ "x":115 , "y":58 },' +
		'{ "x":115 , "y":111 },' +
		'{ "x":115 , "y":165 },' +
		'{ "x":115 , "y":219 },' +
		'{ "x":115 , "y":272 },' +
		'{ "x":115 , "y":326 },' +
		'{ "x":115 , "y":379 },' +
		'{ "x":115 , "y":431 },' +
		'{ "x":115 , "y":483 },' +
		// 31 - 40
		'{ "x":167 , "y":5 },' +
		'{ "x":167 , "y":58 },' +
		'{ "x":167 , "y":111 },' +
		'{ "x":167 , "y":165 },' +
		'{ "x":167 , "y":219 },' +
		'{ "x":167 , "y":272 },' +
		'{ "x":167 , "y":326 },' +
		'{ "x":167 , "y":379 },' +
		'{ "x":167 , "y":431 },' +
		'{ "x":167 , "y":483 },' +
		// 41 - 50
		'{ "x":218 , "y":5 },' +
		'{ "x":218 , "y":58 },' +
		'{ "x":218 , "y":111 },' +
		'{ "x":218 , "y":165 },' +
		'{ "x":218 , "y":219 },' +
		'{ "x":218 , "y":272 },' +
		'{ "x":218 , "y":326 },' +
		'{ "x":218 , "y":379 },' +
		'{ "x":218 , "y":431 },' +
		'{ "x":218 , "y":483 },' +
		// 51 - 60
		'{ "x":260 , "y":5 },' +
		'{ "x":260 , "y":58 },' +
		'{ "x":260 , "y":111 },' +
		'{ "x":260 , "y":165 },' +
		'{ "x":260 , "y":219 },' +
		'{ "x":260 , "y":272 },' +
		'{ "x":260 , "y":326 },' +
		'{ "x":260 , "y":379 },' +
		'{ "x":260 , "y":431 },' +
		'{ "x":260 , "y":483 },' +
		// 61 - 70
		'{ "x":301 , "y":5 },' +
		'{ "x":301 , "y":58 },' +
		'{ "x":301 , "y":111 },' +
		'{ "x":301 , "y":165 },' +
		'{ "x":301 , "y":219 },' +
		'{ "x":301 , "y":272 },' +
		'{ "x":301 , "y":326 },' +
		'{ "x":301 , "y":379 },' +
		'{ "x":301 , "y":431 },' +
		'{ "x":301 , "y":483 },' +
		// 71 - 80
		'{ "x":343 , "y":5 },' +
		'{ "x":343 , "y":58 },' +
		'{ "x":343 , "y":111 },' +
		'{ "x":343 , "y":165 },' +
		'{ "x":343 , "y":219 },' +
		'{ "x":343 , "y":272 },' +
		'{ "x":343 , "y":326 },' +
		'{ "x":343 , "y":379 },' +
		'{ "x":343 , "y":431 },' +
		'{ "x":343 , "y":483 },' +
		// 81 - 90
		'{ "x":382 , "y":5 },' +
		'{ "x":382 , "y":58 },' +
		'{ "x":382 , "y":111 },' +
		'{ "x":382 , "y":165 },' +
		'{ "x":382 , "y":219 },' +
		'{ "x":382 , "y":272 },' +
		'{ "x":382 , "y":326 },' +
		'{ "x":382 , "y":379 },' +
		'{ "x":382 , "y":431 },' +
		'{ "x":382 , "y":483 },' +
		// 91 - 100
		'{ "x":420 , "y":5 },' +
		'{ "x":420 , "y":58 },' +
		'{ "x":420 , "y":111 },' +
		'{ "x":420 , "y":165 },' +
		'{ "x":420 , "y":219 },' +
		'{ "x":420 , "y":272 },' +
		'{ "x":420 , "y":326 },' +
		'{ "x":420 , "y":379 },' +
		'{ "x":420 , "y":431 },' +
		'{ "x":420 , "y":483 } ] }';
		
		var sprite_json = JSON.parse(sprite_num_str);
		
		//console.log("X: "+sprite_json.pins[prop_index].x+" Y: "+sprite_json.pins[prop_index].y);
	   
	  //  set interval
	  timer = setInterval(function(){ 			
			
			// if last index is reach stop interval calls
			if(parseInt(prop_index)==parseInt(last_prop_index)){
				
				// clear timer
				console.log('stop timer');
				clearInterval(timer);
				
			}else{
				
				//console.log('loop: '+i+' first index: '+first_index+' last index: '+last_index);
			
				// instantiate direction object
				var directionsDisplay = new google.maps.DirectionsRenderer({
					'suppressMarkers': true
				});
				// set directions
				 directionsDisplay.setMap(map);

			
				  // distance
				var start_dis = new google.maps.LatLng(prop_address[prop_index]['lat'], prop_address[prop_index]['lng']);
				var end_dis = new google.maps.LatLng(prop_address[prop_index+1]['lat'], prop_address[prop_index+1]['lng']);
				//console.log("Distance From: "+prop_address[prop_index]['address']+" To: "+prop_address[prop_index+1]['address']);
				calculateDistances(start_dis,end_dis);
				 
				 console.log("start index: "+prop_index+" -  Address: "+prop_address[prop_index].address+" - last property index: "+last_prop_index+" x: "+sprite_json.pins[prop_index].x+" y: "+sprite_json.pins[prop_index].y);
				 var startObj = prop_address[prop_index];
				 var start = new google.maps.LatLng(startObj['lat'], startObj['lng']);
		
				 
				 // home point, index = 0
				 if(parseInt(prop_index)==0){
					
					// if sprite number is available, currently max is 50
					if(sprite_json.pins[prop_index]!=""){
						
						var image = 'images/google_map/house_pin.png';
						var icon = jMarkerCustomIcon(image,sprite_json.pins[prop_index].x,sprite_json.pins[prop_index].y);
						
					}else{
						var icon = "";
					}
										
				}else{ // start 
					 
					// if sprite number is available, currently max is 50
					if(sprite_json.pins[prop_index]!=""){
						
						var jdate = new Date(startObj['created']);
						var last_60_day = new Date('<?php echo date("Y-m-d",strtotime("-60 days")); ?>');
						
						//console.log("index: "+prop_index+" Created:"+jdate);
						
						if( parseInt(startObj['is_keys']) == 1 ){
								var image = 'images/google_map/house_pin.png';
						}else if( startObj['status']=='To Be Booked' && parseInt(startObj['urgent_job'])==1 ){
							image = 'images/google_map/alert_mappin.png';
						}else if( startObj['status']=='To Be Booked' && jdate<last_60_day ){
							image = 'images/google_map/alert_mappin.png';
						}else if(startObj['status']=='To Be Booked'){
							image = 'images/google_map/orange_sprite.png'; 
						}else if(startObj['status']=='Booked'){
							image = 'images/google_map/red_sprite.png'; 
						}else{
							image = 'images/google_map/green_sprite.png';
						}
						
						var icon = jMarkerCustomIcon(image,sprite_json.pins[prop_index].x,sprite_json.pins[prop_index].y);
						
					}else{
						var icon = "";
					}
					 					
				}
				 
				// add markers
				jAddMarkers(start,startObj['address'],icon);
				++prop_index;				
				 
				// way points
				var wp = [];
				
				// 2nd and second to the last addresses for waypoints
				var second = first_index+1;
				var second_to_the_last = last_index-1;

				 // if only one address left on the next 10 batch, there's no way point. assign it to end point
				if((last_prop_index-prop_index)!=1){
					
					var y = 1;
					while( prop_index!=last_prop_index && y<=8 ){

						 // distance
						var start_dis = new google.maps.LatLng(prop_address[prop_index]['lat'], prop_address[prop_index]['lng']);
						var end_dis = new google.maps.LatLng(prop_address[prop_index+1]['lat'], prop_address[prop_index+1]['lng']);
						//console.log("Distance From: "+prop_address[prop_index]['address']+" To: "+prop_address[prop_index+1]['address']);
						calculateDistances(start_dis,end_dis);
						 
						 //console.log("way points index: "+prop_index+" -  Address: "+prop_address[prop_index].address+" - last property index: "+last_prop_index);
						var wpObj = prop_address[prop_index];
						var wp_loc = new google.maps.LatLng(wpObj['lat'], wpObj['lng']);
						wp.push({
							'location': wp_loc,
							'stopover':true
						}); 	

						// if sprite number is available, currently max is 50
						if(sprite_json.pins[prop_index]!=""){
							
							var jdate = new Date(wpObj['created']);
							var last_60_day = new Date('<?php echo date("Y-m-d",strtotime("-60 days")); ?>');
							
							//console.log("index: "+prop_index+" Created:"+jdate);
							
							if( parseInt(wpObj['is_keys']) == 1 ){
								var image = 'images/google_map/house_pin.png';
							}else if( wpObj['status']=='To Be Booked' && parseInt(wpObj['urgent_job'])==1 ){
								image = 'images/google_map/alert_mappin.png';
							}else if( wpObj['status']=='To Be Booked' && jdate<last_60_day ){
								image = 'images/google_map/alert_mappin.png';
							}else if(wpObj['status']=='To Be Booked'){
								image = 'images/google_map/orange_sprite.png'; 
							}else if(wpObj['status']=='Booked'){
								image = 'images/google_map/red_sprite.png'; 
							}else{
								image = 'images/google_map/green_sprite.png';
							}
							
							var icon = jMarkerCustomIcon(image,sprite_json.pins[prop_index].x,sprite_json.pins[prop_index].y);
							
						}else{
							var icon = "";
						}						
						
						// add markers
						jAddMarkers(wp_loc,wpObj['address'],icon);
						
						++prop_index;
						++y;
						
					}	
					 
				}
				
				var endObj = prop_address[prop_index];
				console.log("end index: "+prop_index+" -  Address: "+prop_address[prop_index].address+" - last property index: "+last_prop_index+" x: "+sprite_json.pins[prop_index].x+" y: "+sprite_json.pins[prop_index].y);
				var end = new google.maps.LatLng(endObj['lat'], endObj['lng']);
				
				 // end point
				 if(prop_index==last_prop_index){
					
					// if sprite number is available, currently max is 50
					if(sprite_json.pins[prop_index]!=""){
						
						var image = 'images/google_map/house_pin.png';
						var icon = jMarkerCustomIcon(image,sprite_json.pins[prop_index].x,sprite_json.pins[prop_index].y);
						
					}else{
						var icon = "";
					}
					
					// add markers
					jAddMarkers(end,endObj['address'],icon);
				 }
				 
				// direction options
				var request = {
				  origin: start,
				  destination: end,
				  waypoints: wp,
				  travelMode: google.maps.TravelMode.DRIVING,
				  unitSystem: google.maps.UnitSystem.METRIC
				};

				// invoke direction
				directionsService.route(request, function(response, status) {
					if (status == google.maps.DirectionsStatus.OK) {
					  directionsDisplay.setDirections(response);
					}
				});								
			
				i++;
				first_index=(first_index+9);
				last_index=(last_index+9);				
				
			}			

	}, 1000);
	
}

// on load  
google.maps.event.addDomListener(window, 'load', initialize);

</script>

</body>
</html>
