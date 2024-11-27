<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$title;?></title>

<link href="css/mainsite.css" type="text/css" rel="stylesheet" />
<SCRIPT type="text/javascript">
 function showConfirm(){
	var answer = confirm("Create pending jobs for next month?");

	if(answer){
		document.getElementById('busy').innerHTML = "Processing data, please wait ...";
		location.href = "find_pending.php";
	}
 }
</SCRIPT>
<script type="text/javascript">

function numbersonly(e){

var unicode=e.charCode? e.charCode : e.keyCode

if (unicode!=8 && unicode!=9){ //if the key isn't the backspace key (which we should allow)

	if (unicode<48||unicode>57) //if not a number

		return false //disable key press

	}

}

</script>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=URL;?>js/AnchorPosition.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=URL;?>js/CalendarPopup.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=URL;?>js/date.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=URL;?>js/formValidate.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=URL;?>js/PopupWindow.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=URL;?>js/util.js"></SCRIPT>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="<?=URL;?>fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link href="<?=URL;?>fancybox/jquery.fancybox-1.3.4.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="<?=URL;?>js/jquery.tablednd_0_5.js"></script>
<SCRIPT LANGUAGE="JavaScript">
	var cal = new CalendarPopup();
	cal.setWeekStartDay(1);
</SCRIPT>

<SCRIPT LANGUAGE="JavaScript"><!--
 var agencyname='this agency';
 var oldcolor = new Array();
 var cal = new CalendarPopup();
 cal.setWeekStartDay(1);

function recValue(){
 var aindex = document.getElementById('agency').selectedIndex;
 agencyname = document.getElementById('agency').options[aindex].text;
 
 //set cookie to remember agency name in dropdown box
 createCookie('agencyname',agencyname,1);
}

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}
	
--></SCRIPT> 

<script language="javascript">
function checkRadioService(){
	var radioVal = "";
	for (var i=0; i < document.form1.radioService.length; i++)
    {
   	  	if (document.form1.radioService[i].checked)
      		radioVal = document.form1.radioService[i].value;
    }

	 if (radioVal == ""){
		alert("Select Yes or No to Service");
		return false;
	 }
	 else{	//radio button is selected, ready to submit form	 	//alert(radioVal);
		document.forms["form1"].submit();
		return true;
	 }
}
</script>

<!--  -->
<script type="text/javascript">

$(document).ready(function() {
    
    
    
    <? # TECHS CANNOT REORDER THE DAY'S JOBS 
    if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"): ?>
    // Initialise the table
    $("#tech_schedule").tableDnD({
    	onDragClass: "row_drag",
    	onDrop: function(table, row) {
			
			$.ajax({
				type: "POST",
				data: "UpdateTable=1&Serial=" + escape($.tableDnD.serialize()),
				url: "ajax/ajax.php",
				success: function(output){
						//
				}
			});
		}
    });
    <? endif; ?>
    
    $("#price_row").hide();
    
    $("#pricechange").click(function(){
    	$("#price_row").show('slow');
    });
    
    $("#pricehide").click(function(){
    	$("#price_row").hide('slow');
    });
    

});
	
</script>    
</head>

<body class="thrColLiqHdr" <? if($onload): ?>onload="<?=$onload_txt;?>" <? endif;?>>


<div id="container">
 <div id="header">
    <h1 style="float: left;"><span class="style1">Smoke Alarm Testing <?=(stristr(URL, 'dev') ? '(Dev)': '');?></span>
      <!-- end #header -->
    </h1>
    <div id="apDiv1"><img src="satslogo.gif" alt="sats logo" /></div>
    <? if($_SESSION['USER_DETAILS']['StaffID']): ?>
    <div id="staff_box">
    <p><strong>Welcome:</strong> <?=$_SESSION['USER_DETAILS']['FirstName'];?> <?=$_SESSION['USER_DETAILS']['LastName'];?> [<?=$_SESSION['USER_DETAILS']['ClassName'];?>]<br /><a href="<?=URL;?>main.php?logout=1">logout</a></p>
    </div>
    <? endif; ?>
    
  </div>
  
  <? if($_GET['restricted']): ?>
  <div id="permission_error">You do not have permission to access that resource</div>
  <? endif; ?>
  
  <div class="clearfloat">
  
  </div>
