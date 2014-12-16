<?php

namespace AKL;

/**
 *	AKL\Rout is a static interface that controls
 *	all underlying calls to the framework
 */

final class Rout
{
	protected static $router;
	protected static $handler;
	protected static $map;

	/**
	 * Must be called with every method call to make sure we are using singleton instances
	 * @return NULL
	 */
	public static function start()
	{
		if( ! isset( self::$router ) )
		{
			self::$router = new RouteFactory(  );
		}

		if( !isset( self::$handler ) )
		{
			self::$handler = new URLHandler(  );
		}

		if( !isset( self::$map ) )
		{
			self::$map = new RouteMap(  );
		}
	}

	/**
	 * Calls URLHandler::parseRoute with provided arguments
	 * @param  String  $requestString    	request string
	 * @return Array 			parsed  URL segments
	 */
	public static function parseRoute( $requestString )
	{
		self::start();

		return self::$handler->parseRoute( $requestString, $leadingWhitespace );
	}

	/**
	 * Runs the URL action associated with a request string
	 * @param  String $requestString  	the URL request
	 * @return  mixed                		returns the result of the action call
	 */
	public static function runRoute( $requestString )
	{
		$routeData = self::$map->find( self::$handler->parseRoute($requestString), true );
		# no routeID is returned - this is a dead route
		if( ! isset( $routeData["id"] ) ){ echo "404"; die(); }

		$route = self::$map->get($routeData["id"]);
		# a route ID was returned but no route exists, abort
		if( ! $route ){  echo "404"; die(); }
		return $route->run( $routeData );
	}

	/**
	 * Takes an array of routes and registers them
	 * @param  Array $arr 	collection of route objects
	 * @return  Boolean      true
	 */
	public static function routeCollection( $arr )
	{
		self::start();

		foreach( $arr as $key => $arr )
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
	public static function add( $key, $options )
	{
		self::start();

		# break route into route keys
		$keys = self::$handler->parseRoute($key);
		$route = self::$router->create( $keys, $options );

		# generate a unique ID that needs passed to the route map
		$uniq = uniqid();
		return self::$map->add( $route, $uniq );
	}

	public static function getMap(  )
	{
		self::start();

		return self::$map->getMap();
	}
}