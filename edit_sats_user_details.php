<?

$title = "Edit SATS User Details";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

# handle id from get
$_GET['id'] = intval($_GET['id']);

# get sats user details
if($_POST)
{ 
	$user_details = $_POST;
	
	#put states as same format that comes from db
	if(is_array($_POST['States']))
	{
		$tmp = array();
		for($x = 0; $x < sizeof($_POST['States']); $x++) $tmp[$x]['StateID'] = $_POST['States'][$x];
		$user_details['States'] = $tmp;
		unset($tmp);
		
	}
	else $error_message = "Must include at least one state";
	
	#validate email address
	if(!validEmail($_POST['Email'])) $error_message = "Please enter a valid email address";
	if(!$user->isEmailAvailable($_POST['Email'], $_POST['StaffID'])) $error_message = "Email address already in use, please choose another";
	
	if(!isset($error_message))
	{
		if($user->updateUserDetails($user_details)) $error_message = "User details updated successfully";
		else $error_message = "There was a technical problem, please try again";
	}
}
else $user_details = $user->getUserDetails($_GET['id']);

# get allowable classes
$classes = $user->getAllowableClasses();

# get all states
$states = $user->getAllStates();

# get tech list
$techs = getTechList();

?>
  <div id="mainContent">
  
  <div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Edit SATS User Details" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Edit SATS User Details</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>


<? if(is_numeric($user_details['StaffID'])): ?>

<? if($error_message): ?>
<p><?=$error_message;?></p>
<? endif;?>

<form action="" method="post">

<div id="esud-frm" class="formholder addagency" style="margin-bottom: 20px; overflow: auto;">
  
  <div class="row">
      <label class="addlabel">First Name</label>
      <input type="text" name="FirstName" value="<?=$user_details['FirstName'];?>" class="sats_user">
  </div>
   <div class="row">
      <label class="addlabel">Last Name</label>
      <input type="text" name="LastName" value="<?=$user_details['LastName'];?>" class="sats_user">
  </div>
   <div class="row">
      <label class="addlabel">Email</label>
      <input type="text" name="Email" value="<?=$user_details['Email'];?>" class="sats_user">
  </div>
   <div class="row">
      <label class="addlabel">Phone Number</label>
      <input type="text" name="ContactNumber" value="<?=$user_details['ContactNumber'];?>" class="sats_user">
  </div>
   <div class="row">
      <label class="addlabel">User Type</label>
      <select name="ClassID" class="sats_user">
              <? foreach($classes as $index=>$data): ?>
              <option value="<?=$data['ClassID'];?>" <? if($user_details['ClassID'] == $data['ClassID']):?>selected <? endif;?> class="sats_user">
              <?=$data['ClassName'];?>
              </option>
              <? endforeach;?>
            </select>
  </div>
  
   <? if($user_details['ClassID'] == 6): ?>
  <div class="row">
      <label class="addlabel">Link to Technician</label>
     <select name="TechID" class="sats_user">
              <option value=""></option>
              <? foreach($techs as $index=>$data): ?>
              <option value="<?=$data['id'];?>" <? if($user_details['TechID'] == $data['id']):?>selected <? endif;?> class="sats_user">
              <?=$data['first_name'];?>
              <?=$data['last_name'];?>
              </option>
              <? endforeach;?>
            </select>
  </div>
  <? endif;?>
  
  <div class="row">
      <label class="addlabel">State Access</label>
      <div class="vsud-inner">
      	<? foreach($states as $index=>$data): ?>
        
                  <input type="checkbox" id="<?=$data['StateID'];?>" name="States[]" value="<?=$data['StateID'];?>" <? if(stateIsChecked($user_details['States'],$data['StateID'])):?> checked <? endif;?>>
        	<label for="<?=$data['StateID'];?>" class="statelabel">
                    <?=$data['state'];?>
                  </label>	
        <? endforeach; ?>
      </div>
  </div>
  
  <? foreach($user_details['States'] as $index=>$data): ?><?=$data['state'];?> - <? endforeach;?>
  
  <div class="row" style="clear: both; padding-top: 10px;">
      <label class="addlabel">Active</label>
      <select name="active" class="sats_user">
              <option value="1" <?php if($user_details['active'] == 1) echo 'selected="selected"' ?>>Active</option>
              <option value="0" <?php if($user_details['active'] == 0) echo 'selected=""' ?>>Inactive</option>
            </select>
  </div>
  
  <div class="row" style="width:165px; float:left;">
     <input type="hidden" name="StaffID" value="<?=$user_details['StaffID'];?>">
     <input type="submit" value="Update User Details" Details class="submitbtnImg" style="width:auto;">
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
