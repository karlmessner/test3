<?PHP
require '../vendor/autoload.php';
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
$config = array(
'ffmpeg.binaries' => '/usr/local/bin/ffmpeg',
'ffprobe.binaries' => '/usr/local/bin/ffprobe',
'timeout' => 3600, // The timeout for the underlying process
'ffmpeg.threads' => 12, // The number of threads that FFMpeg should use
);

$resultFile = 'FIXED_'.$file;

// create the ffmpeg object
$ffmpeg = FFMpeg\FFMpeg::create($config, null);

// open video file
$video = $ffmpeg->open($file);

// get the first video stream
$videostream = $ffmpeg->getFFProbe()
                      ->streams($file)
                      ->videos()
                      ->first();

//echo "<pre>";print_r($videostream);
                      

if (!$videostream instanceof FFMpeg\FFProbe\DataMapping\Stream) {
    throw new \Exception('No stream given'); 
    } else {
	    echo "<video src='$file' width='100'></video><br>";
	    echo "$file<br>";
	    
	    echo "<pre>";print_r($videostream);
	    
	    

	if ($videostream->has('tags')) { 
		//echo "has tags<BR>";
	
// MUST WE ROTATE?	
		$tags = $videostream->get('tags');
				if (isset($tags['rotate'])) { 
			echo "has rotate" . $tags['rotate'] . "<BR>" ;
		
			if ($tags['rotate'] != 0) { 
				echo "rotate not 0<BR>";
			
				switch($tags['rotate']) {
				    case 270:
				        $angle = FFMpeg\Filters\Video\RotateFilter::ROTATE_270;
				        break;
				    case 180:
				        $angle = FFMpeg\Filters\Video\RotateFilter::ROTATE_180;
				        break;
				    case 90:
				        $angle = FFMpeg\Filters\Video\RotateFilter::ROTATE_90;
				        break;
				}
				
				$video->filters()
			      ->rotate($angle); echo "rotating<br>";

			} // if ($tags['rotate']	
			
			
// MUST WE REENCODE TO H.264?
		if (isset($tags['encoder'])) {

		echo "encoding: " . $tags['encoder'];
			} 
				
			$format = new FFMpeg\Format\Video\X264();
			//$format->setAudioCodec("aac");
			//$video->save($format,$resultFile );
		} // if (isset($tags['rotate']
	} // if ($videostream->has('tags')
	echo "<BR><BR>";
} // if $videostream instanceof

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