<?

$title = "VieW Jobs";
$onload = 1;
$onload_txt = "zxcSelectSort('agency',1)";

include('inc/init.php');


# additional check for OFFICE users - they are not allowed to view the status' per $office_job_disallowed in config.php

if($_SESSION['USER_DETAILS']['ClassID'] == 4 && isset($_GET['status']))
{
	if(in_array($_GET['status'], $office_job_disallowed))
	{
		header("location: " . URL . "main.php?restricted=1");
		exit();
	}
}


include('inc/header_html.php');
include('inc/menu.php');

$istatus = "";
$_status = isset($_GET['status']);
$_filterdate = isset($_GET['filterdate']);
$_filterbyagency = isset($_GET['agency']);
$_searchsuburb = isset($_GET['searchsuburb']);

$status = ($_status) ? $_GET['status'] : "";
$filterdate = ($_filterdate) ? $_GET['filterdate'] : "";
$filterbyagency = ($_filterbyagency) ? $_GET['agency'] : "";
$searchsuburb = ($_searchsuburb) ? $_GET['searchsuburb'] : "";

if ($status=="") { $status = "All"; }

	switch($status) {
		case "tobebooked":
			$istatus= "To Be Booked";
			break;
		case "sendletters":
			$istatus = "Send Letters";
			break;
		case "booked":
			$istatus = "Booked";
			break;
		case "completed":
			$istatus = "Completed";
			break;
		case "cancelled":
			$istatus = "Cancelled";
			break;
		case "completed":
			$istatus = "Completed";
			break;
		case "precompleted":
			$istatus = "Pre Completion";
			break;	
		case "merged":
			$istatus = "Merged Certificates";
			break;
        case "fixreplace":
            $istatus = "Fix or Replace";
            break;
        case "pending":
            $istatus = "Pending";
            break;			
		case "":
			$istatus = "";
			break;
	}


if(isset($_GET['sendemails']) && $_GET['sendemails'] == "yes")
{
	$num_emails_sent = batchSendInvoicesCertificates();
}	

?>
  <div id="mainContent">

<h1 class='style4'>View Jobs - <?php echo $istatus;?></h1>


<h5 class="style3">
<hr noshade="noshade" size=1 width=700 />
<ol class="job_steps">
<?php
 echo "<li><a href='" . URL . "view_jobs_export.php?status=$status&filterdate=$filterdate&agencyid=$filterbyagency'>Export These Jobs</a></li>";
//echo "<a href='" . URL . "view_jobs_export3.php?status=$status&filterdate=$filterdate&agencyid=$filterbyagency'>Export These Jobs ver3</a>";


// echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='" . URL . "book_all_jobs.php'>Book All Jobs</a>";

if ($status == "merged")
{
	$email_stats_query = "(
		SELECT 'sent' as result_type, COUNT(j.id) AS result
		FROM jobs j, property p, agency a 
		WHERE j.property_id = p.property_id 
		AND j.status = '$istatus'".$filter."
		AND  p.agency_id = a.agency_id		
		AND a.account_emails LIKE '%@%'
		AND j.client_emailed IS NOT NULL
		" . $user->prepareStateString('AND', 'p.') . "
	)
	
	UNION ALL
	(
		SELECT 'total', COUNT(j.id) AS result
		FROM jobs j, property p, agency a 
		WHERE j.property_id = p.property_id 
		AND j.status = '$istatus'".$filter."
		AND  p.agency_id = a.agency_id		
		AND a.account_emails LIKE '%@%'
		" . $user->prepareStateString('AND', 'p.') . "
	)";

	$email_stats = mysqlMultiRows($email_stats_query);				
	
	//echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='" . URL . "mark_invoiced_jobs_completed.php'>Mark All as Completed</a>";
	
	echo "<li><a href='" . URL . "batch_view_certificate.php' target='_blank'>Batch Print Certificates</a></li>";
	echo "<li><a href='" . URL . "batch_view_invoices.php' target='_blank'>Batch Print Invoices</a></li>";
	echo "<li><a href='" . URL . "view_jobs.php?status=merged&sendemails=yes' onclick=\"return confirm('Are you sure you want to email the invoices / certificates?');\">Batch Email Certificate / Invoice</a> <span class='green'>(" . $email_stats[0]['result'] . "/" . $email_stats[1]['result'] . " sent)</span></li>";
	echo "<li><a href='" . URL . "export_myob.php'>Export Myob</a></li>";
	echo "<li><a href='" . URL . "mark_completed.php' onclick=\"return confirm('Are you sure you want to mark all jobs as completed?');\">Mark all Merged as Completed</a></li>";
    }

if ($status == "sendletters")
	{
	echo "<li><a href='" . URL . "letter_sent_jobs.php'>Mark All as Letter Sent</a></li>";
    }

//echo "<form method=get name='example' id='example' action='/view_jobs.php?status=$status'>\n";
//echo "<input type='hidden' name='status' value='$status'>\n";
?>
</ol>
<form method=get name='example' id='example' action='<?=URL;?>view_jobs.php?status=<?php echo $status ?>'>
<input type='hidden' name='status' value='<?php echo $status ?>'>

<table border=1 cellpadding=0 cellspacing=0 width="740">
<tr><td>

<table border=0 cellspacing=0 cellpadding=5 width=100%>
<!-- tr bgcolor="#DDDDDD" id='test'><td>123</td></tr -->

<?php
$printfilterdate = "";
$agencyname = "";

// dipaloticus
echo "<tr>\n";
//echo "<td>&nbsp;</td>\n";

if($status != "tobebooked" && $status != "pending"){
echo "
<td align='right'>Filter by date: &nbsp;<input type=label name='filterdate'></td>
<td><A HREF=\"#\" onClick=\"cal.select(document.forms['example'].filterdate,'anchor1','yyyy-MM-dd'); return false;\" MAXLENGTH=19 NAME=\"anchor1\" ID=\"anchor1\"><img src='images/cal.gif' border=0></a></td>
<td width='10'><input type='submit' value='Filter!'></td></tr>\n";
}
else{

echo "<td width='300' align=''>Search by Suburb: </td>
<td><input class='searchstyle' type=text name='searchsuburb' size=10/></td>
<td><input type='submit' value='go' onClick=''></td>
<td width='300' align='right'>filter by Agency: </td>
<td align='right'>
<select name='agency' id='agency'><option value='Any'>Any</option>";



   // (2) Run the query on the winestore through the
   //  connection
   $result = mysql_query ("SELECT agency_id, agency_name, address_3 FROM agency", $connection);
   $odd=0;

   // (3) While there are still rows in the result set,
   // fetch the current row into the array $row
   while ($row = mysql_fetch_row($result))
   {
     // (4) Print out each element in $row, that is,
     // print the values of the attributes

		echo "<option value='" . $row[0] . "'>";		
		echo $row[1];
		echo "</option>\n";

      // Print a carriage return to neaten the output
      echo "\n";
   }
     
     
echo "</select></td><td><input type='submit' value='Filter!' onClick='recValue()'></td></tr></table>\n";
}
echo "</form>";


echo "<table border=0 cellspacing=0 cellpadding=5 width=100%>
<tr bgcolor='#DDDDDD'>";

echo "<td><b>Job Type</b></td>";
if($status != "tobebooked"){
	$dateheading = ($status == "pending") ? "Retest Date" : "Date";
	echo "<td align='center'><b>$dateheading</b></td>";
}
echo "
<td><b>Status</b></td>
<td colspan=1><b>Address</b></td>
<td colspan=1><b>State</b></td>
<td>Agency</td>
<td>Job details</td>
";

//put checkbox if status is pending only
if($status == "pending"){
	echo "<td>Select Job</td>";
}

// Add email status for merged step
if($status == "merged"){
	echo "<td>Email Sent</td>";
}

echo "</tr>\n";

   // (1) Open the database connection
   


$filter = "";		//filter for date
$afilter = "";		//filter for agency
$acnt=0;			//keep count of filter result by agency 

if ($filterdate != "")
{
	$filter = " AND j.date='$filterdate'";
}
//elseif ($filterbyagency != "")
//if ($filterbyagency != "" && $filterbyagency != "Any")
if ($filterbyagency != "")
{
	$afilter = "$filterbyagency";
}

	
if ($istatus == "")
{
	$Query = "SELECT j.job_type, DATE_FORMAT(j.date,'%d/%m/%Y'), j.status, p.address_1, p.address_2, p.address_3, p.state, p.postcode, j.id, p.property_id FROM jobs j, property p WHERE (j.property_id = p.property_id".$filter.") " . $user->prepareStateString('AND', 'p.') . " ORDER BY j.job_type, p.address_3;";
}
elseif ($istatus == "To Be Booked")
{


	$Query = "SELECT j.job_type, DATE_FORMAT(j.date,'%d/%m/%Y'), j.status, p.address_1, p.address_2, p.address_3, p.state, p.postcode, j.id, p.property_id FROM jobs j, property p WHERE (j.property_id = p.property_id AND j.status = 'To Be Booked'".$filter.") " . $user->prepareStateString('AND', 'p.') . " ORDER BY j.job_type, p.address_3;";
	//echo "Query is: ".$Query."<br><br>";

 if($searchsuburb != ""){
 	$Query = "SELECT j.job_type, DATE_FORMAT(j.date,'%d/%m/%Y'), j.status, p.address_1, p.address_2, p.address_3, p.state, p.postcode, j.id, p.property_id FROM jobs j, property p WHERE (j.property_id = p.property_id AND j.status = 'To Be Booked'".$filter." AND p.address_3 LIKE '%$searchsuburb%') " . $user->prepareStateString('AND', 'p.') . " ORDER BY j.job_type, p.address_3;";
	
	//LIKE '%$searchsuburb%' AND p.deleted=0);";
 }
}
elseif ($istatus == "Pending")
{
	$Query = "SELECT j.job_type, DATE_FORMAT(j.date,'%d/%m/%Y'), j.status, p.address_1, p.address_2, p.address_3, p.state, p.postcode, j.id, p.property_id FROM jobs j, property p WHERE (j.property_id = p.property_id AND j.status = '$istatus'".$filter.") " . $user->prepareStateString('AND', 'p.') . " ORDER BY j.job_type, p.address_3;";
}
else
{
	$Query = "SELECT j.job_type, DATE_FORMAT(j.date,'%d/%m/%Y'), j.status, p.address_1, p.address_2, p.address_3, p.state, p.postcode, j.id, p.property_id, a.send_emails, a.account_emails, j.client_emailed FROM jobs j, property p, agency a WHERE a.agency_id = p.agency_id AND (j.property_id = p.property_id AND j.status = '$istatus'".$filter.") " . $user->prepareStateString('AND', 'p.') . " ORDER BY j.job_type, p.address_3;";
}

	// Prepare Search Params for query
	
	$query_params = array(
	'status' => $istatus,
	'filter' => $filter,
	'search_suburb' => $searchsuburb
	);
	
	$job_list = getJobList($query_params);


	
   // (2) Run the query.	
   $result = mysql_query ($Query, $connection);
   if (mysql_num_rows($result) == 0) {
   	echo "<br><br>No Jobs to display of Status: $istatus.<br><br><br>\n";
	}
	$odd=0;
	$agencyname[0] = "";

   // (3) While there are still rows in the result set,
   // fetch the current row into the array $row
   while ($row = mysql_fetch_array($result))
   {
   //Run Sub-query to select agency name
   if($istatus != "To Be Booked" && $istatus != "Pending"){
      $selectQuery = "SELECT a.agency_name FROM property p, agency a WHERE (p.agency_id = a.agency_id AND p.property_id = '$row[9]');";
   }else{
      $selectQuery = "SELECT a.agency_name, a.agency_id, p.retest_date FROM property p, agency a WHERE (p.agency_id = a.agency_id AND p.property_id = '$row[9]');";
   }
  
    
   $res = mysql_query ($selectQuery, $connection);
   
   if (mysql_num_rows($res) > 0) {
   	   $agencyname = mysql_fetch_row($res);
   }else{
   		$agencyname[0] = "No Agency found";
   }

   $odd++;
	if (is_odd($odd)) {
		echo "<tr id='td$row[8]' bgcolor=#efebef>";
	
	} else {
		echo "<tr id='td$row[8]' bgcolor='#ffffff'>";

   	}
		
      echo "\n";

		$row[1] = "$row[1]\n";
	
	 // Make highlight if Alarm is LI type
	 $bgcolor = '';
	 $LI='';
     if ($row[0] == "Change of Tenancy"){
		   if($status == "tobebooked"){
			   $query = "SELECT a1_pwr, a2_pwr, a3_pwr, a4_pwr, a5_pwr, a6_pwr FROM property p WHERE (property_id = '$row[9]');";
	
			   $res = mysql_query ($query, $connection);
			   if (mysql_num_rows($res) > 0) {
				   $alarm = mysql_fetch_row($res);
			   }
			   if(chkAlarmtype_isLI($alarm)){ $LI="(LI)"; $bgcolor = 'yellow'; }
		   }

		   	$row[0] = "<span class='style14' style='background-color: $bgcolor'>Change of Tenancy $LI</span>";
        	//$row[0] = "<p class=style14>Change of Tenancy</p>";
     }
	 
	 // Put form for submit buttons if job status is pending
		echo "<form method='POST' name='updateform' id='updateform' action='" . URL . "update_pending_jobs.php'>";
	    
		if($status == "tobebooked" || $status == "pending"){
			$retest_date = ($status == "pending") ? "<td>$agencyname[2]</td>\n" : '';	//$row[1];
			$checkbox = ($status == "pending") ? "<td><input type='checkbox' name='chkbox[]' id='$row[8]' value='$row[8]' onClick='javascript:highlight(this.id, ".is_odd($odd).")'></td>\n" : '';

			if($agencyname[1] == $afilter){
			 //echo "<td>$row[0]</td>\n<td>$row[2]</td>\n<td><a href='" . URL . "view_property_details.php?id=$row[9]'>$row[3]  $row[4], $row[5]</a></td>\n<td>$agencyname[0] $agencyname[1]</td>\n";
			 echo "<td>$row[0]</td>\n".$retest_date."<td>$row[2]</td>\n<td><a href='" . URL . "view_property_details.php?id=$row[9]'>$row[3]  $row[4], $row[5]</a></td><td>" . $row[6]. "</td>\n<td>$agencyname[0]</td>\n";
			 echo "<td><a href='" . URL . "view_job_details.php?id=$row[8]'>Details</a></td>$checkbox";
			 $acnt++;
			}
			elseif($filterbyagency == "" || $filterbyagency == "Any" || $searchsuburb != ""){
			 echo "<td>$row[0]</td>\n$retest_date<td>$row[2]</td>\n<td><a href='" . URL . "view_property_details.php?id=$row[9]'>$row[3]  $row[4], $row[5]</a></td><td>" . $row[6]. "</td><td>$agencyname[0]</td>\n";
			 echo "<td><a href='" . URL . "view_job_details.php?id=$row[8]'>Details</a></td>$checkbox";		
			}
		}
		else{
			echo "<td>$row[0]</td>\n<td>$row[1]</td>\n<td>$row[2]</td>\n<td><a href='" . URL . "view_property_details.php?id=$row[9]'>$row[3]  $row[4], $row[5]</a></td><td>" . $row[6]. "</td><td>$agencyname[0]</td>\n";
			echo "<td><a href='" . URL . "view_job_details.php?id=$row[8]'>Details</a></td>\n";
			//echo "</td>\n<td>$row[1]</td>\n<td>$row[2]</td>\n<td><a href='" . URL . "view_property_details.php?id=$row[9]'>$row[3]  $row[4], $row[5]</a></td>\n";
		}
		//echo "<td colspan=2><a href='" . URL . "view_job_details.php?id=$row[8]'>Details</a></td>\n";
		
		if($status == "merged")
		{
			echo "<td>";
			
			if(stristr($row['account_emails'], "@"))
			{
				if(isset($row['client_emailed']))
				{
					echo "<span class='green'>yes</span>";
				}
				else
				{
					echo "<span class='red'>no</span>";
				}
			}
			else
			{
				echo "n/a";
			}

			
			echo "</td>";
		}
		echo "</tr>";
	
      // Print a carriage return to neaten the output
      echo "\n";
   }
   //message display when No result return from filter
   if($acnt == 0 && $afilter != "" && $afilter != "Any"){
		$name = isset($_COOKIE['agencyname']) ? $_COOKIE['agencyname'] : "this agency";
		echo "<tr><td class='style14'>No results found for ".$name.".</td></tr>\n";
		//echo "<br><br>No result found for ".$name.".<br><br><br>\n";
   }
    

?>
</table>
<hr noshade="noshade" size=1 />

<?php
	if($status == "pending"){
		 echo "<div style='text-align: right; margin-right: 8px'> 
		  <input type='hidden' name='status' value='$status'>
		  <input type='submit' name='submit' value='Create job'>
		  <input type='submit' name='submit' value='Delete job'>
		  <input type='submit' name='submit' value='Delete Property'>
		 </div>"; 
	 }
?>
</form>
</td></tr></table>

</h5>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>
      <!-- end #mainContent -->
    </p>
    <div class="clearfloat"></div>
  </div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats -->
    <br class="clearfloat" />
  <div id="footer">
    <p class="style13">Logged in to SATs CRM</p>
    <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
