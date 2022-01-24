<?php
namespace Pulse\Router;

class RouteOptions
{
    private static $options = [];

    public static function setOptions($options)
    {
        RouteOptions::$options = $options;
    }

    public static function getOptions()
    {
        return RouteOptions::$options;
    }

    public static function API(){
        return in_array('api', RouteOptions::$options);
    }
}