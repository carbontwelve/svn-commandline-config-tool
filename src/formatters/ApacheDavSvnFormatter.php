<?php namespace Carbontwelve\Svn\Formatters;

use Carbontwelve\Svn\Interfaces\FormatterInterface;
use Carbontwelve\Svn\SvnLocation;

class ApacheDavSvnFormatter implements FormatterInterface
{

    /** @var array */
    private $input = array();

    /** @var \Twig_Environment */
    private $twig;

    /**
     * @param array $input
     */
    public function __construct(array $input = array())
    {
        $this->input      = $input;
        $twigLoader = new \Twig_Loader_Filesystem(__DIR__ . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR .'templates');
        $this->twig       = new \Twig_Environment($twigLoader);

    }

    /**
     * @return string
     */
    public function render()
    {
        return $this->twig->render('ApacheDavSvn.twig', array( 'locations' => $this->input ));
    }
}
