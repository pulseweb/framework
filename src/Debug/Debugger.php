<?php

namespace Pulse\Debug;

use ENV;

class Debugger
{
    static public function debug()
    {
        if (ENV::ENVIROMENT == 'development') {
            include implode(DIRECTORY_SEPARATOR, [__DIR__, 'View', 'debug.php']);
        }
    }
}
