<?php

$title = "Call Centre";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');


$date = ($_REQUEST['date']=="")?date('d/m/Y'):mysql_real_escape_string($_REQUEST['date']);
$date2 = jFormatDateToBeDbReady($date);


function getCallCentreData($date){
	
	return mysql_query("
		SELECT *, ccd.`staff_id` AS ccd_staff_id, ccd.`date` AS ccd_date
		FROM `call_centre_data` AS ccd
		LEFT JOIN `staff_accounts` AS sa ON ccd.`staff_id` = sa.`StaffID`
		WHERE ccd.`country_id` = {$_SESSION['country_default']}
		AND ccd.`date` = '{$date}'
	");
	
}

// if schedule fall outside of shift
function OutsideShift($shift_from_mt,$shift_to_mt,$col_time_start,$col_time_end){
	return !( ($col_time_start >= $shift_from_mt && $col_time_start <= $shift_to_mt) && ($col_time_end >= $shift_from_mt && $col_time_end <= $shift_to_mt) )?true:false;
}

// color it grey
function colorItGrey($shift_from_mt,$shift_to_mt,$col_time_start,$col_time_end){
	
	if( OutsideShift($shift_from_mt,$shift_to_mt,$col_time_start,$col_time_end)==true  ){
		return 'colorItGrey';
	} else{
		return '';
	}

		
}

?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.jRightSideBorder{
	border-right: 1px solid #cccccc;
}
.jLeftSideBorder{
	border-left: 1px solid #cccccc;
}
.table_header th{
	text-align: center;
}
.no_padding{
	padding: 0px;
}
.right_border{
	border-right: 1px solid #cccccc;
}
.centerEm{
	text-align: center;
}
.colorItGrey{
	background-color: #ececec;
}
.colorItRed{
	color:red;
}
</style>
<div id="mainContent">

	
   
    <div class="sats-middle-cont">
	
		
	
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="<?php echo $title; ?>" href="/call_centre_report.php"><strong><?php echo $title; ?></strong></a></li>
		  </ul>
		</div>
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">New Data has been Added!</div>';
		}else if($_GET['success']==2){
			echo '<div class="success">Data has been Updated!</div>';
		}else if($_GET['del']==1){
			echo '<div class="success">Data has been Deleted!</div>';
		}
		
		
		?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		<div style="border: 1px solid #cccccc;" class="aviw_drop-h">	
			
			<form method="POST">
				<div class="fl-left">
					<label>Date:</label>
					<input type="label" name="date" id="date" style="width:85px!important;" class="addinput searchstyle datepicker" value="<?php echo $date; ?>" />		
				</div>
			
				<div class="fl-left" style="float:left;">
					<input type="submit" value="Go" class="submitbtnImg">
				</div>
			</form>
			
			
			
			
		
			<div class="fl-left">
				<a href="javascript:void(0);"><button class="submitbtnImg" type="button" style="visibility: hidden;">Export</button></a>
			</div>	
		</div>

		
				<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd jtable" style="margin-top: 0px; margin-bottom: 13px;">
						<tr class="toprow jalign_left table_header">
							<th>Name</th>
							<th>Shift</th>
							<th>Check In</th>
							<th>First Call</th>
							<th>Last Call</th>							
							<th>7-8 AM</th>
							<th>8-9 AM</th>
							<th>9-10 AM</th>
							<th>10-11 AM</th>
							<th>11-12 PM</th>
							<th>12-1 PM</th>
							<th>1-2 PM</th>
							<th>2-3 PM</th>
							<th>3-4 PM</th>
							<th>4-5 PM</th>
							<th>5-6 PM</th>
							<th>6-7 PM</th>
							<th>Total</th>
							<th>Strike Rate</th>
							<th>Edit</td>
							<th>Book Count</td>
						</tr>
						
						

				<?php
				
				
				
				$ccd_sql = getCallCentreData($date2);
				
				$num_row = mysql_num_rows($ccd_sql);
				
				if( $num_row>0 ){
				
				while($ccd = mysql_fetch_array($ccd_sql)){ 
				$total_call = 0;
				?>

				
					
							
									<tr class="body_tr jalign_left">
										<td class="no_padding right_border"><?php echo "{$ccd['FirstName']} ".substr($ccd['LastName'],0,1)."."; ?></td>
										<td class="no_padding right_border centerEm">
											<?php 
												$shift = "{$ccd['shift_from']}-{$ccd['shift_to']}"; 
												$shift_from_mt = ($ccd['shift_from']!="")?$ccd['shift_from']:'';
												$shift_to_mt = ($ccd['shift_to']!="")?date("H",strtotime($ccd['shift_to']." PM")):'';
											?>
											<span class="txt_lbl"><?php echo $shift; ?></span>
											<span class="txt_hid">
											<input type="number" class="shift_from shift_fields" style="width:44px" value="<?php echo $ccd['shift_from']; ?>" />
											- <input type="number" class="shift_to shift_fields" style="width:44px" value="<?php echo $ccd['shift_to']; ?>" />
											</span>
										</td>
										<td class="no_padding right_border centerEm">
											<span class="txt_lbl"><?php echo $ccd['check_in']; ?></span>
											<input type="text" class="txt_hid check_in" value="<?php echo $ccd['check_in']; ?>" />
										</td>
										<td class="no_padding right_border centerEm">
											<span class="txt_lbl"><?php echo $ccd['first_call']; ?></span>
											<input type="text" class="txt_hid first_call" value="<?php echo $ccd['first_call']; ?>" />
										</td>	
										<td class="no_padding right_border centerEm">
											<span class="txt_lbl"><?php echo $ccd['last_call']; ?></span>
											<input type="text" class="txt_hid last_call" value="<?php echo $ccd['last_call']; ?>" />
										</td>	
										<?php
										$col_time_start = 7;
										$col_time_end = 8;
										$call_val = $ccd['7-8_am'];
										$total_call += $call_val;
										?>
										<td class="no_padding <?php echo colorItGrey($shift_from_mt,$shift_to_mt,$col_time_start,$col_time_end); ?>">
											<table>
												<tr>
													<td>Call</td>
													<td>Book</td>
												</tr>
												<tr>
													<td>
														<span class="txt_lbl <?php echo ($call_val<30)?'colorItRed':''; ?>"><?php echo ( $call_val=='' )?'&nbsp;':$call_val; ?></span>
														<input type="text" class="txt_hid 7-8_am" value="<?php echo ( $call_val=='' )?'':$call_val; ?>" />
													</td>
													<td>
														<span class="booked_count_col">&nbsp;</span>
														<input type="hidden" class="col_time_start" value="<?php echo $col_time_start ?>" />
														<input type="hidden" class="col_time_end" value="<?php echo $col_time_end ?>" />
													</td>
												</tr>
											</table>
										</td>
										<?php
										$col_time_start = 8;
										$col_time_end = 9;
										$call_val = $ccd['8-9_am'];
										$total_call += $call_val;
										?>
										<td class="no_padding <?php echo colorItGrey($shift_from_mt,$shift_to_mt,$col_time_start,$col_time_end); ?>">	
											<table>
												<tr>
													<td>Call</td>
													<td>Book</td>
												</tr>
												<tr>
													<td>
														<span class="txt_lbl <?php echo ($call_val<30)?'colorItRed':''; ?>"><?php echo ( $call_val=='' )?'&nbsp;':$call_val; ?></span>
														<input type="text" class="txt_hid 8-9_am" value="<?php echo ( $call_val=='' )?'':$call_val; ?>" />
													</td>
													<td>
														<span class="booked_count_col">&nbsp;</span>
														<input type="hidden" class="col_time_start" value="<?php echo $col_time_start ?>" />
														<input type="hidden" class="col_time_end" value="<?php echo $col_time_end ?>" />
													</td>
												</tr>
											</table>
										</td>
										<?php
										$col_time_start = 9;
										$col_time_end = 10;
										$call_val = $ccd['9-10_am'];
										$total_call += $call_val;
										?>
										<td class="no_padding  <?php echo colorItGrey($shift_from_mt,$shift_to_mt,$col_time_start,$col_time_end); ?>">	
											<table>
												<tr>
													<td>Call</td>
													<td>Book</td>
												</tr>
												<tr>
													<td>
														<span class="txt_lbl <?php echo ($call_val<30)?'colorItRed':''; ?>"><?php echo ( $call_val=='' )?'&nbsp;':$call_val; ?></span>
														<input type="text" class="txt_hid 9-10_am" value="<?php echo ( $call_val=='' )?'':$call_val; ?>" />
													</td>
													<td>
														<span class="booked_count_col">&nbsp;</span>
														<input type="hidden" class="col_time_start" value="<?php echo $col_time_start ?>" />
														<input type="hidden" class="col_time_end" value="<?php echo $col_time_end ?>" />
													</td>
												</tr>
											</table>
										</td>	
										<?php
										$col_time_start = 10;
										$col_time_end = 11;
										$call_val = $ccd['10-11_am'];
										$total_call += $call_val;
										?>
										<td class="no_padding  <?php echo colorItGrey($shift_from_mt,$shift_to_mt,$col_time_start,$col_time_end); ?>">	
											<table>
												<tr>
													<td>Call</td>
													<td>Book</td>
												</tr>
												<tr>
													<td>
														<span class="txt_lbl <?php echo ($call_val<30)?'colorItRed':''; ?>"><?php echo ( $call_val=='' )?'&nbsp;':$call_val; ?></span>
														<input type="text" class="txt_hid 10-11_am" value="<?php echo ( $call_val=='' )?'':$call_val; ?>" />
													</td>
													<td>
														<span class="booked_count_col">&nbsp;</span>
														<input type="hidden" class="col_time_start" value="<?php echo $col_time_start ?>" />
														<input type="hidden" class="col_time_end" value="<?php echo $col_time_end ?>" />
													</td>
												</tr>
											</table>
										</td>
										<?php
										$col_time_start = 11;
										$col_time_end = 12;
										$call_val = $ccd['11-12_pm'];
										$total_call += $call_val;
										?>
										<td class="no_padding <?php echo colorItGrey($shift_from_mt,$shift_to_mt,$col_time_start,$col_time_end); ?>">	
											<table>
												<tr>
													<td>Call</td>
													<td>Book</td>
												</tr>
												<tr>
													<td>
														<span class="txt_lbl <?php echo ($call_val<30)?'colorItRed':''; ?>"><?php echo ( $call_val=='' )?'&nbsp;':$call_val; ?></span>
														<input type="text" class="txt_hid 11-12_pm" value="<?php echo ( $call_val=='' )?'':$call_val; ?>" />
													</td>
													<td>
														<span class="booked_count_col">&nbsp;</span>
														<input type="hidden" class="col_time_start" value="<?php echo $col_time_start ?>" />
														<input type="hidden" class="col_time_end" value="<?php echo $col_time_end ?>" />
													</td>
												</tr>
											</table>
										</td>
										<?php
										$col_time_start = 12;
										$col_time_end = 13;
										$call_val = $ccd['12-1_pm'];
										$total_call += $call_val;
										?>
										<td class="no_padding <?php echo colorItGrey($shift_from_mt,$shift_to_mt,$col_time_start,$col_time_end); ?>">	
											<table>
												<tr>
													<td>Call</td>
													<td>Book</td>
												</tr>
												<tr>
													<td>
														<span class="txt_lbl <?php echo ($call_val<30)?'colorItRed':''; ?>"><?php echo ( $call_val=='' )?'&nbsp;':$call_val; ?></span>
														<input type="text" class="txt_hid 12-1_pm" value="<?php echo ( $call_val=='' )?'':$call_val; ?>" />
													</td>
													<td>
														<span class="booked_count_col">&nbsp;</span>
														<input type="hidden" class="col_time_start" value="<?php echo $col_time_start ?>" />
														<input type="hidden" class="col_time_end" value="<?php echo $col_time_end ?>" />
													</td>
												</tr>
											</table>
										</td>
										<?php
										$col_time_start = 13;
										$col_time_end = 14;
										$call_val = $ccd['1-2_pm'];
										$total_call += $call_val;
										?>
										<td class="no_padding <?php echo colorItGrey($shift_from_mt,$shift_to_mt,$col_time_start,$col_time_end); ?>">	
											<table>
												<tr>
													<td>Call</td>
													<td>Book</td>
												</tr>
												<tr>
													<td>
														<span class="txt_lbl <?php echo ($call_val<30)?'colorItRed':''; ?>"><?php echo ( $call_val=='' )?'&nbsp;':$call_val; ?></span>
														<input type="text" class="txt_hid 1-2_pm" value="<?php echo ( $call_val=='' )?'':$call_val; ?>" />
													</td>
													<td>
														<span class="booked_count_col">&nbsp;</span>
														<input type="hidden" class="col_time_start" value="<?php echo $col_time_start ?>" />
														<input type="hidden" class="col_time_end" value="<?php echo $col_time_end ?>" />
													</td>
												</tr>
											</table>
										</td>
										<?php
										$col_time_start = 14;
										$col_time_end = 15;
										$call_val = $ccd['2-3_pm'];
										$total_call += $call_val;
										?>
										<td class="no_padding <?php echo colorItGrey($shift_from_mt,$shift_to_mt,$col_time_start,$col_time_end); ?>">	
											<table>
												<tr>
													<td>Call</td>
													<td>Book</td>
												</tr>
												<tr>
													<td>
														<span class="txt_lbl <?php echo ($call_val<30)?'colorItRed':''; ?>"><?php echo ( $call_val=='' )?'&nbsp;':$call_val; ?></span>
														<input type="text" class="txt_hid 2-3_pm" value="<?php echo ( $call_val=='' )?'':$call_val; ?>" />
													</td>
													<td>
														<span class="booked_count_col">&nbsp;</span>
														<input type="hidden" class="col_time_start" value="<?php echo $col_time_start ?>" />
														<input type="hidden" class="col_time_end" value="<?php echo $col_time_end ?>" />
													</td>
												</tr>
											</table>
										</td>
										<?php
										$col_time_start = 15;
										$col_time_end = 16;
										$call_val = $ccd['3-4_pm'];
										$total_call += $call_val;
										?>
										<td class="no_padding <?php echo colorItGrey($shift_from_mt,$shift_to_mt,$col_time_start,$col_time_end); ?>">	
											<table>
												<tr>
													<td>Call</td>
													<td>Book</td>
												</tr>
												<tr>
													<td>
														<span class="txt_lbl <?php echo ($call_val<30)?'colorItRed':''; ?>"><?php echo ( $call_val=='' )?'&nbsp;':$call_val; ?></span>
														<input type="text" class="txt_hid 3-4_pm" value="<?php echo ( $call_val=='' )?'':$call_val; ?>" />
													</td>
													<td>
														<span class="booked_count_col">&nbsp;</span>
														<input type="hidden" class="col_time_start" value="<?php echo $col_time_start ?>" />
														<input type="hidden" class="col_time_end" value="<?php echo $col_time_end ?>" />
													</td>
												</tr>
											</table>
										</td>
										<?php
										$col_time_start = 16;
										$col_time_end = 17;
										$call_val = $ccd['4-5_pm'];
										$total_call += $call_val;
										?>
										<td class="no_padding <?php echo colorItGrey($shift_from_mt,$shift_to_mt,$col_time_start,$col_time_end); ?>">	
											<table>
												<tr>
													<td>Call</td>
													<td>Book</td>
												</tr>
												<tr>
													<td>
														<span class="txt_lbl <?php echo ($call_val<30)?'colorItRed':''; ?>"><?php echo ( $call_val=='' )?'&nbsp;':$call_val; ?></span>
														<input type="text" class="txt_hid 4-5_pm" value="<?php echo ( $call_val=='' )?'':$call_val; ?>" />
													</td>
													<td>
														<span class="booked_count_col">&nbsp;</span>
														<input type="hidden" class="col_time_start" value="<?php echo $col_time_start ?>" />
														<input type="hidden" class="col_time_end" value="<?php echo $col_time_end ?>" />
													</td>
												</tr>
											</table>
										</td>
										<?php
										$col_time_start = 17;
										$col_time_end = 18;
										$call_val = $ccd['5-6_pm'];
										$total_call += $call_val;
										?>
										<td class="no_padding <?php echo colorItGrey($shift_from_mt,$shift_to_mt,$col_time_start,$col_time_end); ?>">	
											<table>
												<tr>
													<td>Call</td>
													<td>Book</td>
												</tr>
												<tr>
													<td>
														<span class="txt_lbl <?php echo ($call_val<30)?'colorItRed':''; ?>"><?php echo ( $call_val=='' )?'&nbsp;':$call_val; ?></span>
														<input type="text" class="txt_hid 5-6_pm" value="<?php echo ( $call_val=='' )?'':$call_val; ?>" />
													</td>
													<td>
														<span class="booked_count_col">&nbsp;</span>
														<input type="hidden" class="col_time_start" value="<?php echo $col_time_start ?>" />
														<input type="hidden" class="col_time_end" value="<?php echo $col_time_end ?>" />
													</td>
												</tr>
											</table>
										</td>
										<?php
										$col_time_start = 18;
										$col_time_end = 19;
										$call_val = $ccd['6-7_pm'];
										$total_call += $call_val;
										?>
										<td class="no_padding <?php echo colorItGrey($shift_from_mt,$shift_to_mt,$col_time_start,$col_time_end); ?>">	
											<table>
												<tr>
													<td>Call</td>
													<td>Book</td>
												</tr>
												<tr>
													<td>
														<span class="txt_lbl <?php echo ($call_val<30)?'colorItRed':''; ?>"><?php echo ( $call_val=='' )?'&nbsp;':$call_val; ?></span>
														<input type="text" class="txt_hid 6-7_pm" value="<?php echo ( $call_val=='' )?'':$call_val; ?>" />
													</td>
													<td>
														<span class="booked_count_col">&nbsp;</span>
														<input type="hidden" class="col_time_start" value="<?php echo $col_time_start ?>" />
														<input type="hidden" class="col_time_end" value="<?php echo $col_time_end ?>" />
													</td>
												</tr>
											</table>
										</td>
										<td class="no_padding right_border centerEm">
											<table>
													<tr>
														<td>Call</td>
														<td>Book</td>
													</tr>
													<tr>
														<td>
															<span class="txt_lbl total_call"><?php echo $total_call; ?></span>
														</td>
														<td>
															<span class="total_book_span">&nbsp;</span>
															<input type="hidden" class="total_book" value="0" />
														</td>
													</tr>
												</table>										
										</td>
										<td class="no_padding right_border centerEm">
											<span class="strike_rate"><?php echo (0/$total_call)*100; ?></span>%
										</td>
										<td class="no_padding right_border centerEm">
											<input type="hidden" class="ccd_id" value="<?php echo $ccd['call_centre_data_id'] ?>" />
											<input type="hidden" class="staff_id" value="<?php echo $ccd['staff_id'] ?>" />
											<button class="blue-btn submitbtnImg btn_update">Update</button>
											<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
											<button class="submitbtnImg btn_cancel" style="display:none;">Cancel</button>
											<button class="blue-btn submitbtnImg btn_delete" style="display:none;">Delete</button>
										</td>
										<td>
											<button class="submitbtnImg btn_show_booked_num" type="button">Show</button>
										</td>
									</tr>
							
							
					
				
				<?php	
					}
				
				}else{ ?>
					<tr>
						<td colspan="100%" style="text-align: left;">Empty</td>
					</tr>
				<?php
				}
				
				?>
				
				
				</table>
		
		
			

		<div class="jalign_left">
		
		
		<?php
		if($num_row>0){ ?>
		
			<div id="add_data_div">
			
				<button type="button" id="btn_add_data" class="submitbtnImg">ADD Data</button>
				
				<div style="padding-top: 20px;" id="div_staff" class="addproperty formholder">
					<form id="form_call_centre" method="post" action="/add_call_centre_data.php" style="display:none;">
						<div class="row">
							<?php
							$staff_sql = mysql_query("
									SELECT *
									FROM `staff_accounts` AS sa
									LEFT JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
									WHERE sa.`active` = 1
									AND sa.`deleted` = 0
									AND ca.`country_id` = {$_SESSION['country_default']}
									AND sa.`ClassID` != 6
									AND sa.`StaffID` NOT IN(
										SELECT `staff_id`
										FROM `call_centre_data` 
										WHERE `country_id` = {$_SESSION['country_default']}
										AND `date` = '{$date2}'
									)
									ORDER BY sa.`FirstName`, sa.`LastName`
							");
							?>
							<label class="addlabel" for="staff">Name</label>
							<select name="staff"  id="staff" style="width: 200px;">
								<option value="">--Select--</option>
								<?php
								while( $staff = mysql_fetch_array($staff_sql) ){ ?>
									<option value="<?php echo $staff['StaffID'] ?>"><?php echo "{$staff['FirstName']} ".substr($staff['LastName'],0,1)."."; ?></option>
								<?php	
								}
								?>
							</select>
						</div> 
						<div class="row">
							<label class="addlabel" for="shift">Shift</label>
							<div>
							<input type="number" name="shift_from" id="shift_from" class="addinput shift"  style="width: 44px; display:inline;" /> - 
							<input type="number" name="shift_to" id="shift_to" class="addinput shift"  style="width: 44px; display:inline;" />
							</div>
						</div> 	
						<div class="row">
							<label class="addlabel" for="first_call">First Call</label>
							<input type="text" name="first_call" id="first_call" class="addinput first_call"  style="width: 200px; display:inline;" />
						</div>
						<div class="row">
							<label class="addlabel" for="last_call">Last Call</label>
							<input type="text" name="last_call" id="last_call" class="addinput last_call"  style="width: 200px; display:inline;" />
						</div> 						 
						<div style="padding-top: 15px; text-align:left;" class="row clear">
							<input type="hidden" name="date" class="date" value="<?php echo $date; ?>" />
							<input type="submit" class="submitbtnImg" style="width: auto; margin-bottom: 50px;" name="btn_submit" value="Submit" />
						</div>
					</form>
				</div>	
			
			</div>
		
		<?php	
		}else{ ?>
		
			<div id="set_up_page_div">
				<form method="post" action="set_up_call_center_data.php" id="setup_cc_data_form">
					<button class="submitbtnImg" type="button" id="btn_set_up_page" style="display: block; float: none;">Set up Page</button>
					<div id="ms_staff_div" style="display:none;">
						<?php
							$staff_sql = mysql_query("
								SELECT *
								FROM `staff_accounts` AS sa
								LEFT JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
								WHERE sa.`active` = 1
								AND sa.`deleted` = 0
								AND ca.`country_id` = {$_SESSION['country_default']}
								AND sa.`ClassID` != 6						
								ORDER BY sa.`FirstName`, sa.`LastName`
							");					
						?>
						<select multiple="multiple" id="ms_staff" name="ms_staff[]" style="display: block; float: none; height: 300px; margin: 13px 0;">					
							<?php
							while( $staff = mysql_fetch_array($staff_sql) ){ ?>
								<option value="<?php echo $staff['StaffID'] ?>"><?php echo "{$staff['FirstName']} ".substr($staff['LastName'],0,1)."."; ?></option>
							<?php	
							}
							?>
						</select>
						<input type="hidden" name="date" class="date" value="<?php echo $date; ?>" />						
						<input type="submit" class="submitbtnImg" style="width: auto; margin-bottom: 50px;" name="btn_submit" value="Go" />
					</div>	
				</form>
			</div>
		
		<?php	
		}
		?>
		
		</div>	
			
			
			
				
	
		
		
		
		
	</div>
</div>

<br class="clearfloat" />

<script>
jQuery(document).ready(function(){
	
	
	// get date script
	jQuery("#date").change(function(){
		
		var date = jQuery(this).val();
		jQuery(".date").val(date);
		
	});
	
	
	
	jQuery("#btn_set_up_page").click(function(){
		
		if(jQuery(this).html()=="Set up Page"){
			jQuery(this).html("Cancel");
			jQuery("#ms_staff_div").show();
		}else{
			jQuery(this).html("Set up Page");
			jQuery("#ms_staff_div").hide();
		}
		
		
	});
	
	
	jQuery(".btn_show_booked_num").click(function(){
		
		var num_ajax = jQuery(this).parents("tr:first").find(".booked_count_col").length;
		var ajax_count = 0;
		console.log(num_ajax);
		
		/*
		jQuery(this).parents("tr:first").find(".booked_count_col").each(function(){
			
			console.log(jQuery(this).html());
			
		});
		*/
		
		var total_book = 0;
		var total_book_sum = 0;
		jQuery("#load-screen").show();
		jQuery(this).parents("tr:first").find(".booked_count_col").each(function(){
			
			var obj = jQuery(this);
			var staff_id = obj.parents("tr.body_tr").find(".staff_id").val();
			var date = '<?php echo $date2; ?>'
			var col_time_start = obj.parents("td:first").find(".col_time_start").val();
			var col_time_end = obj.parents("td:first").find(".col_time_end").val();
			
			//setTimeout(function(){ 

				jQuery.ajax({
					type: "POST",
					url: "ajax_display_call_centre_data_booked_count.php",
				
					data: { 
						staff_id: staff_id,
						date: date,
						col_time_start: col_time_start,
						col_time_end: col_time_end
					}
				}).done(function( ret ) {	
				
					ajax_count++;
					obj.html(ret);
					
					total_book += parseInt(ret);
					
					console.log('ajax count: '+ajax_count+' number of ajax: '+num_ajax);
														
					if(ajax_count==num_ajax){
						
						console.log('im in');
						
						// total
						
						obj.parents("tr.body_tr").find(".total_book_span").html(total_book);
						obj.parents("tr.body_tr").find(".total_book").val(total_book);
						
						// strike rate
						total_call =  obj.parents("tr.body_tr").find(".total_call").html();
						total_strike_calc = (parseInt(total_book)/parseInt(total_call))*100;
						obj.parents("tr.body_tr").find(".strike_rate").html(total_strike_calc.toFixed(2));
						jQuery("#load-screen").hide();
					}
					
					
					//window.location="/call_centre_report.php?del=1&date=<?php echo $date; ?>";
					
				});

			//}, 1000);
							
			
		});
		
		
		
	});


	// shift script
	jQuery(".shift_fields").change(function(){
		
		var shift_from = jQuery(this).parents("tr:first").find(".shift_from").val();
		var shift_to = jQuery(this).parents("tr:first").find(".shift_to").val();
		
		var shift_combined = shift_from+'-'+shift_to;
		
		 jQuery(this).parents("tr:first").find(".shift").val(shift_combined);
		
	});

	

	function is_numeric(num){
		if(num.match( /^\d+([\.,]\d+)?$/)==null){
			return false
		}
	}

	function validate_email(email){
		var atpos = email.indexOf("@");
		var dotpos = email.lastIndexOf(".");
		if ( atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length ){
		  return false
		}
	}
	
	// Setup data form
	jQuery("#setup_cc_data_form").submit(function(event){
		
		var staff = jQuery("#ms_staff").val();	
		var error = "";
		
		if(staff==null){
			error += "Staff is required\n";
		}
				
		if(error!=""){
			alert(error);
			return false;
		}else{
			return true;
		}
		
	});
		
	
	// ADD data form
	jQuery("#form_call_centre").submit(function(event){
		
		var staff = jQuery("#staff").val();	
		var shift_from = jQuery("#shift_from").val();
		var shift_to = jQuery("#shift_to").val();		
		var error = "";
		
		if(staff==""){
			error += "Name is required\n";
		}
		
		if( shift_from=="" || shift_from==0 ){
			error += "Shift From is required\n";
		}
		
		if( shift_to=="" || shift_to==0 ){
			error += "Shift To is required\n";
		}
				
		if(error!=""){
			alert(error);
			return false;
		}else{
			return true;
		}
	});

	jQuery(".btn_edit").click(function(){
	
		jQuery(this).parents("tr:first").find(".btn_update").show();
		jQuery(this).parents("tr:first").find(".btn_edit").hide();
		jQuery(this).parents("tr:first").find(".btn_cancel").show();
		jQuery(this).parents("tr:first").find(".btn_delete").show();
		jQuery(this).parents("tr:first").find(".txt_hid").show();
		jQuery(this).parents("tr:first").find(".txt_lbl").hide();
	
	});	
	
	jQuery(".btn_cancel").click(function(){
		
		jQuery(this).parents("tr:first").find(".btn_update").hide();
		jQuery(this).parents("tr:first").find(".btn_edit").show();
		jQuery(this).parents("tr:first").find(".btn_cancel").hide();
		jQuery(this).parents("tr:first").find(".btn_delete").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".txt_hid").hide();	
		
	});
	
	jQuery(".btn_update").click(function(){
	
		var ccd_id = jQuery(this).parents("tr:first").find(".ccd_id").val();
		var shift = jQuery(this).parents("tr:first").find(".shift").val();
		var shift_from = jQuery(this).parents("tr:first").find(".shift_from").val();
		var shift_to = jQuery(this).parents("tr:first").find(".shift_to").val();
		var check_in = jQuery(this).parents("tr:first").find(".check_in").val();
		var first_call = jQuery(this).parents("tr:first").find(".first_call").val();
		var last_call = jQuery(this).parents("tr:first").find(".last_call").val();
		
		var time_7_8_am = jQuery(this).parents("tr:first").find(".7-8_am").val();
		var time_8_9_am = jQuery(this).parents("tr:first").find(".8-9_am").val();
		var time_9_10_am = jQuery(this).parents("tr:first").find(".9-10_am").val();
		var time_10_11_am = jQuery(this).parents("tr:first").find(".10-11_am").val();
		var time_11_12_pm = jQuery(this).parents("tr:first").find(".11-12_pm").val();
		var time_12_1_pm = jQuery(this).parents("tr:first").find(".12-1_pm").val();
		var time_1_2_pm = jQuery(this).parents("tr:first").find(".1-2_pm").val();
		var time_2_3_pm = jQuery(this).parents("tr:first").find(".2-3_pm").val();
		var time_3_4_pm = jQuery(this).parents("tr:first").find(".3-4_pm").val();
		var time_4_5_pm = jQuery(this).parents("tr:first").find(".4-5_pm").val();
		var time_5_6_pm = jQuery(this).parents("tr:first").find(".5-6_pm").val();
		var time_6_7_pm = jQuery(this).parents("tr:first").find(".6-7_pm").val();
		
		var error = "";
		
		
		if( shift_from=="" || shift_from==0 ){
			error += "Shift From: cannot be empty\n";
		}
		
		if( shift_to=="" || shift_to==0 ){
			error += "Shift To: cannot be empty\n";
		}
		
		if(error!=""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_call_centre_data.php",
				data: { 
					ccd_id: ccd_id,
					shift_from: shift_from,
					shift_to: shift_to,
					check_in: check_in,
					first_call: first_call,
					last_call: last_call,
					time_7_8_am: time_7_8_am,
					time_8_9_am: time_8_9_am,
					time_9_10_am: time_9_10_am,
					time_10_11_am: time_10_11_am,
					time_11_12_pm: time_11_12_pm,
					time_12_1_pm: time_12_1_pm,
					time_1_2_pm: time_1_2_pm,
					time_2_3_pm: time_2_3_pm,
					time_3_4_pm: time_3_4_pm,
					time_4_5_pm: time_4_5_pm,
					time_5_6_pm: time_5_6_pm,
					time_6_7_pm: time_6_7_pm
				}
			}).done(function( ret ) {
				window.location="/call_centre_report.php?success=2&date=<?php echo $date; ?>";
			});				
			
		}		
		
	});

	// delete data
	jQuery(".btn_delete").click(function(){
		if(confirm("Are you sure you want to delete?")){
			var ccd_id = jQuery(this).parents("tr:first").find(".ccd_id").val();
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_call_centre_data.php",
				data: { 
					ccd_id: ccd_id
				}
			}).done(function( ret ) {	
				window.location="/call_centre_report.php?del=1&date=<?php echo $date; ?>";
			});	
		}				
	});
	
	

	//  opportunity show/hide form toggle
	jQuery("#btn_add_data").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#form_call_centre").slideDown();
	},function(){
		jQuery(this).html("ADD Data");		
		jQuery("#form_call_centre").slideUp();
	});
	
	
	
	
	
});
</script>
</body>
</html>