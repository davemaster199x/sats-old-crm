<?

$title = "Technicians";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class();

if(isset($_POST)){
	foreach($_POST['techids'] as $k => $agent){
		$position = htmlspecialchars($_POST[$agent.'_0'], ENT_QUOTES);
		$mobile = htmlspecialchars($_POST[$agent.'_1'], ENT_QUOTES);
		$fax = htmlspecialchars($_POST[$agent.'_2'], ENT_QUOTES);
		$other = htmlspecialchars($_POST[$agent.'_3'], ENT_QUOTES);
		$email = htmlspecialchars($_POST[$agent.'_4'], ENT_QUOTES);
		$active = $_POST['active_'.$agent];
		// $fname =  htmlspecialchars($_POST['fname'.agent], ENT_QUOTES);
		// $lname =  htmlspecialchars($_POST['lname'.agent], ENT_QUOTES);
		$electrician =  $_POST['electrician_'.$agent];
		$electrician = (int) $electrician;
		$active = (int) $active;
		
		//$alert_email = htmlspecialchars($_POST['alert_email'], ENT_QUOTES);
		   
		   //$Query = "UPDATE techs SET position='$position', ph_mob1='$mobile', ph_mob2='$fax', ph_home='$other', email='$email', active='$active' WHERE (id=".$agent.");";
		   
		   	/// use this query after transfer on live
		   $Query = "UPDATE techs SET position='$position', ph_mob1='$mobile', ph_mob2='$fax', ph_home='$other', email='$email', active=".$active.", electrician=".$electrician." WHERE (id=".$agent.");";
		 
			$result = mysql_query($Query, $connection);

			if (mysql_affected_rows() > 0){
				$update = '<div class="success">Technicians Details were Updated Successfully.</div>';
			}
			else{
				$update =  "<div class='success'>Update Failed!</div>";
				
			}
	}

}


function get_techs($params){

	$filter = '';
	if( is_numeric($params['deleted']) ){
		$filter .= " AND sa.`Deleted` = {$params['deleted']} ";
	}

	if( is_numeric($params['active']) ){
		$filter .= " AND sa.`active` = {$params['active']} ";
	}
	
	$query = "SELECT 
		sa.FirstName, 
		sa.LastName, 
		sa.sa_position, 
		sa.ContactNumber, 
		sa.Email, 
		sa.active, 
		sa.is_electrician, 
		sa.`dha_card`, 
		sa.`StaffID`,
		sa.`Deleted` 
	FROM `staff_accounts` AS sa
	LEFT JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
	WHERE ca.`country_id` ={$_SESSION['country_default']}
	AND sa.`ClassID` = 6
	{$filter}
	ORDER BY sa.`active` DESC,sa.`FirstName` ASC";

	return mysql_query($query);
	
}

?>


  <div id="mainContent">
  
  <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Technicians" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Technicians</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>


<?php echo isset($update)? $update : '';?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" id="form_agents">
<table border=0 cellspacing=1 cellpadding=5 width=100% class="table-left tbl-fr-red">
<tr bgcolor="#b4151b">
<th>Name</th>
<th>Position</th>
<th>Schedule</th>
<th>Mobile</th>
<th>Email</th>
<th>DHA card</th>
<th>Vehicle</th>
<th>Status</th>
<th>Deleted</th>
</tr>
<?php


	/// use this query after transfer on live
   //$result = mysql_query ("SELECT first_name, last_name, position, ph_mob1, ph_mob2, ph_home, email, id, active, electrician FROM techs WHERE `country_id` = {$_SESSION['country_default']} ORDER BY active DESC, first_name ASC", $connection);
   if( $_GET['all'] != 1 ){
		$params = array(
			'deleted' => 0,
			'active' => 1
		);
		
   }
   $result = get_techs($params);
   
   //$result = mysql_query ("SELECT first_name, last_name, position, ph_mob1, ph_mob2, ph_home, email, id, active FROM techs ORDER BY active DESC, first_name ASC", $connection);
	$odd=0;

   while ($row = mysql_fetch_array($result))
   {
   $odd++;
	$class = $row['active']==0 ? "class='inactive'" : "";
	if (is_odd($odd)) {
		echo "<tr bgcolor=#FFFFFF ".$class.">";		
		} else {
		echo "<tr bgcolor=#eeeeee ".$class.">";
   		}
      echo "\n";

	echo "<td class='row_".$row['StaffID']."name' colspan>".$row['FirstName'] ." ".$row['LastName']."</td>\n";
	echo "<td colspan class='row_".$row['StaffID']."'>".$row['sa_position']."</td>\n";		//Position
	$currentmonth = date("n",time());
	$currentyear = date("Y",time());

	$crm_ci_page = "calendar/monthly_schedule_admin/{$row['StaffID']}";
	$view_tech_url = $crm->crm_ci_redirect($crm_ci_page);

	echo "<td colspan><a href='".$view_tech_url."'>View Schedule</a></td>\n";
	echo "<td class='row_".$row['StaffID']."'>".$row['ContactNumber']."</td>";	//Mobile

	echo "<td class='row_".$row['StaffID']."'>".$row['Email']."</td>";	//Email
	
	// DHA card
	echo "<td>".(($row['dha_card']==1)?'Yes':'No')."</td>";
	

	// vehicle
	$v_sql = mysql_query("
		SELECT *
		FROM `vehicles`
		WHERE `StaffID` = {$row['StaffID']}
		LIMIT 0,1
	");
	
	if(mysql_num_rows($v_sql)>0){
		$v = mysql_fetch_array($v_sql);
		$v_str = '<a href="/view_vehicle_details.php?id='.$v['vehicles_id'].'">'.$v['number_plate'].'</a>';
	}else{
		$v_str = '';
	}

	
	echo "<td>{$v_str}</td>";



	echo "<td class='row_".$row['StaffID']."inactive'>".( ($row['active'] == 1)?'Active':'Inactive' )."</td>";
	echo "<td class='row_".$row['StaffID']."inactive'>".( ($row['Deleted'] == 1)?'Yes':'No' )."</td>";

    echo "</tr>\n";
   }
?>

</table>
</form>

<div class="bottom-btn">
	<a href="/view_techs.php?all=1" id="toggle_active_disp" class="submitbtnImg float-left">Display All Technicians</a>
</div>
   
  </div>

</div>

<br class="clearfloat" />


<script type="text/javascript">
	function EditTech(tech){
		 $('.row_'+tech).each(function(index){
			 var cell = $(this).html();
			 $(this).html('<input type="text" name="'+tech+"_"+index+'" value="'+cell+'" />');
		 });
		$('.row_'+tech+'edit').html('<a href="#" class="updateTb">Update</a><input name="techids[]" value="'+tech+'" style="display:none;">');
		$('.row_'+tech+'elec').html('<select name="electrician_'+tech+'"><option <?php echo $row['electrician'] == true ? 'selected="selected"': ''?> value="1">Yes</option><option <?php echo $row['electrician'] != true ? 'selected="selected"': ''?> value="0">No</option></select>');
		$('.row_'+tech+'active').html('<select name="active_'+tech+'"><option selected="selected" value="1">Active</option><option value="0">Inactive</option></select>');
		$('.row_'+tech+'inactive').html('<select name="active_'+tech+'"><option value="1">Active</option><option selected="selected" value="0">Inactive</option></select>');
		
	}
	
	
	$(function(){
		$( document ).on( "click", "a.updateTb", function() {
			$('#form_agents').submit();
			//alert('Update');
		});
		
		$( document ).on( "click", "a.cancelRow" ,function() {
			var td = $("a.cancelRow").parent();
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
