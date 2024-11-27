<?php


$title = "Create Message";
$onload = 1;
//$onload_txt = "zxcSelectSort('agency',1)";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$me = $_SESSION['USER_DETAILS']['StaffID'];

?>

<?php
if($_SESSION['USER_DETAILS']['ClassID']==6){ ?>  
	<div style="clear:both;"></div>
<?php
}  
?>
    
    <div id="mainContent">


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
			<li class="other first"><a title="Create Message" href="/create_message.php"><strong>Create Message</strong></a></li>
		  </ul>
		</div>
	
	<?php
	}
	?>  
	
		
      
      
    
   <div id="time"><?php echo date("l jS F Y"); ?></div>
      
      	
	<div class="addproperty">
    
      <?php
	  if($_GET['success']==1){ ?>
		<div class="success">New Property Added</div>	
	  <?php
	  }
	  ?>
      

	  
    	<form id="form1" name="form1" method="POST" action="/create_message_script.php">
          
			
			<div class="row" style="text-align:left">
				<label class="addlabel" for="address_3">To: </label>
				<span>
					<select class="addinput" style="height: 350px;" name="to[]" id="to[]" multiple>
						<?php
						$sa_sql = mysql_query("
							SELECT *
							FROM `staff_accounts` AS sa
							LEFT JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
							WHERE sa.`active` = 1
							AND sa.`deleted` = 0
							AND sa.`StaffID` != {$_SESSION['USER_DETAILS']['StaffID']}
							AND ca.`country_id` = {$_SESSION['country_default']}
							ORDER BY sa.`FirstName`, sa.`LastName`
						");	
						while($sa = mysql_fetch_array($sa_sql)){ ?>
							<option value="<?php echo $sa['StaffID'] ?>"><?php echo "{$sa['FirstName']} {$sa['LastName']}"; ?></option>	
						<?php	
						}
						?>
					</select>
				</span>
            </div>
			
		  

			
			
			

			 <div class="row" style="text-align:left">
				<label class="addlabel" for="address_3">Message: </label>
				<span><textarea name="msg" class="addtextarea" style="height: 100px; width: 55%; margin: 0!important;"></textarea></span>
            </div>


			 <div class="row" style="text-align:left">
				<label class="addlabel" for="address_3">&nbsp;</label>
				<span> <input type="submit" class="submitbtnImg submitbutton" style="width: auto;" name="btn_send" value="Send" /></span>				
            </div>
		
		
			
	
		
		
	
		
		
    </form>
	
    </div>
	</div>
    
  </div>


  
</div>

</div>

<br class="clearfloat" />

</body>
</html>
