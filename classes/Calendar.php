<?php

class Calendar extends Entity{
	private $date;

	private $week_start;
	private $week_start_name;
	private $week_end;
	private $week_end_name;

	private $start_date;
	private $end_date;

	/**
	 * week start on a monday by default
	 *
	 * @param $date
	 * @param int $week_start
	 */
	public function __construct($date, $week_start = 1){
		if(!empty($date)){
			$current_date = new DateTime($date);
		}

		//todo - validate yyyy-mm or yyyy-mm-01 format
		if(!$current_date){
			$current_date = new DateTime('first day of this month');
		}

		$this->date = $current_date;

		//calculate week start and end
		$this->week_start      = $week_start;
		$this->week_start_name = $this->weekdayToName($this->week_start);
		$this->week_end        = $this->week_start == 0? 6: $this->week_start - 1;
		$this->week_end_name   = $this->weekdayToName($this->week_end);

		//calculate start and end dates to block
		$start_date = clone($this->date);
		if($start_date->format('w') != $this->week_start){
			$start_date->modify('last '.$this->week_start_name);
		}
		$this->start_date = $start_date;

		//build end date
		$end_date = clone($this->date);
		$end_date->modify('first '.$this->week_end_name.' of next month');

		//date interval seems to be a  < not an <=
		$end_date->modify('tomorrow');

		$this->end_date = $end_date;

		$this->_data['end_date'] = $end_date;
	}

	public function weekdayToName($day){
		return date('l', strtotime("Sunday +$day days"));
	}

	public function getWeekdays(){
		$array = array();

		$day = $this->week_start;

		for($i = 0; $i < 7; $i ++){
			//reset if out of weeks range (stupid sunday being 0)

			$array[] = $this->weekdayToName($day);
			$day ++;
			if($day > 6){
				$day = 0;
			}

		}

		return $array;
	}

	public function getDays(){

		//build range
		$interval  = new DateInterval('P1D');
		$daterange = new DatePeriod($this->start_date, $interval, $this->end_date);

		return $daterange;
	}

	public function format($string){
		return $this->date->format($string);
	}

	public function getWeekEnd(){
		return $this->week_end;
	}

	public function buildNav(){
		//previous month
		$date = clone($this->date);
		$date->modify('last month');
		$month = $date->format('m');
		$year  = $date->format('y');

		if($year != $this->format('y')){
			$previous_month = "?m=$month&y=$year";
		}
		else{
			$previous_month = "?m=$month";
		}


		//next month
		$date = clone($this->date);
		$date->modify('next month');
		$month = $date->format('m');
		$year  = $date->format('y');

		if($year != $this->format('y')){
			$next_month = "?m=$month&y=$year";
		}
		else{
			$next_month = "?m=$month";
		}

		$month = $this->format('F');

		ob_start();
		include(dirname(__FILE__)."/../templates/nav_calendar.phtml");
		return ob_get_clean();
	}
}