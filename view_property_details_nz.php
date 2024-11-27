<?php

$title = "View Property Details";

include($_SERVER['DOCUMENT_ROOT'].'/inc/init_for_ajax.php');
$crm = new Sats_Crm_Class;

$id = $_GET['id'];
$country_id = 2;

// get property
$jparams = array(
	'country_id' => $country_id,
	'property_id' => $id
);
$result = $crm->getPropertyData($jparams);
$row = mysql_fetch_array($result);

$p_address = "{$row['p_address_1']} {$row['p_address_2']} {$row['p_address_3']} {$row['p_state']} {$row['p_postcode']}";

?>
<!DOCTYPE html>
<html>

<head>
<title><?php echo $title; ?></title>
<link href="css/mainsite.css" type="text/css" rel="stylesheet">
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
</head>

<body>

<h1><?php echo $title; ?></h1>

  <div id="mainContent">
  
  <div class="sats-middle-cont">

   

   
   
   
   
<div <?php echo ($row['deleted']==1)?'style="background-color:#ECECEC"':''; ?>>
<table border=1 cellpadding=0 cellspacing=0 width=100% class="view-property-table">
<tr class="padding-none">
<td class="padding-none">

<!-- Services table : start -->

<table border=0 cellspacing=1 cellpadding=5 width=100% class="table-center tbl-fr-red view-property-table-inner" id="tbl_services">

<tr bgcolor="#b4151b" class="border-none">


<?php

   		


					// Franchise Group
					$franchise_group = $row['franchise_groups_id'];
					$private_fg = 0;
					if($country_id==1){ // AU
						if($franchise_group == 10){ // AU private ID
							$private_fg = 1;
						}
					}else if($country_id==2){ // NZ
						if($franchise_group == 37){ // NZ private ID
							$private_fg = 1;
						}
					}
   
	 
	 if (mysql_num_rows($result) == 0)
        echo "<tr class='border-none'></td>No property returned, that's odd. Please report.</td></tr>";

	$odd=0;

   // (3) While there are still rows in the result set,
   // fetch the current row into the array $row

   

   $odd++;
	if (is_odd($odd)) {
		echo "<tr bgcolor=#FFFFFF class='border-none'>";		
		} else {
		echo "<tr bgcolor=#FFFFFF class='border-none'>";
   		}
	
		
		
		

		echo "<form method=post name='example' id='example' action='" . URL . "update_property.php'><input type=hidden name='id' value='" . $id . "'>\n";
		
      echo "\n";
     // (4) Print out each element in $row, that is,
     // print the values of the attributes

		$row[1] = htmlspecialchars($row[1], ENT_QUOTES);
		$row[2] = htmlspecialchars($row[2], ENT_QUOTES);
		$row[3] = htmlspecialchars($row[3], ENT_QUOTES);
/*
		echo "<td>";		
		echo "<input type='text' name='address_1' value='$row[0]' size=5><input type='text' name='address_2' value='$row[1]' size=25><input type='text' name='address_3' value='$row[2]' size=7>";
		echo "</td>";	 

		echo "<td valign=top>";		
		echo "<input type='text' name='state' value='$row[3]' size=4>";
		echo "</td>";
		
		echo "<td valign=top'>";		
		echo "<input type='text' name='postcode' value='$row[4]' size=4>";
		echo "</td>";
*/



$agency_id = $row['agency_id'];
		
		if($_REQUEST['success']){
			echo "<div class='success'>Database Updated\n</div>";
		}
		
		
		if($row['deleted'])
		{
			echo "<div id='permission_error'>This Property is Deactivated! To Restore it press the &Prime;Restore this Property&Prime; button</div>";
		}
		elseif($row['agency_deleted'])
		{
			echo "<div id='permission_error'>This property has been marked as no longer managed / deleted by the agency!</div>";
		}
		else
		{
			//echo "<form method=get action='" . URL . "change_of_tenancy.php'><input type=hidden name='id' value='" . $id . "'><input type='submit' value='Create a Job for this Property'> <select name='jobtype'><option value='ym'>Yearly Maintenance</option><option value='ct'>Change of Tenancy</option><option value='oo'>Once Off</option><option value='240v'>240v Rebook</option><option value='for'>Fix or Replace</option></select></form>\n";
		}

echo "<div class='vpd-maindv'>";

	 echo "<div class='float-left'>";
	 
	 	echo "<div class='first-div'>";
		

echo "
	<div class='vw-pro-dtl-tn-hld vpr-left'>
	<h2 class='heading'>Address</h2>
				<div class='row' style='width: auto; margin-bottom: 20px;'>
           	     <label for='fullAdd' class=''>Address</label>
           	    <input type='text' name='fullAdd' id='fullAdd' class='addinput vw-pro-dtl-tnt short-fld' style='width: 413px !important;' value='".$p_address."' />
				</div>
				
				<div style='clear:both;'></div>
				
				<div class='row'>
           	     <label for='tenant_firstname1' class=''>No.</label>
           	    <input type='text' name='address_1' id='address_1' value='".$row['p_address_1']."' class='addinput vw-pro-dtl-tnt short-fld'>
			    </div>
		    	<div class='row'>
           	     <label for='tenant_firstname1' class=''>Street</label>
                <input type='text' name='address_2' id='address_2' value='".$row['p_address_2']."' class='addinput vw-pro-dtl-tnt long-fld streetinput'>
			    </div>	 
                <div class='row'>
				<label for='tenant_lastname1' class=''>Suburb</label>
                <input type='text' name='address_3' id='address_3' value='".$row['p_address_3']."' class='addinput vw-pro-dtl-tnt big-fld'>
			</div>"; 
			
			if(ifCountryHasState($country_id)==true){
				
				echo "<div class='row' style='margin-right: 10px;'>
					<label for='tenant_mob1' class=''>State</label>";
			
				 ?>
				
					<select class="addinput vpr-adev-sel" name="state" id="state" style="width: 70px;">
						<option value="">----</option> 
						<option value="NSW" <?php echo ($row[3]=='NSW')?'selected="selected"':''; ?>>NSW</option>
						<option value="VIC" <?php echo ($row[3]=='VIC')?'selected="selected"':''; ?>>VIC</option>
						<option value="QLD" <?php echo ($row[3]=='QLD')?'selected="selected"':''; ?>>QLD</option>
						<option value="ACT" <?php echo ($row[3]=='ACT')?'selected="selected"':''; ?>>ACT</option>
						<option value="TAS" <?php echo ($row[3]=='TAS')?'selected="selected"':''; ?>>TAS</option>
						<option value="SA" <?php echo ($row[3]=='SA')?'selected="selected"':''; ?>>SA</option>
						<option value="WA" <?php echo ($row[3]=='WA')?'selected="selected"':''; ?>>WA</option>
						<option value="NT" <?php echo ($row[3]=='NT')?'selected="selected"':''; ?>>NT</option>
					 </select>
				
				<?php	
		
							
				 echo "</div>";
				
			}else{ ?>
			
				<div class='row' style='margin-right: 10px;'>
				<label for='tenant_mob1' class=''>Region</label>
				<input type='text' name='state' id='state' value='<?php echo $row['p_state']; ?>' class='addinput vw-pro-dtl-tnt big-fld'>
				</div>
				
			<?php	
			}
			
			
			 
			    echo "<div class='row'>
				<label for='tenant_ph1' class=''>Postcode</label>
                <input type='text' style='width: 44px;' name='postcode' id='postcode' value='".$row['p_postcode']."' class='addinput vw-pro-dtl-tnt'>
			</div>	
	";
echo "</div>"; 	
		
echo "</div>"; 	
 // Ends first-div	
 
 
 echo "
	<div class='vw-pro-dtl-tn-hld vpr-left clear' style='margin-top: 16px;'>
	<input type='hidden' name='tenants_changed' id='tenants_changed' value='0' />
	";
		
	   $row[28] = htmlspecialchars($row[28], ENT_QUOTES);
		$row[29] = htmlspecialchars($row[29], ENT_QUOTES);
		
		//echo "agency id: {$row1[0]}";
		$dha_agencies = array(
			3043, 	
			3036,
			3046,
			1902, 	
			3044,
			1906,
			1927,
			3045
		);
		$td_txt = (in_array($row1[0], $dha_agencies))?'Member Details':'Tenant Details';
	
	echo " <div style='overflow: hidden;'><h2 class='heading tntdetail'>{$td_txt}</h2>
	
	<div style='margin-top: 21px; margin-left: 10px; float: left;color:#00D1E5;text-align: left;margin-bottom: 8px; font-size: 13px;'>".(($row['tenant_changed']!="0000-00-00 00:00:00")?'Last Updated: '.date("d/m/Y",strtotime($row['tenant_changed'])):'')."</div>
	</div>
	</div>
	
	<div style='clear:both'></div>
	
	<div class='jtenant_div'>
	<div class='vw-pro-dtl-tn-hld vpr-left'>
		<div style='float: left; margin-left: 224px; white-space: normal; width: 100px; visibility: hidden;' class='tenant_mobile_error jinvalid_format'>0412 222 222</div>
		<div style='float: left; margin-left: 22px; white-space: normal; width: 100px; visibility: hidden;' class='tenant_phone_error jinvalid_format'>02 2222 2222</div>
	</div>
	
	<div style='clear:both'></div>
	
	<div class='vw-pro-dtl-tn-hld vpr-left'>
				<div class='row'>
				 <label for='tenant_firstname1' class=''>First Name</label>
           	     <input class='addinput vw-pro-dtl-tnt tenant_fields' type=text name='tenant_firstname1' value='$row[28]'>
			    </div>
				
				<div class='row'>
				 <label for='tenant_lastname1' class=''>Last Name</label>
           	     <input class='addinput vw-pro-dtl-tnt tenant_fields' type=text name='tenant_lastname1' value='$row[29]'>
			    </div>
				
				<div class='row'>
				 <label for='tenant_mob1' class=''>Mobile</label>
           	     <input class='addinput vw-pro-dtl-tnt tenant_fields tenant_mobile_field' type=text name='tenant_mob1' value='{$row['tenant_mob1']}'>
			    </div>
				
				<div class='row'>
				 <label for='tenant_ph1' class=''>Landline</label>
           	     <input class='addinput vw-pro-dtl-tnt tenant_fields tenant_phone_field' type=text name='tenant_ph1' value='{$row['tenant_ph1']}'>
			    </div>
				
				<div class='row'>
				 <label for='tenant_email1' class=''>Email</label>
           	     <input class='tenantinput-large addinput vw-pro-dtl-tnt vpd-bg-email tenant_fields' type=text name='tenant_email1' value='{$row['tenant_email1']}'>
			    </div>";
				
	echo "</div>
	</div>"; 	
	
	
	echo "
	
	<div style='clear:both'></div>
	
	<div class='jtenant_div'>
	<div class='vw-pro-dtl-tn-hld vpr-left'>
		<div style='float: left; margin-left: 224px; white-space: normal; width: 100px; visibility: hidden;' class='tenant_mobile_error jinvalid_format'>0412 222 222</div>
		<div style='float: left; margin-left: 22px; white-space: normal; width: 100px; visibility: hidden;' class='tenant_phone_error jinvalid_format'>02 2222 2222</div>
	</div>
	
	<div style='clear:both'></div>
	";   
	
 echo "
	<div class='vw-pro-dtl-tn-hld vpr-left clear'>";	
	
	   $row[31] = htmlspecialchars($row[31], ENT_QUOTES);
	   $row[32] = htmlspecialchars($row[32], ENT_QUOTES);	
	
	echo "
				<div class='row'>
           	     <input class='addinput vw-pro-dtl-tnt tenant_fields' type=text name='tenant_firstname2' value='{$row['tenant_firstname2']}'>
			    </div>
				
				<div class='row'>
           	     <input class='addinput vw-pro-dtl-tnt tenant_fields' type=text name='tenant_lastname2' value='{$row['tenant_lastname2']}'>
			    </div>
				
				<div class='row'>
           	     <input class='addinput vw-pro-dtl-tnt tenant_fields tenant_mobile_field' type=text name='tenant_mob2' value='{$row['tenant_mob2']}'>
			    </div>
				
				<div class='row'>
           	     <input class='addinput vw-pro-dtl-tnt tenant_fields tenant_phone_field' type=text name='tenant_ph2' value='{$row['tenant_ph2']}'>
			    </div>
				
				<div class='row'>
           	     <input class='tenantinput-large addinput vw-pro-dtl-tnt vpd-bg-email tenant_fields' type=text name='tenant_email2' value='{$row['tenant_email2']}'>
			    </div>";
				
	echo "</div>
	</div>"; 


	
	
	 echo "
	<div class='vw-pro-dtl-tn-hld vpr-left clear'>";
		
	    $row[34] = htmlspecialchars($row[34], ENT_QUOTES);
		$row[35] = htmlspecialchars($row[35], ENT_QUOTES);
	
	echo " <h2 class='heading'>Landlord Details</h2>
	
	<div class='jtenant_div'>
	<div class='vw-pro-dtl-tn-hld vpr-left'>
		<div style='float: left; margin-left: 224px; white-space: normal; width: 100px; visibility: hidden;' class='tenant_mobile_error jinvalid_format'>0412 222 222</div>
		<div style='float: left; margin-left: 22px; white-space: normal; width: 100px; visibility: hidden;' class='tenant_phone_error jinvalid_format'>02 2222 2222</div>
	</div>
	
	<div style='clear:both'></div>
	";
	
	
	
	
	
	
				echo "<div class='row'>
				 <label for='landlord_firstname' class=''>First Name</label>
           	     <input class='addinput vw-pro-dtl-tnt' type=text name='landlord_firstname' value='".$row['landlord_firstname']."'>
			    </div>
				
				<div class='row'>
				 <label for='landlord_lastname' class=''>Last Name</label>
           	     <input class='addinput vw-pro-dtl-tnt' type=text name='landlord_lastname' value='".$row['landlord_lastname']."'>
			    </div>
				
				";
				
				
				
				// if franchise group = private
				if( $private_fg == 1 ){
					
					echo "<div class='row'>
					 <label for='ll_mobile' class=''>Mobile</label>
					 <input class='addinput vw-pro-dtl-tnt tenant_mobile_field' type=text name='ll_mobile' id='ll_mobile' value='".$row['landlord_mob']."' />
					</div>
					
					<div class='row'>
					 <label for='ll_landline' class=''>Landline</label>
					 <input class='addinput vw-pro-dtl-tnt tenant_phone_field' type=text name='ll_landline' id='ll_landline' value='".$row['landlord_ph']."' />
					</div>				
					";
					
				}
				
	
echo "
				<div class='row'>
				 <label for='landlord_email' class=''>Email</label>
           	     <input class='tenantinput-large addinput vw-pro-dtl-tnt vpd-bg-email' type=text name='landlord_email' value='".$row['landlord_email']."'>
			    </div>
				
				
	</div>			
</div>"; 




	 
 
		
	 
	 echo "</div>";
	 // Ends Float Left	
	 
	 
	 echo "<div class='float-left'>";
	 
	 
	  echo "
	<div class='vw-pro-dtl-tn-hld vpr-left'>";		
	
	echo "<h2 class='heading'>Agency</h2>
				<div class='row'>
				<div class='vw-pr-fl'>".$row['agency_name']."</div> 
			    </div>";
echo "</div>"; 




echo "
	<div class='vw-pro-dtl-tn-hld vpr-left ex-pad clear' style='margin-top: 10px;'>";
	
	echo "<h2 class='heading'>Property Notes</h2>
				<div class='row'>
				 <label for='key_number' class=''>Notes</label>
				 <textarea name='prop_comments' class='addtextarea vpr-adev-txt'>". $row['p_comments']."</textarea>
			    </div>";
echo "</div>"; 



				 
		// get smoke alarm sats
		$sa_serv_sql = mysql_query("
			SELECT *
			FROM `property_services`
			WHERE `property_id` ={$id}
			AND `alarm_job_type_id` =2
			AND service =1
		");
		// display only for smoke serviced to sats
	
			
	
		
		
		
				 
		
		
		
		
		// Keys, alarms and more details
		echo "<div style='clear:both;'></div>";
		
		
echo "
<div class='vw-pro-dtl-tn-hld vpr-left'>";

$row[34] = htmlspecialchars($row[34], ENT_QUOTES);
$row[35] = htmlspecialchars($row[35], ENT_QUOTES);

echo "<h2 class='heading'>Keys</h2>
	<div class='row'>
	 <label for='key_number' class=''>Key Number</label>
	 <input id='key_number' style='width: 80px;' class='key_number addinput vw-pro-dtl-tnt' type='text' name='key_number' value='" . $row['key_number'] . "'>
	</div>";
echo "</div>"; 



echo "
<div class='vw-pro-dtl-tn-hld vpr-left'>";

$row[34] = htmlspecialchars($row[34], ENT_QUOTES);
$row[35] = htmlspecialchars($row[35], ENT_QUOTES);

echo "<h2 class='heading'>Alarms</h2>
	<div class='row'>
	 <label for='key_number' class=''>Alarm Code</label>
	 <input id='alarm_code' style='width: 80px;' class='alarm_code addinput vw-pro-dtl-tnt' type='text' name='alarm_code' value='" . $row['alarm_code'] . "'>
	</div>";
echo "</div>"; 


echo "
<div class='vw-pro-dtl-tn-hld vpr-left'>";

$row[34] = htmlspecialchars($row[34], ENT_QUOTES);
$row[35] = htmlspecialchars($row[35], ENT_QUOTES);

echo "<h2 class='heading'>More Details</h2>";

	echo "<div class='row'>
	 <label for='holiday_rental' class=''>Short Term Rental</label>
	 <input id='holiday_rental' class='holiday_rental addinput vw-pro-dtl-tnt' style='width:auto;' type='checkbox' name='holiday_rental' ".(($row['holiday_rental']==1)?'checked="checked"':'')." value='1' />
	</div>";
	
	echo "<div class='row' style='margin-left: 10px;'>
	 <label for='no_keys' class=''>No Keys</label>
	 <input id='no_keys' class='no_keys addinput vw-pro-dtl-tnt' style='width:auto;' type='checkbox' name='no_keys' ".(($row['no_keys']==1)?'checked="checked"':'')." value='1' />
	</div>";
	
	echo "<div class='row' style='margin-left: 10px;'>
	 <label for='no_keys' class=''>NO Entry Notice</label>
	 <input id='no_en' class='no_en addinput vw-pro-dtl-tnt' style='width:auto;' type='checkbox' name='no_en' ".(($row['no_en']==1)?'checked="checked"':'')." value='1' />
	</div>";
	
echo "</div>"; 
		
		
	echo "</div>"; 
	
	?>
	
	
	
	 <?php
	 
	 echo "</div>";
	 // Ends Float Left	
	 
	 
	 
	
	 

echo "</div>";
 // Ends vpd-maindv		
 
 
 
 
 
 echo "<div class='vpd-maindv'>";


echo "</div>";
 // Ends vpd-maindv	

        
        echo "<tr bgcolor=#b4151b class='border-none align-left'>";
		echo "<td class='colorwhite bold'>Services</td>";
		echo "<td class='colorwhite bold'>Prices</td>";
		echo "<td class='colorwhite bold'>Service Status</td>";


		/*
		echo "<td class='colorwhite bold'>Alarm Prices</td>";
		*/
		//echo "<td class='colorwhite bold'>Retest Date</td>";
		/*
		echo "<td class='colorwhite bold'></td>";
		echo "<td class='colorwhite bold'></td>";
		*/
		echo "</tr>";
				
	
		?>
		
		<?php
		$ps_sql = mysql_query("
			SELECT *
			FROM `agency_services` AS ps
			LEFT JOIN `alarm_job_type` AS ajt ON ps.`service_id` = ajt.`id`
			WHERE ps.`agency_id` = {$agency_id}
			AND ajt.`active` = 1
			ORDER BY `agency_services_id` ASC
		");
		?>
		
			<?php
			$i = 0;
			if(mysql_num_rows($ps_sql)>0){
				while($ps = mysql_fetch_array($ps_sql)){ ?>
				
					<?php
						$pp_sql = mysql_query("
							SELECT *
							FROM `property_services` AS ps 
							LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
							WHERE ps.`alarm_job_type_id` = {$ps['id']}
							AND ps.`property_id` = {$id}
							AND ajt.`active` = 1
						");
						
						if(mysql_num_rows($pp_sql)>0){
							$pp = mysql_fetch_array($pp_sql);	
							$property_services_id = $pp['property_services_id'];
							$alarm_job_type_id = $pp['alarm_job_type_id'];
							$serv = $pp['service'];
							$price = $pp['price'];							
						}else{	
							$property_services_id = '';
							$alarm_job_type_id = $ps['service_id'];
							$serv = "";
							$price = $ps['price'];
						}						
						?>
					<tr  class="border-none align-left">
					<td style="display:none;">
						<input type="hidden" name="property_services_id[]" class="property_services_id" value="<?php echo $property_services_id; ?>">
						<input type="hidden" name="alarm_job_type_id[]" class="alarm_job_type_id" value="<?php echo $alarm_job_type_id; ?>">
						<input type="hidden" name="price[]" class="price" value="<?php echo $price; ?>">
						<input type="hidden" name="service_name[]" class="service_name" value="<?php echo $ps['type']; ?>">
						<input type="hidden" name="is_updated[]" class="is_updated" value="0" />
						<input type="hidden" name="service_status[]" class="service_status" value="<?php echo $serv; ?>" />
						<?php
						if($alarm_job_type_id==2 && $serv==1){ ?>
							<input type="hidden" id="hid_smoke_price" value="<?php echo $price; ?>" />
						<?php	
						}
						?>
						
					</td>
					<td>
						<?php echo $ps['type']; ?>
					</td>
					<td>
						$<?php echo $price; ?>						
					</td>	
					
					<td style="width: 30%;">		
						<?php
						switch($serv){
							case 0:
								$serv_name_txt = 'DIY';
							break;
							case 1:
								$serv_name_txt = 'SATS';
							break;
							case 2:
								$serv_name_txt = 'No Response';
							break;
							case 3:
								$serv_name_txt = 'Other Provider';
							break;
						}				
						echo $serv_name_txt;
						?>						
					</td>	

					
				<!--
					<td>
					<?php
					/*
					if($alarm_job_type_id==2){
						$pa_sql = mysql_query("
							SELECT *
							FROM `property_alarms` AS pa
							LEFT JOIN `alarm_pwr` AS apwr ON pa.`alarm_pwr_id` = apwr.`alarm_pwr_id`
							WHERE `property_id` = {$id}
						");
						if(mysql_num_rows($pa_sql)>0){							
						?>
							<ul style="padding: 0;list-style-type: none;">
							<?php while($pa = mysql_fetch_array($pa_sql)){ ?>
								<li><?php echo $pa['alarm_pwr'] ?> - $<?php echo $pa['price'] ?></li>
							<?php } ?>
							</ul>
						<?php							
						}
					}
					*/
					?>
					
					</td>
					-->
					
					<?php
					$querydate = "SELECT MAX(j.created), j.retest_interval FROM jobs j, property p WHERE (j.property_id = p.property_id AND p.property_id = $id) GROUP BY j.property_id";
					$resultdate = mysql_query ($querydate, $connection);
					
					$retestd_date = null;
					if (mysql_num_rows($resultdate) != 0){
						while($row_date = mysql_fetch_row($resultdate)){
							$retestd_date = $row_date[0];
							$dt = new DateTime($retestd_date);
							$dt->modify('+365 days');
							$retestd_date = $dt->format('d-m-Y'); 
						}
					}
					?>
					
					<!--<td><?php echo $retestd_date; ?></td>-->
					</tr>
				<?php
				$i++;
				}
			}
			?>
	
		</table>
		<!-- Services table : end -->
		
		<!-- Job type table : start -->
		<table border=0 cellspacing=1 cellpadding=5 width=100% class="table-center tbl-fr-red view-property-table-inner" id="tbl_job_type">
		
		<?php
	       


		echo "<tr bgcolor=#b4151b  class='border-none align-left'>
		<td class='colorwhite bold '>Job Type</td>
		<td class='colorwhite bold'>Service</td>
		<td class='colorwhite bold'>Price</td>
		<td class='colorwhite bold'>Total Price</td>
		<td class='colorwhite bold'>Date</td>
		<td class='colorwhite bold'>Job Status</td>";
		
		/*
		echo "<td class='colorwhite bold' align='center'>Invoice</td>
		<td class='colorwhite bold' align='center'>Certificate</td>
		<td class='colorwhite bold' align='center'>Combined</td>";
		*/
		
		echo "</tr>\n";
		
		$query3 = "
			SELECT 
				j.job_type, 
				DATE_FORMAT(j.date,'%d/%m/%Y'), 
				j.status, j.id, 
				j.`service`,
				j.`job_price`,
				j.`tech_id`
			FROM jobs j, property p 
			WHERE (j.property_id = p.property_id AND p.property_id = $id) AND j.`del_job` = 0 ORDER BY j.date DESC";
		$result3 = mysql_query ($query3, $connection);
		if (mysql_num_rows($result3) == 0)
			echo "<tr class='align-left'><td colspan=3>No jobs have been created for this Property.</td></tr>\n";
			
		while ($row3 = mysql_fetch_row($result3))
			{
			
			
				//get service
				$s_sql = mysql_query("
					SELECT * 
					FROM `alarm_job_type`
					WHERE `id` = {$row3[4]}
				");
				$s = mysql_fetch_array($s_sql);
				$service = $s['type'];
				
				
			
				$retest_date = $row3[1];
				if($row[0] == 'Yearly Maintenance'){
					$dt = new DateTime($retest_date);
					$dt->modify('+365 days');
					$retest_date = $dt->format('d/m/Y'); 
				}
				
				switch($s['id']){
					case 2:
						$serv_color = "FFCCCB";
					break;
					case 5:
						$serv_color = "ffedcc";
					break;
					case 6:
						$serv_color = "e0f2c0";
					break;
					case 7:
						$serv_color = "DCF0F7";
					break;
					default:
						$serv_color = "ffffff";
				}
				
				
				$odd++;
			
				echo "<tr class='align-left' ".(($row3[6]==23)?'style="background-color:#eeeeee;"':'').">";
				
				
						
				
			echo "
				<td>$row3[0]".(($row3[6]==23)?'(Other Supplier)':'')."</td>
				<td>{$service}</td>";
				
				$tot_job_price = $row3[5];
				
				echo "<td>$".number_format($tot_job_price, 2)."</td>";
				
				// get alarms
				$a_sql = mysql_query("
					SELECT *
					FROM `alarm`
					WHERE `job_id`  = {$row3[3]}	
				");
				while($a = mysql_fetch_array($a_sql))
				{		
					if($a['new']==1){
						$tot_job_price += $a['alarm_price'];
					}				
				}
				
			echo "<td>$".number_format($tot_job_price, 2)."</td>
				<td>".( ( $retest_date!="" && $retest_date!="00/00/0000" )?$retest_date:'' )."</td>				
				<td>$row3[2]</td>";
				
				/*
				if( $row3[2]=='Completed' && $row3[6]!=23 ){
				
					echo "<td style='vertical-align: middle;' align='center'><a href='" . URL . "view_invoice.php?job_id=$row3[3]' target='_blank'><img src='images/pdf.png' /></a></td>
					<td style='vertical-align: middle;' align='center'><a href='" . URL . "view_certificate.php?job_id=$row3[3]' target='_blank'><img src='images/pdf.png' /></a></td>
					<td style='vertical-align: middle;' align='center'><a href='" . URL . "view_combined.php?job_id=$row3[3]' target='_blank'><img src='images/pdf.png' /></a></td>";
				
				}else{
				
					echo "<td valign=top colspan='3'></td>";
					
				}	
				*/				
				
				echo "</tr>\n";
			}

		//
     	//mysql_close($connection3);
		$option ='';
		foreach($jobtypes as $types){
			$option .="<option value='".$types[0]."'>".$types[0]."</option>";
		}	
		echo "<tr bgcolor=#ffffff class='align-left' style='border-bottom: 1px solid rgba(0, 0, 0, 0) !important; border-left: 1px solid rgba(0, 0, 0, 0) !important; border-right: 1px solid rgba(0, 0, 0, 0) !important;'>
			<td colspan='12'>&nbsp;</td>\n";
			/*
		echo "<td colspan='4'>
		<form method=get action='" . URL . "change_of_tenancy.php'>";
			
				echo	"<input type=hidden name='id' value='" . $id . "'><input type='submit' value='Create a Job for this Property'> 
					<select name='jobtype'>
					".$option."
					</select>
				</form>\n</td>";
			*/
		echo "</tr>";
		// <option value='ym'>Yearly Maintenance</option>
		// <option value='ct'>Change of Tenancy</option>
		// <option value='oo'>Once Off</option>
		// <option value='240v'>240v Rebook</option>
		// <option value='for'>Fix or Replace</option>
		
      // Print a carriage return to neaten the output

      echo "\n";
   

?>


</table>
<!-- Job type table : end -->

</form>


</td>
</tr>


<tr class="padding-none">
<td class="padding-none">
<?php
	if($_POST){
		if(isset($_POST['add_event'])){
			$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
			// format date
			$event_date = date('Y-m-d H:i:s',strtotime(str_replace("/","-",$_POST['eventdate'])));
			$important = ($_POST['important']!="")?1:0;
			
			$insertEventquery = "INSERT INTO property_event_log(property_id, staff_id, event_type, event_details, log_date, `important`)
								VALUES($id, $staff_id, '".$_POST['contact_type']."', '".mysql_real_escape_string($_POST['comments'])."', '".$event_date."', '".$important."' )";
								
			mysql_query($insertEventquery, $connection);	
		}
		
		if(isset($_POST['del_pelid']) && $_POST['del_pelid'] != ""){
			$query = "DELETE FROM property_event_log WHERE id = ". $_POST['del_pelid'];
			mysql_query($query, $connection);	
		}
	}
?>

<table width="100%" class="table-center tbl-fr-red view-property-table-inner">
	<tr class="padding-none border-none">
	<td style="padding: 0px;">	
	</td>
	</tr>
	<tr class="padding-none border-none">
	<td class="padding-none border-none">
	<form method="POST" name="property_event_del" action="view_property_details.php?id=<?php echo $id;?>">
		<input type="hidden" name="del_pelid" id="del_pelid" value="" />
	<table width="100%" cellspacing="1" cellpadding="5" border="0" class="vw-pr-lst align-left" id="vpd-de">
		<tr bgcolor="#b4151b" class="padding-none border-none"> 
			<td class="colorwhite bold">Date</td>
			<td class="colorwhite bold">Time</td>
			<td class="colorwhite bold">Event</td>
			<td class="colorwhite bold">Who</td>
			<td class="colorwhite bold">Details</td>
		</tr>
		<?php
			//$Query = "SELECT pl.id, staff_id, s.FirstName, s.LastName, event_type, event_details, DATE_FORMAT(log_date, '%d/%m/%Y'), pl.`property_id` FROM property_event_log pl, staff_accounts s where pl.staff_id = s.StaffID AND (property_id='".$id."')ORDER BY log_date DESC";
				$Query = "
					SELECT *
					FROM property_event_log AS pl
					LEFT JOIN `staff_accounts` AS sa ON pl.`staff_id` = sa.`StaffID`
					WHERE pl.property_id ={$id}
					ORDER BY pl.log_date DESC
				";
			$result = mysql_query ($Query, $connection);
	 
			 if (mysql_num_rows($result) == 0)
				echo "<tr class='padding-none border-none'><td colspan='5' >No property event logs returned.</td></tr>";

			$odd=0;

		   while ($row = mysql_fetch_array($result))
		   {
		   
			
			if($row['log_agency_id']!=""){
				$agency2_sql = mysql_query("
					SELECT *
					FROM `agency` 
					WHERE `agency_id` = {$row['log_agency_id']}
				");
				$agency2 = mysql_fetch_array($agency2_sql);
				$who = $agency2['agency_name'];
			}else if($row['staff_id']!=0){
				$who = $crm->formatStaffName($row['FirstName'],$row['LastName']);
			}else{
				$who = 'Agency';
			}
		   
				$odd++;
				if (is_odd($odd)) {
					echo "<tr ".(($row['important']==1)?'style="background-color:#FFCCCB!important; border: 1px solid #b4151b!important; box-shadow: 0 0 2px #b4151b inset!important;"':'bgcolor="#FFFFFF"')." class='border-none'>";		
				} else {
					echo "<tr ".(($row['important']==1)?'style="background-color:#FFCCCB!important; border: 1px solid #b4151b!important; box-shadow: 0 0 2px #b4151b inset!important;"':'bgcolor="#eeeeee"')." class='border-none b-rg'>";
				}
				
				$date = ($row['log_date']=='0000-00-00 00:00:00')?'':date('d/m/Y',strtotime($row['log_date']));
				echo '<td>'.$date.'</td>';
				
				$time = ($row['log_date']=='0000-00-00 00:00:00')?'':date('H:i',strtotime($row['log_date']));
				echo '<td>'.$time.'</td>';
				
				echo '<td class="even_type_td">'.$row['event_type'].'</td>';
				echo '<td>'.$who.'</td>';
				echo '<td>'.$row['event_details'].'</td>';			
				echo '</tr>';
			}
		?>
	</table>
	</form>
	</td>
	</tr>
</table>	


</td>
</tr>
</table>


</div>

  </div>

  
</div>

<br class="clearfloat" />

<div id="dialog-confirm" title="Confirm" style="display:none;">
  <p>Do you want to delete tenant data?</p>
</div>

<script type="text/javascript">

// google map autocomplete
var placeSearch, autocomplete;

// test
var componentForm2 = {
  route: { 
	'type': 'long_name', 
	'field': 'address_2' 
  },
  administrative_area_level_1: { 
	'type': 'short_name', 
	'field': 'state' 
  },
  postal_code: { 
	'type': 'short_name', 
	'field': 'postcode' 
  }
};

function initAutocomplete() {
  // Create the autocomplete object, restricting the search to geographical
  // location types.
  
  var options = {
	  types: ['geocode'],
	  componentRestrictions: {country: '<?php echo CountryISOName($country_id); ?>'}
	};
  
  autocomplete = new google.maps.places.Autocomplete(
     (document.getElementById('fullAdd')),
     options
	  );

  // When the user selects an address from the dropdown, populate the address
  // fields in the form.
  autocomplete.addListener('place_changed', fillInAddress);
}

// [START region_fillform]
function fillInAddress() {
  // Get the place details from the autocomplete object.
  var place = autocomplete.getPlace();

  // test
   for (var i = 0; i < place.address_components.length; i++) {
    var addressType = place.address_components[i].types[0];
    if (componentForm2[addressType]) {
      var val = place.address_components[i][componentForm2[addressType].type];
      document.getElementById(componentForm2[addressType].field).value = val;
    }
  }
  
  // street name
  var ac = jQuery("#fullAdd").val();
  var ac2 = ac.split(" ");
  var street_number = ac2[0];
  console.log(street_number);
  jQuery("#address_1").val(street_number);
  
  // suburb
  jQuery("#address_3").val(place.vicinity);
  
  console.log(place);
}
// end google autocomplete

jQuery(document).ready(function(){
	
	
	
	// mark property as no EN
	jQuery("#no_en").click(function(){
		
		var no_en = (jQuery(this).prop("checked")==true)?1:0;
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_mark_property_as_no_en.php",
			data: { 
				no_en: no_en,
				prop_id: <?php echo $id ?>
			}
		}).done(function( ret ){	
			//jQuery("#load-screen").hide();
			//obj.parents("li:first").find(".reg_db_sub_reg").html(ret);
		});
		
		
	});
	
	
	<?php
	// if franchise group = private
	if( $private_fg == 1 ){ ?>
	
		// for agency = private only
		jQuery("form#example").submit(function(){
			
			var ll_mobile = jQuery("#ll_mobile").val();
			var ll_landline = jQuery("#ll_landline").val();
			var error = '';
			
			if( ll_mobile =='' && ll_landline =='' ){
				error = 'Landlord Mobile or Landline must not be empty';
			}
			
			if( error!='' ){
				alert(error);
				return false;
			}else{
				return true;
			}
			
		});
		
	<?php	
	}
	?>
	
	
	jQuery("#show_agency_pass").click(function(){
		
		btn_txt = jQuery(this).html();
		if( btn_txt == 'Show Password' ){ // show
			jQuery(this).html("Hide Password");
			jQuery(".agency_pass_div").show();
		}else{ // hide
			jQuery(this).html("Show Password");
			jQuery(".agency_pass_div").hide();
		}
		
	});
	
	
	// no longer manage script
	jQuery("#btn_no_longer_managed").click(function(){
	
		if(confirm("Are you sure you want to mark this property as NLM?")==true){
			jQuery.ajax({
				type: "POST",
				url: "ajax_set_no_longer_managed.php",
				data: {
					property_id: <?php echo $id ?>
				}
			}).done(function(ret){
				window.location="/view_property_details.php?id=<?php echo $id ?>";
			});	
		}		
	
	});
	
	
	jQuery("#btn_other_supplier").click(function(){
		
		var text = jQuery(this).html();
		if(text=='+ Other Supplier Job'){
			jQuery("#job_date_div").show();
			jQuery(this).html("Cancel");
		}else{
			jQuery("#job_date_div").hide();
			jQuery(this).html("+ Other Supplier Job");
		}
		
		
	}); 
	
	// restore property script
	jQuery("#restoreProb_btn").click(function(){

	  jQuery( "#dialog-confirm" ).dialog({
		  resizable: false,
		  modal: true,
		  buttons: {
			"Yes": function() {
			  console.log('yes');
			  window.location='undelete_property.php?id=<?php echo $_GET['id']; ?>&del_tenant=1';
			  jQuery( this ).dialog( "close" );
			},
			"No": function() {
			  console.log('no');
			  window.location='undelete_property.php?id=<?php echo $_GET['id']; ?>';
			  jQuery( this ).dialog( "close" );
			}
		  }
		});

	});
	
	// deactivate property script
	jQuery("#deact_prop").click(function(){
		if(confirm("Are you sure you want to deactivate property?")==true){
			window.location='delete_property.php?id=<?php echo $_GET['id']; ?>';
		}		
	});
	
	// call ajax for delete property
	jQuery("#btn_delete_permanently").click(function(){
		
		if(confirm("Are you sure you want to continue?")==true){
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_property_permanently.php",
				data: {
					property_id: <?php echo $_GET['id']; ?>
				}
			}).done(function(ret){
				window.location="/view_properties.php?perm_del=1";
			});
			
		}
			
		
	});
	


	jQuery("#tbl_services input[type='radio']").change(function(){
	  jQuery(this).parents("tr:first").find(".is_updated").val(1);
	});
	

	jQuery("#btn_create_dummy").click(function(){
	
		var hid_smoke_price = jQuery("#hid_smoke_price").val();
		var dummy_date = jQuery("#dummy_date").val();
	
		if(confirm("This will create a Other Supplier Job?")==true){
			jQuery.ajax({
				type: "POST",
				url: "ajax_create_dummy.php",
				data: {
					property_id: <?php echo $id ?>,
					hid_smoke_price: hid_smoke_price,
					dummy_date: dummy_date
				}
			}).done(function(ret){
				window.location="/view_property_details.php?id=<?php echo $id ?>&pending=1";
			});	
		}		
	
	});
	
	jQuery("#btn_create_pending").click(function(){
	
		var hid_smoke_price = jQuery("#hid_smoke_price").val();
	
		if(confirm("Are you sure you want to continue?")==true){
			jQuery.ajax({
				type: "POST",
				url: "ajax_create_pendings.php",
				data: {
					property_id: <?php echo $id ?>,
					hid_smoke_price: hid_smoke_price
				}
			}).done(function(ret){
				window.location="/view_property_details.php?id=<?php echo $id ?>&pending=1";
			});	
		}		
	
	});

	
	// tenants updated mark script
	jQuery(".tenant_fields").change(function(){
	  jQuery("#tenants_changed").val(1);
	});

	jQuery(".btn_update_price").click(function(){
	
		var psid = jQuery(this).parents("tr:first").find(".property_services_id").val();
		var ajt = jQuery(this).parents("tr:first").find(".alarm_job_type_id").val();
		var price = jQuery(this).parents("tr:first").find(".price_field").val();
		var price_reason = jQuery(this).parents("tr:first").find(".price_reason").val();
		var price_details = jQuery(this).parents("tr:first").find(".price_details").val();
	
			jQuery.ajax({
				type: "POST",
				url: "ajax_property_service_update_price.php",
				data: {
					property_id: <?php echo $id ?>,
					psid: psid,
					ajt: ajt,
					price: price,
					price_reason: price_reason,
					price_details: price_details
				}
			}).done(function(ret){
				window.location="/view_property_details.php?id=<?php echo $id ?>&update_price_success=1";
			});	
	
	});
	

	// show/hide change price container
	jQuery(".price_lbl").click(function(){	
		jQuery(this).parents("tr:first").find(".change_price_div").toggle();	
	});
	


	// log required script
	jQuery("#add_event").click(function(){

		var error = "";
		var comments = jQuery("#comments").val();
		if(comments==""){
			error += "Details is required\n";
		}

		if(error!=""){
			alert(error);
		}else{
			jQuery("#property_event").submit();
		}

	});


	jQuery(".btn_create_job").click(function(){
		var property_id = <?php echo $id;  ?>;
		var alarm_job_type_id = jQuery(this).parents("tr:first").find(".alarm_job_type_id").val();
		var job_type = jQuery(this).parents("tr:first").find(".job_type").val();
		var price = jQuery(this).parents("tr:first").find(".price").val();
		var service_name = jQuery(this).parents("tr:first").find(".service_name").val();
		
		var vacant_from = jQuery(this).parents("tr:first").find(".vacant_from_input").val();
		var new_ten_start = jQuery(this).parents("tr:first").find(".new_ten_start_input").val();
		var problem = jQuery(this).parents("tr:first").find(".problem_input").val();
		var delete_tenant = jQuery(this).parents("tr:first").find(".delete_tenant:checked").val();
		var delete_tenant2 = (delete_tenant=="1")?1:0;
		var vacant_prop = jQuery(this).parents("tr:first").find(".vacant_prop:checked").val();
		var vacant_prop2 = (vacant_prop=="1")?1:0;
		
		// call ajax		
		jQuery.ajax({
			type: "POST",
			url: "ajax_create_job.php",
			data: {
				property_id: property_id,
				alarm_job_type_id: alarm_job_type_id,
				job_type: job_type,
				price: price,
				vacant_from: vacant_from,
				new_ten_start: new_ten_start,
				problem: problem,
				service_name: service_name,
				staff_id: <?php echo $_SESSION['USER_DETAILS']['StaffID']; ?>,
				delete_tenant : delete_tenant2,
				vacant_prop: vacant_prop2
			}
		}).done(function(ret){
			window.location.reload();
		});		
	});

	// invoke datepicker
	jQuery(".datepicker").datepicker({ 
		changeMonth: true, // enable month selection
		changeYear: true, // enable year selection
		yearRange: "2006:2026", // year range
		dateFormat: "dd/mm/yy"	// date format
	});

	jQuery(".create_job").click(function(){
		jQuery(this).parents("tr:first").find(".create_job_div").show();
	});
	
	jQuery(".job_type").change(function(){
	
		var btn_txt;

		if(jQuery(this).val()=="Fix or Replace"){
			btn_txt = "Create Repair Job";			
			jQuery(this).parents("tr:first").find(".desc_prob").show();
			jQuery(this).parents("tr:first").find(".vacant_from").hide();
			jQuery(this).parents("tr:first").find(".new_ten_start").show();
		}else if(jQuery(this).val()=="Change of Tenancy"){
			btn_txt = "Create "+jQuery(this).val()+" Job";
			jQuery(this).parents("tr:first").find(".vacant_from").show();
			jQuery(this).parents("tr:first").find(".new_ten_start").show();
			jQuery(this).parents("tr:first").find(".desc_prob").hide();
		}else if(jQuery(this).val()=="Lease Renewal"){
			btn_txt = "Create "+jQuery(this).val()+" Job";
			jQuery(this).parents("tr:first").find(".vacant_from").hide();
			jQuery(this).parents("tr:first").find(".new_ten_start").show();
			jQuery(this).parents("tr:first").find(".desc_prob").hide();
		}else{
			btn_txt = "Create "+jQuery(this).val()+" Job";
			jQuery(this).parents("tr:first").find(".desc_prob").hide();
			jQuery(this).parents("tr:first").find(".vacant_from").hide();
			jQuery(this).parents("tr:first").find(".new_ten_start").hide();
		}
		
		
		jQuery(".btn_create_job").show();
		jQuery(".delete_tenant_span").show();
		jQuery(".vacant_prop_span").show();
		jQuery(".btn_create_job").html(btn_txt);			
				
	});

});

	function submit_property_event_del(pelid){
		if(document.property_event_del.onsubmit && !document.property_event_del.onsubmit()){return;}
		
		var con = confirm('Are you sure you want to Delete this file?');
		if(con){
			$('#del_pelid').val(pelid);
			document.property_event_del.submit();
		}
	}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAa9QRZRQ3eucZ6OE18rSSi8a7VGJjoXQE&signed_in=true&libraries=places&callback=initAutocomplete" async defer></script>
</body>
</html>
