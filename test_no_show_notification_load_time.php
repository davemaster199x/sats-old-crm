<?php
include('inc/init_for_ajax.php');

// No Show Replies Alert
$list_params = array(
	'custom_filter' => "AND CAST( sar.`created_date` AS DATE ) = '".date('Y-m-d')."'",
	'unread' => 1,
	'sms_type' => 4,
	'sms_page' => 'incoming',
	'echo_query' => 0
);
$isms_crm = new Sats_Crm_Class;
$isms_sql = $isms_crm->getSMSrepliesMergedData($list_params);
$no_show_sms_num = mysql_num_rows($isms_sql);

if(  $no_show_sms_num>0 ){ 
	$warning_icon = 'warning_red.png';
	
}else{
	$warning_icon = 'warning_grey.png';
}
if( $no_show_sms_num > 0 ){ ?>
	<span class="notification_bubble no_show_sms_notif_bubble"><?php echo $no_show_sms_num; ?></span>
<?php
}	
?>
<a href="incoming_sms.php">
	<img src="images/<?php echo $warning_icon; ?>" title="No Show Reply Available" style="margin:4px 5px 0 0; width: 20px;" />
</a>