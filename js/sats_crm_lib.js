function formatToDateToYmd(date){
	var date2 = date.split("/");
	var d = date2[0];
	var m = date2[1];
	var y = date2[2];
	return y+'-'+m+'-'+d;
}

function addDays(date, days) {
    var result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
}

function addMonth(date, month) {
	var result = new Date(date);
	result.setMonth(result.getMonth() + month);
	return result;
}

function formatDate(date,format='d/m/y'){
	var d = date.getDate();
	var m = date.getMonth()+1; //January is 0!
	var y = date.getFullYear();
	
	if(d<10){
		d='0'+d;
	} 
	if(m<10){
		m='0'+m;
	} 
	
	switch(format){
		case 'd/m/y':
			format2 = d+'/'+m+'/'+y;
		break;
		case 'y-m-d':
			format2 = y+'-'+m+'-'+d;
		break;
	}
	
	return date = format2;
}