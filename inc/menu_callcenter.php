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
   <li class="has-sub"><a href="#"><i class="menu-icon icon-prop">&nbsp;</i><span>Properties</span></a>
      <ul>
         <li><?php if($user->canView('view_properties.php')){ ?><a href="<?php echo URL; ?>view_properties.php"><span>Active Properties</span></a><?php } ?></li>
         <li><?php if($user->canView('view_deleted_properties.php')){ ?><a href="<?php echo URL; ?>view_deleted_properties.php"><span>Inactive Properties</span></a><?php } ?></li> 
	
	  </ul>
   </li>
   <li class="has-sub"><a href="#"><i class="menu-icon icon-jobs">&nbsp;</i><span>Jobs</span></a>
	 <?php include('view_jobs_functions.php'); ?>
      <ul>
		
         <li><?php if($user->canView('view_jobs.php')){ ?><span><a href="<?php echo URL; ?>view_jobs.php">All Jobs</a></span><?php } ?></li>
		 <?php
			$sl_params = array('status' => 'Send Letters', 'deleted' => 0);
			$sl = mysql_num_rows(getJobList2($sl_params));
		 ?>
         <li><?php if($user->canView('view_jobs.php')){ ?><span><a href="<?php echo URL; ?>view_jobs.php?status=sendletters">Send Letters <?php echo ($sl>0)?'<span class="hm-circle">'.$sl.'</span>':''; ?></a></span><?php } ?></li>
		 <?php
			$pa_params = array('status' => 'On Hold', 'deleted' => 0);
			$pa = mysql_num_rows(getJobList2($pa_params));
		 ?>
		 <li><?php if($user->canView('view_jobs.php')){ ?><span><a href="<?php echo URL; ?>view_jobs.php?status=on_hold">On Hold <?php echo ($pa>0)?'<span class="hm-circle">'.$pa.'</span>':''; ?></a></span><?php } ?></li>
		
		<?php
			$dha_params = array('status' => 'DHA', 'deleted' => 0);
			$dha = mysql_num_rows(getJobList2($dha_params));
		 ?>
		 <li><?php if($user->canView('view_jobs.php')){ ?><span><a href="<?php echo URL; ?>view_jobs.php?status=dha">DHA Jobs <?php echo ($dha>0)?'<span class="hm-circle">'.$dha.'</span>':''; ?></a></span><?php } ?></li>
		 <li><?php if($user->canView('view_jobs.php')){ ?><span><a href="<?php echo URL; ?>view_jobs.php?status=pending&agency=Any">Service Due Jobs</a></span><?php } ?></li>
		 <li><?php if($user->canView('view_jobs.php')){ ?><span><a href="<?php echo URL; ?>view_jobs.php?status=tobebooked&agency=Any">To Be Booked</a></span><?php } ?></li>
		 <li><?php if($user->canView('view_jobs.php')){ ?><span><a href="<?php echo URL; ?>view_jobs.php?status=booked">Booked</a></span><?php } ?></li>
		  <?php
		  
			include('precompleted_jobs_functions.php'); 
			$pj_sql = getPrecompletedJobs('','');	
			$pj = mysql_num_rows($pj_sql);
		
		 ?>
         <li><?php if($user->canView('precompleted_jobs.php')){ ?><span><a href="<?php echo URL; ?>precompleted_jobs.php">Pre-Completion <?php echo ($pj>0)?'<span class="hm-circle">'.$pj.'</span>':''; ?></a></span><?php } ?></li>
		  <?php
			$mc_params = array('status' => 'Merged Certificates', 'deleted' => 0);
			$mc = mysql_num_rows(getJobList2($mc_params));
		 ?>
         <li><?php if($user->canView('view_jobs.php')){ ?><span><a href="<?php echo URL; ?>view_jobs.php?status=merged">Merged Jobs <?php echo ($mc>0)?'<span class="hm-circle">'.$mc.'</span>':''; ?></a></span><?php } ?></li>
         <li><?php if($user->canView('view_jobs.php')){ ?><span><a href="<?php echo URL; ?>view_jobs.php?status=completed">Completed Jobs</a></span><?php } ?></li>
         <li><?php if($user->canView('view_jobs.php')){ ?><span><a href="<?php echo URL; ?>view_jobs.php?status=cancelled">Cancelled Jobs</a></span><?php } ?></li>		
		<li><?php if($user->canView('ageing_jobs.php')){ ?><span><a href="<?php echo URL; ?>ageing_jobs.php">60+ Days old Jobs <?php echo ($ageing_count>0)?'<span class="hm-circle-green">'.$ageing_count.'</span>':''; ?></a></span><?php } ?></li> 
      </ul>
   </li>
   
    <li class="has-sub"><a href="#"><i class="menu-icon icon-reports">&nbsp;</i><span>Reports</span></a>
      <ul>
         <li><?php if($user->canView('report_admin.php')){ ?><span><a href="<?php echo URL; ?>report_admin.php">Admin Report</a></span><?php } ?></li>
       
         <li><?php if($user->canView('report.php')){ ?><span><a href="<?php echo URL; ?>report.php">My Report</a></span><?php } ?></li>
		 
		 
		 
		 
		
		 <li><?php if($user->canView('cron_report.php')){ ?><span><a href="<?php echo URL; ?>cron_report.php">Auto Email Report</a></span><?php } ?></li>
		
		  
		<?php
			/*
			include('activity_functions.php'); 
			$actv_sql = getActivity('','');	
			$actv = mysql_num_rows($actv_sql);	
			*/
		 ?>
		
		<li><?php if($user->canView('key_tracking.php')){ ?><span><a href="<?php echo URL; ?>key_tracking.php">Key Tracking Report</a></span><?php } ?></li>
		<?php
		/*
		if($_SESSION['USER_DETAILS']['StaffID']==2025){ ?>
			<li><?php if($user->canView('test_key_acccess.php')){ ?><span><a href="<?php echo URL; ?>test_key_acccess.php">Test Key Access</a></span><?php } ?></li>
		<?php	
		}
		*/
		?>		
	 </ul>
   </li>
   <li class="has-sub"><a href="#"><i class="menu-icon icon-tech">&nbsp;</i><span>Technicians</span></a>
      <ul>
         <li><?php if($user->canView('view_techs.php')){ ?><span><a href="<?php echo URL; ?>view_techs.php">View Technicians</a></span><?php } ?></li>		
         <?php /*?><li><?php if($user->canView('add_tech_static.php')){ ?><span><a href="<?php echo URL; ?>add_tech_static.php">ADD  Technician</a></span><?php } ?></li><?php */?>
      </ul>
   </li>
    <li class="has-sub"><a href="#"><i class="menu-icon icon-agencies">&nbsp;</i><span>Agencies</span></a>
      <ul>
         <li><?php if($user->canView('view_agencies.php')){ ?><span><a href="<?php echo URL; ?>view_agencies.php">Active Agencies</a></span><?php } ?></li>
		 <li><?php if($user->canView('user_manager.php')){ ?><span><a href="<?php echo URL; ?>user_manager.php">Agency Logins</a></span><?php } ?></li>
        
      </ul>
   </li>
   
   <li class="has-sub"><a href="#"><i class="menu-icon icon-calendar">&nbsp;</i><span>Calendar</span></a>
      <ul>
         <li><?php if($user->canView('view_techs.php')){ ?><span><a href="<?php echo URL; ?>view_tech_calendar.php">Staff Calendar</a></span><?php } ?></li>
         <li><?php if($user->canView('view_techs.php')){ ?><span><a href="<?php echo URL; ?>add_calendar_entry_static.php">Add Calendar Entry</a></span><?php } ?></li>
         <li><?php if($user->canView('view_individual_staff_calendar.php')){ ?><span><a href="view_individual_staff_calendar.php">My Calendar</a></span><?php } ?></li>
      </ul>
   </li>
   <?php # We know that only Global can access SATS user interface
   if($_SESSION['USER_DETAILS']['ClassID'] == 2){ ?>
   <li class="has-sub"><a href="#"><i class="menu-icon icon-users">&nbsp;</i><span>Users</span></a>
      <ul>
         <li><a href="<?php echo URL; ?>view_sats_users.php">View Users</a></li>
         <li><a href="<?php echo URL; ?>add_sats_user.php">Add Users</a></li>		
      </ul>
   </li>
   <?php } ?>

   <li class="has-sub"><a href="#"><i class="menu-icon icon-admin">&nbsp;</i><span>Admin</span></a>
      <ul>
       
		<li><?php if($user->canView('admin_doc.php')){ ?><span><a href="<?php echo URL; ?>admin_doc.php">Internal Documents</a></span><?php } ?></li>
		<li><?php if($user->canView('view_jobs.php')){ ?><span><a href="<?php echo URL; ?>view_regions.php">Booking Regions</a></span><?php } ?></li>

       
		<li><a href="<?php echo URL; ?>banners.php">Agency Banners</a></li>
		<li><a href="<?php echo URL; ?>accomodation.php">Accomodation</a></li>

		  
      </ul>
   </li>
   <li class="has-sub"><a href="#"><i class="menu-icon icon-message">&nbsp;</i><span>Messages</span></a>
      <ul>        
		<li><a href="<?php echo URL; ?>messages.php">View Messages</a></li>
		<li><a href="<?php echo URL; ?>create_message.php">Create Messages</a></li>
		<li><?php if($user->canView('sms.php')){ ?><span><a href="<?php echo URL; ?>sms.php">Send SMS</a></span><?php } ?></li>
		
      </ul>
   </li>


   

   <?php 
   if($_SESSION['USER_DETAILS']['ClassID']!=6){ ?>  
	<li class="last logout"><a href="<?php echo URL; ?>main.php?logout=1"><i class="menu-icon icon-logout">&nbsp;</i><span>LOGOUT</span></a></li>
   <?php
   }
   ?>
   
</ul>