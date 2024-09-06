<?php
declare(strict_types=1);

use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;
use Proxy\TerminalProxy;
use React\Socket\SocketServer;

[$config, $logger] = require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config.php';

$pidFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'proxy-server.pid';

if (file_exists($pidFile)) {
	$pid = file_get_contents($pidFile);
    // don't know how to handle this on windows
	if (file_exists("/proc/$pid")) {
		$logger->error("Found already running daemon instance [pid = $pid], aborting.");
		exit(1);
	}

    unlink($pidFile);
}

if ($fh = fopen($pidFile, 'wb')) {
	fwrite($fh, (string) getmypid());
}
fclose($fh);


$loop = Loop::get();
$socket = new SocketServer($config['proxy']['socket_addr'].':'.$config['proxy']['socket_port'], [], $loop);

$app = new TerminalProxy($logger, $config);

$socket->on('connection', static fn (ConnectionInterface $conn) => $app->connect($conn));

$loop->run();