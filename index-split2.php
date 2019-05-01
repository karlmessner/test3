<?PHP 

/*
	THIS APP IS THE SECOND STEP ENDPOINT FOR SELF-TAPING SUBMISSIONS FROM MOODCASTER IOS APP
	
	1) RECEIVES ID,PK AS GET VAR
	2) PULLS EMAIL FROM DATABASE
	2) INSERTS IT INTO QUEUE
	3) LOGS AND EMAILS SUBMITTER
	
*/


// DEBUG SETTINGS  	
$debug 						= $_REQUEST['debug'];
$logging					= true;
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

// PRIVATE KEY
$goodKey = $_ENV['GOODKEY'];


// check for pk otherwise send to error
if ($_REQUEST['pk']!=$goodKey){
	echo "Bad Auth";
	$location = "Location:" . $_ENV['DOMAIN'] . "error.php";
	header($location);
	} else {
		$auth_good='1';}

	
// SANITZE POST
$id 							= mysqli_real_escape_string($db,$_REQUEST['id']);

// LOGGING
$logMessage = "PULLING EMAIL FROM DB";
if ($logging){logStatus($id,$logMessage);}
					
// PULL EMAIL FROM FROM DATABASE
if ($debug) {echo "PULL EMAIL FROM FROM DATABASE...<BR>";}
$sql = "SELECT  mc_email from mc_submissions where mc_id='$id'";
$rsEMAIL = mysqli_query($db, $sql); 
$thisEMAIL = mysqli_fetch_array($rsEMAIL);
$Email = $thisEMAIL['mc_email'];
$sqlError = mysqli_error($db);
if ($debug) {
	echo "EMAIL: $Email <BR> "; echo mysqli_error($db);}


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


//IF EVERYTHING WENT SMOOTHLY, REPORT SUCCESS TO APP
if ($debug) {echo "callback to ios...<BR>";}
	echo "success";	

// LOGGING
$logMessage = "Sent success to App.";
if ($logging){logStatus($id,$logMessage);}

?>

