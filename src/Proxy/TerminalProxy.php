<?php
namespace Proxy;

use React\Socket\ConnectionInterface;
use Monolog\Logger;

class TerminalProxy
{
	private $logger;

	public function __construct(Logger $logger)
	{
		$this->logger = $logger;
	}

	public function connect(ConnectionInterface $conn)
	{
		$this->logger->info(sprintf("New connection %s", $conn->id));

		$conn->on('end', function () use ($conn) {
				$this->logger->info(sprintf("Connection %s disconnected", $conn->id));
			});

		$conn->on('data', function ($data) use ($conn) {
				if (!$conn->isReadable()) {
					$this->logger->warn("Attempt to read from closed socket ".$conn->id);
					return;
				}
				if (!$conn->isWritable()) {
					$this->logger->warn("Attempt to read from closed socket ".$conn->id);
					return;
				}

				$this->logger->info("Receiving data from ".$conn->id);

				$sender = new HttpSender($this->logger);
				$sender->send($conn, $data);
			});
	}
}