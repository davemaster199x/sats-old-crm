<?

$title = "Change Generic Note Form";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>
  <div id="mainContent">
  
  <div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Change Generic Note " href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Change Generic Note </strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

    <!--<h1 class="style4">Change Noticeboard</h1>-->
<?php


    $statement_generic_note = addslashes($_POST['statement_generic_note']);
    $statement_generic_note_ts = date('Y-m-d H:i:s');

	$udpateQuery = "UPDATE crm_settings set statements_generic_note ='$statement_generic_note', statements_generic_note_ts = '$statement_generic_note_ts'  WHERE country_id={$_SESSION['country_default']}";

    
     if (@ mysql_query ($udpateQuery, $connection) && (@ mysql_affected_rows() == 1))

        echo "<div class='success'>Agency statement generic note successfully Updated</div>";

		

	 if(@ mysql_affected_rows() == 0)

		echo "<div class='success'>No changes made to generic note.</div>";	

     elseif(@ mysql_affected_rows() > 1)

        echo "<div class='success'>A fatal error occurred</div><br>" . $udpateQuery;

		

		// echo "<br><br>First Name: " . $first_name .".<Br>";

		// echo "Post Vars :".$HTTP_POST_VARS . ":";


?>

  </div>

</div>

<br class="clearfloat" />


</body>

</html>