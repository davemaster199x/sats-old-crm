<?php

$title = "Agency Portal Special Agencies";

include('inc/init.php'); 
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$logged_user = $_SESSION['USER_DETAILS']['StaffID'];

?>



    <div id="mainContent">
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
      
    
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['update']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Update Successful</div>
	<?php
	}
	?>
      	
	<?php
	$sql = mysql_query("
		SELECT *
		FROM `crm_settings`
		WHERE `country_id` = {$_SESSION['country_default']}
	");
	$crm_set = mysql_fetch_array($sql);
	?>
	
	
		<div style="margin: 40px 0 0;text-align: left;">
			<h2 class="heading">IMPORTANT</h2>
			<ul>
				<li>Any Agencies that appear on this page will see an additional page on the Agency Portal that displays all active jobs.</li>						
			</ul>
		</div>
	
	<div class="addproperty" style="width: 100%;">	
		
		
		
		<table id="table1" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-bottom: 20px;">
			<thead>
				<tr class="toprow jalign_left">
					<th>Agency ID</th>
					<th>Agency Name</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$sql = mysql_query("
					SELECT *
					FROM `agency`
					WHERE `agency_id` IN({$crm_set['agency_portal_vip_agencies']})
					ORDER BY `agency_name` ASC
				");
				while( $row = mysql_fetch_array($sql) ){ ?>
					<tr>
						<td><?php echo $row['agency_id']; ?></td>
						<td>
							<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
							<a href="<?php echo $ci_link; ?>"><?php echo $row['agency_name']; ?></a>
						</td>
					</tr>
				<?php
				}
				?>
				
			</tbody>
		</table>
		
		<form method="POST" action="update_agency_portal_special_agencies.php">
			<div class="row">
				<label class="addlabel">Agency ID's:</label>
				<input type="text" style="width: 400px; float: left; margin-right: 20px;" class="addinput agency_ids" name="agency_ids" id="agency_ids" value="<?php echo $crm_set['agency_portal_vip_agencies']; ?>" />
				<button class="submitbtnImg blue-btn" id="btn_update" type="submit" style="float: left;">Update</button>
			</div>
		</form>
		
	</div>
    
  </div>

<br class="clearfloat" />



<script>
jQuery(document).ready(function(){
	
});
</script>
</body>
</html>
