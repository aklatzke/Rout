<?php

namespace AKL;

class URLHandler
{

    protected $params = [];
    /**
     * Parses a route into individual pieces
     * @param  String  $requestStr            the string to be parsed, will almost always be REQUEST_URI
     * @return Array                             collection of URL segments
     */
    public function parseRoute($requestStr)
    {
        $args = explode("/", $requestStr);

        if ($args[0] === "")
        {
            array_shift($args);
        }

        $lastArg = $args[ count($args) - 1 ];
        # this should only ever run once per request so we will set params on this singleton
        if( preg_match("#(\d|\w|\s){0,}\?(\d|\w){0,}\=(.)+(\n|&|$)#", $lastArg) )
        {
            $temp = explode("?", $lastArg);
            $args[ count($args) - 1 ] = $temp[0];
        }

        if( $args[0] === "" ) $args[0] = "/";

        return $args;
    }
}
