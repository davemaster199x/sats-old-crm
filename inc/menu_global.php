<ul>
	<li>
    <span class="first">
		<form action="search.php" method="post" style="margin: 8px 0;">
			 <input type="text" name="search" style="width: 85px;" />
			<input type="submit" class="submitbtnImg" name="submit" value="Search" />
		</form>
        </span>
	</li>
   <li class="has-sub"><a href="main.php"><i class="menu-icon icon-shome">&nbsp;</i><span>Home</span></a></li>
   <li class="has-sub"><a href="#"><i class="menu-icon icon-prop">&nbsp;</i><span class="jmenu">Properties</span></a>
      <ul class="menu_ul">
			<li><span><a href=""><img src="/images/loading.gif" style="height: 30px;" /></a></span></li>
		</ul>
   </li>
	<li class="has-sub" id="menu_jobs">
		<a href="#"><i class="menu-icon icon-jobs">&nbsp;</i><span class="jmenu">Jobs</span></a>
		<ul class="menu_ul">
			<li><span><a href=""><img src="/images/loading.gif" style="height: 30px;" /></a></span></li>
		</ul>
	</li>
   
	<li class="has-sub" id="menu_daily_items">
		<a href="#"><i class="menu-icon icon-daily-items">&nbsp;</i><span class="jmenu">Daily Items</span></a>
		<ul class="menu_ul">
			<li><span><a href=""><img src="/images/loading.gif" style="height: 30px;" /></a></span></li>
		</ul>
	</li>
   
   
   
    <li class="has-sub"><a href="#"><i class="menu-icon icon-reports">&nbsp;</i><span class="jmenu">Reports</span></a>
      <ul class="menu_ul">
			<li><span><a href=""><img src="/images/loading.gif" style="height: 30px;" /></a></span></li>
		</ul>
   </li>
   <li class="has-sub"><a href="#"><i class="menu-icon icon-tech">&nbsp;</i><span class="jmenu">Technicians</span></a>
      <ul class="menu_ul">
			<li><span><a href=""><img src="/images/loading.gif" style="height: 30px;" /></a></span></li>
		</ul>
   </li>
    <li class="has-sub"><a href="#"><i class="menu-icon icon-agencies">&nbsp;</i><span class="jmenu">Agencies</span></a>
      <ul class="menu_ul">
			<li><span><a href=""><img src="/images/loading.gif" style="height: 30px;" /></a></span></li>
		</ul>
   </li>
   
   <li class="has-sub"><a href="#"><i class="menu-icon icon-calendar">&nbsp;</i><span class="jmenu">Calendar</span></a>
      <ul class="menu_ul">
			<li><span><a href=""><img src="/images/loading.gif" style="height: 30px;" /></a></span></li>
		</ul>
   </li>
   <?php # We know that only Global can access SATS user interface
   if($_SESSION['USER_DETAILS']['ClassID'] == 2){ ?>
   <li class="has-sub"><a href="#"><i class="menu-icon icon-users">&nbsp;</i><span class="jmenu">Users</span></a>
        <ul class="menu_ul">
			<li><span><a href=""><img src="/images/loading.gif" style="height: 30px;" /></a></span></li>
		</ul>
   </li>
   <?php } ?>
   <li class="has-sub"><a href="#"><i class="menu-icon icon-sales">&nbsp;</i><span class="jmenu">Sales</span></a>
		<ul class="menu_ul">
			<li><span><a href=""><img src="/images/loading.gif" style="height: 30px;" /></a></span></li>
		</ul>
   </li>
   <li class="has-sub"><a href="#"><i class="menu-icon icon-admin">&nbsp;</i><span class="jmenu">Admin</span></a>
      <ul class="menu_ul">
			<li><span><a href=""><img src="/images/loading.gif" style="height: 30px;" /></a></span></li>
		</ul>
   </li>
   <li class="has-sub"><a href="#"><i class="menu-icon icon-message">&nbsp;</i><span class="jmenu">Messages</span></a>
      <ul class="menu_ul">
			<li><span><a href=""><img src="/images/loading.gif" style="height: 30px;" /></a></span></li>
		</ul>
   </li>
   <li class="has-sub"><a href="#"><i class="menu-icon icon-vehicle">&nbsp;</i><span class="jmenu">Vehicles</span></a>
      <ul class="menu_ul">
			<li><span><a href=""><img src="/images/loading.gif" style="height: 30px;" /></a></span></li>
		</ul>
   </li>
   
    <li class="has-sub"><a href="#"><i class="menu-icon icon-vehicle">&nbsp;</i><span class="jmenu">Assign</span></a>
      <ul class="menu_ul">
			<li><span><a href=""><img src="/images/loading.gif" style="height: 30px;" /></a></span></li>
		</ul>
   </li>
   

   <?php 
   if($_SESSION['USER_DETAILS']['ClassID']!=6){ ?>  
	<li class="last logout"><a href="<?php echo URL; ?>main.php?logout=1"><i class="menu-icon icon-logout">&nbsp;</i><span>LOGOUT</span></a></li>
   <?php
   }
   ?>
   
</ul>
<script>
jQuery(document).ready(function(){
	
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
	
});
</script>