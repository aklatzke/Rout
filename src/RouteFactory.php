<?php

namespace AKL;

class RouteFactory
{
	protected $progeny = 'AKL\\Route';

	/**
	 * Returns a Route object
	 * @param  String 	$key 		the key for this route
	 * @param  Array 	$options 	extra options to be passed to the route (action, method)
	 * @return Route 			a new Route object
	 */
	public function create( $key, $options )
	{
		return new $this->progeny( $key, $options );
	}
}