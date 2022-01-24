<?php

namespace Pulse\Model;

class ModelField
{
    static private $instances = [];

    protected $name;
    protected $struct = [];

    static public function New($name)
    {
        // init with name
        $instance = new ModelField();
        $instance->name = $name;

        // add to instance
        ModelField::$instances[] = $instance;
        // var_dump(ModelField::$instances);
        return $instance;
    }

    static public function getAll()
    {
        // save and reset
        $result = ModelField::$instances;
        ModelField::$instances = [];

        return $result;
    }

    public function getField()
    {
        return [
            $this->name => $this->struct
        ];
    }

    public function ID()
    {
        $this->struct = [
            'type' => 'bigint(20)',
            'key' => 'primary',
            'extra' => 'AUTO_INCREMENT'
        ];
        return $this;
    }

    /**
     * TYPES
     */
    public function String($num = 100)
    {
        $this->struct['type'] = "varchar(${num})";
        return $this;
    }

    public function Int($num = 11)
    {
        $this->struct['type'] = "int(${num})";
        return $this;
    }

    /**
     * KEYS
     */

    public function index()
    {
        $this->struct['key'] = "index";
        return $this;
    }

    public function unique()
    {
        $this->struct['key'] = "unique";
        return $this;
    }
}
