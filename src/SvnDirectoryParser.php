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

    public function __construct( $dir = '', Filesystem $fileSystem )
    {
        $this->fileSystem = $fileSystem;
        $this->dir        = $this->validateDirectory( $dir );
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

    public function getFoundRootRepositories()
    {
        return $this->rootRepositoryDirectories;
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

                $children = $this->recursiveSvnFinder( $initDir . DIRECTORY_SEPARATOR . $file, ($depth + 1) );

                if ( $children === 'repo' )
                {
                    $tmp['isRepository'] = true;
                    $this->repositoriesFound++;

                    // Repositories in the root dir are handled correctly and do not require config
                    /*if ( $depth < 1 )
                    {
                        $svnLocation                        = new SvnLocation();
                        $svnLocation->location              = $file;
                        $svnLocation->SVNParentPath         = $initDir . DIRECTORY_SEPARATOR . $file;
                        $this->rootRepositoryDirectories[]  = $svnLocation;
                        unset($svnLocation);
                    }*/

                }else{
                    $tmp['children']                    = $children;
                    $svnLocation                        = new SvnLocation();
                    $svnLocation->setLocation("$initDir/$file", $this->dir);
                    $svnLocation->SVNParentPath         = $initDir . DIRECTORY_SEPARATOR . $file;
                    $this->rootRepositoryDirectories[]  = $svnLocation;
                    unset($svnLocation);
                }
                $output[] = $tmp;
            }
        }
        return $output;
    }
}
