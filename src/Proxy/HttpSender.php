<?php

namespace Proxy;

use Core\DI;
use React\Dns\Resolver\Factory as Resolver;
use React\Socket\ConnectionInterface;
use React\SocketClient\Connector;
use React\Stream\Stream;

class HttpSender
{
	private $host;
	private $url;
	private $port;

	public function __construct()
	{
		$config = DI::get()->getConfig()->proxy;
		$this->host = $config->http_host;
		$this->port = $config->http_port;
		$this->url = $config->url;
	}

	public function getHost()
	{
		return $this->host;
	}

	public function getPort()
	{
		return $this->port;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function send(ConnectionInterface $term, $data)
	{
		if (!$this->getHost() || !$this->getUrl() || !$this->getPort()) {
			throw new \Exception('HttpSender is not configured!');
		}

		$loop = DI::get()->getLoop();
		$dnsResolverFactory = new Resolver();
		$dns = $dnsResolverFactory->createCached(DI::get()->getConfig()->proxy->dns_resolver, $loop);

		$data = ['data' => $data];
		$data = http_build_query($data);

		$connector = new Connector($loop, $dns);
		$connector
			->create($this->getHost(), $this->getPort())
			->then(function (Stream $stream) use ($data, $term) {
					$logger = DI::get()->getLogger();

					if (!$stream->isWritable()) {
						$logger->warn('HTTP host not writable!');
						return;
					}

					$logger->info("Sending data to HTTP");
					$stream->write($this->createRequestText($data));

					$stream->on('data', function ($response) use ($stream, $term, $logger) {
							try {
								$logger->info("Returning response to ".$term->id."\n".print_r($response,1));
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
