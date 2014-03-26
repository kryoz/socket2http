<?php

namespace Proxy;

use React\EventLoop\Factory as EventLoop;

class MightyLoop 
{
	/**
	 * @var \React\EventLoop\LibEventLoop|\React\EventLoop\StreamSelectLoop
	 */
	private $loop;
	private static $instance;

	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function get()
	{
		return $this->loop;
	}

	protected function __construct()
	{
		$this->loop = EventLoop::create();
	}
}