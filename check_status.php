<?PHP 
		
//composer, ENV Vars & mysql
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');
include('includes/functions.php');


// check for pk otherwise send to error
$goodKey = $_ENV['GOODKEY'];
if ($_REQUEST['pk']!=$goodKey){
	$location = "Location:" . $_ENV['DOMAIN'] . "error.php";
	header($location);
	} else {
		$auth_good='1';}


/*
	
	THIS ENDPOINT DELIVERS THE STATUS OF SUBMISSION WITH ID EXTRACTED FROM SHORT DOWNLOAD LINK
	
	
*/

$d = $_REQUEST['d'];
$query = parse_url($d, PHP_URL_QUERY);
$sEnc= substr($query, 2);
$s = decodeShortLink($sEnc);

$sql = "SELECT mc_status from mc_submissions WHERE mc_id = '$s' limit 1";
$rsPERCENTAGES = mysqli_query($db,$sql);
$thisPerc = mysqli_fetch_array($rsPERCENTAGES);
extract($thisPerc);
echo $mc_status;
?>