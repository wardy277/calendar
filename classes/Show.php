<?php

/**
 * Created by PhpStorm.
 * User: ico3
 * Date: 29/05/15
 * Time: 16:44
 */
class Show extends DatabaseEntity{
	protected static $_table = 'tv_shows';

	/**
	 * Overriding load fucntion which will include the show id for the current api
	 * @param $id
	 * @return bool
	 */
	public static function load($id){
		global $db;

		$api_id = ApiWrapper::getApiId();

		$sql = $db->build("SELECT s.*, a.api_ref AS api_id
							FROM api_shows a
							JOIN `?` s ON s.id = a.show_id
							WHERE a.api_id = '?'
							AND s.id = '?'
 							LIMIT 1",
			static::$_table, $api_id, $id);

		$row = $db->rquery($sql);

		if(!$row){
			//not under this api perhaps?
			echo "Show not found $id<br />\n";
			return false;
		}

		return parent::load($id, $row);
	}

	/**
	 * Load a show from an api show id
	 * @param $id
	 * @return Show
	 */
	public static function loadFromApi($id){
		global $db;

		$api_id = ApiWrapper::getApiId();

		$sql = $db->build("SELECT s.*, a.api_ref AS api_id
							FROM api_shows a
							JOIN `?` s ON s.id = a.show_id
							WHERE a.api_id = '?'
							AND a.api_ref = '?'
 							LIMIT 1",
			static::$_table, $api_id, $id);

		$row = $db->rquery($sql);

		return parent::load($row['id'], $row);
	}

	public static function create($data){
		global $db;

		//todo - add useful data like air day and time
		//check exists first
		$sql = $db->build("SELECT id FROM `?` WHERE title = '?'", static::$_table, $data['title']);
		$id  = $db->fquery($sql);

		if(!$id){
			$id = $db->insert(static::$_table, $data);
		}

		//insert into link table
		$api_id = ApiWrapper::getApiId();
		$data   = array(
			'api_id'  => $api_id,
			'show_id' => $id,
			'api_ref' => $data['show_id'],
		);

		$db->insert('api_shows', $data);

		return Show::load($id);
	}

	public function syncEpisodes($type='all'){
		global $settings;
		//update show first - just incase
		$this->updateShow();

		$tv_api = ApiWrapper::load();

		foreach($tv_api->getEpisodes($this->getApiId(), $type) as $data){

			//set aired time from show
			if(!empty($data['aired_date'])){
				//guestimate airtime if not set
				if(strlen($data['aired_date']) == 10){
					$data['aired_date'] .= " ".$this->getAirTime();
				}

				//convert aired date to GMT as that's zero using  timezone for show
				$timezone = $this->getTimezone();

				//calculate is not provided
				if(empty($timezone) || $timezone == '0000-00-00 00:00:00'){
					switch ($this->getCountry()){
						case "US":
							$timezone = 'GMT-5 +DST';
							break;
						case "CA":
							$timezone = 'GMT-4 +DST';
							break;
						default:
							//default to GMT
							$timezone = 'GMT';
							break;
					}

				}

				$tmp      = $timezone;
				$timezone = get_timezone($timezone);

				//get the time based on aired date, at the shows timezone
				$date = new DateTime($data['aired_date'], $timezone);

				//convert the time to GTM as a standard to save into the DB
				$date->setTimezone(new DateTimeZone('Europe/Dublin'));
				$data['aired_date'] = $date->format('Y-m-d H:i:s');

				$tmp .= " - ".$date->format('Y-m-d H:i');
			}
			
			if(isset($_GET['debug']) && $_GET['debug'] == 'time'){
				echo "s".$data['season']." e".$data['episode']." - ".$tmp."\n";
			}

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
					if(isset($_GET['debug'])){
						echo "\tUpdating $field => $value\n";
					}
					$object->setAttr($field, $value);
				}
				
				$object->save();
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
		$api_db = ApiWrapper::load();
		$api_id = $this->getApiId();

		$data = $api_db->getShow($api_id);

		$this->update($data);
	}

	/**
	 * Get id related to current api
	 */
	#public function getApiId(){
	#	pre_R($this);
	#	exit;
	#}

	public function getAirTime(){
		if($this->getAttr('air_time') == ''){
			return '21:00';
		}
		else{
			return $this->getAttr('air_time');
		}
	}
}
