<?php

require 'vendor/autoload.php';

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
    'a'    => "\033[0;31m",
);
$sTabulation = "\033[0;30m┆\033[0m   ";
$oLogger = new ColoredIndentedLogger($COLORS, '  ', '+++', '---', LogLevel::DEBUG);

$oLogger->log(LogLevel::INFO, 'A{C.a}{C.b}{C.c}{C.a}C', array('C.c' => 'HA'));
$oLogger->log(LogLevel::INFO, 'B');
$oLogger->log(LogLevel::INFO, '{C.subsection}Steps+++');
$oLogger->log(LogLevel::INFO, 'bla');