<?php

/**
 * Created by PhpStorm.
 * User: ico3
 * Date: 28/05/15
 * Time: 22:16
 */
class Day extends Entity{

	public function __construct($date){
		$this->_data['date'] = $date;
	}

	public function getTorrents(){
		global $db;

		$sql = $db->build("SELECT t.title AS show_name, e.title, UNIX_TIMESTAMP(e.aired_date) AS time, season, episode
                            FROM tv_shows t, episode_list e, users_shows u
                            WHERE e.show_id=t.tvrage_id
                            AND u.show_id=t.tvrage_id
                         	AND SUBSTRING(e.aired_date, 1, 10) = '?'
                     		AND episode > 0
                            ORDER BY time, title, season, episode",
			$this->getDate()->format('Y-m-d'));

		$data  = $db->getArray($sql);
		$shows = array();

		if(!empty($data)){
			foreach($data as $row){
				$shows[] = new Kickass($row);
			}
		}

		return $shows;
	}

}