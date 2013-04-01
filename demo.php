<?php

if ( ! file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "\033[1m\033[4;33m/!\\\033[0;37m "
        . "You must set up the project dependencies, run the following commands:" . PHP_EOL
        . "    \033[0;33mcomposer install\033[0;37m or \033[0;33mphp composer.phar install\033[0;37m." . PHP_EOL
        . PHP_EOL
        . "If needed, to install \033[1;37mcomposer\033[0;37m locally: "
        . "\033[0;37m\033[0;33mcurl -sS https://getcomposer.org/installer | php\033[0;37m" . PHP_EOL
        . "Or check http://getcomposer.org/doc/00-intro.md#installation-nix for more information." . PHP_EOL
        . PHP_EOL;
    exit(1);
}

require __DIR__ . '/vendor/autoload.php';

use \GAubry\Logger\ColoredIndentedLogger;
use \Psr\Log\LogLevel;

$COLORS = array(
    'job'          => "\033[1;34m",
    'section'      => "\033[1;37m",
    'subsection'   => "\033[1;33m",
    'subsubsection' => "\033[1;35m",
    'comment'      => "\033[1;30m",
    'debug'        => "\033[0;30m",
    'ok'           => "\033[1;32m",
    'discreet-ok'  => "\033[0;32m",
    'running'      => "\033[1;36m",
    'warm-up'      => "\033[0;36m",
    'warning'      => "\033[0;33m",
    'error'        => "\033[1;31m",
    'raw-error'    => "\033[0;31m",
    'red'    => "\033[0;31m",
    'info' => "\033[0;37m"
);
$sTabulation = "\033[0;30m┆\033[0m   ";
$oLogger = new ColoredIndentedLogger($COLORS, '  ', '+++', '---', LogLevel::DEBUG);

$oLogger->log(LogLevel::INFO, 'A{C.red}{toto}{C.yellow}B{C.red}C', array('C.yellow' => "\033[1;33m", 'toto' => 'titi'));
$oLogger->log(LogLevel::INFO, 'D');
$oLogger->log(LogLevel::INFO, '{C.subsection}Steps+++');
$oLogger->log(LogLevel::INFO, 'bla');