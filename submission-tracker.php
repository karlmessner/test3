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

if (isset($_GET['a'])){$a=$_GET['a'];}	// if a=1 just show alp subs
?>
<?PHP
/*
	
	THIS SCRIPT SHOWS THE STATUS OF SUBMISSIONS:
		DATE, ACTOR NAME, TITLE, ROLE, VIDEO THUMB (LINKS TO FILE), SENT, READ, CLICKED, DOWNLOAD DATES
	
*/	
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <?PHP
	    if ($a==1){
			echo "<title>Uploader Tracker</title>";		    
	    } ELSE {
			echo "<title>Moodcaster Submission Tracker</title>";		    
	    }
	?>
    <link href="media/css/trackercss.css" media="all" rel="stylesheet" type="text/css" />
	<meta http-equiv="refresh" content="30">  
	</head>
  <body>
    <div id='mast'>
	    <div id="headerLogo"><img src="media/images/m.svg"/></div><!-- headerlogo-->
    </div><!--mast-->
    
    <div id='navtabs'>
	    <a href="submission-tracker.php"  <?PHP if ($a!=1){echo " class='current'";}?>>APP</a>
	    
	    <a href="submission-tracker.php?a=1" <?PHP if ($a==1){echo " class='current'";}?>>Uploader</a>
	    
    </div>
    <div id='body'>
	    <div id='content'>
    <?PHP
	    if ($a==1){
			echo "<H1>Uploader Tracker</H1>";		    
	    } ELSE {
			echo "<H1>Moodcaster Submission Tracker</H1>";		    
	    }
	?>
		   <div class="table">
			  <div class="th">
			    <div class="td">Date</div>
			    <?PHP if ($a==1){ echo '<div class="td">Moodcode</div>';}?>
			    <div class="td">Name</div>
			    <div class="td">Title</div>
			    <div class="td">Role</div>
			    <div class="td">Share Link</div>
			    <?PHP if ($a!=1){ echo '<div class="td">Raw</div>';}?>
			    <div class="td">Size</div>
			    <?PHP if ($a!=1){ echo '<div class="td">W&nbsp;X&nbsp;H</div>';}?>
			    <div class="td">Status</div>
			    <div class="td">Recipients</div>
			    <div class="td">Sent</div>
			    <div class="td">Read</div>
			    <div class="td">Clicked</div>
    <?PHP
	    if ($a==1){
			echo "<div class='td'>Feedback</div>";		    
	    } 
	?>
			    <div class="td">Downloaded</div>
			    <div class="td">Shared</div>
			    <div class="td">Id</div>
			    <div class="clear"></div>
			  </div>
<?PHP

// PULL DATA
$filter = ($a==1) ?  " WHERE mc_alp=1 " : "WHERE mc_alp!=1 OR mc_alp is NULL " ;

// FILTER OUT EMPTY RECORDS
$filter .= "
	AND
		(
		mc_name != ''
		OR
		mc_title != ''
		OR
		mc_email != ''
		OR
		mc_recipients_emails != ''
		)
";

// LIMIT THE TIME TO LAST 30 DAYS
$lookBackDays = 30;

$timePeriod = $now - ($lookBackDays * 24*60*60);
$filter .="

	AND mc_creation > '$timePeriod'
	
";

$sql =  "SELECT * from mc_submissions $filter ORDER BY mc_creation desc";
$rsSUBS = mysqli_query($db,$sql);

while ($thisSUB = mysqli_fetch_array($rsSUBS)){
	extract($thisSUB);
	
// CREATE SHORT URL TO DOWNLOAD PAGE, flag n=1 to tell downstream pages to not track 

$s = createShortLink ($mc_id);

// $shortDownloadLink = $_ENV['DOMAIN'] . 'download.php?s='.$s;
$shortDownloadLink = $mc_download_link;
$shortDownloadLinkNoTrack = $shortDownloadLink . '&n=1';

/*
$shortDownloadLink2 = $_ENV['DOMAIN'] . 'download2.php?s='.$s;
$shortDownloadLinkNoTrack2 = $shortDownloadLink2 . '&n=1';
*/

$file_size = round($mc_zip_file_size/1000000,2);

?>	 
			  
			  <div class="tr">
<!-- 			    <div class="td"><a href="<?PHP echo $mc_file_url;?>" target="_blank"><img class='thumb' src="<?PHP echo $mc_vid_thumb_url;?>"/></a></div> -->
			    <div class="td"><?PHP echo date('n/d/y g:ia',$mc_creation);?></div>
			    <?PHP if ($a==1){
				    echo " <div class='td'>"; 
				    if ($mc_moodcode){ echo "m-$mc_moodcode";}
				    echo "</div>";}?>
			    <div class="td"><?PHP echo $mc_name;?></div>
			    <div class="td"><?PHP echo $mc_title;?></div>
			    <div class="td"><?PHP echo $mc_role;?></div>
			    <div class="td"><?PHP echo "<a href='$shortDownloadLinkNoTrack' target=_blank >$shortDownloadLink</a>";?></div>
			    
			    
			    <?PHP if ($a!=1){ ?>
			    <div class="td"><?PHP echo ($mc_raw_zip_file_url) ? "<a href='$mc_raw_zip_file_url' target='_blank' style='font-size:18px'>&#x2b07;</a>" : "";?></div>
			    <?PHP } ?>
			    <div class="td"><?PHP echo $file_size;?></div>
			    
			    <?PHP if ($a!=1){ ?>
			    <div class="td"><?PHP echo ($mc_target_width * $mc_target_height) ? "$mc_target_width x $mc_target_height" :"";?></div>
			    <?PHP } ?>
			    
			    <div class="td"><?PHP
				    
				     if (($mc_status)&&($mc_status<100)&&($mc_status!='FAILED')) {
					     echo "<a title='$mc_app_response'>$mc_status:</a>";
					     }
				     if ($mc_status==100) {echo "<center><span style='font-size:18px'>&#x2705;</span></center>";}
				     if ($mc_status=='FAILED') {echo "<center><span style='font-size:18px'>&#x274c;</span></center>";}
				     
				     ?></div>
			    <div class="td"><?PHP echo $mc_recipients_emails;?></div>
			    <div class="td"><?PHP if ($mc_creation) echo date('n/d/y g:ia',$mc_creation);?></div>
			    <div class="td" style="text-align: center">
				    <?PHP 
					    if ($mc_read) {
						    echo "($mc_read_count time" .  is_plural($mc_read_count) .  ")<br>LAST: " . date('n/d/y g:ia',$mc_read);
						    }
					?>
				</div>
				
			    <div class="td" style="text-align: center">
				    <?PHP 
					    if ($mc_click) {
						    echo "($mc_click_count time" .  is_plural($mc_click_count) .  ")<br>LAST: " . date('n/d/y g:ia',$mc_click);
						    }
					?>
				</div>
				
				
				
    <?PHP
	    if ($a==1){
?>
			    <div class="td" style="text-align: center">
				    <?PHP 
					    
					    $sqlFeedback = "SELECT count(mcfb_id) as numFB, max(mcfb_creationdate) as lastFB  from mc_feedback WHERE mcfb_sub ='$mc_id'";
					    $rsFB = mysqli_query($db, $sqlFeedback);
					    $thisFB = mysqli_fetch_array($rsFB);
					    extract($thisFB);
					    					    
					    if ($numFB){
						    echo "<a href='submission-tracker-feedback.php?s=$mc_id'>($numFB time" .  is_plural($numFB) .  ")<br>LAST: " . date('n/d/y g:ia',$lastFB) . "</a>";
						    }
					?>
				</div>
<?PHP

	    } 
	?>
				
				
			    <div class="td" style="text-align: center">
				    <?PHP 
					    if ($mc_download){
						    echo "($mc_download_count time" .  is_plural($mc_download_count) .  ")<br>LAST: " . date('n/d/y g:ia',$mc_download);
						    }
					?>
				</div>
				
				
				
			    <div class="td" style="text-align: center">
				    <?PHP 
					    if ($mc_share) {
						    echo "($mc_share_count time" .  is_plural($mc_share_count) .  ")<br>LAST: " . date('n/d/y g:ia',$mc_share);
						    }
					?>
				</div>
			    <div class="td"><?PHP echo $mc_id;?></div>
			    <div class="clear"></div>
			  </div>
			
<?PHP } ?>			  
			
	    </div><!--content-->
    </div><!--body-->
  </body>
</html>
