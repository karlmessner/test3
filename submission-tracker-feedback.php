<?PHP 
/*
error_reporting(-1);
ini_set('display_errors', 'true');
*/

//composer, ENV Vars & mysql
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');
include('includes/functions.php');
	
// SANITIZE GET

if (isset($_GET['s'])){$s=$_GET['s'];}	// if a=1 just show alp subs

// PULL INFO ON SUBMISSION AND FEEDBACKS

$sqlSUB = "select * from mc_submissions where mc_id = '$s'"; 
$rsSUBS = mysqli_query($db, $sqlSUB); 
$thisSUB = mysqli_fetch_array($rsSUBS);
extract($thisSUB); 


?>
<?PHP
/*
	
	THIS SCRIPT LISTS THE FEEDBACK LOG FOR A GIVEN SUBMISSION
	
*/	
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <?PHP
	    echo "<title>MC FEEDBACK - $mc_name - $mc_role in $mc_title</title>";		    
	?>
    <link href="media/css/trackercss.css" media="all" rel="stylesheet" type="text/css" />
	<meta http-equiv="refresh" content="30">  
	</head>
  <body>
    <div id='mast'>
	    <div id="headerLogo"><img src="media/images/m.svg"/></div><!-- headerlogo-->
    </div><!--mast-->
    <div id='body'>
	    <div id='content'>
    <?PHP
	    echo "<h1>Feedback: $mc_name - $mc_role in $mc_title</h1>";	
	    
	    echo "<a href='submission-tracker.php?a=1'>&larr; BACK TO TRACKER</a>";	    
	?>
		   
		   <div class="table">
			  <div class="th">
<!-- 			    <div class="td"></div> -->
			    <div class="td">Date</div>
			    <div class="td">Viewer Email</div>
			    <div class="td">Talent Email</div>
			    <div class="td">Feedback</div>
			    <div class="clear"></div>
			  </div>
<?PHP


// PULL DATA

$sql =  "SELECT * from mc_feedback WHERE mcfb_sub ='$s'";
$rsFB = mysqli_query($db,$sql);

while ($thisFB = mysqli_fetch_array($rsFB)){
	extract($thisFB);

?>	 
			  
			  <div class="tr">
<!-- 			    <div class="td"><a href="<?PHP echo $mc_file_url;?>" target="_blank"><img class='thumb' src="<?PHP echo $mc_vid_thumb_url;?>"/></a></div> -->
			    <div class="td"><?PHP echo date('n/d/y g:ia',$mcfb_creationdate);?></div>
			    <div class="td"><?PHP echo $mcfb_viewerEmail ?></div>
			    <div class="td"><?PHP echo $mcfb_toEmail ?></div>
			    <div class="td"><?PHP echo $mcfb_message ?></div>

			    <div class="clear"></div>
			  </div>
			
<?PHP } ?>			  
		    
	    </div><!--content-->
    </div><!--body-->
  </body>
</html>






