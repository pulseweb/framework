<?php

namespace Pulse;

use Exception;
use Pulse\Model\BaseModel;

class Model extends BaseModel
{
	protected $primaryKey = 'id';

	protected function hasMany($class, $field, $tableName = '', $join = '')
	{
		$class_instance = new $class();
		$joinData = $class_instance->getJoinAuth($field);
		if (!$joinData) {
			throw new Exception("Unable to join table. Check your data");
		}
		if (empty($tableName)) {
			$tableName = $joinData['justTable'];
		}
		$this->joinedTables[] = [
			'instance' => $class_instance,
			'name' => $tableName,
		];

		$this->join($joinData['table'], $joinData['field'], $this->primaryKey, $tableName, $join);
		return $this;
	}

	protected function belongTo($class, $field, $tableName = '', $join = '')
	{
		$class_instance = new $class();
		$joinData = $class_instance->getJoinAuth();
		if (!$joinData) {
			throw new Exception("Unable to join table. Check your data");
		}
		if (empty($tableName)) {
			$tableName = $joinData['justTable'];
		}
		$this->joinedTables[] = [
			'instance' => $class_instance,
			'name' => $tableName,
		];

		$this->join($joinData['table'], $joinData['field'], $field, $tableName, $join);
		return $this;
	}

	protected function semiFreeJoin($class, $field, $thisField, $tableName = '', $join = '')
	{
		$class_instance = new $class();
		$joinData = $class_instance->getJoinAuth($field);
		if (!$joinData) {
			throw new Exception("Unable to join table. Check your data");
		}
		if (empty($tableName)) {
			$tableName = $joinData['justTable'];
		}
		$this->joinedTables[] = [
			'instance' => $class_instance,
			'name' => $tableName,
		];
		$this->join($joinData['table'], $joinData['field'], $thisField, $tableName, $join);
		return $this;
	}

	protected function freeJoin($class, $field, $onClass, $onField, $tableName = '', $join = '')
	{
		$class_instance = new $class();
		$joinData = $class_instance->getJoinAuth($field);
		if (!$joinData) {
			throw new Exception("Unable to join table. Check your data");
		}

		$onClass_instance = new $onClass();
		$onJoinData = $onClass_instance->getJoinAuth($onField);
		if (!$onJoinData) {
			throw new Exception("Unable to join table. Check your data");
		}

		if (empty($tableName)) {
			$tableName = $joinData['justTable'];
		}
		$this->joinedTables[] = [
			'instance' => $class_instance,
			'name' => $tableName,
		];

		$table = $joinData['table'];
		$onTable = $onJoinData['justTable'];
		$condition = "$tableName.$field = $onTable.$onField";

		$this->db->join($table, $condition, $join, $tableName);
		return $this;
	}

	/**
	 * getJoinAuth
	 * Used to get the details of a table we want to join
	 * Will return false if fail
	 */
	protected function getJoinAuth(string $field = '')
	{
		if (empty($field)) {
			$field = $this->primaryKey;
		} else if (!in_array($field, array_merge($this->fields, [$this->primaryKey]))) {
			return false;
		}

		return [
			'table' => $this->table,
			'justTable' => $this->justName,
			'field' => $field
		];
	}

	public function table()
	{
		return $this->table;
	}

	public function justTable()
	{
		return $this->justName;
	}
}
