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
		 <li><?php if($user->canView('active_properties.php')){ ?><a href="<?php echo URL; ?>active_properties.php"><span>Active Job Properties</span></a><?php } ?></li>
		 <li><?php if($user->canView('add_property_static.php')){ ?><a href="<?php echo URL; ?>add_property_static.php"><span>Add Properties</span></a><?php } ?></li>
         <li><?php if($user->canView('import_properties_static.php')){ ?><a href="<?php echo URL; ?>import_properties_static.php"><span>Import Properties</span></a><?php } ?></li>
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
			
      </ul>
   </li>
   
	<li class="has-sub"><a href="#"><i class="menu-icon icon-daily-items">&nbsp;</i><span>Daily Items</span></a>
      <ul>
          <?php 
			
			include('duplicate_properties_functions.php'); 
			$ptotal_temp = jFindDupProp('','');	
			$ptot = mysql_num_rows($ptotal_temp);	
		?>
		 <li><?php if($user->canView('duplicate_properties.php')){ ?><a href="<?php echo URL; ?>duplicate_properties.php"><span>Duplicate Properties <?php echo ($ptot>0)?'<span class="hm-circle-green">'.$ptot.'</span>':''; ?></span></a><?php } ?></li>  
		 <?php
		include('ageing_jobs_functions.php'); 
		$ageing_sql = getAgeingJobs('','');	
		$ageing_count = mysql_num_rows($ageing_sql);	
		 ?>
		 <li><?php if($user->canView('ageing_jobs.php')){ ?><span><a href="<?php echo URL; ?>ageing_jobs.php">60+ Days old Jobs <?php echo ($ageing_count>0)?'<span class="hm-circle-green">'.$ageing_count.'</span>':''; ?></a></span><?php } ?></li> 
		 <li>
			<?php
				include('multiple_jobs_functions.php'); 
				$mj_sql = getMultipleJobs('','');	
				$mj = mysql_num_rows($mj_sql);	
			?>
			<?php if($user->canView('multiple_jobs.php')){ ?><span><a href="<?php echo URL; ?>multiple_jobs.php">Multiple Jobs <?php echo ($mj>0)?'<span class="hm-circle-green">'.$mj.'</span>':''; ?></a></span><?php } ?>
		</li>
		<?php
			$ar_params = array('status' => 'Action Required', 'deleted' => 0);
			$ar = mysql_num_rows(getJobList2($ar_params));
		 ?>
		<li><?php if($user->canView('view_jobs.php')){ ?><span><a href="<?php echo URL; ?>view_jobs.php?status=action_required">Action Required <?php echo ($ar>0)?'<span class="hm-circle-green">'.$ar.'</span>':''; ?></a></span><?php } ?></li>
		 <?php
			$esc_params = array('status' => 'Escalate', 'deleted' => 0);
			$esc = mysql_num_rows(getJobList2($esc_params));
		 ?>
		 <li><?php if($user->canView('view_jobs.php')){ ?><span><a href="<?php echo URL; ?>view_jobs.php?status=escalate">Escalated Jobs <?php echo ($esc>0)?'<span class="hm-circle-green">'.$esc.'</span>':''; ?></a></span><?php } ?></li>
		<?php 
			include('unserviced_functions.php'); 
			$unserv = mysql_num_rows(getUnservicedProperties(getExcludedProperties(),'',''));
		 ?>
		 <li><?php if($user->canView('unserviced.php')){ ?><span><a href="<?php echo URL; ?>unserviced.php">Unserviced Properties <?php echo ($unserv>0)?'<span class="hm-circle-green">'.$unserv.'</span>':''; ?></a></span><?php } ?></li>
		 <?php 
			include('no_id_properties_functions.php'); 
			$nip = mysql_num_rows(getPropertyNoAgency(getExcludedProperties(),'',''));
		 ?>
		 <li><?php if($user->canView('no_id_properties.php.php')){ ?><a href="<?php echo URL; ?>no_id_properties.php"><span>No ID Properties <?php echo ($nip>0)?'<span class="hm-circle-green">'.$nip.'</span>':''; ?></span></a><?php } ?></li>
		 <?php 
			include('missing_region_functions.php'); 
			$postcode = getPostCode();
			$mr = mysql_num_rows(getMissingRegionProperty('','',$postcode));
		   ?>
		<li><?php if($user->canView('missing_region.php')){ ?><span><a href="<?php echo URL; ?>missing_region.php">Missing Region <?php echo ($mr>0)?'<span class="hm-circle-green">'.$mr.'</span>':''; ?></a></span><?php } ?></li>
	  </ul>
   </li>
   
   
   
    <li class="has-sub"><a href="#"><i class="menu-icon icon-reports">&nbsp;</i><span>Reports</span></a>
      <ul>
         <li><?php if($user->canView('report_admin.php')){ ?><span><a href="<?php echo URL; ?>report_admin.php">Admin Report</a></span><?php } ?></li>
         <li><?php if($user->canView('report_sales_admin.php')){ ?><span><a href="<?php echo URL; ?>report_sales_admin.php">Sales Report</a></span><?php } ?></li>
         <li><?php if($user->canView('report.php')){ ?><span><a href="<?php echo URL; ?>report.php">My Report</a></span><?php } ?></li>
		 <li><?php if($user->canView('expiring.php')){ ?><span><a href="<?php echo URL; ?>expiring.php">Expiring Alarms</a></span><?php } ?></li>
		 <li><?php if($user->canView('status.php')){ ?><span><a href="<?php echo URL; ?>status.php">Status Report</a></span><?php } ?></li>
		 
		 <?php $next_month = date("M",strtotime("+1 month")); ?>
		 <li><?php if($user->canView('future_pendings.php')){ ?><span><a href="<?php echo URL; ?>future_pendings.php"><?php echo strtoupper($next_month); ?> Service Due</a></span><?php } ?></li>
		 <li><?php if($user->canView('servicedue.php')){ ?><span><a href="<?php echo URL; ?>servicedue.php">Service Due Report</a></span><?php } ?></li>
		 <li><?php if($user->canView('cron_report.php')){ ?><span><a href="<?php echo URL; ?>cron_report.php">Auto Email Report</a></span><?php } ?></li>
		 <li><?php if($user->canView('completed_report.php')){ ?><span><a href="<?php echo URL; ?>completed_report.php">Completed Jobs</a></span><?php } ?></li>
		  
		<?php
			/*
			include('activity_functions.php'); 
			$actv_sql = getActivity('','');	
			$actv = mysql_num_rows($actv_sql);	
			*/
		 ?>
		<li><?php if($user->canView('activity.php')){ ?><span><a href="<?php echo URL; ?>activity.php">Agent Activity <?php //echo ($actv>0)?'<span class="hm-circle">'.$actv.'</span>':''; ?></a></span><?php } ?></li>
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
		 <li><?php if($user->canView('tech_doc.php')){ ?><span><a href="<?php echo URL; ?>tech_doc.php">Tech Documents</a></span><?php } ?></li>
		 <li><?php if($user->canView('tech_doc_tech.php')){ ?><span><a href="<?php echo URL; ?>tech_doc_tech.php">Tech View Documents</a></span><?php } ?></li>
		 <li><?php if($user->canView('contractors.php')){ ?><span><a href="<?php echo URL; ?>contractors.php">Contractors</a></span><?php } ?></li>
         <?php /*?><li><?php if($user->canView('add_tech_static.php')){ ?><span><a href="<?php echo URL; ?>add_tech_static.php">ADD  Technician</a></span><?php } ?></li><?php */?>
      </ul>
   </li>
    <li class="has-sub"><a href="#"><i class="menu-icon icon-agencies">&nbsp;</i><span>Agencies</span></a>
      <ul>
         <li><?php if($user->canView('view_agencies.php')){ ?><span><a href="<?php echo URL; ?>view_agencies.php">Active Agencies</a></span><?php } ?></li>
         <li><?php if($user->canView('add_agency_static.php')){ ?><span><a href="<?php echo URL; ?>add_agency_static.php">Add Agency</a></span><?php } ?></li>
         <li><?php if($user->canView('view_agency_regions.php')){ ?><span><a href="<?php echo URL; ?>view_agency_regions.php">Agency Regions</a></span><?php } ?></li>
		 <li><?php if($user->canView('franchise_groups.php')){ ?><span><a href="<?php echo URL; ?>franchise_groups.php">Franchise Groups</a></span><?php } ?></li>
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
   <li class="has-sub"><a href="#"><i class="menu-icon icon-sales">&nbsp;</i><span>Sales</span></a>
      <ul>
         <li><?php if($user->canView('view_target_agencies.php')){ ?><span><a href="<?php echo URL; ?>view_target_agencies.php">Target Agencies</a></span><?php } ?></li>
         <li><?php if($user->canView('sales_documents.php')){ ?><span><a href="<?php echo URL; ?>sales_documents.php">Sales Documents</a></span><?php } ?></li>
		 <li><?php if($user->canView('sales_snapshot.php')){ ?><span><a href="<?php echo URL; ?>sales_snapshot.php">Sales Snapshot</a></span><?php } ?></li>
      </ul>
   </li>
   <li class="has-sub"><a href="#"><i class="menu-icon icon-admin">&nbsp;</i><span>Admin</span></a>
      <ul>
        <li><a href="<?php echo URL; ?>resources.php">Agent Site Documents</a></li>
		<li><?php if($user->canView('admin_doc.php')){ ?><span><a href="<?php echo URL; ?>admin_doc.php">Internal Documents</a></span><?php } ?></li>
		<li><?php if($user->canView('view_jobs.php')){ ?><span><a href="<?php echo URL; ?>view_regions.php">Booking Regions</a></span><?php } ?></li>
		<li><?php if($user->canView('countries.php')){ ?><span><a href="<?php echo URL; ?>countries.php">Countries</a></span><?php } ?></li>
        <li><?php if($user->canView('noticeboard.php')){ ?><span><a href="<?php echo URL; ?>noticeboard.php">Agency Noticeboard</a></span><?php } ?></li>
		<li><a href="<?php echo URL; ?>banners.php">Agency Banners</a></li>
		<li><a href="<?php echo URL; ?>accomodation.php">Accomodation</a></li>
		<li><a href="<?php echo URL; ?>agency_site_maintenance_mode.php">Maintenance Mode</a></li>
		<li><a href="<?php echo URL; ?>passwords.php">Passwords</a></li>
		<li><a href="<?php echo URL; ?>suppliers.php">Suppliers</a></li>  
      </ul>
   </li>
   <li class="has-sub"><a href="#"><i class="menu-icon icon-message">&nbsp;</i><span>Messages</span></a>
      <ul>        
		<li><a href="<?php echo URL; ?>messages.php">View Messages</a></li>
		<li><a href="<?php echo URL; ?>create_message.php">Create Messages</a></li>
		<li><?php if($user->canView('sms.php')){ ?><span><a href="<?php echo URL; ?>sms.php">Send SMS</a></span><?php } ?></li>
		<li><?php if($user->canView('sms_messages.php')){ ?><span><a href="<?php echo URL; ?>sms_messages.php">SMS Templates</a></span><?php } ?></li>
      </ul>
   </li>
   <li class="has-sub"><a href="#"><i class="menu-icon icon-vehicle">&nbsp;</i><span>Vehicles</span></a>
      <ul>
         <li><?php if($user->canView('view_vehicles.php')){ ?><span><a href="<?php echo URL; ?>view_vehicles.php">View Vehicles</a></span><?php } ?></li>
         <li><?php if($user->canView('add_vehicle.php')){ ?><span><a href="<?php echo URL; ?>add_vehicle.php">Add Vehicle</a></span><?php } ?></li>
		 <li><?php if($user->canView('add_vehicle.php')){ ?><span><a href="<?php echo URL; ?>kms.php">KMS Report</a></span><?php } ?></li>
      </ul>
   </li>
   
    <li class="has-sub"><a href="#"><i class="menu-icon icon-vehicle">&nbsp;</i><span>Assign</span></a>
      <ul>
        <li><?php if($user->canView('view_jobs.php')){ ?><span><a href="<?php echo URL; ?>whiteboard.php">Whitebard</a></span><?php } ?></li>
		<li><?php if($user->canView('view_overall_schedule.php')){ ?><span><a href="<?php echo URL; ?>view_overall_schedule.php">Overall Tech Schedule</a></span><?php } ?></li>
      </ul>
   </li>
   

   <?php 
   if($_SESSION['USER_DETAILS']['ClassID']!=6){ ?>  
	<li class="last logout"><a href="<?php echo URL; ?>main.php?logout=1"><i class="menu-icon icon-logout">&nbsp;</i><span>LOGOUT</span></a></li>
   <?php
   }
   ?>
   
</ul>