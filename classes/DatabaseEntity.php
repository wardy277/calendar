<?php

/**
 * Created by PhpStorm.
 * User: ico3
 * Date: 29/05/15
 * Time: 16:38
 */
abstract class DatabaseEntity extends Entity{

	protected static $_table;
	protected static $_key_field = 'id';

	protected $_data_original;
	protected $_db;

	/**
	 * @param array $row
	 */
	public function __construct($row){
		global $db;
		$this->_db = $db;

		parent::__construct($row);

		$this->_data_original = $this->_data;

		//set to save on close
		register_shutdown_function(array($this, 'save'));

	}

	/**
	 * @param $data
	 * @return mixed
	 */
	public static function create($data){
		global $db;
		//todo - add useful data like aired day and time

		$class  = get_called_class();
		$object = new $class($data);

		//insert into db
		$id = $db->insert(static::$_table, $data);
		$object->setId($id);

		return $object;
	}

	/**
	 * @param $id
	 * @param bool $row
	 * @return bool
	 */
	public static function load($id, $row = false){
		global $db;

		if(!$row){
			$sql = $db->build("SELECT * FROM `?` WHERE `?` = '?' LIMIT 1", static::$_table, static::$_key_field, $id);
			$row = $db->rquery($sql);
		}

		if(!empty($row)){
			//get class originally called
			$class = get_called_class();

			$object = new $class($row);

			return $object;
		}
		else{
			return false;
		}
	}

	/**
	 * Load an object based on a where array
	 * @param $where
	 * @return bool
	 */
	public static function loadWhere($where){
		global $db;

		$row = $db->loadWhere(static::$_table, $where);

		if(!empty($row)){
			$class = get_called_class();

			return $class::load($row['id'], $row);
		}
		else{
			return false;
		}
	}

	/**
	 * Update the current object in the database
	 */
	public function save(){
		$data = array();
		//unset id as not updatable
		unset($data['id']);

		foreach($this->_data as $field => $value){
			if($this->_data_original[$field] != $value){
				$data[$field] = $value;
			}
		}

		if(!empty($data)){
			$this->_db->update(static::$_table, $data, array(static::$_key_field => $this->getKey()));
		}
	}

	public function getKey(){
		return $this->getattr(self::$_key_field);
	}

	/**
	 * updated the object form an array
	 * does not create new variable - should it?
	 * This doesnt update in the db as we have save for that.
	 * @param $data
	 */
	public function update($data){
		unset($data['id']);

		//only those which have changed
		foreach($this->_data as $field => $value){
			if($data[$field] != $value){
				$this->setAttr($field, $value);
			}
		}

		if(!empty($data)){
			$this->_db->update(static::$_table, $data, array(static::$_key_field => $this->getKey()));
		}
	}

}