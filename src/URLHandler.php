<?php

namespace AKL;

class URLHandler
{
    protected $domain = '';
    protected $query = [];
    protected $hasRun = false;

    /**
     * Parses a route into individual pieces
     * @param  String  $requestStr            the string to be parsed, will almost always be REQUEST_URI
     * @return Array                             collection of URL segments
     */
    public function parseRoute($requestStr)
    {
        $args = explode("/", $requestStr);
        # if the first argument is for some reason empty
        # such as in the case of a misconfigured .htaccess
        # remove it from the beginning of the array
        if ($args[0] === "")
        {
            array_shift($args);
        }
        # the last argument in the URL keys will contain any url parameters
        $lastArg = $args[ count($args) - 1 ];
        # detect and remove URL parameters from the route keys
        if( preg_match("#(\d|\w|\s){0,}\?(\d|\w){0,}\=(.)+(\n|&|$)#", $lastArg) )
        {
            # if this has arguments, remove them from the final key
            $temp = explode("?", $lastArg);
            $args[ count($args) - 1 ] = $temp[0];
            # parse the URL query into the $query array
            parse_str(parse_url($requestStr)['query'], $query);
            $this->query = $query;
        }
        # set the domain
        $this->domain = $_SERVER['HTTP_HOST'];
        # this catches the index page route
        if( $args[0] === "" ) $args[0] = "/";
        # set this internal flag so that other methods know variables are available
        $this->hasRun = true;

        return $args;
    }
    /**
     * Getter $this->domain
     * @return String domain of request
     */
    public function getDomain(  )
    {
        if( ! $this->hasRun ) $this->parseRoute( $_SERVER['REQUEST_URI'] );

        return $this->domain;
    }

    /**
     * Getter $this->domain
     * @return String domain of request
     */
    public function getQuery(  )
    {
        if( ! $this->hasRun ) $this->parseRoute( $_SERVER['REQUEST_URI'] );

        return $this->query;
    }
}
