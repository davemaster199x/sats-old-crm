function getCustomMarkerSpritePosition(){
	
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
		
		return sprite_json = JSON.parse(sprite_num_str);
		
}


function jMarkerCustomIcon(image,x,y){
	
	//console.log("X: "+x+" Y: "+y);
	
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


function jAddMarkers(map,position,title,icon,isDraggable=false){
	
	//icon = (icon!="")?icon:'https://<?php echo $GLOBALS['domain']; ?>/images/car.png';
	
	var marker = new google.maps.Marker({
	  map: map,
      position: position,	 
	  title:title,
	  icon: icon,
	  draggable:isDraggable
  });
  
  return marker;
  
}


function jAddPopUpWindow(map,position,target,contentString,is_onClick=true){
	
  var infowindow = new google.maps.InfoWindow({
      content: contentString
  });
  
  if( is_onClick==true ){
	  google.maps.event.addListener(target, 'click', function() {
		infowindow.setPosition(position);
		infowindow.open(map,target);
	  });
  }else{
	  infowindow.setPosition(position);
	  infowindow.open(map,target);
  }
  
  return infowindow;
  
}

function jDrawPolygon(map,pc_coor){
	
	// color
	var polygon_color = '#FFA500';
	
	var jpolygon = new google.maps.Polygon({
		paths: pc_coor,
		strokeColor: polygon_color,
		strokeOpacity: 0.8,
		strokeWeight: 2,
		fillColor: polygon_color,
		fillOpacity: 0.35
	});
	
	// Construct the polygon.	  
	jpolygon.setMap(map);
	
	return jpolygon;
	
}

// get polygon center
function jgetPolygonCenter(pc_coor){
	
	var bounds = new google.maps.LatLngBounds();
	for ( var y = 0; y < pc_coor.length; y++) {
	  bounds.extend(pc_coor[y]);
	}
	
	return bounds.getCenter();
	
}