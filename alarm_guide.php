<?php
$title = "Alarm Guide";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$search = mysql_real_escape_string($_REQUEST['search']);
$country_id = $_SESSION['country_default'];


// pagination
$offset = ($_REQUEST['offset']!="")?mysql_real_escape_string($_REQUEST['offset']):0;
$limit = 100;

$this_page = $_SERVER['PHP_SELF'];
$params = "&search={$search}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$order_by = 'sa.`make`';
$sort = 'ASC';

$jparams = array(
	'search' => $search,
	'country_id' => $country_id,
	'sort_list' => array(
		array(
			'order_by' => $order_by,
			'sort' => $sort
		)
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	)
);
$sa_sql = $crm->getSmokeAlarms($jparams);	

$jparams = array(
	'search' => $search,
	'country_id' => $country_id
);
$ptotal = mysql_num_rows($crm->getSmokeAlarms($jparams));


?>

<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
</style>


<div id="mainContent">    

	<div class="sats-middle-cont">
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
  
			
		<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
		 <?php
		if($_GET['success']==1){ ?>
			<div class="success">New Alarms Added</div>
		<?php
		}
		?>
		
		 <?php
		if($_GET['del_succ']==1){ ?>
			<div class="success">Smoke Alarm Deleted</div>
		<?php
		}
		?>
		
		
		<div class="aviw_drop-h" style="border: 1px solid #ccc;">
		<form id="form_search" method="post">
		
		
			<div class="fl-left">
				<label style="margin-right: 9px;">Search:</label>
				<input type="text" name="search" id="search" style="width: 100px" class="addinput" />
			</div>
			
			<div class="fl-left" style="float:left; margin-left: 10px; display:none;">
				<input type="submit" name="btn_submit" class="submitbtnImg" value="Go" />
			</div>	
		</form>
		</div>

		<div id="ajax_body">
		<?php require_once 'inc_alarm_guide.php'; ?>
		</div>
		
	<?php 
	if( $_SESSION['USER_DETAILS']['ClassID']!=6 ){ ?>
		<div class="row" style="display:block; padding-top: 20px; clear: both;">
			<a href="/add_alarm.php"><button style="float: left;" type="button" id="btn_add_alarm" class="submitbtnImg">Add Alarm</button></a>
		 </div>
	<?php	
	}
	?>
     
	
	</div>
	
</div>

<br class="clearfloat" />

<script>
jQuery(document).ready(function(){
	
	// ajax search 
	jQuery("#search").keyup(function(){
		
		var search = jQuery(this).val();
		
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_get_alarm_guide.php",
			data: {
				search: search,
				order_by: '<?php echo $order_by; ?>',
				sort: '<?php echo $sort; ?>',
				offset: '<?php echo $offset; ?>',
				limit: '<?php echo $limit; ?>',
				this_page: '<?php echo $this_page; ?>'
			}
		}).done(function( ret ) {

			jQuery("#load-screen").hide();
			jQuery("#ajax_body").html(ret);
			
		});
		
	});
	
});
</script>
</body>
</html>
