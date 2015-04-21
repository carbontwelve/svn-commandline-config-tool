<?php

require __DIR__.'/vendor/autoload.php';

use Carbontwelve\Svn\Commands\SvnIdentifyCommand;
use Symfony\Component\Console\Application;

try {
    $application = new Application();
    $application->add(new SvnIdentifyCommand());
    $application->run();
}
catch ( \Carbontwelve\Svn\Exceptions\ExitException $exitException )
{
    exit( $exitException->getCode() );
}
