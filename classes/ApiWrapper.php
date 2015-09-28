<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 17/09/15
 * Time: 11:55
 */

class ApiWrapper {
	private static $api_type = false;

	/**
	 * Returns the currently selected api class
	 * @param $data
	 * @return MovieDB
	 */
	public static function load($data=array()){
		$api_type = self::getApiType();

		if($api_type == "tvrage"){
			return new TvRage($data);
		}
		else if($api_type == "themoviedb"){
			return new MovieDB($data);
		}
		else{
			echo "API Fail";
			exit;
		}
	}

	public static function getApiType(){
		if(!self::$api_type){
			global $settings;
			ApiWrapper::$api_type = $settings['api_type'];
		}

		return self::$api_type;
	}

	public static function getApiId(){
		//tdo get id from apis table where code is apitype
		return 2;
	}
}