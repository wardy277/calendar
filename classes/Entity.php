<?php

class Entity{
	protected $_data;

	/**
	 * create the class containing data
	 *
	 * @param array $data - data of the entity
	 */
	public function __construct(array $data){
		$this->_data = $data;
	}

	/**
	 * Super magic functyion which is called when a fucntion is not found
	 *
	 * @param string field - the field we are looking for (coudl also be a functionname)
	 */
	public function __call($field, $arguments){
		if(substr($field, 0, 3) == 'get'){
			$field = substr($field, 3);

			return $this->getAttr($field);
		}
		else if(substr($field, 0, 3) == 'set'){
			$field = substr($field, 3);

			return $this->setAttr($field, $arguments[0]);
		}
		else{
			echo "Function not found $field";
			exit;
		}
	}

	/**
	 * Sub magic function, behaves the same as jQuery's attr fucntion
	 * @TODO: impliment daving of attr (database?)
	 */
	public function getAttr($field){
		//de camel case
		$field = $this->deCamel($field);

		//return the data  if avaliable
		if(array_key_exists($field, $this->_data)){
			return $this->_data[ $field ];
		}

	}

	public function setAttr($field, $value){
		//de camel case
		$field = $this->deCamel($field);

		return $this->_data[ $field ] = $value;
	}
	/**
	 * Coverts a camel case string into an undescore string
	 */
	public static function deCamel($string){
		$string = preg_replace('/(?<=\\w)(?=[A-Z])/', "_$1", $string);
		$string = strtolower($string);

		return $string;
	}

	/**
	 * Shortcut function to get to global site settings
	 * @param $setting
	 * @return mixed
	 */
	public function setting($setting){
		global $settings;

		return $settings[$setting];
	}

	public function getArray(){
		return $this->_data;
	}

}
