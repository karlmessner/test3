<?PHP
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');


// THUMB CREATOR FOR UPLOADER
// THIS SCRIPT ACCEPTS A SUBMISSION ID, AND A PK
// PULLS THE LIST OF FILES ASSOCIATED WITH IT
// LOOP THROUGH THEM. IF A VID FILE"
// PULL THE FILE DOWN FROM S3
// CHECK TO SEE IF THEY ARE ROTATED
// EXTRACT A THUMBNAIL AND A GIF PREV
// ROTATE IT IF NECESSARY
// UPLOAD TO S3
// UPDATE DATABASE THAT THE FILE IS IS UPLOADED
// UPDATE LOG DATABASE THAT IT'S DONE


$ffmpegPath = $_ENV['FFMPEGPATH']; 
$ffprobePath = $_ENV['FFPROBEPATH']; 


//ERROR REPORTING
/*
error_reporting(E_ALL);
ini_set("display_errors", 1);
*/


$logging = true;
$debug = false;


// LOAD FUNCTIONS
require('includes/functions.php');

// INIT VARS
$p=''; // private key
$s=''; // submission id	
	
// SANITIZE INPUT
if (isset($_GET['s'])) {$s = mysqli_real_escape_string($db, $_GET['s']);}
if (isset($_GET['p'])) {$p = mysqli_real_escape_string($db, $_GET['p']);}

$id=$s; // for logging

// PRIVATE KEY
$goodKey = $_ENV['GOODKEY'];

if ($p != $goodKey){
// LOGGING
$logMessage = "UNAUTHORIZED ATTEMPT: BAD KEY";
if ($logging){logStatus($id,$logMessage);}	
}else{
$logMessage = "vidthumb.php: Starting sub# $s";
if ($logging){logStatus($id,$logMessage);}	
	
	function imgOrVid($fn){
	// if it's a video, and the first one make it the poster
	$videoArr = ['mp4','m4v','avi','mpg','mpv'];
	$imgArr = ['jpg','jpeg','gif','png'];
	$extArr = explode('.', $fn);
	$ext = end($extArr);
	$isVid = (in_array($ext, $videoArr));
	$isImg = (in_array($ext, $imgArr));
	$result='';
	if ($isVid) $result = "vid";
	if ($isImg)	$result =  "img";
	return $result;
	}
	

	// PULL ALL FILES ASSOCIATED
	$allFiles = '';
	$firstVid = '';
	$sql = "SELECT * from mc_files where mcf_sub='$s' order by mcf_date";
	$rsFILES = mysqli_query($db,$sql); echo mysqli_error($db);
	while ($thisFILE = mysqli_fetch_array($rsFILES)){
		extract($thisFILE);
		
/*********  LOG *********/		
$logMessage = "vidthumb.php: looking at: $mcf_url";
if ($logging){logStatus($id,$logMessage);}	
/*********  LOG *********/		
		
		$baseFileName = basename($mcf_url);
		
		// create thumb name: convert  name.mp4 to name_mp4 then add _THUMB.jpg
		$thumbBaseSlug = str_replace('.', '_', $baseFileName);
		$thumbFileName = $thumbBaseSlug . "_THUMB.jpg";
		$GIFFileName = $thumbBaseSlug . "_PREV.gif";
		
		$iov = imgOrVid($mcf_url);
		if ($debug){echo "*****<BR>$mcf_url <BR>$iov <br/>$baseFileName<BR>";}
		
		if ($iov == 'vid'){
		
/*********  LOG *********/		
$logMessage = "vidthumb.php: PROCESSING: $mcf_url";
if ($logging){logStatus($id,$logMessage);}	
/*********  LOG *********/		
		
			
			// PULL VIDEO FROM S3
			
			$sandbox = tempdir();

			
			$tempVidFile = $sandbox.'/'.$baseFileName;
			$tempThumbFile = $sandbox.'/'.$thumbFileName;
			$tempGIFFile = $sandbox.'/'.$GIFFileName;
			
			
			
			$bucket = $_ENV['AWSVIDBUCKET'];
			$keyname =  basename($mcf_url);
			$awsKey=$_ENV['AWSKEY'];
			$awsSecret=$_ENV['AWSSECRET'];
			$result = '';
			if ($debug) {echo "downloading $keyname to $tempVidFile <BR>";}
	
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
				'SaveAs' => $tempVidFile
			]);






			// DO WE NEED TO ROTATE
			// STEP 1: CHECK FOR ROTATION META DATA
			$ffprobeCommand =  "  -loglevel error -select_streams v:0 -show_entries stream_tags=rotate -of default=nw=1:nk=1 -i ". $tempVidFile ;
			$rotationCheck1=shell_exec($ffprobePath .  ' '. $ffprobeCommand); 
			$rotationCheck1 = ($rotationCheck1>0);
			
			// STEP 2: CHECK FOR NEGATIVE CENTER SQUARE IN DISPLAY MATRIX
			// not sure why this works, but it solved the selfie-camera issue	
			// hypothesis: if center square is negative, flip it if not flipped by Step 1
			
			// CHECK FOR DISPLAYMATRIX
			$ffprobeCommand =  " -loglevel error -select_streams v:0 -show_entries side_data=displaymatrix -of default=nw=1:nk=1 ". $tempVidFile ;
			$rotation2=shell_exec($ffprobePath .  ' '. $ffprobeCommand); 
			
			// EXTRACT DISPLAYMATRIX INTO ARRAY, ASSIGN TO STRING, REPLACE SPACE WITH UNDERSCORE, EXPLODE ON UNDERSCORE AND FIND 7TH ELEMENT
			$dm = print_r($rotation2,true);
			// REPLACE ANY WHITESPACE WITH UNDERSCORE
			$dm = preg_replace('/\s+/', '_', trim($dm));
			$dmArr = explode('_', $dm);
			$centerSquare = $dmArr[6];
			$rotationCheck2 = ($centerSquare<0);
			
			// IF EITHER CHECK BUT NOT BOTH IS TRUE, ADD ROTATION COMPONENT TO COMMAND
			$needRotation = ( ($rotationCheck1>0) xor ($rotationCheck2) ) ? 1:0;
			$rotationCmd = ($needRotation) ?" -vf hflip,vflip " : "";		
			$rotationPart = ($needRotation) ?" hflip,vflip " : ""; // for preview gif
			
			if ($debug) {echo ($needRotation) ?"** NEED ROTATION **<BR>" :"** no rotation needed **<BR>";}
			
			
			// ASSEMBLE FFMPEG THUMBNAIL COMMAND
			$ffmpegCommand = " -i $tempVidFile $rotationCmd $tempThumbFile";
			if ($debug) {echo "$ffmpegPath  $ffmpegCommand<BR>";}
			
			// EXECUTE COMMAND
			$ffmpegExec=shell_exec($ffmpegPath .' '. $ffmpegCommand); 

		
/*********  LOG *********/		
$logMessage = "vidthumb.php: ffmpeg: $ffmpegCommand";
if ($logging){logStatus($id,$logMessage);}	
/*********  LOG *********/		
		
			
			// UPLOAD THUMB TO S3
				$thumbResult = $s3->putObject([
					'Bucket' => $bucket,
					'Key'    => $thumbFileName,
					'SourceFile' => $tempThumbFile,
					'ACL' => 'public-read'		
				]);
				
				
				
				




				
			// ASSEMBLE FFMPEG GIF PREVIEW COMMAND
			$ffmpegCommand = " -i $tempVidFile  -r 10 -ss 0 -t 5 -vf $rotationPart scale=-1:63 $tempGIFFile -hide_banner";
			if ($debug) {echo "$ffmpegPath  $ffmpegCommand<BR>";}
			
			// EXECUTE COMMAND
			$ffmpegExec=shell_exec($ffmpegPath .' '. $ffmpegCommand); 

		
/*********  LOG *********/		
$logMessage = "vidthumb.php: ffmpeg: $ffmpegCommand";
if ($logging){logStatus($id,$logMessage);}	
/*********  LOG *********/		
		
			
			// UPLOAD THUMB TO S3
				$GIFResult = $s3->putObject([
					'Bucket' => $bucket,
					'Key'    => $GIFFileName,
					'SourceFile' => $tempGIFFile,
					'ACL' => 'public-read'		
				]);




				
				
				
				
				
				
				
				
				
				
				
			
			// UPDATE DATABASE
			$thumbURL = $thumbResult['ObjectURL'];
			if ($debug) {echo "RESULTING THUMB: $thumbURL <br><img src='$thumbURL' /><BR>";}

			$GIFURL = $GIFResult['ObjectURL'];
			if ($debug) {echo "RESULTING GIF: $GIFURL <br><img src='$GIFURL' /><BR>";}

			$sql = "UPDATE mc_files SET mcf_thumb_url ='$thumbURL', mcf_gif_url ='$GIFURL' WHERE mcf_id='$mcf_id' LIMIT 1";
			mysqli_query($db, $sql);

		} //iov=vid
		
	}// while
	
$logMessage = "vidthumb.php: DONE: sub# $s";
if ($logging){logStatus($id,$logMessage);}	
echo "Done.";
} // IF GOODKEY
