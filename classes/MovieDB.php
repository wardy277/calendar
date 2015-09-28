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
				'title'   => $show['name'],
				'image'   => $show['backdrop_path'],
				'country' => $show['origin_country'][0],
			);

			$shows[] = new Entity($show_details);
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
		$data = array('query' => $search);

		$search_results = $this->queryArray('/search/tv', $data);

		$shows = array();

		foreach($search_results['results'] as $show_details){
			$data    = array(
				'show_id' => $show_details['id'],
				'image'   => $this->getImage($show_details['poster_path']),
				'name'    => $show_details['name']
			);
			$shows[] = new Entity($data);
		}

		return $shows;
	}

	public function getShow($show_id){

		$url = self::$domain."/tv/$show_id?api_key=".$this->getApiKey();

		$file_contents = file_get_contents($url);

		if(!$file_contents){
			return false;
		}

		$show = json2array($file_contents);

		//todo - handle 404
		$air_date = new DateTime($show['last_air_date']);

		$num_seasons = 0;
		foreach($show['seasons'] as $season){
			if($season['season_number'] > $num_seasons){
				$num_seasons = $season['season_number'];
			}
		}

		$show_details = array(
			'show_id'     => $show['id'],
			'title'       => $show['name'],
			'image'       => $show['backdrop_path'],
			'country'     => $show['origin_country'][0],
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
	public function getEpisodes($show_id){
		//get show info

		///tv/{id}
		$show_details = $this->queryArray("/tv/$show_id");

		//skip on failure
		if(!$show_details){
			return false;
		}

		foreach($show_details['seasons'] as $season_info){
			$season       = $season_info['season_number'];
			$num_episodes = $season_info['episode_count'];

			if($season > 0){
				//get season info
				for($episode = 1; $episode <= $num_episodes; $episode ++){
					//get data
					///tv/{id}/season/{season_number}/episode/{episode_number}
					$episode_details = $this->queryArray("/tv/$show_id/season/$season/episode/$episode");

					//skip on failure
					if(!$episode_details){
						continue;
					}

					//convert to our format
					$data = array(
						'season'     => $season,
						'episode'    => $episode,
						'title'      => $episode_details['name'],
						'aired_date' => $episode_details['air_date'],
						'rating'     => $episode_details['rating'],
						'image'      => $episode_details['still_path'],
					);

					//$episodes[] = new Entity($data);
					$episodes[] = $data;

				}
			}
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
		$query['api_key'] = $this->getApiKey();
		//build as a url
		$url = self::$domain.$path.'?'.http_build_query($query);

		$file_contents = file_get_contents($url);

		if(!$file_contents){
			return false;
		}

		return json2array($file_contents);

	}

	public function getImage($image){
		//todo - get and cahe this data from http://api.themoviedb.org/3/configuration
		return "http://image.tmdb.org/t/p/w154/".$image;

	}
}
