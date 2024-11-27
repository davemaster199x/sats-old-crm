<?php

$title = "Banners";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

function getBanners(){
	
	return mysql_query("
		SELECT *
		FROM `banners`	
		WHERE `active` = 1
	");

}

?>
<style>
.jalign_left{
	text-align:left;
}
</style>
<div id="mainContent">

	
   
    <div class="sats-middle-cont">
	
		
	
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="Banners" href="/banners.php"><strong>Banners</strong></a></li>
		  </ul>
		</div>
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php echo ($_GET['success']==1)?'<div class="success">Banners Updated</div>':''; ?>
		<?php echo ($_GET['error']!="")?'<div class="error">'.$_GET['error'].'</div>':''; ?>
		
		<style>		
		#banner_tbl ul li {
			padding: 5px 0;
		}
		</style>
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;" id="banner_tbl">
			<tr class="toprow">
				<?php
				$sql = getBanners();
				$i = 1;
				while($row = mysql_fetch_array($sql)){ ?>
					<th>Banner <?php echo $i; ?></th>
				<?php
				$i++;
				}
				?>
			</tr>
			<tr>
				<?php
				$sql = getBanners();
				while($row = mysql_fetch_array($sql)){ ?>
					<td>
						<form class="banner_frm" action="/update_banners.php" method="post" enctype="multipart/form-data">
							<ul style="list-style-type: none;">
								<li style="display:none;">
									<input type="text" name="banner_id" class="banner_id" value="<?php echo $row['banners_id']; ?>" />
									<input type="text" name="operation" class="operation" value="link" />
									<input type="file" name="file" class="file" />
								</li>
								<li><img src="/agency_banners/<?php echo $row['path']; ?>" /></li>
								<li><button type="button" class="submitbtnImg btn-change-banner">Change Banner</button></li>
								<li>Banner Link:</li>
								<li><input type="text" name="link" value="<?php echo $row['link']; ?>" /></li>
								<li><input type="submit" class="submitbtnImg" value="Update Banner Link" /></li>
							</ul>	
						</form>												
					</td>
				<?php
				}
				?>
			</tr>
		</table>
	
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){


	// binnd change banner button to the upload file field
	jQuery(".btn-change-banner").click(function(){
		jQuery(this).parents("ul:first").find(".file").click();
	});

	// auto-submit upon selecting file	
	jQuery(".file").change(function(){
		if(jQuery(this).val()!=""){
			jQuery(this).parents("ul:first").find(".operation").val("banner");
			jQuery(this).parents("form.banner_frm:first").submit();
		}
	});
	
	
	
});
</script>
</body>
</html>