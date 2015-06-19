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

	/**
	 * @return Kickass[]
	 */
	public function getTorrents(){
		global $db;

		//work out timezones - convert user's local timezone to GMt as that's what the db is set to

		//todo - need to get this from session->user
		$user_timeszone = 'Europe/London';

		$start_of_day   = $this->getDate()->format('Y-m-d')."00:00:00";
		$end_of_day     = $this->getDate()->format('Y-m-d')."23:59:59";


		$start_date = new DateTime($start_of_day, new DateTimeZone($user_timeszone));
		$end_date   = new DateTime($end_of_day, new DateTimeZone($user_timeszone));

		//convert local user time to GTM as a standard which is used for the DB
		$start_date->setTimezone(new DateTimeZone('GMT'));
		$end_date->setTimezone(new DateTimeZone('GMT'));

		$start_range = $start_date->format('Y-m-d');
		$end_range   = $end_date->format('Y-m-d');


		$sql = $db->build("SELECT t.title AS show_name, e.title, UNIX_TIMESTAMP(e.aired_date) AS time, season, episode
                            FROM tv_shows t, episode_list e, users_shows u
                            WHERE e.show_id = t.id
                            AND u.show_id = t.id
                         	AND e.aired_date BETWEEN '?' AND '?'
                     		AND episode > 0
                            ORDER BY time, title, season, episode",
			$start_range, $end_range);

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