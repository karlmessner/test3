<?PHP 

/*
	THIS APP IS THE FIRST STEP IN A TWO-STEP ENDPOINT FOR SELF-TAPING SUBMISSIONS FROM MOODCASTER IOS APP.
	IT ACCEPTS THE SMALL Title_card FILE, BUT CREATES A PRESIGNED URL FOR S3 SO THE APP CAN DIRECTLY UPLOAD TO S3,
	GETTING AROUND HEROKU'S 30 SECOND LIMITATION
	
	EXPECTS: 
	POST VARS: pk, Name, Role, Title_text, Note, Email, Recipients_emails, Age_range, Bio, Profile_pic_url,w,h
	POST FILES: Title_card
	
	1) RECEIVES THE SMALL Title_card FILE OF TITLECARD AND SENDS RAW ZIP FILE AND ANY OTHER FILE SUBMITTED TO CLOUD STORAGE
	2) INSERTS INTO DATABASE
	3) QUERIES AWS TO GET THE PRESIGNED URL
	4) REPORTS BACK TO IOS THE PRESIGNED URL, THE EVENTUAL DOWNLOAD URL AND THE URL FOR THE APP TO HIT WHEN IT'S DONE UPLOADING TO AWS, THAT OTHER APP (index-split2.php) PUTS THE ID IN THE QUEUE, EMAILS THE PERSON AND FINISHES THE PROCESS
	
*/

// DEBUG SETTINGS  	
$debug 						= $_POST['debug'];
$logging						= true;

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
$sql .=" mc_title_card_text		= '$title_card_text', \n";
$sql .=" mc_title_card_url		= '$titleCardURL', \n";
$sql .=" mc_profile_url			= '$Profile_pic_url', \n";
$sql .=" mc_rawpost				= '$rawPost', \n";
$sql .=" mc_target_width		= '$target_width', \n";
$sql .=" mc_target_height		= '$target_height', \n";
$sql .=" mc_pk					= '$pk' \n";
if ($debug) echo "<BR><BR><pre>$sql</pre><br /><br />";
if ($debug) {echo "inserting...<BR>";}
$result = mysqli_query($db, $sql); 
$sqlError = mysqli_error($db);
if ($debug) {echo mysqli_error($db);}
$id = mysqli_insert_id($db);

// LOGGING
$cleanSQL= mysqli_real_escape_string($db, $sql);
$logMessage = "Inserted Into Database: id: $id $cleanSQL  ERROR: $sqlError";
if ($logging){logStatus($id,$logMessage);}
	
// CREATE SHORT URL (FROM INSERT ID) TO DOWNLOAD PAGE, STORE IN DB
if ($debug) {echo "create short url...<BR>";}
$s=createShortLink($id);
$shortDownloadLink = $_ENV['DOMAIN'] . 'download.php?s='.$s;
$shortDownloadLink = "https://www.Moodcaster.com/send/download.php?s=".$s;
$shortDownloadLink = "https://moodcaster.com/share/".$s;

// CREATE S3 FILENAME (KEY) TIMESTAMP_ID
$s3Key = $now ."_".$id . ".zip";


// EXTRACT ENVIRONMENT VARIABLES
extract($_ENV);



// GET PRESIGNED URL FOR ZIP FILE
$s3Client = new Aws\S3\S3Client([
			'region'  => 'us-east-1',
			'version' => 'latest',
			'credentials' => [
				'key'    => "$AWSKEY",
				'secret' => "$AWSSECRET"
				]
]);

$cmd = $s3Client->getCommand('putObject', [
    'Bucket' => $AWSVIDBUCKET,
    'Key' => $s3Key,
	'ACL' => 'public-read'	
]);

$request = $s3Client->createPresignedRequest($cmd, '+20 minutes');	

$presignedUrl = (string)$request->getUri();	

//Getting the URL to to object
$rawUrl = $s3Client->getObjectUrl($AWSVIDBUCKET,$s3Key);

// UPDATE DATABASE WITH DOWNLOAD LINK, (EVENTUAL) URL TO S3 OBJECT
$sql = "UPDATE mc_submissions SET mc_download_link = '$shortDownloadLink', mc_raw_zip_file_url	= '$rawUrl'
 WHERE mc_id ='$id' LIMIT 1";
mysqli_query($db,$sql);
if ($result){$db_good='1';}
if ($debug) echo mysqli_error($db);

// CREATE SECOND STEP ENDPOINT FOR IOS APP:
$completedUrl = "https://video.moodcaster.com/index-split2.php?id=$id&pk=$goodKey";

// RESPONSE TO CALLER	
if ($debug){
	echo "\n\n\n";
	if ($auth_good) {echo "Authorized Key\n";}
	if ($file_good) {echo "File Uploaded\n";}	
	if ($db_good) {echo "Database Updated\n";}
	}

//IF EVERYTHING WENT SMOOTHLY, REPORT SUCCESS TO APP
if ($debug) {echo "callback to ios...<BR>";}
 if (1){
	$responseARR = array(
    "shortDownloadLink" => "$shortDownloadLink",
    "preSignedUrl" => "$presignedUrl",
    "completedUrl" => "$completedUrl"
);
	$response = json_encode($responseARR,JSON_PRETTY_PRINT);
	echo $response; 
	}	else  {
	echo "Error. Please try again";	
	}

// LOGGING
$logMessage = "Sent link to App.";
if ($logging){logStatus($id,$logMessage);}
?>