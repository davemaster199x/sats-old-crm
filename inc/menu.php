<?php
// message pop ups are only for techs
if($_SESSION['USER_DETAILS']['ClassID']==6){
include('check_new_message.php');
}

$crm = new Sats_Crm_Class;

if( $_SESSION['country_default'] == 1 ){ // AU

	/*
	2070 - Developer testing
	2025 - Daniel
	11 - Ness
	2056 - Robert Bell
	2175 - Thalia Paki
	2191 - Ashlee Ryan
	*/
	$tester = array(2025,2070,11,2056,2175,2191);

}else if( $_SESSION['country_default'] == 2 ){ // NZ

	/*
	2070 - Developer testing
	2025 - Daniel
	11 - Ness
	2056 - Robert Bell
	2175 - Thalia Paki
	2191 - Ashlee Ryan

	2210 - Julie Denega
	2193 - Thalia Paki
	*/
	$tester = array(2025,2070,11,2056,2175,2191,2210,2193);

}


?>



<style>
.jflag{
	width: 30px;
	cursor: pointer;
}
.jstaff_name{
	color: #575755;
	font-size: 14px;
	font-weight: bold;
	float: right;
	position: relative;
    right: 12px;
    top: 20px;
}
div#time{
	display: none;
}
.new_tenants_format{
	float: right;
	position: relative;
	top: 19px;
	right: 14px;
	margin-right: 16px;
	color: #575755;
	font-size: 14px;
	font-weight: bold;
}
.tbl-sd tr td button {
    font-size: 16px;
}
.search_jobs_div{
	float: right;
	position: relative;
	right: 44px;
	top: 14px;
	color: #b4151b;
}
#keep_session_alive{
	display: none;
}
</style>

<!-- new message pop up for tech - START -->
<a id="tech_new_msg_fb_link" href="#tech_new_msg_fb" style="display:none;">click</a>
<div style="display:none">
	<div id="tech_new_msg_fb" style="margin: 10px;">
		<h4>You have new message, please check <a href="/messages.php">messages</a> page for details</h4>
	</div>
</div>
<!-- new message pop up for tech - END -->

<script>

// Enable pusher logging - don't include this in production
Pusher.logToConsole = true;
var pusherKey = "<?=PUSHER_KEY?>";
var pusherClu = "<?=PUSHER_CLUSTER?>";
var pusher = new Pusher(pusherKey, {
  cluster: pusherClu,
  forceTLS: true
});
var ch = "ch<?=$_SESSION['USER_DETAILS']['StaffID']?>";
var ev = "ev01";

var channel = pusher.subscribe(ch);
channel.bind(ev, function(data) {


	<?php
	if( $_SESSION['USER_DETAILS']['ClassID'] == 6 ){ // tech ?>
		jQuery("a#tech_new_msg_fb_link").fancybox();
		jQuery("#tech_new_msg_fb_link").click();
	<?php
	}else{ // all other users ?>
		// alert(JSON.stringify(data));
		getNotification();
	<?php
	}
	?>


});
</script>

 <? if($_SESSION['USER_DETAILS']['StaffID']){ ?>
    <div id="staff_box">



	<?php

	$c_sql = getStaffCountries($_SESSION['USER_DETAILS']['StaffID'],'c.`iso`');
	$c_default_sql = getCountrySelectedDefault($_SESSION['USER_DETAILS']['StaffID']);
	$c_default = mysql_fetch_array($c_default_sql);
	$country_default = $c_default['country_id'];
	$_SESSION['country_default'] = $country_default;
	$_SESSION['country_iso'] = $c_default['iso'];
	$_SESSION['country_name'] = $c_default['country'];

	// get Staff user and pass for redirect
	$logged_user_sql = mysql_query("
		SELECT *
		FROM staff_accounts
		WHERE `StaffID` = {$_SESSION['USER_DETAILS']['StaffID']}
	");
	$logged_user = mysql_fetch_array($logged_user_sql);
	$username = $logged_user['Email'];

	$encrypt = new cast128();
	$encrypt->setkey(SALT);
	$password = $encrypt->decrypt(utf8_decode($logged_user['Password']));
	//echo "Username: {$username}<br />Password: {$password}";

	if( mysql_num_rows($c_sql)>1 ){
		while( $c = mysql_fetch_array($c_sql) ){
			// allowed country to be displayed
			$active_country = array(1,2);
			if( in_array($c['country_id'], $active_country) ){

				$domain = $_SERVER['SERVER_NAME'];
				if( $c['country_id']==1 ){ // AU
					// go to NZ
					$country_iso_txt = 'NZ';

					if( strpos($domain,"crmdev") !== false ){ // DEV
						$site_link = 'https://crmdev.sats.com.au';
						$crm_ci_link = 'https://crmdevci.sats.com.au';
					}else{ // LIVE
						$site_link = 'https://crm.sats.com.au';
						$crm_ci_link = 'https://crmci.sats.com.au';
					}


				}else if( $c['country_id']==2 ){ // NZ
					// go to AU
					$country_iso_txt = 'AU';

					if( strpos($domain,"crmdev") !== false ){ // DEV
						$site_link = 'https://crmdev.sats.co.nz';
					}else{ // LIVE
						$site_link = 'https://crm.sats.co.nz';
					}
				}

				$complete_url = "{$site_link}?user=".urlencode($username)."&pass=".urlencode($password);

			?>
			<span>
				<a target="__blank" href="<?php echo $complete_url; ?>">
					<img class="jflag" src="/images/flags/<?php echo strtolower($c['iso']); ?><?php echo ($c['country_id']!=$country_default)?'_bw':''; ?>.png" title="<?php echo $c['country'] ?>" />
				</a>
				<input type="hidden" class="m_country_id" value="<?php echo $c['country_id']; ?>" />
			</span>
		<?php
			}
		}
	}






	?>


    </div>


	<?php
	// notification are only for non tech
	if($_SESSION['USER_DETAILS']['ClassID']!=6){ ?>









		<div style="position:absolute; right: 1px;">


			<div class="jstaff_name" style="float:right;">
				<?php echo $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName']; ?>
				- <?php echo date("l jS F Y"); ?>
			</div>

			<div class="notication_div">
			<?php
			// get live notificationss
			include('inc_get_notifications.php');
			?>
			</div>

			<div class="search_jobs_div">
				<form action="search_jobs.php" method="post" style="margin-top: 14px; display: inline;">

					<label style="margin-left: 26px;"><strong>Search:</strong></label>
					<select name="search_type">
						<option value="1">Phone Number</option>
						<option value="2" selected>Address</option>
						<option value="3">Landlord</option>
						<option value="4">Building Name</option>
					</select>
					<input class="addinput" type="text" name="phrase" size=10 value="" style="float:none; width: 130px;" />
					<input type="submit" class="submitbtnImg chops" value="Search" name="btn_search_jobs" />

				</form>
			</div>


		</div>

	<?php
	}
	?>





	<div style="clear:both;"></div>

 <? } ?>
<script>

function playIonSoundNotification(){

	// init bunch of sounds
	ion.sound({
		sounds: [
			{name: "door_bell"}
		],

		// main config
		path: "ion_sound/sounds/",
		preload: true,
		multiplay: true,
		volume: 0.9
	});

	// play sound
	ion.sound.play("door_bell");

}


// hide notification box if clicked outside of it
jQuery(document).mouseup(function (e){
	var container = jQuery(".notification_box");

	if (!container.is(e.target) // if the target of the click isn't the container...
		&& container.has(e.target).length === 0) // ... nor a descendant of the container
	{
		container.hide();

	}
});


function playSoundNotification(){

	var num_not = parseInt(jQuery(".notification_bubble").html());
	var sound_nofication = parseInt(jQuery(".sound_nofication").val());
	//console.log("Num of not:"+num_not);
	//console.log("sound_nofication:"+sound_nofication);
	if( sound_nofication==1 ){
		//console.log('update notification to read');
		// play sound notification
		playIonSoundNotification();
		// turn sound notifcation off
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_sound_notification.php",
			data: {
				staff_id: <?php echo $_SESSION['USER_DETAILS']['StaffID']; ?>
			}
		}).done(function( ret ) {
			// function here
		});

	}

}

// ajax call get notifications
function getNotification(){

	//playSoundNotification();

	// update notication
	jQuery.ajax({
		type: "POST",
		url: "ajax_get_notifications.php",
		data: {
			staff_id: <?php echo $_SESSION['USER_DETAILS']['StaffID']; ?>
		}
	}).done(function( ret ) {
		//window.location="/main.php";
		jQuery(".notication_div").html(ret);
		playSoundNotification();
	});




}

var notf_intv;

jQuery(document).ready(function(){





	// Message Pop Up box, Using custom settings
	jQuery("a#messagePopUp").fancybox({
		'hideOnOverlayClick':false,
		'hideOnContentClick':false,
		'onClosed': function() {
			//console.log('onclosed');
			jQuery("#fbPopUp .mh_id").each(function(){

				var mh_id = jQuery(this).val();
				var msg_id = jQuery(this).parents("#fbPopUp").find(".msg_id").val();

				jQuery.ajax({
					type: "POST",
					url: "ajax_message_mark_as_read.php",
					data: {
						mh_id: mh_id,
						msg_id: msg_id,
						staff_id: <?php echo $_SESSION['USER_DETAILS']['StaffID']; ?>
					}
				});

			});
		}
	});
	jQuery("#messagePopUp").click();





	// KMS pop up
	jQuery("a#kmsPopUp").fancybox({
		'hideOnOverlayClick':false,
		'hideOnContentClick':false,
		'onClosed': function() {
			// do function
		}
	});
	jQuery("#kmsPopUp").click();



	// Stocktake pop up
	jQuery("a#stocktakePopUp").fancybox({
		'hideOnOverlayClick':false,
		'hideOnContentClick':false,
		'onClosed': function() {
			// do function
		}
	});
	jQuery("#stocktakePopUp").click();





	// play notification if available on load
	playSoundNotification();
	// real time notification, not really realtime its every 15 seconds xD
	notf_intv = setInterval(function(){

		//console.log('fetch notification');
		// getNotification();

	}, 15000);






	// show/hide notification box
	jQuery(document).on("click",".notification_icons",function(){

		//console.log('notification clicked');
		var display = jQuery(this).parents(".main_notf_div").find(".notification_box").css("display");
		var notf_type = jQuery(this).parents(".main_notf_div").find(".notification_box").attr("data-notf_type");
		if( display == 'none' ){


			jQuery.ajax({
				type: "POST",
				url: "ajax_update_notification_read.php",
				data: {
					staff_id: <?php echo $_SESSION['USER_DETAILS']['StaffID']; ?>,
					notf_type: notf_type
				}
			}).done(function( ret ) {
				//window.location="/main.php";
			});



			jQuery(this).parents(".main_notf_div").find(".notification_box").show();
		}else{
			jQuery(this).parents(".main_notf_div").find(".notification_box").hide();
		}


	});



	/*
	jQuery(".jflag").click(function(){

		var country_name = jQuery(this).prop("title");

		if(confirm("Are you sure you want to change to "+country_name+"?")){

			var country_id = jQuery(this).parents("span:first").find(".m_country_id").val();

			jQuery.ajax({
				type: "POST",
				url: "ajax_update_country_default.php",
				data: {
					staff_id: <?php echo $_SESSION['USER_DETAILS']['StaffID']; ?>,
					country_id: country_id
				}
			}).done(function( ret ) {
				window.location="/main.php";
			});

		}

	});
	*/






	
	// keep session alive, refresh session every minute
    var resetSession = setInterval(function(){

		jQuery.ajax({
			type: "POST",
			url: "ajax_session_keep_alive.php"
		}).done(function(ret){

			console.log(ret)
			jQuery("#keep_session_alive").html(ret);

		});

    }, 60 * 1000 );
	



});
</script>

  <h3 class="fltrt" style="display:none;">&nbsp;</h3>

<div class="panl">Show/Hide Menu</div>

<?
# All types EXCEPT TECHS can see the menu, TECHS only see a single "view schedule" link
if($_SESSION['USER_DETAILS']['ClassName'] <> "TECHNICIAN"){ ?>

<div id="sidebar1">



  <script type="text/javascript">
		$(function(){ // document ready

			/*
		  if (!!$('.sticky').offset()) { // make sure ".sticky" element exists

		    var stickyTop = $('.sticky').offset().top; // returns number

		    $(window).scroll(function(){ // scroll event

		      var windowTop = $(window).scrollTop(); // returns number

		      if (stickyTop < windowTop){
		        $('.sticky').addClass('stickyfixed');
				$('.homepage').addClass('homepageright');
		      }
		      else {
		        $('.sticky').removeClass('stickyfixed');
				$('.homepage').removeClass('homepageright');
		      }

		    });

		  }
		  */




		});
	</script>


<?php
$user_type = $_SESSION['USER_DETAILS']['ClassID'];
?>
<div id="cssmenu" class="sticky">
	<ul>
		<li>
			<span class="first">
				<form action="search.php" method="post" style="margin: 8px 0;">
					 <input type="text" name="search" style="width: 85px; margin-left: 5px;" placeholder="Job/Prop ID" />
					<input type="submit" class="submitbtnImg" name="submit" value="Search" />
				</form>
			</span>
		</li>
		<?php
		if(CURRENT_DOMAIN=='sats.com.au'){
			$current_server_txt = 'AU';
		}else if(CURRENT_DOMAIN=='sats.co.nz'){
			$current_server_txt = 'NZ';
		}
		?>
		<li class="has-sub">
			<a href="main.php">
				<i class="menu-icon icon-shome">&nbsp;</i>
				<span>Home <?php echo $current_server_txt; ?></span>
			</a>
		</li>
		<?php

		// Initiate job class
		$crm_menu = new Sats_Crm_Class;

		$sc_id = $_SESSION['USER_DETAILS']['ClassID'];
		$sa_id = $_SESSION['USER_DETAILS']['StaffID'];

		// MENU
		$menu_sql = $crm_menu->getMenus();


		if(mysql_num_rows($menu_sql)>0){
			$menus = [];
			$menusById = [];
			$index = 0;
			while (($menuResult = mysql_fetch_array($menu_sql))) {
				$menu = $menuResult;

				$menu['can_view'] = false;
				$menus[] = $menu;
				$menusById[$menu['menu_id']] = &$menus[$index++];
			}

			$menus_staff_can_view = $crm_menu->menusStaffCanView($sc_id, $sa_id);

			foreach ($menus_staff_can_view as $m) {
				$menusById[$m['menu_id']]['can_view'] = true;
			}

			// while( $menu = mysql_fetch_array($menu_sql) ){
			// foreach ($menus as $menu) {
			for ($x = 0; $x < count($menus); $x++) {
			$menu = &$menus[$x];

			// if( $crm_menu->canViewMenu($menu['menu_id'],$sa_id,$sc_id) == true  ){
			if( $menu['can_view'] ){

			$jtable_id = "jtable_menu_{$menu['menu_id']}";






			switch( $menu['menu_name'] ){

				case 'Reports':

					// CI version
					$crm_ci_page = 'reports';
					$menu_link = $crm->crm_ci_redirect($crm_ci_page);

				break;
				default:
					$menu_link = 'javascript:void(0);';

			}

			?>
				<li class="has-sub">
					<a href="<?php echo $menu_link; ?>">
						<i class="menu-icon <?php echo $menu['icon_class']; ?>">&nbsp;</i>
						<span class="jmenu_v2" data-menu_id="<?php echo $menu['menu_id']; ?>"><?php echo $menu['menu_name']; ?></span>
					</a>
					<?php
					if( $menu['menu_id'] != 4 ){ ?>
						<ul class="menu_ul">
							<li><span><a href=""><img src="/images/loading.gif" style="height: 30px;" /></a></span></li>
						</ul>
					<?php
					}
					?>
				</li>
		<?php
				}
			}
		}
		?>

		<?php
		if( in_array($sa_id, $tester) ){ ?>

			<!--
			<li class="has-sub"><a href="#"><i class="menu-icon icon-reports">&nbsp;</i><span class="jmenu">API</span></a>
				<ul class="menu_ul">
					<li><a href="pm_agencies.php">PM Agencies</a></li>
					<li><a href="search_address.php">Search Address</a></li>
				</ul>
			</li>
			-->




			<li class="has-sub"><a href="#"><i class="menu-icon icon-reports">&nbsp;</i><span class="jmenu">Test</span></a>
				<ul class="menu_ul">

					<!--
					<li><a href="payments_credits.php">Payments Credits</a></li>
					<li><a href="add_warranty.php">Add Warranty</a></li>
					<li><a href="warranty_report.php">Warranty Report</a></li>
					<li><a href="run_move_tenants_to_new_table.php">Move Tenants Query</a></li>
					<li><a href="run_update_invoice_details.php">Run Update Invoice Details</a></li>
					<li><a href="run_compass_create_jobs.php">Run Compass Job Creation</a></li>
					<li><a href="run_copy_new_tenants.php" target="_blank">Run Copy New Tenants</a></li>
					<li><a href="export_agency_username_and_password_main.php" target="_blank">Export Agency Username and Password</a></li>
					-->

					<li><a href="/export_staff_accounts_password.php" target="_blank">Export Staff Account Password</a></li>
					<li><a href="/find_expired_240v_alarm.php" target="_blank" target="blank">Find expired 240v alarms</a></li>
					<li><a href="/run_cron_manually.php" target="blank">Run Cron Manually</a></li>
					<li><a href="/find_pending_jobs_with_completed_jobs_in_the_last_30_days.php" target="blank">Find Pending Jobs with Completed job in the last 30 days</a></li>
					<!--<li><a href="/cronjobs/multi_cron_flush.php" target="blank">Multi Cron Flush</a></li>-->
					<li><a href="/nlm_jobs.php" target="blank">NLM jobs</a></li>
					<li><a href="/less_than_4_postcodes_properties.php" target="blank">Less than 4 postcodes properties</a></li>



					<li><a href="/property_service_to_sats.php" target="blank">Property Service To SATS</a></li>
					<li><a href="/property_service_to_sats_ver2.php" target="blank">Property Service To SATS 2</a></li>
					<li><a href="/property_services_inactive_in_agency.php" target="blank">Property Service Inactive in Agency</a></li>


					<li><a href="/send_letters_all.php" target="blank">All send letter jobs</a></li>

					<li><a href="/no_ym_completed_properties.php" target="blank">No YM Completed Properties</a></li>
					<li><a href="/property_with_no_active_jobs.php" target="blank">No Active Jobs Properties</a></li>

					<li><a href="/possible_wrong_street_name.php" target="blank">Possible Wrong Street Name</a></li>



				</ul>
			</li>





		<?php
		}
		?>







		<?php
	   if( $user_type!=6 ){ ?>
		<li class="last logout"><a href="<?php echo URL; ?>main.php?logout=1"><i class="menu-icon icon-logout">&nbsp;</i><span>LOGOUT</span></a></li>
	   <?php
	   }
	   ?>


	</ul>
</div>

<?php
if( $user_type!=6 ){ ?>
<div class="logo"><a href="/main.php"><img src="images/satslogo.png" alt="SATS"></a></div>
<?php
}
?>

</div>

<?php } ?>

<div id="keep_session_alive">Session Test</div>

<script src="js/css_browser_selector.js"></script>

<script>

$(document).ready(function(){


	// menu ajax script
	jQuery(".jmenu_v2").click(function(){

		var obj = jQuery(this);
		var menu_id = obj.attr("data-menu_id");

		//console.log('trigger');

		if( menu_id != 4 ){

			jQuery.ajax({
				type: "POST",
				url: "ajax_menu_v2.php",
				data: {
					menu_id: menu_id
				}
			}).done(function( ret ){
				obj.parents("li:first").find(".menu_ul").html(ret);
			});

		}



	});


	/*
	// menu ajax script
	jQuery(".jmenu").click(function(){

		var obj = jQuery(this);
		var menu_type = obj.html();

		//console.log('trigger');

		jQuery.ajax({
			type: "POST",
			url: "ajax_menu.php",
			data: {
				menu_type: menu_type
			}
		}).done(function( ret ){
			//window.location="/precompleted_jobs.php";
			obj.parents("li:first").find(".menu_ul").html(ret);
		});


	});
	*/

$('#cssmenu > ul > li ul').each(function(index, e){
  var count = $(e).find('li').length;
  var content = '<span class="cnt">' + count + '</span>';
  $(e).closest('li').children('a').append(content);
});
$('#cssmenu ul ul li:odd').addClass('odd');
$('#cssmenu ul ul li:even').addClass('even');
$('#cssmenu > ul > li > a').click(function() {
  $('#cssmenu li').removeClass('active');
  $(this).closest('li').addClass('active');
  var checkElement = $(this).next();
  if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
    $(this).closest('li').removeClass('active');
    checkElement.slideUp('normal');
  }
  if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
    $('#cssmenu ul ul:visible').slideUp('normal');
    checkElement.slideDown('normal');
  }
  if($(this).closest('li').find('ul').children().length == 0) {
    return true;
  } else {
    return false;
  }
});

//$(".panl").click(function(){
//    $("#sidebar1").animate({width: 'toggle'});
//});

 $(function() {
$( ".panl" ).click(function() {
$("#sidebar1").animate({width: 'toggle'});
$( "#sidebar1" ).toggleClass( "newClass", 1000 );
});
});




});


</script>