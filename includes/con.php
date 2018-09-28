<?PHP 
session_start();



error_reporting(E_ALL);
ini_set("display_errors", 1);





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

// SSL CERTIFICATE PATH
$certpath = $_SERVER["DOCUMENT_ROOT"] . $_ENV['CERTPATH'];

$db = mysqli_init();
mysqli_options($db, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
mysqli_options($db, MYSQLI_OPT_CONNECT_TIMEOUT, 10);
mysqli_ssl_set($db,NULL,NULL,$certpath,NULL,NULL);
mysqli_real_connect($db,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD,$DB_DATABASE,'3306',NULL,MYSQLI_CLIENT_SSL);

// ONE TIME INITIALIZATION OF USER PRIV
//mysqli_query($db,"GRANT USAGE ON *.* TO 'username'@'%' REQUIRE SSL;");echo mysqli_error($db);

$now = time();

?>