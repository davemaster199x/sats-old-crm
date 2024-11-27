function highlight1(id, odd){
	var i = 'td'+id;
	var color = document.getElementById(i).style.backgroundColor;	//bgColor
	var str;
	var oddrowColor;
	
	var browser = navigator.appName;
	//alert(browser);

	if(browser == "Netscape"){
		oddrowColor = "rgb(239, 235, 239)";
	}else if(browser == "Microsoft Internet Explorer"){
		oddrowColor = "#efebef";
	}else return;
	
	if(color == 'white'){
		oldcolor[0] = color;
	}
	else if(color == oddrowColor){	//#efebef 'rgb(239, 235, 239)' lightgrey rgb(211, 211, 211)
		oldcolor[1] = color;
	}

	str = 'id: '+id+' odd: '+odd+'\nold color:'+color+'\nnew color: yellow';

	if(color == 'yellow' && odd == 0){
		document.getElementById(i).style.backgroundColor = oldcolor[0];
		str = 'id: '+id+' odd: '+odd+'\nold color:'+color+'\nnew color: '+oldcolor[0];
	}
	else if(color == 'yellow' && odd == 1){
		document.getElementById(i).style.backgroundColor = oldcolor[1];
		str = 'id: '+id+' odd: '+odd+'\nold color:'+color+'\nnew color: '+oldcolor[1];
	}
	else{
		document.getElementById(i).style.backgroundColor = 'yellow';
	}
	//alert(str);
}

function highlight2(id, odd){
	var i = 'td'+id;
	var color = document.getElementById(i).bgColor;
	//var str = 'id: '+id+' odd: '+odd+'\nold color:'+color+'\nnew color: yellow';
	var oddrowColor;
	var evenrowColor;
	var highlightColor = '#ffff00';
	var browser = navigator.appName;
	//alert(color);
	
	if(browser == "Netscape"){
		oddrowColor = "#efebef";
		evenrowColor = "white";
	}
	else if(browser == "Microsoft Internet Explorer"){
		oddrowColor = "#efebef";
		evenrowColor = "#ffffff";
	}
	else {
		 return; 
	}
	
	if(color == evenrowColor){
		oldcolor[0] = color;
	}
	else if(color == oddrowColor){
		oldcolor[1] = color;
	}

	if(color == highlightColor){
		document.getElementById(i).bgColor = (odd == 0) ? oldcolor[0] : oldcolor[1];
		//str = 'id: '+id+' odd: '+odd+'\nold color:'+color+'\nnew color: '+document.getElementById(i).bgColor;
	}
	else{
		document.getElementById(i).bgColor = highlightColor;
	}
	//alert(str);
}

function highlight(id, odd){
	var i = 'td'+id;
	var color = document.getElementById(i).bgColor;
	var oddrowColor = "#efebef";
	var evenrowColor = "#ffffff";
	var highlightColor = "#ffff00";

	//remember old colour
	if(color == evenrowColor){
		oldcolor[0] = color;
	}
	else if(color == oddrowColor){
		oldcolor[1] = color;
	}
	
	//check if it is already highlighted, if so restore to it's original colour
	if(color == highlightColor){
		document.getElementById(i).bgColor = (odd == 0) ? oldcolor[0] : oldcolor[1];
	}
	else{
		document.getElementById(i).bgColor = highlightColor;
	}
}