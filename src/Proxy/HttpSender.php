<?php
declare(strict_types=1);

namespace Proxy;

use Psr\Log\LoggerInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

class HttpSender
{
	private string $host;
	private string $url;
    private LoggerInterface $logger;
    private bool $closeConnectionOnResponse;

    public function __construct(LoggerInterface $logger, array $proxyConfig)
	{
		$this->host = (string)$proxyConfig['destination_host'];
		$this->url = (string)$proxyConfig['url'];
        $this->closeConnectionOnResponse = (bool) $proxyConfig['close_socket_on_response'];
        $this->logger = $logger;

        if (!$this->host || !$this->url) {
            throw new \RuntimeException('HttpSender is not configured!');
        }
    }

	public function send(ConnectionInterface $term, $data): void
    {
        $httpConnector = new Connector();
        $data = ['data' => $data];
        $data = http_build_query($data);

		$httpConnector
			->connect($this->host)
			?->then(
                function (ConnectionInterface $httpConn) use ($data, $term) {
                    $this->logger->info("Sending data to HTTP remote:".json_encode($data));
                    $httpConn->write($this->createRequestText($data));

                    $httpConn->on('data', function ($response) use ($httpConn, $term) {
                        try {
                            $this->logger->info("Returning response: ".json_encode($response));
                            $term->write($response);
                        } catch (\Exception $e) {
                            $this->logger->error($e->getMessage());
                        } finally {
                            $httpConn->end();
                            if ($this->closeConnectionOnResponse) {
                                $term->end();
                            }
                        }
                    });
                },
                function (\Exception $error) {
                    $this->logger->error($error->getMessage());
                }
            );

	}

	private function createRequestText($data): string
    {
		$out = "POST ".$this->url." HTTP/1.1\r\n";
		$out.= "Host: ".$this->host."\r\n";
		$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out.= "Content-Length: ".strlen($data)."\r\n";
		$out.= "Connection: Close\r\n\r\n";
		$out.= $data;

		return $out;
	}
}
