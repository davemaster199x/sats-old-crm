<?
$title = "Completed Report";

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');
include ('inc/completed_report_function.php');

$from = ( $_REQUEST['from'] !='' )?$_REQUEST['from']:date('Y-m-01');
$to = ( $_REQUEST['to'] !='' )?$_REQUEST['to']:date('Y-m-t');

?>
<div id="mainContent">


 <div class="sats-middle-cont">
   
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Completed Report" href="/completed_report.php"><strong>Completed Report</strong></a></li>
      </ul>
    </div>
 
    
    <table cellpadding=0 cellspacing=0 >
    <tr class="tbl-view-prop">
        <td>
          <form action="" id="date_range" method="get" style="margin: 0;">
          <div class="aviw_drop-h">
            
			  <div class="fl-left" style="float: left;">
				<label><?php echo date("F Y"); ?></label>
			  </div>

			</div>

			<div class="aviw_drop-h qlnk">

				<div class="float-left content-black prev_next_link">
					<a href="/completed_report.php?from=<?php echo date('Y-m-01',strtotime('-1 month')); ?>&to=<?php echo date('Y-m-t',strtotime('-1 month')); ?>">
						<div class="arw-lft2">&nbsp;</div> 
						Previous Month
					</a>
				</div>

				<div style="float: left; margin: 0px 20%;">
					Quick Links
					<?php
					for(  $i=-3; $i<=0; $i++ ){ ?> 
						&nbsp;|&nbsp;	
						<a href="/completed_report.php?
							from=<?php echo date('Y-m-01',strtotime("{$i} month")); ?>
							&to=<?php echo date('Y-m-t',strtotime("{$i} month")); ?>
						">
							<?php echo date('F',strtotime("{$i} month")); ?>
						</a>
					<?php										
					}
					?>
				</div>
						
				<div class="float-right pg-tp-rg content-black prev_next_link">
					<a href="/completed_report.php?from=<?php echo date('Y-m-01',strtotime('+1 month')); ?>&to=<?php echo date('Y-m-t',strtotime('+1 month')); ?>">
						Next Month 
						<div class="arw-rgt2">&nbsp;</div>
					</a>
				</div>	
				
			</div>
            
 </form>           
            
     </td>
  </tr>
</table>

	

		
	<style>
	.table-center.tbl-fr-red td.f_col {
		text-align: left;
	}
	</style>
	
	
	<div class="serv_div">
		<button type="button" style="float: left; margin-top: 13px; margin-right: 14px; background-color:#b4151b;color:#ffffff;" class="submitbtnImg btn_show_data">Display</button>
		<h2 class="heading" style="color:#b4151b;float: left;display:block; clear:none;">All Services</h2> 
		<img src="images/ajax-loader.gif" class="serv_load_gif" style="float: left; margin-left: 18px; margin-top: 21px; display:none;" />
		
		<input type="hidden" class="from" value="<?php echo $from; ?>" />
		<input type="hidden" class="to" value="<?php echo $to; ?>" />
		<input type="hidden" class="ajt_id" value="" />
		
		<div style="clear:both;"></div>

		<div class="serv_data"></div>	
	</div>
	
<div style="clear:both;"></div>			
	
	
	
	<?php
		$ajt_sql = mysql_query("
			SELECT *
			FROM `alarm_job_type`
			WHERE `active` = 1
			ORDER BY `id` DESC
 		");
		while($ajt = mysql_fetch_array($ajt_sql)){ 
		
		switch($ajt['id']){
			case 2:
				$serv_color = 'b4151b';
			break;
			case 5:
				$serv_color = 'f15a22';
			break;
			case 6:
				$serv_color = '00ae4d';
			break;
			case 7:
				$serv_color = '0000FF';
			break;
			default:
				$serv_color = '9B30FF';
		}
		
		?>
			
			<div class="serv_div">
				<button type="button" style="float: left; margin-top: 13px; margin-right: 14px; background-color:#<?php echo $serv_color; ?>;color:#ffffff;" class="submitbtnImg btn_show_data">Display</button>
				<h2 class="heading" style="color:#<?php echo $serv_color; ?>;float: left;display:block; clear:none;"><?php echo $ajt['type']; ?></h2> 
				<img src="images/ajax-loader.gif" class="serv_load_gif" style="float: left; margin-left: 18px; margin-top: 21px; display:none;" />
				
				<input type="hidden" class="from" value="<?php echo $from; ?>" />
				<input type="hidden" class="to" value="<?php echo $to; ?>" />
				<input type="hidden" class="ajt_id" value="<?php echo $ajt['id']; ?>" />
				
				<div style="clear:both;"></div>
		
				<div class="serv_data"></div>	
			</div>
			<div style="clear:both;"></div>					
		
		<?php	
		}
	?>
    
	
	


</div>

</div>

<br class="clearfloat" />

<script>
jQuery(document).ready(function(){
	
	jQuery("#load-screen").hide();
	
	
	
	// show data script
	jQuery(".btn_show_data").click(function(){
		
		var obj = jQuery(this);
		
		obj.parents(".serv_div").find(".serv_load_gif").show();
				
		var from = obj.parents(".serv_div").find(".from").val();
		var to = obj.parents(".serv_div").find(".to").val();
		var ajt_id = obj.parents(".serv_div").find(".ajt_id").val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_completed_report.php",
			data: { 
				from: from,
				to: to,
				ajt_id: ajt_id
			}
		}).done(function(ret){
		
			obj.parents(".serv_div").find(".serv_load_gif").hide();
			obj.parents(".serv_div").find(".serv_data").html(ret);	
			
		});
		
	});
				
	
});
</script>
</body>
</html> 
