<?php

$title = "Voice Recordings";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$source_path = "voice/HOSTED_STUFF/rec";
$folder = ($_GET['folder']!='')?mysql_real_escape_string($_GET['folder']):$source_path; // traversing to folders
$folder_name = mysql_real_escape_string($_GET['folder_name']);
$scan_path   = "{$_SERVER['DOCUMENT_ROOT']}{$folder}";
$folders_arr = array_diff(scandir($scan_path,1), array('..', '.'));

?>
<style>
.jalign_left{
	text-align:left;
}
.res_hid_data{
	display:none;
}
</style>

<link rel="stylesheet" type="text/css" href="/jquery_multiselect/css/jquery.multiselect.css" />
<script type="text/javascript" src="/jquery_multiselect/js/jquery.multiselect.js"></script>
<div id="mainContent">

	
   
    <div class="sats-middle-cont">
	
		
	
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first" <?php echo ($folder=='voice/HOSTED_STUFF/rec')?'style="font-weight: bold;"':''; ?>><a title="<?php echo $title ?>" href="voice.php"><?php echo $title ?></a></li>
			<?php
			if( $folder!='voice/HOSTED_STUFF/rec' ){ ?>
				<li class="other first"  <?php echo ($folder!='voice/HOSTED_STUFF/rec')?'style="font-weight: bold;"':''; ?>><a title="<?php echo $folder ?>" href="voice.php?folder=<?php echo $folder; ?>&folder_name=<?php echo $folder_name; ?>"><?php echo $folder_name ?></a></li>	
			<?php
			}
			?>
		</ul>
		</div>
		
		

		
		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">
			<tr class="toprow jalign_left">
				<th>Dates</th>	
			</tr>
			<?php
			foreach( $folders_arr as $ds ){ 
			
			$dir = $scan_path.'/'.$ds;
			$path = "{$folder}/{$ds}";
			
			if(is_dir($dir)){ // if it's a folder, then traverse
				$link_url = "voice.php?folder={$path}&folder_name={$ds}";
				$new_tab = false;
			}else{ // if it's a file
				$link_url = "/{$path}";
				$new_tab = true;
			} 
			
			?>
				<tr>
					<td style="text-align:left;">
						<a <?php echo ($new_tab==true)?'target="_blank"':''; ?> href="<?php echo $link_url; ?>"><?php echo $ds; ?></a>
					</td>					
				</tr>
			<?php	
			}
			?>			
		</table>
		
		<?php
		if( $folder!='voice/HOSTED_STUFF/rec' ){ ?>
			<a style="float:left;" href="/voice.php">Back</a>
		<?php
		}
		?>
		

		
	</div>
</div>

<br class="clearfloat" />
</body>
</html>