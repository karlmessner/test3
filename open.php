<?PHP include('../includes/db2.php');?><?PHP 
// this script attempts to track opens
// it records date of first open (checks first)

// get submission number

$s = mysql_real_escape_string($_GET['s']);

// check database to see if this submission has already been opened

$sql = "SELECT mc_read from mc_submissions WHERE mc_id = '$s' and mc_read >0";
$rsOPEN = mysql_query($sql);
$opened = mysql_num_rows($rsOpen);

if ($opened<1){
	// first time opening	
	$sql =  "UPDATE mc_submissions SET mc_read = '$now' WHERE mc_id='$s' LIMIT 1";
	mysql_query($sql);	
}
//Begin the header output
header( 'Content-Type: image/gif' );	 	
//Full URI to the image
$graphic_http = 'http://www.actorslaunchpad.com/moodcaster/media/images/pixel.gif';

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