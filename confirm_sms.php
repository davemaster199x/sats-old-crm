<?php
include('inc/init_for_ajax.php');
$job_url_enc = $_GET['job_id'];

$encrypt = new cast128();
$encrypt->setkey(SALT);
$job_id = $encrypt->decrypt(utf8_decode(rawurldecode($job_url_enc)));
?>
<!DOCTYPE html>
<html>
<head>
<title>Title of the document</title>
</head>

<body>
<p>Are you sure you sure you want to confirm appointnment?</p><br />
<form>
<?php
echo "Job ID: {$job_id}<br />";
?>
<select>
	<option value="">--- Select ---</option>
	<option value="1">Yes</option>
	<option value="0">No</option>
</select>
<input type="submit" value="submit" />
</form>
</body>

</html>