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

		$data = array(
			'tvrage_id' => $data->getShowid(),
			'title'     => $data->getShowname(),
			'url'       => $data->getShowlink()
		);

		//todo - add useful data like air day and time
		$id = $db->insert(static::$_table, $data);

		return Show::load($id);
	}

	public function syncEpisodes(){
		global $settings;
		$data   = array('api_key' => $settings['tvrage_api_key']);
		$tvrage = new TvRage($data);

		foreach($tvrage->getEpisodes($this->getTvrageId()) as $data){

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
	
}