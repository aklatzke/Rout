<?php

namespace AKL;

/**
 *    AKL\Rout is a static interface that controls
 *    all underlying calls to the framework
 */

const ROUTE_NOT_FOUND = 'The page you are requesting could not be found.';

final class Rout
{
    protected static $routeFactory;
    protected static $urlHandler;
    protected static $map;
    protected static $filters;

    /**
     * Must be called with every method call to make sure we are using singleton instances
     * @return NULL
     */
    public static function start()
    {
        if (!isset(self::$routeFactory)) {
            self::$routeFactory = new RouteFactory();
        }

        if (!isset(self::$urlHandler)) {
            self::$urlHandler = new URLHandler();
        }

        if (!isset(self::$map)) {
            self::$map = new RouteMap();
        }

        if (!isset(self::$filters)) {
            self::$filters = new FilterMap();
        }
    }

    /**
     * Calls URLHandler::parseRoute with provided arguments
     * @param  String  $requestString        request string
     * @return Array             parsed  URL segments
     */
    public static function parseRoute($requestString)
    {
        self::start();

        return self::$urlHandler->parseRoute($requestString, $leadingWhitespace);
    }

    /**
     * Runs the URL action associated with a request string
     * @param  String $requestString      the URL request
     * @return  mixed                        returns the result of the action call
     */
    public static function runRoute( $requestString, $extra = [] )
    {
        self::start();

        $routePath = self::$urlHandler->parseRoute($requestString);

        $routeData = self::$map->find($routePath);
        # no routeID is returned - this is a dead route
        if (!isset($routeData["_id"]))
        {
            self::notFound(ROUTE_NOT_FOUND);
        }

        $input = self::$urlHandler->getQuery();
        $extra = array_merge( $extra, ["_query" => $input] );

        $route = self::$map->get($routeData["_id"]);
        # a route ID was returned but no route exists, abort
        if ( ! $route)
        {
            self::notFound(ROUTE_NOT_FOUND);
        }
        $filterList = $route->getFilters();

       $res = self::$filters->run( $filterList, $extra, 'before' );

        if( ! $res )
        {
            self::notFound(ROUTE_NOT_FOUND);
        }

        $route->run($routeData, $extra);
         self::$filters->run( $filterList, $extra, 'after' );

         return true;
    }
    /**
     * Getter $this->params
     * @return Array            array of URL params
     */
    public function getURLParams( )
    {
        self::$start();

        return self::$urlHandler->getParams();
    }

    /**
     * Takes an array of routes and registers them
     * @param  Array $arr     collection of route objects
     * @return  Boolean      true
     */
    public static function routeCollection($arr)
    {
        self::start();

        foreach ($arr as $key => $arr)
        {
            self::add($key, $arr);
        }

        return true;
    }

    /**
     * Registers a single route
     * @param String $key         the url key for this route
     * @param Array $options  options for the route
     */
    public static function add($key, $options)
    {
        self::start();
        # break route into route keys
        $keys = self::$urlHandler->parseRoute($key);
        $route = self::$routeFactory->create($keys, $options);
        # generate a unique ID that needs passed to the route map
        $uniq = uniqid();
        return self::$map->add($route, $uniq);
    }

    /**
     * Returns the internal map for the RouteMap singleton
     * @return Array    tree of routes
     */
    public static function getMap()
    {
        self::start();

        return self::$map->getMap();
    }

    /**
     * Standard "route not found" action. Define the helper function 'rout_not_found'
     * to override this behavior
     * @param  [type] $message [description]
     * @return [type]          [description]
     */
    public static function notFound($message)
    {
        # if the helper 'rout_not_found' has been defined, run it
        # instead of using the default 404 functionality
        if( function_exists('rout_not_found') ) return rout_not_found();

        header('HTTP/1.0 404 Not Found');
        echo "<h4>404</h4>";
        echo $message;
        die();
    }
    /**
     * Getter
     * @return string request domain
     */
    public static function getDomain()
    {
        self::start();

        return self::$handler->getDomain();
    }
/**
 * Adds a filter to the FilterMap singleton
 * @param string $name        name of the filter
 * @param string $action      fed to call_user_func internally - method name or function name
 * @param string $designation 'before' or 'after'
 */
    public static function addFilter( $name, $action, $designation = 'before' )
    {
        self::start();

        return self::$filters->add($name, $action, $designation);
    }
}
