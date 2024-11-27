<?

$title = "Change Notice Form";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>
  <div id="mainContent">
  
  <div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Change Noticeboard" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Change Noticeboard</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

    <!--<h1 class="style4">Change Noticeboard</h1>-->
<?php

$nb_id = mysql_real_escape_string($_POST['nb_id']);
$notice = addslashes($_POST['notice']);

	if($nb_id!=""){
		$insertQuery = "UPDATE noticeboard set notice='$notice' WHERE id={$nb_id}";
	}else{
		$insertQuery = "
			INSERT INTO
			`noticeboard` (
				`notice`,
				`date_updated`,
				`country_id`
			)
			VALUES(
				'{$notice}',
				'".date("Y-m-d H:i:s")."',
				{$_SESSION['country_default']}
			)
		";
	}
     



     if (@ mysql_query ($insertQuery, $connection) && (@ mysql_affected_rows() == 1))

        echo "<div class='success'>Noticeboard Successfully Updated</div>";

	//else

        //echo "<h3>A fatal error occurred</h3><br>" . $insertQuery;

		

	 if(@ mysql_affected_rows() == 0)

		echo "<div class='success'>No changes made to noticeboard.</div>";	

     elseif(@ mysql_affected_rows() > 1)

        echo "<div class='success'>A fatal error occurred</div><br>" . $insertQuery;

		

		// echo "<br><br>First Name: " . $first_name .".<Br>";

		// echo "Post Vars :".$HTTP_POST_VARS . ":";


?>

  </div>

</div>

<br class="clearfloat" />


</body>

</html>