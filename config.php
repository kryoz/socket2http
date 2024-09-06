<?php
declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

const DS = DIRECTORY_SEPARATOR;
const ROOT = __DIR__;

if (!isset($loader)) {
    $loader = require ROOT.DS.'vendor'.DS.'autoload.php';
    $loader->register();
}

$config = parse_ini_file(ROOT . DS . 'conf' . DS . 'default.ini', true);
gc_enable();
print_r($config);
$logger = new Logger('SocketServer');
$logger->pushHandler(new StreamHandler($config['logger'] ?? fopen('php://stdout', 'wb')));

set_error_handler(static function ($errno, $errstr, $errfile, $errline) use ($logger){
    $logger->error("Error: $errfile line $errline: $errstr");

    return true;
});

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Europe/Moscow');

setlocale(LC_CTYPE, "en_US.UTF8");
setlocale(LC_TIME, "en_US.UTF8");

set_time_limit(0);
ob_implicit_flush();

return [$config, $logger];