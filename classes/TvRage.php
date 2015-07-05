<?php

/**
 * Created by PhpStorm.
 * User: ico3
 * Date: 29/05/15
 * Time: 15:03
 */
class TvRage extends Entity{

	public function __construct($data = false){
		if(!$data){
			global $settings;
			$data = array('api_key' => $settings['tvrage_api_key']);
		}

		return parent::__construct($data);
	}

	public function getAllShows(){
		$url           = "http://services.tvrage.com/myfeeds/currentshows.php?key=".$this->getApiKey();
		$file_contents = file_get_contents($url);

		if(empty($file_contents)){
			return false;
		}

		$xml = simplexml_load_string($file_contents);

		$shows = array();

		foreach($xml->children() as $results){
			foreach($results->children() as $result){
				$show_details = xml2array($result);
				$shows[]      = new Entity($show_details);
			}
		}

		return $shows;
	}

	public function searchShows($search){
		$url           = "http://services.tvrage.com/myfeeds/search.php?key=".$this->getApiKey()."&show=".$search;
		$file_contents = file_get_contents($url);

		if(empty($file_contents)){
			return false;
		}

		$xml = simplexml_load_string($file_contents);

		$shows = array();

		foreach($xml->children() as $result){
			$show_details = xml2array($result);
			$shows[]      = new Entity($show_details);
		}

		return $shows;
	}

	public function getShow($show_id){
		$cached = "/tmp/getShow-$show_id-".date('Y-m-d');
		if(!file_exists($cached) || filesize($cached) < 10){
			$url           = "http://services.tvrage.com/myfeeds/showinfo.php?key=".$this->getApiKey()."&sid=".$show_id;
			$file_contents = file_get_contents($url);
			file_put_contents($cached, $file_contents);
		}

		$file_contents = file_get_contents($cached);

		$xml          = simplexml_load_string($file_contents);
		$show_details = xml2array($xml);

		//covnert to Show compatible array
		$data = $show_details;

		//convert tvrage api to show format
		$data['tvrage_id'] = $data['showid'];
		$data['title']     = $data['showname'];
		$data['url']       = $data['showlink'];
		$data['country']   = $data['origin_country'];
		$data['air_time']   = $data['airtime'];
		$data['air_day']   = $data['airday'];

		return $data;
	}

	public function getEpisodes($show_id){
		//check for cached
		$cached = "/tmp/getEpisodes-$show_id-".date('Y-m-d');
		if(!file_exists($cached) || filesize($cached) < 10){
			$url           = "http://services.tvrage.com/myfeeds/episode_list.php?key=".$this->getApiKey()."&sid=".$show_id;
			$file_contents = file_get_contents($url);

			file_put_contents($cached, $file_contents);
		}

		$file_contents = file_get_contents($cached);

		if(empty($file_contents)){
			return false;
		}

		$xml = simplexml_load_string($file_contents);

		$episodes = array();

		foreach($xml->children() as $details){
			if($details->getName() == "Episodelist"){
				foreach($details->children() as $season){

					//season is in here <Season no="1">...</Season>
					$season_details = xml2array($season);
					$season_details = $season_details['@attributes'];

					foreach($season->children() as $episode){
						$episode = xml2array($episode);

						//all sources (tvrage is a a source seoul return the same array format
						$data = array(
							'season'     => $season_details['no'],
							'episode'    => $episode['seasonnum'],
							'title'      => $episode['title'],
							'aired_date' => $episode['airdate'],
							'rating'     => $episode['rating'],
							'link'       => $episode['link'],
							'image'      => $episode['screencap'],
						);

						//$episodes[] = new Entity($data);
						$episodes[] = $data;
					}
				}
			}
		}

		return $episodes;

	}

}
