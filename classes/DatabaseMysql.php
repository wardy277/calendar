<?php
/** DatabaseMysqli.php Class*/

/**
 * DatabaseMysqli.php class
 * @category   Core Functionality
 * @package    Khan
 * @author     Chris Ward <chris@verticalplus.co.uk>
 * @copyright  2019 Vertical PLus LTD
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 */
class DatabaseMysqli extends Database {
	
	/** @var $db Mysqli */
	protected $db;
	
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
			return $this->db->real_escape_string(trim($data));
		}
	}
	
	/**
	 * @param mysqli_result $result
	 * @return mixed
	 */
	public function assoc($result){
		return $result->fetch_assoc();
	}
	
}
