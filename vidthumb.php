<?PHP
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');

//ERROR REPORTING
error_reporting(E_ALL);
ini_set("display_errors", 1);
$logging = true;
$debug = true;


// LOAD FUNCTIONS
require('includes/functions.php');

// INIT VARS
$p=''; // private key
$s=''; // submission id
$o=''; // override flag to re process over existing thumbs (overwrites the database, doesn't delete the files)
	
// THUMB CREATOR FOR UPLOADER
// THIS SCRIPT ACCEPTS A SUBMISSION ID, AND A PK
// PULLS THE LIST OF FILES ASSOCIATED WITH IT
// LOOP THROUGH THEM:
// PULL THE FILE DOWN FROM S3
// IF IT'S A JPG, CREATE SMALL IMAGE
// CHECK TO SEE IF THEY ARE ROTATED
// EXTRACT A THUMBNAIL
// ROTATE IT IF NECESSARY
// UPLOAD TO S3
// UPDATE DATABASE THAT THE FILE IS IS UPLOADED
// UPDATE LOG DATABASE THAT IT'S DONE
	
// SANITIZE INPUT
if (isset($_GET['s'])) {$s = mysqli_real_escape_string($db, $_GET['s']);}
if (isset($_GET['p'])) {$p = mysqli_real_escape_string($db, $_GET['p']);}
if (isset($_GET['o'])) {$o = mysqli_real_escape_string($db, $_GET['o']);}

// PRIVATE KEY
$goodKey = $_ENV['GOODKEY'];

if ($p != $goodKey){
// LOGGING
$logMessage = "UNAUTHORIZED ATTEMPT: BAD PRIVATE KEY";
if ($logging){logStatus($id,$logMessage);}	
}else{
	
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
		$baseFileName = basename($mcf_url);
		$iov = imgOrVid($mcf_url);
		if ($debug){echo "*****<BR>$mcf_url <BR>$iov <br/>$baseFileName<BR>";}
		
		if ($iov == 'img'){
			
			// RESIZE TO MAX WIDTH 200
			$im = file_get_contents($mcf_url);
			$img = new Imagick();
			$img -> readImageBlob($im);
			$img -> thumbnailImage(200,400,true);
			$img -> setImageFormat('jpeg');
// 			$img -> setImageCompressionQuality(51);
			$tempThumbFile = tempnam(sys_get_temp_dir(), "tempfilename");
			$img -> writeImage($tempThumbFile);
			
			if ($debug){echo "<img src='$tempThumbFile' /><BR>";}
		
/*
		$s3 = new Aws\S3\S3Client([
			'region'  => 'us-east-1',
			'version' => 'latest',
			'credentials' => [
				'key'    => "$AWSKEY",
				'secret' => "$AWSSECRET",
			]
		]);		
		$result = $s3->putObject([
			'Bucket' => $AWSVIDBUCKET,
			'Key'    => $file_name,
			'SourceFile' => $tempThumbFile,
			'ACL' => 'public-read'		
		]);
*/







			
		} //image
		if ($iov == 'vid'){}
			
		
		
		
		
		
		
	}// while
} // IF GOODKEY
