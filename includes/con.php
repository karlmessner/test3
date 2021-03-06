<?PHP session_start();

/*
error_reporting(E_ALL);
ini_set("display_errors", 1);
*/


/*
// PHP SETTINGS
ini_set('post_max_size', '5M');
ini_set('upload_max_filesize', '5M');
ini_set('memory_limit', '1000M');
ini_set('max_execution_time', '1920');
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

// SSL CERTIFICATE PATH
$certpath = $_SERVER["DOCUMENT_ROOT"] . $_ENV['CERTPATH'];

$db = mysqli_init();
mysqli_options($db, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
mysqli_options($db, MYSQLI_OPT_CONNECT_TIMEOUT, 10);


if (!$_ENV['DOMAIN']=='http://localhost/test3/'){    //
mysqli_ssl_set($db,NULL,NULL,$certpath,NULL,NULL);
}
mysqli_real_connect($db,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD,$DB_DATABASE,'3306',NULL,MYSQLI_CLIENT_SSL);


// ONE TIME INITIALIZATION OF USER PRIV
//mysqli_query($db,"GRANT USAGE ON *.* TO 'username'@'%' REQUIRE SSL;");echo mysqli_error($db);
// mysqli_query($db,"ALTER USER 'mstr_li_top_sec'@'%' REQUIRE NONE;");echo mysqli_error($db);


   

$now = time();

?>