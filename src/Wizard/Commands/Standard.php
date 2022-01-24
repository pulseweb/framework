<?php

namespace Pulse\Wizard\Commands;

use Exception;

trait Standard
{
    // use Models;

    public function help()
    {
        echo "Help...." . PHP_EOL;
    }

    public function serve()
    {
        echo "serve on port 8000" . PHP_EOL;
        exec("php -S 127.0.0.1:8000 -t public -d display_errors=on ");
    }

    public function run($cmd)
    {
        $src = explode(":", $cmd[0]);
        $class = "Pulse\\Wizard\\Jobs\\" . ucfirst($src[0]);

        if (class_exists($class)) {
            $instance = new $class;
            if (isset($src[1])) {
                $instance->{$src[1]}([]);
            } else {
                $instance->index([]);
            }
        } else {
            $this->help();
        }
    }
}
