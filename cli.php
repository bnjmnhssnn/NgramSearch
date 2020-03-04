<?php
use Symfony\Component\Console\Application;
use NgramSearch\CliCommand\Import;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/env.php';

$application = new Application();

// ... register commands
$application->add(new Import());

$application->run();