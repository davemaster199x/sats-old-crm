<?php

include('inc/init_for_ajax.php');
$jc = new Job_Class();
$crm = new Sats_Crm_Class();

$menu_type = $_POST['menu_type'];
$user_type = $_SESSION['USER_DETAILS']['ClassID'];
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

// WIP only show to these people
$vip = array(2025,2070,58);

// 2070 - Joe, 2025 - Daniel
$tester = array(2025,2070);


/*
2 = global
3 = admin
5 = sales
7 = call center
8 = OS call center
9 = full access
*/

if( $menu_type=='Properties' ){ ?>

	<?php
	if( $user_type != 7 && $user_type != 8 ){ ?>	
		<li><?php if($user->canView('active_properties.php')){ ?><a href="<?php echo URL; ?>active_properties.php"><span>Active Job Properties</span></a><?php } ?></li>			
	<?php	
	}
	?>
	<li><?php if($user->canView('view_properties.php')){ ?><a href="<?php echo URL; ?>view_properties.php"><span>Active Properties</span></a><?php } ?></li>
	<li><?php if($user->canView('add_private_residential.php')){ ?><a href="<?php echo URL; ?>add_private_residential.php"><span>Add Private Residential</span></a><?php } ?></li>
	<?php
	if( $user_type != 7 && $user_type != 8 ){ ?>	
		<li><?php if($user->canView('add_property_static.php')){ ?><a href="<?php echo URL; ?>add_property_static.php"><span>Add Properties</span></a><?php } ?></li>
		<li><?php if($user->canView('import_properties.php')){ ?><a href="<?php echo URL; ?>import_properties.php"><span>Import Properties</span></a><?php } ?></li>
	<?php	
	}
	?>
	<li><?php if($user->canView('view_deleted_properties.php')){ ?><a href="<?php echo URL; ?>view_deleted_properties.php"><span>Inactive Properties</span></a><?php } ?></li> 


<?php	
}else if( $menu_type=='Jobs' ){ ?>

	<li><?php if($user->canView('outside_tech_hours.php')){ ?><span><a href="<?php echo URL; ?>outside_tech_hours.php">After Hours Jobs <?php echo ($jtot>0)?'<span class="hm-circle">'.$jtot.'</span>':''; ?></a></span><?php } ?></li>

	<?php 
	$jparams = array(
		'country_id' => $_SESSION['country_default'],
		'job_status' => 'Allocate'
	);
	$jtot = mysql_num_rows($crm->getJobsData($jparams));
	?>
	<li><?php if($user->canView('allocate.php')){ ?><span><a href="<?php echo URL; ?>allocate.php">Allocate <?php echo ($jtot>0)?'<span class="hm-circle">'.$jtot.'</span>':''; ?></a></span><?php } ?></li>
	
	<li><?php if($user->canView('view_jobs.php')){ ?><span><a href="<?php echo URL; ?>view_jobs.php">All Jobs</a></span><?php } ?></li>
	<li><?php if($user->canView('booked_jobs.php')){ ?><span><a href="<?php echo URL; ?>booked_jobs.php">Booked Jobs</a></span><?php } ?></li>
	
	<li><?php if($user->canView('cancelled_jobs.php')){ ?><span><a href="<?php echo URL; ?>cancelled_jobs.php">Cancelled Jobs</a></span><?php } ?></li>
	<li><?php if($user->canView('completed_jobs.php')){ ?><span><a href="<?php echo URL; ?>completed_jobs.php">Completed Jobs</a></span><?php } ?></li>
	<?php
	$cot_count = 0;
	$cot_sql = $jc->getJobs('','',$sort,$order_by,'cot & lr','To Be Booked','','','','','');
	while( $cot_row = mysql_fetch_array($cot_sql) ){
		if( ( $cot_row['start_date']=="" || $cot_row['start_date']=="0000-00-00" ) && ( $cot_row['due_date']=="" || $cot_row['due_date']=="0000-00-00" ) && $cot_row['no_dates_provided']==0 ){
			$cot_count++;
		}
	}
	?>
	<li><?php if($user->canView('cot_jobs.php')){ ?><span><a href="<?php echo URL; ?>cot_jobs.php">COT Jobs <?php echo ($cot_count>0)?'<span class="hm-circle">'.$cot_count.'</span>':''; ?></a></span><?php } ?></li>
	
	<li><?php if($user->canView('deleted_jobs.php')){ ?><span><a href="<?php echo URL; ?>deleted_jobs.php">Deleted Jobs</a></span><?php } ?></li>
	
	<?php
	$jtot = mysql_num_rows($jc->getJobs('','',$sort,$order_by,'','DHA','','','','',''));
	?>
	<li><?php if($user->canView('dha_jobs.php')){ ?><span><a href="<?php echo URL; ?>dha_jobs.php">DHA Jobs <?php echo ($jtot>0)?'<span class="hm-circle">'.$jtot.'</span>':''; ?></a></span><?php } ?></li>
	
	<?php 
	$jparams = array(
		'job_status' => 'Escalate',
		'country_id' => $_SESSION['country_default']
	);
	$jtot = mysql_num_rows($crm->getJobsData($jparams));
	?>
	<li><?php if($user->canView('escalate.php')){ ?><span><a href="<?php echo URL; ?>escalate.php">Escalate <?php echo ($jtot>0)?'<span class="hm-circle">'.$jtot.'</span>':''; ?></a></span><?php } ?></li>
	
	<?php
	$jtot = mysql_num_rows($jc->getJobs('','',$sort,$order_by,'','Merged Certificates','','','','',''));
	?>
	<li><?php if($user->canView('merged_jobs.php')){ ?><span><a href="<?php echo URL; ?>merged_jobs.php">Merged Jobs <?php echo ($jtot>0)?'<span class="hm-circle">'.$jtot.'</span>':''; ?></a></span><?php } ?></li>
	
	<?php
	$jparams = array(
		'dha_need_processing' => 1,
		'join_maintenance_program' => 1,
		'custom_filter' => " AND ( j.`status` = 'Merged Certificates' OR j.`status` = 'Completed' ) ",
		'exclude_tech_other_supplier' => 1,
		'return_count' => 1
	);
	$jtot = $crm->getJobsData($jparams);
	?>
	<li><?php if($user->canView('maintenance_software_pre_com.php')){ ?><span><a href="<?php echo URL; ?>maintenance_software_pre_com.php">MM Pre Com <?php echo ($jtot>0)?'<span class="hm-circle">'.$jtot.'</span>':''; ?></a></span><?php } ?></li>
	
	<?php
	$jtot = mysql_num_rows($jc->getJobs('','',$sort,$order_by,'','On Hold','','','','',''));
	?>
	<li><?php if($user->canView('on_hold_jobs.php')){ ?><span><a href="<?php echo URL; ?>on_hold_jobs.php">On Hold <?php echo ($jtot>0)?'<span class="hm-circle">'.$jtot.'</span>':''; ?></a></span><?php } ?></li>	
	
	<?php
	$custom_filter = " AND ( j.`status` = 'To Be Booked' OR j.`status` = 'Escalate' OR j.`status` = 'Booked' ) ";	
	$jparams = array(
		'out_of_tech_hours' => 1,
		'custom_filter' => $custom_filter,
		'country_id' => $_SESSION['country_default'],
		'return_count' => 1
	);
	$jtot = $crm->getJobsData($jparams);
	?>
	
	<?php
	$pj_sql = getPrecompletedJobs('','');	
	$pj = mysql_num_rows($pj_sql);
	$jtot = mysql_num_rows($jc->getJobs('','',$sort,$order_by,'','Pre Completion','','','','',''));
	?>
	<li><?php if($user->canView('precompleted_jobs.php')){ ?><span><a href="<?php echo URL; ?>precompleted_jobs.php">Pre-Completion <?php echo ($jtot>0)?'<span class="hm-circle">'.$jtot.'</span>':''; ?></a></span><?php } ?></li>
	
	<?php
	$jtot = mysql_num_rows($jc->getJobs('','',$sort,$order_by,'','Send Letters','','','','',''));
	?>
	<li><?php if($user->canView('send_letter_jobs.php')){ ?><span><a href="<?php echo URL; ?>send_letter_jobs.php">Send Letters <?php echo ($jtot>0)?'<span class="hm-circle">'.$jtot.'</span>':''; ?></a></span><?php } ?></li>
	
	<li><?php if($user->canView('service_due_jobs.php')){ ?><span><a href="<?php echo URL; ?>service_due_jobs.php">Service Due Jobs</a></span><?php } ?></li>
	
	<?php
	if( $user_type==2 || $user_type==7 || $user_type==9 ){ ?>
		<li><?php if($user->canView('set_tech_run.php')){ ?><span><a href="<?php echo URL; ?>set_tech_run.php">Set Tech Run</a></span><?php } ?></li> 
	<?php	
	}
	?>
	
	<li><?php if($user->canView('todays_jobs.php')){ ?><span><a href="<?php echo URL; ?>todays_jobs.php">Todays Jobs</a></span><?php } ?></li> 
	<li><?php if($user->canView('to_be_booked_jobs.php')){ ?><span><a href="<?php echo URL; ?>to_be_booked_jobs.php">To Be Booked</a></span><?php } ?></li>
	
	<?php
	$jtot = mysql_num_rows($jc->getJobs('','',$sort,$order_by,'','To Be Invoiced','','','','',''));
	?>
	<li><?php if($user->canView('to_be_invoiced_jobs.php')){ ?><span><a href="<?php echo URL; ?>to_be_invoiced_jobs.php">To Be Invoiced <?php echo ($jtot>0)?'<span class="hm-circle">'.$jtot.'</span>':''; ?></a></span><?php } ?></li>
	
	<li><?php if($user->canView('urgent_jobs.php')){ ?><span><a href="<?php echo URL; ?>urgent_jobs.php">Urgent Jobs</a></span><?php } ?></li>
	
	<?php
	// quick solution for custom query vacant jobs
	$custom_query = " AND j.property_vacant = 1 AND j.status NOT IN('Completed','Cancelled','Merged Certificates','Booked','Pre Completion') ";
	$jtot = mysql_num_rows($jc->getJobs('','',$sort,$order_by,'','','','','','','','','','','','',0,'','','','','','','','','',$custom_query));
	?>
	<li><?php if($user->canView('vacant_jobs.php')){ ?><span><a href="<?php echo URL; ?>vacant_jobs.php">Vacant Jobs <?php echo ($jtot>0)?'<span class="hm-circle">'.$jtot.'</span>':''; ?></a></a></span><?php } ?></li>
	
<?php	
}else if( $menu_type=='Daily Items' ){ ?>

	<?php
	$ageing_sql = getAgeingJobs('','');	
	$ageing_count = mysql_num_rows($ageing_sql);	
	?>
	<li><?php if($user->canView('ageing_jobs.php')){ ?><span><a href="<?php echo URL; ?>ageing_jobs.php">30+ Days old Jobs <?php echo ($ageing_count>0)?'<span class="hm-circle-green">'.$ageing_count.'</span>':''; ?></a></span><?php } ?></li> 
	
	<?php
	$jtot = mysql_num_rows($jc->getJobs('','',$sort,$order_by,'','Action Required','','','','',''));
	?>
	<li><?php if($user->canView('action_required_jobs.php')){ ?><span><a href="<?php echo URL; ?>action_required_jobs.php">Action Required <?php echo ($jtot>0)?'<span class="hm-circle-green">'.$jtot.'</span>':''; ?></a></span><?php } ?></li>
	
	<?php
	if( $user_type != 8 ){ ?>
		<li><?php if($user->canView('agency_keys.php')){ ?><a href="<?php echo URL; ?>agency_keys.php"><span>Agency Keys</span></a><?php } ?></li>
	<?php	
	}
	?>
	
	<?php $pc_dup_num = count($crm->getPostcodeDuplicates()); ?>
	<li><a href="/duplicate_postcode.php">Duplicate Postcode <?php echo ($pc_dup_num>0)?'<span class="hm-circle-green">'.$pc_dup_num.'</span>':''; ?></a></li>
	
	<?php 
	$ptotal_temp = jFindDupProp('','');	
	$ptot = mysql_num_rows($ptotal_temp);	
	?>
	<li><?php if($user->canView('duplicate_properties.php')){ ?><a href="<?php echo URL; ?>duplicate_properties.php"><span>Duplicate Properties <?php echo ($ptot>0)?'<span class="hm-circle-green">'.$ptot.'</span>':''; ?></span></a><?php } ?></li>  
		
	<?php
	$lc = new Last_Contact_Class();
	$lctot = mysql_num_rows($lc->getJobs('',''));
	?>
	<li><?php if($user->canView('last_contact.php')){ ?><span><a href="<?php echo URL; ?>last_contact.php">Last Contact <?php echo ($lctot>0)?'<span class="hm-circle-green">'.$lctot.'</span>':''; ?></a></span><?php } ?></li>
	
	<?php 
	$postcode = getPostCode();
	$mr = mysql_num_rows(getMissingRegionProperty('','',$postcode));
	?>
	<li><?php if($user->canView('missing_region.php')){ ?><span><a href="<?php echo URL; ?>missing_region.php">Missing Region <?php echo ($mr>0)?'<span class="hm-circle-green">'.$mr.'</span>':''; ?></a></span><?php } ?></li>
	
	<?php
	$mj_sql = getMultipleJobs('','');	
	$mj = mysql_num_rows($mj_sql);	
	?>
	<li><?php if($user->canView('multiple_jobs.php')){ ?><span><a href="<?php echo URL; ?>multiple_jobs.php">Multiple Jobs <?php echo ($mj>0)?'<span class="hm-circle-green">'.$mj.'</span>':''; ?></a></span><?php } ?></li>

	<?php
	if( $user_type==2 || $user_type==3 || $user_type==9 ){ 
		$nap = mysql_num_rows(noActiveJobPropperties('',''));
	?>
		<li><?php if($user->canView('no_active_job_properties.php')){ ?><a href="<?php echo URL; ?>no_active_job_properties.php"><span>No Active Job Properties <?php echo ($nap>0)?'<span class="hm-circle-green">'.$nap.'</span>':''; ?></span></a><?php } ?></li>
	<?php	
	}
	?>
	
	<?php 
	$nip = mysql_num_rows(getPropertyNoAgency(getExcludedProperties(),'',''));
	?>
	<li><?php if($user->canView('no_id_properties.php.php')){ ?><a href="<?php echo URL; ?>no_id_properties.php"><span>No ID Properties <?php echo ($nip>0)?'<span class="hm-circle-green">'.$nip.'</span>':''; ?></span></a><?php } ?></li>
	
	<?php 
	$unserv = mysql_num_rows(getUnservicedProperties(getExcludedProperties(),'',''));
	?>
	<li><?php if($user->canView('unserviced.php')){ ?><span><a href="<?php echo URL; ?>unserviced.php">Unserviced Properties <?php echo ($unserv>0)?'<span class="hm-circle-green">'.$unserv.'</span>':''; ?></a></span><?php } ?></li>
	
<?php	
}else if( $menu_type=='Reports' && $user_type!=7 ){ ?>
	
	<li><?php if($user->canView('report_admin.php')){ ?><span><a href="<?php echo URL; ?>report_admin.php">Admin Report</a></span><?php } ?></li>
	<?php
	if( $user_type==7 && $user_type == 8 ){ ?>
		<li><?php if($user->canView('agency_keys.php')){ ?><a href="<?php echo URL; ?>agency_keys.php"><span>Agency Keys</span></a><?php } ?></li>
	<?php	
	}
	?>
	
	<?php
	if( $user_type==2 || $user_type==9 ){ ?> 
		<li><?php if($user->canView('booked.php')){ ?><span><a href="<?php echo URL; ?>booked.php">Booked Report</a></span><?php } ?></li>
	<?php 
	}
	?>
	
	<?
	$vip2 = array(2025,2070);
	if( in_array($staff_id, $vip2) ){ ?>
		<li><?php if($user->canView('call_centre_report.php')){ ?><span><a href="<?php echo URL; ?>call_centre_report.php">Call Centre</a></span><?php } ?></li>
	<?php	
	}
	?>
	
	<?php
	if( $user_type==2 || $user_type==5 || $user_type==9 ){ ?>		
		<li><?php if($user->canView('completed_report.php')){ ?><span><a href="<?php echo URL; ?>completed_report.php">Completed Jobs</a></span><?php } ?></li>
	<?php	
	}
	?>
	
	<li><?php if($user->canView('cron_report.php')){ ?><span><a href="<?php echo URL; ?>cron_report.php">Cron Report</a></span><?php } ?></li>
	
	<li><span><a href="<?php echo URL; ?>daily_figures.php">Daily Figures</a></span></li>
	
	<?php
	if( $user_type==2 || $user_type==5 || $user_type==9 ){ ?>
		<li><?php if($user->canView('expiring.php')){ ?><span><a href="<?php echo URL; ?>expiring.php">Expiring Alarms</a></span><?php } ?></li>
	<?php 
	} 
	?>
	
	<?php
	if( $user_type==2 || $user_type==9 ){ ?>
		<li><?php if($user->canView('figures.php')){ ?><span><a href="<?php echo URL; ?>figures.php">Figures</a></span><?php } ?></li>
	<?php	
	}
	?>
	
	<li><?php if($user->canView('icons.php')){ ?><span><a href="<?php echo URL; ?>icons.php">Icons</a></span><?php } ?></li>
	<li><?php if($user->canView('installed_alarms.php')){ ?><span><a href="<?php echo URL; ?>installed_alarms.php">Installed Alarms</a></span><?php } ?></li>
	
	<li><?php if($user->canView('key_tracking.php')){ ?><span><a href="<?php echo URL; ?>key_tracking.php">Key Tracking Report</a></span><?php } ?></li>
	<li><?php if($user->canView('kpi.php')){ ?><span><a href="<?php echo URL; ?>kpi.php">KPIs</a></span><?php } ?></li>
	
	
	<?php
	if( $user_type==2 || $user_type==3 || $user_type==9 ){ ?>
		<li><?php if($user->canView('missed_jobs.php')){ ?><span><a href="<?php echo URL; ?>missed_jobs.php">Missed Jobs</a></span><?php } ?></li>
	<?php	
	}
	?>
	<li><?php if($user->canView('maintenance_program_agencies.php')){ ?><span><a href="<?php echo URL; ?>maintenance_program_agencies.php">MM Agencies</a></span><?php } ?></li>
	
	<!--- <li><?php if($user->canView('report.php')){ ?><span><a href="<?php echo URL; ?>report.php">My Report</a></span><?php } ?></li> -->
	
	
	<?php
	if( $user_type==2 || $user_type==5 || $user_type==9 ){ ?>
		<li><?php if($user->canView('new_jobs_report.php')){ ?><span><a href="<?php echo URL; ?>new_jobs_report.php">New Jobs Report</a></span><?php } ?></li>
		<li><?php if($user->canView('new_properties_report.php')){ ?><span><a href="<?php echo URL; ?>new_properties_report.php">New Properties Report</a></span><?php } ?></li>	
	<?php	
	}
	?>
	
	<li><span><a href="no_auto_renew_agencies.php">No Auto Renew Agencies</a></span></li>
	
	<li><?php if($user->canView('region_numbers.php')){ ?><span><a href="<?php echo URL; ?>region_numbers.php">Region Numbers</a></span><?php } ?></li>
	
	
	<li><a href="reports.php">Reports</a></li>
	
	<li><?php if($user->canView('report_sales_admin.php')){ ?><span><a href="<?php echo URL; ?>report_sales_admin.php">Sales Report</a></span><?php } ?></li>	
	<?php
	if( $user_type==2 || $user_type==5 || $user_type==9 ){ ?>		
		<li><?php if($user->canView('servicedue.php')){ ?><span><a href="<?php echo URL; ?>servicedue.php">Service Due Report</a></span><?php } ?></li>
		<?php $next_month = date("M",strtotime("+1 month")); ?>
		<li><?php if($user->canView('future_pendings.php')){ ?><span><a href="<?php echo URL; ?>future_pendings.php">Service Due (<?php echo strtoupper($next_month); ?>)</a></span><?php } ?></li>	
		<li><?php if($user->canView('status.php')){ ?><span><a href="<?php echo URL; ?>status.php">Status Report</a></span><?php } ?></li>				
	<?php	
	}
	?>


	<?php
	if( $user_type==2 || $user_type==5 || $user_type==9 ){ ?>
		<li><?php if($user->canView('whiteboard.php')){ ?><span><a href="<?php echo URL; ?>whiteboard.php">Whiteboard</a></span><?php } ?></li>
	<?php	
	}
	?>

	
	
	
<?php	
}else if( $menu_type=='Technicians' ){ ?>

	<?php
	if( $user_type!=8 ){ ?>		
		<li><?php if($user->canView('add_purchase_order.php')){ ?><span><a href="<?php echo URL; ?>add_purchase_order.php">Add Purchase Order</a></span><?php } ?></li>		
	<?php
	}
	?>

	<?php
	if( $user_type!=7 && $user_type != 8 ){ ?>
		<li><?php if($user->canView('contractors.php')){ ?><span><a href="<?php echo URL; ?>contractors.php">Contractors</a></span><?php } ?></li>
	<?php	
	}
	?>
	
	<?php
	if( $user_type==2 || $user_type==5 || $user_type==9 ){ ?>
		<li><?php if($user->canView('view_overall_schedule.php')){ ?><span><a href="<?php echo URL; ?>view_overall_schedule.php">Overall Tech Schedule</a></span><?php } ?></li>
	<?php	
	}
	?>
	
	<?php
	if( $user_type!=8 ){ ?>
		<li><?php if($user->canView('purchase_order.php')){ ?><span><a href="<?php echo URL; ?>purchase_order.php">Purchase Orders</a></span><?php } ?></li>
	<?php
	}
	?>
	
	<?php
	if( $user_type==2 || $user_type==9 ){ ?>
		<li><?php if($user->canView('add_tech_stock.php')){ ?><span><a href="<?php echo URL; ?>add_tech_stock.php">Stock Items</a></span><?php } ?></li>
	<?php	
	}
	?>

	<?php
	if( $user_type==2 || $user_type==5 || $user_type==9 ){ ?>
		<li><?php if($user->canView('tech_doc.php')){ ?><span><a href="<?php echo URL; ?>tech_doc.php">Tech Documents</a></span><?php } ?></li>		
	<?php	
	}
	?>
	
	<?php
	if( $user_type!=8 ){ ?>
		<li><?php if($user->canView('update_tech_stock.php')){ ?><span><a href="<?php echo URL; ?>update_tech_stock.php?id=<?php echo $_SESSION['USER_DETAILS']['StaffID']; ?>">Tech Stocktake</a></span><?php } ?></li>
		<li><?php if($user->canView('tech_stock.php')){ ?><span><a href="<?php echo URL; ?>tech_stock.php">Tech Stock Report</a></span><?php } ?></li>			
	<?php
	}
	?>
	
	<?php
	if( $user_type==2 || $user_type==5 || $user_type==9 ){ ?>		
		<li><?php if($user->canView('tech_doc_tech.php')){ ?><span><a href="<?php echo URL; ?>tech_doc_tech.php">Tech View Documents</a></span><?php } ?></li>
	<?php	
	}
	?>
	
	
		
	<li><?php if($user->canView('view_techs.php')){ ?><span><a href="<?php echo URL; ?>view_techs.php">View Technicians</a></span><?php } ?></li>
	


<?php	
}else if( $menu_type=='Agencies' ){ ?>

	<?php
	if( $user_type != 8 ){ // only show on NON-OS call centre
	?>

		<?php
		if( $user_type!=7 && $user_type != 8 ){ ?> 
			<li><?php if($user->canView('add_agency_static.php')){ ?><span><a href="<?php echo URL; ?>add_agency_static.php">Add Agency</a></span><?php } ?></li>
		<?php 
		}
		?>
		
		<li><?php if($user->canView('view_agencies.php')){ ?><span><a href="<?php echo URL; ?>view_agencies.php">Active Agencies</a></span><?php } ?></li>
	
	<?php
	}
	?>
	
	<li><?php if($user->canView('agency_booking_notes.php')){ ?><span><a href="<?php echo URL; ?>agency_booking_notes.php">Agency Booking Notes</a></span><?php } ?></li>	
	
	<?php
	if( $user_type != 8 ){ // only show on NON-OS call centre
	?>
	
		<li><?php if($user->canView('user_manager.php')){ ?><span><a href="<?php echo URL; ?>user_manager.php">Agency Logins</a></span><?php } ?></li>	
		<?php
		if( $user_type==2 || $user_type==9 ){ ?> 
			<li><?php if($user->canView('agency_portal_data.php')){ ?><span><a href="<?php echo URL; ?>agency_portal_data.php">Agency Portal Data</a></span><?php } ?></li>
		<?php 
		}
		?>
		<li><?php if($user->canView('view_deactivated_agencies.php')){ ?><span><a href="<?php echo URL; ?>view_deactivated_agencies.php">Deactivated Agencies</a></span><?php } ?></li>
		
		<?php 
		if( $user_type!=3 && $user_type!=7 && $user_type != 8 ){ ?> 
			<li><?php if($user->canView('franchise_groups.php')){ ?><span><a href="<?php echo URL; ?>franchise_groups.php">Franchise Groups</a></span><?php } ?></li>
		<?php 
		}
		?>
	
	<?php
	}
	?>
	
<?php	
}else if( $menu_type=='Calendar' ){ ?>

	<li><?php if($user->canView('view_techs.php')){ ?><span><a href="<?php echo URL; ?>add_calendar_entry_static.php">Add Calendar Entry</a></span><?php } ?></li>
	<li><?php if($user->canView('view_individual_staff_calendar.php')){ ?><span><a href="view_individual_staff_calendar.php">My Calendar</a></span><?php } ?></li>
	<li><?php if($user->canView('view_techs.php')){ ?><span><a href="<?php echo URL; ?>view_tech_calendar.php">Staff Calendar</a></span><?php } ?></li>

	<?
	$vip2 = array(2025,2070);
	if( in_array($staff_id, $vip2) ){ ?>
		<li><?php if($user->canView('view_tech_calendar_v2.php')){ ?><span><a href="<?php echo URL; ?>view_tech_calendar_v2.php?class_id=2">Staff Calendar v2</a></span><?php } ?></li>
	<?php	
	}
	?>

	


<?php	
}else if( $menu_type=='Users' ){ ?>

	<li><a href="<?php echo URL; ?>add_sats_user.php">Add Users</a></li>
	<li><a href="<?php echo URL; ?>view_sats_users.php">View Users</a></li>

<?php	
}else if( $menu_type=='Sales' ){ ?>

	<li><a href="/add_prospects.php">ADD Prospects</a></li>
	<li><?php if($user->canView('sales_activity.php')){ ?><span><a href="<?php echo URL; ?>sales_activity.php">Sales Activity</a></span><?php } ?></li>
	<li><?php if($user->canView('sales_documents.php')){ ?><span><a href="<?php echo URL; ?>sales_documents.php">Sales Documents</a></span><?php } ?></li>
	<li><a href="send_email_template.php">Sales Emails</a></li>
	<li><?php if($user->canView('sales_snapshot.php')){ ?><span><a href="<?php echo URL; ?>sales_snapshot.php">Sales Snapshot</a></span><?php } ?></li>
	<li><?php if($user->canView('view_target_agencies.php')){ ?><span><a href="<?php echo URL; ?>view_target_agencies.php">Target Agencies</a></span><?php } ?></li>
	<li><?php if($user->canView('view_all_agencies.php')){ ?><span><a href="<?php echo URL; ?>view_all_agencies.php">View All Agencies</a></span><?php } ?></li>

<?php	
}else if( $menu_type=='Forms' ){ ?>

	<li><?php if($user->canView('expense.php')){ ?><span><a href="<?php echo URL; ?>expense.php">Expense Form</a></span><?php } ?></li>
	<li><?php if($user->canView('incident_and_injury_report.php')){ ?><span><a href="<?php echo URL; ?>incident_and_injury_report.php">Incident Form</a></span><?php } ?></li>

	<?php
	// cara and amy straw
	if( $user_type==2 || $staff_id==2158 || $staff_id==2155 ){ ?>
		<li><?php if($user->canView('incident_and_injury_report_list.php')){ ?><span><a href="<?php echo URL; ?>incident_and_injury_report_list.php">Incident Summary</a></span><?php } ?></li>
	<?php	
	}
	?>

	<li><?php if($user->canView('leave_form.php')){ ?><span><a href="<?php echo URL; ?>leave_form.php">Leave Request Form</a></span><?php } ?></li>

	<?php
	if( $user_type==2 || $staff_id==2155 || $staff_id==2158 || $staff_id==2155 ){ ?>
		<li><?php if($user->canView('leave_requests.php')){ ?><span><a href="<?php echo URL; ?>leave_requests.php">Leave Summary</a></span><?php } ?></li>
	<?php	
	}
	?>

<?php	
}else if( $menu_type=='Admin' ){ ?>

	<li><a href="<?php echo URL; ?>accomodation.php">Accomodation</a></li>
	<li><?php if($user->canView('add_alarm.php')){ ?><span><a href="<?php echo URL; ?>add_alarm.php">Add Alarm</a></span><?php } ?></li>

	<?php
	if( $user_type!=3 ){ ?> 
		<li><a href="<?php echo URL; ?>banners.php">Agency Banners</a></li>
	<?php 
	}
	?>
	<?php
	if( $user_type!=7 && $user_type != 8 ){ ?> 
		<li><?php if($user->canView('noticeboard.php')){ ?><span><a href="<?php echo URL; ?>noticeboard.php">Agency Noticeboard</a></span><?php } ?></li>
	<?php 
	}
	?>

	<li><a href="/agency_portal_special_agencies.php">Agency Portal Special Agencies</a></li>
	<li><a href="<?php echo URL; ?>resources.php">Agent Site Documents</a></li>
	<li><?php if($user->canView('alarm_guide.php')){ ?><span><a href="<?php echo URL; ?>alarm_guide.php">Alarm Guide</a></span><?php } ?></li>
	<li><a href="<?php echo URL; ?>alarm_pricing_page.php">Alarm Pricing Page</a></li>


	<?php
	if( $user_type!=7 && $user_type != 8 ){ ?>
		<li><?php if($user->canView('view_regions.php')){ ?><span><a href="<?php echo URL; ?>view_regions.php">Booking Regions</a></span><?php } ?></li>
		<li><?php if($user->canView('countries.php')){ ?><span><a href="<?php echo URL; ?>countries.php">Countries</a></span><?php } ?></li>
	<?php	
	}
	?>

	<li><a href="<?php echo URL; ?>create_message.php">Create Messages</a></li>
	<li><a href="<?php echo URL; ?>create_renewals.php">Create Renewals</a></li>
	<li><a href="crm_tasks.php"><span>CRM Tasks</span></a></li>
	
	<li><a href="/email_templates.php">Email Templates</a></li>
	<li><?php if($user->canView('admin_doc.php')){ ?><span><a href="<?php echo URL; ?>admin_doc.php">Internal Documents</a></span><?php } ?></li>

	<?php
	if( $user_type==2 || $user_type==5 || $user_type==9 ){ ?> 
		<li><a href="<?php echo URL; ?>agency_site_maintenance_mode.php">Maintenance Mode</a></li>
		<?php
		/* 
			2056 - Robert B. 
			2025 - Daniel K. 
			2070 - Developer T. 
			2124 - Ashley O. 
			2175 - Thalia P.
			2204 - Mark R.
			2206 - Mitchell M.
		*/
		$allowed_ppl = array( 2056, 2025, 2070, 2124, 2175, 2204, 2206 );
		if(in_array($_SESSION['USER_DETAILS']['StaffID'], $allowed_ppl)){ ?>
			<li><a href="<?php echo URL; ?>passwords.php">Passwords</a></li>
		<?php	
		}
	}
	?>

	<?php
	if( in_array($staff_id, $vip2) ){ ?>
		<li><a href="/postcode_map.php">Postcode Map</a></li>
	<?php
	}
	?>

	<li><a href="/search_emails.php">Search Emails</a></li>

	<?php
	if( $user_type!=7 && $user_type != 8 ){ ?> 
		<li><a href="<?php echo URL; ?>suppliers.php">Suppliers</a></li>
	<?php 
	}
	?>

	

<?php	
}else if( $menu_type=='Messages' ){ ?>

	<li><a href="<?php echo URL; ?>create_message.php">Create Messages</a></li>
	<li><?php if($user->canView('incoming_sms.php')){ ?><a href="incoming_sms.php"><span>Incoming SMS</span></a><?php } ?></li>
	<li><?php if($user->canView('job_feedback.php')){ ?><a href="job_feedback.php"><span>Job Feedback</span></a><?php } ?></li>
	<li><?php if($user->canView('outgoing_sms.php')){ ?><a href="outgoing_sms.php"><span>Outgoing SMS</span></a><?php } ?></li>

	<?php
	if( $user_type != 8 ){ ?>
		<li><?php if($user->canView('sms.php')){ ?><span><a href="<?php echo URL; ?>sms.php">Send SMS</a></span><?php } ?></li>
	<?php
	}
	?>
	
	<?php
	if( $user_type==2 || $user_type==5 || $user_type==9 ){ ?> 
		<li><?php if($user->canView('sms_messages.php')){ ?><span><a href="<?php echo URL; ?>sms_messages.php">SMS Templates</a></span><?php } ?></li>
	<?php 
	}
	?>
	
	<li><a href="<?php echo URL; ?>messages.php">View Messages</a></li>

<?php	
}else if( $menu_type=='Vehicles/Tools' ){ ?>

<li><?php if($user->canView('add_tools.php')){ ?><span><a href="<?php echo URL; ?>add_tools.php">Add Tools</a></span><?php } ?></li>
<li><?php if($user->canView('add_vehicle.php')){ ?><span><a href="<?php echo URL; ?>add_vehicle.php">Add Vehicle</a></span><?php } ?></li>
<li><?php if($user->canView('add_vehicle.php')){ ?><span><a href="<?php echo URL; ?>kms.php">KMS Report</a></span><?php } ?></li>
<li><?php if($user->canView('view_tools.php')){ ?><span><a href="<?php echo URL; ?>view_tools.php">View Tools</a></span><?php } ?></li>
<li><?php if($user->canView('view_vehicles.php')){ ?><span><a href="<?php echo URL; ?>view_vehicles.php">View Vehicles</a></span><?php } ?></li>

<?php	
}else if( $menu_type=='Accounts' ){ ?>

<li><?php if($user->canView('create_credit_request.php')){ ?><a href="create_credit_request.php"><span>Create Credit Request</span></a><?php } ?></li>
<li><?php if($user->canView('credit_requests.php')){ ?><a href="credit_requests.php"><span>Credit Request Summary</span></a><?php } ?></li>

<li><?php if($user->canView('debtors_report.php')){ ?><a href="debtors_report.php"><span>Debtors Report</span></a><?php } ?></li>

<?php
if( $user_type!=8 ){ ?>
	<li><?php if($user->canView('expense_summary.php')){ ?><span><a href="<?php echo URL; ?>expense_summary.php">Expense Claims Summary</a></span><?php } ?></li>
<?php	
}
?>

<?php
if( $user_type==2 || $user_type==3 || $user_type==9 ){ ?> 
	<li><?php if($user->canView('nlm_properties.php')){ ?><a href="nlm_properties.php"><span>NLM Properties</span></a><?php } ?></li>
<?php 
}
?>

<li><?php if($user->canView('printing_tracker.php')){ ?><a href="printing_tracker.php"><span>Printer Tracker</span></a><?php } ?></li>
<li><?php if($user->canView('remittance.php')){ ?><a href="remittance.php"><span>Remittance</span></a><?php } ?></li>
<li><?php if($user->canView('statements.php')){ ?><a href="statements.php"><span>Statements</span></a><?php } ?></li>
<li><?php if($user->canView('to_be_printed.php')){ ?><a href="to_be_printed.php"><span>To Be Printed</span></a><?php } ?></li>


<?php	
}else if( $menu_type=='API' ){ ?>

	<li><a href="pm_agencies.php">PM Agencies</a></li>
	<li><a href="search_address.php">Search Address</a></li>


<?php	
}else if( $menu_type=='Test' ){ ?>

	<!--<li><a href="test_menu.php">Menu</a></li>-->
	<!--<li><a href="page_permission_manager.php">Page Permission Manager</a></li>-->
	<li><a href="menu_manager.php">Menu Manager</a></li>
	<!--<li><a href="test_visibility.php">Test Visibility</a></li>-->
	<li><a href="menu_v2.php">Menu V2</a></li>

<?php	
}