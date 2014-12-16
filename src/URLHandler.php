<?php

namespace AKL;

class URLHandler
{
    /**
     * Parses a route into individual pieces
     * @param  String  $requestStr            the string to be parsed, will almost always be REQUEST_URI
     * @return Array                             collection of URL segments
     */
    public function parseRoute($requestStr)
    {
        $args = explode("/", $requestStr);

        if ($args[0] === "") {
            array_shift($args);
        }

        return $args;
    }
}
