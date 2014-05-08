<?php

namespace Proxy;

use Monolog\Logger;
use React\Dns\Resolver\Factory as Resolver;
use React\Socket\ConnectionInterface;
use React\SocketClient\Connector;
use React\Stream\Stream;

class HttpSender
{
	private $host;
	private $url;
	private $port;
	/**
	 * @var Logger
	 */
	private $logger;

	public function __construct(Logger $logger)
	{
		$this->logger = $logger;
		$this->host = CONF_HTTP_HOST;
		$this->url = CONF_URL_PATH;
		$this->port = CONF_HTTP_PORT;
	}

	public function setHost($host)
	{
		$this->host = $host;
		return $this;
	}

	public function getHost()
	{
		return $this->host;
	}

	public function setPort($port)
	{
		$this->port = $port;
		return $this;
	}

	public function getPort()
	{
		return $this->port;
	}

	public function setUrl($url)
	{
		$this->url = $url;
		return $this;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getLogger()
	{
		return $this->logger;
	}

	public function send(ConnectionInterface $term, $data)
	{
		if (!$this->getHost() || !$this->getUrl() || !$this->getPort()) {
			throw new \Exception('HttpSender is not configured!');
		}

		$loop = MightyLoop::getInstance()->get();
		$dnsResolverFactory = new Resolver();
		$dns = $dnsResolverFactory->createCached(CONF_DNS_RESOLVER, $loop);

		$data = ['data' => $data];
		$data = http_build_query($data);

		$connector = new Connector($loop, $dns);
		$connector
			->create($this->getHost(), $this->getPort())
			->then(function (Stream $stream) use ($data, $term) {


					if (!$stream->isWritable()) {
						$this->getLogger()->warn('HTTP host not writable!');
						return;
					}

					$this->getLogger()->info("Sending data to HTTP");
					$stream->write($this->createRequestText($data));

					$stream->on('data', function ($response) use ($stream, $term) {
							try {
								$this->logger->info("Returning response to ".$term->id."\n".print_r($response,1));
								$term->write($response);
							} catch (\Exception $e) {
								$term->close();
								throw $e;
							} finally {
								$stream->close();
							}
						});
				});

	}

	private function createRequestText($data)
	{
		$out = "POST ".$this->getUrl()." HTTP/1.1\r\n";
		$out.= "Host: ".$this->getHost()."\r\n";
		$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out.= "Content-Length: ".strlen($data)."\r\n";
		$out.= "Connection: Close\r\n\r\n";
		$out.= $data;

		return $out;
	}
}
