<?php namespace Carbontwelve\Svn\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
{

    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface|\Symfony\Component\Console\Output\ConsoleOutput  */
    protected $output;

    /**
     * Write the $text error line to the command line so long as verbosity is set to debug, unless $force is set to true
     *
     * @param $text
     * @param bool $force
     */
    protected function writeError( $text, $force = false )
    {
        if ( $this->output->isDebug() || $force === true )
        $this->output->writeln('<error>[!]</error> ' . $text);
    }

    protected function writeDebug( $text )
    {
        if ( $this->output->isDebug() )
        $this->output->writeln('<info>[+]</info> ' . $text);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;
        $this->command();
    }

    abstract protected function command();

}
