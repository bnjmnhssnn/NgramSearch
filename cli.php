<?php
use Symfony\Component\Console\Application;
use NgramSearch\CliCommand\Setup;
use NgramSearch\CliCommand\Import;

require __DIR__ . '/vendor/autoload.php';
if(file_exists(__DIR__ . '/src/env.php')) {
    require __DIR__ . '/src/env.php';
}

$application = new Application();

// ... register commands
$application->add(new Setup());
$application->add(new Import());

$application->run();