<?PHP 
session_start();

/*
error_reporting(E_ALL);
ini_set("display_errors", 1);
*/



// SET CURRENT TIMEZONE
date_default_timezone_set('America/New_York');

// USER CONSTANTS
$ip 			= $_SERVER['REMOTE_ADDR'];
$user_agent 	= $_SERVER['HTTP_USER_AGENT'];
$referrer		= $_SERVER['HTTP_REFERER'];
$php_self		= $_SERVER['PHP_SELF'];

// MYSQL CONNECTION
$DB_SERVER 		= $_ENV['MYSQL_SUBMISSIONS_DBSERVER'];
$DB_USERNAME 	= $_ENV['MYSQL_SUBMISSIONS_USER'];
$DB_PASSWORD 	= $_ENV['MYSQL_SUBMISSIONS_PASSWORD'];
$DB_DATABASE 	= $_ENV['MYSQL_SUBMISSIONS_DB'];
$DB_URL 		= $_ENV['DATABASE_URL'];

/*
$db = mysqli_connect($DB_SERVER, $DB_USERNAME, $DB_PASSWORD) or die(mysqli_error($db));
mysqli_ssl_set($db,NULL,NULL,NULL,'config',NULL);
mysqli_real_connect($db);
*/

$db = mysqli_init();
mysqli_ssl_set($db,NULL,NULL,'config/rds-combined-ca-bundle.pem',NULL,NULL);
mysqli_real_connect($db,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD,$DB_DATABASE);
// $database = mysqli_select_db($db, $DB_DATABASE) or die(mysqli_error($db));

$now = time();

?>