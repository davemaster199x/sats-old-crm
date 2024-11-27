<?

$title = "Edit SATS User Password";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

# handle id from get
$_GET['id'] = intval($_GET['id']);

# get sats user details
$user_details = $user->getUserDetails($_GET['id']);

if($_POST)
{ 
	# trim and slash data
	$_POST = trimData($_POST);
	$_POST = addSlashesData(stripSlashesData($_POST));
	
	$error_message = $user->validatePassword($_POST);
	
	if(!isset($error_message))
	{
		# encode password for database insertion
		
		$encrypt = new cast128();
		$encrypt->setkey(SALT);
		
		if(UTF8_USED)
		{
			$password_encrypted = addslashesData(utf8_encode($encrypt->encrypt($_POST['Password'])));
		}
		else
		{
			$password_encrypted = addslashesData($encrypt->encrypt($_POST['Password']));
		}
		
		if($user->updatePassword($password_encrypted, $user_details['StaffID'])) $error_message = "Password updated successfully";
		else $error_message = "There was a technical error - please try again";
	}
}



?>
  <div id="mainContent">

<div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Edit SATS User Password" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Edit SATS User Password</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

<? if(is_numeric($user_details['StaffID'])): ?>

<? if($error_message): ?>
<p class="red"><?=$error_message;?></p>
<? endif;?>


<form action="" method="post">

<div class="esu-fr formholder addagency">
  
  <div class="row">
      <label class="addlabel">Account</label>
      <span class="vsud-l"><?=$user_details['Email'];?></span>
  </div>
   <div class="row">
      <label class="addlabel">New Password</label>
      <input type="password" name="Password" value="<?=$_POST['Password'];?>" class="sats_user">
  </div>
   <div class="row">
      <label class="addlabel">Retype</label>
      <input type="password" name="Password2" value="<?=$_POST['Password2'];?>" class="sats_user">
  </div>
  
  <div class="row" style="width:190px; float:left;">
      <input type="hidden" name="StaffID" value="<?=$user_details['StaffID'];?>">
      <input type="submit" value="Update User Password" class="submitbtnImg" Details style="width:auto;">
  </div>
  <div style="float: left;">  	
     <a href="view_sats_user_details.php?id=<?=$user_details['StaffID'];?>" class="submitbtnImg colorwhite" style="display:block;">Go Back to Account Details</a>
  </div>
  
  

</div>


</form>

<? else: ?>
<p>Invalid user ID, please <a href="<?=URL;?>view_sats_users.php">go back</a>.</p>

<? endif; ?>

  </div>
  </div>

<br class="clearfloat" />

</body>
</html>
