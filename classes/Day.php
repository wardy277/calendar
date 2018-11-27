<?php

/**
 * Created by PhpStorm.
 * User: ico3
 * Date: 28/05/15
 * Time: 22:16
 */
class Day extends Entity{

	protected static $_cache = false;

	public function __construct($date){
		$this->_data['date'] = $date;
	}

	/**
	 * @return Kickass[]
	 */
	public function getTorrents(){
		global $db, $session;


		//work out timezones - convert user's local timezone to GMT as that's what the db is set to
		$time_zone = $session->getTimezone();

		//build start and end of current date
		$start_of_day = $this->getDate()->format('Y-m-d')." 00:00:00";
		$end_of_day   = $this->getDate()->format('Y-m-d')." 23:59:59";

		//convert into a date time object for current users timezone
		$start_date = new DateTime($start_of_day, new DateTimeZone($time_zone));
		$end_date   = new DateTime($end_of_day, new DateTimeZone($time_zone));

		//offset is the time it takes for a torrent to be created
		#$start_date->modify('+1 hours');
		#$end_date->modify('+1 hours');

		//convert local user time to GTM as a standard which is used for the DB
		$start_date->setTimezone(new DateTimeZone('GMT'));
		$end_date->setTimezone(new DateTimeZone('GMT'));

		$start_range = $start_date->format('Y-m-d H:i:s');
		$end_range   = $end_date->format('Y-m-d H:i:s');

/*
		$sql = $db->build("SELECT t.title AS show_name, e.title, UNIX_TIMESTAMP(e.aired_date) AS time, season, episode
							FROM tv_shows t
							JOIN episode_list e ON e.show_id = t.id
							JOIN users_shows u ON u.show_id = t.id
							WHERE u.user_id = '?'
							AND e.aired_date BETWEEN '?' AND '?'
							AND episode > 0
							ORDER BY time, title, season, episode",
			$session->getUserId(), $start_range, $end_range);
		$data  = $db->getArray($sql);
*/

		$shows = array();
		$data = $this->getShows($start_date->format('Y-m-d'));

		if(!empty($data)){
			foreach($data as $row){
				$shows[] = new Zooqle($row);
			}
		}

		//return $shows;

		//special -- dc week
		if($this->getDate()->format('D') == "Thu"){
			$yesterday = clone($this->getDate());
			$yesterday->modify('yesterday');
			$shows[] = new Comic(array('date' => $yesterday));
		}

		return $shows;
	}


	protected function getShows($day){
		if(static::$_cache === false){
			global $db, $session;

			$date = new DateTime($day);

			$date->modify('first day of last month');
			$start_range = $date->format('Y-m-d')." 00:00:00";

                        $date->modify('last day of next month');
                        $date->modify('last day of next month');
			$end_range = $date->format('Y-m-d')." 23:59:59";


			//recache
			$sql = $db->build("SELECT date(e.aired_date) as day, t.title AS show_name, e.title, UNIX_TIMESTAMP(e.aired_date) AS time, season, episode
                                                        FROM tv_shows t
                                                        JOIN episode_list e ON e.show_id = t.id
                                                        JOIN users_shows u ON u.show_id = t.id
                                                        WHERE u.user_id = '?'
                                                        AND e.aired_date BETWEEN '?' AND '?'
                                                        AND episode > 0
							
							#group by date(e.aired_date)
                                                        ORDER BY time, title, season, episode",
	                        $session->getUserId(), $start_range, $end_range);

			foreach($db->getArray($sql) as $row){
				static::$_cache[$row['day']][] = $row;
			}
		}


		return static::$_cache[$day];
	}
}
