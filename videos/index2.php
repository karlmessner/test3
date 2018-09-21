<?PHP
require '../vendor/autoload.php';
error_reporting(E_ALL);
ini_set("display_errors", 1);

$output = shell_exec("date"); echo $output;

print_r($_ENV);
// on heroku, the path is /app/vendor/ffmpeg/ffmpeg
// on localhost the path is /usr/local/bin/ffmpeg

$ffmpegPath = $_ENV['FFMPEGPATH']; echo $ffmpegPath;

$ffmpegCommand = ' -i appL.mp4';

$codecArr=shell_exec($ffmpegPath. ' ' .$ffmpegCommand ); 

/*
echo "<pre>$codecArr";

$file="appL.mp4";
$config = array(
'timeout' => 3600, // The timeout for the underlying process
'ffmpeg.threads' => 12, // The number of threads that FFMpeg should use
);

// create the ffmpeg object
$ffmpeg = FFMpeg\FFMpeg::create($config, null);

// open video file
$video = $ffmpeg->open($file);
$format = new FFMpeg\Format\Video\x264();
$format->setAudioCodec("aac");
$format->setAdditionalParameters(array('-strict', '-2'));
$video->save($format,'fixed-appL.mp4');
*/


