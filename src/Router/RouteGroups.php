<?php
namespace Pulse\Router;

class RouteGroups{
    static public function New($parent, $current)
    {
		$prefix = RouteGroups::Path($parent['prefix'], $current['prefix']);
        $options = RouteGroups::Options($parent['options'], $current['options']);

        return [
            'prefix' => $prefix,
            'options' => $options,
        ];
    }

    static public function Path($parent, $current)
    {
        $newPrefixArray = array_filter([
			...explode('/',$parent),
			...explode('/',$current)
		]);

		if(count($newPrefixArray) == 0){
			$newPrefix = '/';
		}else{
			$newPrefix = '/' . implode('/', $newPrefixArray) . '/';
		}
        return $newPrefix;
    }

    static public function Options($parent, $current)
    {
        // merge array from inside
        $parentArray = array_filter($parent, 'is_array');
        $currentArray = array_filter($current, 'is_array');
        
        // get keys
        $parentKeys = array_keys($parentArray);
        $currentKeys = array_keys($currentArray);

        // intersection
        $intersect = array_intersect($parentKeys, $currentKeys);

        // merge intersection
        $intersectionArray = array_map(function($v) use($parentArray, $currentArray){
            return array_merge(
                $parentArray[$v],
                $currentArray[$v]
            );
        }, $intersect);

        //final merge
        return array_merge($parent, $current, $intersectionArray);
    }
}