
<?

$title = "SATS CRM - Invalid Login";

include('inc/header_html.php');


?>

<script src="js/css_browser_selector.js" type="text/javascript"></script>

<div id="login-container">

<div class="sats-tp-cnt">
    	<div class="sats-lgp-cnt">        	
            <? if($error_message) echo "<p class='logout'>$error_message</p>";?>
    		<p class="toll-num">1300 41 66 67</p>
    	</div>
    </div>

<div class="sats-md-cnt">
    	<div class="sats-login-fld">
        	<h1>Agency Login</h1>
             <div class="dv-invld2">Invalid Login - Please try again</div>
            <form method="post" action="alogin.php">
                <input class="addinput clearMeFocus" type=text name="login_id">
                <input class="addinput clearMeFocus" type="password" name="password">
                <input type="submit" value="Login" class="submitbtnImg">
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
