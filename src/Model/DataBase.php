<?php
namespace Pulse\Model;

use Exception;
use PDO;
use PDOException;
use PDOStatement;
use ENV;
use Pulse\Logs\Logger;

class DataBase
{
    private $dsn;
    private $user;
    private $password ;
    private $pdo;

    private $query = [
        'operation' => 'SELECT',
        'table' => '',
        'select' => '*',
        'where' => [],
        'wherein' => [],
        'whereCondition' => 'AND',
        'order' => '',
        'status' => 'status = true',
        'join' => [],
        'limit' => '',
        'group' => '',
    ];
    private $autoReset = true; // always reset the query after executing it

    public function __construct($host, $db_name, $user, $password)
    {
        $this->dsn = "mysql:host=$host;dbname=$db_name";
        $this->user = $user;
        $this->password = $password;

        try {
            $this->pdo = new PDO($this->dsn, $this->user, $this->password);
            // set the PDO error mode to exception
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e)
        {
            throw new Exception("DataBase Connection failed: " . $e->getMessage());
        }
    }

    public function insert($table, $data)
    {
        //formating data
        $colomn = "(" . implode(", ", array_keys($data)) .")";
        extract($this->parseValues($data));
        $values = "(" . implode(", ", $values) .")";
        
        //sql prepare
        $sql= "INSERT INTO $table  $colomn VALUES $values";
        $stmt=$this->pdo->prepare($sql);
       
        // execute and retrun
        if(!$stmt->execute($statments)) return -1;

        $logger = new Logger("database");
        $logger->insert("insert",$data);

        return $this->pdo->lastInsertId();
    }

    public function insertMany($table, $data)
    {
        $colomn = "(" . implode(", ", array_keys($data[0])) .")";
           
        $chunks = array_chunk($data, 500);
        foreach ($chunks as $list) {
            $statments = []; 
            $rows = [];   

            foreach ($list as $row) {
                $res = $this->parseValues($row);

                $rows[] =  "(" . implode(", ", $res['values']) .")";
                $statments = array_merge($statments, $res['statments']);
            }
            $values = implode(",", $rows);

            $sql= "INSERT INTO $table  $colomn VALUES $values";
            $stmt=$this->pdo->prepare($sql);

            // execute and retrun
            if(!$stmt->execute($statments)) return false;
        }

        $logger = new Logger("database");
        $logger->insert("insert",$data);

        return true;
    }

    private function parseValues($data)
    {
        $r = [
            'values' => [],
            'statments' =>[]
        ];

        foreach ($data as $key => $value) {
            switch ($value) {
                case 'NOW':
                case 'NOW()':
                    $r['values'][] = $value;
                    break;
                
                default:
                    $r['values'][] = "?";
                    $r['statments'][] = $value;
                    break;
            }
        }

        return $r;
    }

    public function table(string $table)
    {
        $this->query['table'] = $table;
        return $this;
    }

    public function select($select)
    {
        if(is_array($select)){
            $select = implode(", ", $select);
        }
        $this->query['select'] = $select;
        return $this;
    }
    public function where(string $stem, string $value)
    {
        $this->query['where'][] = [$stem, $value];
        return $this;
    }

    public function whereIn(string $stem, array $list)
    {
        $this->query['wherein'][] = [$stem, $list];
    }

    public function condition(string $value)
    {
        $this->query['whereCondition'] = $value;
        return $this;
    }
    public function order(string $order)
    {
        $this->query['order'] = $order;
        return $this;
    }

    public function group(string $group)
    {
        $this->query['group'] = $group;
        return $this;
    }

    public function limit($value)
    {
        $this->query['limit'] = $value;
        return $this;
    }

    public function join(string $table, string $condition, string $method, string $tableName)
    {
        $this->query['join'][] = [
            'table' => $table,
            'condition' => $condition,
            'method' => $method,
            'name' => $tableName
        ];
    }
    public function run()
    {
        if(empty($this->query['table'])){
            throw new \Exception("No Table Found to run the query");
        }

        //where condition
        $where = '';
        $statement_value = [];
        $status = $this->getStatus();

        if(! empty($this->query['where']) || ! empty($this->query['wherein'])){
            $new_where = [];

            if(! empty($this->query['where'])){
                $where_stem = array_column($this->query['where'], 0);
                $where_value = array_column($this->query['where'], 1);
    
                $where_parsed = $this->parseValues($where_value);
                $statement_value = array_merge($statement_value, $where_parsed['statments']);
                
                foreach ($where_stem as $key => $value) {
                    $new_where[] = $value . $where_parsed['values'][$key];
                }
            }

            if(! empty($this->query['wherein'])){
                $where_stem = array_column($this->query['wherein'], 0);
                $where_array = array_column($this->query['wherein'], 1);

                foreach ($where_stem as $key => $value) {
                    $statement_value = array_merge($statement_value, $where_array[$key]);

                    $parse = array_fill(0,count($where_array[$key]), '?');
                    $list = "(" . implode(', ', $parse) . ")";

                    $new_where[] = "$value IN $list";
                }
            }

            $cond = $this->query['whereCondition'];
            $where = "WHERE (" . implode(" ${cond} ",$new_where). ")";
            
            if($status){
                $where .= " AND " . $status;
            }
        }
        else if($status){
            $where = "WHERE " . $status;
        }

        $order = '';
        if( $this->query['order'] != ''){
            $order = "ORDER BY " .  $this->query['order'];
        }

        $group = '';
        if( $this->query['group'] != ''){
            $group = "GROUP BY " .  $this->query['group'];
        }
        
        $limit = '';
        if($this->query['limit'] != ''){
            $limit = "LIMIT " . $this->query['limit'];
        }
        
        $table = $this->query['table'];
        
        //join tables
        $join = '';
        foreach ($this->query['join'] as $row) {
            $name = (empty($row['name']))? "":"as {$row['name']}";
            $tmp = " {$row['method']} JOIN {$row['table']} $name ON {$row['condition']} ";
            $join .= $tmp;
        }

        switch ($this->query['operation']) {
            case 'SELECT':
                $select = $this->query['select'];
                $sql = "SELECT $select FROM $table $join $where $order $limit $group";
                break;

            case 'UPDATE':
                $set = $this->query['data'];
                $statement_value = array_merge($this->query['values'], $statement_value);
                $sql = "UPDATE $table $set $where";

                $logger = new Logger("database");
                $logger->insert("update",[
                    "set" => $this->query['main_data'],
                    "where" => $this->query['where']
                ]);
                break;

            case 'DELETE':
                $sql = "DELETE FROM $table $where";
                break;
        }
        if(ENV::ENVIROMENT == 'development')
            $GLOBALS['queries'][] = $sql;

        $this->resetQuery();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($statement_value);
        return $stmt;
    }

    public function update(array $data)
    {
        $this->query['operation'] = 'UPDATE';

        $key = array_keys($data);
        $res = $this->parseValues($data);

        $tmp = [];
        $new_data = array_combine($key, $res['values']);
        foreach ($new_data as $name => $value) {
            $tmp[] = "$name = $value";
        }
        $imp = implode(', ', $tmp);

        $this->query['data'] = "SET $imp";
        $this->query['values'] = $res['statments'];
        $this->query['main_data'] = $data;

        return $this;
    }

    public function delete()
    {
        $this->query['operation'] = 'DELETE';
        return $this;
    }

    public function SQL($exp)
    {
        if(ENV::ENVIROMENT == 'development')
            $GLOBALS['queries'][] = $exp;

        $stmt=$this->pdo->prepare($exp);
        return $stmt;
    }

    public function Fetch(PDOStatement $stmt)
    {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function FetchAll(PDOStatement $stmt)
    {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function resetQuery()
    {
        if($this->autoReset){
            $this->query = [
                'operation' => 'SELECT',
                'table' => '',
                'select' => '*',
                'where' => [],
                'wherein' => [],
                'whereCondition' => 'AND',
                'order' => '',
                'status' => 'status = true',
                'join' => [],
                'limit' => '',
                'group' => '',
            ];
        }
    }
    public function setAutoReset(bool $value)
    {
        $this->autoReset = $value;
        return $this;
    }
    public function getAutoReset()
    {
        return $this->autoReset;
    }

    public function showWithDeleted()
    {
        $this->query['status'] = '';
        return $this;
    }

    public function showJustDeleted()
    {
        $this->query['status'] = 'status = false';
        return $this;
    }

    private function getStatus()
    {
        $status = [];
        if($this->query['status']){
            //allow null if we have any right join
            if($this->hasRightJoin()){
                $status[] = "(" . $this->query['table'].".".$this->query['status'] . 
                    " OR " . $this->query['table'] .".status is NULL )";
            }else{
                $status[] = $this->query['table'].".".$this->query['status'];
            }
                
            
            foreach ($this->query['join'] as $join) {
                // allow null if it's left join
                if(strtolower(trim($join['method'])) == 'left'){
                    $status[] ="(" . $join['name'].".".$this->query['status'] .
                        " OR " . $join['name'] . ".status is NULL )";
                }
                else{
                    $status[] =$join['name'].".".$this->query['status'];
                }
            }
        }
        return implode(" AND ",$status);
    }

    private function hasRightJoin()
    {
        foreach ($this->query['join'] as $join) {
            if(trim($join['method']) == 'right') return true;
        }
        return false;
    }
}
