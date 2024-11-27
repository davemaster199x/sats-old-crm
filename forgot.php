<?

$title = "SATS CRM - Login";

include('inc/init.php');
include('inc/header_html.php');

if($_POST)
{
	
	
	$user = new User();
	
	$_POST['email'] = trimData($_POST['email']);
	$_POST['email'] = addSlashesData(stripSlashesData($_POST['email']));
	
	if(!validEmail($_POST['email'])) $error_message = "Please enter a valid email address";
	
	
	
	if(!isset($error_message)) 
	{
		if($user->isValidUser($_POST['email']))
		{
			
			#recover
			if($user->SendRecovery($_POST['email']))
			{
				$error_message = "Your password has been emailed to you";
			}
			else
			{
				$error_message = "Technical error - please try again";
			}
			
			
		}
		else
		{
			$error_message = "No account exists with that email address";
		}
	}
	
	$_POST['email'] = stripSlashesData($_POST['email']);
}

?>


<div id="login-container">

<div class="sats-tp-cnt">
    	<div class="sats-lgp-cnt">        	
            <? if($error_message) echo "<p class='logout'>$error_message</p>";?>
    		<p class="toll-num">1300 41 66 67</p>
    	</div>
    </div>

<div class="sats-md-cnt">
    	<div class="sats-login-fld">
        	<h1>Enter your Email to recover your password</h1>
            <form method="post" action="<?=URL;?>forgot.php">
                <input type="hidden" name="recover_attempt" value="1">
                <label for='email'>Email</label>
				<input type="text" value="<?=$_POST['email'];?>" title="email" name="email" class="addinput clearMeFocus"><br>
                <p><a href="index.php">Return to the login form</a></p>
				<input type="submit" name="submit" value="Recover my Password" class="submitbtnImg">
			</form>            
        </div>
    </div>

<div class="sats-ft-cnt">
    	<div class="sats-lgp-cnt">
        	<div class="ftr-lf-hl">
        	<a title="SATS" href="http://sats.com.au/"><img alt="" src="images/logo.png"></a>
        </div>
            <div class="ftr-rf-hl">
        	<div class="dv1">get connected with us</div>
            <div class="dv2">
            	<a title="Facebook" href="https://www.facebook.com/pages/SATS-Smoke-Alarm-Testing-Services/91545515845#"><img src="images/fb.png"></a>
                <a title="SATS" href="http://sats.com.au/"><img src="images/twitter.png"></a>
            </div>
            <div class="dv3">
            	<ul>
                	<li><a title="SATS Website" href="http://sats.com.au/">SATS Website</a></li>
                    <li>|</li>
                    <li><a title="AGENCY Website" href="http://agency.sats.com.au/">AGENCY Website</a></li>
                    <li>|</li>
                    <li><a title="SARAH" href="http://smokealarmreminders.com.au/">SARAH</a></li>
                </ul>
            </div>
        </div>
        </div>
    </div>
    
    </div>


</body>
</html>
