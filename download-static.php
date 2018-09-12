<?PHP include('../includes/db.php');?>
<?PHP
/*
THIS SCRIPT SHOWS THE DOWNLOAD PAGE. IT USES THE SAME TEMPLATE FROM THE EMAIL
1) EXTRACT THE GET VAR
2) PULL THE RECORD
3) UPDATE THE CLICK TABLE
4) SHOW THE PAGE	
*/	

// EXTRACT GET
$s=mysql_real_escape_string($_GET['s']);

// PULL RECORD
$sql =  "SELECT * from mc_submissions WHERE mc_id='$s' LIMIT 1";
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

$body=file_get_contents("template-download.htm");
$stylesheet=file_get_contents("media/css/emailcss.css");

//injections
$downloadLink = "download_file.php?s=$s";
$variablesToInject = array("stylesheet","Name","Role","Title","thumb_url","fontSize","lineHeight","s","downloadLink");
foreach ($variablesToInject as $thisVar){
	$thisVal = $$thisVar;
	$thisVar = "$".$thisVar;
	$body = str_replace($thisVar, $thisVal, $body);
}
$body = stripslashes($body);

echo $body;	
?>