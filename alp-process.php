<?PHP 

/*
	THIS APP IS THE ENDPOINT FOR ACTORS LAUNCHPAD AUDITION TAPING
	
	1) RECEIVES FILES AND SENDS FILE  TO CLOUD STORAGE
	2) INSERTS INTO DATABASE
	3) EMAILS RECIPIENT
	
*/


// DEBUG SETTINGS  	
$debug 				= $_POST['debug'];
$logging			= true;


// ERROR REPORTING
if ($debug){
error_reporting(E_ALL);
ini_set("display_errors", 1);
}


// LOAD FUNCTIONS
require('includes/functions.php');


//composer, ENV Vars & mysql
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');

// INIT RABBITMQ
define('AMQP_DEBUG', true);
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;


// INIT VARS
$mc_file_size 		= '';
$title_card_url		= '';
$vidSize			= '';
$auth_good			= 0;
$file_good			= 0;
$db_good			= 0;
$em_good			= 0;
$id					=0;


// PRIVATE KEY
$goodKey = $_ENV['GOODKEY'];


// check for pk otherwise send to error
if ($_POST['pk']!=$goodKey){
	echo "Bad Auth";
	$location = "Location:" . $_ENV['DOMAIN'] . "error.php";
	header($location);
	} else {
		$auth_good='1';}

	
// SANITZE POST
$pk 							= mysqli_real_escape_string($db,$_POST['pk']);
$Name 							= mysqli_real_escape_string($db,$_POST['Name']);
$Role 							= mysqli_real_escape_string($db,$_POST['Role']);
$Title 							= mysqli_real_escape_string($db,$_POST['Title']);
$Note 							= mysqli_real_escape_string($db,$_POST['Note']);
$Email 							= mysqli_real_escape_string($db,$_POST['Email']);


// DEBUGGING	
if ($debug) {echo "<pre>";}
if ($debug) {echo "POST:<br>"; print_r($_POST);}
if ($debug) {echo "FILES:<br>"; print_r($_FILES);}
if ($debug) {/*echo "ENV:<br>"; print_r($_ENV);*/}
if ($debug) {echo "</pre>";}


// STORE RAW POST AS STRING IN VAR
$rawPost = mysqli_real_escape_string($db, print_r($_POST,true) );
$rawPost .= mysqli_real_escape_string($db, print_r($_FILES,true) );


// UPLOAD RAW ZIP FILE TO CLOUD STORAGE

// LOGGING
$logMessage = "ALP STARTING to store to cloud.";
if ($logging){logStatus($id,$logMessage);}



	if ($debug) {echo "upload raw zip to s3...<BR>";}
	$isUploadedFile = $_FILES['vid_file']['tmp_name'];
	if ($isUploadedFile){
		$vidFileSize = $_FILES['vid_file']['size'];
		$vidAWS = uploadFileFromFieldname('vid_file',$_ENV['AWSVIDBUCKET'],'');
		$vidURL = $vidAWS['ObjectURL'];
		if ($debug) {echo "<BR>VID FILE URL: $vidURL <BR>";}
		if ($vidURL) {$file_good = '1';}
	}	
	
	
// LOGGING
$logMessage = "DONE storing vid to cloud.";
if ($logging){logStatus($id,$logMessage);}

// LOGGING
$logMessage = "STARTING to store titlecard to cloud.";
if ($logging){logStatus($id,$logMessage);}


// UPLOAD RAW TITLE CARD FILE TO CLOUD STORAGE
if ($_FILES['Title_card']['size'] >1){
	if ($debug) {echo "upload title card...<BR>";}
	$titleCardAWS = uploadFileFromFieldname('Title_card',$_ENV['AWSVIDBUCKET']);
	$titleCardURL = $titleCardAWS['ObjectURL'];
	}

// LOGGING
$logMessage = "DONE storing titlecard to cloud.";
if ($logging){logStatus($id,$logMessage);}




// INSERT INTO DATABASE
if ($debug) {echo "insert into database if there's a file...<BR>";}
$sql = "INSERT INTO mc_submissions SET \n";
$sql .=" mc_creation 			= '$now', \n";
$sql .=" mc_alp 				= '1', \n";
$sql .=" mc_name 				= '$Name', \n";
$sql .=" mc_role 				= '$Role', \n";
$sql .=" mc_title 				= '$Title', \n";
$sql .=" mc_email 				= '$Email', \n";
$sql .=" mc_stitch_file_url	= '$vidURL', \n";
$sql .=" mc_zip_file_size		= '$vidFileSize', \n";
$sql .=" mc_title_card_url		= '$titleCardURL', \n";
$sql .=" mc_rawpost				= '$rawPost', \n";
$sql .=" mc_pk					= '$pk' \n";
if ($debug) echo "<BR><BR><pre>$sql</pre><br /><br />";
// ONLY INSERT INTO DATABASE IF THEY ATTACHED SOMETHING OR allowNoFile=true
if ($vidFileSize>0){
if ($debug) {echo "inserting...<BR>";}
	$result = mysqli_query($db, $sql); 
	if ($debug) {echo mysqli_error($db);}
	$id = mysqli_insert_id($db);
	}

// LOGGING
$logMessage = "Inserted Into Database";
if ($logging){logStatus($id,$logMessage);}


	
// CREATE SHORT URL (FROM INSERT ID) TO DOWNLOAD PAGE, STORE IN DB
if ($debug) {echo "create short url...<BR>";}
$s=createShortLink($id);
$shortDownloadLink = $_ENV['DOMAIN'] . 'download.php?s='.$s;
$sql = "UPDATE mc_submissions SET mc_download_link = '$shortDownloadLink' WHERE mc_id ='$id' LIMIT 1";
mysqli_query($db,$sql);
if ($result){$db_good='1';}
if ($debug) echo mysqli_error($db);


// EMAIL SUBMISSION TO RECIPIENT
if ($debug) {echo "Sending Submission to recipients...<BR>";}
include ('email/sendALPRecipientsEmail.php');

// LOGGING
$logMessage = "Submission Received Email Sent.";
if ($logging){logStatus($id,$logMessage);}


// RESPONSE TO CALLER	
if ($debug){
	echo "\n\n\n";
	if ($auth_good) {echo "Authorized Key\n";}
	if ($file_good) {echo "File Uploaded\n";}	
	if ($db_good) {echo "Database Updated\n";}
	}


//IF EVERYTHING WENT SMOOTHLY, REPORT SUCCESS TO APP
if ($debug) {echo "callback to ios...<BR>";}
 if (($auth_good)&&($file_good)&&($db_good)){
	//echo "success";
	echo $shortDownloadLink;
	}	else  {
	echo "Error. Please try again";	
	}

// LOGGING
$logMessage = "Done.";
if ($logging){logStatus($id,$logMessage);}

?>

