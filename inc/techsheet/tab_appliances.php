<?php

if(!defined('TECH_SHEET_INC'))
{
    exit();
}

?>
<script type="text/javascript">

    $(document).ready(function() {
    
        var _ROW_COUNTER = 0;

        // Set Variable for next item number
        var _NEXT_ITEM_NUMBER = <?php echo $next_item_number; ?>;

        updateApplianceCount();
    
        $("button#add_appliance").live('click', function() {

            var error = 0;
            var error_message = "";

            // Retrieve form values and prepare to validate / submit
            var app_new = $("input#app_new").val();
            var app_alarm_id = $("input#app_alarm_id").val();
            var app_type = $("select#app_type option:selected").val();
            var app_pass = $("select#app_pass option:selected").val();
            var app_reason = $("select#app_reason option:selected").val();
            var app_make = $("input#app_make").val().trim();
            var app_ts_comments = $("input#app_ts_comments").val().trim();
            var app_ts_location = $("input#app_ts_location").val().trim();
            var job_id = $("input#job_id").val();

            // Minimum we need is Type / Pass / Reason / Applianace
            if(!app_type)
            {
                error = 1;
                error_message += "\n" + "Please enter the appliance type";
            }
            if(!app_reason && app_pass != 1)
            {
                error = 1;
                error_message += "\n" + "Please enter the reason";
            }
            if(!app_pass)
            {
                error = 1;
                error_message += "\n" + "Please enter the Pass result";
            }
            if(app_make == "")
            {
                error = 1;
                error_message += "\n" + "Please enter the appliance name";
            }

            if(error == 1)
            {
                alert($.trim(error_message));
            }
            else
            {
                // Prepare Ajax Statement
                $.ajax({
                    type: "POST",
                    data: "job_id=" + job_id + 
                            "&app_new=" + app_new +
                            "&app_alarm_id=" + app_alarm_id +
                            "&app_type=" + app_type +
                            "&app_pass=" + app_pass +
                            "&app_reason=" + app_reason +
                            "&app_make=" + app_make +
                            "&app_ts_comments=" + app_ts_comments +
                            "&app_ts_location=" + app_ts_location,
                    url: "ajax/add_appliance.php",
                    cache: false,
                    dataType: "json",
                    success: function(data){

                        if(data.status == "success")
                        {
                            var applianceAppend = "";
                            applianceAppend += "<tr class='new_row' id='row_" + _ROW_COUNTER + "'>";
                            applianceAppend += "    <td>";
                            applianceAppend += "        <input type='hidden' name='appliance_alarm_id_" + data.data.alarm_id + "' value='" + data.data.alarm_id + "' />";
                            applianceAppend += "        <input type='hidden' name='appliance_alarm_id[]' value='" + data.data.alarm_id + "' />";
                            applianceAppend += "        <input type='hidden' name='appliance_new[]' value='1' />";
                            applianceAppend += "        " + data.data.appliance_number + "</td>";
                            applianceAppend +=  "<td><select type=text name='appliance_alarm_type[]' class='app_type' size=1>";
                            applianceAppend +=  "<option selected value=''>&nbsp;</option>";
                            <? foreach($alarm_type_appliances as $index=>$data): ?>
                            applianceAppend +=  "<option value='<?=$data['alarm_type_id'];?>'><?=$data['alarm_type'];?></option>";
                            <? endforeach; ?>
                            applianceAppend +=  "</select>";
                            applianceAppend +=  "</td>";
                            applianceAppend += "    <td>"
                            applianceAppend += "        <select name='appliance_pass[]' class='app_pass'>";
                            applianceAppend += "            <option value='1'>Pass</option>";
                            applianceAppend += "            <option value='0'>Fail</option>";
                            applianceAppend += "        </select>";
                            applianceAppend += "    </td>";
                            applianceAppend += "    <td>"
                            applianceAppend +=  "<select type=text name='appliance_reason[]' size=1 class='app_reason'>";
                            applianceAppend +=  "<option selected value=''>&nbsp;</option>";
                            <? foreach($alarm_reason_appliances as $index=>$data): ?>
                            applianceAppend +=  "<option value='<?=$data['alarm_reason_id'];?>'><?=$data['alarm_reason'];?></option>";
                            <? endforeach; ?>
                            applianceAppend +=  "</select>";
                            applianceAppend += "    </td>";
                            applianceAppend += "    <td><input type='text' name='appliance_make[]' value='" + app_make + "' /></td>";
                            applianceAppend += "    <td><input type='text' name='appliance_ts_location[]' value='" + app_ts_location + "' />";
                            applianceAppend += "    </td>";
                            applianceAppend += "    <td><input type='text' name='appliance_ts_comments[]' value='" + app_ts_comments + "' /></td>";
                            applianceAppend += "    <td><a href='?id=<?=$job_id;?>&delalarm=" +  data.data.alarm_id + "' onclick=\"return confirm('Are you sure you want to delete this appliance?');\">Del</a></td>";
                            applianceAppend += "</tr>";

                            var table_length = $("#existing_appliances tbody tr").length;

                            if(table_length == 0)
                            {
                                $("#existing_appliances tbody").append(applianceAppend);
                            }
                            else
                            {
                                $("#existing_appliances tbody tr:last").after(applianceAppend);
                            }

                            console.log(app_reason);
                            console.log(app_pass);
                            

                            // Set the select menus to their chosen value
                            $("#existing_appliances tr#row_" + _ROW_COUNTER + " select.app_type").val(app_type).attr("selected", "selected");
                            $("#existing_appliances tr#row_" + _ROW_COUNTER + " select.app_pass").val(app_pass).attr("selected", "selected");
                            $("#existing_appliances tr#row_" + _ROW_COUNTER + " select.app_reason").val(app_reason).attr("selected", "selected");

                            // Reapply zebra stripes
                            $("#existing_appliances tr:odd").addClass("grey");

                            // Reset fields
                            $("input#app_make").val("");
                            $("input#app_ts_comments").val("");
                            $("input#app_ts_location").val("");

                            updateApplianceCount();

                            alert("Appliance Added Successfully");

                            // Hide Error Message
                            $("div#appliance_error").fadeOut();

                            _ROW_COUNTER++;


                        }
                        else
                        {
                            alert("Technical problem, please try again");
                        }
                    }
                });
            }


            return false;
        });
    });

</script>
<input type="hidden" name="appliance_count" value="<?=$num_existing_appliances;?>" />
<? if($num_existing_appliances == 0):
?>
<div class="error" id="appliance_error">
    Note: no existing appliances are listed on this properties file, please add appliances into the 'New Appliances' section below, or contact office.
</div>
<?php endif; ?>
<table border=0 cellspacing=0 cellpadding=0 width=100% class="tech_table" id="existing_appliances">
    <thead>
        <tr>
            <td class="techsheet_header" colspan="10" style="border-bottom: 1px solid #000;">Appliance Data - Next Retest Date <?php echo $job_details['retest_date'];?></td>
        </tr>
        <tr>
            <td class="techsheet_header" syle="text-align: center;">#</td>
            <td class="techsheet_header">Type</td>
            <td class="techsheet_header">Pass/Fail</td>
            <td class="techsheet_header">Reason</td>
            <td class="techsheet_header">Appliance</td>
            <td class="techsheet_header">Location</td>
            
            <td class="techsheet_header">Comments</td>
            <td class="techsheet_header">Del</td>
        </tr>
        </thead>
        <tbody>
        <?php

        for($x = 0; $x < $num_existing_appliances; ++$x ):

            if(isset($_POST['pass'][$x]))
            {
                $appliances[$x]['pass'] = $_POST['pass'][$x];
            }

        ?>
        <tr class="<?php echo  ($x % 2 == 0 ? "off" : "grey"); ?>">

            <td>
                <input type='hidden' name='appliance_alarm_id_<?=$x;?>' value='<?=$appliances[$x]['alarm_id'];?>' />
                <input type='hidden' name='appliance_alarm_id[]' value='<?=$appliances[$x]['alarm_id'];?>' />
                <input type='hidden' name='appliance_new[]' value='0' />
                <?php echo $appliances[$x]['ts_item_number']; ?></td>
            <td>
            <select type=text name='appliance_alarm_type[]' id='alarm_type' size=1>
                <option selected value=''>&nbsp;</option>
                <? foreach($alarm_type_appliances as $index=>$data): ?>
                <option value='<?=$data['alarm_type_id'];?>' <?php echo $appliances[$x]['alarm_type_id'] == $data['alarm_type_id'] ? " selected " : "";?>><?=$data['alarm_type'];?></option>
                <? endforeach; ?>
            </select>
            </td>
            <td>
                <select name="appliance_pass[]">
                    <option value="1" <?php echo $appliances[$x]['pass'] ? "selected" : ""; ?>>Pass</option>
                    <option value="0" <?php echo !$appliances[$x]['pass'] ? "selected" : ""; ?>>Fail</option>
                </select>
            </td>
            <td>
                <select name="appliance_reason[]">
                    <option value=''>&nbsp;</option>
                    <? foreach($alarm_reason_appliances as $index=>$data): ?>
                    <option value='<?=$data['alarm_reason_id'];?>'  <?php echo $appliances[$x]['alarm_reason_id'] == $data['alarm_reason_id'] ? " selected " : "";?>><?=$data['alarm_reason'];?></option>
                    <? endforeach; ?>
                </select>
            </td>
            <td><input type="text" name="appliance_make[]" value="<?php echo $_POST['make'][$x] ? $_POST['make'][$x] : $appliances[$x]['make'];?>" /></td>
            <td><input type="text" name="appliance_ts_location[]" value="<?php echo $_POST['ts_location'][$x] ? $_POST['ts_location'][$x] : $appliances[$x]['ts_location'];?>" /></td>
            <td><input type="text" name="appliance_ts_comments[]" value="<?php echo $_POST['ts_comments'][$x] ? $_POST['ts_comments'][$x] : $appliances[$x]['ts_comments'];?>" /></td>
            <td><? if($appliances[$x]['ts_added'] == 1):
            ?>
            <a href="?id=<?=$job_id;?>&delalarm=<?=$appliances[$x]['alarm_id'];?>" onclick="return confirm('Are you sure you want to delete this appliance?');" >Del</a><? else:?>
            N/A
            <? endif;?></td>
        </tr>
        <?php endfor; ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="8" class="techsheet_header">Total Number of Appliances: <span id='appliance_count'></span></td>
        </tr>
        </tfoot>
    </table>
    <table border=0 cellspacing=0 cellpadding=5 width=100% class="tech_table">
        <tr>
            <td colspan="4" class="techsheet_header">Enter New Appliance Details</td>
        </tr>
        <tr>
            <td>
            <table cellpadding=2 cellspacing=0 width=100% border=0 id='appliance_table'>
                <tr>
                    <td>Type</td>
                    <td>Pass</td>
                    <td>Reason</td>
                    <td>Appliance</td>
                    <td>Comments</td>
                    <td>Location</td>
                    <td>Add</td>    
                </tr>

                <tr bgcolor=#F0F0F0>
                <td>
                    <input type='hidden' name='app_new' id='app_new' value='1' />
                    <input type='hidden' name='app_new_alarm_id' id='app_alarm_id' value='0' />
                    <select type=text name='app_new_type' id='app_type' size=1>
                    <option selected value=''>&nbsp;</option>
                    <? 

                    foreach($alarm_type_appliances as $index=>$data): ?>
                    <option value='<?=$data['alarm_type_id'];?>'><?=$data['alarm_type'];?></option>
                    <? endforeach; ?>
                    </select>
                                    
                    </td>
                    <td><select type=text id='app_pass' name='app_new_pass' size=1><option value='1'>Pass</option><option value='0'>Fail</option></select></td>
                    <td>
                    <select type=text id='app_reason' name='app_new_reason' size=1>
                    <option selected value=''>&nbsp;</option>
                    <? foreach($alarm_reason_appliances  as $index=>$data): ?>
                    <option value='<?=$data['alarm_reason_id'];?>'><?=$data['alarm_reason'];?></option>
                    <? endforeach; ?>
                    </select>
                    </td>
            
                    <td><input type=text id='app_make' name='app_new_make' value='' class='ts_appliance'></td>
                    <td><input type=text id='app_ts_comments' name='app_new_ts_comments' value='' class='ts_comments'></td>
                    <td><input type=text id='app_ts_location' name='app_new_ts_location' value='' class='ts_location'></td>
                    <td align=center style='text-align: center;'><button id='add_appliance'>Add</button></td>
                </tr>

            </table>

            </td>
        </tr>
    </table>
