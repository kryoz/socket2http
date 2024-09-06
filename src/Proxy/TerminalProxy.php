<?php
declare(strict_types=1);

namespace Proxy;

use Psr\Log\LoggerInterface;
use React\Socket\ConnectionInterface;

class TerminalProxy
{
    private LoggerInterface $logger;
    private HttpSender $sender;

    public function __construct(LoggerInterface $logger, array $config)
    {
        $this->sender = new HttpSender($logger, $config['proxy']);
        $this->logger = $logger;
    }

    public function connect(ConnectionInterface $conn): void
    {
		$conn->on('data', function ($data) use ($conn) {
            if (!$conn->isReadable()) {
                $this->logger->error('Attempt to read from closed socket');
                return;
            }
            if (!$conn->isWritable()) {
                $this->logger->error('Attempt to read from closed socket');
                return;
            }

            $this->logger->info("Receiving data from ".$conn->getRemoteAddress());

            $this->sender->send($conn, $data);
        });
	}
}