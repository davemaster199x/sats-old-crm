<?

$title = "Franchise Groups";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$encrypt = new cast128();
$encrypt->setkey(SALT);

?>
  <div id="mainContent">
  
  <div class="sats-middle-cont">
  
  <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http:/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Franchise Groups" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Franchise Groups</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>  
  
    <?php
	    // get sats agency regions
		$fg_str = "
			SELECT * 
			FROM `franchise_groups`
			WHERE `country_id` = {$_SESSION['country_default']}
			ORDER BY `name`
			";
		$fg_sql = mysql_query($fg_str);		
	?>

<?php
if($_GET['success']){
	switch($_GET['success']){
		case 1:
			$msg = "<div class='success'>Update Successful!</div>";
		break;
		case 2:
			$msg = "<div class='success'>Delete Successful!</div>";
		break;
		case 3:
			$msg = "<div class='success'>New Franchise Group Added!</div>";
		break;
		case -3:
			$msg = "<div class='error'>Username already exist</div>";
		break;
	}
	echo $msg;	
}
?>

<style>
.fgroup-top{ background-color: #ececec; border: 1px solid #ccc; border-bottom: none; height: 30px;   padding-bottom: 5px; overflow: hidden;}
.fgroup-top .fl-left {
    float: left;
    margin-left: 5px;
    margin-top: 2px;
}
</style>

<div class="fgroup-top">

<div class="fl-left">
	<span id="add_div" style="display:none;">
		Franchise Group Name: <input type="text" id="add_fg_name" />
		Username: <input type="text" id="add_fg_user" />
		Password: <input type="password" id="add_fg_pass" />
		<button id="btn_add2" class="submitbtnImg">Add</button>
	</span> 	
	<button id="btn_add" class="submitbtnImg">Add Franchise Groups</button>
</div>
<div class="fl-left"></div>

</div>

		  <table border=0 cellspacing=1 cellpadding=5 width=100% class="table-left tbl-fr-red">
<tr bgcolor="#b4151b">
<td style="color: white;" class="bold">Franchise Group</td>
<td style="color: white;" class="bold">Number of Offices</td>
<td style="color: white;" class="bold">Username</td>
<td style="color: white;" class="bold">Password</td>
<td style="color: white; width: 165px;" class="bold">Edit</td>
<td style="color: white; width: 68px;" class="bold">Delete</td>
</tr>
 
<?php

	$odd=0;

   // (3) While there are still rows in the result set,
   // fetch the current row into the array $row
   while($fg = mysql_fetch_array($fg_sql)){

   $odd++;
	if (is_odd($odd)) {
		echo "<tr class='bg-white'>";		
		} else {
		echo "<tr class='bg-grey-light'>";
   		}	
		
	?>
    
    <td>
		<input type="hidden" name="fg_id" class="fg_id" value="<?php echo $fg['franchise_groups_id']; ?>" /> 
		<div class="txt_lbl orig_fg_name"><a href="/franchise_group_agencies.php?fg_id=<?php echo $fg['franchise_groups_id']; ?>"><?php echo $fg['name']; ?></a></div>
		<div><input type="text" style="display: none;" name="fg_name" class="txt_hid fg_name" value="<?php echo $fg['name']; ?>" /></div>
	</td>
	<?php
	$a_sql = mysql_query("
		SELECT count( `agency_id` ) AS jcount
		FROM `agency`
		WHERE `franchise_groups_id` ={$fg['franchise_groups_id']}
		AND `status` = 'active'
	");
	$a = mysql_fetch_array($a_sql);
	?>
	<td><?php echo $a['jcount']; ?></td>
	<td>
		<div class="txt_lbl orig_fg_user"><?php echo $fg['username']; ?></div>
		<input type="text" style="display: none;" name="fg_user" class="txt_hid fg_user" value="<?php echo $fg['username']; ?>" />
	</td>
	<td>
		<?php $pass = ($fg['password']!="")?$encrypt->decrypt(utf8_decode($fg['password'])):''; ?>
		<div class="txt_lbl orig_fg_pass"><?php echo $pass; ?></div>
		<input type="text" style="display: none;" name="fg_pass" class="txt_hid fg_pass" value="<?php echo $pass; ?>" />
	</td>
	<td>
		<a href="javascript:void(0);" class="btn_edit">Edit</a>
		<button class="blue-btn submitbtnImg btn_update" style="display: none;">Update</button>
		<button class="submitbtnImg btn_cancel" style="display: none;">Cancel</button>
	</td>
    <td><a href="javascript:void(0);" class="btn_delete">Delete</a></td>
    </tr>
    <?php } ?>
	


</table>
            
    


   
  </div>
  
  </div>
  
<br class="clearfloat">

</body>
<script>
jQuery(document).ready(function(){

	jQuery("#btn_add").toggle(function(){	
		jQuery("#add_div").show();
		jQuery(this).html("Cancel");
	},function(){
		jQuery("#add_div").hide();
		jQuery(this).html("Add Franchise Groups");
	});	
	
	jQuery("#btn_add2").click(function(){	
		var add_fg_name = jQuery("#add_fg_name").val();	
		var add_fg_user = jQuery("#add_fg_user").val();	
		var add_fg_pass = jQuery("#add_fg_pass").val();	
			jQuery.ajax({
				type: "POST",
				url: "ajax_add_franchise_group.php",
				data: { 
					add_fg_name: add_fg_name,
					add_fg_user: add_fg_user,
					add_fg_pass: add_fg_pass
				}
			}).done(function( ret ){
				window.location="/franchise_groups.php?success="+ret;				
			});	
	});

	jQuery(".btn_edit").click(function(){	
		jQuery(this).parents("tr:first").find(".btn_update").show();
		jQuery(this).parents("tr:first").find(".btn_edit").hide();
		jQuery(this).parents("tr:first").find(".btn_cancel").show();
		jQuery(this).parents("tr:first").find(".txt_hid").show();
		jQuery(this).parents("tr:first").find(".txt_lbl").hide();	
	});	
	
	jQuery(".btn_cancel").click(function(){		
		jQuery(this).parents("tr:first").find(".btn_update").hide();
		jQuery(this).parents("tr:first").find(".btn_edit").show();
		jQuery(this).parents("tr:first").find(".btn_cancel").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".txt_hid").hide();			
	});

	jQuery(".btn_update").click(function(){	
		var fg_id = jQuery(this).parents("tr:first").find(".fg_id").val();
		var fg_name = jQuery(this).parents("tr:first").find(".fg_name").val();
		var fg_user = jQuery(this).parents("tr:first").find(".fg_user").val();
		var fg_pass = jQuery(this).parents("tr:first").find(".fg_pass").val();		
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_franchise_group.php",
			data: { 
				fg_id: fg_id,
				fg_name: fg_name,
				fg_user: fg_user,
				fg_pass: fg_pass
			}
		}).done(function( ret ){
			window.location="/franchise_groups.php?success=1";
		});				
	});
	
	jQuery(".btn_delete").click(function(){	
		var fg_id = jQuery(this).parents("tr:first").find(".fg_id").val();	
		var orig_fg_name = jQuery(this).parents("tr:first").find(".orig_fg_name").html();
		if(confirm("Are you sure you want to delete")){
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_franchise_group.php",
				data: { 
					fg_id: fg_id
				}
			}).done(function( ret ){
				if(parseInt(ret)==1){
					alert("Agencies still attached to "+orig_fg_name);
				}else{
					window.location="/franchise_groups.php?success=2";
				}	
			});	
		}
	});

});
</script>
</html>
