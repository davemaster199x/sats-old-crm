<?php

include('inc/init_for_ajax.php');

$agency_id = mysql_real_escape_string($_POST['agency_id']);

$sql = "SELECT * FROM agency_user_accounts WHERE agency_id={$agency_id} AND active=1";
$query = mysql_query($sql);

?>

<!-- PM label and dropdown start -->
<div class="pm_div">
    <label class="addlabel" for="agency">Select PM</label>
    <select name="pm_id_new" style="float:left;">
        <option value="">Please Select</option>
        <?php 
        while( $pm_row = mysql_fetch_array($query) ){
        ?>
            <option value="<?php echo $pm_row['agency_user_account_id'] ?>"><?php echo "{$pm_row['fname']} {$pm_row['lname']}" ?></option>
        <?php
        }
        ?>
    </select>
</div>
<!-- PM label and dropdown end -->