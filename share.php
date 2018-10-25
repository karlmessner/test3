<?PHP 
// THIS APPLET RECORDS THAT SOMEONE HIT SHARE ON A DOWNLOAD PAGE
// TRIGGERED BY JQUERY/AJAX ON THE DOWNLOAD PAGE

//composer, ENV Vars & mysql
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');

// get submission number
$s = mysqli_real_escape_string($db,$_GET['s']);
$n = mysqli_real_escape_string($db,$_GET['n']);

// check database to see if this submission has already been opened

if ((is_null($n)) || ($n<1)){
	// first time opening	
	$sql =  "UPDATE mc_submissions SET mc_share = '$now',mc_share_count = mc_share_count+1 WHERE mc_id='$s' LIMIT 1"; 
	mysqli_query($db,$sql);	
}
?>