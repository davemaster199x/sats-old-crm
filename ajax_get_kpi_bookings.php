<?php
include('inc/init_for_ajax.php');
// intiate class
$crm = new Sats_Crm_Class;
$country_id = CURRENT_COUNTRY;
?>
<div class='jdiv'>

    <table id="jtable1" border=0 cellspacing=0 cellpadding=5 class='table-center tbl-fr-red jtable' style="width:auto;">
    <tr>					
        <th colspan="8" class="row_bg_color">Bookings</th>			
    </tr>
    <tr style="background-color: #eeeeee;">
        <td><strong>Day</strong></td>
        <td><strong>Date</strong></td>
        <td><strong>Booked</strong></td>
        <td><strong>DKs</strong></td>
        <td><strong>Completed</strong></td>
        <td><strong>Techs</strong></td>
        <td><strong>Average</strong></td>
        <td><strong>Estimated Income</strong></td>
    </tr>
    <?php
    for($i=0;$i<=4;$i++){ 
        // date
        $date = date('Y-m-d',strtotime("+{$i} day")); 
        $day = date('l',strtotime($date));	
    ?>
        <tr <?php echo ( $day=="Saturday" || $day=="Sunday" )?'style="background-color: #ececec;"':''; ?>>
            <td>
                <?php 
                echo $day;
                ?>
            </td>
            <td>
                <?php 
                // date
                echo date('d/m/Y',strtotime($date));
                ?>
            </td>
            <td>
                <?php
                // booked
                $params = array(
                    'booked' => 1,
                    'dk' =>0,
                    'date' => $date,
                    'country_id' => $country_id,
                    'return_count' => 1
                );
                $booked_count = number_format($crm->getJobs($params));	
                echo ($booked_count>0)?$booked_count:'';
                ?>
            </td>
            <td>
                <?php
                // DK's
                $params = array(
                    'dk' => 1,
                    'status_booked_or_completed' => 1,
                    'date' => $date,
                    'country_id' => $country_id,
                    'return_count' => 1
                );
                $jobserv_count = number_format($crm->getJobs($params));	
                echo ($jobserv_count>0)?$jobserv_count:'';
                ?>
            </td>
            <td>
                <?php
                // completed
                $params = array(
                    'ts_completed' => 1,
                    'date' => $date,
                    'country_id' => $country_id,
                    'return_count' => 1
                );
                $jobserv_count = number_format($crm->getJobs($params));
                echo ($jobserv_count>0)?$jobserv_count:'';				
                ?>
            </td>
            <td>
                <?php
                // tech
                $params = array(
                    'status_booked_or_completed' => 1,
                    'exclude_tech_other_supplier' => 1,
                    'distinct' => 'tech_id',
                    'date' => $date,
                    'country_id' => $country_id
                );
                $tsql = $crm->getJobs($params);	
                $tech_count = number_format(mysql_num_rows($tsql));
                echo ($tech_count>0)?$tech_count:'';	
                ?>
            </td>
            <td>
                <?php 
                // average
                // booked and completed
                $params = array(
                    'status_booked_or_completed' => 1,
                    'exclude_tech_other_supplier' => 1,
                    'date' => $date,
                    'country_id' => $country_id,
                    'return_count' => 1
                );
                $booked_and_completed_job_count = $crm->getJobs($params);	
                $jobcount_ave = floor($booked_and_completed_job_count/$tech_count); 
                echo ($jobcount_ave>0)?$jobcount_ave:'';
                ?>
            </td>
            <td>
                <?php
                // estimated income
                // job price
                $params = array(
                    'query_for_estimated_income' => 1,
                    'date' => $date,
                    'country_id' => $country_id,
                    'sum_job_price' => 1
                );
                $jp_sql = $crm->getJobs($params);
                $jp = mysql_fetch_array($jp_sql);
                $job_price = $jp['j_price'];
                
                // alarm price
                $params = array(
                    'new_alarm' => 1,
                    'query_for_estimated_income' => 1,
                    'date' => $date,
                    'country_id' => $country_id,
                    'sum_alarm_price' => 1,
                    'ts_discarded' => 0
                );
                $ap_sql = $crm->getAlarms($params);
                $ap = mysql_fetch_array($ap_sql);
                $alarm_price = $ap['a_price'];
                
                $job_tot = $job_price+$alarm_price;
                
                if($job_tot>0){
                    echo '$'.number_format($job_price+$alarm_price, 2);
                }else{
                    echo '';
                }			
                ?>
            </td>
        </tr>
    <?php	
    }
    ?>	
    </table>
    
</div>