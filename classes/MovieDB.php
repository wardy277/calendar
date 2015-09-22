<?php

/**
 * Created by PhpStorm.
 * User: ico3
 * Date: 29/05/15
 * Time: 15:03
 */
class MovieDB extends Entity implements ApiAbstract{
	private static $domain = "http://api.themoviedb.org/3";
	private static $urls = array(
		'all'     => '/tv/popular',
		'current' => '/tv/on_the_air'
	);

	//todo - impliment config for psotersizes and location etc: http://api.themoviedb.org/3/configuration?api_key=ce842a1d45f50cd3de2acc09a6ec771f

	public function __construct($data = false){
		if(!$data){
			global $settings;
			$data = array('api_key' => $settings['themoviedb_api_key']);
		}

		return parent::__construct($data);
	}

	public function getUrl($type){
		if(!array_key_exists($type, self::$urls)){
			$type = 'all';
		}

		$url = self::$domain.self::$urls[ $type ]."?api_key=".$this->getApiKey();

		return $url;
	}

	public function getShows($type = 'all'){
		$url = $this->getUrl($type);

		$file_contents = file_get_contents($url);

		if(empty($file_contents)){
			return false;
		}

		$json = json2array($file_contents);

		//alternative image: poster_path
		foreach($json['results'] as $show){
			$show_details = array(
				'show_id' => $show['id'],
				'title' => $show['name'],
				'image' => $show['backdrop_path'],
				'country' => $show['origin_country'][0],
			);

			$shows[]      = new Entity($show_details);
		}

		return $shows;
	}

	public function getAllShows(){
		return $this->getShows('all');
	}

	public function getCurrentShows(){
		return $this->getShows('current');
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

		$url = self::$domain."/tv/$show_id?api_key=".$this->getApiKey();

		$file_contents = file_get_contents($url);

		if($file_contents){
			return false;
		}

		$show = json2array($file_contents);

		//todo - handle 404
		$air_date = new DateTime($show['last_air_date']);

		$show_details = array(
			'show_id' => $show['id'],
			'title' => $show['name'],
			'image' => $show['backdrop_path'],
			'country' => $show['origin_country'][0],
			'air_day' => $air_date->format('l'),
		);

		return $show_details;
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

		pre_r($xml);

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
