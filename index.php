<?PHP
//composer
error_reporting(E_ALL);
ini_set("display_errors", 1);
require './vendor/autoload.php';


$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->overload();	


	?>
	
	<?PHP echo "<a href=s3.php>upload</a>"; 
	
	
	echo $_ENV['secret'];
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <img src="https://s3.amazonaws.com/mc-vid-submissions/DSC02196.JPG" />
  </body>
</html>