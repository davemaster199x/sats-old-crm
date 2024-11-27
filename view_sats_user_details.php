<?

$title = "View SATS User Details";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

# handle id from get
$_GET['id'] = intval($_GET['id']);

# get sats user details
$user_details = $user->getUserDetails($_GET['id']);

?>

<div id="mainContent">
  
  <div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="SATS User Details" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>SATS User Details</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

<? if(is_numeric($user_details['StaffID'])): ?>

<? if($error_message): ?>
<p><?=$error_message;?></p>
<? endif;?>

<div class="formholder addagency" style="margin-bottom: 20px; overflow: auto;">
  
  <div class="row">
      <label class="addlabel">First Name</label>
      <span class="vsud-l"><?=$user_details['FirstName'];?></span>
  </div>
   <div class="row">
      <label class="addlabel">Last Name</label>
      <span class="vsud-l"><?=$user_details['LastName'];?></span>
  </div>
   <div class="row">
      <label class="addlabel">Last Name</label>
      <span class="vsud-l"><?=$user_details['Email'];?></span>
  </div>
   <div class="row">
      <label class="addlabel">Email</label>
      <span class="vsud-l"><?=$user_details['ContactNumber'];?></span>
  </div>
   <div class="row">
      <label class="addlabel">Phone Number</label>
      <span class="vsud-l"><?=$user_details['ClassName'];?></span>
  </div>
   <div class="row">
      <label class="addlabel">User Type</label>
      <span class="vsud-l"><?=$user_details['ClassName'];?></span>
  </div>
  
  <? if(is_numeric($user_details['id'])): ?>
  
  <div class="row">
      <label class="addlabel">Linked to Tech:</label>
      <span class="vsud-l"><?=$user_details['first_name'];?> <?=$user_details['last_name'];?></span>
  </div>
  
  <? endif; ?>
  
  <div class="row">
      <label class="addlabel">State Access</label>
      <span class="vsud-l"><? foreach($user_details['States'] as $index=>$data): ?><?=$data['state'];?><br /><? endforeach;?></span>
  </div>
  
  <div class="row">
      <label class="addlabel">Active</label>
      <span class="vsud-l"> 
	   <?php if($user_details['active'] == 0) echo 'Inactive'; 
       if($user_details['active'] == 1) echo 'Active';?>
      </span>
  </div>

</div>

<h2 class="heading">Account Options</h2>
<div class="vsud-btm-lk">
<a href="edit_sats_user_details.php?id=<?=$user_details['StaffID'];?>" class="submitbtnImg">Edit Account Details</a>
<a href="edit_sats_user_password.php?id=<?=$user_details['StaffID'];?>" class="submitbtnImg">Change Password</a>
<a href="delete_sats_user.php?id=<?=$user_details['StaffID'];?>" class="submitbtnImg">Delete Account</a>
</div>
<? else: ?>
<p>Invalid user ID, please <a href="<?=URL;?>view_sats_users.php">go back</a>.</p>

<? endif; ?>

  </div>

</div>

<br class="clearfloat" />

</body>
</html>
