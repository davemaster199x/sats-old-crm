<?php 

include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');

// include class 
include($_SERVER['DOCUMENT_ROOT'].'class.upload/src/class.upload.php');

// upload
if($_POST['Submit']){
	
	
	$handle = new upload($_FILES['image_field']);
	if ($handle->uploaded) {
	  $handle->file_new_name_body   = 'image_resized';
	  $handle->image_resize         = true;
	  $handle->image_x              = 760;
	  $handle->image_ratio_y        = true;
	  $handle->process($_SERVER['DOCUMENT_ROOT'].'temp_upload/');
	  if ($handle->processed) {
		echo 'image resized';
		$handle->clean();
	  } else {
		echo 'error : ' . $handle->error;
	  }
	}
	
	$handle = new upload($_FILES['image_field2']);
	if ($handle->uploaded) {
	  $handle->file_new_name_body   = 'image_resized_2nd';
	  $handle->image_resize         = true;
	  $handle->image_x              = 760;
	  $handle->image_ratio_y        = true;
	  $handle->process($_SERVER['DOCUMENT_ROOT'].'temp_upload/');
	  if ($handle->processed) {
		echo 'image resized';
		$handle->clean();
	  } else {
		echo 'error : ' . $handle->error;
	  }
	}
	
	
}




?>
<html>
<head>
<title>Test Resize Image</title>
</head>
<body>
<h1>Test Resize Image</h1>
<form enctype="multipart/form-data" method="post">
  image 1 <input type="file" size="32" name="image_field" accept="image/*" capture="camera" /><br />
  image 2 <input type="file" size="32" name="image_field2" accept="image/*" capture="camera" /><br /><br />
  <input type="submit" name="Submit" value="upload">
</form>
</body>
</html>