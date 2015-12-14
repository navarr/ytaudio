<?php

error_reporting(-1);

$composer = __DIR__.'/../vendor/autoload.php';
if (!is_file($composer)) {
    throw new RuntimeException('Composer dependencies must be installed first.');
}
require_once $composer;
