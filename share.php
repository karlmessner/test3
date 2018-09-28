<?PHP 
// THIS APPLET RECORDS THAT SOMEONE HIT SHARE ON A DOWNLOAD PAGE
// TRIGGERED BY JQUERY/AJAX ON THE DOWNLOAD PAGE

//composer, ENV Vars & mysql
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');

// get submission number
$s = mysqli_real_escape_string($db,$_GET['s']);

// check database to see if this submission has already been opened


	// first time opening	
	$sql =  "UPDATE mc_submissions SET mc_share = '$now' WHERE mc_id='$s' LIMIT 1"; 
	mysqli_query($db,$sql);	

?>