<?

# The main user functions are within here

class User {

	function SendRecovery($email)
	{
		$query = "SELECT * FROM staff_accounts WHERE Email = '$email' AND Deleted = 0 LIMIT 1";	
		
		$user_details = mysqlSingleRow($query);
		
		if(is_numeric($user_details['StaffID']))
		{
			
			# decode password
			$encrypt = new cast128();
			
			$encrypt->setkey(SALT);
			
			if(UTF8_USED)
			{
				$password_decrypted = addslashes($encrypt->decrypt(utf8_decode($user_details['Password'])));
			}
			else {
				$password_decrypted = addslashes($encrypt->decrypt($user_details['Password']));
			}
			
			$headers  = "From: SATS < ".INFO_EMAIL." >\r\n";
			$headers .= "Reply-To: SATS < ".INFO_EMAIL." >\r\n";
			$headers .= "Content-type: text/plain;charset=ISO-8859-9\n";
			
			$subject =  "SATS CRM password recovery";
			
			$to = $user_details['Email'];
					
			$body  = "Hi " . $user_details['FirstName'] . ",\n\n";
			$body .= "A SATS CRM password recovery request was issued for this account - the password for Email " . $user_details['Email'] . " is " . $password_decrypted . "\n\n";
			
			$body .= "This is an automated system, so please do not reply to this email.\n\n";
			
			$body .= "Regards,\n";
			$body .= "SATS Team.\n";
			$body .= "https://www.".CURRENT_DOMAIN;
			
			
			
			mail($to, $subject, $body, $headers);
			
			return 1;
		}
		else
		{
			return 0;	
		}
		
		
	}
	
	function getAllowableClasses()
	{
		# Pull types from database
		
		$query = "SELECT * FROM staff_classes WHERE ClassID > 1";
		
		$result = mysqlMultiRows($query);
		
		return $result;
	}
	
	function getAllStates()
	{
		# Pull types from database
		
		$query = "SELECT * FROM states_def";
		
		$result = mysqlMultiRows($query);
		
		return $result;
	}
	
	function getAllUsers($orderby = 'a.FirstName', $dir = 'ASC')
	{		
		$query = "SELECT a.*, b.ClassName FROM staff_accounts a, staff_classes b WHERE b.ClassID = a.ClassID AND Deleted = 0 ORDER BY $orderby $dir";
			
		$result = mysqlMultiRows($query);
		
		return $result;
		
	}

	
	function getUserDetails($user_id)
	{
		$query = "SELECT a.*, b.ClassName FROM (staff_accounts a, staff_classes b)  WHERE a.StaffID = '$user_id' AND b.ClassID = a.ClassID AND Deleted = 0 LIMIT 1";
		
		$result = mysqlSingleRow($query);
		
		if(is_numeric($result['StaffID'])) $result['States'] = $this->getUserStatePermissions($user_id);
		
		return $result;	
	}
	
	function getTechDetails($tech_id)
	{
		$query = "SELECT * FROM staff_accounts WHERE StaffID = '$tech_id' LIMIT 1";
		$result = mysqlSingleRow($query);
		return $result;	
	}
	
	function getUserStatePermissions($user_id)
	{
		$query = "SELECT sd.StateID, sd.state FROM states_def sd, staff_states ss WHERE ss.StateID = sd.StateID AND ss.StaffID = '$user_id' ORDER BY sd.StateID ASC";
		
		$result = mysqlMultiRows($query);
		
		return $result;
	}
	
	function prepareStateString($type = "WHERE", $prefix = "")
	{	
		$query = $type . " (";
	
		foreach($_SESSION['USER_DETAILS']['States'] as $index=>$data) $query .= $prefix."state = '" . $data['state'] . "' OR ";
	
		$query .= ")";
		
		$query = str_replace("OR )", " )", $query);
		
		return $query;
	}
	
	# delete user 
	function deleteUser($user_id)
	{
		# update user account set deleted
		$query = "UPDATE staff_accounts SET Deleted = 1 WHERE StaffID = '" . $user_id . "' LIMIT 1";
		
		if(mysql_query($query)) return 1;
		else return 0;
	}
	
	function authenticateUser($email, $password)
	{
		$query = "SELECT StaffID FROM staff_accounts WHERE Email = '$email' AND Password = '$password' AND Deleted = 0 AND `active` = 1 LIMIT 1";
        //$query = "SELECT StaffID FROM staff_accounts WHERE Email = '$email' AND Deleted = 0 LIMIT 1";

		$result = mysqlSingleRow($query);

		return $result['StaffID'];	
	}
	
	function isEmailAvailable($email, $user_id)
	{
		$user_id = intval($user_id);
			
		$query = "SELECT StaffID FROM staff_accounts WHERE Email = '$email' AND Deleted = 0";
		
		if($user_id > 0) $query .= " AND StaffID != $user_id";
		
		$query .= " LIMIT 1";
		
		$result = mysqlSingleRow($query);
		
		if(is_numeric($result['StaffID'])) return 0;

		else return 1;
	}
	
	
	function validatePassword($post_array)
	{
		if(sizeof($error_message) == 0) if(strcmp($post_array['Password'], $post_array['Password2']) != 0) $error_message = "Passwords do not match";
		
		if(sizeof($error_message) == 0) if(strlen($post_array['Password']) < 4 || strlen($post_array['Password']) > 12) $error_message = "Password must be between 4 to 12 characters";
		
		if(sizeof($error_message) == 0) if(preg_match('/[^a-zA-Z0-9]/', $post_array['Password'])) $error_message = "Password can only contain numbers and letters, (0-9, a-z, A-Z) ";
		
		return $error_message;
	}
	
	
	function updateUserDetails($post_array)
	{
		$query = "UPDATE staff_accounts SET 
					FirstName = '" . addSlashesData(stripSlashesData($post_array['FirstName'])) . "', 
					LastName = '" . addSlashesData(stripSlashesData($post_array['LastName'])) . "',
					ContactNumber = '" . addSlashesData(stripSlashesData($post_array['ContactNumber'])) . "',
					Email = '" . addSlashesData(stripSlashesData($post_array['Email'])) . "',
					ClassID = '" . $post_array['ClassID'] . "',
					active = '" . $post_array['active'] . "',
					TechID = '" . $post_array['TechID'] . "' ";
					
		$query .= "WHERE StaffID = '" . $post_array['StaffID'] . "' LIMIT 1";
					
		if(!mysql_query($query)) return 0;
		
		if(is_numeric($post_array['StaffID']) && is_array($post_array['States']))
		{
		
			# delete current permissions
			$query = "DELETE FROM staff_states WHERE StaffID = '" . $post_array['StaffID'] . "'";
			if(!mysql_query($query)) return 0;
			
			# add new permissions		
			foreach($post_array['States'] as $index=>$StateID)
			{
				$query = "INSERT INTO staff_states(StaffID, StateID) VALUES ('" . $post_array['StaffID'] . "', '" . $StateID['StateID'] . "')";
				if(!mysql_query($query)) return 0;
			}
		}
			
		return 1;
	}
	
	function addUser($post_array)
	{
		# don't slash password before encryption
		# $post_array['Password'] = stripSlashesData($post_array['Password']);
			
		# encrypt password
		$encrypt = new cast128();
		$encrypt->setkey(SALT);
		
		if(UTF8_USED)
		{
			$password_encrypted = utf8_encode($encrypt->encrypt($post_array['Password']));
		}
		else
		{
			$password_encrypted = $encrypt->encrypt($post_array['Password']);
		}
		
		
		# geenrate MD5
		$md5 = md5(rand(1,10000));
		
		$query = "INSERT INTO staff_accounts ( ClassID, Email, FirstName, LastName, Password, Hash, ContactNumber )
				  VALUES ( 
				  '" . $post_array['ClassID'] . "', 
				  '" . addSlashesData(stripSlashesData($post_array['Email'])) . "',
				  '" . addSlashesData(stripSlashesData($post_array['FirstName'])) . "',
				  '" . addSlashesData(stripSlashesData($post_array['LastName'])) . "',
				  '" . $password_encrypted . "',
				  '" . $md5 . "',
				  '" . addSlashesData(stripSlashesData($post_array['ContactNumber'])) . "')";
		
		if(mysql_query($query)) 
		{
			$StaffID = mysql_insert_id();
		
			if(is_numeric($StaffID) && is_array($post_array['States']))
			{
				# delete current permissions
				$query = "DELETE FROM staff_states WHERE StaffID = '" . $StaffID . "'";
				if(!mysql_query($query)) return 0;
				
				# add new permissions		
				foreach($post_array['States'] as $index=>$StateID)
				{
					$query = "INSERT INTO staff_states(StaffID, StateID) VALUES ('" . $StaffID . "', '" . $StateID['StateID'] . "')";
					if(!mysql_query($query)) return 0;
				}
			}
		
			return 1;
		}	
		else return 0;
		
		 
	}
	
	function updatePassword($password, $user_id)
	{
		# password should have already been encoded and validated
		
		$query = "UPDATE staff_accounts SET Password = '" . $password . "' WHERE StaffID = $user_id LIMIT 1";
		
		if(mysql_query($query)) return 1;	
		else return 0;

	}
	
	function isValidUser($email)
	{

		$query = "SELECT StaffID FROM staff_accounts WHERE Email = '$email' AND Deleted = 0 LIMIT 1";	
		
		$result = mysqlSingleRow($query);
		
		if(is_numeric($result['StaffID']))
		{
			return 1;	
		}
		else
		{
			return 0;	
		}
	}
	
	function canView($page)
	{
		global $ignore_pages;
		
		# if ignoring page assume yes
		if(in_array($page, $ignore_pages)) return 1;
		
		# if they are GLOBAL user assume yes
		if( $_SESSION['USER_DETAILS']['ClassID'] == 2 || $_SESSION['USER_DETAILS']['ClassID'] == 3 ||
			$_SESSION['USER_DETAILS']['ClassID'] == 5 || $_SESSION['USER_DETAILS']['ClassID'] == 7 ||
			$_SESSION['USER_DETAILS']['ClassID'] == 8 || $_SESSION['USER_DETAILS']['ClassID'] == 9 ||
			$_SESSION['USER_DETAILS']['ClassID'] == 10 || $_SESSION['USER_DETAILS']['ClassID'] == 11
		) return 1;
		else
		{
		
			# query permission table
			$query = "SELECT pp.PermissionID FROM page_permissions pp, pages p WHERE pp.PageID = p.PageID AND p.FileName = '$page' AND pp.ClassID = '" .$_SESSION['USER_DETAILS']['ClassID'] . "' LIMIT 1";
			
			$result = mysqlSingleRow($query);
			
			if(is_numeric($result['PermissionID'])) return 1;
			else return 0;
		}
	}
}

?>
