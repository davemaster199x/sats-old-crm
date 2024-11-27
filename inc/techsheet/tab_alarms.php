<?php

if(!defined('TECH_SHEET_INC'))
{
    exit();
}




# Get Property Alarms
$alarms = getPropertyAlarms($job_id, 0, 1, 2);
$alarms = stripSlashesData($alarms);

$num_existing_alarms = sizeof($alarms);

# Get New Property Alarms
$new_alarms = getPropertyAlarms($job_id, 2, 1, 2);
$new_alarms = stripSlashesData($new_alarms);
$num_existing_new_alarms = sizeof($new_alarms);



$ic_serv = getICService();

?>
<input type="hidden" name="alarm_count" value="<?php echo $num_existing_alarms; ?>" />
        <table border=0 cellspacing=0 cellpadding=5 width=100% class="tech_table" id="vjdt-ftable">
            <tr bgcolor="b4151b">
                <th class="techsheet_header colorwhite bold" colspan="100%">Property Survey</th>              
            </tr>
			<tr class='grey' style="height:35px;">
                <td colspan="2"><strong>HOUSE</strong></td>
                <td colspan="2"><strong>SWITCHBOARD</strong></td>
				<td colspan="2"><strong>ALARMS</strong></td>
            </tr>
            <tr>
                <td>Levels in Property</td>
                <td>
                <input type="number" style="margin-bottom: 0px;" name="survey_numlevels" id="survey_numlevels" value="<?=$job_details['survey_numlevels'];?>" class="xxsmall required addinput">
                </td>
				<td>
					<?php
					$ss_sql = mysql_query("
						SELECT *
						FROM `jobs`
						WHERE `id` = {$_GET['id']}
					");
					$ss = mysql_fetch_array($ss_sql);
					?>
					Switchboard Viewed
				</td>
				<td>
					<input type="radio" onclick="" name="ts_safety_switch" class="safety_switch_toggle" id="safety_switch_yes" <?php echo ($job_details['ts_safety_switch'] == '2')?'checked':''; ?> value="2">
                    <label for="safety_switch_yes">Yes</label> &nbsp;&nbsp;
                    <input type="radio" onclick="" name="ts_safety_switch" class="safety_switch_toggle radiobut-red"  id="safety_switch_no"  <?=($job_details['ts_safety_switch'] == '1') ? 'checked' : '';?> value="1">
                    <label for="safety_switch_no">No</label>
				</td>
                <td>Current Number of Alarms</td>
                <td colspan="100%">
                <input type="number" style="margin-bottom: 0px;" name="survey_numalarms" id="survey_numalarms" value="<?=$job_details['survey_numalarms'];?>" class="xxsmall required addinput">
                </td>
            </tr>
            <tr>
			
				
				<td>Ladder Required</td>
                <td>
                <input type="radio" onclick="" name="survey_ladder" class="required" id="survey_ladder_Y" <?=($job_details['survey_ladder'] == '4FT' ? 'checked' : '');?> value="4FT">
                <label for="survey_ladder_Y">3FT</label>                &nbsp;&nbsp;
                <input type="radio" onclick="" name="survey_ladder" class="required" id="survey_ladder_no" <?=($job_details['survey_ladder'] == '6FT' ? 'checked' : '');?> value="6FT">
                <label for="survey_ladder_no">6FT</label>
				<input type="radio" onclick="" name="survey_ladder" class="required" id="survey_ladder_8ft" <?=($job_details['survey_ladder'] == '8FT' ? 'checked' : '');?> value="8FT">
                <label for="survey_ladder_8ft">8FT</label>
				</td>
                				
                <td>
					<div class="cw_lbl ssp_yes" style="display:<?php echo ($ss['ts_safety_switch']==2)?'block':'none'; ?>">Switchboard Location</div>
					<div class="cw_lbl ssp_no" style="display:<?php echo ($ss['ts_safety_switch']==1)?'block':'none'; ?>">Reason</div>
				</td>
				<td>
					<input type="text" name="ss_location" id="ss_location_view" class="fsbxl addinput widthauto m-l-n ssp_yes" style="width: 160px; margin-bottom: 0px; display:<?php echo ($ss['ts_safety_switch']==2)?'block':'none'; ?>" value="<?php echo $job_details['ss_location'];?>">
					<select name="safety_switch_reason" id="safety_switch_reason" class="ssp_no" style="display:<?php echo ($ss['ts_safety_switch']==1)?'block':'none'; ?>"> 
						<option value="">----</option>
						<option value="0" <?php echo ($job_details['ts_safety_switch_reason'] == 0 ? "selected" : ""); ?>>Circuit Breaker Only</option>
						<option value="1" <?php echo ($job_details['ts_safety_switch_reason'] == 1 ? "selected" : ""); ?>>Unable to Locate</option>
						<option value="2" <?php echo ($job_details['ts_safety_switch_reason'] == 2 ? "selected" : ""); ?>>Unable to Access</option>
					</select>
				</td>
				<td>Alarms Positioned Correctly</td>
                <td colspan="100%">
                <input type="radio" name="survey_alarmspositioned" class="required" id="survey_alarmspositioned_Y" <?=($job_details['survey_alarmspositioned'] == 1 ? 'checked' : '');?> value="1" onclick="">
                <label for="survey_alarmspositioned_Y">Y</label> &nbsp;&nbsp;
                <input type="radio" name="survey_alarmspositioned" class="required radiobut-red" id="survey_alarmspositioned_no" <?=(is_null($job_details['survey_alarmspositioned']) ? '' : (intval($job_details['survey_alarmspositioned']) === 0) ? 'checked' : '')?> value="0" onclick="">
                <label for="survey_alarmspositioned_no">N</label></td>
            </tr>
            <tr>
			
				<td>Ceiling Type</td>
                <td>
                <input type="radio" name="survey_ceiling" class="required" id="survey_ceiling_Y" <?=($job_details['survey_ceiling'] == 'CON' ? 'checked' : '');?> value="CON" onclick="">
                <label for="survey_ceiling_Y">CON</label> &nbsp;&nbsp;
                <input type="radio" name="survey_ceiling" class="required" id="survey_ceiling_no" <?=($job_details['survey_ceiling'] == 'GYP' ? 'checked' : '');?> value="GYP" onclick="">
                <label for="survey_ceiling_no">GYP</label>
				</td>
				
                
				<td>
					<div class="cw_lbl ssp_yes" style="display:<?php echo ($ss['ts_safety_switch']==2)?'block':'none'; ?>">
						Safety Switch Quantity
					</div>
					
				 </td>
				 <td>
					<input type="number" name="ss_quantity" id="ss_quantity_view" class="fsbxl addinput widthauto m-l-n ssp_yes" style="margin-bottom: 0px; width: 160px; display:<?php echo ($ss['ts_safety_switch']==2)?'block':'none'; ?>" value="<?php echo $job_details['ss_quantity'];?>">										
				 </td>
				<td>Number Meets Minimum Standard</td>
                <td colspan="100%">
                <input type="radio" onclick="" name="survey_minstandard" class="required" id="survey_minstandard_Y" <?=($job_details['survey_minstandard'] == 1 ? 'checked' : '');?> value="1" >
                <label for="survey_minstandard_Y">Y</label> &nbsp;&nbsp;
                <input type="radio" onclick="" name="survey_minstandard" class="required radiobut-red" id="survey_minstandard_no" <?=(is_null($job_details['survey_minstandard']) ? '' : (intval($job_details['survey_minstandard']) === 0) ? 'checked' : '')?> value="0" >
                <label for="survey_minstandard_no">N</label></td>
            </tr>
			
				<?php
				// if IC alarms, Hide it
				if( in_array($job['service'], $ic_serv) ){
					$isIcAlarm = 1;
				}else{
					$isIcAlarm = 0;
				}
				?>
			
				<tr>
			
					<td colspan="4">
						<span style="display: inline;">Number of Bedrooms</span>
						<input type="number" name="ps_number_of_bedrooms" id="ps_number_of_bedrooms" class="fsbxl addinput widthauto m-l-n ps_number_of_bedrooms required" style="margin-bottom: 0px; width: 35px;" value="<?php echo ($job_details['ps_number_of_bedrooms']==0)?'':$job_details['ps_number_of_bedrooms']; ?>" />
						
						<span <?php echo ( $job_details['state']=='QLD' )?'style="display:inline;"':''; ?> class="prop_upgraded_to_ic_sa_elem">Property already upgraded to interconnected alarms (<strong style="color:red;">QLD ONLY</strong>)?</span>
						<?php 
						if( $job_details['prop_upgraded_to_ic_sa'] == 1 ){ ?>
							<select style="width: auto; <?php echo ( $job_details['state']=='QLD' )?'display:inline':''; ?>" id="prop_upgraded_to_ic_sa_disp" class="prop_upgraded_to_ic_sa_elem prop_upgraded_to_ic_sa_disp" disabled="disabled">
								<option value=''>---</option>
								<option value='1' <?php echo ($job_details['prop_upgraded_to_ic_sa']==1)?'selected="selected"':''; ?>>Yes</option>
								<option value='0' <?php echo ( is_numeric($job_details['prop_upgraded_to_ic_sa']) && $job_details['prop_upgraded_to_ic_sa']==0 )?'selected="selected"':''; ?>>No</option>
							</select>							
						<?php
						}else{ ?>
							<select style="width: auto; <?php echo ( $job_details['state']=='QLD' )?'display:inline':''; ?>" id="prop_upgraded_to_ic_sa_disp" class="prop_upgraded_to_ic_sa_elem prop_upgraded_to_ic_sa_disp">
								<option value=''>---</option>
								<option value='1' <?php echo ($job_details['prop_upgraded_to_ic_sa']==1)?'selected="selected"':''; ?>>Yes</option>
								<option value='0' <?php echo ( is_numeric($job_details['prop_upgraded_to_ic_sa']) && $job_details['prop_upgraded_to_ic_sa']==0 )?'selected="selected"':''; ?>>No</option>
							</select>
						<?php
						}
						?>
						
					</td>
					
						
					<td>
						<div <?php echo ( $job_details['state']=='QLD' )?'style="display:inline;"':''; ?> class="qld_new_leg_alarm_num_div">
							Total Number of alarms required to meet NEW legislation (<strong style="color:red;">QLD ONLY</strong>)
						</div>					
					</td>
					<td style="border-right: 1px solid #ccc;">						
						<div <?php echo ( $job_details['state']=='QLD' )?'style="display:inline;"':''; ?> class="qld_new_leg_alarm_num_div">
							<input type="number" name="qld_new_leg_alarm_num" id="qld_new_leg_alarm_num" class="fsbxl addinput widthauto m-l-n" style="margin-bottom: 0px; width: 35px;" <?php echo ($job_details['qld_new_leg_alarm_num']>0)?'readonly="readonly"':''; ?> value="<?php echo ($job_details['qld_new_leg_alarm_num']==0)?'':$job_details['qld_new_leg_alarm_num']; ?>" />
						</div>				
					</td>
					
				</tr>
			
		
            <tr>
				<td colspan="4">
					<span class="swms_span">Working at Heights</span> 
					<a target="_blank" href="/pdf_swms.php?id=<?php echo $job_id; ?>&swms_type=heights">
						<img src="/images/swms.png" class="swms_icon" />
					</a>
					<span class="swms_span">Working Outside</span> 
					<a target="_blank" href="/pdf_swms.php?id=<?php echo $job_id; ?>&swms_type=uv_protection">
						<img src="/images/swms.png" class="swms_icon" />
					</a>
					<span class="swms_span">Drilling </span> 
					<a target="_blank" href="/pdf_swms.php?id=<?php echo $job_id; ?>&swms_type=asbestos">
						<img src="/images/swms.png" class="swms_icon" />
					</a>
					<span class="swms_span">Corded Power Tools</span>  
					<a target="_blank" href="/pdf_swms.php?id=<?php echo $job_id; ?>&swms_type=powertools">
						<img src="/images/swms.png" class="swms_icon" />
					</a>
					<span class="swms_span">Animals on Site</span>  
					<a target="_blank" href="/pdf_swms.php?id=<?php echo $job_id; ?>&swms_type=animals">
						<img src="/images/swms.png" class="swms_icon" />
					</a>
					<span class="swms_span">Live Circuits</span>  
					<a target="_blank" href="/pdf_swms.php?id=<?php echo $job_id; ?>&swms_type=live_circuits">
						<img src="/images/swms.png" class="swms_icon" />
					</a>
				</td>
				<td>Entry Gained via</td>
				<td>
					<select id='entry_gained_via' class="entry_gained_via" name='entry_gained_via'>
						<option value=''>--- Select ---</option>
						<option value='1' <?php echo ($job_details['entry_gained_via']==1)?'selected="select"':''; ?>>Tenant</option>
						<option value='2' <?php echo ($job_details['entry_gained_via']==2)?'selected="select"':''; ?>>Keys from Agency</option>
						<option value='3' <?php echo ($job_details['entry_gained_via']==3)?'selected="select"':''; ?>>Keys Left On-site</option>
						<option value='4' <?php echo ($job_details['entry_gained_via']==4)?'selected="select"':''; ?>>Lock-box</option>
						<option value='5' <?php echo ($job_details['entry_gained_via']==5)?'selected="select"':''; ?>>Met Agent</option>
						<option value='6' <?php echo ($job_details['entry_gained_via']==6)?'selected="select"':''; ?>>Locksmith</option>
						<option value='-1' <?php echo ($job_details['entry_gained_via']==-1)?'selected="select"':''; ?>>Other </option>						
					</select><br />
					<input type="text" name="entry_gained_other_text" id="entry_gained_other_text" style="margin-top: 5px; <?php echo ($job_details['entry_gained_via']==-1)?'':'display:none;'; ?>" class="addinput" value="<?php echo ($job_details['entry_gained_via']==-1)?$job_details['entry_gained_other_text']:''; ?>" />
				</td>
			</tr>
			
			
			
			
			
			
			
		
			           
        </table> 

		<?php
		// show only on interconnected alarms services
		//$ic_serv = getICService();
		if( in_array($job['service'], $ic_serv) ){ ?>
			<div style="color: red;font-weight: bold;">*** ALL ALARMS ARE TO BE INTERCONNECTED ***</div>
		<?php	
		}
		?>		
		
    
        <? if($num_existing_alarms == 0):
        ?>
	
        <div class="error">
            This Property has no Alarms  on file. Please add Alarms below
        </div>
	
        <? else:?>
                <table border=0 cellspacing=0 cellpadding=0 width=100% class="tech_table" id="vjdt-ftable">
                    <tr bgcolor="b4151b">
                        <th class="techsheet_header colorwhite bold">Inspection and Testing - Existing Alarms</th>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?>
                        <th class="techsheet_header colorwhite bold">Alarm <?=($x + 1);?>
                        <input type='hidden' name='alarm_alarm_id_<?=$x;?>' value='<?=$alarms[$x]['alarm_id'];?>'>
                        </th>
                        <? endfor;?>
                    </tr>
                    <tr class='grey'>
                        <td>Position</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> <td>
                        <input type="text" style="margin-bottom: 0px;" class="alarm_small caps required addinput bgwhite alarm_ts_position" name="alarm_ts_position_<?=$x;?>" value="<?=$alarms[$x]['ts_position'];?>">
                        </td>
                        <? endfor;?>
                    </tr>
                    <tr>
                        <td>Type</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> 
						<td>
							<select name="alarm_type_id_<?=$x;?>" name='type[]'>
								<option value=''>--- Select ---</option>
								<? foreach($alarm_type as $index=>$data): ?>
								<option value='<?=$data['alarm_type_id'];?>' <?php echo ( $data['alarm_type_id'] == $alarms[$x]['alarm_type_id'] )?'selected="selected"':''; ?>><?=$data['alarm_type'];?></option>
								<? endforeach;?>
							</select>
						</td>
                        <? endfor;?>
                    </tr>
                    <tr>
                        <td>Make</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> <td><?=strtoupper($alarms[$x]['make']);?></td>
                        <? endfor;?>
                    </tr>
                    <tr>
                        <td>Model</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> <td><?=strtoupper($alarms[$x]['model']);?></td>
                        <? endfor;?>
                    </tr>
                    <tr>
                        <td>Power</td>
                        <? 
							for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> 
						<td><?//=$alarms[$x]['alarm_id'];?>
						<?php 
						$alrm_pw_sql = mysql_query("
							SELECT * 
							FROM  `alarm_pwr`
							WHERE `alarm_job_type_id` = 2
							AND `alarm_pwr_id` != 6
						");
						?>
						<select size="1" class="ext_alarm_pw" name="ext_alarm_pw_<?=$x;?>" id="ext_alarm_pw_<?=$x;?>">
							<?php
							while( $alrm_pw = mysql_fetch_array($alrm_pw_sql) ){ ?>
								<option value="<?php echo $alrm_pw['alarm_pwr_id']; ?>" <?php echo ($alrm_pw['alarm_pwr_id']==$alarms[$x]['alarm_power_id'])?'selected="selected"':''; ?>><?php echo $alrm_pw['alarm_pwr']; ?></option>
							<?php	
							}
							?>																				
						</select>
						</td>
                        <? endfor;?>
                    </tr>
					
					<tr>
                        <td>Required for Compliance</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> <td>
                        <input type="radio" onclick="" name="required_compliance_<?=$x;?>" id="required_compliance_<?=$x;?>_Y" value="1" <?=(intval($alarms[$x]['ts_required_compliance']) == 1 || $alarms[$x]['ts_required_compliance'] == "" ? 'checked' : '');?>>
                        <label for="required_compliance_<?=$x;?>_Y">Y</label> &nbsp;&nbsp;
                        <input type="radio" onclick="" name="required_compliance_<?=$x;?>" id="required_compliance_<?=$x;?>_no" value="0" class="radiobut-red" <?=(intval($alarms[$x]['ts_required_compliance']) == 0 && $alarms[$x]['ts_required_compliance'] != "" ? 'checked' : '');?>>
                        <label for="required_compliance_<?=$x;?>_no">N</label></td>
                        <? endfor;?>
                    </tr>
					
					
                    <tr class='grey'>
                        <td>Expiry Date</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> <td><?
                        # If expiry is blank and the alarm has been added in this tech sheet, set the expiry date to the
                        if ($alarms[$x]['ts_expiry'] == "" & $alarms[$x]['ts_added'] == 1)
                            $alarms[$x]['ts_expiry'] = $alarms[$x]['expiry'];
                        ?>
                        <input style="margin-bottom: 0px;" type="hidden" id="alarm_expiry_<?=$x;?>" name="expiry_<?=$x;?>" value="<?=$alarms[$x]['expiry'];?>">
                        <input style="margin-bottom: 0px;" type="number" id="alarm_ts_expiry_<?=$x;?>" name="alarm_ts_expiry_<?=$x;?>" value="<?=$alarms[$x]['ts_expiry'];?>" class="alarm_small ts_expiry required addinput bgwhite">
                        </td>
                        <? endfor;?>
                    </tr>

                    <tr class='grey'>
                        <td>dB Reading (Minimum 85dB at 3 metres)</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> <td>
                        <input type="number" style="margin-bottom: 0px;" id="alarm_ts_db_rating_<?=$x;?>" name="alarm_ts_db_rating_<?=$x;?>" value="<?=$alarms[$x]['ts_db_rating'];?>" class="alarm_small ts_db_rating required addinput bgwhite">
                        </td>
                        <? endfor;?>
                    </tr>
                    


                    <tr>
                        <td>Securely Fixed</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> <td>
                        <input type="radio" onclick="" name="alarm_ts_fixing_<?=$x;?>" id="secure_fixing_<?=$x;?>_Y" value="1" <?=(intval($alarms[$x]['ts_fixing']) == 1 || $alarms[$x]['ts_fixing'] == "" ? 'checked' : '');?>>
                        <label for="secure_fixing_<?=$x;?>_Y">Y</label> &nbsp;&nbsp;
                        <input type="radio" onclick="" name="alarm_ts_fixing_<?=$x;?>" id="secure_fixing_<?=$x;?>_no" value="0" class="radiobut-red" <?=(intval($alarms[$x]['ts_fixing']) == 0 && $alarms[$x]['ts_fixing'] != "" ? 'checked' : '');?>>
                        <label for="secure_fixing_<?=$x;?>_no">N</label></td>
                        <? endfor;?>
                    </tr>
                    <tr>
                        <td>Cleaned</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> <td>
                        <input type="radio" onclick="" name="alarm_ts_cleaned_<?=$x;?>" id="cleaned_<?=$x;?>_Y" value="1" <?=(intval($alarms[$x]['ts_cleaned']) == 1 || $alarms[$x]['ts_cleaned'] == "" ? 'checked' : '');?>>
                        <label for="cleaned_<?=$x;?>_Y">Y</label> &nbsp;&nbsp;
                        <input type="radio" onclick="" name="alarm_ts_cleaned_<?=$x;?>" id="cleaned_<?=$x;?>_no" value="0" class="radiobut-red" <?=(intval($alarms[$x]['ts_cleaned']) == 0 && $alarms[$x]['ts_cleaned'] != "" ? 'checked' : '');?>>
                        <label for="cleaned_<?=$x;?>_no">N</label></td>
                        <? endfor;?>
                    </tr>
                    <tr>
                        <td>Battery Tested and Replaced if Required (Where replaceable)</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> <td>
                        <input type="radio" onclick="" name="alarm_ts_newbattery_<?=$x;?>" id="fitted_<?=$x;?>_Y" value="1" <?=(intval($alarms[$x]['ts_newbattery']) == 1 || $alarms[$x]['ts_newbattery'] == "" ? 'checked' : '');?>>
                        <label for="fitted_<?=$x;?>_Y">Y</label> &nbsp;&nbsp;
                        <input type="radio" onclick="" name="alarm_ts_newbattery_<?=$x;?>" id="fitted_<?=$x;?>_no" value="0"class="radiobut-red"  <?=(intval($alarms[$x]['ts_newbattery']) == 0 && $alarms[$x]['ts_newbattery'] != "" ? 'checked' : '');?>>
                        <label for="fitted_<?=$x;?>_no">N</label></td>
                        <? endfor;?>
                    </tr>
                    <tr>
                        <td>Test Button Working</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> <td>
                        <input type="radio" onclick="" name="alarm_ts_testbutton_<?=$x;?>" id="testbutton_<?=$x;?>_Y" value="1" <?=(intval($alarms[$x]['ts_testbutton']) == 1 || $alarms[$x]['ts_testbutton'] == "" ? 'checked' : '');?>>
                        <label for="testbutton_<?=$x;?>_Y">Y</label> &nbsp;&nbsp;
                        <input type="radio" onclick="" name="alarm_ts_testbutton_<?=$x;?>" id="testbutton_<?=$x;?>_no" value="0" class="radiobut-red" <?=(intval($alarms[$x]['ts_testbutton']) == 0 && $alarms[$x]['ts_testbutton'] != "" ? 'checked' : '');?>>
                        <label for="testbutton_<?=$x;?>_no">N</label></td>
                        <? endfor;?>
                    </tr>
                    <tr>
                        <td>Visual Indicators Working</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> <td>
                        <input type="radio" onclick="" name="alarm_ts_visualind_<?=$x;?>" id="inspectvisual_<?=$x;?>_Y" value="1" <?=(intval($alarms[$x]['ts_visualind']) == 1 || $alarms[$x]['ts_visualind'] == "" ? 'checked' : '');?>>
                        <label for="inspectvisual_<?=$x;?>_Y">Y</label> &nbsp;&nbsp;
                        <input type="radio" onclick="" name="alarm_ts_visualind_<?=$x;?>" id="inspectvisual_<?=$x;?>_no" value="0" class="radiobut-red"<?=(intval($alarms[$x]['ts_visualind']) == 0 && $alarms[$x]['ts_visualind'] != "" ? 'checked' : '');?>>
                        <label for="inspectvisual_<?=$x;?>_no">N</label></td>
                        <? endfor;?>
                    </tr>
					
					<?php
					// show only on interconnected alarms services
					//$ic_serv = getICService();
					if( in_array($job['service'], $ic_serv) ){ ?>
					
						<tr>
							<td style="color: red;font-weight: bold;">Does Alarm sound all other alarms?</td>
							<? for($x = 0; $x < $num_existing_alarms; ++$x ):
							?> <td>
							<input type="radio" onclick="" name="alarm_sounds_other_<?=$x;?>" id="alarm_sounds_other_<?=$x;?>_Y" value="1" checked="checked" <?=(intval($alarms[$x]['ts_alarm_sounds_other']) == 1 || $alarms[$x]['ts_alarm_sounds_other'] == "" ? 'checked' : '');?> />
							<label for="alarm_sounds_other_<?=$x;?>_Y">Y</label> &nbsp;&nbsp;
							<input type="radio" onclick="" name="alarm_sounds_other_<?=$x;?>" id="alarm_sounds_other_<?=$x;?>_no" value="0" class="radiobut-red" <?=(intval($alarms[$x]['ts_alarm_sounds_other']) == 0 && $alarms[$x]['ts_alarm_sounds_other'] != "" ? 'checked' : '');?> />
							<label for="alarm_sounds_other_<?=$x;?>_no">N</label></td>
							<? endfor;?>
						</tr>
						
					<?php	
					}
					?>
					
					
<!--                     <tr>
                        <td>Operated with simulated smoke</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> <td>
                        <input type="radio" onclick="" name="alarm_ts_simsmoke_<?=$x;?>" id="simulatedsmoke_<?=$x;?>_Y" value="1" <?=(intval($alarms[$x]['ts_simsmoke']) == 1 || $alarms[$x]['ts_simsmoke'] == "" ? 'checked' : '');?> />
                        <label for="simulatedsmoke_<?=$x;?>_Y">Y</label> &nbsp;&nbsp;
                        <input type="radio" onclick="" name="alarm_ts_simsmoke_<?=$x;?>" id="simulatedsmoke_<?=$x;?>_no" value="0" <?=(intval($alarms[$x]['ts_simsmoke']) == 0 && $alarms[$x]['ts_simsmoke'] != "" ? 'checked' : '');?> />
                        <label for="simulatedsmoke_<?=$x;?>_no">N</label></td>
                        <? endfor;?>
                    </tr> -->
<!--                     <tr class='grey'>
                        <td>Checked for Db</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> <td>
                        <input type="radio" onclick="" name="alarm_ts_checkeddb_<?=$x;?>" id="checkeddb_<?=$x;?>_Y" value="1" <?=(intval($alarms[$x]['ts_checkeddb']) == 1 || $alarms[$x]['ts_checkeddb'] == "" ? 'checked' : '');?> />
                        <label for="checkeddb_<?=$x;?>_Y">Y</label> &nbsp;&nbsp;
                        <input type="radio" onclick="" name="alarm_ts_checkeddb_<?=$x;?>" id="checkeddb_<?=$x;?>_no" value="0" <?=(intval($alarms[$x]['ts_checkeddb']) == 0 && $alarms[$x]['ts_checkeddb'] != "" ? 'checked' : '');?> />
                        <label for="checkeddb_<?=$x;?>_no">N</label></td>
                        <? endfor;?>
                    </tr> -->
                    <tr>
						<!-- renamed from 1851 to 3786 -->
                        <td>
						<?php 
						if( $_SESSION['country_default'] == 1 ){ // AU
							echo "Meets AS 3786:2014";
						}else if( $_SESSION['country_default'] == 2 ){ // NZ
							echo "Meets AS3786, AS3786:2014, ULCS531, BS5446: Part 1, BS EN 14604, ISO12239";
						}
						?>
						
						</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> <td>
                        <input type="radio" onclick="" name="alarm_ts_meetsas1851_<?=$x;?>" id="meetAS1851_<?=$x;?>_Y" value="1" <?=(intval($alarms[$x]['ts_meetsas1851']) == 1 || $alarms[$x]['ts_meetsas1851'] == "" ? 'checked' : '');?>>
                        <label for="meetAS1851_<?=$x;?>_Y">Y</label> &nbsp;&nbsp;
                        <input type="radio" onclick="" name="alarm_ts_meetsas1851_<?=$x;?>" id="meetAS1851_<?=$x;?>_no" value="0" class="radiobut-red"<?=(intval($alarms[$x]['ts_meetsas1851']) == 0 && $alarms[$x]['ts_meetsas1851'] != "" ? 'checked' : '');?>>
                        <label for="meetAS1851_<?=$x;?>_no">N</label></td>
                        <? endfor;?>
                    </tr>
					<!--
                    <tr>
                        <td>&nbsp;</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?>
                        <td>&nbsp;</td>
                        <? endfor;?>
                    </tr>
					-->
                    <tr>
                        <td>Discarded</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?> <td>
                        <input type="radio" onclick="" class="discarded_toggle" data-id="<?=$x;?>" name="alarm_ts_discarded_<?=$x;?>" id="discarded_<?=$x;?>_Y" value="1" <?=(intval($alarms[$x]['ts_discarded']) == 1  || $alarms[$x]['ts_discarded'] == "" ? 'checked' : '');?>>
                        <label for="discarded_<?=$x;?>_Y">Y</label> &nbsp;&nbsp;
                        <input type="radio" onclick="" class="discarded_toggle radiobut-red" data-id="<?=$x;?>" name="alarm_ts_discarded_<?=$x;?>" id="discarded_<?=$x;?>_no" value="0" <?=(intval($alarms[$x]['ts_discarded']) == 0 && $alarms[$x]['ts_discarded'] != "" ? 'checked' : '');?>>
                        <label for="discarded_<?=$x;?>_no">N</label>

                        <div id="discarded_<?=$x;?>_reason_cont" class="discarded_reason_cont <?php echo !$alarms[$x]['ts_discarded'] ? 'disable' : '' ?>">
                            Reason: <br />
                            <select name="alarm_ts_discarded_reason_<?=$x;?>">
                                <option value="0">Select...</option>
                                <?php 
								$adr_sql = mysql_query("
									SELECT * 
									FROM `alarm_discarded_reason`
									WHERE `active` = 1
									ORDER BY `reason` ASC 
								");
								while($reason=mysql_fetch_array($adr_sql)){ ?>
                                <option value="<?php echo $reason['id'];?>" <?php echo ($alarms[$x]['ts_discarded_reason'] == $reason['id'] ? 'selected' : ''); ?>><?php echo $reason['reason'];?></option>
                                <?php } ?>
                            </select>
                        </div>
                        </td>

                        <? endfor;?>
                    </tr>
                    <tr>
                        <td>Delete</td>
                        <? for($x = 0; $x < $num_existing_alarms; ++$x ):
                        ?>
                        <td align="center"><? if($alarms[$x]['ts_added'] == 1):
                        ?>
                        <a href="?id=<?=$job_id;?>&delalarm=<?=$alarms[$x]['alarm_id'];?>&tab=smoke-co2-tab" onclick="return confirm('Are you sure you want to delete this alarm?');" >Click to Delete</a><? else:?>
                        N/A
                        <? endif;?></td>
                        <? endfor;?>
                    </tr>
                </table>
                <?php endif; ?>
				
						<div style="text-align: left; margin-bottom: 9px;">
						
								<button type="button" id="btn_add_alarm" class="submitbtnImg">ADD Alarm</button>
							
						</div>
						
						
						
						<!-- SA lightbox -->
						<div style="display:none" id="main_add_alarm_div">
							<div id="sa_window">
							
							
								<table cellpadding=2 cellspacing=0 width=100% border=0 id='tbl_add_sa' class='tbl_add_sa' style="border: none; margin-bottom: 10px;">
									<tbody class="add_sa_tbody">
										<tr class="servBgcolorNTextColor_sa">
											<td><strong>New?</strong></td>
											<td>
												<select id='new_alarm_new' class="addinput jSAalarm new_alarm_new add_sa_dp" name='new'>
													<option value=''>--- Select ---</option>
													<option value='1'>New</option>
													<option value='0'>Existing</option>
												</select>
											</td>					
										</tr>
										<tr class="servBgcolorNTextColor_sa add_sa_tr_new">					
											<td><strong>RFC?</strong></td>
											<td>
												<select id='new_alarm_compliance' class="addinput new_alarm_compliance add_sa_dp" name='pwr'>
													<option value=''>--- Select ---</option>
													<option value='1'>Yes</option>
													<option value='0'>No</option>
												</select>
											</td>
										</tr>
										<tr class="servBgcolorNTextColor_sa add_sa_tr_new">					
											<td><strong>Power</strong></td>
											<td>
												<?php	
												// property alarms
												$p_sql2 = getDynamicPropertyAlarms($job_details['agency_id']);								
												?>
												<select id='new_alarm_pwr' name='pwr' class="addinput jpwr_typ new_alarm_pwr add_sa_dp">
													<option value=''>--- Select ---</option>
													<?php
														while($p2 = mysql_fetch_array($p_sql2)){ ?>
															<option value="<?php echo $p2['alarm_pwr_id'] ?>"><?php echo $p2['alarm_pwr'] ?></option>
														<?php	
														}
													?>										
												</select>
											</td>
										</tr>
										
										<tr class="servBgcolorNTextColor_sa add_sa_tr_new">					
											<td><strong>Type</strong></td>
											<td>
												<select id='new_alarm_type' type=text name='type[]' class="addinput new_alarm_type add_sa_dp">
													<option value=''>--- Select ---</option>
													<? foreach($alarm_type as $index=>$data): ?>
													<option value='<?=$data['alarm_type_id'];?>'><?=$data['alarm_type'];?></option>
													<? endforeach;?>
												</select>
											</td>
										</tr>
										<tr class="servBgcolorNTextColor_sa reason_td add_sa_tr_exist">					
											<td><strong>Reason</strong></td>
											<td>
												<input type='hidden' name='alarm_id[]' value='0' />
												<select id='new_alarm_reason' type=text name='reason[]' class="addinput new_alarm_reason add_sa_dp">
													<option value=''>--- Select ---</option>
													<? foreach($alarm_reason as $index=>$data): 
													// respect the new active flag
													if($data['active'] == 1):
													?>
													<option value='<?=$data['alarm_reason_id'];?>'><?=$data['alarm_reason'];?></option>
													<? 
													endif;
													endforeach;?>
												</select>
											</td>
										</tr>
										<?php
										if( in_array($job['service'], $ic_serv) ){ ?>
											<tr class="servBgcolorNTextColor_sa add_sa_tr_new">					
												<td><strong>Interconnected</strong></td>
												<td>
													<select id='new_is_alarm_ic' name='new_is_alarm_ic' class="addinput new_is_alarm_ic add_sa_dp">
														<option value='1'>Yes</option>
														<option value='0' selected="selected">No</option>
													</select>
												</td>
											</tr>
										<?php	
										}
										?>				
										<tr class="servBgcolorNTextColor_sa add_sa_tr_new">					
											<td><strong>Position</strong></td>
											<td>
												<input id='new_alarm_position' type="text" name='position' class="addinput new_alarm_position" value='' size=8>
											</td>
										</tr>
										<tr class="servBgcolorNTextColor_sa add_sa_tr_new">					
											<td><strong>Make</strong></td>
											<td>
												<input id='new_alarm_make' type="text" name='make' value='' class="addinput new_alarm_make" size=8>
											</td>
										</tr>
										<tr class="servBgcolorNTextColor_sa add_sa_tr_new">					
											<td><strong>Model</strong></td>
											<td>
												<input id='new_alarm_model' type="text" name='model' value='' class="addinput new_alarm_model" size=8>
											</td>
										</tr>
										<tr class="servBgcolorNTextColor_sa add_sa_tr_new">					
											<td><strong>Expiry</strong></td>
											<td>
												<input id='new_alarm_exp' type="number" name='exp' maxlength="4" class='addinput new_alarm_exp' size="4" />
											</td>
										</tr>
										<tr class="servBgcolorNTextColor_sa add_sa_tr_new">					
											<td><strong>dB</strong></td>
											<td>
												<input id='new_alarm_db_rating' type="number" name='db_rating' class="addinput new_alarm_db_rating" value='' />
											</td>
										</tr>
										<tr class="add_sa_tr_new">					
											<td><button type="button" id='btn_remove_sa_row' class="servBgcolorNTextColor_sa submitbtnImg btn_remove_sa_row">Remove Alarm</button></td>
											<td>
												<button type="button" id='btn_add_clear' class="servBgcolorNTextColor_sa submitbtnImg btn_add_clear">Clear Alarm Data</button>
											</td>
										</tr>
									</tbody>
									<tfoot>
										<tr>
											<td colspan="2">&nbsp;</td>
										<tr>
										<tr>					
											<td>						
												<button style="background-color:#00aeef;" type="button" id='btn_add_sa' class="servBgcolorNTextColor_sa submitbtnImg blue-btn btn_add_sa">
													<img class="inner_icon" src="images/add-button.png">
													Alarm
												</button>
												<!--<button type="button" id='btn_add_clear' class="servBgcolorNTextColor_sa submitbtnImg btn_add_clear">Clear</button>-->
											</td>
											<td>
												<input type="hidden" name="new_sa_submitted" id="new_sa_submitted" /> 
												<button style="background-color:#00aeef;" style="color:#00aeef;" type="button" id='btn_save_sa' class="servBgcolorNTextColor_sa submitbtnImg btn_save_sa">
													<img class="inner_icon" src="images/save-button.png">
													Save
												</button>						
											</td>
										</tr>
									</tfoot>
								</table>

								
							</div>
						</div>
				
						
				
                       <style>
					   .nntab{ width: 50%; float: left;}
                       .tb-alarm-add2{ float: right; margin-right: 0px;}
					   .tb-alarm-add2 input{ margin-right: 10px;}
                       </style> 
						
						
                       
					
				
					<table border=0 cellspacing=0 cellpadding=5 width=98% class="tech_table" id="alarm_div2" <?php echo (count($new_alarms)==0)?'style="display:none;"':''; ?>>
						<tr>
							<td colspan="4" class="techsheet_header">New Installed / Existing Property Alarms</td>
						</tr>
						<tr>
							<td  style=" border-right: 1px solid #ccc !important; padding: 0px">
							
							<table cellpadding=2 cellspacing=0 width=100% border=0 id='alarm_table'>
								<tr style="border-top: none !important; border-left: none !important; border-right: none !important;">
									<td class="tbl-almtg">New?</td>
                                    <td class="tbl-almtg">RFC?</td>
                                    <td class="tbl-almtg">Power</td>
                                    <td class="tbl-almtg">Type</td>
                                    <td class="tbl-almtg">Reason</td>
									<?php
									// show only on interconnected alarms services
									//$ic_serv = getICService();
									if( in_array($job['service'], $ic_serv) ){ ?>
										<td class="tbl-almtg">Interconnected</td>
									<?php
									}
									?>									
									<td class="tbl-almtg">Position</td>
									<td class="tbl-almtg">Make</td>
                                    <td class="tbl-almtg">Model</td>
                                    <td class="tbl-almtg-expiry">Expiry</td>
									<td class="tbl-almtg-db">dB</td>
									<td class="tbl-almtg">Delete</td>
								</tr>



								<? # Draw the existing alarms
								 foreach($new_alarms as $alarm):

								?>
								<tr bgcolor=#F0F0F0 style="border: none !important;">
									<td class="tbl-almtg">
									<select name='alarm_new[]' class="select bgwhite alarm_new jSAalarm" style="padding:5px;">
										<option value='1'>New</option><option value='0'>Existing</option>
									</select></td>
									<td class="tbl-almtg">
									   <select name='alarm_compliance[]' size=1 class="select bgwhite" style="padding:5px;">
										
											<option value='1' <?php echo ($alarm['ts_required_compliance'] == 1 ? 'selected' : ''); ?>>Yes</option>
											<option value='0' <?php echo ($alarm['ts_required_compliance'] == 0 ? 'selected' : ''); ?>>No</option>


										</select> 
									</td>

									<td class="tbl-almtg">
									<input type='hidden' name='alarm_alarm_id[]' value='<?=$alarm['alarm_id'];?>' class="addinput inputauto bgwhite" style="margin-left: 0px;">
									<select name='alarm_pwr[]' size=1 class="select bgwhite jpwr_typ" style="padding:5px;">
										<?php
											$p_sql2 = getDynamicPropertyAlarms($job_details['agency_id']);
										while($p2 = mysql_fetch_array($p_sql2)){ ?> 
										<option value='<?=$p2['alarm_pwr_id'];?>' <?php echo ($p2['alarm_pwr_id']==$alarm['alarm_power_id'])  ? 'selected' : ''; ?>><?=$p2['alarm_pwr'];?></option>
										<?php } ?>
									</select>
								</td>
                                <td class="tbl-almtg">
									<select type=text name='alarm_type[]' size=1 class="select bgwhite" style="padding:5px;">
										<option selected value=''>&nbsp;</option>
										<? foreach($alarm_type as $index=>$data):
										?>
										<option value='<?=$data['alarm_type_id'];?>' <?=($alarm['alarm_type_id'] == $data['alarm_type_id'] ? 'selected' : '');?>><?=$data['alarm_type'];?></option><? endforeach;?>
									</select></td>
									


									<td class="tbl-almtg">
									<select type=text name='alarm_reason[]' size=1 class="select bgwhite" style="padding:5px;">
										<option selected value=''>&nbsp;</option>
										<? 
										$na_sql = mysql_query("
											SELECT *
											FROM `alarm_reason`
											WHERE `active` = 1
											ORDER BY `alarm_reason` ASC
										");
										while($data = mysql_fetch_array($na_sql)){ ?> 
										<option value='<?=$data['alarm_reason_id'];?>' <?=($alarm['alarm_reason_id'] == $data['alarm_reason_id'] ? 'selected' : '');?>>
											<?=$data['alarm_reason'];?>
										</option>
										<? } ?>
									</select>
									</td>
									
									<?php
									// show only on interconnected alarms services
									//$ic_serv = getICService();
									if( in_array($job['service'], $ic_serv) ){ ?>
										<td class="tbl-almtg">	
											<select type="text" name='ts_is_alarm_ic[]' size=1 class="select bgwhite" style="padding:5px;">
												<option value='1' <?php echo ( $alarm['ts_alarm_sounds_other']==1 )?'selected="selected"':''; ?>>Yes</option>
												<option value='0' <?php echo ( $alarm['ts_alarm_sounds_other']==0 )?'selected="selected"':''; ?>>No</option>										
											</select>
										</td>
									<?php
									}
									?>
									
									
									
									<td class="tbl-almtg">
									<input type=text name='alarm_position[]' value='<?=$alarm['ts_position'];?>' size=8 class="caps addinput inputauto bgwhite" style="margin-left: 0px;">
									</td>
									<td class="tbl-almtg">
									<input type=text name='alarm_make[]' value='<?=$alarm['make'];?>' size=8 class="caps addinput inputauto bgwhite" style="margin-left: 0px;">
									</td>
									<td class="tbl-almtg">
									<input type=text name='alarm_model[]' value='<?=$alarm['model'];?>' size=8 class="caps addinput inputauto bgwhite" style="margin-left: 0px;">
									</td>
									<td class="tbl-almtg-expiry">
									<input style="width: 85px;" type=text name='alarm_exp[]' value='<?=$alarm['expiry'];?>' size=8 class='xxsmall addinput inputauto bgwhite' style="margin-left: 0px;">
									</td>
									
									<td class="tbl-almtg-db">
									<input type=text name='alarm_db_rating[]' value='<?=$alarm['ts_db_rating'];?>' size=8 class="addinput inputauto bgwhite" style="margin-left: 0px;">
									</td>
									
									<td align=center class="tbl-almtg"><a href='?id=<?=$job_id;?>&delalarm=<?=$alarm['alarm_id'];?>&tab=smoke-co2-tab'  onclick="return confirm('Are you sure you want to delete this alarm?');"  class='remove_link'>Delete</a></td>
								</tr>
								<?
								endforeach;
								?>
							</table>
						  
							</td>
						</tr>
						
						
				
						
						
					</table>
					
				
				
				
				<table border=0 cellspacing=0 cellpadding=5 class="tech_table">
									<tr class="grey">
										<td style="text-align: right;">Batteries Installed</td>
										<td style="padding-left: 0px;">
										<input type="number" style="margin-bottom: 0px; width: 48px;" name="ts_batteriesinstalled" id="ts_batteriesinstalled" value="<?=$job_details['ts_batteriesinstalled'];?>" class="required addinput bgwhite">
										</td>
										<td style="text-align: right;">Items Tested</td>
										<td style="padding-left: 0px;">
										<input type="number" style="margin-bottom: 0px; width: 48px;" name="ts_items_tested" id="ts_items_tested"  value="<?=($job_details['ts_items_tested']!=""&&$job_details['ts_items_tested']!=0)?$job_details['ts_items_tested']:'';?>" class="required addinput bgwhite">
										</td>
										<td style="text-align: right;">Alarms Installed</td>
										<td style="padding-left: 0px;">
										<input type="number" style="margin-bottom: 0px; width: 48px;" name="ts_alarmsinstalled" id="ts_alarmsinstalled" value="<?=$job_details['ts_alarmsinstalled'];?>" class="required addinput bgwhite">
										</td>
										<td>
											
										</td>
									</tr>
								</table>
				
				
				<table border=0 cellspacing=0 cellpadding=5 width=98% class="tech_table">
			<tr>
				<td class="techsheet_header">Technician</td>				
				<td class="techsheet_header">Date</td>
				<td class="techsheet_header">Safe Work Method Statements (SWMS)</td>
				<td class="techsheet_header">Repair Notes</td>
				<td class="techsheet_header">Job Notes</td>
                <td class="techsheet_header">Property Notes</td>
			</tr>


			<tr class="grey">
				<td><?=$job_details['tech_first_name'];?> <?=$job_details['tech_last_name'];?><br /><br /></td>	
				<?php 
				$jc_sql = mysql_query("
					SELECT `completed_timestamp`
					FROM `jobs`
					WHERE `id` = {$_GET['id']}
				"); 
				$jc = mysql_fetch_array($jc_sql);
				?>
				<td><input type="text" name="ts_signoffdate" style="width: 80px !important;" value="<?php echo ($job_details['status']=='Completed')?(($jc['completed_timestamp']!="")?date("d/m/Y",strtotime($jc['completed_timestamp'])):''):$job_details['ts_signoffdate']; ?>" class="addinput inputauto"></td>
				<td style="background-color: white; border: 1px solid #cccccc;">
					<p>
						<strong>
							Whilst on site at the above property <br />
							I observed and followed the following SWMS:
						</strong>
					</p>
					<ul style="list-style-type: none; padding: 0;">
						<li><input type="checkbox" class="swms_chk" name="swms_heights" id="swms_heights" value="1" <?php echo ($job_details['swms_heights']==1)?'checked="checked"':''; ?> /> Working at Heights</li>
						<li><input type="checkbox" class="swms_chk" name="swms_uv_protection" id="swms_uv_protection" value="1" <?php echo ($job_details['swms_uv_protection']==1)?'checked="checked"':''; ?> /> UV Protection</li>
						<li><input type="checkbox" class="swms_chk" name="swms_asbestos" id="swms_asbestos" value="1" <?php echo ($job_details['swms_asbestos']==1)?'checked="checked"':''; ?> /> Likely to involve Disturbing Asbestos</li>
						<li><input type="checkbox" class="swms_chk" name="swms_powertools" id="swms_powertools" value="1" <?php echo ($job_details['swms_powertools']==1)?'checked="checked"':''; ?> /> Using Corded Power Tools</li>
						<li><input type="checkbox" class="swms_chk" name="swms_animals" id="swms_animals" value="1" <?php echo ($job_details['swms_animals']==1)?'checked="checked"':''; ?> /> Animals on Site</li>
						<li><input type="checkbox" class="swms_chk" name="swms_live_circuit" id="swms_live_circuit" value="1" <?php echo ($job_details['swms_live_circuit']==1)?'checked="checked"':''; ?> /> Working with Live Circuits</li>
					</ul>
					
				</td>
				<td>
					<a class="inlineFB" href="#repair_notes_lb_div">
						<textarea style="height: 150px;" name="repair_notes" id="repair_notes" class="techsheet addtextarea sig_commments"><?=stripslashes((isset($job_details['repair_notes']) ? $job_details['repair_notes'] : $job_details['repair_notes']));?></textarea>
					</a>
				</td>
				<td>
					<a class="inlineFB" href="#job_lb_div">
						<textarea style="height: 150px;" name="tech_comments" id="tech_comments" class="techsheet addtextarea sig_commments" readonly="readonly"><?=stripslashes((isset($job_details['tech_comments']) ? $job_details['tech_comments'] : $job_details['tech_comments']));?></textarea>
					
				</td>
				<td>
					<a class="inlineFB" href="#prop_lb_div">
						<textarea style="height: 150px;" name="prop_comments" id="prop_comments" class="techsheet addtextarea sig_commments" readonly="readonly"><?=stripslashes((isset($p['comments']) ? $p['comments'] : $p['comments']));?></textarea>
					</a>
				</td>
			</tr>
			
			<?php
			if($serv2['bundle']==1){
				$tickbox = ( $job_details['ts_techconfirm'] == 1 ) ? 'checked' : '';
			}else{
				$tickbox = ( $job_details['ts_techconfirm'] == 1 && $job_details['ts_completed'] == 1 ) ? 'checked' : '';
			}
			?>
			
			
			<tr>
				<td colspan="5" class="vjdtch-row">
					<?php
					// show only on interconnected alarms services
					//$ic_serv = getICService();
					if( in_array($job['service'], $ic_serv) ){ ?>
						<!--
						<div style="margin:0px;">
							<input type="checkbox" id="ts_ic_alarm_confirm" class="required" name="ts_ic_alarm_confirm" value="1" <?php echo ($job_details['ts_ic_alarm_confirm']==1)?'checked="checked"':''; ?> />
							<label for="ts_ic_alarm_confirm" style="color: red;font-weight: bold;">I confirm that all alarms in this property are interconnected.</label>
						</div>
						-->
					<?php
					}
					?>					
					<div style="clear:both;"></div>
					<div style="margin:0px;">
						<input type="checkbox" id="ts_techconfirm" class="required last_confirm_elem ts_techconfirm" name="ts_techconfirm" <?=$tickbox?> value="1">
						<label for="ts_techconfirm">I confirm that all items on the above checklist have been completed and all Appliances noted have been Inspected and Maintained as per Manufacturers Recommendations and the Australian Standards.</label>
					</div>	
	
					<?php
					if( $job_details['state']=='QLD' ){ ?>
						<div style="clear:both;"></div>
						<div style="margin:0px;">
							<label class="colorItRed"><strong>Does this property meet QLD NEW Legislation?</strong></label>
							<select style="width: auto; <?php echo ( $job_details['state']=='QLD' )?'display:inline':''; ?>" id='prop_upgraded_to_ic_sa' class="prop_upgraded_to_ic_sa_elem prop_upgraded_to_ic_sa ts_confirm_ic_upgrade last_confirm_elem" name='prop_upgraded_to_ic_sa'>
								<option value=''>---</option>
								<option value='1' <?php echo ($job_details['prop_upgraded_to_ic_sa']==1)?'selected="selected"':''; ?>>Yes</option>
								<option value='0' <?php echo ( is_numeric($job_details['prop_upgraded_to_ic_sa']) && $job_details['prop_upgraded_to_ic_sa']==0 )?'selected="selected"':''; ?>>No</option>
							</select>
						</div>
					<?php
					}
					?>
					
				</td>
				<td colspan="1">
					<?php
					if( $job_details['state']=='QLD' ){ 
						if( $job_details['prop_upgraded_to_ic_sa'] != '' && $tickbox == 'checked' ){
							$show_ts_button = 1;
						}
					}else{
						if( $tickbox == 'checked' ){
							$show_ts_button = 1;
						}
					}
					?>
					<div id="ts_button_div" class="<?php echo ( $show_ts_button == 1 )?'showItBlock':'hideIt'; ?>">
						<input type="hidden" name="job_id" value="<?php echo $job_id; ?>" id="job_id">
						<input type="hidden" name="tab" id="tab" value="<?php echo $job_tech_sheet_job_types[0]['html_id']; ?>-tab">
						<input type="hidden" name="btn_comp_ts_submit" id="btn_comp_ts_submit" value="0">
						<button type="button" id="btn_comp_ts" class="submitbtnImg bluebutton">SUBMIT COMPLETED TECHSHEET</button>					
					</div>
				</td>
			</tr>
		</table>
				

                
<style>
.disable{
display:none!important;
}
.qld_new_leg_alarm_num_div, .prop_upgraded_to_ic_sa_elem{
	display:none;
}
#prop_upgraded_to_ic_sa{
	width: 50px;
}
.swms_icon{
	position: relative; 
	top: 2px;
	margin-right: 10px;
}
.swms_span {
    bottom: 8px;
    position: relative;
}
.ps_number_of_bedrooms{
	display: inline; 
	float: none; 
	width: 46px; 
	margin-right: 7px;
}
</style>
<script>



jQuery(document).ready(function(){
	
	<?php	
	if( $job_details['state']=='QLD' ){ ?>
	
		jQuery(".last_confirm_elem").change(function(){
		
			var ts_techconfirm = jQuery(".ts_techconfirm").prop("checked");
			var ts_confirm_ic_upgrade = jQuery(".ts_confirm_ic_upgrade").val();
			
			//console.log("ts_techconfirm: "+ts_techconfirm+" ts_confirm_ic_upgrade: "+ts_confirm_ic_upgrade);
			
			if( ts_techconfirm == true && ts_confirm_ic_upgrade != '' ){
				jQuery("#ts_button_div").show();
			}else{
				jQuery("#ts_button_div").hide();
			}
			
		});
	
	<?php
	}else{ ?>
	
	
		jQuery(".last_confirm_elem").change(function(){
		
			var ts_techconfirm = jQuery(".ts_techconfirm").prop("checked");
			
			//console.log("ts_techconfirm: "+ts_techconfirm);
			
			if( ts_techconfirm == true ){
				jQuery("#ts_button_div").show();
			}else{
				jQuery("#ts_button_div").hide();
			}
			
		});
	
	<?php
	}		
	?>
	
	
	
	// upgrade script
	jQuery(".prop_upgraded_to_ic_sa_disp").change(function(){

		var sel_val = jQuery(this).val();
		
		/*
		if( parseInt(sel_val) == 0){
			jQuery(".qld_new_leg_alarm_num_div").show();
		}else{
			jQuery(".qld_new_leg_alarm_num_div").hide();
		}
		*/
		
		/*
		// follow top QLD dp script
		jQuery("#prop_upgraded_to_ic_sa option").each(function(){
			
			var option = jQuery(this).val();
			
			if( option == sel_val  ){
				jQuery(this).prop("selected",true);
			}
			
		});
		*/

	});
	
	
	jQuery("#entry_gained_via").change(function(){
		
		var egv = jQuery(this).val();
		
		if( parseInt(egv) == -1 ){ // if Other
			jQuery("#entry_gained_other_text").show();
		}else{
			jQuery("#entry_gained_other_text").hide();
		}
		
	});
	
	
	// add grey shade for not empty
	function checkEmpty(){
		
		jQuery("#sa_window select:visible, #sa_window tr input[type='text']:visible, #sa_window input[type='number']:visible").each(function(){			
			
			
			if( jQuery(this).val()!='' ){
				jQuery(this).parents("tr:first").addClass("cwFileSelectedHLRed");
			}
			
			
		});
		
	}
	
	
	// remove alarm form row
	jQuery("#sa_window").on("click",".btn_remove_sa_row",function(){
		
		jQuery(this).parents("tbody.add_sa_tbody:first").remove();
		
	});
	
	
	
	// show add alarm div
	jQuery("#btn_add_alarm").click(function(){
		
			jQuery("#main_add_alarm_div").show();
		
	});
	
	
	// clear form
	jQuery("#sa_window").on("click","#btn_add_clear",function(){
		
		jQuery(this).parents(".add_sa_tbody:first").find("input[type='number']").val('');
		jQuery(this).parents(".add_sa_tbody:first").find("input[type='text']").val('');
		jQuery(this).parents(".add_sa_tbody:first").find("select option").removeAttr('selected');
		jQuery(this).parents(".add_sa_tbody:first").find("tr.cwFileSelectedHLRed").removeClass("cwFileSelectedHLRed");
		
		
	});
	
	
	/*
	// insert selected attribute on dropdowns, so jquery clone can copy it too
	jQuery("#sa_window").on("click",".add_sa_dp option",function(){
		
		jQuery(this).parents("select:first").find("option").removeAttr("selected");
		jQuery(this).attr("selected","selected");
		
	});
	*/
	
	
	// alarm power type script
	jQuery("#sa_window").on("change",".new_alarm_pwr",function(){
		
		//alert('trigger');
		var obj = jQuery(this);
		var alarm_pwr_id = obj.val();
		var is_alarm_new = obj.parents("tbody:first").find(".new_alarm_new").val();
		
		// only for new alarm
		if( is_alarm_new==1 ){
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_get_alarm_power_details.php",
				data: { 
					alarm_pwr_id: alarm_pwr_id
				},
				dataType: "json"
			}).done(function( ret ){
				console.log(ret);
				obj.parents("tbody:first").find(".new_alarm_make").val(ret.alarm_make);
				obj.parents("tbody:first").find(".new_alarm_model").val(ret.alarm_model);
				obj.parents("tbody:first").find(".new_alarm_exp").val(ret.alarm_expiry);
				obj.parents("tbody:first").find(".new_alarm_type option").each(function(){
										
					if( parseInt(jQuery(this).val()) == parseInt(ret.alarm_type_id) ){
						jQuery(this).attr('selected','selected');
					}					
					
				});
			});	
			
		}
		
		
	});
	
	
	
	
	
	// add alarm
	jQuery("#sa_window").on("click","#btn_add_sa",function(){
		
		// needed to make the height auto
		jQuery("#fancybox-content").css('height','auto');
		
		//obj.parents("tbody:first").find(".add_sa_tr_new").hide();
		//obj.parents("tbody:first").find(".add_sa_tr_exist").hide();
		
		var last_form = jQuery("#sa_window .add_sa_tbody:last");
		var last_rfc = last_form.find(".new_alarm_compliance").val();
		var last_pwr = last_form.find(".new_alarm_pwr").val();
		var last_typ = last_form.find(".new_alarm_type").val();
		var last_res = last_form.find(".new_alarm_reason").val();
		var last_is_alarm_ic = last_form.find(".new_is_alarm_ic").val();
		
		// copy sa form fields
		var add_sa_form = jQuery("#sa_window .add_sa_tbody:last").clone();
		
		
		
		// repopulate dropdowns since jquery .clone wont get it's selection
		add_sa_form.find(".new_alarm_compliance option[value='"+last_rfc+"']").prop('selected',true);
		add_sa_form.find(".new_alarm_pwr option[value='"+last_pwr+"']").prop('selected',true);
		add_sa_form.find(".new_alarm_type option[value='"+last_typ+"']").prop('selected',true);
		add_sa_form.find(".new_alarm_reason option[value='"+last_res+"']").prop('selected',true);
		add_sa_form.find(".new_is_alarm_ic option[value='"+last_is_alarm_ic+"']").prop('selected',true);
		
		// clear location and DB which unique		
		add_sa_form.find(".new_alarm_db_rating").val('');
		add_sa_form.find(".new_alarm_position").val('');
		
		// close form by default
		//add_sa_form.find(".add_sa_tr_new").hide();
		//add_sa_form.find(".add_sa_tr_exist").hide();
		
		// clear fields
		/*
		add_sa_form.each(function(){
			jQuery(this).find("input[type='number']").val('');
			jQuery(this).find("input[type='text']").val('');
			jQuery(this).find("select option:eq(0)").prop('selected',true);
		});
		*/
		
		
		// check for not empty and highlight it grey
		jQuery("#sa_window input[type='text'], #sa_window input[type='number'], #sa_window select").each(function(){
			
			if( jQuery(this).val()!='' ){
				jQuery(this).parents("tr:first").addClass("saFileSelectedHL");
			}
			
			
		});
		
		
		// insert
		add_sa_form.insertAfter("#sa_window .add_sa_tbody:last");
		
		
		
	});
	
	

	// New or Existing?
	jQuery("#sa_window").on("change",".jSAalarm",function(){
		
		// needed to make the height auto
		jQuery("#fancybox-content").css('height','auto');
		
		var obj = jQuery(this);
		var agency_id = <?php echo $job_details['agency_id']; ?>;
		var is_new = obj.val();
		
		if( is_new==1 ){
			obj.parents("tbody:first").find(".add_sa_tr_new").show();
			obj.parents("tbody:first").find(".reason_td").show();
		}else{
			obj.parents("tbody:first").find(".add_sa_tr_new").show();
			obj.parents("tbody:first").find(".reason_td").hide();
			
			/*
			// clear make, model, expiry
			obj.parents("tbody:first").find(".new_alarm_make").val('');
			obj.parents("tbody:first").find(".new_alarm_model").val('');
			obj.parents("tbody:first").find(".new_alarm_exp").val('');
			*/
		}
	
			jQuery.ajax({
				type: "POST",
				url: "ajax_getDynamicSaAlarms.php",
				data: { 
					agency_id: agency_id,
					is_new: is_new
				}
			}).done(function( ret ){
				obj.parents("tbody:first").find(".jpwr_typ").html('<option value="">--- Select ---</option>'+ret);
			});	
		
	});

	/*
	jQuery("#btn_add_alarm").click(function(){
		jQuery("#alarm_div1").show();
	});
	*/
	
	

	jQuery("#btn_comp_ts").click(function(){
	
			var error_message = "";
			var fnc_return = true;	
			var error = 0;
			
			 $(".required").each(function(i) {

                var val = $(this).val();

                if(val === "")
                {
                    fnc_return = false;
                    $(this).addClass("error_border");
                }
                else
                {
                    $(this).removeClass("error_border");
                }
            });
			
			 if(fnc_return === false)
            {
                //error_message += "Please complete the fields in red: \n";
            }			
	
			// property survey
			var survey_numlevels = $("input#survey_numlevels").val();	
			var survey_numalarms = $("input#survey_numalarms").val();
			var ts_batteriesinstalled = $("input#ts_batteriesinstalled").val();
			var ts_items_tested = $("input#ts_items_tested").val();
			var ts_alarmsinstalled = $("input#ts_alarmsinstalled").val();
			var entry_gained_via = $("#entry_gained_via").val();
			var qld_new_leg_alarm_num = $("#qld_new_leg_alarm_num").val();
			var ps_number_of_bedrooms = $("#ps_number_of_bedrooms").val();
			
			<?php
			if( $job_details['state']=='QLD' ){ ?>
			
				if( jQuery("#prop_upgraded_to_ic_sa_disp").val() == 0 ){ // no
					
					if(qld_new_leg_alarm_num == ""){
						error = 1;
						error_message += "\n" + "Total Number of alarms required to meet NEW legislation field is Required";
					}

				}else if( jQuery("#prop_upgraded_to_ic_sa_disp").val() == 1 ){ // yes

					if( qld_new_leg_alarm_num > 0 ){
						//error_message += "\n" + "If Property is upgraded then required alarms should be 0";
					}

				}
			
			<?php
			}
			?>
			
			
				
		
			if(entry_gained_via == "")
			{
				error = 1;
				error_message += "\n" + "Entry Gained is Required";
			}
			if(survey_numlevels == "")
			{
				error = 1;
				error_message += "\n" + "Please enter Levels in Property field";
			}
			if(survey_numalarms == "")
			{
				error = 1;
				error_message += "\n" + "Please enter Current Number of Alarms field";
			}
			if( jQuery("#survey_ceiling_Y").prop("checked") == false && jQuery("#survey_ceiling_no").prop("checked") == false ){
				error = 1;
				error_message += "\n" + "Please select 'Ceiling Type' field";
			}
			if( jQuery("#survey_alarmspositioned_Y").prop("checked") == false && jQuery("#survey_alarmspositioned_no").prop("checked") == false ){
				error = 1;
				error_message += "\n" + "Please select 'Alarms Positioned Correctly' field";
			}
			/*
			if( jQuery("#survey_ladder_Y").prop("checked") == false && jQuery("#survey_ladder_no").prop("checked") == false ){
				error = 1;
				error_message += "\n" + "Please select 'Ladder Used' field";
			}
			*/
			if( jQuery("#survey_minstandard_Y").prop("checked") == false && jQuery("#survey_minstandard_no").prop("checked") == false ){
				error = 1;
				error_message += "\n" + "Please select 'Number Meets Minimum Standard' field";
			}
			if(ts_batteriesinstalled == "")
			{
				error = 1;
				error_message += "\n" + "Please enter Batteries Installed field";
			}
			if(ts_items_tested == "")
			{
				error = 1;
				error_message += "\n" + "Please enter Items Tested field";
			}
			if(ts_alarmsinstalled == "")
			{
				error = 1;
				error_message += "\n" + "Please enter Alarms Installed field";
			}
			
			if( jQuery(".swms_chk:checked").length==0 ){
				error = 1;
				error_message += "\n" + "You must mark at least one SWMS";
			}
			
			
			if( jQuery("#safety_switch_yes").prop("checked") == false && jQuery("#safety_switch_no").prop("checked") == false ){
				error = 1;
				error_message += "\n" + "Please select 'Safety Switch Present' field";
			}else if( jQuery("#safety_switch_yes").prop("checked") == true ){
				jQuery("#ss_location_view").addClass("required");
				jQuery("#ss_quantity_view").addClass("required");
				jQuery("#safety_switch_reason").removeClass("required");
				if(jQuery("#ss_location_view").val()==""){
					error = 1;
					error_message += "\n" + "Please select 'Fuse Box Location' field";
				}
				if(jQuery("#ss_quantity_view").val()==""){
					error = 1;
					error_message += "\n" + "Please select 'Safety Switch Quantity' field";
				}
			}else if( jQuery("#safety_switch_no").prop("checked") == true ){
				jQuery("#safety_switch_reason").addClass("required");
				jQuery("#ss_location_view").removeClass("required");
				jQuery("#ss_quantity_view").removeClass("required");
				if(jQuery("#safety_switch_reason").val()==""){
					error = 1;
					error_message += "\n" + "Please select 'Safety Switch Reason' field";
				}
			}
			
			if($("#ts_ic_alarm_confirm").prop("checked")==false)
			{
				error = 1;
				error_message += "\n" + "Please tick the Interconnect confirmation box";
			}
			
			if($("#ts_techconfirm").prop("checked")==false)
			{
				error = 1;
				error_message += "\n" + "Please tick the confirmation box";
			}
			
			if($("#prop_upgraded_to_ic_sa").val()=='')
			{
				error = 1;
				error_message += "\n" + "Please select YES/NO for upgraded to NEW QLD Legislation";
			}
			
			
			
			var empty = 0;
			jQuery(".alarm_ts_position").each(function(){
			  if(jQuery(this).val()==""){
				empty = 1;
			  }
			});
			if(empty==1){
				error = 1;
				error_message += "\n" + "Please enter existing alarm position";
			}
			
			var empty = 0;
			jQuery(".ts_expiry").each(function(){
			  if(jQuery(this).val()==""){
				empty = 1;
			  }
			});
			if(empty==1){
				error = 1;
				error_message += "\n" + "Please enter existing alarm expiry date";
			}
			
			var empty = 0;
			jQuery(".ts_db_rating").each(function(){
			  if(jQuery(this).val()==""){
				empty = 1;
			  }
			});
			if(empty==1){
				error = 1;
				error_message += "\n" + "Please enter existing alarm db reading";
			}
			if(ps_number_of_bedrooms == "")
			{
				error = 1;
				error_message += "\n" + "Number of Bedrooms Required";
			}



			
			

			if(error_message !="" ){
				alert(error_message);
			}

			if( error == 0 ){
				jQuery("#btn_comp_ts_submit").val(1);
				$("form#techsheetform").submit();
			}
			

	});

    var _ALARM_ROW_COUNTER = 0;

    jQuery("button#btn_save_sa").click(function(){
		
		var error = '';
		var alarms_inserted = 0;
		var new_alarm_compliance_flag = 0;
		var new_alarm_pwr_flag = 0;
		var new_alarm_type_flag = 0;
		var new_alarm_reason_flag = 0;
		var new_alarm_db_rating_flag = 0;
		var new_alarm_position_flag = 0;
		var new_alarm_make_flag = 0;
		var new_alarm_model_flag = 0;
		var new_alarm_exp_flag = 0;
		var i = 0;
		var ctr = 0;
		
		jQuery("select.new_alarm_compliance").each(function(){
			
			if(jQuery(this).val()==''){
				new_alarm_compliance_flag = 1;
			}
			
		});
		
		jQuery("select.new_alarm_pwr").each(function(){
			
			if(jQuery(this).val()==''){
				new_alarm_pwr_flag = 1;
			}
			
		});
		
		jQuery("select.new_alarm_type").each(function(){
			
			if(jQuery(this).val()==''){
				new_alarm_type_flag = 1;
			}
			
		});
		
		jQuery("select.new_alarm_reason").each(function(){
			
			var is_new = jQuery(this).parents("tbody:first").find(".new_alarm_new").val();
			if( is_new==1 && jQuery(this).val()==''){
				new_alarm_reason_flag = 1;
			}
			
		});
		
		jQuery("input.new_alarm_position").each(function(){
			
			if(jQuery(this).val()==''){
				new_alarm_position_flag = 1;
			}
			
		});
		
		jQuery("input.new_alarm_make").each(function(){
			
			if(jQuery(this).val()==''){
				new_alarm_make_flag = 1;
			}
			
		});
		
		jQuery("input.new_alarm_db_rating").each(function(){
			
			if(jQuery(this).val()==''){
				new_alarm_db_rating_flag = 1;
			}
			
		});
		
		jQuery("input.new_alarm_model").each(function(){
			
			if(jQuery(this).val()==''){
				new_alarm_model_flag = 1;
			}
			
		});
		
		jQuery("input.new_alarm_exp").each(function(){
			
			if(jQuery(this).val()==''){
				new_alarm_exp_flag = 1;
			}
			
		});
		
		
		if(new_alarm_compliance_flag==1){
			 error += "Please enter RFC field\n";
		}
		
		if(new_alarm_pwr_flag==1){
			 error += "Please enter Alarm Power field\n";
		}
		
		if(new_alarm_type_flag==1){
			 error += "Please enter Alarm Type field\n";
		}
		
		if(new_alarm_reason_flag==1){
			 error += "Please enter Alarm Reason field\n";
		}
		
		if(new_alarm_position_flag==1){
			 error += "Please enter Alarm Position field\n";
		}
		
		if(new_alarm_make_flag==1){
			 error += "Please enter Alarm Make field\n";
		}
		
		if(new_alarm_model_flag==1){
			 error += "Please enter Alarm Model field\n";
		}
		
		if(new_alarm_exp_flag==1){
			 error += "Please enter Alarm Expiry field\n";
		}
		
		if(new_alarm_db_rating_flag==1){
			 error += "Please enter dB Reading field\n";
		}
		
		
		
		
		if(error!='')
        {
			checkEmpty();
            alert(error);
        }else{
	
			
			
			// loop throw each items
			var ctr = 0;
			jQuery(".add_sa_tbody").each(function(){
				ctr++;
			});
			
			jQuery(".add_sa_tbody").each(function(){
				
				var new_alarm_new = jQuery(this).find(".new_alarm_new").val();
				var new_alarm_compliance = jQuery(this).find(".new_alarm_compliance").val();
				var new_alarm_pwr = jQuery(this).find(".new_alarm_pwr").val();
				var new_alarm_type = jQuery(this).find(".new_alarm_type").val();
				var new_alarm_reason = jQuery(this).find(".new_alarm_reason").val();
				var new_alarm_position = jQuery(this).find(".new_alarm_position").val();
				var new_alarm_make = jQuery(this).find(".new_alarm_make").val();
				var new_alarm_model = jQuery(this).find(".new_alarm_model").val();
				var new_alarm_exp = jQuery(this).find(".new_alarm_exp").val();
				var new_alarm_db_rating = jQuery(this).find(".new_alarm_db_rating").val();
				var new_is_alarm_ic = jQuery(this).find(".new_is_alarm_ic").val(); 
				
				//alert(new_alarm_new);
				
				// Prepare Ajax Statement
				$.ajax({
					type: "POST",
					data: "job_id=" + <?php echo $job_id; ?> + 
							"&agency_id=<?php echo $job_details['agency_id']; ?>" + 
							"&alarm_new=" + new_alarm_new +
							"&alarm_pwr=" + new_alarm_pwr +
							"&alarm_type=" + new_alarm_type +
							"&alarm_reason=" + new_alarm_reason +
							"&alarm_position=" + new_alarm_position +
							"&alarm_make=" + new_alarm_make +
							"&alarm_model=" + new_alarm_model +
							"&alarm_exp=" + new_alarm_exp +
							"&alarm_compliance=" + new_alarm_compliance + 
							"&alarm_db_rating=" + new_alarm_db_rating+
							 "&new_is_alarm_ic=" + new_is_alarm_ic,
					url: "ajax/add_alarm.php",
					cache: false,
					dataType: "json",
					success: function(data){
						
						i++;
					  
					   if(i==ctr){
						   //window.location='/view_job_details_tech.php?id=<?php echo $_GET['id']; ?>&service=<?php echo $_GET['service']; ?>&bundle_id=<?php echo $_GET['bundle_id']; ?>&sa_added=1';
							jQuery("#techsheetform").submit();
					   }
						
					}
				});
				
				
				
			});
			
			
			
			
			
		}
		
	});


    // Discarded toggle
    $("input.discarded_toggle").click(function() {

        var id = $(this).attr("data-id");
        var val = $(this).val();

        if(val == 0)
        {
            $("#discarded_" + id + "_reason_cont").addClass("disable");
        }
        else if(val == 1)
        {
            $("#discarded_" + id + "_reason_cont").removeClass("disable");
        }
    });
    

    // Safety Switch yes no toggle
	/*
    $("input.safety_switch_toggle").click(function() {

        var val = $(this).val();

        $("tr.safety_switch_toggle").addClass("disable");

        $("tr.safety_switch_" + val).removeClass("disable");

        return true;
    });
	*/
	
	jQuery("#safety_switch_yes").click(function(){
		/*
		jQuery(".safety_switch_2").show();
		jQuery(".safety_switch_1").hide();
		*/
		jQuery(".ssp_yes").show();
		jQuery(".ssp_no").hide();
	});

	jQuery("#safety_switch_no").click(function(){
		/*
		jQuery(".safety_switch_2").hide();
		jQuery(".safety_switch_1").show();
		*/
		jQuery(".ssp_yes").hide();
		jQuery(".ssp_no").show();
	});


    // Double check expiry date entered matches previous expiry
    $("form#techsheetform").on('submit', function() {

        var fnc_return = true;

        $(".ts_expiry").each(function(i) {

            var alarm_num = i+1;
            var id = $(this).attr("id");
            var hidden_id = id.replace("ts_", "");
        
            if($("#" + id).val() != $("#" + hidden_id).val())
            {
				
				$(this).addClass("error_border");
				
				
                var conf = confirm("Alarm #" + alarm_num + " Expiry does not match previous tech sheet. Press OK to override or Cancel to change");
                if(conf != true)
                {
                    // Send back to the form 
                    fnc_return = false;

                    // Stop checking alarms
                    return false;
                }
            }

        });

        // Otherwise let the form submit
        return fnc_return;
    });
	
	
});
</script>