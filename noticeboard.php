 
<?

$title = "Add New Notice Form";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');
?>

<link rel="stylesheet" href="/inc/css/summernote/summernote.css">
<link rel="stylesheet" href="/inc/css/separate/pages/editor.css">
<link rel="stylesheet" href="/inc/css/bootstrap/bootstrap.min.css" />
<link rel="stylesheet" href="/inc/css/summernote/main.css">
<style type="text/css">
    *{
       /* box-sizing: content-box;*/
    }
    body{
       /* font-family: 100% arial,sans-serif !important;*/
    }
    .search_jobs_div label{
        display: inline-block;
    }
    .sats-breadcrumb ul li{
        height: 34px;
    }
    .statement_generic_note{clear:both;padding-top:30px;}
</style>

  <div id="mainContent">

<div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Noticeboard" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Agencies / Agency Noticeboard</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

<?php

   // (1) Open the database connection and use the winestore database

   

   // (2) Run the query on the winestore through the connection

   $result = mysql_query("SELECT * FROM noticeboard WHERE `country_id` = {$_SESSION['country_default']}", $connection);
   

   if(mysql_num_rows($result) > 0){

   	  $row = mysql_fetch_array($result);

   }

   else

   	  $noticetext = "";

   $noticetext = $row['notice'];


?>
    <? # Noticeboard has special permissions - only GLOBAL and FULL ACCCESS can edit - rest view only ?>
    <? if( $_SESSION['USER_DETAILS']['ClassID'] == 2 || $_SESSION['USER_DETAILS']['ClassID'] == 9 ): ?>
	<div class="noticeboard">
		<form id="form1" name="form1" method="POST" action="<?=URL;?>change_noticeboard.php">

            <div class="summernote-theme-5" style="text-align:left;">
                    <textarea class="summernote" name="notice" id="notice" ><?php echo "$noticetext"; ?></textarea>
            </div>
            
			<div style="float:left;margin-top:10px;">
			<input type="hidden" name="nb_id" value="<?php echo $row['id']; ?>" />
			<input class="submit submitbtnImg" type="submit" name="submit" id="submit" value="Modify">
			</div>
		</form>
    </div>
    

    <!-- Statement Genenric Note START -->
    <?php
        //statement query
        $statement_sql = mysql_query("SELECT `statements_generic_note`,`statements_generic_note_ts` FROM crm_settings WHERE `country_id` = {$_SESSION['country_default']}");
        $statement_row = mysql_fetch_array($statement_sql);
        $statement_text = $statement_row['statements_generic_note'];
        //statement query end
    ?>
    <div class="statement_generic_note">
        <h4>Agency Statement Generic Note</h4>
        <form id="form2" name="form2" method="POST" action="<?=URL;?>change_statement_generic_note.php">
            <div class="summernote-theme-5" style="text-align:left;">
                <textarea class="summernote" name="statement_generic_note" id="statement_generic_note" ><?php echo "$statement_text"; ?></textarea>
            </div>
            <div style="float:left;margin-top:10px;">
                <input class="submit submitbtnImg" type="submit" name="submit_generic_note" id="submit_generic_note" value="Modify">
			</div>
        </form>
    </div>
     <!-- Statement Genenric Note START -->


    <? else: ?>
    <?=$noticetext;?>
    <? endif; ?>

   
    
    
      <!-- end #mainContent -->
  </div>

</div>

<br class="clearfloat" />
<script type="text/javascript" src="/inc/js/popper/popper.min.js"></script>
<script type="text/javascript" src="/inc/js/bootstrap/bootstrap.min.js"></script>
<script type="text/javascript" src="/inc/js/summernote/summernote.min.js"></script>
</body>

<script type="text/javascript">

$(function(){
    
    jQuery('.summernote').summernote();
    
});
</script>
</html>