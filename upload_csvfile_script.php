<?

$title = "Import Properties Preview";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');




?>
<style>
#load-screen {
	width: 100%;
	height: 100%;
	background: url("/images/loading.gif") no-repeat center center #fff;
	position: fixed;
	opacity: 0.7;
	z-index: 9999999999;
}
</style>
<div id="load-screen"></div>
<div id="mainContent">
  
  <div class="sats-middle-cont">
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="/import_properties.php"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
	


    <div class="jalign_left">	
	
		<div style="margin: 40px 0 0;text-align: left;">
	
		<form id="jform" method="POST" action="upload_csvfile_script_final.php">
		<?php


		// data
		$agency = mysql_real_escape_string($_POST['agency']);
		$upload_type = mysql_real_escape_string($_POST['upload_type']);

		/*
		echo "Agency: {$agency}<br />";
		echo "Upload type: {$upload_type}<br />";
		*/

		$sucess = "";
		$error = "";

			if($_FILES){

				$title = $_POST['title'];
				$states = implode(',',$_POST['states']);	
				
				$folder = "temp_upload";
				if ($_FILES["file"]["error"] > 0){
					$error .= "Return Code: " . $_FILES["file"]["error"] . "<br>";	
				}
				
				if($_FILES["file"]["size"] > 4000000){
					$error .= "Uploaded file is too large, file size limit is 4mb<br />";
					// limit file size to 4mb 						
				}
				
				if(file_exists("{$folder}/" . $_FILES["file"]["name"])){
					$error .= $_FILES["file"]["name"] . " already exists.<br />";
				}	
				
				if($_FILES["file"]["type"]!='text/csv' && $_FILES["file"]["type"]!='application/vnd.ms-excel' && $_FILES["file"]["type"]!='application/csv'){
					$error .= "File must be CSV format.<br />";
				}

				if($error==""){
				
					// if folder does not exist, make one
					if(!is_dir($folder)){
						mkdir($folder);
					}
					
					// upload file
					$file_path = "{$folder}/" . $_FILES["file"]["name"];
					if(move_uploaded_file($_FILES["file"]["tmp_name"],$file_path)){	
						
						// open csv file
						$file = fopen($file_path,"r");
						$csv_array = array();
						
						// extracts csv data and store it on array 
						while(! feof($file)){
							$csv_array[] = fgetcsv($file);	
						}
											
						fclose($file);
						
						if($file_path!=''){
							// delete csv file
							unlink($file_path);	
						}
						
						
						//echo "Agency: {$agency} <br />";
						
						
						
						
						
						// check if property already exist
						$prop_exist = array();
						foreach($csv_array as $index=>$csv){
							$street_num = str_replace("*","/",mysql_real_escape_string(trim($csv[0])));
							// exclude 1st row, header
							if($index!=0&&$csv!=""){
								if( $street_num!="" && $csv[1]!="" && $csv[2]!="" && $csv[3]!="" && $csv[4]!="" ){
									
									// extracted property
									$prop_extract = array(
										"street_num" => trim($csv[0]),
										"street_name" => trim($csv[1]),
										"suburb" => trim($csv[2]),
										"state" => trim($csv[3]),
										"postcode" => trim($csv[4]),
										
										"landlord_fname" => trim($csv[5]),
										"landlord_lname" => trim($csv[6]),
										"landlord_email" => trim($csv[7]),
										
										"tenant_fname1" => trim($csv[8]),
										"tenant_lname1" => trim($csv[9]),
										"tenant_phone1" => trim($csv[10]),
										"tenant_mobile1" => trim($csv[11]),
										"tenant_email1" => trim($csv[12]),
										
										"tenant_fname2" => trim($csv[13]),
										"tenant_lname2" => trim($csv[14]),
										"tenant_phone2" => trim($csv[15]),
										"tenant_mobile2" => trim($csv[16]),
										"tenant_email2" => trim($csv[17]),
																					
										"tenant_fname3" => trim($csv[18]),
										"tenant_lname3" => trim($csv[19]),
										"tenant_phone3" => trim($csv[20]),
										"tenant_mobile3" => trim($csv[21]),
										"tenant_email3" => trim($csv[22]),
										
										"tenant_fname4" => trim($csv[23]),
										"tenant_lname4" => trim($csv[24]),
										"tenant_phone4" => trim($csv[25]),
										"tenant_mobile4" => trim($csv[26]),
										"tenant_email4" => trim($csv[27]),										
										
										"key_number" => trim($csv[28]),
										"comments" => trim($csv[29])
									);
									
									$p_sql = mysql_query("
										SELECT *
										FROM `property` 
										WHERE TRIM(LCASE(`address_1`)) = LCASE('". mysql_real_escape_string($street_num) ."') 
										AND TRIM(LCASE(`address_2`)) = LCASE('". mysql_real_escape_string(trim($csv[1]))."') 
										AND TRIM(LCASE(`address_3`)) = LCASE('". mysql_real_escape_string(trim($csv[2])) ."') 
									");
									// PROPERTY already exist
									if(mysql_num_rows($p_sql)>0){
										//$prop_exist[] = "{$street_num} {$csv[1]} {$csv[2]} {$csv[3]} {$csv[4]}";
										//$street_num = str_replace("*","/",mysql_real_escape_string(trim($csv[0])));
										$prop_exist2[] = $prop_extract;
									}else{
										$prop_ok[] = $prop_extract;
									}
								}												
							}
						}
						
						/*
						echo "Properties: <br />";
						echo "<pre>";
						print_r($prop_ok);
						echo "</pre>";
						
						
						echo "Duplicate Properties: <br />";
						echo "<pre>";
						print_r($prop_exist2);
						echo "</pre>";
						*/
						
						

						if(count($prop_ok)>0){
							
							switch($upload_type){
								case 'nr':
									$upload_type_msg = 'Properties will be uploaded with ALL Services as "No Response"';
								break;
								case 'sats':
									$upload_type_msg = 'Properties will be uploaded with Smoke Alarms as SATS and other services as "No Response"';
								break;
								case 'mixed':
									$upload_type_msg = 'Please SELECT Services for ALL properties then press CONTINUE';
								break;
							}
							
							echo '<div class="success_blue">'.$upload_type_msg.'</div>';
							
						}
						
						$total_prop = (count($prop_exist2)+count($prop_ok));
						
						echo "<ul>";
						echo "<li>".$total_prop." Properties in file</li>";
						
						if(count($prop_exist2)>0){
							$dup_flag = 1;
							$_SESSION['import_property_duplicates'] = $prop_exist2;
							echo "<li>".count($prop_exist2)." Duplicates found: <a href='/download_duplicate_property.php'>Download Duplicates CSV</a></li>";
						}
						
						if(count($prop_ok)>0){
							$_SESSION['imported_property'] = $prop_ok;
							echo "<li>".count($prop_ok)." Properties are ready to import</li>";
						}
						echo "</ul>";

						
							if($upload_type=='mixed'){
								
								foreach($prop_ok as $index=>$prop){
						
										echo "<p style='font-size: 16px;  margin: 5px 0;'>";
										
											echo "<span style='font:100% arial,sans-serif; font-weight: bold !important;'>{$prop['street_num']} {$prop['street_name']} {$prop['suburb']} {$prop['state']} {$prop['postcode']}</span><br />";
											// get agency services
											$agen_sql = mysql_query("
												SELECT * 
												FROM `agency_services` AS agen_srv
												LEFT JOIN `alarm_job_type` AS ajt ON agen_srv.`service_id` = ajt.`id`
												WHERE  agen_srv.`agency_id` ={$agency}
											");
											
											$i = 1;
											while( $agen = mysql_fetch_array($agen_sql) ){
												echo "<ul style='list-style: outside none none; display: inline-block; margin: 0;'>";
												echo "<li>".$agen['type']."</li>";
												echo "<li><input type='radio' name='prop{$index}_serv{$agen['service_id']}' value='1' /> SATS</li>";
												echo "<li><input type='radio' name='prop{$index}_serv{$agen['service_id']}' value='0' /> DIY</li>";
												echo "<li><input type='radio' name='prop{$index}_serv{$agen['service_id']}' checked='checked' value='2' /> No Response</li>";
												echo "<li><input type='radio' name='prop{$index}_serv{$agen['service_id']}' value='3' /> Other Provider</li>";
												echo "</ul>";
												$i++;
											}
											
												
										echo "</p>";
													
								}
								
							}
							
							
					
						
						
						
						
				}
					
					
					
					
						
				
				}else{
					echo $error;
				}
				
					
										
			}

			
			
			

		?>
		<input type="hidden" name="dup_flag" id="dup_flag" value="<?php echo $dup_flag; ?>" />
		<input type="hidden" name="agency" value="<?php echo $agency; ?>" />
		<input type="hidden" name="upload_type" value="<?php echo $upload_type; ?>" />
		<?php
		if(count($prop_ok)>0){ ?>
			<input type="submit" name="submit" class="submitbtnImg" value="CONTINUE" style="margin-right: 10px;" />
			<script>jQuery("#load-screen").hide();</script>
		<?php 
		}else{ 
			?>
			Can't proceed with upload. 
			<script>jQuery("#load-screen").hide();</script>
		<?php		
		}
		?>
		<a href="/import_properties.php">
			<button type="button" class="submitbtnImg">BACK</button>
		</a>
		
		</form>
		
		</div>
					
	</div>	
   

  </div>
  
</div>

<br class="clearfloat" />

<script>
jQuery(document).ready(function(){
	
	// safety check for download duplicate
	jQuery("#jform").submit(function(e){
		var dup_flag = jQuery("#dup_flag").val();
		if(dup_flag==1){
			
			if(confirm("Duplicates has been found. Please make sure you downloaded it first, proceed?")){
				return true;
			}else{
				return false;
				e.preventDefault();
			}
		}		
	});
	
});
</script>
</body>
</html>
