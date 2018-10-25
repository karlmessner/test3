<?PHP 
	
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
			echo "<title>ALP Audition Tape Tracker</title>";		    
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
    <div id='body'>
	    <div id='content'>
    <?PHP
	    if ($a==1){
			echo "<H1>ALP Audition Tape Tracker</H1>";		    
	    } ELSE {
			echo "<H1>Moodcaster Submission Tracker</H1>";		    
	    }
	?>
		   
		   <div class="table">
			  <div class="th">
<!-- 			    <div class="td"></div> -->
			    <div class="td">Date</div>
			    <div class="td">Name</div>
			    <div class="td">Title</div>
			    <div class="td">Role</div>
			    <div class="td">Share Link</div>
			    <div class="td">Raw</div>
			    <div class="td">Size</div>
			    <div class="td">Status</div>
			    <div class="td">Recipients</div>
			    <div class="td">Sent</div>
			    <div class="td">Read</div>
			    <div class="td">Clicked</div>
			    <div class="td">Downloaded</div>
			    <div class="td">Shared</div>
			    <div class="td">Id</div>
			    <div class="clear"></div>
			  </div>
<?PHP


// PULL DATA
$filter = ($a==1)?  " WHERE mc_alp=1 " : "WHERE mc_alp!=1 OR mc_alp is NULL " ;
$sql =  "SELECT * from mc_submissions $filter ORDER BY mc_creation desc";
$rsSUBS = mysqli_query($db,$sql);

while ($thisSUB = mysqli_fetch_array($rsSUBS)){
	extract($thisSUB);
	
// CREATE SHORT URL TO DOWNLOAD PAGE, flag n=1 to tell downstream pages to not track 




$s = createShortLink ($mc_id);

$shortDownloadLink = $_ENV['DOMAIN'] . 'download.php?s='.$s;
$shortDownloadLinkNoTrack = $shortDownloadLink . '&n=1';

$shortDownloadLink2 = $_ENV['DOMAIN'] . 'download2.php?s='.$s;
$shortDownloadLinkNoTrack2 = $shortDownloadLink2 . '&n=1';

$file_size = round($mc_zip_file_size/1000000,2);

?>	 
			  
			  <div class="tr">
<!-- 			    <div class="td"><a href="<?PHP echo $mc_file_url;?>" target="_blank"><img class='thumb' src="<?PHP echo $mc_vid_thumb_url;?>"/></a></div> -->
			    <div class="td"><?PHP echo date('n/d/y g:ia',$mc_creation);?></div>
			    <div class="td"><?PHP echo $mc_name;?></div>
			    <div class="td"><?PHP echo $mc_title;?></div>
			    <div class="td"><?PHP echo $mc_role;?></div>
			    <div class="td"><?PHP echo "<a href='$shortDownloadLinkNoTrack' target=_blank >$shortDownloadLink</a>";?></div>
			    <div class="td"><?PHP echo ($mc_raw_zip_file_url) ? "<a href='$mc_raw_zip_file_url' target='_blank' style='font-size:18px'>&#x2b07;</a>" : "";?></div>
			    <div class="td"><?PHP echo $file_size;?></div>
			    <div class="td"><?PHP
				    
				     if (($mc_status)&&($mc_status<100)) {echo "$mc_status";}
				     if ($mc_status==100) {echo "<center><span style='font-size:18px'>&#x2705;</span></center>";}
				     
				     ?></div>
			    <div class="td"><?PHP echo $mc_recipients_emails;?></div>
			    <div class="td"><?PHP if ($mc_creation) echo date('n/d/y g:ia',$mc_creation);?></div>
			    <div class="td"><?PHP if ($mc_read) echo "($mc_read_count) " . date('n/d/y g:ia',$mc_read);?></div>
			    <div class="td"><?PHP if ($mc_click) echo "($mc_click_count) " . date('n/d/y g:ia',$mc_click);?></div>
			    <div class="td"><?PHP if ($mc_download) echo "($mc_download_count) " . date('n/d/y g:ia',$mc_download);?></div>
			    <div class="td"><?PHP if ($mc_share) echo "($mc_share_count) " . date('n/d/y g:ia',$mc_share);?></div>
			    <div class="td"><?PHP echo $mc_id;?></div>
			    <div class="clear"></div>
			  </div>
			
<?PHP } ?>			  
			
			
			
			
		    
	    </div><!--content-->
    </div><!--body-->
  </body>
</html>






