<?php

include('server_hardcoded_values.php');
include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');

define("IS_CRON", 1);
define("CRON_TYPE_ID", 9);
define("CURR_WEEK", intval(date('W')));
define("CURR_YEAR", date('Y'));

echo "<h1>Cron Flush Voice</h1>";

$scan_path = "{$_SERVER['DOCUMENT_ROOT']}voice/HOSTED_STUFF/rec";

echo "<p>Voice Path: {$scan_path}</p>";

// scan folders
$folders_arr = array_diff(scandir($scan_path,1), array('..', '.'));

echo "<p>Voice Array:<br />";
echo "<pre>";
print_r($folders_arr);
echo "</pre>";
echo "</p>";

$age_for_delete = 14;

echo "<p>Age for deletion: {$age_for_delete}</p>";

?>
<style>
table{
	border-collapse: collapse;
}
table td, table th{
	border: 1px solid;
	padding: 4px;
    text-align: center;
}
</style>		
<table>
	<thead>
	<tr>
		<th>Folder Name</th>
		<th>Date Modified</th>
		<th>Age</th>
		<th>To Delete</th>
	</tr>
	</thead>
	<tbody>
	<?php
	
	// loop throw scanned folders
	foreach( $folders_arr as $ds ){
	
		$dir = $scan_path.'/'.$ds;
		
		// if it's a directory
		if( is_dir($dir) ){
			
			// get folder name as date, only used of folder name was used as date
			$year = substr($ds,0,4);
			$month = substr($ds,4,2);
			$day = substr($ds,6,2);
			
			// format date from folder name
			$full_date = "{$year}-{$month}-{$day}";
			
			// get date via file/folder date modified
			$date_mod = date("Y-m-d H:i:s", filemtime($dir));
			
			// Age
			//$date1=date_create(date('Y-m-d',strtotime($full_date)));
			$date1=date_create(date('Y-m-d',strtotime($date_mod)));
			$date2=date_create(date('Y-m-d'));
			$diff=date_diff($date1,$date2);
			$age = $diff->format("%r%a");
			$age_diff = (((int)$age)!=0)?$age:0;
			
			?>
			
			<tr>
				<td><?php echo $ds; ?></td>
				<td><?php echo $date_mod; ?></td>
				<td><?php echo $age_diff; ?></td>
				<td><?php echo ( $age_diff>=$age_for_delete )?'Yes':'No'; ?></td>
			</tr>
			
			<?php
			
			
			if( $age_diff>=$age_for_delete ){
				
				if( file_exists($dir)==true ){
					echo "file/folder exist<br />";
					deleteDir($dir);
				}else{
					echo "file/folder doesn't exist anymore<br />";
				}
				
			}
			
			
		}
		
	}
	
	// insert cron log
	// AU
	mysql_query("INSERT INTO cron_log (`type_id`, `week_no`, `year`, `started`, `finished`, `country_id`) VALUES (" . CRON_TYPE_ID . "," . CURR_WEEK . ", " . CURR_YEAR . ", NOW(), NOW(), 1)");
	// NZ
	mysql_query("INSERT INTO cron_log (`type_id`, `week_no`, `year`, `started`, `finished`, `country_id`) VALUES (" . CRON_TYPE_ID . "," . CURR_WEEK . ", " . CURR_YEAR . ", NOW(), NOW(), 2)");
	
	?>
	</tbody>
<table>
