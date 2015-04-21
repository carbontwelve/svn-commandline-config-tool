<?php namespace Carbontwelve\Svn;

use Carbontwelve\Svn\Exceptions\SvnDirectoryNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class SvnDirectoryParser
{

    /** @var string */
    private $dir;

    /** @var Filesystem */
    private $fileSystem;

    /** @var int */
    private $repositoriesFound = 0;

    /** @var array|string */
    private $directoryStructure;

    /** @var array */
    private $rootRepositoryDirectories = array();

    /** @var bool */
    private $includeRootRepositories;

    public function __construct( $dir = '', Filesystem $fileSystem, $includeRootRepositories = false )
    {
        $this->fileSystem               = $fileSystem;
        $this->dir                      = $this->validateDirectory( $dir );
        $this->includeRootRepositories  = (bool) $includeRootRepositories;
    }

    /**
     * @return array|string
     */
    public function identifyDirectoryStructure()
    {
        return $this->directoryStructure = $this->recursiveSvnFinder($this->dir);
    }

    /**
     * @return int
     */
    public function getFoundRepositoriesCount()
    {
        return (int) $this->repositoriesFound;
    }

    /**
     * @return array
     */
    public function getFoundRootRepositories()
    {
        $return = array();
        arsort($this->rootRepositoryDirectories);
        array_walk_recursive($this->rootRepositoryDirectories, function($a) use (&$return) { $return[] = $a; });
        return $return;
    }
    /**
     * @param string $dir
     * @return string
     * @throws SvnDirectoryNotFoundException
     */
    private function validateDirectory( $dir )
    {
        if ( ! $this->fileSystem->exists( $dir ) )
        {
            throw new SvnDirectoryNotFoundException('The directory ['. $dir .'] could not be found');
        }

        return $dir;
    }

    /**
     * Check to see if a dir has child repositories or not...
     *
     * @param $dir
     * @return bool
     */
    private function hasChildren( $dir )
    {
        if ( in_array('format', scandir( $dir ) ) )
        {
            return false;
        }

        return true;
    }

    /**
     * A recursive svn repository finder, this uses the stupidly basic check of seeing if a directory called "format"
     * exists to tell if a directory is a svn repository. It would have been better to use the svnlook info command but
     * it works and that's all it needs to do right now - feel free to update if you would like to.
     *
     * @param string $initDir
     * @param int $depth This is used to identify what level depth we are within the file system from base
     * @return array|string this will return the string 'repo' if the $initDir is a svn repository
     */
    private function recursiveSvnFinder( $initDir, $depth = 0 )
    {
        $output = array();
        $files  = scandir( $initDir );

        if ( in_array('format', $files ) )
        {
            return 'repo';
        }

        foreach ( $files as $file )
        {
            if ( $file == '.' || $file == '..' ){ continue; }
            if ( is_dir( $initDir . DIRECTORY_SEPARATOR . $file ) ){

                $tmp = array(
                    'rootPath'     => $initDir . DIRECTORY_SEPARATOR . $file,
                    'isRepository' => false,
                    'children'     => null
                );

                if ( ! $this->hasChildren( $initDir . DIRECTORY_SEPARATOR . $file ) )
                {
                    $tmp['isRepository'] = true;
                    $this->repositoriesFound++;

                    // Use the -i flag to enable this, testing shows it is not required.
                    if ( $this->includeRootRepositories === true && $depth < 1 )
                    {
                        $svnLocation                        = new SvnLocation();
                        $svnLocation->location              = $file;
                        $svnLocation->SVNParentPath         = $initDir . DIRECTORY_SEPARATOR . $file;
                        $this->addRootRepositoryDirectory($depth, $svnLocation);
                        unset($svnLocation);
                    }

                }else{
                    $tmp['children']                        = $this->recursiveSvnFinder( $initDir . DIRECTORY_SEPARATOR . $file, ($depth + 1) );
                    $svnLocation                            = new SvnLocation();
                    $svnLocation->setLocation("$initDir/$file", $this->dir);
                    $svnLocation->SVNParentPath             = $initDir . DIRECTORY_SEPARATOR . $file;
                    $this->addRootRepositoryDirectory($depth, $svnLocation);
                    unset($svnLocation);
                }
                $output[] = $tmp;
            }
        }
        return $output;
    }

    private function addRootRepositoryDirectory( $level, SvnLocation $location )
    {
        if ( ! isset($this->rootRepositoryDirectories[$level]) ){
            $this->rootRepositoryDirectories[$level] = array();
        }

        $this->rootRepositoryDirectories[$level][] = $location;
    }
}
