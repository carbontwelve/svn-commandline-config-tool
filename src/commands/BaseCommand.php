<?php namespace Carbontwelve\Svn\Commands;

use Carbontwelve\Svn\Exceptions\ExitException;
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

    /**
     * @param string $text
     * @param int $code
     * @throws ExitException
     */
    protected function writeQuitError( $text, $code = 1 )
    {
        $this->writeError($text, true);
        $this->output->writeln('<error>Doing nothing more and exiting!</error>');

        throw new ExitException($text, $code);
    }

    /**
     * @param string $text
     */
    protected function writeDebug( $text )
    {
        if ( $this->output->isDebug() )
        $this->output->writeln('<info>[+]</info> ' . $text);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;
        $this->command();
    }

    abstract protected function command();

}
