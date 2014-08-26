<?php
namespace Core;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Orno\Di\Container;
use React\EventLoop\Factory as Loop;
use Zend\Config\Config;
use Zend\Config\Reader\Ini;


class DIBuilder
{
	public static function setupNormal(Container $container)
	{
		self::setupConfig($container);
		self::setupEventLoop($container);
		self::setupLogger($container);
	}

	public static function setupConfig(Container $container)
	{
		$container->add(
			'config',
			function () {
				$DS = DIRECTORY_SEPARATOR;
				$confPath = ROOT . $DS . 'conf' . $DS;
				$reader = new Ini();
				$config = new Config($reader->fromFile($confPath . 'default.ini'));
				if (file_exists($confPath . 'local.ini')) {
					$config->merge(new Config($reader->fromFile($confPath . 'local.ini')));
				}

				return $config;
			},
			true
		);
	}

	/**
	 * @param Container $container
	 */
	public static function setupEventLoop(Container $container)
	{
		$container->add(
			'loop',
			function () {
				return Loop::create();
			},
			true
		);
	}

	/**
	 * @param Container $container
	 */
	public static function setupLogger(Container $container)
	{
		$container->add(
			'logger',
			function () use ($container) {
				$logger = new Logger('Chat');
				$type = $container->get('config')->logger ? : fopen('php://stdout', 'w');
				$logger->pushHandler(new StreamHandler($type));
				return $logger;
			},
			true
		);
	}
}
