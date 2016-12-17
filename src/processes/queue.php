<?php

/**
 * Launches the queue, will be restarted by Supervisor when it finishes
 */

$root = realpath(__DIR__ . '/../..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

$queue = new Proximate\Queue('', '');
$queue->processor();
