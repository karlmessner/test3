<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

$fontsize = $_GET['s'];
$imsize = 1.5* $fontsize;
$text = $_GET['t'];
$xparent = false;
$y=.8*$imsize; // allow for decenders
	
	
// Set the content-type
header('Content-Type: image/png');

// Create the image
$im = imagecreatetruecolor(350, $imsize);

// Create some colors
$white = imagecolorallocate($im, 255, 255, 255);
$teal = imagecolorallocate($im, 68, 217, 230);
$bg = imagecolorallocate($im, 182, 27, 70);
$black = imagecolorallocate($im, 0, 0, 0);
imagefilledrectangle($im, 0, 0, 350, $imsize, $white);

// Make the background transparent
if ($xparent) imagecolortransparent($im, $bg);


// Replace path by your own font path
$font = 'media/fonts/Chivo-BlackItalic.ttf';

// Add the text
imagettftext($im, $fontsize, 0, 0, $y, $teal, $font, $text);

// Using imagepng() results in clearer text compared with imagejpeg()
imagepng($im);
imagedestroy($im);
?>