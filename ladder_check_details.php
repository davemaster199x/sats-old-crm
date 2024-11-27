<?php

$title = "Ladder Check Details";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$ladder_check_id = mysql_real_escape_string($_GET['id']);
$tools_id = mysql_real_escape_string($_GET['tools_id']);

$params = array('ladder_check_id'=>$ladder_check_id);
$lc_sql = $crm->getLadderCheck($params);
$lc = mysql_fetch_array($lc_sql);

?>
<style>
table#tbl_ladder td {
    border: 1px solid #cccccc;
}
table#tbl_ladder {
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
        <li class="other first"><a title="<?php echo $title; ?>" href="/ladder_check_details.php?id=<?php echo $ladder_check_id; ?>&tools_id=<?php echo $tools_id; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['update']==1){ ?>
		<div class="success" style="margin-bottom: 15px;">Update Successful</div>
	<?php
	}
	?>
      	
	<form action="ladder_check_update.php" method="post" id="jform" style="font-size: 14px;">
	<div class="addproperty">		
		<div class="row">
			<label class="addlabel">Date</label>
			<input type="text"  class="addinput datepicker" name="date" id="date" value="<?php echo date('d/m/Y',strtotime($lc['date'])); ?>" />
		</div>
		<div class="row">
			<table style="width:auto;" id="tbl_ladder">
				<tr>
					<td>Ladder Inspection</td>
					<td>Yes</td>
					<td>No</td>
				</tr>
				<?php
				$li_sql = $crm->getLadderInspection($params);
				while( $li = mysql_fetch_array($li_sql) ){ 
				
				// selection
				$params = array(
					'ladder_check_id'=>$ladder_check_id,
					'ladder_inspection_id'=>$li['ladder_inspection_id']
				);
				$lis_sql = $crm->ladderInspectionSelection($params);
				$lis = mysql_fetch_array($lis_sql);

				?>
					<tr>
						<td>
							<?php echo $li['item']; ?>
							<input type="hidden" name="ladder_inspection[]" value="<?php echo $li['ladder_inspection_id']; ?>" />
						</td>
						<td><input type="radio" class="jradio" name="ladder_opt<?php echo $li['ladder_inspection_id']; ?>" value="1" <?php echo ($lis['value']==1)?'checked="checked"':''; ?> /></td>
						<td><input type="radio" class="jradio" name="ladder_opt<?php echo $li['ladder_inspection_id']; ?>" value="0" <?php echo ( is_numeric($lis['value']) && $lis['value']==0 )?'checked="checked"':''; ?> /></td>
					</tr>
				<?php	
				}
				?>				
			</table>
		</div>
		<div class="row">
			<label class="addlabel" style="color:red">Next Inspection Due</label>
			<input type="text"  class="addinput" name="inspection_due" id="inspection_due" value="<?php echo date('d/m/Y',strtotime($lc['inspection_due'])); ?>" readonly="readonly" />
		</div>
		<div class="row" style="margin-top: 17px;">
			<input type="hidden" name="ladder_check_id" value="<?php echo $ladder_check_id; ?>" />
			<input type="hidden" name="tools_id" value="<?php echo $tools_id; ?>" />
        	<input type="submit" class="submitbtnImg" id="btn_submit" style="float: left; width: auto;" value="Update" />
        </div>
	</div>
	</form>


    
  </div>

<br class="clearfloat" />


<script>




jQuery(document).ready(function(){
	
	
	// inspection due script
	jQuery("#date").change(function(){
		
		var date = formatToDateToYmd(jQuery(this).val());
		var insp_due = addMonth(new Date(date), 6);
		var insp_due2 = formatDate(insp_due);
		jQuery("#inspection_due").val(insp_due2);
		
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