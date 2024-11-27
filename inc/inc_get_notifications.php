<?php
$crm2 = new Sats_Crm_Class();

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$country_id = $_SESSION['country_default'];

if( $staff_id != '' ){

// get sound notification marker
$jparams = array(
	'staff_id' => $staff_id
);
$sa_sql = $crm2->getStaffAccount($jparams);
$sa = mysql_fetch_array($sa_sql);

// play notification sound
$sound_notif = $sa['sound_notification'];

?>
<style>
.notication_div{
	float: right;
	position: relative;
	top: 14px;
	right: 14px;
}

.notification_bubble{
	background-color: #00aeef;
	border-radius: 50px;
	color: white;
	font-size: 10px;	
	text-align: center;
	position: relative;
	bottom: 1px;
	right: 2px;
	padding: 2px 5px;
}



.notification_icons{
	width: 20px; 
	position: relative; 
	top: 4px; 
	right: 5px;
	cursor: pointer;
	margin-left: 5px;
}

.notification_box{
	position: absolute;
	border: 1px solid #00aeef;
	width: 590px;
	right: 112px;
	display: none;
	padding: 0 !important;
	z-index: 99999999;
	background-color: white;
}

.notification_box ul{
	list-style-type: none;
	padding: 0;
	margin: 0;
	text-align: left;
}

.notification_box ul li{
	padding: 5px;
	border-bottom: 1px solid #cccccc;
}
.main_notf_div {
    float: left;
}
.noft_bubble_gen {
    left: 2px;
}
.no_show_sms_notif_bubble{
	position: relative;
	bottom: 15px;
	left: 8px;
}
.low_sms_credit_span{
	color: #b4151b;
	margin-right: 6px;
}
</style>
<?php
$notf_arr = [];

// notification type
// 1 - general 
// 2 - SMS
// 3 - No Show SMS
$notf_arr = array(
	array(
		'notf_type' => 2,
		'noft_bubble' => 'noft_bubble_sms',
		'button_name' => 'notifications-sms',
		'notf_title' => 'SMS Notification'
	),
	array(
		'notf_type' => 1,
		'noft_bubble' => 'noft_bubble_gen',
		'button_name' => 'notifications-button',
		'notf_title' => 'General Notification'
	),
	array(
		'notf_type' => 3,
		'noft_bubble' => 'noft_bubble_no_show_sms',
		'button_name' => 'notifications-no_show_sms',
		'notf_title' => 'SMS No Show Notification'
	)
);

foreach( $notf_arr as $notf_data ){ ?>

	<div class="main_notf_div">
		<?php
		// get  notification
		$jparams = array(
			'notf_type' => $notf_data['notf_type'],
			'notify_to' => $staff_id,			
			'sort_list' => array(
				array(
					'order_by' => 'n.`date_created`',
					'sort' => 'DESC'
				)
			),
			'paginate' => array(
				'offset' => 0,
				'limit' => 15
			)
		);
		$n_sql = $crm2->getNotifications($jparams);
		$n_num = mysql_num_rows($n_sql);
		
		

		if( $n_num >0 ){ 
		?>
		
			<!-- NOTIFICATION BOX -->
			<div class="notification_box" data-notf_type="<?php echo $notf_data['notf_type']; ?>">
				<ul>
					<?php
					while( $n = mysql_fetch_array($n_sql) ){ 
					$sms_notf_msg = '';
					
					// need to append sent by filter for SMS notification
					if( $notf_data['notf_type'] == 2 ){ // SMS
						$sms_notf_msg = str_replace('incoming_sms.php',"incoming_sms.php?sent_by={$staff_id}",$n['notification_message']); 
					}if( $notf_data['notf_type'] == 3 ){ // SMS NO SHOW
						$sms_type = 4; // No Show
						$sms_notf_msg = str_replace('incoming_sms.php',"incoming_sms.php?sms_type={$sms_type}",$n['notification_message']); 
					}else{
						$sms_notf_msg = $n['notification_message']; 
					}

					$domain = $_SERVER['SERVER_NAME'];
					if (CURRENT_COUNTRY == 1) { // AU

						if (strpos($domain, "crmdev") !== false) { // DEV
							$crm_ci_domain = 'https://crmdevci.sats.com.au';
						} else { // LIVE
							$crm_ci_domain = 'https://crmci.sats.com.au';
						}

					} else if (CURRENT_COUNTRY == 2) { // NZ

						if (strpos($domain, "crmdev") !== false) { // DEV
							$crm_ci_domain = 'https://crmdevci.sats.co.nz';
						} else { // LIVE
							$crm_ci_domain = 'https://crmci.sats.co.nz';
						}

					}

					// append crm CI domain to incoming SMS notification link
					$sms_notf_msg = str_replace('sms/view_incoming_sms',"{$crm_ci_domain}/sms/view_incoming_sms",$n['notification_message']);
					
					?>
						<li <?php echo ($n['read']==1)?'style="background-color: #f2f2f2;"':'' ?>><?php echo $sms_notf_msg; ?></li>
					<?php	
					}
					?>
				</ul>
			</div>

			<!-- NOTIFICATION ICON AND BUBBLE -->
			<?php
			// get unread notification count
			$jparams = array(
				'notf_type' => $notf_data['notf_type'],
				'return_count' => 1,
				'notify_to' => $staff_id,
				'read' => 0
			);
			$notf_count = $crm2->getNotifications($jparams);
			if( $notf_count>0 ){ 
				//$sound_notif = 1;
			?>
				<!-- NOTIFICATION BUBBLE -->
				<div class="notification_bubble <?php echo $notf_data['noft_bubble']; ?>" style="float:left;"><?php echo $notf_count; ?></div>
			<?php
			}
			?>
			<!-- NOTIFICATION ICON -->
			<div style="float:left;">		
				<img class="notification_icons" title="<?php echo $notf_data['notf_title']; ?>" src="/images/<?php echo $notf_data['button_name'].''.( ($notf_count>0)?'-red':'' ); ?>.png" />
			</div>

		<?php
		}
		?>
	</div>

<?php
}
?>
<input type="hidden" class="sound_nofication" value="<?php echo $sound_notif; ?>" />
<?php
}
?>

<?php
// SMS credit low alert
$sms_cred_sql = $crm2->getCrmSettings($country_id);
$sms_cred = mysql_fetch_array($sms_cred_sql);
$current_sms_cred = $sms_cred['sms_credit'];
// credit limit
if( $country_id == 1 ){ // AU
	$credit_trigger_val = 50000;
}else if( $country_id == 2 ){ // NZ
	$credit_trigger_val = 1500;
}

if( ( $current_sms_cred <= $credit_trigger_val ) && $country_id == 2 ){ // updated to NZ only ?>
	<div style="float:left;">		
		<img class="notification_icons" title="LOW CREDIT" src="/images/notifications-no_show_sms-red.png">
		<span class="low_sms_credit_span">LOW SMS CREDIT</span>
	</div>
<?php
}

?>
