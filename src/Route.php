<?php

namespace AKL;

class Route
{
    protected $keys;
    protected $options;
    protected $action;
    protected $ajax = false;
    protected $params = [];
    protected $method = ["*"];

    public function __construct($keys, $options)
    {
        $this->keys = $keys;
        $this->options = $options;
        $this->action = $options['action'];

        if (isset($options["ajax"])) {
            $this->ajax = $options["ajax"];
        }

        if (isset($options["method"])) {
            $this->method = array_map(function ($n) {
                return strtoupper($n);
            }, $options["method"]);
        }
    }

    /**
     * Runs the route action
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function run($data)
    {
        if ($this->checkRequestMethod() !== true) {
            echo '404';
            die();
        }

        $args = $data["value"];
        $callback = $this->action;
        # if it's a string, it's a method call
        if (is_string($callback)) {
            # split the string at the method name
            $temp = explode('::', $callback);

            $callback = array();
            # create an instance of our object/controller for call_user_func
            $callback[0] = new $temp[0];
            $callback[1] = $temp[1];
        }
        # if processed over AJAX, JSON encode and die immediately
        if ($this->ajax) {
            echo json_encode(call_user_func($callback, $args));
            die();
        }
        # if none of the above happened, it's just a function call
        echo call_user_func($callback, $args);
        die();
    }

    /**
     * Returns the keys for the route
     * @return Array array of route keys
     */
    public function getKeys()
    {
        return $this->keys;
    }

    /**
     * Checks to make certain this route allows the request method
     * @return bool     true if method allowed
     */
    public function checkRequestMethod()
    {
        # if set, all methods are allowed
        if ( in_array("*", $this->method) ) {
            return true;
        }

        $reqMethod = strtoupper($_SERVER['REQUEST_METHOD']);
        $allowedMethods = $this->method;

        # return the result of the lookup
        # will return false and therefore trigger a 404 for
        # unallowed methods
        return array_search($reqMethod, $allowedMethods) !== false;
    }
}
