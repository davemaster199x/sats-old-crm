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

        updateSafetySwitchCount();
    
        $("button#add_ss_mech").live('click', function() {

            var error = 0;
            var error_message = "";

            // Retrieve form values and prepare to validate / submit
            var ss_mech_new = $("input#ss_mech_new").val();
            var ss_mech_alarm_id = $("input#ss_mech_alarm_id").val();
            var ss_mech_pass = $("select#ss_mech_pass option:selected").val();
            var ss_mech_ts_comments = $("input#ss_mech_ts_comments").val().trim();
            var ss_type = $("select#ss_type option:selected").val();
            var job_id = $("input#job_id").val();

            // Minimum we need is Type / Pass / Reason / Applianace
            if(!ss_mech_pass)
            {
                error = 1;
                error_message += "\n" + "Please enter the Pass result";
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
                            "&ss_mech_new=" + ss_mech_new +
                            "&ss_mech_alarm_id=" + ss_mech_alarm_id +
                            "&ss_pass=" + ss_mech_pass +
                            "&ss_ts_comments=" + ss_mech_ts_comments + 
                            "&alarm_job_type_id=4" +
                            "&ss_type=" + ss_type,
                    url: "ajax/add_ss_mech.php",
                    cache: false,
                    dataType: "json",
                    success: function(data){

                        if(data.status == "success")
                        {
                            var applianceAppend = "";
                            applianceAppend += "<tr class='new_row' id='row_" + _ROW_COUNTER + "'>";
                            applianceAppend += "    <td>";
                            applianceAppend += "        <input type='hidden' name='ss_alarm_id_" + data.data.alarm_id + "' value='" + data.data.alarm_id + "' />";
                            applianceAppend += "        <input type='hidden' name='ss_alarm_id[]' value='" + data.data.alarm_id + "' />";
                            applianceAppend += "        " + data.data.appliance_number + "</td>";

                            applianceAppend +=  "<td><select type=text name='ss_type[]' class='app_type' size=1>";
                            applianceAppend +=  "<option selected value=''>&nbsp;</option>";
                            <? foreach($alarm_type_safety_switch as $index=>$data): ?>
                            applianceAppend +=  "<option value='<?=$data['alarm_type_id'];?>'><?=$data['alarm_type'];?></option>";
                            <? endforeach; ?>
                            applianceAppend +=  "</select>";
                            applianceAppend +=  "</td>";
                            applianceAppend += "    <td>"
                            applianceAppend += "        <select name='ss_pass[]' class='ss_mech_pass'>";
                            applianceAppend += "            <option value='1'>Pass</option>";
                            applianceAppend += "            <option value='0'>Fail</option>";
                            applianceAppend += "        </select>";
                            applianceAppend += "    </td>";
                            applianceAppend += "    <td><input type='text' class='ts_comments_long' name='ss_comments[]' value='" + ss_mech_ts_comments + "' /></td>";
                            applianceAppend += "    <td><a href='?id=<?=$job_id;?>&delalarm=" +  data.data.alarm_id + "' onclick=\"return confirm('Are you sure you want to delete this Safety Switch?');\">Del</a></td>";
                            applianceAppend += "</tr>";

                            var table_length = $("#existing_safety_switch tbody tr").length;

                            if(table_length == 0)
                            {
                                $(".existing_safety_switch tbody").append(applianceAppend);
                            }
                            else
                            {
                                $(".existing_safety_switch tbody tr:last").after(applianceAppend);
                            }

                            // Set the select menus to their chosen value
                            $(".existing_safety_switch tr#row_" + _ROW_COUNTER + " select.ss_mech_pass").val(ss_mech_pass).attr("selected", "selected");
                            $(".existing_safety_switch tr#row_" + _ROW_COUNTER + " select.app_type").val(ss_type).attr("selected", "selected");

                            // Reapply zebra stripes
                            $(".existing_safety_switch tr:odd").addClass("grey");

                            // Reset fields
                            $("input#ss_mech_ts_comments").val("");

                            updateSafetySwitchCount();

                            alert("Safety Switch Added Successfully");

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
<input type="hidden" name="safety_switch_count" value="<?=$num_existing_ss;?>" />
<? if($num_existing_ss == 0):
?>
<div class="error" id="appliance_error">
    Note: no existing safety switches are listed on this properties file, please add using the 'New Safety Switch' section below, or contact office.
</div>
<?php endif; ?>
<table border=0 cellspacing=0 cellpadding=0 width=100% class="tech_table existing_safety_switch">
    <thead>
        <tr>
            <td class="techsheet_header" colspan="10" style="border-bottom: 1px solid #000;">Existing Safety Switch Data</td>
        </tr>
        <tr>
            <td class="techsheet_header" syle="text-align: center;">#</td>
            <td class="techsheet_header">Type</td>
            <td class="techsheet_header">Circuit Pass/Fail</td>
            <td class="techsheet_header">Comments</td>
            <td class="techsheet_header">Del</td>
        </tr>
        </thead>
        <tbody>
        <?php

        for($x = 0; $x < $num_existing_ss; ++$x ):

            if(isset($_POST['pass'][$x]))
            {
                $safety_switches[$x]['pass'] = $_POST['pass'][$x];
            }

        ?>
        <tr class="<?php echo  ($x % 2 == 0 ? "off" : "grey"); ?>">

            <td>
                <input type='hidden' name='ss_alarm_id_<?=$x;?>' value='<?=$safety_switches[$x]['alarm_id'];?>' />
                <input type='hidden' name='ss_alarm_id[]' value='<?=$safety_switches[$x]['alarm_id'];?>' />
                <input type='hidden' name='ss_new[]' value='0' />
                <?php echo $safety_switches[$x]['ts_item_number']; ?>
            </td>
            <td>
            <select type=text name='ss_type[]' id='alarm_type' size=1>
                <option selected value=''>&nbsp;</option>
                <? foreach($alarm_type_safety_switch as $index=>$data): ?>
                <option value='<?=$data['alarm_type_id'];?>' <?php echo $safety_switches[$x]['alarm_type_id'] == $data['alarm_type_id'] ? " selected " : "";?>><?=$data['alarm_type'];?></option>
                <? endforeach; ?>
            </select>
            </td>
            <td>
                <select name="ss_pass[]">
                    <option value="1" <?php echo $safety_switches[$x]['pass'] ? "selected" : ""; ?>>Pass</option>
                    <option value="0" <?php echo !$safety_switches[$x]['pass'] ? "selected" : ""; ?>>Fail</option>
                </select>
            </td>
            <td>
                <input type="hidden" class="ts_comments" name="ss_trip_rate[]" value="<?php echo $_POST['ss_trip_rate'][$x] ? $_POST['ss_trip_rate'][$x] : $safety_switches[$x]['ts_trip_rate'];?>" />
                <input type="text" class="ts_comments_long" name="ss_comments[]" value="<?php echo $_POST['ts_comments'][$x] ? $_POST['ts_comments'][$x] : $safety_switches[$x]['ts_comments'];?>" /></td>
            <td><? if($safety_switches[$x]['ts_added'] == 1):
            ?>
            <a href="?id=<?=$job_id;?>&delalarm=<?=$safety_switches[$x]['alarm_id'];?>" onclick="return confirm('Are you sure you want to delete this safety switch?');" >Del</a><? else:?>
            N/A
            <? endif;?></td>
        </tr>
        <?php endfor; ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="8" class="techsheet_header">Total Number of Safety Switches: <span class='safety_switch_count'></span></td>
        </tr>
        </tfoot>
    </table>
    <table border=0 cellspacing=0 cellpadding=5 width=100% class="tech_table">
        <tr>
            <td colspan="4" class="techsheet_header">Add a new Safety Switch</td>
        </tr>
        <tr>
            <td>
            <table cellpadding=2 cellspacing=0 width=100% border=0 id='appliance_table'>
                <tr>
                    <td>Type</td>
                    <td>Curcuit Pass / Fail</td>
                    <td>Comments</td>
                    <td style='text-align: right;'>Add&nbsp;&nbsp;</td>    
                </tr>

                <tr bgcolor=#F0F0F0>
                    <td>
                        <select type=text name='ss_type_new' id='ss_type' size=1>
                        <option selected value=''>&nbsp;</option>
                        <? 

                        foreach($alarm_type_safety_switch as $index=>$data): ?>
                        <option value='<?=$data['alarm_type_id'];?>'><?=$data['alarm_type'];?></option>
                        <? endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input type='hidden' name='ss_mech_new' id='ss_mech_new' value='1' />
                        <input type='hidden' name='ss_mech_alarm_id' id='ss_mech_alarm_id' value='0' />
                        <select type=text id='ss_mech_pass' name='ss_mech_pass' size=1><option value='1'>Pass</option><option value='0'>Fail</option></select>
                    </td>
                    <td><input type=text id='ss_mech_ts_comments' name='ss_mech_ts_comments' value='' class='ts_comments_long'></td>
                    <td align=center style='text-align: right;'><button id='add_ss_mech'>Add</button></td>
                </tr>

            </table>

            </td>
        </tr>
    </table>
