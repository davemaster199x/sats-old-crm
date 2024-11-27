<?

$title = "Delete SATS User";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

# handle id from get
$_GET['id'] = intval($_GET['id']);

# get sats user details
$user_details = $user->getUserDetails($_GET['id']);

if($_POST['confirm'] && is_numeric($_POST['StaffID']))
{
	if($user->deleteUser($_POST['StaffID']))
	{
		$success = 1;
		# change staff id for form below - we want to skip it
		$user_details['StaffID'] = "z";
	}
	else $error_message = "There was a technical problem, please try again";
}

?>
  <div id="mainContent">
  
  <div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Confirm SATS User Delete" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Confirm SATS User Delete</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>


<? if(is_numeric($user_details['StaffID'])): ?>

<? if($error_message): ?>
<p><?=$error_message;?></p>
<? endif;?>


<table border=0 cellspacing=0 cellpadding=5 width=100% class="sats_user">
      <tr>
        <th width="30%"><b>First Name</b></th>
        <td width="70%"><?=$user_details['FirstName'];?></td>
      </tr>
      <tr>
        <th><b>Last Name</b></th>
        <td><?=$user_details['LastName'];?></td>
      </tr>
      <tr>
        <th><b>Email</b></th>
        <td><?=$user_details['Email'];?></td>
      </tr>
      <tr>
        <th><b>Phone Number</b></th>
        <td><?=$user_details['ContactNumber'];?></td>
      </tr>
      <tr>
        <th><b>User Type</b></th>
        <td><?=$user_details['ClassName'];?></td>
      </tr>
      <tr>
        <th><b>State Access</b></th>
        <td><? foreach($user_details['States'] as $index=>$data): ?>
          <?=$data['state'];?>
          <br />
          <? endforeach;?></td>
      </tr>
      <tr>
        <th></th>
        <td><form action="" method="post">
            <input type="hidden" name="StaffID" value="<?=$user_details['StaffID'];?>">
            <input type="hidden" name="confirm" value="1">
            <input type="submit" value="Delete User" class="submitbtnImg">
          </form></td>
      </tr>
    </table>


<p><a href="view_sats_user_details.php?id=<?=$user_details['StaffID'];?>" class="submitbtnImg colorwhite">Go Back to Account Details</a></p>
<? else: ?>

	<? if($success): ?>
    
    <p>User has been deleted, please <a href="<?=URL;?>view_sats_users.php">click here</a> to return to main user list.</p>
    
    <? else: ?>
    
    <p>Invalid user ID, please <a href="<?=URL;?>view_sats_users.php">go back</a>.</p>
    
    <? endif; ?>

<? endif; ?>

  </div>
  
  </div>

<br class="clearfloat" />

</body>
</html>
