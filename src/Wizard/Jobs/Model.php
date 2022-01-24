<?php

namespace Pulse\Wizard\Jobs;

use ENV;
use Exception;
use Pulse\Model\DataBase;
use Pulse\Model\ModelField;

class Model
{
    protected $modelsPath = APP_PATH . DIRECTORY_SEPARATOR . "Models";
    protected $modelName = "App\\Models\\";

    protected $db;
    protected $defaultFields = [
        'status' => [
            'type' => 'tinyint(1)',
            'default' => '1'
        ],
        'created' => [
            'type' => 'timestamp',
            'default' => 'current_timestamp()'
        ],
        'creator' => [
            'type' => 'varchar(9)',
            'key' => 'index',
            'null' => true
        ],
        'updated' => [
            'type' => 'timestamp',
            'default' => 'current_timestamp()',
            'extra' => 'ON UPDATE current_timestamp()'
        ],
        'updator' => [
            'type' => 'varchar(9)',
            'key' => 'index',
            'null' => true,
        ]
    ];

    public function __construct()
    {
    }

    public function index(array $data)
    {
        print_r($data);
    }

    public function list(array $data = [])
    {
        $list = $this->getList();
        foreach ($list as $key => $model) {
            $num = $key + 1;
            echo "# ${num}\t$model" . PHP_EOL;
        }
        echo PHP_EOL . "Total: " . count($list) . PHP_EOL;
    }

    public function build(array $data = [])
    {
        $start = microtime(true);
        $this->db = new DataBase(ENV::DB_HOST, ENV::DB_NAME, ENV::DB_USER, ENV::DB_PASS);
        $list = $this->getList();
        foreach ($list as $model) {
            $this->buildTables($this->modelName . $model);
        }
        $takes = round(microtime(true) - $start, 3);
        echo "executed in ${takes}s" . PHP_EOL;
    }


    /**
     * PRIVATE Functions
     */

    private function getList()
    {
        $list = [];
        foreach (new \DirectoryIterator($this->modelsPath) as $fileInfo) {
            if ($fileInfo->isDot()) continue;
            $list[] = str_replace(".php", "", $fileInfo->getFilename());
        }
        sort($list);
        return $list;
    }

    private function buildTables($table)
    {
        $table_obj = new $table();
        $name = $table_obj->justTable();

        if (!method_exists($table_obj, "struct")) {
            echo "skipping $name" . PHP_EOL;
            return;
        }
        $table_obj->struct();

        $setting = array_merge(
            ...array_map(
                function ($v) {
                    return $v->getField();
                },
                ModelField::getAll()
            )
        );

        $setting = array_merge($setting, $this->defaultFields);

        foreach ($setting as $key => $value) {
            $setting[$key]['line'] = $this->buildFieldLine($key, $value);
        }
        $body = implode(',', array_column($setting, 'line'));

        $rand = rand(1000, 5000);
        $sql = "CREATE TABLE IF NOT EXISTS `$name` ($body) AUTO_INCREMENT = $rand";
        $stmt = $this->db->SQL($sql);
        $stmt->execute();
    }

    private function buildFieldLine($name, $data)
    {
        $line = "`$name` ${data['type']}";
        // is NULL ?
        if (!(isset($data['null']) && $data['null'])) {
            $line .= " NOT NULL";
        }

        // has default
        if (isset($data['default'])) {
            $line .= " DEFAULT ${data['default']}";
        }

        // extra Features
        if (isset($data['extra'])) {
            $line .= " ${data['extra']}";
        }

        // keys
        if (isset($data['key'])) {
            switch ($data['key']) {
                case 'primary':
                    $line .= ", PRIMARY KEY (`$name`)";
                    break;
                case 'unique':
                    $line .= ", UNIQUE KEY (`$name`)";
                    break;
                case 'index':
                    $line .= ", KEY (`$name`)";
                    break;
                default:
                    throw new Exception("Undefined key name");
                    break;
            }
        }
        return $line;
    }
}
