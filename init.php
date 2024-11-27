<?

mb_internal_encoding("UTF-8");

# Start a session
session_start();

# Add trailing slash to doc root if needed
if(substr($_SERVER['DOCUMENT_ROOT'], -1) != '/') $_SERVER['DOCUMENT_ROOT'] .= "/";

# Include config file
if($_SERVER['SERVER_NAME'] == "lcrm.testmyhome.com.au") include($_SERVER['DOCUMENT_ROOT'] . 'inc/config.php');
elseif($_SERVER['DOCUMENT_ROOT'] == "E:/projects/ilisys-sats-com-au/crm/") include($_SERVER['DOCUMENT_ROOT'] . 'inc/config.php');
else include('config.php');

# Include classes
include('user.class.php');
include('agency.class.php');
include('encryption.class.php');
include('report.class.php');
include('region.class.php');

# Include functions
include('functions.php');
include('property_functions.php');
include('job_functions.php');
//include('alarm_functions.php');


# Include email functions
include('swiftmailer/lib/swift_required.php');
include('email_functions.php');

# Connect to Database
$connection = mysql_connect(HOST, USERNAME, PASSWORD) or die("Could not connect to database:" . mysql_error());
mysql_select_db(DATABASE, $connection) or die("Unable to Select database");

if(UTF8_USED)
{
	#mysql_query("SET NAMES 'utf8'");
}

# Create user object
$user = new User();

# Perform access check (ensure logged in / process login & logout requests)
include('access_check.php');



?>