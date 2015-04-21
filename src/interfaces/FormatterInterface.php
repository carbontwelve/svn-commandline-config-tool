<?php namespace Carbontwelve\Svn\Interfaces;

interface FormatterInterface
{
    public function __construct( array $input = array() );

    public function render();
}
