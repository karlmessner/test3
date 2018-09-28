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

$db = mysqli_connect($DB_SERVER, $DB_USERNAME, $DB_PASSWORD) or die(mysqli_error($db));
$database = mysqli_select_db($db, $DB_DATABASE) or die(mysqli_error($db));

$now = time();

?>