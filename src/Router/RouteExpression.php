<?php
namespace Pulse\Router;

use Exception;

class RouteExpression
{
    protected $from;
    protected $to;
    protected $data;

    protected $route;
    protected $path;

    public function __construct(string $from,string $to, array $data) {
        $this->from = $from;
        $this->to = $to;
        $this->data = $data;

        $this->SourceExpression();
        $this->DestinationExpression();
    }

    private function SourceExpression()
    {
        if ($this->from == '/')
		{
            $this->path = [
                [
                    'type' => 'path',
                    'name' => '/'
                ]
            ];
            return;
		}

        $sections= explode("/",$this->from);

        for ($i=0; $i < count($sections); $i++) { 
            $var = $this->checkVariable($sections[$i]);
            if($var['type'] == false){
                $var = $this->checkPath($sections[$i]);
                if($var['type'] ==false){
                    throw new Exception("Bad Route Path format: `" . $sections[$i] . "`");
                }
            }
            $sections[$i] = $var;
        }
        $this->path = $sections;
    }

    private function DestinationExpression()
    {   
        $reg = "/^(?<class>([\\\]*[a-zA-Z_\d]+)+)(::(?<method>[a-zA-Z_\d]+))?$/";
        preg_match($reg, $this->to, $matches);

        $this->route =  [
            'class' => $matches['class'],
            'method' => $matches['method']??$this->data['defaultMethod']
        ];
    }

    public function getRoute()
    {
        return $this->route;
    }
    public function getPath()
    {
        return $this->path;
    }

    private function checkVariable($name)
    {
        $reg = "/^{(?<name>[a-zA-Z_\d]+)?}$/";
        $m = preg_match($reg, $name, $matches);

        if($m){
            return [
                'type' => 'var',
                'name' => $matches['name'],
                // 'optional' => $matches['optional']?true: false
            ];
        }
        else{
            return [
                'type' => false
            ];
        }
    }
    
    private function checkPath($name)
    {
        $reg = "/^(?<name>[a-zA-Z_\d\?]+)$/";
        $m = preg_match($reg, $name, $matches);

        if($m){
            return [
                'type' => 'path',
                'name' => $matches['name'],
            ];
        }
        else{
            return [
                'type' => false
            ];
        }
    }
}