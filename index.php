<?PHP 
	
	
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
		
		
		
		
		
// TESTING SETTINGS  	
$debug 				= false;
$debugByEmail		= true;
$allowNoFile		= false;
$actuallySendEmail 	= true;
$debugBody 			= false; // nb: triggers read pixel
$overRideRecipients	= false;


// DEBUG BY EMAIL NEEDS DEBUG TO BE TRUE
if ($debugByEmail){$debug=true;}

ob_start();

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

// DEBUGGING	
if ($debug) {echo "<pre>";}
if ($debug) {echo "POST:<br>"; print_r($_POST);}
if ($debug) {echo "FILES:<br>"; print_r($_FILES);}
if ($debug) {echo "ENV:<br>"; print_r($_ENV);}
if ($debug) {echo "</pre>";}

// STORE RAW POST
$rawPost = mysqli_real_escape_string($db, print_r($_POST,true) );
$rawPost .= mysqli_real_escape_string($db, print_r($_FILES,true) );

// SET NAME OF FINAL DOWNLOADABLE
$auditionDate = date("m-d-Y g_ia",$now);
$downloadableFolderName = "MOODCASTER-" . $Title . "-" . $Role . "-" . $Name . "-" . $auditionDate;
$downloadableFolderName = str_replace(' ', '_', $downloadableFolderName);

// UNZIP, NORMALIZE VIDEOS, STITCH, RE-ZIP NEW FILES
	// create a tmp directory with timestamp-email as name
		$emailWithoutSymbols = preg_replace("/[^A-Za-z0-9 ]/", '', $Email);
		$sandbox = tempdir(null, $emailWithoutSymbols);
		if ($debug) {echo "SANDBOX : " .$sandbox, "\n";}
	
	// copy Zip_file into it
		$fieldname = 'Zip_file';
		if(isset($_FILES[$fieldname])){
			$file_name = $_FILES[$fieldname]['name']; 
			$uploadedFile = $_FILES['Zip_file']['tmp_name'];
			$tmpFileName = $sandbox . '/' . $file_name;
			move_uploaded_file( $uploadedFile , $tmpFileName );
		}
		
	// unzip file
		$zip = new ZipArchive;
		$res = $zip->open($tmpFileName);
	if ($res === TRUE){
		$zip->extractTo($sandbox);
		$zip->close();
		}	
				
	// stitch files
		$stitchedFilePath = stitchMP4sIn($sandbox);
		if ($debug) {echo "<BR>STITCHED FILE: $stitchedFilePath <BR>";}
		
	// upload stitched file
		if ($stitchedFilePath){
			$stitchAWS = uploadFile ($stitchedFilePath,$_ENV['AWSVIDBUCKET'],'');
			$stitchURL = $stitchAWS['ObjectURL'];
		if ($debug) {echo "<BR>STITCHED FILE URL: $stitchURL <BR>";}
		}	
		
	// REZIP
		// CREATES NEW TMP SUBDIR, MOVES FIXED_ VIDEO FILES, TRIMMING FIXED_ FROM FILE, 
		// MOVE IN STITCHED FILE RENAMED FINAL.MP4
		$newZipPath = rezip($sandbox, $downloadableFolderName );

// upload video files
if ($newZipPath){
	$zipAWS = uploadFile ($newZipPath,$_ENV['AWSVIDBUCKET'],$downloadableFolderName);
	$zipURL = $zipAWS['ObjectURL'];
	$zipFileSize = $_FILES['Zip_file']['size'];
	$file_good = ($zipAWS);
	}
	
if ($_FILES['Title_card']['size'] >1){
	$titleCardAWS = uploadFileFromFieldname('Title_card',$_ENV['AWSVIDBUCKET']);
	$titleCardURL = $titleCardAWS['ObjectURL'];
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
$sql .=" mc_zip_file_url		= '$zipURL', \n";
$sql .=" mc_stitch_file_url		= '$stitchURL', \n";
$sql .=" mc_zip_file_size		= '$zipFileSize', \n";
$sql .=" mc_title_card_text		= '$title_card_text', \n";
$sql .=" mc_title_card_url		= '$titleCardURL', \n";
$sql .=" mc_profile_url			= '$Profile_pic_url', \n";
$sql .=" mc_rawpost				= '$rawPost', \n";
$sql .=" mc_pk					= '$pk' \n";

if ($debug) echo "<BR><BR><pre>$sql</pre><br /><br />";

// ONLY INSERT INTO DATABASE IF THEY ATTACHED SOMETHING OR allowNoFile=true
if (($zipFileSize>0)||($allowNoFile)){
	$result = mysqli_query($db, $sql); 
	if ($debug) {echo mysqli_error($db);}
	$id = mysqli_insert_id($db);
	}
	
// CREATE SHORT URL TO DOWNLOAD PAGE, STORE IN DB
$s=base64_encode($id);
$shortDownloadLink = "http://d.moodcaster.com/$s";

$shortDownloadLink = $_ENV['DOMAIN'] . 'download.php?s='.$s;

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
		
		
		// explode Recipients_emails
		$recipARR = explode(',', $Recipients_emails);
		
		foreach ($recipARR as $eachEmail){
							$email = new \SendGrid\Mail\Mail(); 
							$email->setFrom($fromEmail, $fromName);
							$email->setSubject($subject);
							$email->addTo($eachEmail);
							$email->addContent("text/plain", "You have a new video audition submission sent from $fromName: $shortDownloadLink");
							$email->addContent("text/html", $body);
							$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
							try {
							    $response = $sendgrid->send($email);
							    if($debug){
								   echo "<pre>LINE 251:";
							    print $response->statusCode() . "\n";
							    print_r($response->headers());
							    print_r($response->body()) . "\n";
							    echo "(251)</pre>";
							    }
							} catch (Exception $e) {
							    if ($debug) {echo 'Caught exception: '. $e->getMessage() ."\n";}
								}	
								
		} //foreach	
		
	} // if actuallySendEmail
} // if zipsize	
if ($result){$em_good='1';}
if ($debug) echo "TO:$to<BR>";
if ($debug) echo "FROM:$fromEmail<BR>";
if ($debug) echo "HEADERS:$headers<BR>";

if ($debugBody) echo $body;

	
if ($debug){
	echo "\n\n\n";
	if ($auth_good) {echo "Authorized Key\n";}
	if ($file_good) {echo "File Uploaded\n";}	
	if ($db_good) {echo "Database Updated\n";}
	if ($em_good) {echo "Email Sent\n";}	
	}

//IF EVERYTHING WENT SMOOTHLY, REPORT SUCCESS TO APP
 if (($auth_good)&&($file_good)&&($db_good)&&($em_good)){
	//echo "success";
	echo $shortDownloadLink;
	}	
	
	
$debugText = ob_get_contents();	
ob_end_clean();	

if ($debugByEmail){
	echo $debugText;
	$email = new \SendGrid\Mail\Mail(); 
	$email->setFrom('hello@moodcaster.com', 'DEBUG REPORT');
	$email->setSubject('MOODCASTER SELF TAPE DEBUG REPORT');
	$email->addTo('karl@moodcaster.com');
	$email->addContent("text/plain", "need html");
	$email->addContent("text/html", $debugText);
	$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
}


?>

