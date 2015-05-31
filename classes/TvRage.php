<?php

/**
 * Created by PhpStorm.
 * User: ico3
 * Date: 29/05/15
 * Time: 15:03
 */
class TvRage extends Entity{

	public function searchShows($search){
		$url           = "http://services.tvrage.com/myfeeds/search.php?key=".$this->getApiKey()."&show=".$search;
		$file_contents = file_get_contents($url);

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

}