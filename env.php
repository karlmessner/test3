<?PHP 
$dotenv = new Dotenv\Dotenv(__DIR__);
$envPath = $_SERVER['DOCUMENT_ROOT'] . "/test3/.env";
//echo $envPath;
if (file_exists($envPath)){
$dotenv->load();	
}
?>