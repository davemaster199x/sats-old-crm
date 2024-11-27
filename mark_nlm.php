<?php
$start = microtime(true);
$page_url = $_SERVER['REQUEST_URI'];

if( $_REQUEST['in_crm_only'] == 1 ){
	$title = "In CRM Only";
}else if( $_REQUEST['inactive_in_pm'] == 1 ){
	$title = "Inactive in PM";
}


include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$propertyme = new Propertyme_api;
$crm = new Sats_Crm_Class;


$agencies = $propertyme->getAgencies();



// NLM
if( $_POST['btnsave'] == 'Mark as NLM' ){	
	
	$property_id_arr = $_POST['chkProperty']; 	
	
	//print_r($property_id_arr);
	
	// property has active jobs array
	$prop_haj_arr = [];
	$prop_success_nlm_arr = [];
	
	if( count($property_id_arr)>0  ){
		
		foreach( $property_id_arr as $property_id ){
			
			$nlm_ret = $crm->NLM_Property($property_id);
			$nlm_json = json_decode($nlm_ret);
			
			//print_r($nlm_ret);
			
			if( $nlm_json->nlm_chk_flag == 1 ){ // has active job
				$prop_haj_arr[] = $property_id;
			}else{
				$prop_success_nlm_arr[] = $property_id;
			}
			
		}
		
	}
	
}



if(isset($_GET['agency_id'])){
	
	//$agency_id = explode("**", $_GET['agency_id'])[0];
	//$agency_name = explode("**", $_GET['agency_id'])[1];
	
	$pm_agency_id = mysql_real_escape_string($_REQUEST['agency_id']);
	$agency_id = $pm_agency_id;
	$agency_name = $propertyme->getAgencyName($pm_agency_id);
	
	
	// in PM ONLY
	if( $_REQUEST['in_crm_only'] == 1 ){

		$crmSql = "
		SELECT p.`property_id`, p.`address_1`,p.`address_2`,p.`address_3`,p.`state`,p.`postcode`,p.`deleted`,p.`propertyme_prop_id`
		FROM `property` AS p
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`propertyme_agency_id` = '".$agency_id."'
		AND p.`deleted` = 0
		AND (
			p.`propertyme_prop_id` IS NULL OR
			p.`propertyme_prop_id` = ''
		)
		"; 
		$crmQuery = mysql_query($crmSql);
	
	
	}else if( $_REQUEST['inactive_in_pm'] == 1 ){ // INACTIVE in PM
	
		// get inactive PM prop
		$res = $propertyme->getAgencyDetails($agency_id);
		$props = $propertyme->getAllProperties(FALSE)['Rows'];
		
		$inActivePmPropID = [];
		foreach($props as $prop){
						
			if( $prop['ArchivedOn'] !='' ){ // inactive		
				$inActivePmPropID[] = "'{$prop['Id']}'";						
			}				
			
		}	

		$imp_inActivePmPropID = implode(",",$inActivePmPropID);
		//print_r($imp_inActivePmPropID);
		
		$crmSql = "
		SELECT p.`property_id`, p.`address_1`,p.`address_2`,p.`address_3`,p.`state`,p.`postcode`,p.`deleted`,p.`propertyme_prop_id`
		FROM `property` AS p 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`propertyme_agency_id` = '{$agency_id}'
		AND p.`deleted` = 0
		AND p.`propertyme_prop_id` != ''
		AND p.`propertyme_prop_id` IN({$imp_inActivePmPropID})
		";
		$crmQuery = mysql_query($crmSql);	
	
	}
	
	
}


function getAllActiveJobsofProperty($property_id){
	
	$sql_str = "
	SELECT 
		j.`id` AS jid,  
		j.`status`
	FROM `jobs` AS j
	WHERE j.`property_id` = {$property_id}
	AND j.`del_job` = 0
	AND (
		j.`status` = 'Booked' OR
		j.`status` = 'Pre Completion' OR
		j.`status` = 'Merged Certificates' 
	)
	";	
	return mysql_query($sql_str);
	
}

?>
<style type="text/css">
#load-screen{
	display: block;
}
#properties_table{
	margin: 20px 0;
}
#properties_table th,
#properties_table td{
	text-align: left;
}
.save_match_btn{
	display: none;
}
.active_jobs_msg{
	text-align:left;
	color:red; 
	display: none;
}
.mark_as_nlm_btn{
	display: none;
}
.job_status_ul{
	list-style-type:none; 
	margin: 0; 
	padding: 0;
}
</style>
<div id="mainContent">


	<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="PM Agencies" href="pm_agencies.php">PM Agencies</a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $page_url; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
	
	
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	<div class="sats-middle-cont">


	<h2 class="heading"><?=$agency_name?></h2>
	
	
	
	<!-- BEGIN IN CRM ONLY -->


			
			
	<p style="text-align:left;">Properties under <strong><?=$agency_name?></strong> Agency that are in <strong>CRM ONLY</strong></p>
	<p class="heading active_jobs_msg">*Highlighted properties have active jobs. Please cancel the jobs before you mark NLM</p>
	
	

	<br /><br />
	<?php 
	if(isset($_GET['nlm']) AND $_GET['nlm'] == 1){
		echo '<div class="alert alert-warning">Properties has been marked to NLM.</div>';
	}
	?>
	<?php if(mysql_num_rows($crmQuery) > 0){?>
	<div id="nlm-message"></div>
	<input type="hidden" id="num_error">
	<form method="post" id="formcrm" style="text-align: left;">
  
	<button type="button" class="submitbtnImg blue-btn mark_as_nlm_btn">
		<span class="inner_icon_span">Mark as NLM</span>
	</button>
  
	<table id="properties_table" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd">
		<thead>
			<tr class="toprow jalign_left">
				<th><input type="checkbox" id="select-all"></th>
				<th>ID in CRM</th>
				<th>Address in CRM</th>
				<th>Job Status</th>
				<th>Status in CRM</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		while($rowCRM = mysql_fetch_array($crmQuery)){
			$addressText = $rowCRM['address_1']." ".$rowCRM['address_2']." ".$rowCRM['address_3']." ".$rowCRM['state']." ".$rowCRM['postcode'];
		?>
			<tr id="addressrow<?=$count?>" style="text-align:left !important;">
				<td align="center"><input type="checkbox" class="prop_id_chk" name="chkProperty[]" value="<?=$rowCRM['property_id']?>" class="chkaddress"></td>
				<td><?=$rowCRM['property_id']?></td>
				<td>
					<a href="view_property_details.php?id=<?php echo $rowCRM['property_id']; ?>">
						<?=$addressText?>
					</a>
				</td>
				<td>
				<?php
				
			
				
				if( $crm->NLMjobStatusCheck($rowCRM['property_id']) == true ){ // has active job
					
					$active_jobs_sql = getAllActiveJobsofProperty($rowCRM['property_id']);
					if( mysql_num_rows($active_jobs_sql) > 0 ){ ?>
						<ul class="job_status_ul">
						<?php
						while( $aj = mysql_fetch_array($active_jobs_sql) ){							
						?>
							<li><?php echo $aj['status']; ?></li>
						<?php	
						}
						?>
						</ul>
					<?php
					}							
					
				}
				
												
				?>
				</td>
				<td>
					<?php echo ( $rowCRM['deleted'] == 1 )?'<span class="jInActiveStatus">Inactive</span>':'<span class="jActiveStatus">Active</span>'; ?>								
				</td>
			</tr>
		<?php }?>
		</tbody>
	</table>
	
	<input type="hidden" name="btnsave" value="Mark as NLM" />
	<button type="button" class="submitbtnImg blue-btn mark_as_nlm_btn">
		<span class="inner_icon_span">Mark as NLM</span>
	</button>
	
	</form>
	<?php } else { ?>
	No properties found.
	<?php }?>
			
	<!-- BEGIN IN CRM ONLY -->


</div>
</div>


<!-- BEGIN MODAL -->
<div id="responsive" class="modal fade bs-modal-lg" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false"></div>
<!-- END MODAL -->


<script type="text/javascript">

function showButtonIfTicked(){
	
	
	var num_checked = jQuery(".prop_id_chk:checked").length;
		
	if( num_checked > 0 ){
		//return true;
		jQuery(".mark_as_nlm_btn").show();
	}else{
		//return false;	
		jQuery(".mark_as_nlm_btn").hide();
	}
	
}


$(document).ready( function () {
	
	
	jQuery("#load-screen").hide();
	
	
	jQuery(".mark_as_nlm_btn").click(function(){
		
		if( confirm("Are you sure you want to continue?") ){
			jQuery("#formcrm").submit();
		}
		
	});
	
	
	jQuery(".prop_id_chk").change(function(){	
		showButtonIfTicked();
	});
	
	
	
	
	jQuery(".job_status_ul").each(function(){
		
		jQuery(".active_jobs_msg").show();
		jQuery(this).parents("tr:first").find(".prop_id_chk").hide();
		jQuery(this).parents("tr:first").addClass('jredHighlightRow');
		
	});

	
    $('#select-all').click(function(event) {   
	    if(this.checked) {
	        // Iterate each checkbox
	        $('.prop_id_chk:visible').each(function() {
	            this.checked = true;                        
	        });
	    } else {
	        $('.prop_id_chk:visible').each(function() {
	            this.checked = false;                       
	        });
	    }
	    showButtonIfTicked();
	});
	


	


} );
</script>
</body>
</html>
<?php 
$time_elapsed_secs = microtime(true) - $start;
echo "Execution Time: {$time_elapsed_secs }";
 ?>