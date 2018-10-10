<?PHP
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');
$debug = false;

error_reporting(E_ALL);
ini_set("display_errors", 1);

if ($debug) {echo "trying<BR>";}

$sql = "insert into TESTvideoQueue set content = 'trying'";
mysqli_query($db, $sql);


define('AMQP_DEBUG', true);
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
$url = parse_url(getenv('CLOUDAMQP_URL'));
$conn = new AMQPConnection($url['host'], 5672, $url['user'], $url['pass'], substr($url['path'], 1));
$ch = $conn->channel();

if ($debug) {echo "step 2<BR>";}

$exchange = 'amq.direct';
$queue = 'Video_Process_queue';
$ch->queue_declare($queue, false, true, false, false);
$ch->exchange_declare($exchange, 'direct', true, true, false);
$ch->queue_bind($queue, $exchange);


if ($debug) {echo "step 3<BR>";}

$retrived_msg = $ch->basic_get($queue);
$payload = $retrived_msg->body;

if ($debug) {echo "Message recieved: $payload <BR>";}

$sql = "insert into TESTvideoQueue set content = '$payload'";
mysqli_query($db, $sql);


if ($debug) {echo "step 4<BR>";}



$ch->basic_ack($retrived_msg->delivery_info['delivery_tag']);

if ($debug) {echo "step 5<BR>";}

if ($debug) {echo "<pre>";var_dump($ch);echo "</pre><BR><BR>";}

$i=1;
while (count($ch->callbacks)) {
    $ch->wait();
    if ($debug) {echo "$i <BR>"; $i++;}
}

echo "step 6<BR>";



$ch->close();
$conn->close();
echo "step 7<BR>";
