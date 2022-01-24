<?php

namespace Pulse;

use ENV;
use Pulse\Debug\Errors;
use Pulse\Logs\Logger;
use Pulse\Router\Route;

class Pulse
{

    protected $route;
    protected $method;

    protected $router;

    public function __construct()
    {
        require __DIR__ . "/Common.php";
    }

    public function init($route, $method)
    {
        $this->route = $route;
        $this->method = $method;
    }

    public function run()
    {
        // check if the site if down or not
        if (ENV::ENVIROMENT == "maintenance") {
            header("HTTP/1.0 503 Service unavailable");
            echo view("maintenance");
            return;
        } else if (ENV::ENVIROMENT == "development") {
            $GLOBALS['queries'] = []; // init the query holder
        }

        try {
            // set time zone
            date_default_timezone_set(ENV::TIME_ZONE);

            // get all routes
            require $this->RoutePath();

            //check requested route
            Route::Route($this->method, $this->route);
        } catch (\Throwable $th) {
            header($_SERVER["SERVER_PROTOCOL"] . ' 500 Internal Server Error', true, 500);
            if (ENV::ENVIROMENT == "development") {
                $error = new Errors($th);
                $error->trace();
            } else { // TODO make a better view
                $logger = new Logger("error");
                $logger->insert("error", [
                    "msg" => $th->getMessage(),
                    'file' => str_replace(ROOT_DIR, "", $th->getFile()),
                    'line' => $th->getLine()
                ]);
            }
        }
    }

    private function RoutePath()
    {
        $sp = DIRECTORY_SEPARATOR;
        $path = APP_PATH . "${sp}Config${sp}Routes.php";
        return $path;
    }
}
