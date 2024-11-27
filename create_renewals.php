<?php

$title = "Create Renewals";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$tools_id = $_GET['id'];

$params = array('tools_id'=>$tools_id);
$tools_sql = $crm->getTools($params);
$t = mysql_fetch_array($tools_sql);

?>
<style>
.addproperty input, .addproperty select {
    width: 30%;
}
.addproperty label {
   width: 230px;
}
table#tbl_ladder td {
    border: 1px solid #cccccc;
}
.success{
	 margin-bottom: 17px;
}
</style>
    
    <div id="mainContent">
      
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="create_renewals.php"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['update']==1){ ?>
		<div class="success">Update Successful</div>
	<?php
	}
	?>
	
	<?php
	if($_GET['ladder_success']==1){ ?>
		<div class="success">New ladder Check Added</div>
	<?php
	}
	?>
	
	<?php
	if($_GET['tnt_success']==1){ ?>
		<div class="success">Tag and Test Added</div>
	<?php
	}
	?>
      	
		
	<!--	
	<div>
		<div class="alert-holder">
			<p>Click <a href="javascript:showConfirm()">Here</a> on the 15th of <?php echo date('F');?> to generate <?php echo date('F',strtotime("+1 month"));?> Renewals.</p>
		</div>	
	</div>
	-->
	
	
	<div>
		<table style="width:auto; margin: 0;" id="tbl_ladder" class="tbl-sd">
			<tr class="toprow">
				<th>Date</th>
				<th>Jobs Created</th>
				<th>By Who</th>
			</tr>
			<?php
			$r_sql = mysql_query("
				SELECT *, r.`StaffID` AS r_staff_id
				FROM `renewals` AS r
				LEFT JOIN `staff_accounts` AS sa ON r.`StaffID` = sa.`StaffID`
				WHERE `country_id` = {$_SESSION['country_default']}
				ORDER BY r.`date` DESC
				LIMIT 10
			");
			while( $r = mysql_fetch_array($r_sql) ){ ?>
				<tr class="body_tr">
					<td><?php echo date("d/m/Y",strtotime($r['date'])); ?></td>
					<td><?php echo $r['num_jobs_created']; ?></td>
					<td>
						<?php
						if( $r['r_staff_id'] == -1 ){
							echo 'System';
						}else{
							echo "{$r['FirstName']} {$r['LastName']}";
						}
						?>
					</td>
				</tr>
			<?php
			}
			?>			
		</table>	
	</div>
	
    
  </div>

<br class="clearfloat" />
<script>
function showConfirm(){
	
	var answer = confirm("Create pending jobs for next month?");
	if(answer){
		//document.getElementById('busy').innerHTML = "Processing data, please wait ...";
		location.href = "find_pending.php";
	}
	
}
</script>
</body>
</html>
