<?PHP
require './vendor/autoload.php';
require 'env.php';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>iphone simulator</title>
    <link href="media/css/iphonecss.css" media="all" rel="stylesheet" type="text/css" />
  </head>
  <body>
	<div id='wrapper'>
    <form id='theform' enctype="multipart/form-data" action="<?PHP echo $_ENV['DOMAIN'];?>index.php" method="post">
	    <input type="checkbox" name="debug" id='debug' value='true'/>Verbose output <br/>
	    <input type="text" name="Name" placeholder="Name"/> <br/>
	    <input type="text" name="Role" placeholder="Role"/> <br/>
	    <input type="text" name="Title" placeholder="Title"/> <br/>
	    <input type="text" name="Title_text" placeholder="Title Text"/> <br/>
	    <input type="text" name="Email" placeholder="Email"/><br />
	    <input type="text" name="Note" placeholder="Note"/> <br/>
	    <input type="text" name="Recipients_emails" placeholder="Recipients_emails"/> <br/>	    
	    <input type="text" name="Age_range" placeholder="Age_range"/> <br/>	
	    <input type="text" name="Bio" placeholder="Bio"/> <br/>
	    <input type="text" name="Profile_pic_url" placeholder="Profile Pic Url"/> <br/>
	    <input type="file" name="Title_card" id='titlecard'/><label for='titlecard'>Title_Card</label> <br/>
	    <input type="file" name="Zip_file" id='zip'/><label for='zip'>Zip_file</label><br />
	    <input type="hidden" name="pk" value ="Wa6-abf-oDM4-rgEn" readonly/>

	    <input type="submit" />
</form>
	 </div>
</body>
</html>
