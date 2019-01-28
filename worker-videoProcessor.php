<?PHP
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');

//ERROR REPORTING
error_reporting(E_ALL);
ini_set("display_errors", 1);
$logging = true;


// LOAD FUNCTIONS
require('includes/functions.php');



// INITIALIZE RABBITMQ
define('AMQP_DEBUG', true);
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
$url = parse_url(getenv('CLOUDAMQP_URL'));
$conn = new AMQPConnection($url['host'], 5672, $url['user'], $url['pass'], substr($url['path'], 1));
$ch = $conn->channel();
$exchange = 'amq.direct';
$queue = 'Video_Process_queue';
$ch->queue_declare($queue, false, true, false, false);
$ch->exchange_declare($exchange, 'direct', true, true, false);
$ch->queue_bind($queue, $exchange);

function callback($msg){
	$payload = $msg->body;
	/****************** APPLICATION CODE ******************************************************************/
	global $db;
	global $now;
	global $logging;

$debug 	= false;
if ($debug) {
	echo "<pre> \n";
	//phpinfo();
	}

/*  TO DO
	
	Check to make sure it wasn't already done!
	
*/

	
/*

This app is an endpoint for an ios device. It accepts a
post request which includes a zip file full of individual files
The zip is unzipped,  individual video files are normalized
to codec, size, and orientation. Then, they're re-keyframed
then it re-keyframes every video to 30fps with keyframes every 
1 sec, STITCHES THEM TOGETHER, trims off the unavoidable first 
1/2 second, then moves the new stitched file, along with all the 
newly fixed  individual scenes into a new folder, rezips it, 
uploads it to Amazon, uploads the stitched final file separately, 
records all the info, including the amazon urls into the database, 
builds the email, sends it through Sendgrid.	
*/

// PAYLOAD FROM QUEUE IS THE ID WE NEED TO PULL		
$id=$payload;				
if ($debug) {echo "id : " .$id, "\n";}
		
// TESTING SETTINGS  	
$debug 				= true;
$actuallySendEmail	= true;
$overRideRecipients	= false;
$debugBody			= false;

// DEFS
$mc_file_size = '';
$title_card_url='';
$fontSize='';
$lineHeight='';
$vidSize='';

// PRIVATE KEY
$goodKey = $_ENV['GOODKEY'];

// LOGGING
$logMessage = "WORKER: Got job from queue.";
if ($logging){logStatus($id,$logMessage);}

// UPDATE PERCENTAGE
updatePercentage($id,'Processing');

// PULL RECORD FROM DB
$sql= "SELECT * from mc_submissions WHERE mc_id = '$id'";
if ($debug) {echo "SQL : " .$sql, "\n";}

$rsSUBS = mysqli_query($db, $sql); 
if ($debug) {echo mysqli_error($db);}

$thisSUB = mysqli_fetch_array($rsSUBS);
extract($thisSUB);

// ASSIGN VARS
$Title 				= $mc_title;
$title_card_text	= $mc_title_card_text;
$Role 				= $mc_role;
$Name 				= $mc_name;
$auditionDate 		= $mc_creation;
$Email 				= $mc_email;
$shortDownloadLink 	= $mc_download_link;
$titleCardURL		= $mc_title_card_url;
$Profile_pic_url	= $mc_profile_url;
$Recipients_emails	= $mc_recipients_emails;
$zipFileSize		= $mc_zip_file_size;
$target_width		= $mc_target_width;
$target_height		= $mc_target_height;

// LOGGING
$logMessage = "WORKER: Pulled record from Database. $Title $Role $Name $Email";
if ($logging){logStatus($id,$logMessage);}



// SET NAME OF FINAL DOWNLOADABLE
$auditionDate = date("m-d-Y g_ia",$now);
$downloadableFolderName = "MOODCASTER-" . $Title . "-" . $Role . "-" . $Name . "-" . $auditionDate;
$downloadableFolderName = str_replace(' ', '_', $downloadableFolderName);
if ($debug) {echo "downloadableFolderName : " .$downloadableFolderName, "\n";}


// UNZIP, NORMALIZE VIDEOS, STITCH, RE-ZIP NEW FILES
	// create a tmp directory with timestamp-email as name
		if ($debug) {echo "create tmp dir...<BR>";}
		$emailWithoutSymbols = preg_replace("/[^A-Za-z0-9 ]/", '', $Email);
		$sandbox = tempdir(null, $emailWithoutSymbols);
		if ($debug) {echo "SANDBOX : " .$sandbox, "\n";}


// LOGGING
$logMessage = "WORKER: SANDBOX : $sandbox";
if ($logging){logStatus($id,$logMessage);}


	
	// download cloud stored  raw_Zip_file into it
	
	
// LOGGING
$logMessage = "WORKER: STARTING to pull zip from cloud.";
if ($logging){logStatus($id,$logMessage);}

	
		if ($debug) {echo "Downloading zip from Cloud...<BR>";}
		$bucket = $_ENV['AWSVIDBUCKET'];
		$keyname =  pathinfo($mc_raw_zip_file_url,PATHINFO_BASENAME);
		$tmpFileName = $sandbox . '/' . $keyname;
		$awsKey=$_ENV['AWSKEY'];
		$awsSecret=$_ENV['AWSSECRET'];
		$result = '';
		if ($debug) {echo "downloading $keyname to $tmpFileName \n";}

		$s3 = new Aws\S3\S3Client([
			'region'  => 'us-east-1',
			'version' => 'latest',
			'credentials' => [
				'key'    => "$awsKey",
				'secret' => "$awsSecret",
			]
		]);		
		$result = $s3->getObject([
			'Bucket' => $bucket,
			'Key'    => $keyname,
			'SaveAs' => $tmpFileName
		]);

// LOGGING		
$logMessage = "WORKER: DONE pulling zip from cloud.";
if ($logging){logStatus($id,$logMessage);}


// LOGGING
$logMessage = "WORKER: STARTING to unzip file.";
if ($logging){logStatus($id,$logMessage);}
		
		
	// unzip file
		if ($debug) {echo "unzip file $tmpFileName ...<BR>";}
		$zip = new ZipArchive;
		$res = $zip->open($tmpFileName);
	if ($res === TRUE){
		$zip->extractTo($sandbox);
		$zip->close();
		}	

// LOGGING
$logMessage = "WORKER: DONE unzipping file.";
if ($logging){logStatus($id,$logMessage);}



// LOGGING
$logMessage = "WORKER: STARTING to Process videos and stitch.";
if ($logging){logStatus($id,$logMessage);}

				
	// stitch files
		if ($debug) {echo "stitch files...<BR>";}
		$stitchedFilePath = stitchMP4sIn($id,$sandbox,$target_width,$target_height);
		if ($debug) {echo "<BR>STITCHED FILE: $stitchedFilePath <BR>";}
		
// IF THE STITCHING FAILED, LOG IT, MARK IT FAILED AND EXIT
if (!file_exists($stitchedFilePath)){
	
// LOGGING
$logMessage = "WORKER: ERROR - STITCHED FILE DOESNT EXIST. $stitchedFilePath";
if ($logging){logStatus($id,$logMessage);}

// MARK JOB AS DONE IN QUEUE
$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

$logMessage = "WORKER: JOB MARKED DONE IN QUEUE.";
if ($logging){logStatus($id,$logMessage);}

// UPDATE PERCENTAGE
updatePercentage($id,'FAILED');


// EXIT
exit();	
	
}		
	
		
// LOGGING
$logMessage = "WORKER: DONE processing and stitching.";
if ($logging){logStatus($id,$logMessage);}
		

// LOGGING
$logMessage = "WORKER: STARTING to upload stitched file to cloud.";
if ($logging){logStatus($id,$logMessage);}
		
	// upload stitched file
		if ($debug) {echo "upload stitched file...<BR>";}
		if ($stitchedFilePath){
			$stitchAWS = uploadFile ($stitchedFilePath,$_ENV['AWSVIDBUCKET'],'');
			$stitchURL = $stitchAWS['ObjectURL'];
		if ($debug) {echo "<BR>STITCHED FILE URL: $stitchURL <BR>";}
		}	

// LOGGING
$logMessage = "WORKER: DONE uploading stitched file to cloud.";
if ($logging){logStatus($id,$logMessage);}

// LOGGING
$logMessage = "WORKER: STARTING to rezip processed video files.";
if ($logging){logStatus($id,$logMessage);}
		
	// REZIP
		// CREATES NEW TMP SUBDIR, MOVES FIXED_ VIDEO FILES, TRIMMING FIXED_ FROM FILE, 
		// MOVE IN STITCHED FILE RENAMED FINAL.MP4
		if ($debug) {echo "rezip...<BR>";}
		$newZipPath = rezip($sandbox, $downloadableFolderName );

// LOGGING
$logMessage = "WORKER: DONE rezipping processed video files.";
if ($logging){logStatus($id,$logMessage);}

// LOGGING
$logMessage = "WORKER: STARTING to upload zip of processed video files.";
if ($logging){logStatus($id,$logMessage);}
		

// upload video files
if ($newZipPath){
	if ($debug) {echo "upload new zip...<BR>";}
	$zipAWS = uploadFile ($newZipPath,$_ENV['AWSVIDBUCKET'],$downloadableFolderName);
	$zipURL = $zipAWS['ObjectURL'];
	$file_good = ($zipAWS);
	}
	
// LOGGING
$logMessage = "WORKER: updating database with url of final files.";
if ($logging){logStatus($id,$logMessage);}


// INSERT INTO DATABASE
if ($debug) {echo "Update database...<BR>";}

$sql = "UPDATE mc_submissions SET \n";
$sql .= " mc_zip_file_url		= '$zipURL', \n";
$sql .= " mc_stitch_file_url		= '$stitchURL' \n";
$sql .= "WHERE mc_id ='$id' LIMIT 1";

if ($debug) echo "<BR><BR><pre>$sql</pre><br /><br />";

// ONLY INSERT INTO DATABASE IF THEY ATTACHED SOMETHING OR allowNoFile=true
if ($stitchURL){
if ($debug) {echo "Updating database...<BR>";}
	$result = mysqli_query($db, $sql); 
	if ($debug) {echo mysqli_error($db);}
	}
	
// LOGGING
$logMessage = "WORKER: Sending email to recipients list.";
if ($logging){logStatus($id,$logMessage);}

// EMAIL SUBMISSION TO RECIPIENTS
if ($debug) {echo "Sending Submission to recipients...<BR>";}
include ('email/sendRecipientsEmail.php');

// LOGGING
$logMessage = "WORKER: Sending email to sender that it was send list.";
if ($logging){logStatus($id,$logMessage);}


// EMAIL NOTICE THAT SUBMISSION WAS SENT
// alter link with no-track flag AFTER it has already been sent to recipients above
$shortDownloadLink .= "&n=1";
if ($debug) {echo "Notifying Sender...<BR>";}
include ('email/sendSubmissionSentEmail.php');




// LOGGING
$logMessage = "WORKER: Done.";
if ($logging){logStatus($id,$logMessage);}


// UPDATE PERCENTAGE
updatePercentage($id,'100');


	
	
	
	/****************** .APPLICATION CODE ******************************************************************/
	// MARK JOB AS DONE IN QUEUE
	$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
	}
	
$ch->basic_qos(null, 1, null);
$ch->basic_consume($queue, '', false, false, false, false, 'callback');

while (count($ch->callbacks)) {
    $ch->wait();
}
//     $ch->wait();


$ch->close();
$conn->close();

?>