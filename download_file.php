<?PHP //composer, ENV Vars & mysql
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');


/*
THIS SCRIPT DOWNLOADS THE VIDEO ZIP FILE
1) EXTRACT THE GET VAR
2) PULL THE RECORD
3) UPDATE THE CLICK TABLE
4) DELIVER THE FILE	
*/	

// EXTRACT GET
$s=mysqli_real_escape_string($db,$_GET['s']);
$n=mysqli_real_escape_string($db,$_GET['n']);

// PULL RECORD
$sql =  "SELECT * from mc_submissions WHERE mc_id='$s' LIMIT 1";
$rsSUBS = mysqli_query($db,$sql);
$thisSUB = mysqli_fetch_array($rsSUBS);
extract($thisSUB);

// UPDATE CLICK TABLE
// don't update if from submission tracker

if (!$n){
$sql =  "UPDATE mc_submissions SET mc_download='$now' where mc_id='$s' LIMIT 1";
mysqli_query($db,$sql);echo mysqli_error($db);
}

// IF ALP SUBMISSION, SEND STITCHED FILE, OTHERWISE, GIVE ZIP FILE

if ($mc_alp == 1){
	$location =  "Location: $mc_stitch_file_url";
	$file_name = $mc_name . " _Audtion_Taping_" . date($mc_creation, "(m-d-y).mp4");
	header('Content-Type: application/octet-stream');
	header("Content-Transfer-Encoding: Binary"); 
	header("Content-disposition: attachment; filename=\"".$file_name."\""); 
	readfile($mc_stitch_file_url);
	exit;
	}else{
		$location =  "Location: $mc_zip_file_url";
		header($location);
	}
	



?>