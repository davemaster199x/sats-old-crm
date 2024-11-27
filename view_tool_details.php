<?php

$title = "Tool Details";

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
		<li class="other first"><a title="View Tools" href="/view_tools.php">View Tools</a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="view_tool_details.php?id=<?php echo $tools_id; ?>"><strong><?php echo $title; ?></strong></a></li>
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
      	
	<form action="update_tool.php" method="post" id="jform" style="font-size: 14px;">
	<div class="addproperty">		
		<div class="row">
			<label class="addlabel">Item</label>
			<input type="text" class="addinput" name="item_id" id="item_id" value="<?php echo $t['item_name']; ?>" readonly="readonly" />
		</div>
		<div class="row">
			<label class="addlabel">Item ID</label>
			<input type="text" class="addinput" name="item_id" id="item_id" value="<?php echo $t['item_id']; ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Brand</label>
			<?php
			// if ladder
			if( $t['item']==1 ){ ?>
				<select name="brand" class="brand">
					<option value="">----</option>				
					<option value="Gorilla" <?php echo ($t['brand']=='Gorilla')?'selected="selected"':''; ?>>Gorilla</option>
					<option value="Rhino" <?php echo ($t['brand']=='Rhino')?'selected="selected"':''; ?>>Rhino</option>
					<option value="werner" <?php echo ($t['brand']=='werner')?'selected="selected"':''; ?>>werner</option>
				</select>				
			<?php	
			}else{ ?>
				<input type="text" class="addinput" name="brand" id="brand"  value="<?php echo $t['brand']; ?>" />
			<?php	
			}
			?>
			
			
		</div>	
		<div class="row">
			<label class="addlabel">Description</label>
			<?php
			// if ladder
			if( $t['item']==1 ){ ?>
				<select name="description" class="description">
					<option value="">----</option>				
					<option value="3FT Single sided ladder" <?php echo ($t['description']=='3FT Single sided ladder')?'selected="selected"':''; ?>>3FT Single sided ladder</option>
					<option value="3FT Double sided ladder" <?php echo ($t['description']=='3FT Double sided ladder')?'selected="selected"':''; ?>>3FT Double sided ladder</option>
					<option value="4FT Single sided ladder" <?php echo ($t['description']=='4FT Single sided ladder')?'selected="selected"':''; ?>>4FT Single sided ladder</option>
					<option value="4FT Double sided ladder" <?php echo ($t['description']=='4FT Double sided ladder')?'selected="selected"':''; ?>>4FT Double sided ladder</option>
					<option value="6FT Single sided ladder" <?php echo ($t['description']=='6FT Single sided ladder')?'selected="selected"':''; ?>>6FT Single sided ladder</option>				
					<option value="6FT Double sided ladder" <?php echo ($t['description']=='6FT Double sided ladder')?'selected="selected"':''; ?>>6FT Double sided ladder</option>
					<option value="8FT Single sided ladder" <?php echo ($t['description']=='8FT Single sided ladder')?'selected="selected"':''; ?>>8FT Single sided ladder</option>
					<option value="8FT Double sided ladder" <?php echo ($t['description']=='8FT Double sided ladder')?'selected="selected"':''; ?>>8FT Double sided ladder</option>
					<option value="10FT Single sided ladde" <?php echo ($t['description']=='10FT Single sided ladder')?'selected="selected"':''; ?>>10FT Single sided ladder</option>
					<option value="10FT Double sided ladder" <?php echo ($t['description']=='10FT Double sided ladder')?'selected="selected"':''; ?>>10FT Double sided ladder</option>
				</select>
			<?php	
			}else{ ?>
				<input type="text" class="addinput" name="description" id="description"  value="<?php echo $t['description']; ?>" />
			<?php	
			}
			?>			
		</div>
		<div class="row">
			<label class="addlabel">Purchase Date</label>
			<input type="text" class="addinput datepicker" name="purchase_date" id="purchase_date" value="<?php echo ($t['t_purchase_date']!="")?date('d/m/Y',strtotime($t['t_purchase_date'])):''; ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Purchase Price</label>
			<input type="text" class="addinput" name="purchase_price" id="purchase_price" value="<?php echo $t['t_purchase_price']; ?>" />
		</div>
		
		<div class="row">
			<label class="addlabel">Assign to Vehicle</label>
			<select name="assign_to_vehicle" id="assign_to_vehicle">
				<option value="">----</option>
				<?php
				// get Users
				$jparams = array(
					'country_id' => $_SESSION['country_default'],
					'tech_vehicle' => 1,
					'sort_list' => array(
						'order_by' => 'v.`number_plate`',
						'sort' => 'ASC'
					)
				);
				$v_sql = $crm->getVehicles($jparams);
				while( $v = mysql_fetch_array($v_sql) ){ ?>
					<option value="<?php echo $v['vehicles_id']; ?>" <?php echo ( $v['vehicles_id'] == $t['assign_to_vehicle'] )?'selected="selected"':''; ?>><?php echo $v['number_plate']; ?> - <?php echo $crm->formatStaffName($v['FirstName'],$v['LastName']); ?></option>
				<?php	
				}
				?>
			</select>
		</div>
		
		<div class="row">
			<input type="hidden" name="tools_id" value="<?php echo $tools_id; ?>" />
        	<input class="submitbtnImg" id="btn_update" type="submit" style="float: left; width: auto;" value="Update">
			<?php 
			if($t['item']==1){ ?>
				<a href="/ladder_check.php?id=<?php echo $tools_id; ?>">
					<button class="submitbtnImg" id="btn_add_ladder_check" type="button" style="float: left; margin-left: 8px;">Add Ladder Check</button>
				</a>
			<?php	
			}
			?>	
			<?php 
			if($t['item']==2){ ?>
				<a href="/test_tag.php?id=<?php echo $tools_id; ?>">
					<button class="submitbtnImg" id="btn_add_test_tag" type="button" style="float: left; margin-left: 8px;">Add Test and Tag</button>
				</a>
			<?php	
			}
			?>
			<?php 
			if($t['item']==4){ ?>
				<a href="/lockout_kit_check.php?id=<?php echo $tools_id; ?>">
					<button class="submitbtnImg" id="btn_lockout_kit" type="button" style="float: left; margin-left: 8px;">Add Lockout Check</button>
				</a>
			<?php	
			}
			?>
        </div>
	</div>
	</form>

	<?php
	// ladder check
	if($t['item']==1){ 
	$params = array(
		'tools_id' => $tools_id,
		'sort_list' => array(
			'order_by' => 'date',
			'sort' => 'DESC'
		)
	);
	$lc_sql = $crm->getLadderCheck($params);
	if( mysql_num_rows($lc_sql)>0 ){
	?>
		<div style="text-align: left;">
			<h2 class="heading">Ladder Check</h2>
			<table style="width:auto; margin: 0;" id="tbl_ladder" class="tbl-sd">
			<tr class="toprow">
				<th>Date</th>
				<th>Next Inspection Due</th>
			</tr>
			<?php			
			while( $lc = mysql_fetch_array($lc_sql) ){ ?>
				<tr class="body_tr">
					<td><a href="/ladder_check_details.php?id=<?php echo $lc['ladder_check_id']; ?>&tools_id=<?php echo $tools_id; ?>"><?php echo date('d/m/Y',strtotime($lc['date'])); ?></a></td>
					<td><?php echo ($lc['inspection_due']!='')?date('d/m/Y',strtotime($lc['inspection_due'])):''; ?></td>
				</tr>
			<?php	
			}
			?>
			</table>
		</div>
	<?php	
		}
	}
	?>
	
	<?php
	// test and tag
	if($t['item']==2){ 
		$params = array(
		'tools_id' => $tools_id,
		'sort_list' => array(
			'order_by' => 'date',
			'sort' => 'DESC'
		)
	);
	$tnt_sql = $crm->getTestAndTag($params);
	if( mysql_num_rows($tnt_sql)>0 ){
	?>
		<div style="text-align: left;">
			<h2 class="heading">Test & Tag</h2>
			<table style="width:auto; margin: 0;" id="tbl_ladder" class="tbl-sd">
			<tr class="toprow">
				<th>Date</th>
				<th>Next Inspection Due</th>
			</tr>
			<?php
			
			while( $tnt = mysql_fetch_array($tnt_sql) ){ ?>
				<tr class="body_tr">
					<td><a href="/test_tag_details.php?id=<?php echo $tnt['test_and_tag_id']; ?>&tools_id=<?php echo $tools_id; ?>"><?php echo date('d/m/Y',strtotime($tnt['date'])); ?></a></td>
					<td><?php echo ($tnt['inspection_due']!="")?date('d/m/Y',strtotime($tnt['inspection_due'])):''; ?></td>
				</tr>
			<?php	
			}
			?>
			</table>			
		</div>
	<?php
		}
	}
	?>
	
	
	
	<?php
	// Lockout Kit
	if($t['item']==4){ 
		$params = array(
		'tools_id' => $tools_id,
		'sort_list' => array(
			'order_by' => 'date',
			'sort' => 'DESC'
		)
	);
	$tnt_sql = $crm->getLockoutKitCheck($params);
	if( mysql_num_rows($tnt_sql)>0 ){
	?>
		<div style="text-align: left;">
			<h2 class="heading">Lockout kit check</h2>
			<table style="width:auto; margin: 0;" id="tbl_ladder" class="tbl-sd">
			<tr class="toprow">
				<th>Date</th>
				<th>Next Inspection Due</th>
			</tr>
			<?php
			
			while( $tnt = mysql_fetch_array($tnt_sql) ){ ?>
				<tr class="body_tr">
					<td><a href="/lockout_kit_check_details.php?id=<?php echo $tnt['lockout_kit_check_id']; ?>&tools_id=<?php echo $tools_id; ?>"><?php echo date('d/m/Y',strtotime($tnt['date'])); ?></a></td>
					<td><?php echo ($tnt['inspection_due']!="")?date('d/m/Y',strtotime($tnt['inspection_due'])):''; ?></td>
				</tr>
			<?php	
			}
			?>
			</table>			
		</div>
	<?php
		}
	}
	?>
    
  </div>

<br class="clearfloat" />


<script>
jQuery(document).ready(function(){

	jQuery("#btn_add_vehicle").click(function(){
	
		var item = jQuery("#item").val();
		var item_id = jQuery("#item_id").val();
		var error = "";
		
		if(item==""){
			error += "Item is required\n";
		}
		if(item_id==""){
			error += "Item ID is required\n";
		}
		
		if(error!=""){
			alert(error);
		}else{
			jQuery("#jform").submit();
		}
		
	});

	
	
});
</script>

</body>
</html>
