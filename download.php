<?PHP include('../includes/db.php');?>
<?PHP

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

$s=mysql_real_escape_string($_GET['s']);
$s=base64_decode($s);

// PULL RECORD
$sql =  "SELECT * from mc_submissions WHERE mc_id='$s'  LIMIT 1";
$rsSUBS = mysql_query($sql);
$thisSUB = mysql_fetch_array($rsSUBS);
extract($thisSUB);

// UPDATE CLICK TABLE
$sql =  "UPDATE mc_submissions SET mc_click='$now',mc_ip='$ip', mc_useragent='$user_agent' where mc_id='$s' LIMIT 1";
mysql_query($sql);echo mysql_error();

// MAP VARS
$Name = $mc_name;
$Role = $mc_role;
$Title= $mc_title;
$thumb_url=$mc_vid_thumb_url;
	
// CALCULATE FONT SIZE and LINE-HEIGHT OF NAME BASED ON NAME LENGTH
include('calcFontSize.php');

// $body=file_get_contents("download-template.htm");
$body=file_get_contents("template-download.htm");

$stylesheet=file_get_contents("media/css/download.css");
//echo "$mc_file_path";
if ($mc_file_path){
// unzip movie to play
$zipfile = $mc_file_path;
$zip = new ZipArchive;
$path = "/uploads-moodcaster-submissions-zip-sandbox/" . $now . "-" . $mc_id ."-". $mc_email ."/";
$abspath = $_SERVER['DOCUMENT_ROOT'] . $path;

$res = $zip->open($zipfile);
if ($res === TRUE){
	$zip->extractTo($abspath);
	$zip->close();
	//echo "successfully opened zip";
	
	//choose file with extension .m4v
	$globstring = $abspath . '*.mp4';
	$m4vPath = glob($globstring);
	$firstm4v = $m4vPath[0];
	$m4vfile = substr($firstm4v, strrpos($firstm4v, '/') + 1);
	//echo "<BR>file: $m4vfile<BR><BR>";
	
	$m4vPath = $path . $m4vfile;
	//echo "<BR>url: $m4vPath<BR><BR>";
	
	//figure out orientation from META data
	

	
	
	
	}// if res

} // if mc_file_path

// SHARELINK IS PHPSELF
$shareLink = $mc_download_link;

//injections
$downloadLink = "download_file.php?s=$s";
$variablesToInject = array("stylesheet","Name","Role","Title","Profile_pic","fontSize","lineHeight","shareLink","downloadLink","m4vPath");
foreach ($variablesToInject as $thisVar){
	$thisVal = $$thisVar;
	$thisVar = "$".$thisVar;
	$body = str_replace($thisVar, $thisVal, $body);
}
$body = stripslashes($body);

echo $body;	
?>