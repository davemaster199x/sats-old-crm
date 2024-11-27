<?

$crm = new Sats_Crm_Class;

# Process logout
if($_GET['logout'])
{
	# reset session details
	$_SESSION = array();
	
	# redirect to login page
	header("Location: " . URL . "index.php?didlogout=1");
	die();
}


# Process login
if($_POST['login_attempt'])
{
	# clean up input - we want to stripslashes from the password since it will be encrypted below before entering database
	$login_email = addslashes(stripslashes(trim($_POST['email'])));
	$login_password = stripslashes(trim($_POST['password']));
	
	
	# if they didnt enter an email address or password, send them back and trigger error - trigger die in case obscure browser does not like header redirects
	if(strlen($login_email) == 0) 
	{	
		$error_message = "Please enter an email address";
	}
	
	if(strlen($login_password) == 0)
	{
		$error_message = "Please enter a password";
	}
	
	if(!isset($error_message))
	{
	
		# attempt to authenticate user
		$encrypt = new cast128();
		$encrypt->setkey(SALT);
		
		if(UTF8_USED)
		{
			$password_encrypted = addslashes(utf8_encode($encrypt->encrypt($login_password)));
		}
		else
		{
			$password_encrypted = addslashes($encrypt->encrypt($login_password));
		}

		$user_id = $user->authenticateUser($login_email, $password_encrypted);
		
		if(intval($user_id) > 0) 
		{
			
			// verift CSRF token
			if (!empty($_POST['csrf_token'])) {
				if ( $_SESSION['csrf_token'] === $_POST['csrf_token'] ) {

					// Proceed to process the form data
					$_SESSION['USER_DETAILS']['StaffID'] = $user_id;

					// capture login
					$capture_login_sql_str = "
					INSERT INTO 
					`crm_user_logins`(
						`user`,
						`ip`,
						`date_created`
					)
					VALUES(
						{$user_id},
						'{$_SERVER['REMOTE_ADDR']}',
						'".date('Y-m-d H:i:s')."'
					)
					";
					mysql_query($capture_login_sql_str);
					
					# redirect to main
					header("Location: " . URL . "main.php");
					die();

				}
			}
			
		}
		else
		{
			$error_message = "Invalid login details, please try again";
		}
	
	}
}

# Populate User Details, or redirect to login (security measure)
if(intval($_SESSION['USER_DETAILS']['StaffID']) > 0)
{
	$tmp = $user->getUserDetails($_SESSION['USER_DETAILS']['StaffID']);
	
	$_SESSION['USER_DETAILS']['FirstName'] = $tmp['FirstName'];
	$_SESSION['USER_DETAILS']['LastName'] = $tmp['LastName'];
	$_SESSION['USER_DETAILS']['Email'] = $tmp['Email'];
	$_SESSION['USER_DETAILS']['ClassID'] = $tmp['ClassID'];
	$_SESSION['USER_DETAILS']['TechID'] = $tmp['TechID'];
	$_SESSION['USER_DETAILS']['ClassName'] = $tmp['ClassName'];
	$_SESSION['USER_DETAILS']['ContactNumber'] = $tmp['ContactNumber'];
	$_SESSION['USER_DETAILS']['States'] = $tmp['States'];

	# unset $tmp
	unset($tmp);
	
	# Perform Security access check on page, ClassID 2 (Global) can do everything so we ignore
	if(!$user->canView(PAGE))
	{
		# if not allowed redirect to main with error banner - and exit so no more scripts can run
		header("location: " . URL . "main.php?restricted=1&test=1&can_view=".$user->canView(PAGE));
		exit();
	}
	
	# If no referrer throw an error - this means they could have manually typed in a url (ie change ?id=123 to ?id=124 and resubmit
	if(!in_array(PAGE, $ignore_pages) && $_SERVER['HTTP_REFERER'] == "")
	{		
		
		if ( CURRENT_COUNTRY == 1 ) { // AU

            if ( IS_PRODUCTION == 1 ) { // LIVE

                
				$allowed_people_to_direct_access_url = array(
					2025, // Daniel
					2070, // Developer testing
					2478 // Simon A
				);

            } else { // DEV

                $allowed_people_to_direct_access_url = array(
					2025, // Daniel
					2070, // Developer testing
					2221 // Simon A
				);

            }

        } else if ( CURRENT_COUNTRY == 2 ){ // NZ

			$allowed_people_to_direct_access_url = array(
				2025, // Daniel
				2070, // Developer testing
				2322 // Simon A
			);

		}
		
		if (!in_array($_SESSION['USER_DETAILS']['StaffID'], $allowed_people_to_direct_access_url)){
			header("location: " . URL . "main.php?restricted=1&test=2");
			exit();
		}
	}
	
	# If is a technician, and this is the main page, redirect to their schedule
	if($_SESSION['USER_DETAILS']['ClassName'] == "TECHNICIAN" && PAGE == "main.php")
	{
		
		
		// check tech run
		$tr_sql = mysql_query("
			SELECT *
			FROM `tech_run`
			WHERE `assigned_tech` = {$_SESSION['USER_DETAILS']['StaffID']}
			AND `date` = '".date('Y-m-d')."'
		");
		
		if( mysql_num_rows($tr_sql)>0 ){
			$tr = mysql_fetch_array($tr_sql);
			$tr_id = $tr['tech_run_id'];
			$_SESSION['USER_DETAILS']['has_tech_run'] = 1;
			$_SESSION['USER_DETAILS']['tr_id'] = $tr_id;
			//header("location: /tech_day_schedule.php?tr_id={$tr_id}");
		}else{
			//header("location: " . URL . "view_tech_schedule.php?id=" . $_SESSION['USER_DETAILS']['TechID'] . "&month=" . date('m') . "&year=" . date('y'));
			//header("location: " . URL . "view_tech_schedule_day.php?id=" . $_SESSION['USER_DETAILS']['TechID'] . "&day=". date('d') ."&month=" . date('m') . "&year=" . date('y'));
			//header("location: /tech_day_schedule.php?tr_id={$tr_id}");
		}
		
		

		
		$crm_ci_page = '/home/index';
		$page_url = $crm->crm_ci_redirect($crm_ci_page);
		header("location: {$page_url}");
		
		
		
	}
}
else
{
	if(PAGE != "index.php" && PAGE != "forgot.php")
	{
		# redirect to main
		header("Location: " . URL . "index.php");
		die();
	}
}


?>
