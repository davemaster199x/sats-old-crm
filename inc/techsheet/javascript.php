<?php

if(!defined('TECH_SHEET_INC'))
{
    exit();
}

?><script type="text/javascript">

    function updateApplianceCount()
    {
        var app_count = $("table#existing_appliances tbody tr").length;
        $("span#appliance_count").html(app_count);
        return true;
    }

    function updateSafetySwitchCount()
    {
        var app_count = $("table.existing_safety_switch:first tbody tr").length;
        $("span.safety_switch_count").html(app_count);
        return true;
    }

    function updateCordedWindowCount()
    {
        var window_count = $("table.existing_corded_window:first tbody tr").length;
        $("span.corded_window_count").html(window_count);
        return true;
    }

    $(document).ready(function() {
        $("#noshow-modal").fancybox({
                'modal' : true
        });
        
        $("#noshow-modal-rebook").fancybox({
                'modal' : true
        });
        
        $("#noshow-modal-doorknock").fancybox({
                'modal' : true
        });

       

    });

    

</script>
