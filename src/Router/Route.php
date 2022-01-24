<?php

namespace Pulse\Router;

use ENV;

class Route
{
	protected static $defaultNamespace = '\\';
	protected static $defaultController = 'Home';
	protected static $defaultMethod = 'index';

	private static $groupData = [ // all groups
		[ // root group
			'prefix' => '/',
			'options' => []
		]
	];

	protected static $routes = [
		'get'     => [],
		'post'    => [],
	];

	public function setDefaultController(string $value)
	{
		Route::$defaultController = filter_var($value, FILTER_SANITIZE_STRING);
	}

	public function setDefaultMethod(string $value)
	{
		Route::$defaultMethod = filter_var($value, FILTER_SANITIZE_STRING);
	}

	public static function setDefaultNamespace(string $value)
	{
		Route::$defaultNamespace = filter_var($value, FILTER_SANITIZE_STRING);
		Route::$defaultNamespace = rtrim(Route::$defaultNamespace, '\\') . '\\';
	}

	public static function get(string $from, $to, array $options = [])
	{
		Route::create('get', $from, $to, $options);
	}

	public static function post(string $from, $to, array $options = [])
	{
		Route::create('post', $from, $to, $options);
	}

	protected static function create(string $verb, string $from, string $to, $options)
	{
		$from = filter_var($from, FILTER_SANITIZE_STRING);

		$group = end(Route::$groupData);
		$from = RouteGroups::Path($group['prefix'], $from);
		$options = RouteGroups::Options($group['options'], $options);

		// if api
		if (in_array('api', $options)) {
			$from = "/api$from";
		}

		// While we want to add a route within a group of '/',
		// it doesn't work with matching, so remove them...
		if ($from !== '/') {
			$from = trim($from, '/');
		}

		// If no namespace found, add the default namespace
		if (strpos($to, '\\') === false || strpos($to, '\\') > 0) {
			$to = trim(Route::$defaultNamespace, '\\') . '\\' . $to;
		}

		// Always ensure that we escape our namespace so we're not pointing to
		// \CodeIgniter\Routes\Controller::method.
		$to = '\\' . ltrim($to, '\\');

		// if not duplicated, assign to the table
		if (!isset(Route::$routes[$verb][$from])) {
			$data = [
				'defaultController' => Route::$defaultController,
				'defaultMethod' => Route::$defaultMethod,
			];
			$exp = new RouteExpression($from, $to, $data);
			Route::$routes[$verb][$from] = [
				'route' => $exp->getRoute(),
				'path' => $exp->getPath(),
				'options' => $options
			];
		}
	}

	public static function Route($verb, $from)
	{
		if ($from !== '/') {
			$from = trim($from, '/');
		}
		$routeFinder = new RouteFinder($from, Route::$routes[$verb]);
		$routeFinder->Find();
		$result = $routeFinder->getResult();
		if (!$result) {
			echo redirect404();;
		} else {
			RouteOptions::setOptions($result['options']);
			$class = new $result['class'];
			$result = call_user_func_array(
				array($class, $result['method']),
				$result['data']
			);

			if (RouteOptions::API()) {
				header('Content-Type: application/json');
				if (ENV::ENVIROMENT == 'development') {
					header('Access-Control-Allow-Origin: *');
				}
				$result = json_encode($result);
			}

			// return the final result
			echo $result;
		}
	}

	static public function Group(string $prefix, callable $fn, array $options = [])
	{
		// add settings
		$parent = end(Route::$groupData);
		$current = [
			'prefix' => $prefix,
			'options' => $options
		];

		$currentGroup = RouteGroups::New($parent, $current);
		array_push(Route::$groupData, $currentGroup);

		// run the routes
		$fn();

		// remove settings
		array_pop(Route::$groupData);
	}
}
