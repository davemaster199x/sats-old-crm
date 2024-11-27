<?
$title = "Daily Figures";

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');

$crm = new Sats_Crm_Class;

$from = date('Y-m-01');
$to = date('Y-m-t');

// variables
$start_date = ($_GET['from_date']!='')?$_GET['from_date']:date('Y-m-01'); // start
$end_date = ($_GET['end_date']!='')?$_GET['end_date']:date('Y-m-t'); // end of month
$state = $_REQUEST['state'];

$country_id = $_SESSION['country_default'];
$date = date('Y-m-d');

$ic_service = getICService();
$ic_service_imp = implode(',',$ic_service);

// get Daily Figures data
function getDailyFigures($date){
	
	return mysql_query("
		SELECT *
		FROM `daily_figures`
		WHERE `month` = '{$date}'
		AND `country_id` = {$_SESSION['country_default']}
	");
	
}
?>
<div id="mainContent">

	<div class="sats-middle-cont">
		
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="<?php echo $title; ?>" href="daily_figures.php"><strong><?php echo $title; ?></strong></a></li>
		  </ul>
		</div>
	   <div id="time"><?php echo date("l jS F Y"); ?></div>
	   
	   <table cellpadding=0 cellspacing=0 >
			<tr class="tbl-view-prop">
				<td>
				
					<div class="aviw_drop-h qlnk" style="padding: 15px 0!important;">

						Quick Links 
						
						<?php 
						
						// previous 6 months
						for( $i=6; $i>=1; $i-- ){ 
							$prev_month_ts = strtotime("{$start_date} -{$i} month"); 
							$ql_start_date = date('Y-m-01',$prev_month_ts);
							$ql_end_date = date('Y-m-t',$prev_month_ts);
						?>				
							| 
							<a href="?from_date=<?php echo $ql_start_date; ?>&end_date=<?php echo $ql_end_date; ?>">
								<span <?php echo ($ql_start_date==$start_date)?'class="bold_this"':''; ?>><?php echo date("F",$prev_month_ts); ?></span>
							</a>
						
						<?php	
						}			
						?>

						
						<?php
						// current month
						$filtered_month_ts = strtotime($start_date); 
						$ql_start_date = date('Y-m-01',$filtered_month_ts);
						$ql_end_date = date('Y-m-t',$filtered_month_ts);
						?>
						| 
						<a href="?from_date=<?php echo date('Y-m-01') ?>&end_date=<?php echo date('Y-m-t') ?>">
							<span <?php echo ($ql_start_date==$start_date)?'class="bold_this"':''; ?>><?php echo date("F",$filtered_month_ts); ?></span>
						</a>
						
						<?php
						// next month
						$next_month_ts = strtotime("{$start_date} +1 month"); 
						$nm_start_date = date('Y-m-01',$next_month_ts);
						$nm_end_date = date('Y-m-t',$next_month_ts);
						?>
						| 
						<a href="?from_date=<?php echo $nm_start_date; ?>&end_date=<?php echo $nm_end_date; ?>">
							<span <?php echo ($nm_start_date==$start_date)?'class="bold_this"':''; ?>><?php echo date("F",$next_month_ts); ?></span>
						</a>
					
					</div>		
					
				</td>
		  </tr>
		</table>

		<?php
		// get daily figures data
		$df_sql = getDailyFigures($start_date);
		$df = mysql_fetch_array($df_sql);
		
		// get todays daily figure
		$dfpd_sql = $crm->getDailyFiguresPerDate($date);
		$dfpd = mysql_fetch_array($dfpd_sql);
		$todays_working_day = $dfpd['working_day'];
		?>
		<table id="tbl_budget">
			<tr>
				<td>Budget:</td><td><input type="text" id="budget" class="budget_fields_elem" value="<?php echo $df['budget']; ?>" /></td>
				<td>Working Days:</td><td><input type="text" id="working_days" class="budget_fields_elem" value="<?php echo $df['working_days']; ?>" /></td>
				<td>Days Worked:</td><td><?php echo $todays_working_day; ?></td>
				<td>Working Days Left: <span id="working_days_left"><?php echo ($df['working_days']-$todays_working_day); ?></span></td>
				<td>
					<button type="button" class="submitbtnImg blue-btn" id="btn_save">
						<img class="inner_icon" src="images/button_icons/save-button.png">
						Save
					</button>
				</td>							
			</tr>
		</table>

		<div style="float: left; margin-right: 20px;">
		<table border=0 cellspacing=0 class='table-center tbl-fr-red' style="width:auto;">
			<tr bgcolor="#b4151b" class="noBorderTop">
				<th class="noBorderTop">Date</th>
				<th class="noBorderTop">Working Day</th>
				<th class="noBorderTop jtextalignleft">Day</th>
				<th class="noBorderTop">Sales</th>
				<th class="noBorderTop">Techs</th>
				<th class="noBorderTop">Jobs</th>		
				<th class="noBorderTop">Avg. Jobs</th>
				<th class="noBorderTop">Avg. $ Jobs</th>
				<th class="noBorderTop">MTD Sales</th>	
			</tr>	
			<?php

			// current date
			$curr_date = $start_date; 
			$todays_sale = 0;
			$mtd_sales = 0;
			
			while( $curr_date <= $end_date ){ 
				
				$bgcolorClass = '';
				// get current date timestamp
				$curr_date_ts = strtotime($curr_date);
				
				// get Daily Figures data per Date
				$dfpd_sql = $crm->getDailyFiguresPerDate(date('Y-m-d',$curr_date_ts));
				$dfpd = mysql_fetch_array($dfpd_sql);
				
				// day full textual display
				$day_txt = date('l',$curr_date_ts);
				
				// today
				$is_today = ( $curr_date==date('Y-m-d') )?1:0;
				
				if( $is_today==1 ){
					$bgcolorClass = 'todaysBgColor';
					$todays_sale = $dfpd['sales'];
				}else{
					if( $day_txt=='Sunday' || $day_txt=='Saturday' ){
						$bgcolorClass = 'greyRowBgColor';
					}
				}
				
				
			?>
				<tr class="<?php echo $bgcolorClass; ?>">
					<td>
						<a href="javascript:void(0);" class="date_link">
							<?php echo date('d/m/Y',$curr_date_ts); ?>
						</a>
						<input type="hidden" class="date" value="<?php echo date('Y-m-d',$curr_date_ts); ?>" />
					</td>
					<td>
						<span class="display_elem"><?php echo ($dfpd['working_day']>0)?$dfpd['working_day']:''; ?></span>
						<input type="text" class="working_day update_elem" value="<?php echo $dfpd['working_day']; ?>" />
					</td>
					<td class="jtextalignleft"><?php echo $day_txt; ?></td>
					<td>
						<span class="display_elem"><?php echo ($dfpd['sales']>0)?'$'.number_format($dfpd['sales'], 2):''; ?></span>
						<input type="text" class="sales update_elem" value="<?php echo $dfpd['sales']; ?>" />
					</td>
					<td>
						<span class="display_elem"><?php echo ($dfpd['techs']>0)?$dfpd['techs']:''; ?></span>
						<input type="text" class="techs update_elem" value="<?php echo $dfpd['techs']; ?>" />
					</td>
					<td>
						<span class="display_elem"><?php echo ($dfpd['jobs']>0)?$dfpd['jobs']:''; ?></span>
						<input type="text" class="jobs update_elem" value="<?php echo $dfpd['jobs']; ?>" />
						
						<input type="hidden" class="dfpd_id" value="<?php echo $dfpd['daily_figure_per_date_id']; ?>" />
						<?php
						// today
						if( $is_today==1 ){ ?>
							<button type="button" class="submitbtnImg blue-btn update_elem btn_fetch_data" id="" data-date="<?php echo date('Y-m-d',$curr_date_ts); ?>">Fetch Data</button>
						<?php	
						}
						?>
						<button type="button" class="submitbtnImg btn_update" style="display:none;">Update</button>						
					</td>			
					<td>
						<span class="average">
						<?php 
						$average = round($dfpd['jobs']/$dfpd['techs']); 
						echo ($average>0)?$average:'';
						?>
						</span>
					</td>
					<td>
						<span class="average">
						<?php 
						$average2 = $dfpd['sales']/$dfpd['jobs']; 
						echo ($average2>0)?'$'.number_format($average2, 2):'';
						?>
						</span>
					</td>
					<td>
						<?php 
						if($dfpd['sales']>0){
							$mtd_sales += $dfpd['sales'];
						}				
						?>
						<span class="display_elem"><?php echo ($dfpd['sales']>0)?'$'.number_format($mtd_sales, 2):''; ?></span>
					</td>
				</tr>
			<?php	
				// increment current date
				$curr_date = date('Y-m-d',strtotime($curr_date.'+ 1 day'));
			}
			?>			
		</table>

		
		<div style="text-align: left;" class="instruct_div">
			<ul style="padding-left: 16px;">
				<li>Outstanding Jobs = Number of Jobs EXCEPT (On Hold,Pending,Booked, Completed,Cancelled)</li>
				<li>Outstanding Value = Value of Jobs EXCEPT (On Hold,Pending,Booked, Completed,Cancelled)</li>
			</ul>
		</div>
		</div>
			
		
		<div style="float: left; margin-right: 20px;">
			
			<input type="hidden" id="from" class="from" value="<?php echo $from; ?>" />
			<input type="hidden" id="to" class="to" value="<?php echo $to; ?>" />
			<input type="hidden" id="df_working_days" class="df_working_days" value="<?php echo $df['working_days']; ?>" />
			<input type="hidden" id="df_budget" class="df_budget" value="<?php echo $df['budget']; ?>" />
			<input type="hidden" id="mtd_sales" class="mtd_sales" value="<?php echo $mtd_sales; ?>" />
			<button type="button" class="submitbtnImg blue-btn btn_statistics_tbl">Fetch Data</button>	

			<div style="clear:both"></div>
			
			<div class="statistics_tbl">{statistics_tbl}</div>
			
			<div style="clear:both"></div>
			
			<div style="text-align: left;" class="instruct_div">				
				<ol style="padding-left: 20px;">
					<li>Click Todays Date, Fetch Data, Update</li>
					<li>
						<?php 			
						// MAILTO
						if($country_id==1){
							$mailto_to = 'figures@sats.com.au';
							$mailto_subject = rawurlencode("Daily figures for ".date('d/m/Y',strtotime($date))." SATS AU");
						}else if($country_id==2){
							$mailto_to = 'figures@sats.co.nz';
							$mailto_subject = rawurlencode("Daily figures for ".date('d/m/Y',strtotime($date))." SATS NZ");
						}			
						?>
						<a href="mailto:<?php echo $mailto_to; ?>?Subject=<?php echo $mailto_subject; ?>">CLICK HERE</a> to email figures
					</li>
					<li>Export from <a href="/merged_jobs.php" target="__blank">Merged Certifictates</a></li>
					<li>
						<?php 			
						// MAILTO
						if($country_id==1){
							$mailto_to = 'accounts@sats.com.au';
							$mailto_subject = rawurlencode("MYOB Import for ".date('d/m/Y',strtotime($date))." SATS AU");
						}else if($country_id==2){
							$mailto_to = 'accounts@sats.co.nz';
							$mailto_subject = rawurlencode("MYOB Import for ".date('d/m/Y',strtotime($date))." SATS NZ");
						}			
						?>
						<a href="mailto:<?php echo $mailto_to; ?>?Subject=<?php echo $mailto_subject; ?>">CLICK HERE</a> to email Export to Accounts
					</li>
					<li>Mark All Jobs Completed</li>
				</ol>
			</div>
		</div>

		
		 
		<div style="clear:both;"></div>
		
	</div>

</div>

<br class="clearfloat" />

<style>
table.tbl-fr-red td,
table.tbl-fr-red th{
	border: 1px solid #cccccc;
}
.update_elem{
	display:none;
}
.bold_this{
	font-weight: bold;
}
.todaysBgColor{
	background-color: #dfffa5;
}
.greyRowBgColor{
	background-color: #ececec;
}
.jtextalignleft{
	text-align: left;
}
#mainContent input{
	width: 70px;
}
#table2{
	width:auto; 
	margin-bottom: 20px;
}
#table2 td{
	text-align:left;
}

.shadeRow{
	color: #cccccc;
	border: 1px solid #ccc;	
}
.noBorderTop{
	border-top: medium none !important;
}

.instruct_div li {
    font-size: 13px;
	padding: 4px 0;
}
.t_header{
	text-align: center !important;
	background-color: #ececec;
}
.statistics_tbl{
	text-align:left; 
	margin-top: 22px;
	display: none;
}
.btn_statistics_tbl{
	float: left;
	margin-top: 10px;
}
</style>
<script>
jQuery(document).ready(function(){
	
	
	// statistics table
	// update daily figures per date
	jQuery(".btn_statistics_tbl").click(function(){
		
		var from = jQuery("#from").val();
		var to = jQuery("#to").val();
		var df_working_days = jQuery("#df_working_days").val();
		var mtd_sales = jQuery("#mtd_sales").val();
		var df_budget = jQuery("#df_budget").val();
		
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_daily_figures_statistics.php",
			data: {
				mtd_sales: mtd_sales,
				df_budget: df_budget,
				df_working_days: df_working_days,
				from: from,
				to: to
			}
		}).done(function( ret ){

			jQuery("#load-screen").hide();
			jQuery(".statistics_tbl").html(ret);
			jQuery(".statistics_tbl").show();
		});
		
	});

	
	
	// fetch data ajax
	jQuery(".btn_fetch_data").click(function(){
		
		var obj = jQuery(this);
		var date = jQuery(this).attr("data-date");
		
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_daily_figures_fetch_data.php",
			dataType: 'json',
			data: {
				date: date
			}
		}).done(function( ret ){			
			jQuery("#load-screen").hide();
			obj.parents("tr:first").find(".sales").val(ret.sales);
			obj.parents("tr:first").find(".techs").val(ret.techs);
			obj.parents("tr:first").find(".jobs").val(ret.jobs);
			obj.hide();
			obj.parents("tr:first").find(".btn_update").show();
		});	
		
	});
	
	
	// save budget and working days
	jQuery("#btn_save").click(function(){
		
		var budget = jQuery("#budget").val();	
		var working_days = jQuery("#working_days").val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_daily_figures.php",
			data: {
				df_id: '<?php echo $df['daily_figure_id']; ?>',
				month: '<?php echo $start_date ?>',
				budget: budget,
				working_days: working_days
			}
		}).done(function( ret ){			
			//location.reload();
			window.location="daily_figures.php";
		});
		
	});
	
	
	// update daily figures per date
	jQuery(".btn_update").click(function(){
		
		var obj = jQuery(this);
		var dfpd_id = obj.parents("tr:first").find(".dfpd_id").val();	
		
		var working_day = obj.parents("tr:first").find(".working_day").val();
		var date = obj.parents("tr:first").find(".date").val();	
		var techs = obj.parents("tr:first").find(".techs").val();
		var jobs = obj.parents("tr:first").find(".jobs").val();
		var sales = obj.parents("tr:first").find(".sales").val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_daily_figures_per_date.php",
			data: {
				dfpd_id:dfpd_id,
				working_day: working_day,
				date: date,
				techs: techs,
				jobs: jobs,
				sales: sales
			}
		}).done(function( ret ){			
			//location.reload();
			window.location="daily_figures.php";
		});
		
	});

	
	// update toggle
	jQuery(".date_link").click(function(){
		
		var date = jQuery(this).parents("tr:first").find(".date").val();
		
		jQuery(this).parents("tr:first").find(".display_elem").toggle();
		jQuery(this).parents("tr:first").find(".update_elem").toggle();
		
		if( date!='<?php echo $date ?>' ){
			jQuery(this).parents("tr:first").find(".btn_update").toggle();
		}		
		
	});
	
});
</script>
</body>
</html> 
