<?PHP
require './vendor/autoload.php';
require 'env.php';
include('includes/con.php');



define('AMQP_DEBUG', false);
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






/* new way? */
$callback = function($msg) {
    
    try {

		$payload = $retrived_msg->body;
		$payload = print_r($retrived_msg,1);
		
		$sql = "insert into TESTvideoQueue set content = '$payload'";
		mysqli_query($db, $sql);

    } catch(Exception $e) {


    }
};
$ch->basic_qos(null, 1, null);
$ch->basic_consume($queue, '', false, false, false, false, $callback);
/* .new way*/





/* old way */
/*
$retrived_msg = $ch->basic_get($queue);
//var_dump($retrived_msg->body);

$payload = $retrived_msg->body;
$payload = print_r($retrived_msg,1);

$sql = "insert into TESTvideoQueue set content = '$payload'";
mysqli_query($db, $sql);

$ch->basic_ack($retrived_msg->delivery_info['delivery_tag']);
*/
/* .old way */



    while (count($ch->callbacks)) {
        $ch->wait();
    }
    
    
$ch->close();
$conn->close();