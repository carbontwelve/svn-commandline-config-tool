<?php

require __DIR__.'/vendor/autoload.php';

use Carbontwelve\Svn\Commands\SvnIdentifyCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add( new SvnIdentifyCommand() );
$application->run();
