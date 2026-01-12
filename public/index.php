<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Kernel;
use App\Config\Config;

Config::load(__DIR__ . '/../.env');

$kernel = new Kernel();
$response = $kernel->handle();

$response->send();
