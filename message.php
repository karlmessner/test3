<?PHP 
		
//composer, ENV Vars & mysql
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');

// check for pk otherwise send to error
$goodKey = $_ENV['GOODKEY'];
if ($_REQUEST['pk']!=$goodKey){
	$location = "Location:" . $_ENV['DOMAIN'] . "error.php";
	header($location);
	} else {
		$auth_good='1';}


/*
	
	THIS ENDPOINT DELIVERS THE LATEST MESSAGE AND WHETHER OR NOT AN UPDATE IS REQUIRED
	
	
*/

$sql = "SELECT max(mdm_date)as message_date, mdm_id, mdm_message,mdm_must_update,mdm_current_version from mc_downstream_messages";
$rsMESSAGE = mysqli_query($db,$sql);
echo mysqli_error($db);
$numMESSAGES = mysqli_num_rows($rsMESSAGE); 
	$data = array();
if ($numMESSAGES>0){
	$thisMessage = mysqli_fetch_assoc($rsMESSAGE); 
    $data[] = $thisMessage;
	echo "<pre>";
	echo json_encode($data);
	echo "</pre>";

}?>