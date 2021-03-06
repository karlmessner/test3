<?PHP 

/*
	THIS APP IS THE ENDPOINT FOR SELF-TAPING SUBMISSIONS FROM MOODCASTER IOS APP
	
	1) RECEIVES FILES AND SENDS RAW ZIP FILE AND ANY OTHER FILE SUBMITTED TO CLOUD STORAGE
	2) INSERTS INTO DATABASE
	3) ADDS THE JOB TO THE RABBITMQ QUEUE
	4) REPORTS BACK TO IOS THE URL
	
*/


// DEBUG SETTINGS  	
$debug 						= $_POST['debug'];
$logging						= true;
$sendTheNotificationEmail 	= true; // also need to change in worker-videoProcessor.php


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
$standbyVideoUrl	= 'http://www.moodcaster.com/send/media/video/Standby.mp4';


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
$title_card_text 				= mysqli_real_escape_string($db,$_POST['Title_text']);
$Note 							= mysqli_real_escape_string($db,$_POST['Note']);
$Email 							= mysqli_real_escape_string($db,$_POST['Email']);
$Recipients_emails 				= mysqli_real_escape_string($db,$_POST['Recipients_emails']);
$Age_range 						= mysqli_real_escape_string($db,$_POST['Age_range']);
$Bio 							= mysqli_real_escape_string($db,$_POST['Bio']);
$Profile_pic_url 				= mysqli_real_escape_string($db,$_POST['Profile_pic_url']);
$target_width 					= mysqli_real_escape_string($db,$_POST['w']);
$target_height 					= mysqli_real_escape_string($db,$_POST['h']);

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
$logMessage = "STARTING to store raw zip to cloud.";
if ($logging){logStatus($id,$logMessage);}



	if ($debug) {echo "upload raw zip to s3...<BR>";}
	$isUploadedFile = $_FILES['Zip_file']['tmp_name'];
	if ($isUploadedFile){
		$zipFileSize = $_FILES['Zip_file']['size'];
		$rawAWS = uploadFileFromFieldname('Zip_file',$_ENV['AWSVIDBUCKET'],'');
		$rawURL = $rawAWS['ObjectURL'];
		if ($debug) {echo "<BR>RAW ZIP FILE URL: $rawURL <BR>";}
		if ($rawURL) {$file_good = '1';}
	}	
	
	
// LOGGING
$logMessage = "DONE storing raw zip to cloud.";
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
$sql .=" mc_name 				= '$Name', \n";
$sql .=" mc_role 				= '$Role', \n";
$sql .=" mc_title 				= '$Title', \n";
$sql .=" mc_note 				= '$Note', \n";
$sql .=" mc_email 				= '$Email', \n";
$sql .=" mc_recipients_emails 	= '$Recipients_emails', \n";
$sql .=" mc_age_range 			= '$Age_range', \n";
$sql .=" mc_bio		 			= '$Bio', \n";
$sql .=" mc_raw_zip_file_url	= '$rawURL', \n";
$sql .=" mc_zip_file_size		= '$zipFileSize', \n";
$sql .=" mc_stitch_file_url		= '$standbyVideoUrl', \n";
$sql .=" mc_title_card_text		= '$title_card_text', \n";
$sql .=" mc_title_card_url		= '$titleCardURL', \n";
$sql .=" mc_profile_url			= '$Profile_pic_url', \n";
$sql .=" mc_rawpost				= '$rawPost', \n";
$sql .=" mc_target_width		= '$target_width', \n";
$sql .=" mc_target_height		= '$target_height', \n";







$sql .=" mc_pk					= '$pk' \n";
if ($debug) echo "<BR><BR><pre>$sql</pre><br /><br />";
// ONLY INSERT INTO DATABASE IF THEY ATTACHED SOMETHING OR allowNoFile=true
if ($zipFileSize>0){
if ($debug) {echo "inserting...<BR>";}
	$result = mysqli_query($db, $sql); 
	$sqlError = mysqli_error($db);
	
	if ($debug) {echo mysqli_error($db);}
	$id = mysqli_insert_id($db);
	}

// LOGGING
$cleanSQL= mysqli_real_escape_string($db, $sql);
$logMessage = "Inserted Into Database: id: $id $cleanSQL  ERROR: $sqlError";
if ($logging){logStatus($id,$logMessage);}


	
// CREATE SHORT URL (FROM INSERT ID) TO DOWNLOAD PAGE, STORE IN DB
if ($debug) {echo "create short url...<BR>";}
$s=createShortLink($id);
$shortDownloadLink = $_ENV['DOMAIN'] . 'download.php?s='.$s;
$shortDownloadLink = "https://www.Moodcaster.com/send/download.php?s=".$s;
$shortDownloadLink = "https://www.moodcaster.com/share/".$s;

$sql = "UPDATE mc_submissions SET mc_download_link = '$shortDownloadLink' WHERE mc_id ='$id' LIMIT 1";
mysqli_query($db,$sql);
if ($result){$db_good='1';}
if ($debug) echo mysqli_error($db);


// ADD TO QUEUE
if ($id){
	$url = parse_url(getenv('CLOUDAMQP_URL'));
	$conn = new AMQPConnection($url['host'], 5672, $url['user'], $url['pass'], substr($url['path'], 1));
	$ch = $conn->channel();
	$exchange = 'amq.direct';
	$queue = 'Video_Process_queue';
	$ch->queue_declare($queue, false, true, false, false);
	$ch->exchange_declare($exchange, 'direct', true, true, false);
	$ch->queue_bind($queue, $exchange);
	$msg_body = $id;
	$msg = new AMQPMessage($msg_body, array('content_type' => 'text/plain', 'delivery_mode' => 2));
	$ch->basic_publish($msg, $exchange);
	if ($debug) echo "<BR><BR>id to be inserted into queue: $id<br /><br />";
	$ch->close();
	$conn->close();
	
	
	
// LOGGING
$logMessage = "Added to Queue.";
if ($logging){logStatus($id,$logMessage);}
	
// UPDATE PERCENTAGE
updatePercentage($id,'in queue');
	
	}

// EMAIL UPDATE TO USER
if ($debug) {echo "Sending Submission uploaded email...<BR>";}
if ($sendTheNotificationEmail){
include ('email/sendSubmissionUploadedEmail.php');
}

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
$logMessage = "Sent link to App.";
if ($logging){logStatus($id,$logMessage);}

?>

