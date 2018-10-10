<?PHP
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');

error_reporting(E_ALL);
ini_set("display_errors", 1);

echo "trying<BR>";

define('AMQP_DEBUG', true);
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
$url = parse_url(getenv('CLOUDAMQP_URL'));
$conn = new AMQPConnection($url['host'], 5672, $url['user'], $url['pass'], substr($url['path'], 1));
$ch = $conn->channel();

echo "step 2<BR>";

$exchange = 'amq.direct';
$queue = 'Video_Process_queue';
$ch->queue_declare($queue, false, true, false, false);
$ch->exchange_declare($exchange, 'direct', true, true, false);
$ch->queue_bind($queue, $exchange);


echo "step 3<BR>";


$callback = function ($msg) {
    $comment =  ' [x] Received '. $msg->body . "\n";
	echo "COMMENT: $comment <BR>";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

echo "step 4<BR>";

// $ch->basic_qos(null, 1, null);
$ch->basic_consume($queue, '', false, false, false, false, $callback);

echo "step 5<BR>";

/*
while (count(ch->callbacks)) {
    $ch->wait();
}
*/
$ch->close();
$conn->close();
echo "step 6<BR>";
