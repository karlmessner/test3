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

$retrived_msg = $ch->basic_get($queue);
$payload = $retrived_msg->body;
echo "Message recieved: $payload <BR>";

$sql = "insert into TESTvideoQueue set content = '$payload'";
mysqli_query($db, $sql);


echo "step 4<BR>";



$ch->basic_ack($retrived_msg->delivery_info['delivery_tag']);

echo "step 5<BR>";



while (count($ch->callbacks)) {
    $ch->wait();
}

echo "step 6<BR>";



$ch->close();
$conn->close();
echo "step 7<BR>";
