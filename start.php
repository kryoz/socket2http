<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use React\Socket\Server as SocketServer;
use Proxy\MightyLoop;
use Proxy\TerminalProxy;

require dirname(__DIR__).'/vendor/autoload.php';

define('CONF_SOCKET_PORT', 4000);
define('CONF_DNS_RESOLVER', '127.0.0.1');
// ip + path + port of HTTP host destination
define('CONF_HTTP_HOST', '127.0.0.1');
define('CONF_URL_PATH', '/index2.php');
define('CONF_HTTP_PORT', 80);


gc_enable();
set_time_limit(0);
ob_implicit_flush();


$logger = new Logger('socketserver');
$logger->pushHandler(new StreamHandler(STDOUT));

$loop = MightyLoop::getInstance()->get();
$socket = new SocketServer($loop);


$app = new TerminalProxy($logger);

$i = 0;

$socket->on('connection', function ($conn) use (&$i, $app) {
		$conn->id = $i;
		$app->connect($conn);
		$i++;
	});

$socket->listen(CONF_SOCKET_PORT);
$loop->run();