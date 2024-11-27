<?php

$title = "Lockout Kit Check";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$tools_id = mysql_real_escape_string($_GET['id']);

?>
<style>
table#tbl_lockout_kit td {
    border: 1px solid #cccccc;
}
table#tbl_lockout_kit {
    text-align: left;
}

.addproperty label {
    width: 150px;
}
.addinput{
	width: 88px!important;
}
.jradio{
	width:auto !important;
}
</style>
    
    <div id="mainContent">
      
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="View Tools" href="/view_tools.php">View Tools</a></li>
		<li class="other first"><a title="Tool Details" href="/view_tool_details.php?id=<?php echo $tools_id; ?>">Tool Details</a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="/lockout_kit_check.php?id=<?php echo $tools_id; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success">New tools added</div>
	<?php
	}
	?>
      	
	<form action="lockout_kit_check_script.php" method="post" id="jform" style="font-size: 14px;">
	<div class="addproperty">		
		<div class="row">
			<label class="addlabel">Date</label>
			<input type="text"  class="addinput datepicker" name="date" id="date" value="<?php echo date('d/m/Y'); ?>" />
		</div>
		<div class="row">
			<table style="width:auto;" id="tbl_lockout_kit">
				<tr>
					<td>Lockout Kit Checklist</td>
					<td>Yes</td>
					<td>No</td>
				</tr>
				<?php
				$li_sql = $crm->getLockOutKitCheckList($params);
				while( $li = mysql_fetch_array($li_sql) ){ ?>
					<tr>
						<td>
							<?php echo $li['item']; ?>
							<input type="hidden" name="lockout_kit_checklist[]" value="<?php echo $li['lockout_kit_checklist_id']; ?>" />
						</td>
						<td><input type="radio" class="jradio" name="lockout_kit_opt<?php echo $li['lockout_kit_checklist_id']; ?>" value="1" /></td>
						<td><input type="radio" class="jradio" name="lockout_kit_opt<?php echo $li['lockout_kit_checklist_id']; ?>" value="0" /></td>
					</tr>
				<?php	
				}
				?>				
			</table>
		</div>
		<div class="row">
			<label class="addlabel" style="color:red">Next checklist Due</label>
			<input type="text"  class="addinput" name="checklist_due" id="checklist_due" value="<?php echo date('d/m/Y',strtotime("+ 6 months")); ?>" readonly="readonly" />
		</div>
		<div class="row" style="margin-top: 17px;">
			<input type="hidden" name="tools_id" value="<?php echo $tools_id; ?>" />
        	<input type="submit" class="submitbtnImg" id="btn_submit" style="float: left; width: auto;" value="Submit" />
        </div>
	</div>
	</form>


    
  </div>

<br class="clearfloat" />


<script>




jQuery(document).ready(function(){
	
	
	// checklist due script
	jQuery("#date").change(function(){
		
		var date = formatToDateToYmd(jQuery(this).val());
		var insp_due = addMonth(new Date(date), 6);
		var insp_due2 = formatDate(insp_due);
		jQuery("#checklist_due").val(insp_due2);
		
	});
	

	jQuery("#jform").submit(function(){
	
		var date = jQuery("#date").val();
		var error = "";
		
		if(date==""){
			error += "Date is required\n";
		}
		
		
		if(error!=""){
			alert(error);
			return false;
		}else{
			return true;
		}
		
	});

	
	
});
</script>

</body>
</html>