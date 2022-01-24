<?php

namespace Pulse\Model;

use ENV;
use Exception;
use Pulse\Validation\Validation;

class BaseModel
{
    protected $db;
    protected $validate;
    protected $stmt; // the selected statement
    protected $justName;
    protected $joinedTables = [];

    function __construct()
    {
        if (!isset($this->table)) {
            throw new Exception("Undefined Table");
        }
        $this->justName = $this->table;

        if (!isset($this->fields)) {
            throw new Exception("Undefined Fields");
        }
        $this->db = new DataBase(ENV::DB_HOST, ENV::DB_NAME, ENV::DB_USER, ENV::DB_PASS);

        $this->validate = new Validation($this->fields);
    }

    /**
     * FILTERS
     */
    public function where(string $stem, string $value)
    {
        $this->db->where($stem, $value);
        return $this;
    }

    public function wherein(string $stem, array $list)
    {
        $this->db->wherein($stem, $list);
        return $this;
    }

    public function condition(string $cond)
    {
        $this->db->condition($cond);
        return $this;
    }

    public function order(string $order)
    {
        $this->db->order($order);
        return $this;
    }

    public function group(string $group)
    {
        $this->db->group($group);
        return $this;
    }

    public function limit(string $limit)
    {
        $this->db->limit($limit);
        return $this;
    }

    /**
     * Modifier and joiners
     */
    public function join($table, $field, $key, $tableName, $join = '')
    {
        $thisTable = $this->table;
        $condition = "$tableName.$field = $thisTable.$key";

        $this->db->join($table, $condition, $join, $tableName);
    }

    /**
     * OPERATIONS
     */
    public function select($data = null)
    {
        if ($data == null) {
            $fields = array_merge([$this->primaryKey], $this->fields);
            $table = $this->table;
            $data = array_map(function ($v) use ($table) {
                return "${table}.${v} as ${v}";
            }, $fields);

            // if joined
            if (!empty($this->joinedTables)) {
                foreach ($this->joinedTables as $table) {
                    $fields = array_merge([$table['instance']->primaryKey], $table['instance']->fields);
                    $tablename = $table['name'];
                    $select = array_map(function ($v) use ($tablename) {
                        return "${tablename}.${v} as ${tablename}_${v}";
                    }, $fields);
                    $data = array_merge($data, $select);
                }
            }
        }

        $this->db->select($data);
        $this->stmt = $this->db->table($this->table)->run();
        if ($this->db->getAutoReset()) {
            $this->joinedTables = [];
        }
        return $this;
    }

    public function insert(array $data)
    {
        foreach ($data as $key => $value) {
            if (!in_array($key, $this->fields)) {
                throw new Exception("Undefined Field: $key");
            }
        }

        if (isset($_SESSION['userid'])) {
            $data['creator'] = $_SESSION['userid'];
            $data['updator'] = $_SESSION['userid'];
        }
        return $this->db->insert($this->table, $data);
    }

    public function insertMany(array $data)
    {
        foreach ($data as $key => $row) {
            if (!is_array($row)) throw new Exception("Row at index $key should be array");

            // check feilds
            $keys = array_keys($row);
            foreach ($keys as $key_) {
                if (!in_array($key_, $this->fields)) throw new Exception("Undefined Field: $key_");
            }

            // set the user who doing it
            $data[$key]['creator'] = $_SESSION['userid'];
            $data[$key]['updator'] = $_SESSION['userid'];
        }

        // do the insert
        return $this->db->insertMany($this->table, $data);
    }

    public function update(array $data)
    {
        if (isset($_SESSION['userid'])) {
            $data['updator'] = $_SESSION['userid'];
        }
        $returns = $this->db->table($this->table)->update($data)->run();
        if ($this->db->getAutoReset()) {
            $this->joinedTables = [];
        }
        return $returns;
    }

    public function delete()
    {
        return $this->db->table($this->table)->delete()->run();
    }

    public function disable()
    {
        if (isset($_SESSION['userid'])) {
            $data['updator'] = $_SESSION['userid'];
        }
        $returns = $this->db->table($this->table)->update(['status' => 0])->run();
        if ($this->db->getAutoReset()) {
            $this->joinedTables = [];
        }
        return $returns;
    }

    public function enable()
    {
        if (isset($_SESSION['userid'])) {
            $data['updator'] = $_SESSION['userid'];
        }
        $returns = $this->db->table($this->table)->update(['status' => 1])->run();
        if ($this->db->getAutoReset()) {
            $this->joinedTables = [];
        }
        return $returns;
    }

    public function SQL(string $query, array $data = [])
    {
        $stmt = $this->db->SQL($query);
        $stmt->execute($data);

        $this->stmt = $stmt;
        return $this;
    }

    public function fetch()
    {
        return $this->db->Fetch($this->stmt);
    }

    public function fetchAll()
    {
        return $this->db->FetchAll($this->stmt);
    }

    public function ValidateTool()
    {
        return $this->validate;
    }

    public function withDeleted()
    {
        $this->db->showWithDeleted();
        return $this;
    }

    public function justDeleted()
    {
        $this->db->showJustDeleted();
        return $this;
    }
}
