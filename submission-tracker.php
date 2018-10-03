<?PHP 
	
//composer, ENV Vars & mysql
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');
	
	
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
    <title>Moodcaster Submission Tracker</title>
    <link href="media/css/trackercss.css" media="all" rel="stylesheet" type="text/css" />
  </head>
  <body>
    <div id='mast'>
	    <div id="headerLogo"><img src="media/images/m.svg"/></div><!-- headerlogo-->
    </div><!--mast-->
    <div id='body'>
	    <div id='content'>
		   <h1>Submissions</h1>
		   
		   <div class="table">
			  <div class="th">
<!-- 			    <div class="td"></div> -->
			    <div class="td">Date</div>
			    <div class="td">Name</div>
			    <div class="td">Title</div>
			    <div class="td">Role</div>
			    <div class="td">Share Link</div>
			    <div class="td">Recipients</div>
			    <div class="td">Sent</div>
			    <div class="td">Read</div>
			    <div class="td">Clicked</div>
			    <div class="td">Downloaded</div>
			    <div class="td">Shared</div>
			    <div class="clear"></div>
			  </div>
<?PHP


// PULL DATA
$sql =  "SELECT * from mc_submissions  ORDER BY mc_creation desc";
$rsSUBS = mysqli_query($db,$sql);

while ($thisSUB = mysqli_fetch_array($rsSUBS)){
	extract($thisSUB);
	
// CREATE SHORT URL TO DOWNLOAD PAGE, flag n=1 to tell downstream pages to not track 
$s=base64_encode($mc_id);
$shortDownloadLink = $_ENV['DOMAIN'] . 'download.php?s='.$s;
$shortDownloadLinkNoTrack = $shortDownloadLink . '&n=1';
?>	 
			  
			  <div class="tr">
<!-- 			    <div class="td"><a href="<?PHP echo $mc_file_url;?>" target="_blank"><img class='thumb' src="<?PHP echo $mc_vid_thumb_url;?>"/></a></div> -->
			    <div class="td"><?PHP echo date('n/d/y g:ia',$mc_creation);?></div>
			    <div class="td"><?PHP echo $mc_name;?></div>
			    <div class="td"><?PHP echo $mc_title;?></div>
			    <div class="td"><?PHP echo $mc_role;?></div>
			    <div class="td"><?PHP echo "<a href='$shortDownloadLinkNoTrack' target=_blank >$shortDownloadLink</a>";?></div>
			    <div class="td"><?PHP echo $mc_recipients_emails;?></div>
			    <div class="td"><?PHP if ($mc_creation) echo date('n/d/y g:ia',$mc_creation);?></div>
			    <div class="td"><?PHP if ($mc_read) echo date('n/d/y g:ia',$mc_read);?></div>
			    <div class="td"><?PHP if ($mc_click) echo date('n/d/y g:ia',$mc_click);?></div>
			    <div class="td"><?PHP if ($mc_download) echo date('n/d/y g:ia',$mc_download);?></div>
			    <div class="td"><?PHP if ($mc_share) echo date('n/d/y g:ia',$mc_share);?></div>
			    <div class="clear"></div>
			  </div>
			
<?PHP } ?>			  
			
			
			
			
		    
	    </div><!--content-->
    </div><!--body-->
  </body>
</html>






