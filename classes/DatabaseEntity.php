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

	protected $_db;

	public function __construct($row){
		global $db;
		$this->_db   = $db;

		parent::__construct($row);

		//set to save on close
		register_shutdown_function(array($this, 'save'));

	}

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

	public static function loadWhere($where){
		global $db;

		$row = $db->loadWhere(static::$_table, $where);

		$class = get_called_class();

		return $class::load($row['id'], $row);
	}

	public function save(){
		$data = $this->_data;

		//unset id as not updatable
		unset($data['id']);

		$this->_db->update(static::$_table, $data, array(static::$_key_field => $this->getKey()));
	}

	public function getKey(){
		return $this->getattr( self::$_key_field );
	}

}