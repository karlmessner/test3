<?PHP 
 session_start();
// this is an additional copy of db without the fonts

// IF THEY'RE TRYING TO VIEW PAGES IN THE ADMIN DIRECTORY,
// BUT DON'T HAVE A $_SESSION[ADMIN] FLAG SET
// SEND THEM TO THE ADMIN LOGIN PAGE
// UNLESS THEY'RE ALREADY ON THE LOGIN PAGE, OR LOGMEIN SCRIPT, OR LOGOUT
// SINCE ALL OF THOSE START WITH LOG, JUST CHECK THAT MANY LETTERS OF THE CURRENT PAGE
if (substr($_SERVER['PHP_SELF'],0,10) != "/admin/log") {
	$inAdmin = (substr($_SERVER['PHP_SELF'],0,7) == "/admin/");
	$loggedInAdmin = $_SESSION['admin'];	
	if (($inAdmin)&&(!$loggedInAdmin)) {header("Location: login.php");}
}

// SET CURRENT TIMEZONE
date_default_timezone_set('America/New_York');


$ip 			= $_SERVER['REMOTE_ADDR'];
$user_agent 	= $_SERVER['HTTP_USER_AGENT'];
$referrer		= $_SERVER['HTTP_REFERER'];
$php_self		= $_SERVER['PHP_SELF'];



define('DB_SERVER', 'Localhost');
define('DB_USERNAME', 'actorsla_ActLpWe');
define('DB_PASSWORD', 'W3bU$3rP@$$');
define('DB_DATABASE', 'actorsla_wp_t8d2');

$admin_user='ALPadmin';
$admin_pass='Act0r$!';

//$db = mysql_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD) or die(mysql_error());
//$database = mysql_select_db(DB_DATABASE) or die(mysql_error());

$now = time();
$yesterday = $now - 24*60*60;
$tomorrow = $now + 24*60*60;
$today = strtotime('midnight');
$thirtyDaysAgo = $now - 30*24*60*60;

$domain = 'http://www.ActorsLaunchpad.com';

$thispage = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
$thispageEncoded = urlencode($thispage);

if ($_SESSION){
$user_id			= $_SESSION['user_id'];
$user_first			= $_SESSION['user_first'];
$user_last			= $_SESSION['user_last'];
$user_email			= $_SESSION['user_email'];
$user_headshot		= $_SESSION['user_headshot'];
$member				= $_SESSION['member'];

// track last seen
$lastSeenSQL = "Update users set u_last_seen='$now' where u_id='$user_id' limit 1";
mysql_query($lastSeenSQL);



}



// EMAIL HEADERS

	    $headers = "Organization: Actors Launchpad\r\n";
	    $headers .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
	   	$headers .= "MIME-Version: 1.0\r\n";
	    //$headers .= "Content-Type: multipart/alternative\r\n";
	    $headers .= "From: Actors Launchpad<do-not-reply@ActorsLaunchpad.com>\r\n";
		//$headers .= "boundary=\"----=_NextPart_=----\"\r\n";

		//$returnpath = "-f" . $user_email;
		$returnpath = "-f" . "info@ActorsLaunchpad.com";

		$text_part = "

----=_NextPart_=----

Content-type: text/plain; charset=iso-8859-1
Content-Transfer-Encoding: quoted-printable
";


		$html_part = "

----=_NextPart_=----

Content-type: text/html; charset=iso-8859-1
Content-Transfer-Encoding: quoted-printable
";


$end_part = "
----=_NextPart_=----
";

// UBER INSTRUCTOR CAN'T SEE SIDE INFO
// CURRENTLY HARDWIRED TO ID=107

$uber = ($_SESSION['user_id'] == 55);
?>