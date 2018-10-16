<?PHP


// REZIP CONTENTS OF SANDBOX
// include and rename FIXED_ files and stitched file
function rezip($sandbox){	
	// create a subdir
	$dir = $sandbox . '/final';
	mkdir($dir);

	// move the stitched file into it renaming it final
	$oldStitchedFile = $sandbox .'/'.'stitched.mp4';
	$newStitchedFile = $sandbox .'/final/FINAL.mp4';
	rename($oldStitchedFile, $newStitchedFile);
	
	// RENAME AND MOVE ALL FIXED VIDEOS INTO FINAL
	$files1 = scandir($sandbox);	
	foreach ($files1 as $file) {
		$fileinfo = new SplFileInfo($file);
		$filename = $fileinfo->getFilename();	
		
		if (substr($filename, 0,6) == 'FIXED_'){
			$oldFile = $sandbox .'/'.$filename;
			$truncName = substr($filename, 6);
			$newFile = $sandbox .'/final/'.$truncName;
			rename($oldFile, $newFile);
		}	
	}
	
	// ZIP FINAL
	$finalDir = $sandbox .'/final';
	$zipPath = zipfolder($finalDir,$sandbox);
	//RETURN PATH OF FINAL
	return $zipPath;
}



function zipfolder($folder_to_zip,$sandbox){
	// Get real path for our folder
	$rootPath = realpath($folder_to_zip);
	
	// Initialize archive object
	$zipFile = $sandbox . '/FINAL.zip';
	$zip = new ZipArchive();
	$zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
	
	// Create recursive directory iterator
	/** @var SplFileInfo[] $files */
	$files = new RecursiveIteratorIterator(
	    new RecursiveDirectoryIterator($rootPath),
	    RecursiveIteratorIterator::LEAVES_ONLY
	);
	
	foreach ($files as $name => $file)
	{
	    // Skip directories (they would be added automatically)
	    if (!$file->isDir())
	    {
	        // Get real and relative path for current file
	        $filePath = $file->getRealPath();
	        $relativePath = substr($filePath, strlen($rootPath) + 1);
	
	        // Add current file to archive
	        $zip->addFile($filePath, $relativePath);
	    }
	}
	
	// Zip archive will be created only after closing object
	$zip->close();
	return  $zipFile;
	}


	
// upload to AWS directly from $_FILES	
function uploadFileFromFieldname ($fieldname,$bucket){	
	// READ ENVIRONMENT VARS
	
	$awsKey=$_ENV['AWSKEY'];
	$awsSecret=$_ENV['AWSSECRET'];
	$result = '';
	if(isset($_FILES[$fieldname])){
		$file_name = $_FILES[$fieldname]['name']; 
		$file_name = normalizeString ($file_name);
		//prepend timestamp to avoid overwrite
		$file_name = time() . "_" . $file_name;
		$file_name = urlencode($file_name);		  
		$temp_file_location = $_FILES[$fieldname]['tmp_name']; 
		
		$s3 = new Aws\S3\S3Client([
			'region'  => 'us-east-1',
			'version' => 'latest',
			'credentials' => [
				'key'    => "$awsKey",
				'secret' => "$awsSecret",
			]
		]);		
		$result = $s3->putObject([
			'Bucket' => $bucket,
			'Key'    => $file_name,
			'SourceFile' => $temp_file_location,
			'ACL' => 'public-read'		
		]);
	} // if
	return $result;	
} // function uploadFile


// upload file to AWS 	
function uploadFile ($pathToStitchedFile,$bucket,$folderName){	
	// READ ENVIRONMENT VARS
	$awsKey=$_ENV['AWSKEY'];
	$awsSecret=$_ENV['AWSSECRET'];
	$result = '';
	
	if(isset($pathToStitchedFile)){
		// EXTRACT 	filename.ext from /path/to/filename.ext
		$path_parts = pathinfo($pathToStitchedFile);
		$file_name =  $path_parts['basename'] ; 
		$file_name = normalizeString ($file_name);
		
		//prepend timestamp to avoid overwrite
		$file_name = time() . "_" . $file_name;
		
		
		// OVERRIDE FILE NAME IF FOLDERNAME PRESENT
		if (strlen($folderName)>1){
			$file_name=$folderName;
			}
		
		$file_name = urlencode($file_name);
		  
		$s3 = new Aws\S3\S3Client([
			'region'  => 'us-east-1',
			'version' => 'latest',
			'credentials' => [
				'key'    => "$awsKey",
				'secret' => "$awsSecret",
			]
		]);		

		$result = $s3->putObject([
			'Bucket' => $bucket,
			'Key'    => $file_name,
			'SourceFile' => $pathToStitchedFile,
			'ACL' => 'public-read'		
		]);
	} // if
		return $result;	
} // function uploadFile



/**
 * Creates a  unique temporary directory, with specified parameters,
 * that does not already exist (like tempnam(), but for dirs).
 *
 * Created dir will begin with the specified prefix, followed by timestamp.
 *
 * @link https://php.net/manual/en/function.tempnam.php
 *
 * @param string|null $dir Base directory under which to create temp dir.
 *     If null, the default system temp dir (sys_get_temp_dir()) will be
 *     used.
 * @param string $prefix String with which to prefix created dirs.
 * @param int $mode Octal file permission mask for the newly-created dir.
 *     Should begin with a 0.
 * @param int $maxAttempts Maximum attempts before giving up (to prevent
 *     endless loops).
 * @return string|bool Full path to newly-created dir, or false on failure.
 */
function tempdir($dir = null, $prefix = 'tmp_', $mode = 0700, $maxAttempts = 1000)
{
    /* Use the system temp dir by default. */
    if (is_null($dir))
    {
        $dir = sys_get_temp_dir();
    }

    /* Trim trailing slashes from $dir. */
    $dir = rtrim($dir, '/');

    /* If we don't have permission to create a directory, fail, otherwise we will
     * be stuck in an endless loop.
     */
    if (!is_dir($dir) || !is_writable($dir))
    {
        return false;
    }

    /* Make sure characters in prefix are safe. */
    if (strpbrk($prefix, '\\/:*?"<>|') !== false)
    {
        return false;
    }

    /* Attempt to create a random directory until it works. Abort if we reach
     * $maxAttempts. Something screwy could be happening with the filesystem
     * and our loop could otherwise become endless.
     */
    $attempts = 0;
    do
    {
        $path = sprintf('%s/%s%s', $dir, $prefix, time()); 
    } while (
        !mkdir($path, $mode) &&
        $attempts++ < $maxAttempts
    );

    return $path;
}

// NORMALIZE FILE NAMES
function normalizeString ($str = '')
{
    $str = strip_tags($str); 
    $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
    $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
    $str = strtolower($str);
    $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
    $str = htmlentities($str, ENT_QUOTES, "utf-8");
    $str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
    $str = str_replace(' ', '-', $str);
    $str = rawurlencode($str);
    $str = str_replace('%', '-', $str);
    return $str;
}


// STITCHING
function stitchMP4sIn($id,$dirPath){
	global $debug;
	global $logging;
	
	// CONSTANTS
	$ffmpegPath = $_ENV['FFMPEGPATH']; 
	$ffprobePath = $_ENV['FFPROBEPATH']; 
	$stitchListFileName = "stitch.txt";
	$stitchedFileName = "stitched.mp4";
	$targetWidth = 960;
	$targetHeight = 540;
	$targetFPS = 30;
	$targetKeyFramesInterval = 15;
	$dirPath .= '/';
	$textFileContents = '';

	// READ ALL MP4 FILES IN DIRECTORY AND PROCESS THEM
	$files1 = scandir($dirPath);
	$filecount = count($files1) - 2;
if ($debug) {echo "f:stitchMP4sIn  Looping through $filecount files in dir ...<BR>";}

// LOGGING
$logMessage = "WORKER: f:stitchMP4sIn  Looping through $filecount files in dir.";
if ($logging){logStatus($id,$logMessage);}


/*
// CALCULATE QUALITATIVE PERCENTAGE INCREMENTS
// if there are 5 files, each is 20%, so the incremnets of completion are 0,20,40,80,100
$eachPortion = round(98/$filecount);// so when we're done processsing, we're at 98%
$percentDone = 0;
*/


$i=0; //which file




// LOOP THROUGH FILES
	foreach ($files1 as $file) {

		if ($debug) {echo "START OF file: $file ...<BR>";}
		
		$fileinfo = new SplFileInfo($file);
		if ($debug) {echo "fileinfo: <pre>"; print_r($fileinfo); echo "</pre><BR>";}
		$extn = $fileinfo->getExtension();	
			if ($debug) {echo "ext: $extn...<BR>";}
		if ($extn == 'mp4'){
			if ($debug) {echo "Trying to fix: $file...<BR>";}
			
// LOGGING
$logMessage = "WORKER: Identified mp4 to process: $file";
if ($logging){logStatus($id,$logMessage);}

// UPDATE PERCENTAGE
$i++;
$message = "processing $i of $filecount";
updatePercentage($id,$percentDone);


			
			// NORMALIZE FILENAME
			// GET BASE FILENAME WITHOUT EXTENSION
			$path_parts = pathinfo($file);
			$filenameOnly = $path_parts['basename'];	
			$newFileName = normalizeString ($filenameOnly);
			$oldFilePath = $dirPath . $file;
			$newFilePath = $dirPath . $newFileName;
			rename($oldFilePath, $newFilePath);
			$file = $newFilePath;
			if ($debug){echo "(from function stitchMP4sIn): old base filename: $oldFilePath <BR>Normalized to: $newFilePath <BR>";}
			

			$resultFileName = 'FIXED_'.$newFileName;
			$resultFile = $dirPath . $resultFileName;
			$pathToFile = $dirPath .'/'.$newFileName;
			fixVideo($pathToFile,$resultFile,$ffmpegPath,$ffprobePath,$targetWidth,$targetHeight,$targetFPS,$targetKeyFramesInterval);	
			// ADD FILE TO THE text LIST	
			$textFileContents .= "file '" . $resultFileName . "' \n";
						
		}	
		
		if ($debug) {echo "END OF $file ...<BR><BR>";}
		
		
		
		
		
	}

// LOGGING
$logMessage = "WORKER: Creating text file of videos to stitch.";
if ($logging){logStatus($id,$logMessage);}

	
	// CREATE BLANK TEXT FILE
	if ($debug) {echo "creating text file of movies to stitch...<BR>";}
	$textFileName = $dirPath . $stitchListFileName;
	$theTextFile = fopen($textFileName, "w");
	fwrite($theTextFile, $textFileContents);
	fclose($theTextFile);
	
// LOGGING
$logMessage = "WORKER: Beginning stitching.";
if ($logging){logStatus($id,$logMessage);}
	
	
	// STITCH FILES FROM TEXT FILE
	$stitchedFilePath = $dirPath . $stitchedFileName;
	$ffmpegCommand = "-ss 00:00:00.5 -f concat -i ".$dirPath . $stitchListFileName." -c copy  " . $stitchedFilePath . ' ' ;
	if ($debug) {echo "trying to execute:$ffmpegPath $ffmpegCommand<BR>";}
	$ffmpegExec=shell_exec($ffmpegPath .' '. $ffmpegCommand); 
	
// LOGGING
$logMessage = "WORKER: done stitching.";
if ($logging){logStatus($id,$logMessage);}

	return $stitchedFilePath;

	
	} //function

// FUNCTION
function fixVideo($file,$resultFile,$ffmpegPath,$ffprobePath,$targetWidth,$targetHeight,$targetFPS,$targetKeyFramesInterval) {
	global $debug;
	
	
	
	
	
	
	
	

	// DO WE NEED TO ROTATE
	// STEP 1: CHECK FOR ROTATION META DATA
	$ffprobeCommand =  "  -loglevel error -select_streams v:0 -show_entries stream_tags=rotate -of default=nw=1:nk=1 -i ". $file ;
	$rotationCheck1=shell_exec($ffprobePath .  ' '. $ffprobeCommand); 
	$rotationCheck1 = ($rotationCheck1>0);
	
	// STEP 2: CHECK FOR NEGATIVE CENTER SQUARE IN DISPLAY MATRIX
	// not sure why this works, but it solved the selfie-camera issue	
	// hypothesis: if center square is negative, flip it if not flipped by Step 1
	
	// CHECK FOR DISPLAYMATRIX
	$ffprobeCommand =  " -loglevel error -select_streams v:0 -show_entries side_data=displaymatrix -of default=nw=1:nk=1 ". $file ;
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
	$rotation_conversion = ($needRotation) ? ',hflip,vflip' : ' ' ;

	// NORMALIZE TO H.264
	$h264_conversion = " -bsf:v h264_mp4toannexb -map 0:v -map 0:a  -vcodec libx264 " ;
	
	// NORMALIZE STREAM PARAMETERS TO PREPARE FOR CONCAT
	$normalizeAndScale= " -c:a aac -ar 48000 -ac 2 -vf \"scale=".$targetWidth . "x" . $targetHeight . $rotation_conversion .  "\" -c:v libx264 -profile:v baseline -video_track_timescale 60000 -time_base 1001/60000 -r $targetFPS -g $targetKeyFramesInterval";
	
	// ASSEMBLE COMMAND
	$ffmpegCommand = '    -i ' . $file .'  '. $h264_conversion .' '.  $normalizeAndScale  . ' '  .  $resultFile;
	
	// EXECUTE COMMAND
	$ffmpegExec=shell_exec($ffmpegPath .' '. $ffmpegCommand); 
	
	
		if ($debug){echo "(from function fixVideo): ffmpeg command: $ffmpegPath  $ffmpegCommand <BR>";}

	}//function
	
	
	
	
function createShortLink($id){
	$seed = str_split('abcdefghijklmnopqrstuvwxyz'
                     .'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                     .'0123456789!@#$%^&*()'); // and any other characters
    shuffle($seed); // probably optional since array_is randomized; this may be redundant
    $rand = '';
    foreach (array_rand($seed, 2) as $k) $rand .= $seed[$k];
	$slug = $rand . $id;
	$code=base64_encode($slug);
	return $code;

}	


function decodeShortLink($code){
	$slug = base64_decode($code);
	$id = substr($slug, 2);
	return $id;
}



function logStatus($sub,$msg){
	global $db;
	global $debug;
	$thisInstant = microtime();
	$logSQL = "INSERT INTO mc_log SET ml_microtime='$thisInstant', ml_submissionId='$sub', ml_message='$msg'";
	mysqli_query($db, $logSQL);
	if ($debug){
		echo "<BR>";
		echo "<BR>";
		echo "****************************************<BR>";
		echo "*   LOG: $sub - $msg <BR>";
		echo "****************************************<BR>";
		echo "<BR>";
	}
	
}

function updatePercentage($id,$percentDone){
	global $db;
	$updateSQL = "UPDATE mc_submissions SET mc_status='$percentDone' WHERE mc_id='$id' LIMIT 1";
	mysqli_query($db, $updateSQL);

}


	
	
?>
