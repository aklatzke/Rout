<?php

namespace AKL;

class FilterMap
{
	protected $map = [];

	public function add( $name, $action, $designation = 'before' )
	{
		# if the action has a :: designation, split it into its parts for the method call
		if( is_string($action) )
		{
			$action = strpos($action, "::") !== false ? explode("::", $action) : $action;
		}

		# the designation of the item determines when it runs
		$this->map[$name] = [ 'action' => $action, 'designation' => 'before' ];

		return $this->map;
	}

	public function run( Array $filterList, $data, $designation = 'before' )
	{
		foreach( $filterList as $filter )
		{
			d( $this->map[$filter]['designation'],  $designation);
			if( isset($this->map[$filter]) && $this->map[$filter]['designation'] === $designation )
			{
				$res = call_user_func( $this->map[$filter]['action'], $data );

				if( $res === false )
					return $res;
			}
		}

		return true;
	}
}