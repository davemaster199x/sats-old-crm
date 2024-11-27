<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title><?=$title;?></title>
<link rel="icon" type="image/png" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/favicon.png" />
<link href="css/mainsite.css" type="text/css" rel="stylesheet">

<script LANGUAGE="JavaScript" SRC="<?=URL;?>js/AnchorPosition.js"></script>
<script LANGUAGE="JavaScript" SRC="<?=URL;?>js/CalendarPopup.js"></script>
<script LANGUAGE="JavaScript" SRC="<?=URL;?>js/date.js"></script>
<script LANGUAGE="JavaScript" SRC="<?=URL;?>js/formValidate.js"></script>
<script LANGUAGE="JavaScript" SRC="<?=URL;?>js/PopupWindow.js"></script>
<script LANGUAGE="JavaScript" SRC="<?=URL;?>js/util.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.coolfieldset.js"></script>
<script type="text/javascript" src="<?=URL;?>fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link href="<?=URL;?>fancybox/jquery.fancybox-1.3.4.css" type="text/css" rel="stylesheet">
<link href="<?=URL;?>inc/css/blitzer/jquery-ui-1.8.23.custom.css" type="text/css" rel="stylesheet">
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?=URL;?>js/jquery.tablednd_0_5.js"></script>
<script type="text/javascript" src="<?=URL;?>js/charcount.js"></script>
<script LANGUAGE="JavaScript" SRC="<?=URL;?>js/tinymce/tinymce.min.js"></script>
<script src="<?=URL;?>js/jquery.cookie.js"></script>
<script src="/js/maskedinput.js" type="text/javascript"></script>
<script src="/js/sats_crm_lib.js" type="text/javascript"></script>
<script src="/ion_sound/ion.sound.min.js"></script>
<script src="jsignature/jSignature.min.js"></script>
<link rel="stylesheet" href="css/responsive_tab.css">
<link rel="stylesheet" href="css/jc.css">
<script src="https://js.pusher.com/4.4/pusher.min.js"></script>
<script LANGUAGE="JavaScript" SRC="<?=URL;?>js/jquery.idle.js"></script>
<style>
.jinvalid_format{
	font-size: 11px; 
	color: red; 
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
.inner_icon{
	position: relative;
	top: 2px;
	margin-right: 3px;
}
.j_icons {
    margin-right: 5px;
}
</style>
<script LANGUAGE="JavaScript">
	var cal = new CalendarPopup();
	cal.setWeekStartDay(1);
</script>

<script LANGUAGE="JavaScript"><!--
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
	
--></script> 

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
	
	
	
	// region filter check all script
	jQuery(document).on('change','.check_all_sub_region',function(){

		var obj = jQuery(this);
		var state = obj.prop("checked");
		
		if( state == true ){
			obj.parents("li.main_region_li").find('.reg_db_main_reg').click();
			//obj.parents("li.main_region_li").find(".postcode_region_id").prop("checked",true);
		}else{
			obj.parents("li.main_region_li").find(".postcode_region_id").prop("checked",false);
		}

	});
	
	
	<?php
	if($custom_datepicker!=1){ ?>
		// datepicker
		jQuery(".datepicker").datepicker({ dateFormat: "dd/mm/yy" });
	<?php	
	}
	?>
	
	
	
	
	

	
		// mobile
		var mobile_mask = '<?php echo ($_SESSION["country_default"]==1)?'?9999 999 999':'?999 9999 9999'; ?>';
		jQuery(".tenant_mobile_field").mask(mobile_mask);

		jQuery(".tenant_mobile_field").blur(function(){
			
			var mobile = jQuery(this).val();
			
			var mobile_err_msg_format = 'Format to be <?php echo ($_SESSION["country_default"]==1)?'0412 222 222':'041 2222 2222'; ?>';
			//var mobile_err_msg_format = 'Format to be 0412 222 222';
			
			var mobile_length = <?php echo ($_SESSION["country_default"]==1)?12:13; ?>;
			
			if(mobile.length!=mobile_length){
				//alert("Phone Number format should be xx xxxx xxxx");
				//jQuery(this).addClass('error_border');
				//jQuery(this).removeClass('green_border');
				if(mobile.length!=0){
					//jQuery(this).parents(".jtenant_div:first").find(".tenant_mobile_error").css("visibility","visible");
					jQuery(this).addClass('jred_border_higlight');
					jQuery(this).attr('title',mobile_err_msg_format);
				}				
				jQuery(this).click(function(e){e.preventDefault();});
			}else{
				jQuery(this).removeClass('jred_border_higlight');
				jQuery(this).removeAttr('title');
				//jQuery(this).addClass('green_border');
				//jQuery(this).parents(".jtenant_div:first").find(".tenant_mobile_error").css("visibility","hidden");
			}
			
		});
	
	
	
	
	
	// landline
	var phone_mask = '<?php echo ($_SESSION["country_default"]==1)?'?99 9999 9999':'?99 9999 999'; ?>';
	jQuery(".tenant_phone_field").mask(phone_mask);

	jQuery(".tenant_phone_field").blur(function(){
		
		var phone_err_msg_format = 'Format to be <?php echo ($_SESSION["country_default"]==1)?'02 2222 2222':'02 2222 222'; ?>';

		//jQuery(this).parents(".jtenant_div:first").find(".tenant_phone_error").html(phone_err_msg_format );
		
		var phone = jQuery(this).val();
		var phone_length = <?php echo ($_SESSION["country_default"]==1)?12:11; ?>;
		
		if(phone.length!=phone_length){

			//jQuery(this).removeClass('green_border');
			if(phone.length!=0){
				//jQuery(this).parents(".jtenant_div:first").find(".tenant_phone_error").css("visibility","visible");
				jQuery(this).addClass('jred_border_higlight');
				jQuery(this).attr('title',phone_err_msg_format);
			}			
			jQuery(this).click( function(e){ e.preventDefault(); } );
			
		}else{
			jQuery(this).removeClass('jred_border_higlight');
			jQuery(this).removeAttr('title');
			//jQuery(this).addClass('green_border');
			//jQuery(this).parents(".jtenant_div:first").find(".tenant_phone_error").css("visibility","hidden");
		}
		
	});
	
	
	

	
	// run 
	jQuery(".tenant_phone_field, .tenant_mobile_field").blur();

	

    
    
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
<script>
  $(function() {
    $('.fieldset2').coolfieldset({collapsed:true});
  });
</script>  


  
</head>

<body class="thrColLiqHdr" <? if($onload): ?>onload="<?=$onload_txt;?>" <? endif;?>>


<div id="container">
 
  
  
  
  <? if($_GET['restricted']): ?>
  <div id="permission_error">You do not have permission to access that resource</div>
  <? endif; ?>
  

  
  </div>
  <div id="load-screen"></div>
