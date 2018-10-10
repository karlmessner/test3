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

//$msg = $ch->basic_get($queue);






// First, you define `$callback` as a function receiving
// one parameter (the _message_).
$callback = function($msg) {


	$payload = $msg->body;
	
	$sql = "insert into TESTvideoQueue set content = '$payload'";
	mysqli_query($db, $sql);
	
	$ch->basic_ack($msg->delivery_info['delivery_tag']);
	
	};

// Then, you assign `$callback` the the "hello" queue.
$ch->basic_consume($queue, '', false, true, false, false, $callback);

// Finally: While I have any callbacks defined for the channel, 
while(count($ch->callbacks)) {
    // inspect the queue and call the corresponding callbacks
    //passing the message as a parameter
    $ch->wait();
}
// This is an infinite loop: if there are any callbacks,
// it'll run forever unless you interrupt script's execution.







//var_dump($retrived_msg->body);




    while (count($ch->callbacks)) {
        $ch->wait();
    }
    
    
$ch->close();
$conn->close();