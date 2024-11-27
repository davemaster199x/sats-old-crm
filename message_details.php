<?php


$title = "Message Details";
$onload = 1;
//$onload_txt = "zxcSelectSort('agency',1)";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$msg_h_id = $_GET['id'];
$me = $_SESSION['USER_DETAILS']['StaffID'];


// set it as read
mysql_query("
	UPDATE `message_header`
	SET `read` = 1,
		`read_date` = '".date("Y-m-d H:i:s")."'
	WHERE `message_header_id` = {$msg_h_id}
	AND `read` IS NULL
");

// set it as read per user
mysql_query("
	UPDATE `message_group`
	SET `read` = 1,
		`read_date` = '".date("Y-m-d H:i:s")."'
	WHERE `message_header_id` = {$msg_h_id}
	AND `staff_id` = {$me}
	AND `read` IS NULL
");



// update read markers
// clear read markers
mysql_query("
	DELETE mrb
	FROM `message_read_by` AS mrb
	LEFT JOIN `message` AS m ON mrb.`message_id`  = m.`message_id`
	WHERE mrb.`staff_id` = {$me}
	AND m.`message_header_id` = {$msg_h_id}
");

// get last message and mark it as read
$msg_sql2 = mysql_query("
	SELECT m.`message_id`
	FROM `message` AS m
	WHERE m.`message_header_id` = {$msg_h_id}
	ORDER BY m.`date` DESC
	LIMIT 1
");
$msg_sql_row = mysql_fetch_array($msg_sql2);

// mark read markers
mysql_query("
	INSERT INTO 
	message_read_by (
		`read`,
		`message_id`,
		`staff_id`,
		`date`
	)
	VALUES (
		1,
		{$msg_sql_row['message_id']},
		{$me},
		'".date("Y-m-d H:i:s")."'
	)
");


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
			<li class="other first"><a title="Messages" href="messages.php">Messages</a></li>
			<li class="other first"><a title="Message Details" href="message_details.php?id=<?php echo $_GET['id'] ?>"><strong>Message Details</strong></a></li>
		  </ul>
		</div>
	
	<?php
	}
	?>  
      
	 
	  
    
    
   <div id="time"><?php echo date("l jS F Y"); ?></div>
      
      	
	<div class="addproperty" style="width:auto;">
    
      <?php
	  if($_GET['mar']==1){ ?>
		<div class="success">Message Marked As Read</div>	
	  <?php
	  }
	  ?>
      
	<?php	
	// get messag headers
	$msg_h_sql = mysql_query("
		SELECT *
		FROM `message_header` AS mh
		LEFT JOIN `staff_accounts` AS sa ON mh.`from` = sa.`StaffID`
		WHERE `message_header_id` = {$msg_h_id}
		
	");
	$msg_h = mysql_fetch_array($msg_h_sql);
	?>
	
	  
    	<form id="form1" name="form1" method="POST" action="/message_details_script.php" style="text-align: left;">
                
			<div id="mobile_message">		
				<?php 
				// get messages
				$msg_sql= mysql_query("
					SELECT *, m.`date` AS m_message
					FROM `message` AS m
					LEFT JOIN `staff_accounts` AS sa ON m.`author` = sa.`StaffID`
					WHERE m.`message_header_id` = {$msg_h_id}					
					ORDER BY m.`date`
				");
				while($msg = mysql_fetch_array($msg_sql)){ 
				$me_sms = ($msg['author']==$me)?'Me':$msg['FirstName'];
				?>
                <div id="mobilemessage">
					<div class="message_container">
						<div class="message_name"><?php echo $msg['FirstName']; ?></div>
						<div class="message_other_features">							
						    <?php
							if($me_sms=="Me"){ ?>
                            <div class="mh_first">
                                <div style="float: left; margin: 0 8px;">
                                  <div class="messagegrey"><?php echo $msg['message']; ?></div>
                                 </div>
								<div class="mhf_f"><?php echo date("d/m/Y (H:i)",strtotime($msg['m_message'])); ?></div>								
                            </div>    
								
								<?php 
								$rb_sql = mysql_query("
									SELECT *
									FROM `message_read_by` AS mrb
									LEFT JOIN `staff_accounts` AS sa ON mrb.`staff_id` = sa.`StaffID`
									WHERE mrb.`message_id` = {$msg['message_id']}
									AND mrb.`read` IS NOT NULL
								");
								if( mysql_num_rows($rb_sql)>0 ){ ?>
									
									<div class="message_readby_name"  style="width: 100%;">
										<div>
											<?php										
											while($rb = mysql_fetch_array($rb_sql)){ ?>
												<div class="message_readby">Read</div>
												<div class="readby">
													<div class="rby_f"><?php echo "{$rb['FirstName']} {$rb['LastName']}"; ?></div>
													<div class="rby_f" style="color:#00D1E5; font-size: 13px;"><?php echo date("d/m/Y (H:i)",strtotime($rb['date'])); ?></div>
												</div>
											<?php	
											}
											?>										
										</div>
									</div>
								<?php	
								}												
							}
							?>								
						</div>
						<div class="message_other">							
							 <?php
							if($me_sms!="Me"){ ?>
								<div class="mh_first">
                                	<div class="mhf_f"><?php echo date("d/m/Y (H:i)",strtotime($msg['m_message'])); ?></div>
								    <div style="float: left; margin: 0 8px;">
                                        <div class="messageblue"><?php echo $msg['message']; ?></div>
                                    </div> 
                                </div>
								
								<?php 
								$rb_sql = mysql_query("
									SELECT *
									FROM `message_read_by` AS mrb
									LEFT JOIN `staff_accounts` AS sa ON mrb.`staff_id` = sa.`StaffID`
									WHERE mrb.`message_id` = {$msg['message_id']}
									AND mrb.`read` IS NOT NULL
								");
								if( mysql_num_rows($rb_sql)>0 ){ ?>
									
									<div class="message_readby_name"  style="width: 100%;">
										<div>
											<?php										
											while($rb = mysql_fetch_array($rb_sql)){ ?>
												<div class="message_readby">Read</div>
												<div class="readby">
													<div class="rby_f"><?php echo "{$rb['FirstName']} {$rb['LastName']}"; ?></div>
													<div class="rby_f" style="color:#00D1E5; float: left; font-size: 13px;"><?php echo date("d/m/Y (H:i)",strtotime($rb['date'])); ?></div>
												</div>
											<?php	
											}
											?>										
										</div>
									</div>
								<?php	
								}												
							}
							?>	
						</div>					
					</div>
                    </div>
				<?php	
				}
				?>	
				<div class="message_sender">
					<div><textarea name="msg" class="addtextarea" style="height: 100px; width: 55%; margin: 0!important; float: none;"></textarea></div>
                    <div style="width: 120px;">
							<input type="hidden" name="msg_h_id" value="<?php echo $msg_h_id; ?>" />
							<input type="submit" class="submitbtnImg submitbutton" style="width: auto;" name="btn_send" value="Send" />
						</div>
				</div>					
			</div>
		</form>
		
		
		
    </div>
	
	
	</div>
	
	</div>
    
  </div>


  
</div>

</div>

<br class="clearfloat" />


</body>
</html>
