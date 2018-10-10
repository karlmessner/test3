<?PHP
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');

error_reporting(E_ALL);
ini_set("display_errors", 1);

$sql = "insert into TESTvideoQueue set content = 'trying'";
mysqli_query($db, $sql);

define('AMQP_DEBUG', true);
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
$url = parse_url(getenv('CLOUDAMQP_URL'));
$conn = new AMQPConnection($url['host'], 5672, $url['user'], $url['pass'], substr($url['path'], 1));
$ch = $conn->channel();

$exchange = 'amq.direct';
$queue = 'Video_Process_queue';
$ch->queue_declare($queue, false, true, false, false);
$ch->exchange_declare($exchange, 'direct', true, true, false);
$ch->queue_bind($queue, $exchange);


function doIt($ch,$queue,$db,$msg){
	$payload = $msg->body;
	
	$sql = "insert into TESTvideoQueue set content = '$payload'";
	mysqli_query($db, $sql);
	$ch->basic_ack($retrived_msg->delivery_info['delivery_tag']);
	}
	
	
$msg = $ch->basic_get($queue);	
doIt($ch,$queue,$db,$msg);	

while (count($ch->callbacks)) {
    $ch->wait();
}


$ch->close();
$conn->close();