<?

ini_set("error_reporting", E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING & ~E_STRICT);
//error_reporting(0);
//@ini_set('display_errors', 0);

mb_internal_encoding("UTF-8");

//ini_set('session.gc_maxlifetime', 60*60);

# Start a session
session_start();

# Add trailing slash to doc root if needed
if(substr($_SERVER['DOCUMENT_ROOT'], -1) != '/') $_SERVER['DOCUMENT_ROOT'] .= "/";

# Include config file
if($_SERVER['SERVER_NAME'] == "lcrm.testmyhome.com.au") include($_SERVER['DOCUMENT_ROOT'] . 'inc/config.php');
else include('config.php');

# Include classes
include('user.class.php');
include('agency.class.php');
include('encryption.class.php');
include('report.class.php');
include('region.class.php');
include('job_class.php');
include('jpagination_class.php');
include('last_contact_class.php');
include('tech_run_class.php');
include('sats_crm_class.php');
include('propertyme_api.class.php');
include('sats_query.class.php');
include('ws_sms_class.php');
include('Openssl_Encrypt_Decrypt.php');

# Include functions
include('functions.php');
//include('functions2.php');
include('property_functions.php');
include('job_functions.php');
include('alarm_functions.php');
include('view_jobs_functions.php');

include('precompleted_jobs_functions.php');
include('duplicate_properties_functions.php');
include('ageing_jobs_functions.php');
include('multiple_jobs_functions.php');
include('unserviced_functions.php');
include('no_id_properties_functions.php');
include('missing_region_functions.php');
include('booked_function.php');
include('agency_api_class.php');

# Include email functions
include('swiftmailer/lib/swift_required.php');
//include('swiftmailer2/lib/swift_required.php');
include('email_functions.php');

# include library
include($_SERVER['DOCUMENT_ROOT'].'phpqrcode/qrlib.php');
include($_SERVER['DOCUMENT_ROOT'].'class.upload/src/class.upload.php');
include($_SERVER['DOCUMENT_ROOT'].'BitlyPHP/bitly.php');

# Connect to Database
$connection = mysql_connect(HOST, USERNAME, PASSWORD) or die("Could not connect to database:" . mysql_error());
mysql_select_db(DATABASE, $connection) or die("Unable to Select database");

if(UTF8_USED)
{
	#mysql_query("SET NAMES 'utf8'");
}

if(DEV_SITE)
{
	mysql_set_charset('utf8');
}

# Create user object
$user = new User();