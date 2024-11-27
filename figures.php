<?php

$title = "Figures";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$months_arr = array(
	'January',
	'February',
	'March',
	'April',
	'May',
	'June',
	'July ',
	'August',
	'September',
	'October',
	'November',
	'December'
);

?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.jRightSideBorder{
	border-right: 1px solid #cccccc;
}
.jLeftSideBorder{
	border-left: 1px solid #cccccc;
}
</style>
<div id="mainContent">

	
   
    <div class="sats-middle-cont">
	
		
	
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="<?php echo $title; ?>" href="/figures.php"><strong><?php echo $title; ?></strong></a></li>
		  </ul>
		</div>
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">New Data has been Added!</div>';
		}else if($_GET['success']==2){
			echo '<div class="success">Data has been Updated!</div>';
		}else if($_GET['del']==1){
			echo '<div class="success">Data has been Deleted!</div>';
		}
		
		
		?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		<div style="border: 1px solid #cccccc; display: none;" class="aviw_drop-h">		 
			<div class="fl-left" style="visibility:hidden;">
				<a href="javascript:void(0);"><button class="submitbtnImg" type="button">Export</button></a>
			</div>	
		</div>

		
				<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd jtable" style="margin-top: 0px; margin-bottom: 13px;">
						
						<tr class="jtable_heading" style="background-color:#ececec;">
							<th colspan="3" align="center" class="jRightSideBorder">MONTH</th>
							<th colspan="3" align="center" class="jRightSideBorder">PROPERTIES</th>
							<th colspan="9" align="center" class="jRightSideBorder">JOBS</th>
							<th align="center" class="jRightSideBorder">UPGRADES</th>
							<th colspan="2" align="center" class="jRightSideBorder">SALES</th>
							<th colspan="4" align="center" class="jRightSideBorder">REVENUE</th>							
							<th colspan="2" class="jRightSideBorder">COMP. REVENUE</th>			
							<th colspan="3" align="center" class="jRightSideBorder">TECHNICIANS</th>
							<th>&nbsp;</td>
						</tr>
						
						<tr class="toprow jalign_left">
							<th>Month</th>
							<th>Year</th>
							<th class="jRightSideBorder">Days</th>
							
							<th>Actual</th>
							<th>Last Mo.</th>
							<th class="jRightSideBorder">Net+/-</th>
							
							<!-- JOBS -->
							<th>YM</th>
							<th>O/Off</th>
							<th>COT</th>
							<th>LR</th>
							<th>FR</th>
							<th>Upgrade</th>		
							<th>Annual</th>					
							<th>Total</th>
							<th class="jRightSideBorder">O/S</th>														
							
										
							<th class="jRightSideBorder">Income</th>
							
							<th>New</th>
							<th class="jRightSideBorder">Renewed</th>
							
							<th>Budget</th>
							<th>Actual</th>
							<th>Diff+/-</th>
							<th class="jRightSideBorder">Daily Avg</th>
							
							<th>Prev. Year</th>
							<th class="jRightSideBorder">Diff</th>
							
							<th>Techs</th>
							<th>Daily Avg</th>
							<th class="jRightSideBorder">Mo. Avg</th>
							
							<th>Edit</td>
						</tr>

				<?php
				
				
				$fig_sql = mysql_query("
					SELECT *
					FROM `figures` AS f
					WHERE f.`country_id` = {$_SESSION['country_default']}
					ORDER BY f.`year` DESC, f.`month` DESC
				");
				
			
				$ctr = 0;
				while($fig = mysql_fetch_array($fig_sql)){ ?>

				
					
							
									<tr class="body_tr jalign_left" <?php echo ( $ctr%2 == 0 )?'':'style="background-color:#ececec"'; ?>>
										<td>
											<span class="txt_lbl"><?php echo $months_arr[$fig['month']-1]; ?></span>											
											<select class="txt_hid month" name="month" id="month" style="width:auto;">
												<option value="">--Select--</option>
												<?php
												foreach( $months_arr AS $index => $month ){ ?>
													<option value="<?php echo $index+1; ?>" <?php echo ($index+1==$fig['month'])?'selected="selected"':''; ?>><?php echo $month; ?></option>
												<?php	
												}
												?>
											</select>
										</td>
										<td>
											<span class="txt_lbl"><?php echo $fig['year']; ?></span>
											<input type="text" class="txt_hid year" value="<?php echo $fig['year']; ?>" />
										</td>										
										<td class="jRightSideBorder">
											<span class="txt_lbl"><?php echo ( $fig['working_days']!='' )?$fig['working_days']:''; ?></span>
											<input type="text" class="txt_hid working_days" value="<?php echo $fig['working_days']; ?>" />
										</td>
										
										
										<td>
											<span class="txt_lbl"><?php echo ( $fig['p_actual']!='' )?number_format($fig['p_actual']):''; ?></span>
											<input type="text" class="txt_hid p_actual" value="<?php echo $fig['p_actual']; ?>" />
										</td>
										<td>
											<span class="txt_lbl"><?php echo ( $fig['p_last_month']!='' )?number_format($fig['p_last_month']):''; ?></span>
											<input type="text" class="txt_hid p_last_month" value="<?php echo $fig['p_last_month']; ?>" />
										</td>
										<td class="jRightSideBorder">
											<?php
											$diff = $fig['p_actual']-$fig['p_last_month'];
											$diff_fin = ($diff/$fig['p_last_month'])*100;
											?>
											<span class="txt_lbl" style="color:<?php echo ($diff_fin>0)?'green':'red'; ?>; font-weight: bold;">
												<?php echo floor($diff_fin); ?>%
											</span>
											<span class="txt_hid"><?php echo floor($diff_fin); ?>%</span>											
										</td>
										
										<!-- JOBS -->
										<td>
											<span class="txt_lbl"><?php echo ( $fig['ym']!='' )?$fig['ym']:''; ?></span>
											<input type="text" class="txt_hid ym" value="<?php echo $fig['ym']; ?>" />
										</td>
										<td>
											<span class="txt_lbl"><?php echo ( $fig['of']!='' )?$fig['of']:''; ?></span>
											<input type="text" class="txt_hid of" value="<?php echo $fig['of']; ?>" />
										</td>
										<td>
											<span class="txt_lbl"><?php echo ( $fig['cot']!='' )?$fig['cot']:''; ?></span>
											<input type="text" class="txt_hid cot" value="<?php echo $fig['cot']; ?>" />
										</td>
										<td>
											<span class="txt_lbl"><?php echo ( $fig['lr']!='' )?$fig['lr']:''; ?></span>
											<input type="text" class="txt_hid lr" value="<?php echo $fig['lr']; ?>" />
										</td>
										<td>
											<span class="txt_lbl"><?php echo ( $fig['fr']!='' )?$fig['fr']:''; ?></span>
											<input type="text" class="txt_hid fr" value="<?php echo $fig['fr']; ?>" />
										</td>	
										<td>
											<span class="txt_lbl"><?php echo ( $fig['upgrades']!='' )?$fig['upgrades']:''; ?></span>
											<input type="text" class="txt_hid upgrades" value="<?php echo $fig['upgrades']; ?>" />
										</td>
										<td>
											<span class="txt_lbl"><?php echo ( $fig['annual']!='' )?$fig['annual']:''; ?></span>
											<input type="text" class="txt_hid annual" value="<?php echo $fig['annual']; ?>" />
										</td>

										<td>
											<?php $total_completed = $fig['ym']+$fig['of']+$fig['cot']+$fig['lr']+$fig['fr']+$fig['upgrades']+$fig['annual']; ?>
											<span class="txt_lbl"><strong><?php echo ( $total_completed>0 )?$total_completed:''; ?></strong></span>											
											<span class="txt_hid"><?php echo $total_completed; ?></span>	
										</td>																														
										<td class="jRightSideBorder">
											<span class="txt_lbl"><?php echo $fig['jobs_not_comp']; ?></span>
											<input type="text" class="txt_hid jobs_not_comp" value="<?php echo $fig['jobs_not_comp']; ?>" />
										</td>																				
										
										
										<td class="jRightSideBorder">
											<span class="txt_lbl"><?php echo ( $fig['upgrades_income']>0 )?'$'.number_format($fig['upgrades_income']):''; ?></span>
											<input type="text" class="txt_hid upgrades_income" value="<?php echo $fig['upgrades_income']; ?>" />
										</td>
										
										<td>
											<span class="txt_lbl"><?php echo ( $fig['new_sales']!='' )?$fig['new_sales']:''; ?></span>
											<input type="text" class="txt_hid new_sales" value="<?php echo $fig['new_sales']; ?>" />
										</td>
										<td class="jRightSideBorder">
											<span class="txt_lbl"><?php echo ( $fig['renewals']!='' )?$fig['renewals']:''; ?></span>
											<input type="text" class="txt_hid renewals" value="<?php echo $fig['renewals']; ?>" />
										</td>
										
										<td>
											<span class="txt_lbl">$<?php echo number_format($fig['budget']); ?></span>
											<input type="text" class="txt_hid budget" value="<?php echo $fig['budget']; ?>" />
										</td>
										<td>
											<span class="txt_lbl" style="font-weight: bold;">$<?php echo number_format($fig['actual']); ?></span>
											<input type="text" class="txt_hid actual" value="<?php echo $fig['actual']; ?>" />
										</td>
										<td>
											<?php 
											$difference = $fig['actual']-$fig['budget'];
											?>
											<span class="txt_lbl" style="color:<?php echo ($difference>0)?'green':'red'; ?>; font-weight: bold;">$
											<?php echo number_format($difference); ?>
											</span>											
											<span class="txt_hid">$<?php echo number_format($difference); ?></span>	
										</td>
										<td class="jRightSideBorder">
											<span class="txt_lbl">$<?php echo $daily_avg = number_format($fig['actual']/$fig['working_days']); ?></span>											
											<span class="txt_hid">$<?php echo $daily_avg; ?></span>	
										</td>
										
										<td>
											<span class="txt_lbl">$<?php echo number_format($fig['prev_year']); ?></span>
											<input type="text" class="txt_hid prev_year" value="<?php echo $fig['prev_year']; ?>" />
										</td>
										<td class="jRightSideBorder">
											<?php
											$diff = $fig['actual']-$fig['prev_year'];
											$diff_fin = ($diff/$fig['prev_year'])*100;
											?>
											<span class="txt_lbl" style="color:<?php echo ($diff_fin>0)?'green':'red'; ?>; font-weight: bold;">
											<?php echo floor($diff_fin); ?>%
											</span>						
											<span class="txt_hid"><?php echo $diff_fin; ?></span>	
										</td>
										<td>
											<span class="txt_lbl"><?php echo $fig['techs']; ?></span>
											<input type="text" class="txt_hid techs" value="<?php echo $fig['techs']; ?>" />
										</td>
										
										<td class="jRightSideBorder">
											<?php $techs_daily_avg = $fig['actual']/($fig['techs'] * $fig['working_days']); ?>
											<span class="txt_lbl">$<?php echo number_format($techs_daily_avg, 2); ?></span>
											<span class="txt_hid">$<?php echo number_format($techs_daily_avg, 2); ?></span>																					
										</td>											
										<td class="jRightSideBorder">
											<?php $techs_monthly_avg = $fig['actual']/$fig['techs']; ?>
											<span class="txt_lbl">$<?php echo number_format($techs_monthly_avg, 2); ?></span>
											<span class="txt_hid">$<?php echo number_format($techs_monthly_avg, 2); ?></span>	
										</td>
																			
										<td>
											<input type="hidden" class="figures_id" value="<?php echo $fig['figures_id']; ?>" readonly="readonly" />
											<button class="blue-btn submitbtnImg btn_update">Update</button>
											<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
											<button class="submitbtnImg btn_cancel" style="display:none;">Cancel</button>
											<button class="blue-btn submitbtnImg btn_delete" style="display:none;">Delete</button>
										</td>
									</tr>
							
							
					
				
				<?php	
				$ctr++;
				}
				
				?>
				
				
				</table>
		
		
			

		<div class="jalign_left">
		
			<button type="button" id="btn_add_data" class="submitbtnImg blue-btn">
				<img class="inner_icon" src="images/button_icons/add-button.png">
				<span class="inner_icon_txt">Data</span>
			</button>
			
            <div style="padding-top: 20px;" id="div_staff" class="addproperty formholder">
				<form id="form_figures" method="post" action="add_figures.php" style="display:none;">
					<div class="row">
						<label class="addlabel" for="month">Month</label>
						<select name="month"  id="month">
							<option value="">--Select--</option>
							<?php
							foreach( $months_arr AS $index=>$month ){ ?>
								<option value="<?php echo $index+1; ?>"><?php echo $month; ?></option>
							<?php	
							}
							?>
						</select>
					</div> 
					<div class="row">
						<label class="addlabel" for="year">Year</label>
						<input type="text" name="year" id="year" class="year">
					</div> 
					<div class="row">
						<label class="addlabel" for="working_days">Days</label>
						<input type="text" name="working_days" id="working_days" class="working_days">
					</div>
					
					
					<div class="row">
						<label class="addlabel" for="p_actual">Actual (Properties)</label>
						<input type="text" name="p_actual" id="p_actual" class="p_actual">
					</div>
					<div class="row">
						<label class="addlabel" for="p_last_month">Last Month (Properties)</label>
						<input type="text" name="p_last_month" id="p_last_month" class="p_last_month">
					</div>
					
					<div class="row">
						<label class="addlabel" for="ym">YM</label>
						<input type="text" name="ym" id="ym" class="ym">
					</div>
					<div class="row">
						<label class="addlabel" for="of">ONCE OFF</label>
						<input type="text" name="of" id="of" class="of">
					</div>
					<div class="row">
						<label class="addlabel" for="cot">COT</label>
						<input type="text" name="cot" id="cot" class="cot">
					</div>
					<div class="row">
						<label class="addlabel" for="lr">LR</label>
						<input type="text" name="lr" id="lr" class="lr">
					</div>
					<div class="row">
						<label class="addlabel" for="fr">FR</label>
						<input type="text" name="fr" id="fr" class="fr">
					</div>
					<div class="row">
						<label class="addlabel" for="upgrades">Upgrades</label>
						<input type="text" name="upgrades" id="upgrades" class="upgrades">
					</div>				
					<div class="row">
						<label class="addlabel" for="upgrades_income">Upgrades Income</label>
						<input type="text" name="upgrades_income" id="upgrades_income" class="upgrades_income">
					</div>
					<div class="row">
						<label class="addlabel" for="annual">Annual</label>
						<input type="text" name="annual" id="annual" class="annual">
					</div>
					<div class="row">
						<label class="addlabel" for="jobs_not_comp">Not Completed</label>
						<input type="text" name="jobs_not_comp" id="jobs_not_comp" class="jobs_not_comp">
					</div>
					<div class="row">
						<label class="addlabel" for="new_sales">New Sales</label>
						<input type="text" name="new_sales" id="new_sales" class="new_sales">
					</div>
					<div class="row">
						<label class="addlabel" for="renewals">Renewals</label>
						<input type="text" name="renewals" id="renewals" class="renewals">
					</div>
					<div class="row">
						<label class="addlabel" for="budget">Budget</label>
						<input type="text" name="budget" id="budget" class="budget">
					</div>
					<div class="row">
						<label class="addlabel" for="actual">Actual</label>
						<input type="text" name="actual" id="actual" class="actual">
					</div>
					<div class="row">
						<label class="addlabel" for="prev_year">Prev. Year</label>
						<input type="text" name="prev_year" id="prev_year" class="prev_year">
					</div>
					<div class="row">
						<label class="addlabel" for="techs">Techs</label>
						<input type="text" name="techs" id="techs" class="techs" />
					</div>
					<div style="padding-top: 15px; text-align:left;" class="row clear">
						<input type="hidden" name="btn_submit" value="Submit" />
						<button type="submit" class="submitbtnImg" style="width: auto; margin-bottom: 50px;" >
							<img class="inner_icon" src="images/button_icons/save-button.png">
							<span class="inner_icon_txt">Submit</span>
						</button>
					</div>
				</form>
			</div>			
			
		</div>
			
				
	
		
		
		
		
	</div>
</div>

<br class="clearfloat" />

<script>
jQuery(document).ready(function(){


	

	function is_numeric(num){
		if(num.match( /^\d+([\.,]\d+)?$/)==null){
			return false
		}
	}

	function validate_email(email){
		var atpos = email.indexOf("@");
		var dotpos = email.lastIndexOf(".");
		if ( atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length ){
		  return false
		}
	}
		
	
	// opportunity validation check
	jQuery("#form_figures").submit(function(event){
		
		var month = jQuery("#form_figures #month").val();
		var year = jQuery("#form_figures #year").val();		
		var error = "";
		
		if( month=="" ){
			error += "Month is required\n";
		}
		
		if( year=="" ){
			error += "Year is required\n";
		}
				
		if(error!=""){
			alert(error);
			return false;
		}else{
			return true;
		}
	});

	// sales rep validation check
	jQuery("#form_sales_rep").submit(function(event){
		
		var fname = jQuery("#fname").val();		
		var lname = jQuery("#lname").val();
		var error = "";
		
		if(fname==""){
			error += "Sales Rep first name is required\n";
		}
		
		if(lname==""){
			error += "Sales Rep last name is required\n";
		}
				
		if(error!=""){
			alert(error);
			return false;
		}else{
			return true;
		}
	});

	jQuery(".btn_edit").click(function(){
	
		jQuery(this).parents("tr:first").find(".btn_update").show();
		jQuery(this).parents("tr:first").find(".btn_edit").hide();
		jQuery(this).parents("tr:first").find(".btn_cancel").show();
		jQuery(this).parents("tr:first").find(".btn_delete").show();
		jQuery(this).parents("tr:first").find(".txt_hid").show();
		jQuery(this).parents("tr:first").find(".txt_lbl").hide();
	
	});	
	
	jQuery(".btn_cancel").click(function(){
		
		jQuery(this).parents("tr:first").find(".btn_update").hide();
		jQuery(this).parents("tr:first").find(".btn_edit").show();
		jQuery(this).parents("tr:first").find(".btn_cancel").hide();
		jQuery(this).parents("tr:first").find(".btn_delete").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".txt_hid").hide();	
		
	});
	
	jQuery(".btn_update").click(function(){
	
		var figures_id = jQuery(this).parents("tr:first").find(".figures_id").val();
		var month = jQuery(this).parents("tr:first").find(".month").val();
		var year = jQuery(this).parents("tr:first").find(".year").val();
		var working_days = jQuery(this).parents("tr:first").find(".working_days").val();
		
		var p_actual = jQuery(this).parents("tr:first").find(".p_actual").val();
		var p_last_month = jQuery(this).parents("tr:first").find(".p_last_month").val();
		
		var ym = jQuery(this).parents("tr:first").find(".ym").val();
		var of = jQuery(this).parents("tr:first").find(".of").val();
		var cot = jQuery(this).parents("tr:first").find(".cot").val();
		var lr = jQuery(this).parents("tr:first").find(".lr").val();
		var fr = jQuery(this).parents("tr:first").find(".fr").val();
		var upgrades = jQuery(this).parents("tr:first").find(".upgrades").val();
		var upgrades_income = jQuery(this).parents("tr:first").find(".upgrades_income").val();
		var jobs_not_comp = jQuery(this).parents("tr:first").find(".jobs_not_comp").val();
		var annual = jQuery(this).parents("tr:first").find(".annual").val();
		
		var new_sales = jQuery(this).parents("tr:first").find(".new_sales").val();
		var renewals = jQuery(this).parents("tr:first").find(".renewals").val();
			
		var budget = jQuery(this).parents("tr:first").find(".budget").val();
		var actual = jQuery(this).parents("tr:first").find(".actual").val();
		
		var prev_year = jQuery(this).parents("tr:first").find(".prev_year").val();
		var techs = jQuery(this).parents("tr:first").find(".techs").val();
		
		var error = "";
		
		if(month==""){
			error += "Month cannot be empty\n";
		}
		
		if(error!=""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_figures.php",
				data: { 
					figures_id: figures_id,
					
					month : month,
					year: year,
					working_days: working_days,
					
					p_actual: p_actual,
					p_last_month: p_last_month,
					
					ym: ym,
					of: of,
					cot: cot,
					lr: lr,
					fr: fr,
					upgrades: upgrades,
					upgrades_income: upgrades_income,
					jobs_not_comp: jobs_not_comp,
					annual: annual,
					
					new_sales: new_sales,
					renewals: renewals,
					
					budget: budget,
					actual: actual,
					
					prev_year: prev_year,
					techs: techs
				}
			}).done(function( ret ) {
				window.location="/figures.php?success=2";
			});				
			
		}		
		
	});

	// delete data
	jQuery(".btn_delete").click(function(){
		if(confirm("Are you sure you want to delete?")){
			var figures_id = jQuery(this).parents("tr:first").find(".figures_id").val();
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_figures.php",
				data: { 
					figures_id: figures_id
				}
			}).done(function( ret ) {	
				window.location="/figures.php?del=1";
			});	
		}				
	});
	
	// delete salesrep
	jQuery(".btn_del_sr").click(function(){
		if(confirm("Are you sure you want to delete?")){
			var ss_sr_id = jQuery(this).parents("tr:first").find(".ss_sr_id").val();
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_snapshot_sales_rep.php",
				data: { 
					ss_sr_id: ss_sr_id
				}
			}).done(function( ret ) {	
				window.location.reload();
			});	
		}				
	});

	//  opportunity show/hide form toggle		
	jQuery("#btn_add_data").click(function(){
		
		var btn_txt = jQuery(this).find(".inner_icon_txt").html();
		var orig_btn_txt = 'Data';
		var orig_btn_icon = 'images/button_icons/add-button.png';
		var cancel_btn_icon = 'images/button_icons/cancel-button.png';
		
		if( btn_txt == orig_btn_txt ){
			jQuery(this).removeClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html('Cancel');
			jQuery(this).find(".inner_icon").attr("src",cancel_btn_icon)
			jQuery("#form_figures").show();
		}else{
			jQuery(this).addClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html(orig_btn_txt);
			jQuery(this).find(".inner_icon").attr("src",orig_btn_icon)
			jQuery("#form_figures").hide();
		}		
		
	});
	
	
	// main sales rep show/hide form toggle
	jQuery("#btn_add_edit_sales_rep").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#sales_rep_div").slideDown();
	},function(){
		jQuery(this).html("Add/Edit Sales Rep");		
		jQuery("#sales_rep_div").slideUp();
	});
	
	// sales rep show/hide form toggle
	jQuery("#btn_add_sales_rep").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#form_sales_rep").slideDown();
	},function(){
		jQuery(this).html("Add Sales Rep");		
		jQuery("#form_sales_rep").slideUp();
	});
	
	
	
	
	
});
</script>
</body>
</html>