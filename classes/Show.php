<?php

/**
 * Created by PhpStorm.
 * User: ico3
 * Date: 29/05/15
 * Time: 16:44
 */
class Show extends DatabaseEntity{
	protected static $_table = 'tv_shows';

	public static function loadFromTvrage($id){
		global $db;

		$sql = $db->build("SELECT * FROM `?` WHERE tvrage_id = '?' LIMIT 1", static::$_table, $id);
		$row = $db->rquery($sql);

		return parent::load($id, $row);
	}

	public static function create($data){
		global $db;

		//todo - add useful data like air day and time
		$id = $db->insert(static::$_table, $data);

		return Show::load($id);
	}

	public function syncEpisodes(){
		global $settings;

		//update show first - just incase
		$this->updateShow();

		$data   = array('api_key' => $settings['tvrage_api_key']);
		$tvrage = new TvRage($data);


		foreach($tvrage->getEpisodes($this->getTvrageId()) as $data){
			//set aired time from show
			$data['aired_date'] .= " ".$this->getAirTime();

			//convert aired date to GMT as that's zero using  timezone for show
			$timezone = $this->getTimezone();
			if(empty($timezone) || $timezone == '0000-00-00 00:00:00'){
				//default to GMT
				$timezone = 'GMT';
			}

			//get the time based on aired date, at the shows timezone
			$date = new DateTime($data['aired_date'], new DateTimeZone($timezone));
			//convert the time to GTM as a standard to save into the DB
			$date->setTimezone(new DateTimeZone('GMT'));
			$data['aired_date'] = $date->format('Y-m-d H:i:s');

			//check already exists
			$lookup_array = array(
				'show_id' => $this->getId(),
				'season'  => $data['season'],
				'episode' => $data['episode'],
			);

			$object = Episode::loadWhere($lookup_array);

			if($object){
				//update
				foreach($data as $field => $value){
					$object->setAttr($field, $value);
				}
			}
			else{
				$data['show_id'] = $this->getId();
				Episode::create($data);

			}
		}
	}

	/**
	 * update show if force update - rarely need to do this
	 */
	public function updateShow(){
		$tvrage = new TvRage();
		$tvrage_id = $this->getTvrageId();

		$data = $tvrage->getShow($tvrage_id);

		$this->update($data);
	}
	
}