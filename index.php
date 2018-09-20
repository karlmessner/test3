<?PHP 
// INCREASE MAX FILE UPLOAD SIZE
/*
ini_set("upload_max_filesize", "150m");
ini_set("post_max_size", "151m");
*/
// TESTING	
$debug 				= false;
$allowNoFile		= false;
$actuallySendEmail 	= true;
$debugBody 			= false; // nb: triggers read pixel
$overRideRecipients	= true;

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
		
// DEFS
$mc_file_size = '';
$title_card_url='';
$fontSize='';
$lineHeight='';
$vidSize='';


// PRIVATE KEY
$goodKey = $_ENV['GOODKEY'];

//status:
$auth_good	=0;
$file_good	=0;
$db_good	=0;
$em_good	=0;

// check for pk otherwise send to error
if ($_POST['pk']!=$goodKey){
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

// DEBUGGING	
if ($debug) {echo "<pre>";}
if ($debug) {echo "POST:<br>"; print_r($_POST);}
if ($debug) {echo "FILES:<br>"; print_r($_FILES);}
if ($debug) {echo "ENV:<br>"; print_r($_ENV);}
if ($debug) {echo "</pre>";}

// STORE RAW POST
$rawPost = mysqli_real_escape_string($db, print_r($_POST,true) );
$rawPost .= mysqli_real_escape_string($db, print_r($_FILES,true) );

// upload video files
$zipAWS = uploadFile ('Zip_file',$_ENV['AWSVIDBUCKET']);
$zipURL = $zipAWS['ObjectURL'];
$zipFileSize = $_FILES['Zip_file']['size'];

$titleFrameAWS = uploadFile ('Title_frame',$_ENV['AWSVIDBUCKET']);
$titleFrameURL = $titleFrameAWS['ObjectURL'];

$titleCardAWS = uploadFile ('Title_card',$_ENV['AWSVIDBUCKET']);
$titleCardURL = $titleCardAWS['ObjectURL'];

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
$sql .=" mc_title_frame_url		= '$titleFrameURL', \n";
$sql .=" mc_file_url			= '$zipURL', \n";
$sql .=" mc_title_card_text		= '$title_card_text', \n";
$sql .=" mc_title_card_url		= '$titleCardURL', \n";
$sql .=" mc_profile_url			= '$Profile_pic_url', \n";
$sql .=" mc_file_size			= '$zipFileSize', \n";
$sql .=" mc_rawpost				= '$rawPost', \n";
$sql .=" mc_pk					= '$pk' \n";

if ($debug) echo "<BR><pre>$sql</pre><br /><br />";

// ONLY INSERT INTO DATABASE IF THEY ATTACHED SOMETHING OR allowNoFile=true
if (($zipFileSize>0)||($allowNoFile)){
	$result = mysqli_query($db, $sql); echo mysqli_error($db);
	$id = mysqli_insert_id($db);
	}
	
// CREATE SHORT URL TO DOWNLOAD PAGE, STORE IN DB
$s=base64_encode($id);
$shortDownloadLink = "http://d.moodcaster.com/$s";
$sql = "UPDATE mc_submissions SET mc_download_link = '$shortDownloadLink' WHERE mc_id ='$id' LIMIT 1";
mysqli_query($db,$sql);
	
if ($result){$db_good='1';}
if ($debug) echo mysqli_error($db);

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

if ($zipFileSize>0){
	// don't email unless there is a file attached	
	if ($actuallySendEmail) {
		
		$email = new \SendGrid\Mail\Mail(); 
		$email->setFrom($fromEmail, $fromName);
		$email->setSubject($subject);
		$email->addTo($Recipients_emails);
		$email->addContent("text/plain", "and easy to do anywhere, even with PHP");
		$email->addContent("text/html", $body);
		$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
		try {
		    $response = $sendgrid->send($email);
		    if($debug){
		    print $response->statusCode() . "\n";
		    print_r($response->headers());
		    print $response->body() . "\n";
		    }
		} catch (Exception $e) {
		    echo 'Caught exception: '. $e->getMessage() ."\n";
		}		
		
		
		//$result = mail($to, $subject, $body,$headers);
		} // if actuallySendEmail
	} // if vidSize	
if ($result){$em_good='1';}
if ($debug) echo "TO:$to<BR>";
if ($debug) echo "FROM:$fromEmail<BR>";
if ($debug) echo "HEADERS:$headers<BR>";

if ($debugBody) echo $body;

// callback to app
if ($auth_good * $file_good * $db_good * $em_good){	
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

