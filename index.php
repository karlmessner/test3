<?PHP include('includes/db2.php');?>
<?PHP 
//composer
error_reporting(E_ALL);
ini_set("display_errors", 1);
require './vendor/autoload.php';
require 'env.php';
	
	print_r($_ENV);

// INCREASE MAX FILE UPLOAD SIZE
ini_set("upload_max_filesize", "150m");
ini_set("post_max_size", "151m");

//phpinfo();

// PRIVATE KEY
$goodKey = 'Wa6-abf-oDM4-rgEn';

// TESTING	
$debug 				= false;
$actuallySendEmail 	= true;
$debugBody 			= false; // nb: triggers read pixel
$overRideRecipients	= false;

//status:
$auth_good	=0;
$file_good	=0;
$db_good	=0;
$em_good	=0;

if ($debug){
error_reporting(E_ALL);
ini_set("display_errors", 1);
}



// check for pk=Wa6-abf-oDM4-rgEn otherwise send to 404
if ($_POST['pk']!=$goodKey)||(1){
	header('Location:http://www.actorslaunchpad.com/');
	} else {
		$auth_good='1';}
	
// SANITZE POST
$pk 							= mysql_real_escape_string($_POST['pk']);
$Name 							= mysql_real_escape_string($_POST['Name']);
$Role 							= mysql_real_escape_string($_POST['Role']);
$Title 							= mysql_real_escape_string($_POST['Title']);
$title_card_text 				= mysql_real_escape_string($_POST['Title_text']);
$Note 							= mysql_real_escape_string($_POST['Note']);
$Email 							= mysql_real_escape_string($_POST['Email']);
$Recipients_emails 				= mysql_real_escape_string($_POST['Recipients_emails']);
$Age_range 						= mysql_real_escape_string($_POST['Age_range']);
$Bio 							= mysql_real_escape_string($_POST['Bio']);
$Profile_pic_url 				= mysql_real_escape_string($_POST['Profile_pic_url']);

if ($debug) echo "<pre>";
if ($debug) print_r($_POST);
if ($debug) print_r($_FILES);

// STORE RAW POST
$rawPost = mysql_real_escape_string( print_r($_POST,true) );
$rawPost .= mysql_real_escape_string(  print_r($_FILES,true) );

// upload video files
$directory_self = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
$imgdir =  '/uploads-moodcaster-submissions/';
$uploadsDirectory = $_SERVER['DOCUMENT_ROOT'] . $imgdir;
$fieldname = 'Zip_file';
if ($_FILES[$fieldname]['name']){
	$file_good='1';
	$cleanname = str_replace('\'','',$_FILES[$fieldname]['name']);
	$replace="_";
	$mc_file_size = $_FILES[$fieldname]['size'];
	$vidSize = $mc_file_size;
	$pattern="/([[:alnum:]_\.-]*)/";
	$cleanname=str_replace(str_split(preg_replace($pattern,$replace,$cleanname)),$replace,$cleanname);
	$url = "http://www.ActorsLaunchpad.com".$imgdir.$now.'-'.$cleanname; // url
	$uploadFilename = $uploadsDirectory.$now.'-'.$cleanname;
	@move_uploaded_file($_FILES[$fieldname]['tmp_name'], $uploadFilename) ;	
	$file_url = $url;
	$file_path = $uploadFilename;	
}

// upload title frame
$directory_self = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
$imgdir =  '/uploads-moodcaster-submissions/';
$uploadsDirectory = $_SERVER['DOCUMENT_ROOT'] . $imgdir;
$fieldname = 'Title_frame';
if ($_FILES[$fieldname]['name']){
	$file_good='1';
	$cleanname = str_replace('\'','',$_FILES[$fieldname]['name']);
	$replace="_";
	$mc_file_size = $_FILES[$fieldname]['size'];
	$pattern="/([[:alnum:]_\.-]*)/";
	$cleanname=str_replace(str_split(preg_replace($pattern,$replace,$cleanname)),$replace,$cleanname);
	$url = "http://www.ActorsLaunchpad.com".$imgdir.$now.'-'.$cleanname; // url
	$uploadFilename = $uploadsDirectory.$now.'-'.$cleanname;
	@move_uploaded_file($_FILES[$fieldname]['tmp_name'], $uploadFilename) ;	
	$title_frame_url=$url;
	$title_frame_path=$uploadFilename;	
}


// upload title card/profile pic
$directory_self = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
$imgdir =  '/uploads-moodcaster-submissions/';
$uploadsDirectory = $_SERVER['DOCUMENT_ROOT'] . $imgdir;
$fieldname = 'Title_card';
if ($_FILES[$fieldname]['name']){
	$file_good='1';
	$cleanname = str_replace('\'','',$_FILES[$fieldname]['name']);
	$replace="_";
	$mc_file_size = $_FILES[$fieldname]['size'];
	$pattern="/([[:alnum:]_\.-]*)/";
	$cleanname=str_replace(str_split(preg_replace($pattern,$replace,$cleanname)),$replace,$cleanname);
	$url = "http://www.ActorsLaunchpad.com".$imgdir.$now.'-'.$cleanname; // url
	$uploadFilename = $uploadsDirectory.$now.'-'.$cleanname;
	@move_uploaded_file($_FILES[$fieldname]['tmp_name'], $uploadFilename) ;	
	$title_card_url=$url;
	$title_card_path=$uploadFilename;	
}


// INSERT INTO DATABASE
$sql = "INSERT INTO mc_submissions SET \n";

$sql .=" mc_creation 			= '$now', \n";
$sql .=" mc_name 				= '$Name', \n";
$sql .=" mc_role 				= '$Role', \n";
$sql .=" mc_title 				= '$Title', \n";
$sql .=" mc_note 				= '$Note', \n";
$sql .=" mc_email 				= '$Email', \n";
$sql .=" mc_recipients_emails 	= '$Recipients_emails', \n";
$sql .=" mc_age_range 			= '$Age_range', \n";
$sql .=" mc_bio		 			= '$Bio', \n";
$sql .=" mc_profile_pic			= '$profile_pic', \n";
$sql .=" mc_title_frame_url		= '$title_frame_url', \n";
$sql .=" mc_title_frame_path	= '$title_frame_path', \n";
$sql .=" mc_file_url			= '$file_url', \n";
$sql .=" mc_file_path			= '$file_path', \n";
$sql .=" mc_title_card_text		= '$title_card_text', \n";
$sql .=" mc_title_card_url		= '$title_card_url', \n";
$sql .=" mc_title_card_path		= '$title_card_path', \n";
$sql .=" mc_profile_url			= '$Profile_pic_url', \n";
$sql .=" mc_file_size			= '$vidSize', \n";
$sql .=" mc_rawpost				= '$rawPost', \n";
$sql .=" mc_pk					= '$pk' \n";

if ($debug) echo "<BR><pre>$sql</pre><br /><br />";

// ONLY INSERT INTO DATABASE IF THEY ATTACHED SOMETHING
if ($mc_file_size>0){
	$result = mysql_query($sql); echo mysql_error();
	$id = mysql_insert_id();
	}
	
// CREATE SHORT URL TO DOWNLOAD PAGE, STORE IN DB
$s=base64_encode($id);
$shortDownloadLink = "http://d.moodcaster.com/$s";
$sql = "UPDATE mc_submissions SET mc_download_link = '$shortDownloadLink' WHERE mc_id ='$id' LIMIT 1";
mysql_query($sql);
	
if ($result){$db_good='1';}
if ($debug) echo mysql_error();

// CALCULATE FONT SIZE and LINE-HEIGHT OF NAME BASED ON NAME LENGTH
include('calcFontSize.php');


// EMBED SUBMISSION NUMBER
$s=$id;

// SEND EMAIL
include('email-submission-template.php'); // returns $body

$to=$Recipients_emails;
$fromEmail = $Email;
$fromEmail = "submissions@moodcaster.com";
$fromName = "Moodcaster";

// OVERRIDE RECIPIENT TO ME
if ($overRideRecipients){ $to="karlmessner@gmail.com";}


//$bcc="submissions@moodcaster.com";
$subject = "Video submission: $Role in $Title by $Name (" . date("m.d.y g:ia") . ")";
$subject = stripslashes($subject);

// HEADERS
$headers = "From: $fromName <$fromEmail>\r\n";
$headers .= "Reply-To: $fromEmail\r\n";
//$headers .= "$bcc: submissions@moodcaster.com";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

if ($vidSize>0){
	// don't email unless there is a file attached	
	if ($actuallySendEmail) {
		$result = mail($to, $subject, $body,$headers);
		}
	}
if ($result){$em_good='1';}
if ($debug) echo "TO:$to<BR>";
if ($debug) echo "FROM:$fromEmail<BR>";
if ($debug) echo "HEADERS:$headers<BR>";

if ($debugBody) echo $body;

// callback to app
if ($auth_good * $file_good * $db_good * $em_good){
	//echo "https://www.ActorsLaunchpad.com/moodcaster/download.php?s=$id&c=$now";
	
echo $shortDownloadLink;
	
	
	
	
}
if ($debug){
	echo "\n\n\n";
	if ($auth_good) {echo "Authorized Key\n";}
	if ($file_good) {echo "File Uploaded\n";}	
	if ($db_good) {echo "Database Updated\n";}
	if ($em_good) {echo "Email Sent\n";}	
	}

	
?>

