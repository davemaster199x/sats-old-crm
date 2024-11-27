<style>
.bc_bold{
	font-weight: bold;
}
</style>
<div class="sats-breadcrumb">

<!--
	<ul>
		<li class="tech-breadcrumb"><a href="<?=URL;?>main.php?logout=1"><span>LOGOUT</span></a></li>
		<li class="other first"><a href="<?php echo $crm->crm_ci_redirect('/resources'); ?>" title="Resources" <?php echo (basename($_SERVER['PHP_SELF'])=="tech_doc_tech.php")?'class="bc_bold"':''; ?>>Resources</a></li>
		<li class="other first"><a href="<?php echo $crm->crm_ci_redirect('/calendar/my_calendar'); ?>" title="My Calendar" <?php echo (basename($_SERVER['PHP_SELF'])=="view_individual_staff_calendar.php")?'class="bc_bold"':''; ?>>My Calendar</a></li>
		<li class="other second"><a title="Monthly Schedule" href="<?php echo $crm->crm_ci_redirect('/calendar/monthly_schedule/'.$_SESSION['USER_DETAILS']['StaffID']); ?>" <?php echo (basename($_SERVER['PHP_SELF'])=="view_tech_schedule.php")?'class="bc_bold"':''; ?>>Monthly Schedule</a></li>
		<li class="other second"><a title="Run Sheet" href="<?php echo $crm->crm_ci_redirect('/tech_run/run_sheet/'.$_SESSION['USER_DETAILS']['tr_id']); ?>" <?php echo (basename($_SERVER['PHP_SELF'])=="tech_day_schedule.php")?'class="bc_bold"':''; ?>>Run Sheet</a></li>
		<li class="other second"><a title="Messages" href="<?php echo $crm->crm_ci_redirect('/messages'); ?>" <?php echo (basename($_SERVER['PHP_SELF'])=="messages.php")?'class="bc_bold"':''; ?>>Messages</a></li>
		<li class="other second"><a title="Home" href="<?php echo $crm->crm_ci_redirect('/home/index'); ?>">Home</a></li>
		
		<?php
		// message details
		if(basename($_SERVER['PHP_SELF'])=="message_details.php"){ ?>
		
			<li class="other second"><a title="Run Sheet" href="message_details.php?id=<?php echo $_GET['id']; ?>" <?php echo (basename($_SERVER['PHP_SELF'])=="message_details.php")?'class="bc_bold"':''; ?>>Message Details</a></li>
		
		<?php	
		}
		?>
		
		<?php
		// create message
		if(basename($_SERVER['PHP_SELF'])=="create_message.php"){ ?>
		
			<li class="other second"><a title="Run Sheet" href="create_message.php" <?php echo (basename($_SERVER['PHP_SELF'])=="create_message.php")?'class="bc_bold"':''; ?>>Create Message</a></li>
		
		<?php	
		}
		?>
		

		<?php
		/*
		if( strpos(URL,"dev") != false ){ 
		
			$crm_ci_page = '/home/index';
			$page_url = $crm->crm_ci_redirect($crm_ci_page);
		
		?>

			<li class="other second">
				<a title="Run Sheet" href="<?php echo $page_url; ?>">
					CRM CI
				</a>
			</li>

		<?php
		}
		*/
		?>
		
	</ul>
-->


<ul>
	<li class="tech-breadcrumb"><a href="<?php echo $crm->crm_ci_redirect('/home/index'); ?>"><span>Home</span></a></li>
	<li class="other second"><a title="Run Sheet" href="<?php echo $crm->crm_ci_redirect('/tech_run/run_sheet/'.$_SESSION['USER_DETAILS']['tr_id']); ?>" <?php echo (basename($_SERVER['PHP_SELF'])=="tech_day_schedule.php")?'class="bc_bold"':''; ?>>Run Sheet</a></li>	
</ul>

</div>