<?php

namespace Pulse\Logs;

use Exception;

class Logger
{
    protected $type;
    protected $path;

    private $availableTypes = [
        "error",
        "database",
        "access",
        'custom'
    ];

    public function __construct($type)
    {
        $this->type = $type;
        $this->path = implode(DIRECTORY_SEPARATOR, [
            ROOT_DIR, "storage", "logs", date("Y_m")
        ]);

        if (!in_array($type, $this->availableTypes)) {
            throw new Exception("Unknown log type");
        }

        // check if the dir is not existed and create it
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    public function insert($type, $data)
    {
        $line = [
            "type" => $type,
            "user" => $_SESSION['userid'] ?? "guest",
            "time" => date("Y-m-d h:i:s"),
            "url" => $_SERVER['REQUEST_URI'],
            "method" => $_SERVER['REQUEST_METHOD'],
            "ip" => $_SERVER['REMOTE_ADDR'],

            "data" => $data
        ];

        $row = json_encode($line);
        $file_name = $this->path . DIRECTORY_SEPARATOR . $this->type . ".log";
        $file = fopen($file_name, "a", 1);
        if ($file) {
            fwrite($file, $row . PHP_EOL);
            fclose($file);
        }
    }
}
