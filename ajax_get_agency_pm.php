<?php
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$agency_id = mysql_real_escape_string($_POST['agency_id']);

//$new_pm = 0;
$new_pm = NEW_PM;

if( $new_pm == 1 ){ // NEW PM

	$custom_select = "
		aua.`agency_user_account_id`,
		aua.`fname`,
		aua.`lname`,
		aua.`email`
	";

	$pm_params = array( 
		'custom_select' => $custom_select,
		'agency_id' => $agency_id,
		'active' => 1,
		'sort_list' => array(
			array(
				'order_by' => 'aua.`fname`',
				'sort' => 'ASC'
			),
			array(
				'order_by' => 'aua.`lname`',
				'sort' => 'ASC'
			)
		),
		'echo_query' => 0
	 );
	$pm_sql = Sats_Crm_Class::getNewPropertyManagers($pm_params);
	
	if( mysql_num_rows($pm_sql) > 0 ){ ?>
		<option value="">--- Select ---</option>
		<?php
		while( $pm = mysql_fetch_array($pm_sql) ){
		?>
			<option value="<?php echo $pm['agency_user_account_id']; ?>"><?php echo "{$pm['fname']} {$pm['lname']}" ?></option>
		<?php
		}
	}else{ ?>
		<option value="">--- No Property Managers ---</option>
	<?php	
	}

}else{ // OLD PM

	$pm_sql = mysql_query("
		SELECT *
		FROM `property_managers`
		WHERE `agency_id` = {$agency_id}
	");			
	if( mysql_num_rows($pm_sql) > 0 ){ ?>
		<option value="">--- Select ---</option>
		<?php
		while( $pm = mysql_fetch_array($pm_sql) ){
		?>
			<option value="<?php echo $pm['property_managers_id']; ?>"><?php echo $pm['name'] ?></option>
		<?php
		}
	}else{ ?>
		<option value="">--- No Property Managers ---</option>
	<?php	
	}
	
}
?>	