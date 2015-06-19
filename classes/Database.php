<?php

class Database{

	function __construct($server, $username, $password, $database){
		$this->server   = $server;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;

		$this->connect();
	}

	private function connect(){
		//connect to MySQl server via mysqli
		$this->db = new mysqli($this->server, $this->username, $this->password, $this->database);

		if($this->db->connect_errno){
			echo "\nUnable to select the database ".$this->db->connect_error;
			exit;
		}

		return true;

	}

	public function escape($data){
		if(is_array($data)){
			foreach($data as $field => $value){
				$data[ $field ] = trim($this->escape($value));
			}

			return $data;
		}
		else{
			return $this->db->real_escape_string($data);
		}
	}

	public function build($sql, $data){

		if(is_array($data)){
			//replace :name with value of data['name']
			foreach($data as $field => $value){
				$sql = str_replace(":$data", $this->escape($field), $sql);
			}
		}
		else{
			//csv inject the data
			$args  = func_get_args();
			$query = array_shift($args);

			//explode by ?
			$parts = explode("?", $query);
			//first in array is always sql
			$sql      = array_shift($parts);
			$num_args = count($args);

			for($i = 0; $i < count($parts); $i ++){
				//? marks have been replaced  so either stick a variable here or piut the ? back in if not found
				if($i <= $num_args){
					$sql .= $this->escape($args[ $i ]);
				}
				else{
					$sql .= "?";
				}

				//add the rest fo the sql on
				$sql .= $parts[ $i ];
			}
		}

		return $sql;
	}

	public function query($sql){
		$result = $this->db->query($sql);

		if(!$result){
			echo "Query failed: ".$this->db->error;
			pre_r($sql);
			exit;
		}

		return $result;
	}

	public function getArray($sql){
		$result = $this->query($sql);
		$dat    = array();

		while($result && $row = $result->fetch_assoc()){
			$data[] = $row;
		}

		//free resultset (memory optimisation)
		$result->free();

		return $data;
	}


	public function fquery($sql){
		$result = $this->query($sql);

		$row = $result->fetch_assoc();

		//free resultset (memory optimisation)
		$result->free();

		if(count($row) == 1){
			return current($row);
		}

		return $row;
	}

	public function rquery($sql){
		$data = $this->getArray($sql);

		return $data[0];
	}

	public function insert($table, $data, $ignore = false){
		if($ignore){
			//TODO: change this to add an on duplicate update for thsoe that have no index
			$ignore_sql = "IGNORE";
		}

		$columns = $this->getColumns($table);

		foreach($data as $field => $value){
			if(!in_array($field, $columns)){
				//not a valid column to deleting form array
				unset($data[ $field ]);
			}
		}

		//escape values now
		$data = $this->escape($data);

		$fields = implode("`, `", array_keys($data));
		$values = implode("', '", array_values($data));

		$sql = $this->build("INSERT $ignore_sql INTO `?` (`$fields`) VALUES ('$values')", $table);

		$this->query($sql);

		return $this->insertID();
	}

	public function update($table, $data, $where){
		$columns = $this->getColumns($table);

		$where_sql = $this->buildWhere($table, $where);

		//clean data
		foreach($data as $field => $value){
			if(!in_array($field, $columns)){
				//not a valid column to deleting form array
				unset($data[ $field ]);
			}
		}


		//escape values now
		$data = $this->escape($data);

		$set_sql = "";
		foreach($data as $field => $value){
			$set_sql .= "`$field` = '$value', ";
		}
		$set_sql = substr($set_sql, 0, - 2);

		$sql = $this->build("UPDATE `?` SET $set_sql $where_sql", $table);

		return $this->query($sql);
	}

	/**
	 * Returns a row of data from the $table where $where
	 * @param $table
	 * @param array $where
	 * @param int $limit
	 */
	public function loadWhere($table, array $where){
		$where_sql = $this->buildWhere($table, $where);

		$sql = $this->build("SELECT * FROM `?` $where_sql LIMIT 1", $table);
		return $this->rquery($sql);
	}

	public function buildWhere($table, $where){
		$columns = $this->getColumns($table);

		//clean where
		foreach($where as $field => $value){
			if(!in_array($field, $columns)){
				//not a valid column to deleting form array
				unset($where[ $field ]);
			}
		}

		//build where sql
		$where_sql = "WHERE ";
		$where     = $this->escape($where);

		//build where (pre escaped)
		foreach($where as $field => $value){
			$where_sql .= "`$field` = '$value' AND";
		}

		//remove last AND
		$where_sql = substr($where_sql, 0, -4);

		return $where_sql;
	}


	public function getColumns($table){
		$sql     = $this->build("SHOW COLUMNS FROM `?`", $table);
		$columns = $this->getArray($sql);


		$column_names = array();
		foreach($columns as $column){
			$column_names[] = $column['Field'];
		}

		return $column_names;
	}

	public function insertID(){
		return $this->db->insert_id;
	}


}