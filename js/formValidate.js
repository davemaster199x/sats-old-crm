// JavaScript for validating form input

//Use for number only entries
function numbersonly(e){
var unicode=e.charCode? e.charCode : e.keyCode
if (unicode!=8 && unicode!=9 && unicode!=32){ //if the key isn't the backspace, tab or space key (which we should allow)
	if (unicode<48||unicode>57) //if not a number
		return false //disable key press
	}
}
//Check PostCode for 4-digits format
function checkPostcode(str, event){
	if(str.length == 0){
		alert("Postcode is blank");
	}
	else if (str.length != 4){ //if length is greater/less than 4 digits
		alert("Enter a 4 digit postcode only");
	}
}

// Sort Array (02-08-2005)
// by Vic Phillips http://www.vicsjavascripts.org.uk
// The following scripts is used to sort dropdown list
function zxcSelectSort(zxcid,zxcfirstoption){
zxcobj=document.getElementById(zxcid);
zxcAry=new Array();
for (zxc0=zxcfirstoption;zxc0<zxcobj.options.length;zxc0++){
zxcAry[zxc0-zxcfirstoption]=zxcobj.options[zxc0];
}
zxcAry=zxcAry.sort(zxcOptionSort);
for (zxc1=0;zxc1<zxcAry.length;zxc1++){
zxcobj.options[zxc1+zxcfirstoption]=new Option(zxcAry[zxc1].text,zxcAry[zxc1].value,true,true);
}
zxcobj.selectedIndex=0;
}

function zxcOptionSort(zxca,zxcb){
zxcA=zxca.text.toLowerCase();
zxcB=zxcb.text.toLowerCase();
if (zxcA<zxcB){ return -1; }
if (zxcA>zxcB){ return 1; }
return 0;
}
//Use for disable form submit if accidently hit the enter key
function keypress(e){
var unicode=e.charCode? e.charCode : e.keyCode
 if (unicode==13){ 	//if the key is Enter, then disable submit
	return false 	//disable key press
 }
}