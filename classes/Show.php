<?php

/**
 * Created by PhpStorm.
 * User: ico3
 * Date: 29/05/15
 * Time: 16:44
 */
class Show extends DatabaseEntity {
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
		$id = $db->insert(self::$_table, $data);

		return new Show($id);
	}

	public function syncEpisodes(){

	}
	
}