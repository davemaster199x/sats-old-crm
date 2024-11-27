<div id="load-screen"></div>
<?php

$title = "Postcode Map";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;
$country_id = $_SESSION['country_default'];

?>


<div id="mainContent">    

	<div class="sats-middle-cont">
  
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="<?php echo $title; ?>" href="/postcode_map.php"><strong><?php echo $title; ?></strong></a></li>
		  </ul>
		</div>	
		<div id="time"><?php echo date("l jS F Y"); ?></div>
	
		
		<h1>Postcode Map</h1>

		<div style="margin-bottom: 17px; text-align: left;">
		<table id="sub_region_buttons">
			<?php
			$pr_sql = mysql_query("
				SELECT * 
				FROM  `postcode_regions` 
				WHERE  deleted = 0
				AND country_id = {$_SESSION['country_default']}
			");
			$i=1;
			$rtot = mysql_num_rows($pr_sql);
			while( $pr = mysql_fetch_array($pr_sql) ){ 
			
				// postcode region id
				$pr_id = $pr['postcode_region_id'];
			
				if($i==1){
					echo "<tr>";
				}
				?>
				<td class="td_region">
					<div class="region_div">
						<button type="button" style="margin:1px; width:130px;" class="sub_region_btn"><?php echo $pr['postcode_region_name'] ?></button>												
						<input type="hidden" class="pr_id" value="<?php echo $pr_id; ?>" />
						<input type="hidden" class="pr_name" value="<?php echo $pr['postcode_region_name'] ?>" />
					</div>
				</td>
				<?php
				if($i%8==0){ 
					echo "</tr><tr>";
				}
				$i++;
			}
			?>	
		<table>
		</div>		
		
		<div id="map" style="margin: 20px 0"></div>

		<div style="margin-bottom: 45px;">
			<button type="button" id="clear_map">CLEAR MAP</button>
		</div>
		
		
		<div id="current_region"><div>
	
	</div>
	
</div>

<br class="clearfloat" />
<style type="text/css">
#map { 
	height: 500px; 
}
table#sub_region_buttons tr td{
	border: 1px solid #cccccc;
    vertical-align: top;
}
#load-screen {
	width: 100%;
	height: 100%;
	background: url("/images/loading.gif") no-repeat center center #fff;
	position: fixed;
	opacity: 0.7;
	display:none;
	z-index: 9999999999;
}
.img_check{
	margin-left: 5px;
    width: 13px;
	display: none;
}
.region_cp_link {
	margin: 0 5px 0 20px;
}
.active_pins_div{
	display:none; 
	margin-top: 10px;
}
.active_pins{
	margin: 4px 51px; 
	padding: 0;
}
.link_save_pins{
	margin: 0 5px 0 50px;
}
#current_region .region_div{
	margin-bottom: 42px;
	border: 1px solid;
    padding: 10px;
}
</style>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $crm->getSatsAPIkey(); ?>"></script>
<script src="js/googleMapCustomNumberedMarkers.js"></script> 
<script>

var map;
var jpolygon_arr = []; 
var region_marker = {};
var marker_arr = [];
var dialogbox_arr = []; 
var overall_pins = []; 
var custom_markers = [];
var custom_markers_arr = [];
var all_markers = [];


function getCurrentlyActiveRegion(){
	
	var active_pr_arr = [];
	jQuery("#current_region .pr_id").each(function(){
		
		var pr_id = jQuery(this).val();
		active_pr_arr.push(pr_id);
		
	});
	
	return active_pr_arr;
	
}


// pop up window
function displayInfoWindow(event,txt) {

	infoWindow.setContent(txt);
	infoWindow.setPosition(event.latLng);
	infoWindow.open(map);

}

// initiate load basic map
function initMap() {
	
	var mapOptions = {
		zoom: 8,  // zoom - 0 for maxed out out of earth 
		center: {lat: -34.630, lng: 150.320} // where to focus view
	}

	// load map
	map = new google.maps.Map(document.getElementById('map'), mapOptions);

} 


function getRegionControlPanel(obj){
	
	//var parent_div = obj.parents("#sub_region_buttons").length;
	//if( parent_div > 0 ){
		
		var clone = obj.parents("div.region_div").clone();
		var append_elem = '';
		
		// region control panel
		append_elem = '<div style="margin-top: 15px;">'
			+'<input type="hidden" class="region_map_displayed" value="1" />'
			+'<div>'
				+'<a href="javascript:void(0);" class="region_cp_link link_add_pins">Add pins</a>'
				+'<a href="javascipt: void(0);" class="region_cp_link edit_region">Edit</a>'
				+'<a href="javascript:void(0);" class="region_cp_link region_map_clear">Clear Map</a>'
				+'<a href="javascript:void(0);" class="region_cp_link link_clear_custom_pins">Clear Custom Pins</a><img class="img_check img_clear_ccp_check" src="images/check_icon.png" />'
				+'<a href="javascript:void(0);" class="region_cp_link remove_region">Remove Region Panel</a>'				
			+'</div>'		
			+'<div class="active_pins_div">'
				+'<div style="margin-left: 19px;">Custom Pins:</div>'
				+'<div style="margin: 15px 0;">'
					+'<ul class="active_pins"></ul>'
				+'</div>'				
				+'<a href="javascript:void(0);" class="link_save_pins">Save</a><img class="img_check img_save_check" src="images/check_icon.png" />'
			+'</div>'
		+'</div>';
		
		// append
		clone.append(append_elem);
		jQuery("#current_region").append(clone);
		
	//}
	
} 


function displayRegion(obj){
	
	var pr_id = obj.parents("div.region_div").find(".pr_id").val();
	
	marker_arr = [];

	
	var region_name = obj.parents("div.region_div").find(".pr_name").val();
	
	// ajax call
	jQuery("#load-screen").show();
	jQuery.ajax({
		type: "POST",
		url: "ajax_get_region_postcode_coordinates.php",
		dataType: "json",
		data: { 
			pr_id: pr_id,
		}
	}).done(function( ret ){
		
		jQuery("#load-screen").hide();
		//console.log(ret);						
		
		// MARKERS
		var display_pins = ret['display_pins'];
		var json_coor = ret['coordinates'];
		var to_be_booked_job = ret['to_be_booked_job'];
		var custom_pins = ret['custom_pins'];
		
		json_coor_num = json_coor.length;
		
		// display markers/pins if display pins option enabled
		if( display_pins==1 ){
			
			
			for( var i=0; i<json_coor.length; i++ ){				
				
				var lat = parseFloat(json_coor[i]['lat']);
				var lng = parseFloat(json_coor[i]['lng']);
				var myLatlng = new google.maps.LatLng(parseFloat(lat),parseFloat(lng));
				
				var sprite_json = getCustomMarkerSpritePosition();
				var image = '/images/google_map/red_sprite.png';
				var icon = jMarkerCustomIcon(image,sprite_json.pins[i].x,sprite_json.pins[i].y);
				var title = 'postcode '+(i+1)+': '+lat+', '+lng;
				
				// display marker/pins
				var marker = jAddMarkers(map,myLatlng,title,icon);
				marker_arr.push(marker);
				all_markers.push(marker);

				// display dialog box
				var infoWindow = jAddPopUpWindow(map,myLatlng,marker,title);
				dialogbox_arr.push(infoWindow);
				
				
				overall_pins[i] = myLatlng;
				 
				
			}

			
			// CUSTOM PINS
			//console.log(custom_pins);
			var custom_marker = [];
			var custom_marker_obj;
			var custom_pins2 = eval('['+custom_pins+'];');
			//console.log(custom_pins2);
			for( var y=0; y<custom_pins2.length; y++ ){
				
				//console.log("Lat: "+custom_pins2[y].lat+" Lng: "+custom_pins2[y].lng);
				var prcp_id = parseFloat(custom_pins2[y].prcp_id);
				
				var lat = parseFloat(custom_pins2[y].lat);
				var lng = parseFloat(custom_pins2[y].lng);
				var myLatlng = new google.maps.LatLng(parseFloat(lat),parseFloat(lng));
				
				var sprite_json = getCustomMarkerSpritePosition();
				var image = '/images/google_map/green_sprite.png';
				var icon = jMarkerCustomIcon(image,sprite_json.pins[i].x,sprite_json.pins[i].y);
				var title = 'postcode '+(i+1)+': '+lat+', '+lng;
				
				// display marker/pins
				var marker = jAddMarkers(map,myLatlng,title,icon,true);
				marker_arr.push(marker);
				all_markers.push(marker);
				
				// assign custom marker id
				marker.prcp_id = prcp_id;


				// display dialog box
				var infoWindow = jAddPopUpWindow(map,myLatlng,marker,title);
				dialogbox_arr.push(infoWindow);
				
				overall_pins[i] = myLatlng;
				
				// continue pin number on custom pins
				i++;
				

				// drag script
				google.maps.event.addListener(marker, 'dragend', function (event) {
					
					//console.log('Lat: '+this.getPosition().lat()+' Lng: '+this.getPosition().lng());
					var lat = parseFloat(this.getPosition().lat());
					var lng = parseFloat(this.getPosition().lng());
					var coordinates = '{"lat": "'+lat+'", "lng": "'+lng+'"}'
					//console.log('prcp_id: '+this.prcp_id);
					//console.log('coordinates: '+coordinates);
					
					// ajax call
					jQuery("#load-screen").show();
					jQuery.ajax({
						type: "POST",
						url: "ajax_update_custom_pins.php",
						data: { 
							prcp_id: this.prcp_id,
							coordinates: coordinates,
						}
					}).done(function( ret ){
						
						jQuery("#load-screen").hide();
						//console.log(ret);
						
					});

					
					
				});
				
				
				
			}
			
			
			// focus
			map.setCenter(myLatlng);				
			
		}
		
		
		//console.log(overall_pins);
		
		
		// store marker in a region array
		region_marker[pr_id] = {
			marker : marker_arr,
			last_pin_index: i,
			last_pin_coordinates: myLatlng,
			custom_marker_pos: [],
			polygon: []
		}
		
		
		// POLYGON			
		var json_polygon = ret['gm_polygon_points'];
		// draw polygon, only if polygon points already set on db
		if( json_polygon!=null && json_polygon!='' ){
			
			var pc_coor_temp = '';
			var json_polygon2 = json_polygon.split(",");
			for( var x=0; x<json_polygon2.length; x++ ){
				var polygon_index = (json_polygon2[x])-1;
				var lat = parseFloat(overall_pins[polygon_index].lat());
				var lng = parseFloat(overall_pins[polygon_index].lng());
				pc_coor_temp += ',{lat: '+parseFloat(lat)+', lng: '+parseFloat(lng)+'}';
			}
	
			var pc_coor_temp2 = pc_coor_temp.substring(1);
			var pc_coor_txt = '['+pc_coor_temp2+'];';
			
			// polygon coordinates
			var pc_coor = eval(pc_coor_txt);
			
			// draw the polygon
			var jpolygon = jDrawPolygon(map,pc_coor);
			
			// store polygons for delete
			jpolygon_arr.push(jpolygon);	
			region_marker[pr_id].polygon.push(jpolygon);
			
			// DIALOG BOX
			// get center
			var polygon_center = jgetPolygonCenter(pc_coor);
			map.setCenter(polygon_center);
			
			// display dialog box on load
			var dialog_txt = '<h3>'+region_name+'</h3>'+
			'To Be Booked Jobs: '+to_be_booked_job;
			var infoWindow = jAddPopUpWindow(map,polygon_center,jpolygon,dialog_txt,false);
			dialogbox_arr.push(infoWindow);			
			
			// display dialog box
			var infoWindow = jAddPopUpWindow(map,polygon_center,jpolygon,dialog_txt);
			dialogbox_arr.push(infoWindow);
											

		}	

		
	});
	
}

jQuery(document).ready(function(){
	

	// initate map
	initMap();
	
	// region script
	jQuery(document).on('click','.td_region .sub_region_btn',function(){
		
		var obj = jQuery(this);
		var pr_id = obj.parents("div.region_div").find(".pr_id").val();
		var active_regions = getCurrentlyActiveRegion();
		
		console.log(jQuery.inArray( pr_id, active_regions ));
		if( jQuery.inArray( pr_id, active_regions )==-1 ){
		
				getRegionControlPanel(obj);	
				displayRegion(obj);
		
		
		}

		

	});
	

	jQuery(document).on('click','#current_region .sub_region_btn',function(){
		
		var obj = jQuery(this);
		var pr_id = obj.parents("div.region_div").find(".pr_id").val();
		var active_regions = getCurrentlyActiveRegion();
		var region_map_displayed = obj.parents("div.region_div").find(".region_map_displayed").val();
		
		if( region_map_displayed==0 ){	
			displayRegion(obj);
			obj.parents("div.region_div").find(".region_map_displayed").val(1);
		}		
		

	});
	
	function clearMapData(){
		
		jQuery(".region_map_displayed").val(0);
		
		// clear polygon and dialog box
		for(var i=0;i<jpolygon_arr.length;i++){
			jpolygon_arr[i].setMap(null);	
			dialogbox_arr[i].setMap(null);
		}
		
		/*
		// clear markers/pins
		for(var i=0;i<marker_arr.length;i++){
			marker_arr[i].setMap(null);
		}
		
		// clear markers/pins
		for(var i=0;i<custom_markers_arr.length;i++){
			custom_markers_arr[i].setMap(null);
		}
		*/
		
		// clear all markers
		for(var i=0;i<all_markers.length;i++){
			all_markers[i].setMap(null);
		}

		
		// clear active pins link
		jQuery(".active_pins").html("");
		jQuery(".active_pins_div").hide();
		jQuery('.img_check').hide();
		
		//jQuery(".td_region").removeClass();
		
		// clear all data
		jpolygon_arr = []; 
		region_marker = {};
		marker_arr = [];
		dialogbox_arr = []; 
		overall_pins = []; 
		custom_markers = [];
		custom_markers_arr = [];
		
	}
	
	
	// clear map
	jQuery("#clear_map").click(function(){
		
		clearMapData();
		
	});
	
	
	// remove region
	jQuery(document).on('click','.remove_region',function(){
		
		clearMapData();
		jQuery(this).parents("div.region_div").remove();
		
	});
	
	
	
	// edit region
	jQuery(document).on("click",".edit_region",function(e){
		
		e.preventDefault();
		var pr_id = jQuery(this).parents("div.region_div").find(".pr_id").val();				
			
		window.open(
			"/edit_region.php?id="+pr_id+"&popup_window=1", 
			"_blank", 
			"toolbar=no,scrollbars=yes,resizable=no,top=20,left=20,width=600,height=490"
		);
		
	});
	
	
	// add new pins	
	jQuery(document).on("click",".link_add_pins",function(e){
		
		var custom_markers_obj = new Object();
		e.preventDefault(); // disable link
		// postcode region id
		var pr_id = jQuery(this).parents("div.region_div").find(".pr_id").val();
		
		// get region id
		var region = region_marker[pr_id];
		// get last pin index and coordinates
		//var i = region.last_pin_index;
		//var i = marker_arr.length;
		var i = region.marker.length;
		var last_pin_coor = region.last_pin_coordinates;
		console.log(region);
		console.log(region.marker.length);
		console.log(pr_id);
		
		var lat = parseFloat(last_pin_coor.lat());
		var lng = parseFloat(last_pin_coor.lng());
		var myLatlng = new google.maps.LatLng(parseFloat(lat),parseFloat((lng+0.03)));
		
		var sprite_json = getCustomMarkerSpritePosition();
		var image = '/images/google_map/green_sprite.png';
		var icon = jMarkerCustomIcon(image,sprite_json.pins[i].x,sprite_json.pins[i].y);
		var pin_num = i+1;
		var title = 'postcode '+(pin_num)+': '+lat+', '+lng;
		
		// display marker/pins
		var marker = jAddMarkers(map,myLatlng,title,icon,true);
		// store to custom markers array
		custom_markers_obj.marker = marker;
		custom_markers_arr.push(marker);
		region.custom_marker_pos[i] = myLatlng;
		// store to marker array  - test
		//marker_arr.push(marker);
		all_markers.push(marker);
		region.marker.push(marker);
		jQuery(this).parents("div.region_div").find(".active_pins_div").show();
				
		// set last pin to new pin
		region.last_pin_index = pin_num;
		// display pins for delete
		jQuery(this).parents("div.region_div").find(".active_pins").append("<li><a href='javascript:void(0);' class='link_remove_pin'>pin # "+pin_num+"</a><input type='hidden' class='custom_marker_id' value='"+pin_num+"' /></li>");
		
		// store custom marker coordinate
		custom_markers_obj.coordinates = myLatlng;
		custom_markers[pin_num] = custom_markers_obj;
		
		
		// drag script
		google.maps.event.addListener(marker, 'dragend', function (event) {
			
			//console.log('Lat: '+this.getPosition().lat()+' Lng: '+this.getPosition().lng());
			var lat = parseFloat(this.getPosition().lat());
			var lng = parseFloat(this.getPosition().lng());
			var myLatlng = new google.maps.LatLng(parseFloat(lat),parseFloat(lng));
			//console.log(myLatlng);
			
			// set  last pin position to new pins dragged position
			region.last_pin_coordinates = myLatlng;
			
			// store custom marker coordinate
			custom_markers_obj.coordinates = myLatlng;
			custom_markers[pin_num] = custom_markers_obj;	
			region.custom_marker_pos[i] = myLatlng;
			
			
		});
		
	});
	
	// save added custom pins
	jQuery(document).on("click",".link_save_pins",function(){
		
		var obj = jQuery(this);
		var pr_id = obj.parents("div.region_div").find(".pr_id").val();
		var region = region_marker[pr_id];
		var cust_marker = region.custom_marker_pos;
		//console.log(region);
		console.log(cust_marker);
		
		
		if( cust_marker.length>0 ){
			
			// get custom markers count
			var count = 0;
			var pin_coor_arr = [];			
			
			for( var i=0;i<cust_marker.length; i++ ){
				if(  typeof cust_marker[i] !== "undefined" ){
					var lat = parseFloat(cust_marker[i].lat());
					var lng = parseFloat(cust_marker[i].lng());
					pin_coor_arr.push('{"lat": "'+lat+'", "lng": "'+lng+'"}');
				}
				
			}
			
			
			console.log(pin_coor_arr);
			
			
			// ajax call
			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "ajax_save_custom_pins.php",
				data: { 
					pr_id: pr_id,
					pin_coor_arr: pin_coor_arr,
				}
			}).done(function( ret ){
				
				jQuery("#load-screen").hide();
				obj.parents("div.region_div").find('.img_save_check').show();
				//console.log(ret);
				clearIndividuaRegion(obj);
				displayRegion(obj);
				
			});
			
			
			
		}else{
			alert('No pins added');
		}
		
		
		
		
		
		
	});
	
	/*
	// remove custom pins
	jQuery(document).on("click",".link_remove_pin",function(){
		
		var custom_marker_id = jQuery(this).parents("li:first").find(".custom_marker_id").val();
		// clear marker on map
		custom_markers[custom_marker_id].marker.setMap(null);
		// clear marker array
		custom_markers[custom_marker_id] = undefined;
		jQuery(this).parents("li:first").remove();
		
	});
	*/
	
	// clear all saved custom pins
	jQuery(document).on("click",".link_clear_custom_pins",function(){
		
		var obj = jQuery(this);
		var pr_id = obj.parents("div.region_div").find(".pr_id").val();
		
		// ajax call
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_clear_custom_pins.php",
			data: { 
				pr_id: pr_id
			}
		}).done(function( ret ){
			
			jQuery("#load-screen").hide();
			obj.parents("div.region_div").find('.img_clear_ccp_check').show();
			//console.log(ret);			
			
		});
		
	});
	
	function clearIndividuaRegion(obj){
		
		var pr_id = obj.parents("div.region_div").find(".pr_id").val();
		var region = region_marker[pr_id];
		var reg_marker = region.marker;
		var polygon = region.polygon;
		console.log(reg_marker);
		
		// clear markers
		for(var i=0;i<reg_marker.length;i++){
			reg_marker[i].setMap(null);
		}
		
		// clear polygons
		for(var i=0;i<polygon.length;i++){
			polygon[i].setMap(null);	
		}		
		
		obj.parents("div.region_div").find(".region_map_displayed").val(0);
		
	}
	
	jQuery(document).on("click",".region_map_clear",function(){
		
		var obj = jQuery(this);
		clearIndividuaRegion(obj);
		
	});
	

});

</script>
</body>
</html>
