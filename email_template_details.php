<?php
$title = "Email Template Details";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$logged_user = $_SESSION['USER_DETAILS']['StaffID'];
$et_id = mysql_real_escape_string($_REQUEST['id']);

$et_params = array(
	'email_templates_id' => $et_id,
	'echo_query' => 0 
);	
$et_sql = $crm->getEmailTemplates($et_params);
$et = mysql_fetch_array($et_sql);

?>
<style>
#et_body{
	height: 350px;
	width: 500px;
	margin: 0;
	padding: 8px;
}
#template_name, #subject, #temp_type{
	width: 516px;
}
.et_buttons button{
	float: left; 
	margin-top: 15px;	
}
#btn_submit{
	margin-right: 10px;
}
.addproperty {
    width: 700px;
	float: left;
}
#et_div2{
	width: 200px;
	float: left;
	text-align: left;
}
.tag_div {
    margin-bottom: 4px;
}
.email_variables_btn{
	width: 180px;
}
</style>

    
    <div id="mainContent">
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="Email Templates" href="email_templates.php">Email Templates</a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
      
    
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">New Email Template Created</div>
	<?php
	}
	?>
	
	<?php
	if($_GET['update']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Email Template Updated</div>
	<?php
	}
	?>
      	
	
	<div class="addproperty">
		<form method="POST" id="template_form" action="update_email_template.php">
			<div class="row">
				<label class="addlabel">Template Name</label>
				<input type="text" class="addinput template_name" name="template_name" id="template_name" value="<?php echo $et['template_name']; ?>" />
			</div>
			
			<div class="row">
				<label class="addlabel">Subject</label>
				<input type="text" class="addinput subject" name="subject" id="subject" value="<?php echo $et['subject']; ?>" />
			</div>
			
			<div class="row">
				<label class="addlabel">Type</label>
				<select id="temp_type" name="temp_type" class="addinput temp_type">
					<option value="">--- Select ---</option>
					<?php
					$ett_params = array( 
						'echo_query' => 0,
						'sort_list' => array(
							 array(
								'order_by' => 'et_type.`name`',
								'sort' => 'ASC'
							 )
						),
						'active' => 1
					);	
					$ett_sql = $crm->getEmailTemplateType($ett_params);
					if( mysql_num_rows($ett_sql)>0 ){ 
						while( $ett = mysql_fetch_array($ett_sql) ){ ?>																
							<option value="<?php echo $ett['email_templates_type_id'] ?>" <?php echo ( $ett['email_templates_type_id'] == $et['email_templates_type_id'] )?'selected="selected"':''; ?>><?php echo $ett['name'] ?></option>						
						<?php	
						}	
					}
					?>	
				</select>
			</div>
			
			<div class="row">
				<label class="addlabel">Body </label>
				<textarea name="et_body" id="et_body" class="addtextarea et_body"><?php echo $et['body']; ?></textarea>
			</div>
			
			<div class="row">
				<label class="addlabel">Call Centre</label>			
				<select name="show_to_call_centre" id="show_to_call_centre" class="addinput" style="width:auto;">
					<option value="">----</option>
					<option value="1" <?php echo ( $et['show_to_call_centre']==1 )?'selected="selected"':''; ?>>Yes</option>
					<option value="0" <?php echo ( $et['show_to_call_centre']==0 && is_numeric($et['show_to_call_centre']) )?'selected="selected"':''; ?>>No</option>
				</select>
			</div>
			
			<div class="row">
				<label class="addlabel">Active</label>			
				<select name="active" id="active" class="addinput" style="width:auto;">
					<option value="">----</option>
					<option value="1" <?php echo ( $et['et_active']==1 )?'selected="selected"':''; ?>>Yes</option>
					<option value="0" <?php echo ( $et['et_active']==0 && is_numeric($et['et_active']) )?'selected="selected"':''; ?>>No</option>
				</select>
			</div>
			
			<div class="row et_buttons">
				<label class="addlabel">&nbsp;</label>
				<input type="hidden" id="email_var_target" />
				<input type="hidden" id="et_id" name="et_id" value="<?php echo $et['email_templates_id']; ?>" />
				<button class="submitbtnImg" id="btn_submit" type="submit">
					<img class="inner_icon" src="images/save-button.png">
					Update
				</button>
				<button class="submitbtnImg  blue-btn" id="btn_clear" type="button">
					<img class="inner_icon" src="images/cancel-button.png">
					Clear
				</button>
			</div>
			
			
		</form>
	</div>
		
	<div id="et_div2" style="width: 200px; float:left;">
		<?php
		$et_params = array( 
			'echo_query' => 0,
			'sort_list' => array(
				 array(
					'order_by' => 'ett.`tag_name`',
					'sort' => 'ASC'
				 )
			),
			'active' => 1
		);		
		$temp_tag_sql = $crm->getEmailTemplateTag($et_params);
		if( mysql_num_rows($temp_tag_sql)>0 ){ 

			while( $temp_tag = mysql_fetch_array($temp_tag_sql) ){ ?>
						
				
				<div class="tag_div">
					<button id="temp_tag_btn<?php echo $temp_tag['email_templates_tag_id'] ?>" class="submitbtnImg blue-btn email_variables_btn" id="btn_tag" type="button" title="<?php echo $temp_tag['tag'] ?>"><?php echo $temp_tag['tag_name'] ?></button>
				</div>
			
			<?php	
			}
		
		
		}
		?>		

	</div>
    
  </div>

<br class="clearfloat" />



<script>
function typeInTextarea(el, newText) {
  // starting text highlight position
  var start = el.prop("selectionStart");
  //console.log("selection start: "+start+" \n");
  // end text highlight position
  var end = el.prop("selectionEnd");
  //console.log("selection start: "+end+" \n");
  // text area original text
  var text = el.val();
  // before text of the inserted location
  var before = text.substring(0, start);
  // after text of the inserted location
  var after  = text.substring(end, text.length);
  // combine texts
  el.val(before + newText + after);
  // put text cursor at the end of the insertd tag
  el[0].selectionStart = el[0].selectionEnd = start + newText.length;
  // displat text cursor
  el.focus();
}

jQuery(document).ready(function(){
	
	jQuery(".email_variables_btn").on("click", function() {
	  var tag = jQuery(this).attr("title");
	  //var target =  jQuery(':focus');
	  //console.log(target.val());
	  var target = jQuery("#email_var_target").val();
	  typeInTextarea(jQuery(target), tag);
	 // console.log(tag);
	  return false;
	});
	
	jQuery("#subject").click(function(){
		jQuery("#email_var_target").val("input#subject");
	});
	
	jQuery("#et_body").click(function(){
		jQuery("#email_var_target").val("textarea#et_body");
	});
	
	// clear
	jQuery("#btn_clear").click(function(){
		
		jQuery("#template_name, #subject, #et_body").val("");
		
	});
	
	jQuery("#template_form").submit(function(){
		
		var template_name = jQuery("#template_name").val();
		var subject = jQuery("#subject").val();
		var et_body = jQuery("#et_body").val();
		var error = "";

		if(template_name==""){
			error += "Template Name is Required \n";
		}
		
		if(subject==""){
			error += "Subject is Required \n";
		}
		
		if(et_body==""){
			error += "Email Body is Required \n";
		}
		
		if(error!=""){
			alert(error);
			return false;
		}else{
			return true;
		}
		
	});

	
});
</script>
</body>
</html>
