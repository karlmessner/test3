<?PHP
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');
/*

error_reporting(E_ALL);
ini_set("display_errors", 1);
*/

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

function callback($msg){
	global $db;
	$payload = $msg->body;
	$sql = "insert into TESTvideoQueue set content = '$payload'";
	mysqli_query($db, $sql);
	$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

	}
	
$ch->basic_qos(null, 1, null);
$ch->basic_consume($queue, '', false, true, false, false, 'callback');

while (count($ch->callbacks)) {
    $ch->wait();
}


$ch->close();
$conn->close();