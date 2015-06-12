<?php

/**
 * Created by PhpStorm.
 * User: ico3
 * Date: 29/05/15
 * Time: 15:03
 */
class TvRage extends Entity{

	public function searchShows($search){
		$url = "http://services.tvrage.com/myfeeds/search.php?key=".$this->getApiKey()."&show=".$search;
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
		$url           = "http://services.tvrage.com/myfeeds/showinfo.php?key=".$this->getApiKey()."&sid=".$show_id;
		$file_contents = file_get_contents($url);

		$xml          = simplexml_load_string($file_contents);
		$show_details = xml2array($xml);

		$show = new Entity($show_details);

		return $show;
	}

	public function getEpisodes($show_id){
		$url           = "http://services.tvrage.com/myfeeds/episode_list.php?key=".$this->getApiKey()."&sid=".$show_id;
		$file_contents = file_get_contents($url);

		$xml = simplexml_load_string($file_contents);

		$episodes = array();

		foreach($xml->children() as $details){
			if($details->getName() == "Episodelist"){
				foreach($details->children() as $season){

					foreach($season->children() as $episode){
						$episode = xml2array($episode);

						//all sources (tvrage is a asource shoudl return the same array format
						$data = array(
							'season'     => $episode['seasonnum']+0,
							'episode'    => $episode['epnum'],
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
