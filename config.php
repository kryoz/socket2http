<?php

$DS = DIRECTORY_SEPARATOR;
define('ROOT', __DIR__);

function basicSetup()
{
	error_reporting(E_ALL | E_STRICT);

	date_default_timezone_set('Europe/Moscow');

	setlocale(LC_CTYPE, "en_US.UTF8");
	setlocale(LC_TIME, "en_US.UTF8");

	$defaultEncoding = 'UTF-8';
	mb_internal_encoding($defaultEncoding);
	mb_regex_encoding($defaultEncoding);

	set_time_limit(0);
	ob_implicit_flush();
}

function CustomErrorHandler($errno, $errstr, $errfile, $errline)
{
	echo "Error: $errfile line $errline: $errstr\n";
	return true;
}

set_error_handler('CustomErrorHandler');

if (!isset($loader)) {
	$loader = require_once ROOT.$DS.'vendor'.$DS.'autoload.php';
	$loader->register();
}

basicSetup();
