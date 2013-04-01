<?php

if ( ! file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo 'You must set up the project dependencies, run the following commands:'.PHP_EOL.
    'curl -sS https://getcomposer.org/installer | php'.PHP_EOL.
    'php composer.phar install'.PHP_EOL.
    'Or check http://getcomposer.org/doc/00-intro.md#installation-nix'.PHP_EOL;
    exit(1);
}

$oLoader = require __DIR__ . '/../vendor/autoload.php';
$oLoader->add('GAubry\Logger\Tests', __DIR__);
