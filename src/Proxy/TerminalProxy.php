<?php
namespace Proxy;

use Core\DI;
use React\Socket\ConnectionInterface;

class TerminalProxy
{
	public function connect(ConnectionInterface $conn)
	{
		$logger = DI::get()->getLogger();
		$logger->info(sprintf("New connection %s", $conn->id));

		$conn->on('end', function () use ($conn, $logger) {
				$logger->info(sprintf("Connection %s disconnected", $conn->id));
			});

		$conn->on('data', function ($data) use ($conn, $logger) {
				if (!$conn->isReadable()) {
					$logger->warn("Attempt to read from closed socket ".$conn->id);
					return;
				}
				if (!$conn->isWritable()) {
					$logger->warn("Attempt to read from closed socket ".$conn->id);
					return;
				}

				$logger->info("Receiving data from ".$conn->id);

				$sender = new HttpSender();
				$sender->send($conn, $data);
			});
	}
}