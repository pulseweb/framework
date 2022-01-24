<?php
namespace Pulse\Router;

class RouteFinder  
{
    private $from;
    private $paths;

    private $result = null;// default not found 

    public function __construct(string $from, array $paths) {
        if($from == "/"){
            $this->from = array('/');
        }else{
            $this->from = explode('/', $from);
            // first check if the request is json or not
            if($this->from[0] == 'json'){
                $GLOBALS['json'] = true;

                //if is the only one, then it will be '/'
                if(count($this->from) == 1){
                    $this->from = array('/');
                }
                else{
                    unset($this->from[0]);
                    $this->from = array_merge($this->from, []);
                }
            }
        }
        $this->paths = $paths;
		
    }

    public function Find()
    {
        foreach ($this->paths as $path) {
            $match = true; //reset the match
            $data = [];//reset the array
            $part = $path['path'];
            $from = $this->from;

            if(count($from) != count($part)){// maybe optional is skipped 
                continue;
            }

            for ($i=0; $i < count($from); $i++) { 
                if($part[$i]['type'] == 'path'){
                    if($part[$i]['name'] != $from[$i]){
                        $match = false;
                        break;
                    }
                }else if($part[$i]['type'] == 'var'){
                    $data[$part[$i]['name']] =  urldecode($from[$i]);
                }
                else{
                    print($part[$i]['type']);
                }
            }
            if($match){
                $this->result = array(
                    'class' => $path['route']['class'],
                    'method' => $path['route']['method'],
                    'data' => $data,
                    'options' => $path['options']
                );
                return;
            }
        }
    }

    public function getResult()
    {
        return $this->result;
    }
}