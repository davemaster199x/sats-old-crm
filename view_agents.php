<?php

$title = "Agents";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

if($_POST)
{
	 if(isset($_POST['agentid'])){
		 $agent_id = '';
		 foreach($_POST['agentid'] as $k => $agent){
			 $agent_id = $agent;
			 $firstname = $_POST[$agent.'_0'];
			 $lastname = $_POST[$agent.'_1'];
			 $phonework = $_POST[$agent.'_2'];
			 $phonemob = $_POST[$agent.'_3'];
			 $email = $_POST[$agent.'_4'];
			 //$state = $_POST[$agent.'_5'];
			 $agency = $_POST[$agent.'_agency'];
			 
			 $insetQuery = "UPDATE contacts SET 
							first_name='". $firstname ."',
							last_name='". $lastname ."',
							phone_work='". $phonework ."',
							phone_home='". $email ."',
							phone_mob='". $phonemob ."',
							agency_id=".$agency."
							 WHERE contact_id=". $agent_id .";";

			 if ((@ mysql_query ($insetQuery, $connection)) && @ mysql_affected_rows() == 1)
				$update = '<div class="success">Agent Details Updated.</div>';
				/*echo "<h3>Calendar entry successfully updated</h3>\n<br>\n";*/
			 else
				$update = '<div class="success">A fatal error occurred whilst trying to update the entry {'.$agent_id.'}</div>';
				//echo "<div>A fatal error occurred whilst trying to update the entry {".$agent_id."}</div>" . $insertQuery;   
			
		 }
		 
	 }	
}

?>
  <div id="mainContent">
  
  <div class="sats-middle-cont">
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Agents" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Agents</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>


<?php echo isset($update)? $update : '';?>
<form method="post" action="view_agents.php" id="form_agents">
<table border=0 cellspacing=1 cellpadding=5 width=100% class="table-left tbl-fr-red">
<tr bgcolor="#b4151b">
<th>First Name</th>
<th>Last Name</th>
<th>Office</th>
<th>Mobile</th>
<th>Email</th>
<th>Agency</th>
<th>Edit</th>


 
<?php
		
   //$result = mysql_query ("SELECT first_name, last_name, phone_work, phone_home, phone_mob, state, contact_id FROM contacts " . $user->prepareStateString('WHERE') . " ORDER BY first_name ASC; ", $connection);
	$result = mysql_query ("SELECT c.first_name, c.last_name, c.phone_work, c.phone_home, c.phone_mob, c.state, c.contact_id, a.agency_name, a.agency_id FROM contacts c, agency a WHERE c.agency_id = a.agency_id AND a.`country_id` = {$_SESSION['country_default']} ORDER BY first_name ASC; ", $connection);
	$odd=0;
   while ($row = mysql_fetch_row($result))
   {
	$odd++;
	if (is_odd($odd)) {
		echo "<tr class='bg-white'>";		
		} else {
		echo "<tr class='bg-grey-light'>";
   		}
		
      echo "\n";
        echo "<td class='row_".$row[6]."'>" . $row[0] . "</td>";
		echo "<td class='row_".$row[6]."'>" . $row[1] . "</td>";
		echo "<td class='row_".$row[6]."'>" . $row[2] . "</td>";
		echo "<td class='row_".$row[6]."'>" . $row[4] . "</td>";
		echo "<td class='row_".$row[6]."'>" . $row[3] . "</td>";
		//echo "<td>" . $row[5] . "</td>";
		$ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row[8]}");
		echo "<td class='row_".$row[6]."_".$row[8]."'><a href='".$ci_link."'>" . $row[7] . "</a></td>";
		echo "<td class='row_".$row[6]."edit'>";		
		echo "<a href='#' onclick='EditAgent(".$row[6].", ".$row[8].");return false;'>Edit</a>";
		echo "<a href='" . URL . "delete_agent.php?id=". $row[6] ."' style='display:none' class='del_link_".$row[6]." del_link'><button type='button' class='blue-btn submitbtnImg' style='display: inline-block;'>Delete</button></a>";
		echo "</td>";


		

      // Print a carriage return to neaten the output

      echo "\n";
   }
   // (5) Close the database connection
   

?>


</table>
</form>

  </div>
  
    </div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />
  
<!-- end #container --></div>
<script type="text/javascript">
	function EditAgent(agent, agency){
		 
		 $('.row_'+agent).each(function(index){
			 var cell = $(this).html();
			 $(this).html('<input type="text" name="'+agent+"_"+index+'" value="'+cell+'" style="width:98%" >');
		 });
		$('.row_'+agent+'edit').html('<button type="button" class="blue-btn submitbtnImg updateTb" style="display: inline-block;">Update</button>  <button type="button" class="submitbtnImg cancelRow" style="display: inline-block;">Cancel</button><input name="agentid[]" value="'+agent+'" style="display:none;"> <a href="/delete_agent.php?id='+agent+'" style="display:none" class="del_link_'+agent+' del_link"><button type="button" class="blue-btn submitbtnImg" style="display: inline-block;">Delete</button></a>');
		setAgencyDropdown('.row_'+agent+'_'+agency, agency, agent);
		$('.del_link_'+agent).show();
	}
	
	function setAgencyDropdown(row, agency, agent){
		<?php
			//include('inc/init.php');
			$htmlsel = '';
			$htmlsel .='<select style="width: 242px;">';
			$result = mysql_query ("SELECT a.agency_id, a.agency_name FROM agency a ORDER BY a.agency_name ASC; ", $connection);
			while ($row = mysql_fetch_array($result))
			{
				$htmlsel .='<option value="'.$row[0].'">'.addslashes($row[1]).'</option>';
			}
			$htmlsel .= '</select>';
		?>
		$(row +'> a').hide();
		$(row).append('<?php echo $htmlsel;?>');
		$(row +'> select').attr('name', agent+'_agency');
		$(row +'> select > option[value='+agency+']').attr('selected', 'selected');
	};
	
	$(function(){
		$( document ).on( "click", ".updateTb", function() {
			$('#form_agents').submit();
			//alert('Update');
		});
		
		$( document ).on( "click", ".cancelRow" ,function() {
			jQuery(".del_link").hide();
			var td = $(".cancelRow").parent();
			var agent_row = $(td).attr('class');
			agent_row = agent_row.replace('edit', '');
			$('.'+ agent_row).each(function(index){
				var val = $('.'+ agent_row + "> input").val();
				$(this).html(val);
			});
			var agent_id = agent_row.replace('row_', '');
			var agency_row = $('td[class*="row_'+agent_id+'_"]').attr('class');
			agency = agency_row.replace('row_'+agent_id+'_', '');
			$(td).html("<a href='#' onclick='EditAgent("+agent_id+", "+agency+");return false;'>Edit</a>");
			//$(agency_row).html("<a href='"+<?php echo URL; ?>+"view_agency_details.php?id="+agency+"'>"+$(agency_row+ '> select >option[selected=selected]').text()+"</a>");
			$('td[class*="row_'+agent_id+'_"] > select').remove();
			$('td[class*="row_'+agent_id+'_"] > a').show();
			
		});
	});
</script>
</body>
</html>
