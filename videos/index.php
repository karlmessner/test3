<?PHP
require '../vendor/autoload.php';
require '../env.php';
// print_r($_ENV);

error_reporting(E_ALL);
ini_set("display_errors", 1);




$files1 = scandir('./');
foreach ($files1 as $file) {
	$fileinfo = new SplFileInfo($file);
	$extn = $fileinfo->getExtension();	
	if ($extn == 'mp4'){
	fixVideo($file);	
	}	
}

//fixVideo('outside.mp4');

// FUNCTIONS
	
function fixVideo($file) {

$ffmpegPath = $_ENV['FFMPEGPATH']; 
$ffprobePath = $_ENV['FFPROBEPATH']; 



echo "<video src='$file' width='100'></video><br>";
echo "$file<br>";



$resultFile = 'FIXED_'.$file;

// DO WE NEED TO ROTATE
$ffprobeCommand =  " -loglevel error -select_streams v:0 -show_entries stream_tags=rotate -of default=nw=1:nk=1 -i ". $file ;
$rotation=shell_exec($ffprobePath .  ' '. $ffprobeCommand); 
echo "<pre>";print_r($rotation);echo "</pre>";


// IS IT NOT H.264
$ffprobeCommand =  "  -v error -select_streams v:0 -show_entries stream=codec_name \
  -of default=noprint_wrappers=1:nokey=1 ". $file ;
$vidCodec=shell_exec($ffprobePath .  ' '. $ffprobeCommand); 
echo "<pre>";print_r($vidCodec);echo "</pre>";





// ROTATE AND SAVE
/*
$ffmpegCommand = ' -i ' . $file . '  -vf "transpose=2,transpose=2" videoSandbox/'.$resultFile;
$ffmpegExec=shell_exec($ffmpegPath .  ' '. $ffmpegCommand); 
echo $ffmpegPath .$ffmpegCommand;
*/



	    
	    


}//function

/*
function ffmpeg_get_rotation($fileName)
{
    $ffprobe = FFMpeg\FFProbe::create(array(
'ffmpeg.binaries' => '/usr/local/bin/ffmpeg',
'ffprobe.binaries' => '/usr/local/bin/ffprobe',
'timeout' => 3600, // The timeout for the underlying process
'ffmpeg.threads' => 12, // The number of threads that FFMpeg should use
), $logger);

//     return $ffprobe->streams($fileName)->videos()->first();
    $tags= $ffprobe->streams($fileName)->videos()->first()->get("tags");
    return $tags['rotate'];
}
$dur = ffmpeg_get_rotation("L.mov");


//echo "<pre>";print_r($dur);echo "</pre>";
// echo $dur->get('tags')->get('rotate');

//ffmpeg -i input.mp4 -c:a copy output.mp4



//CONCATENATE
// In order to instantiate the video object, you HAVE TO pass a path to a valid video file.
// We recommand that you put there the path of any of the video you want to use in this concatenation.

$ffmpeg = FFMpeg\FFMpeg::create(array(
'ffmpeg.binaries' => '/usr/local/bin/ffmpeg',
'ffprobe.binaries' => '/usr/local/bin/ffprobe',
'timeout' => 3600, // The timeout for the underlying process
'ffmpeg.threads' => 12, // The number of threads that FFMpeg should use
), $logger);


$video = $ffmpeg->open( 'L.mov' );

$format = new FFMpeg\Format\Video\X264();
$format->setAudioCodec("libmp3lame");

$video
    ->concat(array('L.mov', 'R.mov'))
    ->saveFromDifferentCodecs($format, 'stitch.mov');
*/

// '/usr/local/bin/ffmpeg' '-y' '-i' 'appL.mp4' '-threads' '12' '-vcodec' 'libx264' '-acodec' 'aac' '-b:v' '1000k' '-refs' '6' '-coder' '1' '-sc_threshold' '40' '-flags' '+loop' '-me_range' '16' '-subq' '7' '-i_qfactor' '0.71' '-qcomp' '0.6' '-qdiff' '4' '-trellis' '1' '-b:a' '128k' '-pass' '1' '-passlogfile' '/var/tmp/ffmpeg-passes5ba26591b151c99joc/pass-5ba26591b15e3' 'FIXED_appL.mp4'