<?php namespace Carbontwelve\Svn\Commands;

use Carbontwelve\Svn\Exceptions\SvnDirectoryNotFoundException;
use Carbontwelve\Svn\Formatters\ApacheDavSvnFormatter;
use Carbontwelve\Svn\SvnDirectoryParser;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Dotenv;
use Symfony\Component\Filesystem\Filesystem;

class SvnIdentifyCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('svn:identify')
            ->setDescription('Identify SVN repositories')
            ->addArgument(
                'rootDirectory',
                InputArgument::OPTIONAL,
                'Root directory where repositories are stored',
                null
            )
            ->addOption(
                'env',
                'e',
                InputOption::VALUE_OPTIONAL,
                'Name of the environment variable where we can find your root svn directory'
            )
            ->addOption(
                'includeRootRepository',
                'i',
                InputOption::VALUE_NONE,
                'If set, the command will include root repositories'
            );
    }

    /**
     * The actual main command method
     */
    protected function command()
    {
        if ( ! $rootDirectory = $this->input->getArgument('rootDirectory') )
        {
            if ( ! $rootDirectory = $this->identifyRootDirectoryFromEnvironmentHome() ) {

                if (!$rootDirectoryEnvironment = $this->input->getOption('env')) {
                    $this->writeQuitError('No root svn directory has been defined. Doing nothing and quiting.');
                }

                $rootDirectory = $this->identifyRootDirectoryFromEnvironment($rootDirectoryEnvironment);
            }
        }
        $this->writeDebug('Using root directory <comment>['. $rootDirectory .']</comment>');

        $svnDirectoryParser = $this->generateSvnDirectoryParser($rootDirectory);

        $svnDirectoryParser->identifyDirectoryStructure();
        $this->writeDebug('Found <comment>['. $svnDirectoryParser->getFoundRepositoriesCount() .']</comment> repositories');

        $svnOutputFormatter = new ApacheDavSvnFormatter($svnDirectoryParser->getFoundRootRepositories());

        echo $svnOutputFormatter->render();
    }

    /**
     * Identify root directory from environment
     *
     * @param string $dir
     * @param string $file
     * @return string
     */
    private function identifyRootDirectoryFromEnvironment( $dir, $file = '.env' )
    {
        try{
            Dotenv::load( $dir, $file );
            $this->writeDebug('Using environment file in  <comment>['. $dir . DIRECTORY_SEPARATOR . $file . ']</comment>');
        }
        catch( \InvalidArgumentException $err )
        {
            $this->writeQuitError($err->getMessage(), 1);
        }

        return getenv('SVN_PATH');
    }

    /**
     * Identify root directory from users home location
     *
     * @return false|string Returns false on error, string on success
     */
    private function identifyRootDirectoryFromEnvironmentHome()
    {
        if ( ! $homePath = getenv('HOME') )
        {
            if ( ! $homePath = getenv('HOMEPATH') )
            {
                return false;
            }
        }

        try{
            Dotenv::load( $homePath, '.svnIdentifyCommand' );

            $this->writeDebug('Using environment file in  <comment>['. $homePath . DIRECTORY_SEPARATOR .'.svnIdentifyCommand]</comment>');
            return getenv('SVN_PATH');
        }
        catch( \InvalidArgumentException $err )
        {

            return false;
        }
    }

    /**
     * Simple factory method for the SvnDirectoryParser as we have little need for an IoC at this point in time
     *
     * @param $dir
     * @return SvnDirectoryParser
     */
    private function generateSvnDirectoryParser( $dir )
    {
        try{
            return new SvnDirectoryParser( $dir, new Filesystem(), $this->input->getOption('includeRootRepository') );
        }
        catch ( SvnDirectoryNotFoundException $err )
        {
            $this->writeQuitError($err->getMessage());
            return null;
        }
    }
}
