<?php

$title = "Messages";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

include('inc/servicedue_functions.php');


?>


<?php
if($_SESSION['USER_DETAILS']['ClassID']==6){ ?>  
	<div style="clear:both;"></div>
<?php
}  
?>

<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">

	<?php
	if($_SESSION['USER_DETAILS']['ClassID']==6){ 
	
	$tech_id = $_SESSION['USER_DETAILS']['StaffID'];
	
	$day = date("d");
	$month = date("m");
	$year = date("y");
	
	include('inc/tech_breadcrumb.php');
	
	}else{ ?>
	
		<div class="sats-breadcrumb">
			<ul>
				<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
				<li class="other first"><a title="Messages" href="/messages.php"><strong>Messages</strong></a></li>
			</ul>
		</div>	
	
	<?php
	}
	?>  
	
	
	
		
		  
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Message Sent</div>';
		}
		
			if($_GET['del']==1){
			echo '<div class="success">Message Deleted</div>';
		}
		
		
		?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		
		
		
		
		<div style="height: auto;padding-bottom: 5px; text-align: center; border-bottom: 1px solid #ccc;" class="vtsd-tp-chrm ap-vw-reg-sch aviw_drop-h">


		<div class="vtsd-tp-left">


			<span>
				<a href="/create_message.php" style="float: right;">
					<button type="button" class="submitbtnImg">Create Message</button>
				</a>					
			</span>
			
			</div>
			
		  

		<div class="vtsd-tp-right">
				
			</div>
			
		</div>
		
		
		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;  text-align: left;">
			<tr class="toprow jalign_left">	
				<th><b>Date</b></th>
				<th><b>Time</b></th>				
				<th><b>Other Participants</b></th>
				<th><b>Message</b></th>	
			</tr>
			<?php
			
			// staff id
			$me = $_SESSION['USER_DETAILS']['StaffID'];
			
			/*
			// get messages
			$msg_sql = mysql_query("
				SELECT *, mh.read AS m_read
				FROM `message_header` AS mh
				LEFT JOIN `message_group` AS mg ON mh.`message_header_id` = mg.`message_header_id`
				LEFT JOIN `staff_accounts` AS sa ON mh.`from` = sa.`StaffID`
				WHERE mg.`staff_id` = {$me}
				AND mh.`deleted` = 0
				GROUP BY mh.`message_header_id`
				ORDER BY `date` DESC
			");
			*/

			$msg_sql = mysql_query("
			SELECT 
				MAX(m.`date`) as latest_date,
				m.`message_header_id`, 
				m.`message`, 

				sa.`FirstName`, 
				sa.`LastName`

			FROM `message` AS m
			LEFT JOIN `message_header` AS `mh` ON m.`message_header_id` = mh.`message_header_id`
			LEFT JOIN `staff_accounts` AS `sa` ON m.`author` = sa.`StaffID`
			INNER JOIN `message_group` AS `mg` ON ( mh.`message_header_id` = mg.`message_header_id` )
			WHERE `mg`.`staff_id` = {$me}
			GROUP BY m.`message_header_id`
			ORDER by latest_date DESC
			") or die(mysql_error());
			
			while($msg = mysql_fetch_array($msg_sql)){ 
			//$me_sms = ($msg['StaffID']==$me)?'Me':"{$msg['FirstName']} {$msg['LastName']}";
			?>
				<tr class="body_tr jalign_left">
					
					<td>
						<span class="txt_lbl"><?php echo date("d/m/Y",strtotime($msg['latest_date'])) ?></span>
					</td>
					<td>
						<span class="txt_lbl"><?php echo date("H:i a",strtotime($msg['latest_date'])) ?></span>
					</td>
					<td>						
						<span class="txt_lbl">	
						<ul style="margin: 0; padding: 0; list-style: outside none none;">
						<?php
						/*
						// get message group
						$mg_sql = mysql_query("
							SELECT *
							FROM `message_group` AS mg
							LEFT JOIN `staff_accounts` AS sa ON mg.`staff_id` = sa.`StaffID`
							WHERE mg.`message_header_id` = {$msg['message_header_id']}
							AND mg.`staff_id` != {$msg['from']}
						");
						*/

						$mg_sql2 = mysql_query("
							SELECT sa.`FirstName`, sa.`LastName` 
							FROM `message_group` AS mg
							LEFT JOIN `staff_accounts` AS sa ON mg.`staff_id` = sa.`StaffID` 
							WHERE mg.`message_header_id` = {$msg['message_header_id']}
							AND mg.`staff_id` != {$me}
							ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC 
						");
						while($mg2 = mysql_fetch_array($mg_sql2)){ ?>
							<li><?php echo "{$mg2['FirstName']} {$mg2['LastName']}"; ?></li>
						<?php	
						}
						?>
						</ul>
						</span>
					</td>
					<td>
						<?php
						/*
						// get messages
						$msg_sql2 = mysql_query("
							SELECT *
							FROM `message` AS m
							LEFT JOIN `staff_accounts` AS sa ON m.`author` = sa.`StaffID`
							WHERE m.`message_header_id` ={$msg['message_header_id']}
							AND m.`author` ={$msg['StaffID']}
						");
						$msg2 = mysql_fetch_array($msg_sql2);
						*/

						// get messages
						$msg_sql2 = mysql_query("
							SELECT 
								`m`.`message_id`, 
								`m`.`message`, 
								`m`.`message_header_id` 
							FROM `message` AS `m` 
							WHERE `m`.`message_header_id` = {$msg['message_header_id']} 
							ORDER BY `m`.`date` DESC
						");
						$msg2 = mysql_fetch_array($msg_sql2);
						

						?>
                        
                        <span class="txt_lbl">
                        	<a href="message_details.php?id=<?php echo $msg['message_header_id']; ?>" style="display: block;">
                            <input type="text" style="cursor: pointer; width: 100%; color: #b4151b;" value="<?php echo $msg2['message']; ?>" readonly>
                            </a>
                         </span>
                        
					</td>
				</tr>
			<?php
			}
			?>			
		</table>
		
		
		
	</div>
</div>

<br class="clearfloat" />

<script>
// call ajax for delete property
jQuery(".btn_delete_msg").click(function(){
	
	if(confirm("Are you sure you want to continue?")==true){
		
		var msg_h_id = jQuery(this).parents("tr:first").find(".msg_h_id").val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_delete_message.php",
			data: {
				msg_h_id: msg_h_id				
			}
		}).done(function(ret){
			window.location="/messages.php?del=1";
		});
		
	}
		
	
});
</script>

</body>
</html>