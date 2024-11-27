<?php

$title = "Maintenance Mode";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

include('inc/servicedue_functions.php');


?>


<?php
	  if($_SESSION['USER_DETAILS']['ClassID']==6){ ?>  
		<div style="clear:both;"></div>
	  <?php
	  }  
	  ?>


<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
		<div class="sats-breadcrumb">
			  <ul>
				<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
				<li class="other first"><a title="Maintenance Mode" href="/agency_site_maintenance_mode.php"><strong>Maintenance Mode</strong></a></li>
			  </ul>
		</div>
		  
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		$m_sql = mysql_query("
			SELECT `mode`
			FROM `agency_site_maintenance_mode`
		");
		$m = mysql_fetch_array($m_sql);
		
		if($_GET['success']==1){
			echo '<div class="success">Maintenance Mode Updated</div>';
		}
		
		
		?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		
		<?php
		if($m['mode']==1){ ?>
			<p><img src="/images/under-maintenance.png" /></p>
		<?php	
		}else{ ?>
			<p>This page allows you to block access to the Agency Portal whilst doing maintenance.</p>
		<?php	
		}
		?>
		<label>Maintenance Mode: </label> <a href="javascript:void(0);" id="m_mode_switch"><?php echo ($m['mode']==1)?'ON':'OFF'; ?></a>
		<input type="hidden" name="mode" id="mode" value="<?php echo $m['mode']; ?>" />
		<br />
		
	</div>
</div>

<br class="clearfloat" />

<script>
jQuery(document).ready(function(){
	jQuery("#m_mode_switch").click(function(){
		var mode = jQuery("#mode").val();
		if(confirm("Are you sure you want to proceed?")){
			jQuery.ajax({
				type: "POST",
				url: "ajax_switch_agency_site_maintenance_mode.php",
				data: { 
					mode: mode
				}
			}).done(function( ret ) {
				window.location="/agency_site_maintenance_mode.php?success=1";
			});	
		}						
	});
});
</script>

</body>
</html>