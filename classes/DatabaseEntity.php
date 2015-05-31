<?php

/**
 * Created by PhpStorm.
 * User: ico3
 * Date: 29/05/15
 * Time: 16:38
 */
abstract class DatabaseEntity extends Entity{

	protected static $_table;
	protected static $_key;
	protected static $_key_field = 'id';

	protected $_db;

	public function __construct($row){
		global $db;
		$this->_db  = $db;
		self::$_key = $row[self::$_key_field];

		parent::__construct($row);
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


}