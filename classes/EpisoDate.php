<?php

/**
 * Class EpisoDate
 * API docs: https://www.episodate.com/api
 */
class EpisoDate extends Entity implements ApiAbstract {
	private static $domain = "https://www.episodate.com/api";
	private static $urls = array(
		'all'     => '/most-popular',
		'current' => '/most-popular',
	);
	
	/**
	 * EpisoDate constructor.
	 * @param bool $data
	 */
	public function __construct($data = false){
		if(!$data){
			global $settings;
			$data = array();
		}
		
		return parent::__construct($data);
	}
	
	/**
	 * Get API URL
	 * @param $type
	 * @return string
	 */
	public function getUrl($type){
		if(!array_key_exists($type, self::$urls)){
			$type = 'all';
		}
		
		$url = self::$domain.self::$urls[$type];
		
		return $url;
	}
	
	/**
	 * Get popular shows
	 * @param string $type
	 * @return array|bool
	 */
	public function getShows($type = 'all'){
		$url = $this->getUrl($type);
		
		$file_contents = $this->getApiCall($url);
		
		if(empty($file_contents)){
			return false;
		}
		
		$json = json2array($file_contents);
		
		//alternative image: poster_path
		foreach($json['results'] as $show){
			$show_details = array(
				'show_id' => $show['id'],
				'title'   => $show['name'],
				'image'   => $show['backdrop_path'],
				'country' => $show['origin_country'][0],
			);
			
			$shows[] = new Entity($show_details);
		}
		
		return $shows;
	}
	
	/**
	 * Alias for get popular show (all not implemented)
	 * @return array|bool
	 */
	public function getAllShows(){
		return $this->getShows('all');
	}
	
	/**
	 * Alias for get popular shows (current not implemented)
	 * @return array|bool
	 */
	public function getCurrentShows(){
		return $this->getShows('current');
	}
	
	/**
	 * Search for a show
	 * @param $search
	 * @param int $page
	 * @return array
	 */
	public function searchShows($search, $page=1){
		//defaults
		$shows = array();
		$found = false;
		
		$search_results = $this->queryArray('/search?q='.urlencode($search)."&page=".$page);
		
		foreach($search_results['tv_shows'] as $show_details){
			
			if($show_details['name'] == $search){
				$found = true;
			}
			
			$data    = array(
				'show_id' => $show_details['id'],
				'image'   => $this->getImage($show_details['image_thumbnail_path']),
				'name'    => $show_details['name'],
			);
			$shows[] = new Entity($data);
		}
		
		if(!$found && $page == 1){
			//try second page
			$shows = $this->searchShows($search, 2);
		}
		
		
		return $shows;
	}
	
	/**
	 * Get show details
	 * @param $show_id
	 * @return array|bool
	 */
	public function getShow($show_id){
		
		$show = $this->queryArray("/show-details?q=".$show_id);
		$show = $show['tvShow'];
		
		if(isset($show['countdown'])){
			$air_date    = new DateTime($show['countdown']['air_date']);
			$num_seasons = $show['countdown']['season'];
		}
		else{
			$last_episode = end($show['episodes']);
			$num_seasons  = $last_episode['season'];
			$air_date     = new DateTime($last_episode['air_date']);
		}
		
		$show_details = array(
			'show_id'     => $show['id'],
			'title'       => $show['name'],
			'image'       => $show['image_thumbnail_path'],
			'country'     => $show['country'],
			'air_day'     => $air_date->format('l'),
			'num_seasons' => $num_seasons,
		);
		
		return $show_details;
	}
	
	/**
	 * Show id needs to be the api id
	 * @param $show_id
	 * @return array
	 */
	public function getEpisodes($show_id, $type = 'all'){
		//get show info
		
		$show_details = $this->queryArray("/show-details?q=".$show_id);
		
		//skip on failure
		if(!$show_details){
			return false;
		}
		
		$episodes = array();
		
		foreach($show_details['tvShow']['episodes'] as $episode){
			
			//convert to our format
			$data = array(
				'season'     => $episode['season'],
				'episode'    => $episode['episode'],
				'title'      => $episode['name'],
				'aired_date' => $episode['air_date'],
			);
			
			//$episodes[] = new Entity($data);
			$episodes[] = $data;
			
		}
		
		return $episodes;
	}
	
	/**
	 * Shortcut faction to pull data from themoviedb as an array
	 * @param $path
	 * @param array $data
	 * @return bool
	 */
	private function queryArray($path, $query = array()){
		//set api key
		$url = self::$domain.$path;
		
		$file_contents = $this->getApiCall($url);
		
		if(!$file_contents){
			return false;
		}
		
		return json2array($file_contents);
		
	}
	
	/**
	 * Get image for show
	 * @param $image
	 * @return string
	 */
	public function getImage($image){
		//todo - get and cahe this data from http://api.themoviedb.org/3/configuration
		return $image;
		
	}
	
	/**
	 * Return api id
	 * @return int
	 */
	public static function getApiId(){
		//tdo get id from apis table where code is apitype
		return 3;
	}
	
	/**
	 * Cache api calls
	 * @param $url
	 * @return bool|string
	 */
	public function getApiCall($url){
		
		$enable_cache = false;
		
		if($enable_cache){
			//cache url
			$cache = "/tmp/".urlencode($url);
			if(!file_exists($cache)){
				$file_contents = file_get_contents($url);
				
				if(!$file_contents){
					echo "<h1>Error getting Url</h1>";
					
					return false;
				}
				
				file_put_contents($cache, $file_contents);
			}
			
			$file_contents = file_get_contents($cache);
		}
		else{
			$file_contents = file_get_contents($url);
		}
		
		return $file_contents;
	}
	
	/**
	 * @param $api_id - show api id NOT our show id
	 * @param $season_data
	 */
	public function cleanEpisodes($api_id, $season_data){
		global $db;
		
		//get show id
		$sql     = $db->build("SELECT show_id FROM api_shows WHERE api_ref = '?'", $api_id);
		$show_id = $db->fquery($sql);
		
		//remove shows which are no longer avaliable >= important due to php count and zeros
		if($show_id > 0 AND $season_data['season_number'] > 1 && $season_data['episode_count'] > 1){
			$sql = $db->build("DELETE FROM episode_list
				WHERE show_id = '?'
				AND season = '?'
				AND episode >= '?'",
				$show_id, $season_data['season_number'], $season_data['episode_count']
			);
			
			$db->query($sql);
			
		}
		
	}
}

