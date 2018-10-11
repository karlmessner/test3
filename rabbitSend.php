<?PHP
require './vendor/autoload.php';
require 'env.php';

error_reporting(E_ALL);
ini_set("display_errors", 1);

echo "starting<bR>";





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

$array = array("foo", "bar", "hello", "world",'1','3','5','7','9');

foreach ($array as $msg_body){
$msg = new AMQPMessage($msg_body, array('content_type' => 'text/plain', 'delivery_mode' => 2));
$ch->basic_publish($msg, $exchange);
echo "$msg_body <BR>";
}


$ch->close();
$conn->close();