<?php


class LimeTorrents extends Entity {
	
	private $domain = "https://www.limetor.pro";
	
	public function buildLink(){
		$season = str_pad($this->getSeason(), 2, '0', STR_PAD_LEFT);
		$episode = str_pad($this->getEpisode(), 2, '0', STR_PAD_LEFT);
		
		$search = $this->getShowName()." S".$season."E".$episode." 1080p";
		$url = $this->buildSearch($search);
		
		return "<a href='$url' title='".$this->getTitle()."' target='_blank'>".$this->getShowName()."</a>";
	}
	
	
	public function buildSearch($search){
		//apparently the ending / is required
		$search_string = "/search/all/".urlencode($search).'/';
		
		return $this->domain.$search_string;
	}
}