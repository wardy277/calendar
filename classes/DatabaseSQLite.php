<?php
/** DatabaseSQLite Class*/

class DatabaseSQLite extends Database {
	
	/** @var $db SQLite3 */
	protected $db;
	
	protected function connect(){
		//connect to MySQl server via mysqli
		$this->db = new SQLite3($this->server);
		
		if($this->db->connect_errno){
			echo "\nUnable to select the database ".$this->db->connect_error;
			exit;
		}
		
		return true;
		
	}
	
	
	/**
	 * @param $data
	 * @return string|array
	 */
	public function escape($data){
		
		if(is_array($data)){
			
			foreach($data as $field => $value){
				$data[$field] = $this->escape($value);
			}
			
			return $data;
		}
		else{
			return $this->db->escapeString(trim($data));
		}
	}
	
	/**
	 * @param SQLite3Result $result
	 * @return mixed
	 */
	public function assoc($result){
		return $result->fetchArray(SQLITE3_ASSOC);
	}
	
	/**
	 * @param $table
	 * @param $data
	 * @param bool $ignore
	 * @return mixed
	 */
	public function insert($table, $data, $ignore = false){
		
		if(!isset($data['id'])){
			$sql        = $this->build("SELECT MAX(id) FROM `?` WHERE id != ''", $table);
			$data['id'] = $this->fquery($sql);
		}
		
		return parent::insert($table, $data, $ignore);
	}
	
	
	public function getColumns($table){
		$sql     = $this->build("PRAGMA table_info(`?`)", $table);
		$columns = $this->getArray($sql);
		
		$column_names = array();
		foreach($columns as $column){
			$column_names[] = $column['name'];
		}
		
		return $column_names;
	}
	
	public function query($sql){
		
		$sql = str_replace(array(
			'UNIX_TIMESTAMP(',
			),
			array(
				'strftime("%s", '
			),
			$sql);
		
		return parent::query($sql);
	}
	
	
}
