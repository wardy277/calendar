<?php

class Zooqle extends Entity{
	
	private $domain = "https://zooqle.com";
	
	public function buildLink(){
		$season = str_pad($this->getSeason(), 2, '0', STR_PAD_LEFT);
		$episode = str_pad($this->getEpisode(), 2, '0', STR_PAD_LEFT);
		
		$search = $this->getShowName()." S".$season."E".$episode;
		$url = $this->buildSearch($search);
		
		return "<a href='$url' title='".$this->getTitle()."' target='_blank'>".$this->getShowName()."</a>";
	}
	
	
	public function buildSearch($search){
		//apparently the ending / is required
		$search_string = "/search?q=".urlencode($search);
		
		return $this->domain.$search_string;
	}
	
}
