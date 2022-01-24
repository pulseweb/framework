<?php

namespace Pulse\Debug;

class Errors
{
    protected $throwable;
    protected $viewPath;

    function __construct(\Throwable $th)
    {
        $this->throwable = $th;
        $sp = DIRECTORY_SEPARATOR;
        $this->viewPath = __DIR__ .  "${sp}View${sp}";
    }

    public function trace()
    { // TODO if API
        $message = $this->throwable->getMessage();
        $trace = $this->throwable->getTrace();

        include $this->viewPath . "trace.php";
        exit();
    }
}
