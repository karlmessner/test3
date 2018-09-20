<?PHP include('../includes/db2.php');?>
<?PHP
/*
THIS SCRIPT DOWNLOADS THE VIDEO ZIP FILE
1) EXTRACT THE GET VAR
2) PULL THE RECORD
3) UPDATE THE CLICK TABLE
4) change the file name to cut off the time stamp
5) DELIVER THE FILE	
*/	

// EXTRACT GET
$s=mysql_real_escape_string($_GET['s']);

// PULL RECORD
$sql =  "SELECT * from mc_submissions WHERE mc_id='$s' LIMIT 1";
$rsSUBS = mysql_query($sql);
$thisSUB = mysql_fetch_array($rsSUBS);
extract($thisSUB);



// UPDATE CLICK TABLE
$sql =  "UPDATE mc_submissions SET mc_download='$now' where mc_id='$s' LIMIT 1";
mysql_query($sql);echo mysql_error();

/*
$location =  "Location: $mc_file_url";
header($location);
*/

//echo $mc_file_path;
//echo "<BR><BR>";

/* CURRENTLY, THE FILE NAME HAS THIS IN FRONT:
/home2/actorsla/public_html/uploads-moodcaster-submissions/1535013014-

WHICH IS 70 CHARACTERS
*/

$new_filename = substr($mc_file_path, 70);

//echo $new_filename;


// headers to send your file

header("Content-Type: application/zip");
header("Content-Length: $mc_file_size");
header('Content-Disposition: attachment; filename="' . $new_filename . '"');
header("Pragma: no-cache"); 

// upload the file to the user and quit
readfile($mc_file_path);
exit;



?>