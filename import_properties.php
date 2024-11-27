<?

$title = "Import Properties";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>

<style>
.jred_textnCAps{
	color: red;
	
}
</style>
<div id="mainContent">
  
  <div class="sats-middle-cont">
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Import Properties" href="/import_properties.php"><strong>Import Properties</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
	
	<?php
	if($_GET['success']==1){
		echo '<div class="success">Import Successful</div>';
	}
	?>


    <div class="jalign_left">	
	
	
		<div style="margin: 40px 0 0;text-align: left;">
			<h2 class="heading">IMPORTANT</h2>
			<ul>
				<li>Please use the CSV template below.The format must be exactly the same otherwise the Import will not work.</li>
				<li>The state must be in capitals NSW, QLD etc. </li>
				<li>Replace all "/" with "*"</li>	
				<li>Please take your time to get the data perfect before importing, because the process can’t be reversed</li>							
				<!--<li>Please check the time of <span style="color:red;">UPLOAD</span>. These jobs will go to <span style="color:red;">SEND LETTERS</span> and be auto cleared every hour on the hour</li>-->				
				<li>Please go to <span style="color:red;">SEND LETTERS</span> and untick 'auto emails active' so no tenants will be emailed until you retick 'auto emails active'</li>
				<li>These jobs will go to <span style="color:red;">SEND LETTERS</span> and you will need to process</li>
			</ul>
			
			<h2 class="heading" style="margin-top: 37px;">Template</h2>
			<a href="import_properties_template.php">Download CSV Template</a>
		</div>
	
        <div style="padding-top: 20px;" id="div_staff" class="addproperty formholder">
			<form id="form_sales_document" method="post" action="/upload_csvfile_script.php" enctype="multipart/form-data" >
				<div class="row">
				<h2 class="heading">Select Agency</h2>
				<select name="agency" id="agency" style="width: 260px;">
					<option value="">--Select--</option>
					<?php
					// get agency
					$agency_sql = mysql_query("
						SELECT agency_id, agency_name, address_3 
						FROM agency 
						WHERE `status` = 'active' 
						AND `country_id` = {$_SESSION['country_default']} 
						AND `agency_id` != 1 
						ORDER BY `agency_name` ASC
					");
					$agency = mysql_query($agency_sql);
					while( $agency = mysql_fetch_array($agency_sql) ){ ?>
						<option value="<?php echo $agency['agency_id']; ?>"><?php echo $agency['agency_name']; ?></option>
					<?php	
					}
					?>					
				</select>
				</div>	
				
				<div class="row">
					<h2 class="heading">Upload Type</h2>
					<select name="upload_type" id="upload_type" style="width: 260px;">
						<option value="">--Select--</option>
						<option value="nr">ALL No Response</option>
						<option value="sats">ALL SATS</option>
						<option value="mixed">MIXED Upload</option>					
					</select>					
				</div>
				
				<div class="row" style="margin-top: 33px; text-align: left;">
					<div id="ut_nr" style="display:none;">
						All Properties will be uploaded with <span class="jred_textnCAps">ALL</span> services as <span class="jred_textnCAps">NO RESPONSE</span>
					</div>
					<div id="ut_sats" style="display:none;">
						All Properties will be uploaded with <span class="jred_textnCAps">SMOKE ALARMS</span> as <span class="jred_textnCAps">SATS</span> and <span class="jred_textnCAps">OTHER</span> services as <span class="jred_textnCAps">NO RESPONSE</span>
					</div>
					<div id="ut_mixed" style="display:none;">
						You will have a preview screen to <span class="jred_textnCAps">CHOOSE ALL SERVICES AND ALL STATUSES</span>
					</div>
				</div>
				
				<div id="import_csv_div" style="display:none;">
					<div class="row">
						<h2 class="heading">Import CSV</h2>
						<input type="file" name="file" id="file" class="fname uploadfile submitbtnImg" style="width: auto;" />
					</div>			
					<div style="padding-top: 15px; text-align:left;" class="row clear">
						<input type="submit" class="submitbtnImg" value="Upload" style="width: auto;" />
					</div>
				 </div>
			</form>
		</div>			
	</div>	
   

  </div>
  
</div>

<br class="clearfloat" />

<script>
jQuery(document).ready(function(){
	
	// upload type text script
	jQuery("#upload_type").change(function(){
		
		switch(jQuery(this).val()){
			case 'nr':
				jQuery("#ut_nr").show();
				jQuery("#ut_sats").hide();
				jQuery("#ut_mixed").hide();
			break;
			case 'sats':
				jQuery("#ut_sats").show();
				jQuery("#ut_nr").hide();
				jQuery("#ut_mixed").hide();
			break;
			case 'mixed':
				jQuery("#ut_mixed").show();
				jQuery("#ut_nr").hide();
				jQuery("#ut_sats").hide();
			break;
			default:
				jQuery("#ut_nr").hide();
				jQuery("#ut_sats").hide();
				jQuery("#ut_mixed").hide();
		}
		
	});
	
	// hide./show upload script
	jQuery("#agency").change(function(){
		
		if( jQuery(this).val()!="" && jQuery("#upload_type").val()!="" ){
			jQuery("#import_csv_div").show();
		}else{
			jQuery("#import_csv_div").hide();
		}
		
	});
	
	jQuery("#upload_type").change(function(){
		
		if( jQuery(this).val()!="" && jQuery("#agency").val()!="" ){
			jQuery("#import_csv_div").show();
		}else{
			jQuery("#import_csv_div").hide();
		}
		
	});
	
	jQuery("#form_sales_document").submit(function(e){
		
		var file = jQuery("#file").get(0).files.length;
		if(file==0){
			e.preventDefault();
			alert("Please select csv file to import");
		}
		
	});
	
});
</script>  
</body>
</html>
