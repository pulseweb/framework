<?php

namespace Pulse\Etc;

use Exception;

class EtcData
{
    private $dir;
    private $path;

    public function __construct(string $var)
    {
        $tree = explode(".", $var);
        $this->path = implode(
            DIRECTORY_SEPARATOR,
            array_merge(
                [APP_PATH, "storage", 'etc'],
                $tree
            )
        ) . ".conf";
        $this->dir = dirname($this->path);
    }

    public function get()
    {
        if (!is_file($this->path)) return null;

        $file = fopen($this->path, 'r');

        if (!$file) return null;

        // check data
        $data = fread($file, filesize($this->path));
        $json = json_decode($data, true);

        if (is_array($json)) {
            $data = $json;
        }

        fclose($file);
        return $data;
    }

    public function set($data)
    {
        // remove empty files // TODO test functionality
        if (empty($data) && is_file($this->path)) {
            unlink($this->path);
        }

        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
        }

        // open the file
        $file = fopen($this->path, "w");
        if (!$file) {
            throw new Exception("cant open etc file");
        }

        // check data
        if (is_array($data)) {
            $data = json_encode($data);
        }

        fwrite($file, $data);
        fclose($file);
    }
}
