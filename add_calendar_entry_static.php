<?

$title = "Add/Edit Calendar Entry";
$onload = 1;
//$onload_txt = "zxcSelectSort('agency',1)";

$custom_datepicker = 1;

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$logged_user = $_SESSION['USER_DETAILS']['StaffID'];

$get_staff_id = $_GET['staff_id'];
$get_startdate = $_GET['startdate'];

if( $get_staff_id!='' ){
	$select_staff_dp = $get_staff_id;
}else{
	$select_staff_dp = $logged_user;
}

?>
<div id="mainContent">

<div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first" style="z-index:0;"><a title="Add/Edit Calendar Entry" href="<?php echo $_SERVER['REQUEST_URI']; ?>"><strong>Add/Edit Calendar Entry</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

<div class="addproperty aces-hld">

<form id="form1" name="form1" method="POST" action="<?=URL;?>add_calendar_entry.php">
    	
    	<?php 
    	
    	$id = $_GET['id'];
		
		if($id) {
			$existing = mysql_query ("
				SELECT c.staff_id, c.date_start, c.date_finish, c.region, c.details, c.booking_target, c.`accomodation`, c.`accomodation_id`, c.`marked_as_leave`, `date_start_time`, `date_finish_time`, c.`booking_staff`
				FROM calendar AS c
				LEFT JOIN `staff_accounts` AS sa ON c.`staff_id` = sa.`StaffID`
				WHERE c.calendar_id = $id", $connection);
			$existing = mysql_fetch_assoc($existing);
			
			$startdate = str_replace('/', "-", $existing[date_start]);
			$startdate = date('d-m-Y', strtotime($startdate));
			
			$finishdate = str_replace('/', "-", $existing[date_finish]);
			$finishdate = date('d-m-Y', strtotime($finishdate));
		}

    	?>
    	<input type="hidden" name="id" class="addinput" value="<?php echo $id; ?>">
        <div class="row fr-stsel">
    	<label class="addlabel">Staff: <span style="color:red">*</span></label>
    	<?php 
		
		$techs = getStaffByCountry();
		
		if($_GET['id']!=""){ ?>
		
			<select name='staff_id' id="staff_id" style="height: 150px;">
				<?php
			   while ($row = mysql_fetch_row($techs))
			   {		   	
				   echo "<option value='$row[0]' ";
				   if($existing["staff_id"] == $row[0]) {
						echo 'selected="selected"';			
				   }
				   echo ">$row[1] $row[2]</option>\n";
				}
			?>
			</select>
		
		<?
		}else{
		
			echo "<select name='staff_id[]' id='staff_id' multiple='multiple' style='height: 150px;'>
			<option value='-1' id='all_staff_dp' style='color: red;'>ALL STAFF</option>
			";
		
		   // Grab all the techs and put them in an array.
			
			   // (a) Run the query
			   //$techs = mysql_query ("SELECT StaffID, FirstName, LastName FROM staff_accounts WHERE deleted = 0 AND active = 1 ORDER BY FirstName ASC;", $connection);
			
			   // (b) While there are still rows in the result set,
			   // fetch the current row into the array $row
			   while ($row = mysql_fetch_row($techs))
			   {
				
			   echo "<option value='$row[0]' ".(($row[0]==$select_staff_dp)?'selected="selected"':'').">$row[1] $row[2]</option>\n";
			}
			
				echo "</select>\n";	
		
		}
		
			
		?>
		</div>
		<?php
		if($get_startdate!=""){ ?>
			<div class="row">
			 <label class="addlabel">Start Date:</label>
			 <input type="text" name="start_date" id="start_date" class="addinput start_date datepicker" value="<?php echo date("d/m/Y 09:00",strtotime($get_startdate)); ?>">
			</div>
			<div class="row">
			 <label class="addlabel">Finish Date:</label>
			 <input type="text" name="finish_date" id="finish_date" class="addinput finish_date datepicker" value="<?php echo date("d/m/Y 17:00",strtotime($get_startdate)); ?>">
			</div>
		<?php	
		}else{ ?>
			<div class="row">
			 <label class="addlabel">Start Date:</label>
			 <input type="text" name="start_date" id="start_date" class="addinput start_date datepicker" value="<?php if($startdate){ echo date("d/m/Y",strtotime(str_replace("/","-",$startdate))).' '.$existing['date_start_time']; }else{ echo date("d/m/Y 09:00"); } ?>">
			</div>
			<div class="row">
			 <label class="addlabel">Finish Date:</label>
			 <input type="text" name="finish_date" id="finish_date" class="addinput finish_date datepicker" value="<?php if($finishdate){ echo date("d/m/Y",strtotime(str_replace("/","-",$finishdate))).' '.$existing['date_finish_time']; }else{ echo date("d/m/Y 17:00"); } ?>">
			</div>
		<?php	
		}
		?>
		
        <div class="row">
        	<label class="addlabel">Region / Type of Leave: <span style="color:red">*</span></label>
            <input type="text" maxlength="19" lengthcut="true"  name="region" id="region" class="addinput"  value="<?php echo $existing[region]; ?>" style="float: left;margin-right: 12px;" /> 
			<div style="float: left;margin-top: 5px;">
				<input type="checkbox" name="marked_as_leave" style="float: left;margin-right: 11px;width: auto;" value="1" <?php echo ($existing['marked_as_leave']==1)?'checked="checked"':''; ?> />
				<div style="float: left;">LEAVE</div>
			</div>
			<div style="clear:both;"></div>
        </div>
		
		<!--
		<div class="row">
        	<label class="addlabel">Booking Target:</label>
            <input type="text" name="booking_target" id="booking_target" class="addinput"  value="<?php echo $existing['booking_target']; ?>">
        </div>
		-->
		
		<div class="row">
        	<label class="addlabel">Booking Staff:</label>
			<?php
			$sa_sql = mysql_query("
				SELECT *
				FROM staff_accounts AS sa 
				LEFT JOIN staff_classes AS sc ON sa.`ClassID` = sc.`ClassID` 
				INNER JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
				WHERE sa.`Deleted` = 0
				AND sa.`active` = 1
				AND ca.`country_id` = {$_SESSION['country_default']}
				ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
			");
			?>
            <select name="booking_staff">
				<option value="">--Select--</option>
				<?php
				while( $sa = mysql_fetch_array($sa_sql) ){ ?>
					<option value="<?php echo $sa['StaffID'] ?>" <?php echo ($sa['StaffID']==$existing['booking_staff'])?'selected="selected"':'' ?>><?php echo "{$sa['FirstName']} {$sa['LastName']}"; ?></option>
				<?php	
				}
				?>				
			</select>
        </div>
		
        <div class="row">
       	 <label class="addlabel">Details:</label>
         <textarea cols="45" rows="10" name="details" id="details" class="ftextarea aces-txtarea"><?php echo $existing[details]; ?></textarea>
        </div>
		<div class="row">
         <input type="radio" class="acco_opt accomodation_radio" name="accomodation" value="" <?php echo ($existing['accomodation']=="")?'checked="checked"':''; ?> />
       	 <label class="addlabel accomodation_lbl <?php echo ($existing['accomodation']=="")?'':'fadeOutText'; ?>">No Accomodation</label>
        </div>
		<div class="row">
         <input type="radio" class="acco_opt accomodation_radio" name="accomodation" value="0" <?php echo ( is_numeric($existing['accomodation']) && $existing['accomodation']==0 )?'checked="checked"':''; ?> />
       	 <label class="addlabel accomodation_lbl <?php echo ( is_numeric($existing['accomodation']) && $existing['accomodation']==0 )?'"':'fadeOutText'; ?>">Accommodation Required</label>
        </div>
		
		<div class="row">
         <input type="radio" class="acco_opt accomodation_radio" name="accomodation" value="2" <?php echo ($existing['accomodation']==2)?'checked="checked"':''; ?> />
       	 <label class="addlabel accomodation_lbl <?php echo ($existing['accomodation']==2)?'':'fadeOutText'; ?>">Accommodation Pending</label>
        </div>
		
		<div class="row" style="overflow: hidden; padding-bottom: 10px;">
         <input type="radio" class="acco_opt accomodation_radio" name="accomodation" value="1" <?php echo ($existing['accomodation']==1)?'checked="checked"':''; ?> />
       	 <label class="addlabel accomodation_lbl <?php echo ($existing['accomodation']==1)?'':'fadeOutText'; ?>">Accommodation Booked</label>
        </div>
		<div class="row" id="sel_acco" style="display:<?php echo ($existing['accomodation']==1||$existing['accomodation']==2)?'block':'none'; ?>;">
       	 <label class="addlabel">Accommodation</label>
         <select name="accomodation_id">
		<?php
		// sort by name ASC
		$acco_sql2 = mysql_query("
			SELECT *
			FROM `accomodation`
			ORDER BY `name` ASC
		");							
		while($acco2 = mysql_fetch_array($acco_sql2)){ ?>
		<option value="<?php echo $acco2['accomodation_id']; ?>" <?php echo ($acco2['accomodation_id']==$existing['accomodation_id'])?'selected="selected"':''; ?>><?php echo $acco2['name']; ?></option>
		<?php
		}
		?>
		</select>
        </div>		
		
		<div class="row" style="margin-top: 28px;">
         <input type="checkbox" style="float: left; width: auto;" name="send_ical" value="1" />
       	 <label class="addlabel accomodation_lbl" style="margin-top: -2px !important;">Send iCalendar</label>
        </div>
		
        <div class="row" style="margin-top: 21px; text-align: left;">
        	
			
			<?php
			if($_GET['id']){ ?>
			
			
			<button type="submit" id="submit" class="submitbtnImg"  style="float: left; margin-right: 20px;">
				<img class="inner_icon" src="images/save-button.png">
				Update
			</button>
			
			<button class="blue-btn submitbtnImg " id="btn_delete" type="button" style="float: left;">
				<img class="inner_icon" src="images/cancel-button.png">
				Delete
			</button>    
			<?php
			}else{ ?>
				<button type="submit" id="submit" class="submitbtnImg">
					<img class="inner_icon" src="images/add-button.png">
					Event
				</button>
			<?php
			}
			?>			 
        </div>
		

    </form>
    
</div>    

  </div>
  
</div>

<br class="clearfloat" />

<style>
#ui-datepicker-div{
	width: 453px;
}

#ui-datepicker-div .ui-datepicker-calendar{
	float: left;
	width: 272px;
}

#ui-datepicker-div .ui-timepicker-div{
	float: left;
    width: 176px;
	height: 136px;
}

#ui-datepicker-div .ui-datepicker-buttonpane{
	float: left;
	margin-left: 13px;
	font-size: 15px;
	margin: 10px 0 0 23px;
}


#ui-datepicker-div .ui-timepicker-div{
	 font-size: 18px;
}

#ui-datepicker-div .ui-timepicker-div .ui_tpicker_time_label{
	float: left;
    margin: 0 20px 0 30px;
}

#ui-datepicker-div .ui-timepicker-div .ui_tpicker_time{
	float: left;
	clear: right;
	margin: 0;
}

#ui-datepicker-div .ui-timepicker-div .ui_tpicker_hour_label{
	clear: left;
}
.accomodation_lbl{
	float: left; 
	clear: right; 
	margin-left: 5px !important; 
	margin-top: 12px !important; 
	width: auto;
}
.accomodation_radio{
	margin-top: 15px !important;
	width: auto !important; 
	float: left !important;
}
</style>
<script type="text/javascript" src="inc/js/jquery-ui-1.8.23.custom.min.js"></script>
<script>
jQuery(document).ready(function(){
 
 
 
 
	jQuery(".acco_opt").click(function(){

		jQuery(".accomodation_lbl").addClass('fadeOutText');
		jQuery(this).parents("div.row:first").find(".accomodation_lbl").removeClass('fadeOutText');

	});



	jQuery("#form1").submit(function(){
	 
	 var staff_id = jQuery("#staff_id").val();
	 var region = jQuery("#region").val();
	 var error = '';
	 
	 if(staff_id==null){
		 error += "Staff is required\n";
	 }
	 
	 if(region==''){
		  error += "Region is required\n";
	 }
	 
	 if(error!=''){
		 alert(error);
		 return false;
	 }else{
		 return true;	
	 }			 			 
	 
	 
	});

	// delete
	<?php
	if($_GET['id']){ ?>

	jQuery("#btn_delete").click(function(){
	if(confirm("Are you sure you want to delete?")==true){
		var calendar_id = <?php echo $_GET['id']; ?>;
		jQuery.ajax({
			type: "POST",
			url: "ajax_delete_calendar.php",
			data: { 
				calendar_id: calendar_id
			}
		}).done(function( ret ){
			window.location.href="/view_tech_calendar.php";
		});	
	}		
	});

	<?php
	}
	?>


	jQuery(".acco_opt").click(function(){
	var opt = jQuery(this).val();
	if(opt==1||opt==2){
		jQuery("#sel_acco").show();
	}else{
		jQuery("#sel_acco").hide();
	}
	});




	// datepicker
	jQuery(".datepicker").datetimepicker( { 
	dateFormat: "dd/mm/yy",
	changeMonth: true
	} );


	// select ALL STAFF
	jQuery("#all_staff_dp").click(function(){

	jQuery("#staff_id option").prop("selected",true);

	});


});
</script>	 
<script src="/js/datetimepicker/datetimepicker_addon.js"></script>

</body>
</html>
