<?

$title = "Main - Smoke Alarm Testing Services";

include('inc/init.php');
include('inc/header_homepage.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$user_type = $_SESSION['USER_DETAILS']['ClassID'];
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

$arr = getHomeTotals(); 
/*
// check if STR if all unhidden jobs have phone call
function displayGreenPhoneFromSTR($tr_id,$show_hidden){
	
	$job_count = 0;
	$green_phone_count = 0;
	$hideChk = 0;
	
	// Initiate tech run class
	//$tr_class = new Tech_Run_Class();

	//$params = array('country_id'=>$_SESSION['country_default']);
	//$jr_list2 = $tr_class->getTechRunRows($tr_id,$params);
	
	$jr_list2 = getTechRunRows($tr_id,$_SESSION['country_default']);
	while( $row = mysql_fetch_array($jr_list2) ){
	 
		$hiddenText = "";
		$showRow = 1;
		$isUnavailable = 0;
	 
		if( $row['row_id_type'] == 'job_id' ){
			
			$jr_sql = getJobRowData($row['row_id'],$_SESSION['country_default']);
			$row2 = mysql_fetch_array($jr_sql);
			
			$job_count++;
					
			$chk_logs_str = "
				SELECT *
				FROM job_log j 
				LEFT JOIN staff_accounts s ON s.StaffID = j.staff_id
				WHERE j.`job_id` = {$row2['jid']}
				AND j.`deleted` = 0 
				AND j.`eventdate` = '".date('Y-m-d')."'
				AND j.`contact_type` = 'Phone Call'
			";
			$chk_logs_sql = mysql_query($chk_logs_str);
			$chk_log = mysql_fetch_array($chk_logs_sql);
			
			$current_time = date("Y-m-d H:i:s");
			$job_log_time = date("Y-m-d H:i",strtotime("{$chk_log['eventdate']} {$chk_log['eventtime']}:00"));
			$last4hours = date("Y-m-d H:i",strtotime("-3 hours"));
			//echo "Current time: {$current_time }<br />Log Time: {$job_log_time}<br /> last 4 hours: ".$last4hours;
			
			
			if( displayGreenPhone2($row2['jid'],$row2['j_status'])==true ){
				//echo '<img src="/images/green_phone.png" style="cursor: pointer; margin-right: 10px;" title="Phone Call" />';
				$green_phone_count++;
			}		
			
		}
	}
	

	
	if( ($job_count>0) && $green_phone_count == $job_count ){
		return true;
	}else{
		return false;
	}
	
	
}
*/

?>

 
  
  
  
  
  <style>
  .bs_bill, .bs_bkd, .bs_tar{
	width: 80px;
  }
  .bs_tech{
	width: 105px;
  }  
  .jgreenrow {
    background-color: #DFFFA5 !important;
    color: #000000;
	}
	.jyellowrow {
    background-color: #fffca3 !important;
    color: #000000;
	}
	
	
.au_bg_color {
    background-color: #000080 !important;
	color: white;
	background-image: none;
}

.nz_bg_color {
    background-color: #000000 !important;
	color: white;
	background-image: none;
}


.au_border{
	border-left: 1px solid #000080;
	border-right: 1px solid #000080;
}
.au_last_child {
    border-bottom: 1px solid #000080;
}
.nz_border{
	border-left: 1px solid #000000;
	border-right: 1px solid #000000;
}
.nz_last_child {
    border-bottom: 1px solid #000000;
}

.au_jdivbox{
	border: 1px solid #000080;
}
.nz_jdivbox{
	border: 1px solid #000000;
}
.toggler-content{
	margin:0;
}
.jtoggle_div{
	width: 49%;
	margin-bottom: 15px;
}
.junderline_colored{
	color: red;
    text-decoration: underline;	
}

.modal {
  display: none; 
  position: fixed; 
  z-index: 1; 
  padding-top: 100px; 
  left: 0;
  top: 0;
  width: 100%; 
  height: 100%; 
  overflow: auto; 
  background-color: rgb(0,0,0); 
  background-color: rgba(0,0,0,0.4); 
}

.modal-content {
  background-color: #fefefe;
  margin: auto;
  padding: 20px;
  border: 1px solid #888;
  width: 25%;
  color: #343434;
  font-family: 'Proxima Nova',sans-serif;
  line-height: 1.4;
}
.mod-button {
  background-color: Crimson;  
  border-radius: 5px;
  color: white !important;
  padding: 1em;
  text-decoration: none;

}

.booked_box_num {
    font-size: 50px !important;
	margin-top: 10px !important;
}

.booked_box_lbl {
	font-size: 12px !important;
}
  </style>
  
<div id="mainContent" class="homepage">
<?php

// KMS
$sql = mysql_query("
	SELECT *
	FROM `staff_accounts` AS sa
	LEFT JOIN `vehicles` AS v ON v.`StaffID` = sa.`StaffID` 
	WHERE sa.`StaffID` = {$staff_id}
");
$v = mysql_fetch_array($sql);

$kms_sql = mysql_query("
	SELECT *
	FROM `kms` AS k		
	LEFT JOIN `vehicles` AS v ON k.`vehicles_id` = v.`vehicles_id`
	WHERE k.`vehicles_id` = {$v['vehicles_id']}
	ORDER BY k.`kms_updated` DESC
	LIMIT 0, 1
");
$kms = mysql_fetch_array($kms_sql);

include('main_default_view.php');
?>
</div>


<br class="clearfloat" />
 

<!-- The Modal -->
<div id="myModal" class="modal">
  <!-- Modal content -->
  <div class="modal-content">
      	<h3>You've been idle for 60 Minutes</h3>
		<div class="content">
		  <section>
			<p>You will be logout in <span id="counter">15</span> second(s) unless you press 'Extend Session'.</p>
	      	<i class="fa fa-question-circle"></i> Do you want to extend?
	      	<p style="margin-top: 18px;"><a href="javascript:;" id="extendBtn" class="mod-button">Extend Session</a></p>
	      	
		  </section>
    	</div>
  </div>
</div>

<script type="text/javascript">

	var modal = document.getElementById('myModal');
	/*
	$(document).idle({
	  onIdle: function(){
	  	modal.style.display = "block";
	    var cTimer = setInterval(function(){ countdown(); },1000);
		jQuery("#extendBtn").click(function(){
    		$('#counter').html(15)
	    	clearInterval(cTimer)
			modal.style.display = "none";
		});
	  },
	  // idle: 5000
	  idle: 60000*60
	}) */

	function countdown() {
	    var i = document.getElementById('counter');
	    if (parseInt(i.innerHTML)<=1) {
				modal.style.display = "none";
	        location.href = '<?php echo URL; ?>main.php?logout=1';
	    }
	    i.innerHTML = parseInt(i.innerHTML)-1;
	}

	jQuery(document).ready(function(){



			// mark run complete 
			jQuery(".run_status").click(function(){
				
				var run_type = jQuery(this).val();
				var status = (jQuery(this).prop("checked")==true)?1:0;
				var tr_id = jQuery(this).attr("data-tr_id");
				
				jQuery.ajax({
					type: "POST",
					url: "ajax_tech_run_update_run_status.php",
					data: { 
						run_type: run_type,
						status: status,
						tech_run_id: tr_id
					}
				}).done(function( ret ) {
					//window.location="/set_tech_run.php?tr_id=<?php echo $tr_id; ?>";
				});	
				
			});
			
	
			
			// KMS for sales
			<?php 
			if( $user_type == 5 ){ ?>
			
				jQuery("#update_kms").click(function(){
						
					var vehicles_id = jQuery(this).parents("#kms_div").find("#vehicles_id").val();
					var kms = jQuery(this).parents("#kms_div").find("#kms").val();
					jQuery.ajax({
						type: "POST",
						url: "ajax_add_kms.php",
						data: { 
							kms: kms,
							vehicles_id: vehicles_id
						}
					}).done(function( ret ) {
						//window.location="/main.php";
					});
					
				});
				
			<?php	
			}
			?>
				
	
	
		// disable link for non default countries 
		jQuery(".disable_link").bind('click', function(e){
				e.preventDefault();
		});
	
	

		// run country time
		setInterval(function(){ 
			// alert("Hello");
			var sec = new Date().getSeconds();
			//console.log('Seconds: '+sec);
			if( sec == 59 ){
				//console.log('a minute has passed');
				// javascript (client side) 
				jQuery.ajax({
					type: "POST",
					url: "ajax_get_country_time.php",					
					dataType: 'json'
				}).done(function(ret){
					// do something
					jQuery("#main_country_time #country_time_au_qld").html(ret.au_qld);
					jQuery("#main_country_time #country_time_au_nsw").html(ret.au_nsw);
					jQuery("#main_country_time #country_time_au_sa").html(ret.au_sa);
					jQuery("#main_country_time #country_time_au_vic").html(ret.au_vic);
					jQuery("#main_country_time #country_time_nz").html(ret.nz);
				});
			}
			
		}, 1000);
		
	
		jQuery(".toggler-arrow").toggle(function(){
			jQuery(this).parents(".jtoggle_div:first").find(".toggler-arrow").removeClass("arrow-toggler-bottom");
			jQuery(this).parents(".jtoggle_div:first").find(".toggler-arrow").addClass("arrow-toggler-top");			
			jQuery(this).parents(".jtoggle_div:first").find(".toggler-content").slideUp();
		},function(){
			jQuery(this).parents(".jtoggle_div:first").find(".toggler-arrow").removeClass("arrow-toggler-top");
			jQuery(this).parents(".jtoggle_div:first").find(".toggler-arrow").addClass("arrow-toggler-bottom");
			jQuery(this).parents(".jtoggle_div:first").find(".toggler-content").slideDown();
		});
		
		jQuery("#sel_num_days").change(function(){
			
			var bs_num = jQuery(this).val();
			
			// update booking_schedule_num
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_booking_schedule_selected_days.php",
				data: { 
					staff_id: <?php echo $_SESSION['USER_DETAILS']['StaffID']; ?>,
					bs_num: bs_num
				}
			}).done(function( ret ) {
				window.location="/main.php";
			});	
			
		});
		
	
	});

	/*
	$(function() {
		$('.secondset').hide();
		//$("#show_next7").mouseenter(function(){$('.secondset').slideDown();}).mouseleave(function(){$('.secondset').slideUp();$('.secondset').slideUp();});
		$("#show_next7").click(function(){
			$('.secondset').toggle('slow');
		});
	});
	*/
</script>
</body>
</html>
