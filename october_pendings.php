<?

$this_month = date("F");

$title = $this_month;

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

include('inc/october_pendings_functions.php');
					
// pagination script
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;
$this_page = $_SERVER['PHP_SELF'];
$next_link = "{$this_page}?offset=".($offset+$limit);
$prev_link = "{$this_page}?offset=".($offset-$limit);
					
$u_sql = getOctPendings($offset,$limit);
$ptotal = getOctPendings('','');



?> 

<div id="mainContent">
  
  <div class="sats-middle-cont">
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $this_month; ?> " href="/october_pendings.php"><strong><?php echo $this_month; ?> Pendings</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
	
	<?php
	if($_GET['success']==1){
		echo '<div class="success">Import Successful</div>';
	}
	?>

	<!--
	<div class="aviw_drop-h" style="border: 1px solid #ccc;">		 
		<div class="fl-left">
			<a href="export_unserviced.php"><button type="button" class="submitbtnImg">Export</button></a>
		</div>	
	</div>
	-->
   
   
   <table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px; text-align: left;" id="tbl_sms_msg">
				<tr class="toprow jalign_left">
					<th>Property ID</th>
					<th>Address</th>
					<th>Agency</th>
				</tr>
					<?php									
									
					
					if(mysql_num_rows($u_sql)>0){
						$i = 0;
						while($u = mysql_fetch_array($u_sql)){
					?>
							<tr class="body_tr jalign_left">
								<td>
									<span class="txt_lbl">
										<?php echo $u['property_id']; ?>
									</span>
								</td>
								<td>
									<span class="txt_lbl">
										<a href="/view_property_details.php?id=<?php echo $u['property_id']; ?>">
											<?php echo "{$u['p_address1']} {$u['p_address2']} {$u['p_address3']} {$u['p_state']} {$u['p_postcode']}"; ?>
										</a>
									</span>
								</td>
								<td style="border-right: 1px solid #ccc;">
									<span class="txt_lbl">
										<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$u['agency_id']}"); ?>
										<a href="<?php echo $ci_link; ?>">
											<?php echo $u['agency_name']; ?>
										</a>
									</span>
								</td>
							<tr>
					<?php
						$i++;
						}
					}else{ ?>
						<td colspan="3" align="left">Empty</td>
					<?php
					}
					?>
					 <tr>
					<td colspan='10' class="padding-none">
					 <div class="sats-pg-navigation">
						<div class="sats-inner-pagination">
							<div class="sats-inner-pagination">
							<?php
								if($offset!=0&&$offset!=""){ ?>
								<a href="<?php echo $prev_link; ?>" class="left">&lt;</a>
							<?php
								}
							?>			
							 <div class="sats-pagination-view">Viewing <?php echo (mysql_num_rows($u_sql)>0)?$offset+1:"0"; ?> to <?php echo ($offset+mysql_num_rows($u_sql)); ?> of <?php echo mysql_num_rows($ptotal); ?></div> 
							<?php
								if(($offset+mysql_num_rows($u_sql)) < mysql_num_rows($ptotal)){ ?>
								<a href="<?php echo $next_link; ?>" class="right">&gt;</a>
							<?php
								}
							?>
							</div>
						</div>
					</div>
					</td>
				  </tr>
			</table>

  </div>
  
</div>

<br class="clearfloat" />

  
</body>
</html>
