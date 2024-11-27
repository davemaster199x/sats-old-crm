<?php
$title = "Email Templates";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;
//$crm->displaySession();

$current_page = $_SERVER['PHP_SELF'];

$user_type = $_SESSION['USER_DETAILS']['ClassID'];
$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$loggedin_staff_name = "{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}";
$country_id = $_SESSION['country_default'];

$active = mysql_real_escape_string(trim($_REQUEST['active'])); 

if( $active!='' ){
	$active = ( $active >= 0 )?$active:''; // empty active for all
}else{
	$active = 1; // default is active
}

$et_params = array( 
	'echo_query' => 0,
	'active' => $active,
	'sort_list' => array(
		array(
			'order_by' => 'et.`active`',
			'sort' => 'DESC'
		 ),
		 array(
			'order_by' => 'et.`template_name`',
			'sort' => 'ASC'
		 )
	)
);			
$et_sql = $crm->getEmailTemplates($et_params);

?>
<style>
#btn_add_div{
	text-align: left;
	margin-top: 10px;
}
#template_tbl th, #template_tbl td{
	text-align: left;
}
.colorItGreen{
	color: green;
}
.colorItRed{
	color: red;
}
</style>


    
    <div id="mainContent">
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title; ?></strong></a></li>				
		</ul>
	</div>
      
    
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Submission Successful</div>
	<?php
	}else if($_GET['del_success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Delete Successful</div>
	<?php	
	}else if($_GET['update_success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Update Successful</div>
	<?php	
	}
	?>
	
	
	<div class="aviw_drop-h" style="border: 1px solid #ccc;">
	
		<form id="form_search" method="post" action="<?php echo $current_page; ?>">
		
			<div class="fl-left" style="float: left;">
				<label style="margin-right: 9px;">Display: </label>
				<select name="active">
					<option value="-1" <?php echo ( $active == '' )?'selected="selected"':''; ?>>ALL</option>	
					<option value="1" <?php echo ( $active == 1 )?'selected="selected"':''; ?>>Active</option>
					<option value="0" <?php echo ( is_numeric($active) && $active == 0)?'selected="selected"':''; ?>>Inactive</option>						
				</select>
			</div>
			
			<div class="fl-left" style="float:left; margin-left: 10px;">				
				<button class="submitbtnImg" id="btn_submit" type="submit">
					<img class="inner_icon" src="images/search-button.png" />
					Search
				</button>				
			</div>	
			
		</form>

	</div>
	

	<table id="template_tbl" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;">
			<tr class="toprow jalign_left">				
				<th>Template Name</th>
				<th>Subject</th>	
				<th>Type</th>
				<th>Call Centre</th>
				<th>Active</th>	
			</tr>
			<?php				
			if( mysql_num_rows($et_sql)>0 ){
				$i = 0;
				while($et = mysql_fetch_array($et_sql)){ 


				?>
					<tr class="body_tr jalign_left" <?php echo ($i%2==0)?'':'style="background-color:#eeeeee"'; ?>>						
						<td><a href="email_template_details.php?id=<?php echo $et['email_templates_id']; ?>"><?php echo $et['template_name']; ?></a></td>
						<td><?php echo $et['subject']; ?></td>
						<td><?php echo $et['ett_name'] ?></td>
						<td class="<?php echo ($et['show_to_call_centre']==1)?'colorItGreen':'colorItRed'; ?>"><?php echo ($et['show_to_call_centre']==1)?'Yes':'No'; ?></td>
						<td class="<?php echo ($et['et_active']==1)?'colorItGreen':'colorItRed'; ?>"><?php echo ($et['et_active']==1)?'Yes':'No'; ?></td>
					</tr>
				<?php
				$i++;
				}
				?>
			

				
			<?php	
			}else{ ?>
				<tr><td colspan="100%" align="left">Empty</td></tr>
			<?php	
			}
			?>			
		</table>
		
		
		
		
		<div id="btn_add_div">
			<a href="create_email_template.php">
				<button type="button" id="btn_add" class="submitbtnImg">
					<img class="inner_icon" src="images/add-button.png" /> Add
				</button>
			</a>
		</div>
		
    
  </div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	
	
	
});
</script>
</body>
</html>
