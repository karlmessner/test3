<?PHP 
		
//composer, ENV Vars & mysql
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');



// get submission number

$s = mysqli_real_escape_string($db,$_GET['s']);

// check database to see if this submission has already been opened

$sql = "SELECT mc_read from mc_submissions WHERE mc_id = '$s' and mc_read >0";
$rsOPEN = mysqli_query($db,$sql); 
$opened = mysqli_num_rows($rsOPEN); 

if ($opened<1){
	// first time opening	
	$sql =  "UPDATE mc_submissions SET mc_read = '$now', mc_read_count = mc_read_count+1 WHERE mc_id='$s' LIMIT 1";
	mysqli_query($db,$sql);	
}
//Begin the header output
header( 'Content-Type: image/gif' );	 	
//Full URI to the image
$graphic_http = $_ENV['DOMAIN'] . 'media/images/pixel.gif';

//Get the filesize of the image for headers
$filesize = filesize( 'media/images/pixel.gif' );

//Now actually output the image requested (intentionally disregarding if the database was affected)
header( 'Pragma: public' );
header( 'Expires: 0' );
header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
header( 'Cache-Control: private',false );
header( 'Content-Disposition: attachment; filename="pixel.gif"' );
header( 'Content-Transfer-Encoding: binary' );
header( 'Content-Length: '.$filesize );
readfile( $graphic_http );
exit;	
?>