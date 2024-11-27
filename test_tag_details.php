<?php

$title = "Test and Tag Details";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$test_and_tag_id = mysql_real_escape_string($_GET['id']);
$tools_id = mysql_real_escape_string($_GET['tools_id']);

$params = array('test_and_tag_id'=>$test_and_tag_id);
$tnt_sql = $crm->getTestAndTag($params);
$tnt = mysql_fetch_array($tnt_sql);

?>
<style>
.addproperty label {
   width: 211px;
}
table#tbl_ladder td {
    border: 1px solid #cccccc;
}
table#tbl_ladder {
    text-align: left;
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
        <li class="other first"><a title="<?php echo $title; ?>" href="/test_tag_details.php?id=<?php echo $test_and_tag_id; ?>&tools_id=<?php echo $tools_id; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['update']==1){ ?>
		<div class="success" style="margin-bottom: 15px;">Update Successful</div>
	<?php
	}
	?>
      	
	<form action="test_tag_update.php" method="post" id="jform" style="font-size: 14px;">
	<div class="addproperty">		
		<div class="row">
			<label class="addlabel">Date</label>
			<input type="text"  class="addinput datepicker" name="date" id="date" value="<?php echo date('d/m/Y',strtotime($tnt['date'])); ?>" />
		</div>
		<div class="row" style="text-align: left;">
			<label class="addlabel">Test and tag completed</label>
			<input type="radio" name="tnt_comp" value="1" style="width:auto; display:inline;" <?php echo ($tnt['tnt_completed']==1)?'checked="checked"':''; ?> /> Yes
			<input type="radio" name="tnt_comp" value="0" style="width:auto; display:inline;" <?php echo ( is_numeric($tnt['tnt_completed']) && $tnt['tnt_completed']==0 )?'checked="checked"':''; ?> /> No
		</div>
		<div class="row" style="text-align: left;">
			<label class="addlabel">Comment</label>
			<textarea class="addtextarea" style="width: 328px; height: 150px; margin:0; padding: 7px;" name="comment"><?php echo $tnt['comment']; ?></textarea>
		</div>
		<div class="row">
			<label class="addlabel" style="color:red">Next Inspection Due</label>
			<input type="text"  class="addinput" name="inspection_due" id="inspection_due" value="<?php echo ($tnt['inspection_due']!='')?date('d/m/Y',strtotime($tnt['inspection_due'])):''; ?>" readonly="readonly" />
		</div>
		<div class="row" style="margin-top: 17px;">
			<input type="hidden" name="test_and_tag_id" value="<?php echo $test_and_tag_id; ?>" />
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
