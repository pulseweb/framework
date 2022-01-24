<?php

namespace Pulse\Wizard;

use System\Wizard\Commands;

class Wizard
{
    public static function run(array $cmd)
    {
        $commands = new Commands();

        $fn = $cmd[0];
        if (method_exists($commands, $fn)) {
            $commands->$fn();
        } else {

            $commands->run($cmd);
        };
    }
}
