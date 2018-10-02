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

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>MESSAGES-moodcaster</title>
    
    <style>
	    *{font-family: sans-serif}
	    input[type=text]{font-size: 16px;width:400px;text-align: center;}
	    form  {text-align:center;}
	    
	</style>
  </head>
  <body>
<br />
<center>
<h1>APP LAUNCH MESSAGE</h1>
<p>Most recent message is shown IF they don't have the most recent version</p>
<br /><br />
<?PHP 
	if ($_POST['newMessage']){
		$sql = "INSERT INTO mc_downstream_messages SET mdm_date = '$now', mdm_message='".$_POST['mdm_message']."',mdm_current_version='".$_POST['mdm_current_version']."'";
		mysqli_query($db, $sql); echo mysqli_error($db);
		//echo $sql;
			}
	
	// UNCOMMENTING THIS LINE DELETES THE ENTIRE DATABASE
	//$sql = "delete from mc_downstream_messages where mdm_id>0";		mysqli_query($db, $sql); echo mysqli_error($db);
	
?>

<form action="#" method="post">
	<input type="hidden" name="pk" value="<?PHP echo $_ENV['GOODKEY'];  ?>" />
	<input type='text' name='mdm_message' placeholder = "message" /><br /><br />
	<input type='text' name='mdm_current_version' placeholder = "Force Update if version less than" /><br /><br />
	<input type='submit' name='newMessage' value='ADD MESSAGE'/><br />
</form>
</br><?PHP


// LIST ALL MESSAGES
$sql = "SELECT mdm_id, mdm_date ,  mdm_message,mdm_current_version from mc_downstream_messages order by mdm_date desc";
$rsMESSAGE = mysqli_query($db,$sql);
echo mysqli_error($db);
$numMESSAGES = mysqli_num_rows($rsMESSAGE); 
if ($numMESSAGES>0){

echo "<h2>PAST MESSAGES</h2>";	
echo "<table cellspacing=10 cellpadding=10>";	
		echo "<tr><td><strong>ID</strong></td><td><strong>DATE</strong></td><td><strong>UPDATE BELOW VERSION</strong></td><td><strong>MESSAGE</strong></td></tr>";

	while ($thisMessage = mysqli_fetch_array($rsMESSAGE)){
		extract($thisMessage);
		echo "<tr><td>$mdm_id</td><td>" . date("m.d.y g:ia",$mdm_date) . "</td><td>$mdm_current_version</td><td>$mdm_message</td></tr>";
		
	}
echo "</table>";	



}?>

