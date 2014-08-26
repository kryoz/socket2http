<?php

use Core\DI;
use Core\DIBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use React\Socket\Server as SocketServer;
use Proxy\TerminalProxy;
use Zend\Config\Config;

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);
$config = $container->get('config');
/* @var $config Config */
$logger = $container->get('logger');
/* @var $logger Logger */

$pidFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'proxy-server.pid';

if (file_exists($pidFile)) {
	$pid = file_get_contents($pidFile);
	if (file_exists("/proc/$pid")) {
		$logger->error("Found already running daemon instance [pid = $pid], aborting.");
		exit(1);
	} else {
		unlink($pidFile);
	}
}

$fh = fopen($pidFile, 'w');
if ($fh) {
	fwrite($fh, getmypid());
}
fclose($fh);

ini_set("session.gc_maxlifetime", $config->session->lifetime);
gc_enable();

$logger = new Logger('socketserver');
$logger->pushHandler(new StreamHandler(STDOUT));

$loop = DI::get()->getLoop();
$socket = new SocketServer($loop);

$app = new TerminalProxy();

$i = 0;

$socket->on('connection', function ($conn) use (&$i, $app) {
		$conn->id = $i;
		$app->connect($conn);
		$i++;
	});

$socket->listen($config->proxy->socket_port);
$loop->run();