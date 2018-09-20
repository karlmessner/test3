<?PHP 
$dotenv = new Dotenv\Dotenv(__DIR__);
if (file_exists('.env')){
$dotenv->load();	
}
?>