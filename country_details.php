<?

$title = "Country Details";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');



if($_POST['update']){
	
	$country = mysql_real_escape_string($_POST['country']);
	$country_code = mysql_real_escape_string($_POST['country_code']);
	$agent_number = mysql_real_escape_string($_POST['agent_number']);
	$tenant_number = mysql_real_escape_string($_POST['tenant_number']);
	$email_signature = mysql_real_escape_string($_POST['email_signature']);
	$letterhead_footer = mysql_real_escape_string($_POST['letterhead_footer']);
	$trading_name = mysql_real_escape_string($_POST['trading_name']);
	$outgoing_email = mysql_real_escape_string($_POST['outgoing_email']);
	$bank = mysql_real_escape_string($_POST['bank']);
	$abn = mysql_real_escape_string($_POST['abn']);
	$bsb = mysql_real_escape_string($_POST['bsb']);
	$ac_name = mysql_real_escape_string($_POST['ac_name']);
	$ac_number = mysql_real_escape_string($_POST['ac_number']);
	$web = mysql_real_escape_string($_POST['web']);
	$facebook = mysql_real_escape_string($_POST['facebook']);
	$twitter = mysql_real_escape_string($_POST['twitter']);
	$instagram = mysql_real_escape_string($_POST['instagram']);
	$company_address = mysql_real_escape_string($_POST['company_address']);
	
	if($_GET['id']!=""){
		
		mysql_query("
			UPDATE `countries`
			SET 
				`country` = '{$country}',
				`iso` = '{$country_code}',
				`agent_number` = '{$agent_number}',
				`tenant_number` = '{$tenant_number}',
				`email_signature` = '{$email_signature}',
				`letterhead_footer` = '{$letterhead_footer}',
				`trading_name` = '{$trading_name}',
				`outgoing_email` = '{$outgoing_email}',
				`bank` = '{$bank}',
				`abn` = '{$abn}',
				`bsb` = '{$bsb}',
				`ac_name` = '{$ac_name}',
				`ac_number` = '{$ac_number}',
				`web` = '{$web}',
				`facebook` = '{$facebook}',
				`twitter` = '{$twitter}',
				`instagram` = '{$instagram}',
				`company_address` = '{$company_address}'
			WHERE `country_id` = {$_GET['id']}
		");
		
		$success = 1;
		
	}
	
	
	
	
}

?>

<div id="mainContent">

  <div class="sats-middle-cont">
  
    <div class="sats-breadcrumb">
	  <ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="/countries.php">Countries</a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="/country_details.php?id=<?php echo $_GET['id']; ?>"><strong><?php echo $title; ?></strong></a></li>
	  </ul>
	</div>
    <div id="time"><?php echo date("l jS F Y"); ?></div>
	
	<?php
	
	if($success==1){
		echo '<div class="success" style="margin-bottom: 12px;">Update Successful</div>';
	}
	
	$c_sql = mysql_query("
		SELECT *
		FROM `countries`
		WHERE `country_id` = {$_GET['id']}
	");
	$c = mysql_fetch_array($c_sql);
	
	?>
    
    <div class="formholder addagency">
      <form id="form1" name="form1" method="POST" action="/country_details.php?id=<?php echo $_GET['id']; ?>">
	  
		<div class="row">
          <label class="addlabel" for="country">Country Name: </label>
          <input class="addinput" type="text" name="country" id="country" value="<?php echo $c['country']; ?>" />
        </div> 
        
      
		<div class="row">
          <label class="addlabel" for="country_code">Country Code: </label>
          <input class="addinput" type="text" name="country_code" id="country_code" value="<?php echo $c['iso']; ?>" />
        </div> 
		
		<div class="row">
          <label class="addlabel" for="agent_number">Agent Number: </label>
          <input class="addinput" type="text" name="agent_number" id="agent_number" value="<?php echo $c['agent_number']; ?>" />
        </div> 
		
		<div class="row">
          <label class="addlabel" for="tenant_number">Tenant Number: </label>
          <input class="addinput" type="text" name="tenant_number" id="tenant_number" value="<?php echo $c['tenant_number']; ?>" />
        </div> 
		
		
		<div class="row">
          <label class="addlabel" for="email_signature">Email Signature: </label>
          <input class="addinput" type="text" name="email_signature" id="email_signature" value="<?php echo $c['email_signature']; ?>" />
        </div> 
		
		<div class="row">
          <label class="addlabel" for="letterhead_footer">Letterhead Footer: </label>
          <input class="addinput" type="text" name="letterhead_footer" id="letterhead_footer" value="<?php echo $c['letterhead_footer']; ?>" />
        </div> 
		
		<div class="row">
          <label class="addlabel" for="trading_name">Trading Name: </label>
          <input class="addinput" type="text" name="trading_name" id="trading_name" value="<?php echo $c['trading_name']; ?>" />
        </div> 
		
		<div class="row">
          <label class="addlabel" for="company_address">Address: </label>
          <input class="addinput" type="text" name="company_address" id="company_address" value="<?php echo $c['company_address']; ?>" />
        </div>
		
		<div class="row">
          <label class="addlabel" for="outgoing_email">Outgoing Email Address: </label>
          <input class="addinput" type="text" name="outgoing_email" id="outgoing_email" value="<?php echo $c['outgoing_email']; ?>" />
        </div> 
		
		<div class="row">
          <label class="addlabel" for="bank">Bank: </label>
          <input class="addinput" type="text" name="bank" id="bank" value="<?php echo $c['bank']; ?>" />
        </div> 
		
		<div class="row">
          <label class="addlabel" for="abn">ABN: </label>
          <input class="addinput" type="text" name="abn" id="abn" value="<?php echo $c['abn']; ?>" />
        </div> 
		
		<div class="row">
          <label class="addlabel" for="bsb">BSB: </label>
          <input class="addinput" type="text" name="bsb" id="bsb" value="<?php echo $c['bsb']; ?>" />
        </div> 
		
		<div class="row">
          <label class="addlabel" for="ac_name">AC Name: </label>
          <input class="addinput" type="text" name="ac_name" id="ac_name" value="<?php echo $c['ac_name']; ?>" />
        </div> 
		
		<div class="row">
          <label class="addlabel" for="ac_number">AC Number: </label>
          <input class="addinput" type="text" name="ac_number" id="ac_number" value="<?php echo $c['ac_number']; ?>" />
        </div>

		<div class="row">
          <label class="addlabel" for="web">Web: </label>
          <input class="addinput" type="text" name="web" id="web" value="<?php echo $c['web']; ?>" />
        </div>

		<div class="row">
          <label class="addlabel" for="facebook">Facebook: </label>
          <input class="addinput" type="text" name="facebook" id="facebook" value="<?php echo $c['facebook']; ?>" />
        </div>
		
		<div class="row">
          <label class="addlabel" for="twitter">Twitter: </label>
          <input class="addinput" type="text" name="twitter" id="twitter" value="<?php echo $c['twitter']; ?>" />
        </div>
		
		<div class="row">
          <label class="addlabel" for="instagram">Instagram: </label>
          <input class="addinput" type="text" name="instagram" id="instagram" value="<?php echo $c['instagram']; ?>" />
        </div>
		
		<label for="submit">
            <input type="submit" value="Update" name="update" class="submitbtnImg" style="width: auto;" />
        </label>
        
      </form>
  
</div>

</div>

</div>

<br class="clearfloat" />




</body></html>