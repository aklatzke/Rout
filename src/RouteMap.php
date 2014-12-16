<?php

namespace AKL;

const PARAM_REGEX = "#^\{(\d|\w)+\}$#";

class RouteMap
{
    /**
     * Internal tree map of all routes. Used to traverse
     * @var Array
     */
    public $map = [];
    /**
     * Internal hash of ids => Route objects. Used for lookups
     * @var Array
     */
    public $hash = [];
    /**
     * Adds a route to the route tree
     * @param Route $route     route object to be added to the tree
     * @param String $uniq      unique id that is mapped to the route
     */
    public function add($route, $uniq)
    {
        $routeKeys = $route->getKeys();
        $prevKeys = [];
        $lastKey = end($routeKeys);
        $this->hash[$uniq] = $route;
        $newTarget;

        foreach ($routeKeys as $i => $key) {
            $isParam = preg_match(PARAM_REGEX, $key);
            $target = &$this->map;

            if (!isset($target[$key])) {
                # if previous keys are not empty (i.e. this is not the first url segment)
                if (!empty($prevKeys)) {
                    # iterate over previous keys and find our place in the route map
                    foreach ($prevKeys as $index => $value) {
                        if (preg_match(PARAM_REGEX, $value)) {
                            $value = "?";
                        }

                        $target = &$target[$value];
                    }
                }

                $replKey = $key;
                # if it is a param, use the key '?' so that it is recognizable
                if ($isParam) {
                    $replKey = '?';
                }
                # create a node if it doesn't exist
                if (!isset($target[$replKey])) {
                    $target[$replKey] = $this->createRouteNode();
                    $target[$replKey]["isParam"] = $isParam;
                }
                # if it's a param, set the param data to the route key minus {}
                if ($isParam) {
                    $target[$replKey]["param"] = preg_replace("#({|})#", '', $key);
                }
                # if this is the last key, set an ID related to the route and the isFinal flag
                if ($key === $lastKey) {
                    $target[$replKey]["isFinal"] = true;
                    $target[$replKey]["id"] = $uniq;
                }

            }

            $prevKeys[] = $key;
        }

        return $this;
    }

    /**
     * Finds a route by path and returns the route data
     * @param  Array $routeKeys     keys to the route (as parsed from REQUEST_URI)
     * @return  Array                returns a route data object with params, id and children attached
     */
    public function find($routeKeys)
    {
        $target = $this->map;
        $id = false;
        $prevKeys = [];
        $collectedArgs = [];
        # trim any empty indexes
        $routeKeys = array_filter($routeKeys);
        # iterate over the route keys and find the matching path
        foreach ($routeKeys as $index => $key) {
            # if the key itself is set, that is our target
            if (isset($target[$key])) {
                $target = $target[$key];
            }
            # if there is a param string, go ahead and keep moving
            else if (isset($target["?"])) {
                $target = $target["?"];
                # set the param value
                $collectedArgs[$target["param"]] = $key;
            }
            # if this is the last of the request segments, pass the collected param data
            # attach our collected param data
            if (($index + 1) === count($routeKeys)) {
                $target["value"] = $collectedArgs;
            }
        }
        # return the data that corresponds to this URL path (id, params)
        return $target;
    }
    /**
     * Returns a specific route by ID
     * @param  String $id
     * @return mixed         returns the route object associated with the specific id or false if not found
     */
    public function get($id)
    {
        if (isset($this->hash[$id])) {
            return $this->hash[$id];
        }

        return false;
    }

    /**
     * Creates the basic route node architecture
     * @return Array     returns a default node of the route tree
     */
    public function createRouteNode()
    {
        return [
            "id" => "",
            "isFinal" => false,
            "param" => false,
            "isParam" => false,
        ];
    }
    /**
     * Returns the contents of $this->map
     * @return Array     all routes as a tree
     */
    public function getMap()
    {
        return $this->map;
    }
}
