<?

$title = "SATS CRM - Login";

include('inc/init.php');
include('inc/header_html.php');

// generate CSRF token
session_start();
if (empty($_SESSION['csrf_token'])) {
    if (function_exists('mcrypt_create_iv')) {
        $_SESSION['csrf_token'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
    } else {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}

// get country
if( CURRENT_DOMAIN=='sats.com.au' ){ // AU
	$country_id = 1;
}else{
	$country_id = 2;
}
$cntry_sql = getCountryViaCountryId($country_id);
$cntry = mysql_fetch_array($cntry_sql);


$user = mysql_real_escape_string(urldecode($_GET['user']));
$pass = mysql_real_escape_string(urldecode($_GET['pass']));


if($_GET['didlogout']) $error_message = "Logged out successfully";

?>

<script src="js/css_browser_selector.js" type="text/javascript"></script>
  
<div id="login-container">

<div class="sats-tp-cnt">
    	<div class="sats-lgp-cnt">        	
    		<p class="toll-num"><?php echo $cntry['agent_number']; ?></p>
    	</div>
    </div>

<div class="sats-md-cnt">
    	<div class="sats-login-fld">
        	<h1><?php echo $cntry['iso']; ?> CRM Login</h1>
            <? if($error_message) echo "<div class='dv-invld2'>Invalid Login - Please try again</div>";?>
            <form method="post" action="<?=URL;?>index.php">
                <input type="hidden" name="login_attempt" value="1">
				<input type="text" value="<?php echo $user; ?>" title="email" name="email" class="addinput clearMeFocus"><br>
				<input type="password" value="<?php echo $pass; ?>" title="Password" name="password" class="addinput clearMeFocus"><br>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
				<p>Forgot your password?&nbsp;<a title="Click here" href="/forgot.php">Click here</a></p>
				<input type="submit" name="submit" value="Login" class="submitbtnImg">
			</form>            
        </div>
    </div>
	
<?php

$current_url = $_SERVER['HTTP_HOST'];

// sats site
if( strpos($current_url, 'com.au')!==false ) { // AU
	$sats_site = 'sats.com.au';
}else if( strpos($current_url, 'co.nz')!==false ){ // NZ	
	$sats_site = 'sats.co.nz';	
}

// agency site
if( strpos($current_url, 'crmdev')!==false ){ // dev	
	$agency_site = "agencydev.{$sats_site}";
}else{  // live
	$agency_site = "agency.{$sats_site}";
}
?>

<div class="sats-ft-cnt">
    	<div class="sats-lgp-cnt">
        	<div class="ftr-lf-hl">
        	<a title="SATS" href="//<?php echo $sats_site; ?>"><img alt="" src="images/logo.png"></a>
        </div>
            <div class="ftr-rf-hl">
        	<div class="dv1">get connected with us</div>
            <div class="dv2">
            	<a title="Facebook" href="https://www.facebook.com/pages/SATS-Smoke-Alarm-Testing-Services/91545515845#"><img src="images/fb.png"></a>
                <a title="Twitter" href="https://twitter.com/SATS_Australia"><img src="images/twitter.png"></a>
            </div>
            <div class="dv3">
            	<ul>
                	<li><a title="SATS Website" href="//<?php echo $sats_site; ?>">SATS Website</a></li>
                    <li>|</li>
                    <li><a title="AGENCY Website" href="//<?php echo $agency_site; ?>">AGENCY Website</a></li>
                </ul>
            </div>
        </div>
        </div>
    </div>
    
    </div>


</body>
</html>
