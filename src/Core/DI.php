<?php

namespace Core;

use Monolog\Logger;
use Orno\Di\Container;
use React\EventLoop\LibEventLoop;
use ReflectionClass;
use Zend\Config\Config;

class DI
{
	use \Core\TSingleton;

	/**
	 * @var \Orno\Di\Container
	 */
	private $container;

	public function __construct()
	{
		$this->container = new Container();
	}

	/**
	 * @return LibEventLoop
	 */
	public function getLoop()
	{
		return $this->container->get('loop');
	}

	/**
	 * @return Logger
	 */
	public function getLogger()
	{
		return $this->container->get('logger');
	}

	/**
	 * @return Config
	 */
	public function getConfig()
	{
		return $this->container->get('config');
	}


	public function spawn($className)
	{
		$constructorArgs = func_get_args();
		array_shift($constructorArgs);

		$reflectionClass = new ReflectionClass($className);
		$object = !empty($constructorArgs)
			? $reflectionClass->newInstanceArgs($constructorArgs)
			: $reflectionClass->newInstance();

		return $object;
	}

	/**
	 * @return \Orno\Di\Container
	 */
	public function container()
	{
		return $this->container;
	}

	public function __sleep()
	{
		return [];
	}

	public function __wakeup()
	{
		$this->__construct();
	}
}