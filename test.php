<?PHP 
//composer, ENV Vars & mysql
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');

//require 'env.php';
echo "<pre>";
print_r($_ENV);	
	
	
	phpinfo(); ?>