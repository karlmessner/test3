<?PHP 
	
//composer, ENV Vars & mysql
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');
include('includes/functions.php');

/*
	

THIS SCRIPT SHOWS THE DOWNLOAD PAGE. IT USES THE SAME TEMPLATE FROM THE EMAIL
1) EXTRACT THE GET VAR
2) PULL THE RECORD
3) UPDATE THE CLICK TABLE
4) SHOW THE PAGE	
*/	


// SHOW ERRORS
$showerrors = false;
			if ($showerrors){
			error_reporting(E_ALL);
			ini_set("display_errors", 1);
			}


// EXTRACT GET

$s=mysqli_real_escape_string($db,$_GET['s']);
$e=mysqli_real_escape_string($db,$_GET['e']);
$s=decodeShortLink($s);
$n=mysqli_real_escape_string($db,$_GET['n']);

// PULL RECORD
$sql =  "SELECT * from mc_submissions WHERE mc_id='$s'  LIMIT 1";
$rsSUBS = mysqli_query($db,$sql); echo mysqli_error($db);
$thisSUB = mysqli_fetch_array($rsSUBS); 
extract($thisSUB);


// UPDATE CLICK TABLE
// don't register click if clicked from submission tracker
if (!$n){
$sql =  "UPDATE mc_submissions SET mc_click='$now',mc_ip='$ip', mc_useragent='$user_agent' where mc_id='$s' LIMIT 1";
mysqli_query($db,$sql);echo mysqli_error($db);
}


// NOT READY YET?
$notReady = ($_GET['nr']) ? $_GET['nr'] : (strlen($mc_stitch_file_url)<5);
if ($notReady){
	
	$returnLink = $mc_download_link . "&n=1";
	$returnLink = urlencode($returnLink);
	$waitingRoom = "Location:". $_ENV['DOMAIN'] . "/download-not-ready.php?n=1&d=$returnLink";
	header($waitingRoom);
}



// MAP VARS
$Name = $mc_name;
$Role = $mc_role;
$Email = $mc_email;
$Title= $mc_title;
$thumb_url=$mc_vid_thumb_url;


// SEND EMAIL NOTIFICATION THAT AUDITION IS BEING WATCHED (if by someone else)
if (!$n){
	$shortRecipEmail = '';
	$shortRecipEmail = substr($e, 0,2) . '&hellip;'. substr($e, -8);
	$shortRecipEmail = (strlen($e)>4) ? $shortRecipEmail : "Someone ";
	include('email/sendSubmissionClickedEmail.php');
	}




	
// CALCULATE FONT SIZE and LINE-HEIGHT OF NAME BASED ON NAME LENGTH
//require_once 'calcFontSize.php';

// $body=file_get_contents("download-template.htm");
$body=file_get_contents("template-download.htm");
$stylesheet=file_get_contents("media/css/download.css");

$m4vPath = $mc_stitch_file_url;

// SHARELINK IS PHPSELF
$shareLink = $mc_download_link;

//injections
$downloadLink = "download_file.php?s=$s";

// pass flag along to download file that this came from submission tracker to not register the download
$downloadLink .= ($n) ? '&n=1':'';

$sGetVar = $s;
$nGetVar = $n;
$DOMAIN = $_ENV['DOMAIN'];
$variablesToInject = array("stylesheet","Name","Role","Title","Profile_pic","fontSize","lineHeight","shareLink","downloadLink","m4vPath","sGetVar","DOMAIN");
foreach ($variablesToInject as $thisVar){
	$thisVal = $$thisVar;
	$thisVar = "$".$thisVar;
	$body = str_replace($thisVar, $thisVal, $body);
}
$body = stripslashes($body);

echo $body;	
?>