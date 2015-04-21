<?php namespace Carbontwelve\Svn;

class SvnLocation
{

    public $baseLocation    = 'svn';
    public $location        = '';
    public $SVNParentPath   = '';
    public $authName        = 'Subversion Repository';
    public $authUserFile    = '/etc/subversion/passwd';

    /** @var bool */
    private $authEnabled    = true;

    /**
     * @param $enabled
     * @return void
     */
    public function setAuthEnabled( $enabled )
    {
        $this->authEnabled = (bool) $enabled;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return "/" . $this->baseLocation . $this->location;
    }

    public function setLocation( $location, $baseDir )
    {
        $this->location = str_replace('\\','/', str_ireplace($baseDir, '', $location) );
    }

    /**
     * @return bool
     */
    public function isProtected()
    {
        return $this->authEnabled;
    }

}
