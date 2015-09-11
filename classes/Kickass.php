<?php

/**
 * Created by PhpStorm.
 * User: ico3
 * Date: 28/05/15
 * Time: 22:18
 */
class Kickass extends Entity{
	private static $proxy;
	private $domain = "http://kat.cr";//todo - and this
	private $unblocked_isps = array('threembb'); //todo - and this- dbms this bad boy
	private $proxy_url = "http://www.katproxy.com/"; //todo - dbms this;

	public function __construct($data){
		//add ucstom settings
		$this->domain = $this->setting('kickass_url');
		$this->proxy_url = $this->setting('kickass_proxy');

		//construct as usual
		parent::__construct($data);
	}

	public function getProxy(){
		//needs to be cached or something

		//if(!static::$proxy){
		if(!$_SESSION['kickass_proxy'] || $_GET['recache_kickass']){
			$url = $this->proxy_url;

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0"); // Necessary. The server checks for a valid User-Agent.
			curl_exec($ch);

			$response = curl_exec($ch);
			preg_match_all('/^Location:(.*)$/mi', $response, $matches);
			curl_close($ch);

			$proxy = current(explode('?', $matches[1][0]));

			if(substr($proxy, -1) == "/"){
				$proxy = substr($proxy, 0, -1);
			}

			//Kickass::$proxy = $proxy;
			$_SESSION['kickass_proxy'] = $proxy;
		}

		//return Kickass::$proxy;
		return $_SESSION['kickass_proxy'];
	}

	public function buildLink(){
		$season = str_pad($this->getSeason(), 2, '0', STR_PAD_LEFT);
		$episode = str_pad($this->getEpisode(), 2, '0', STR_PAD_LEFT);

		$search = $this->getShowName()." S".$season."E".$episode;
		$url = $this->buildSearch($search);

		return "<a href='$url' title='".$this->getTitle()."' target='_blank'>".$this->getShowName()."</a>";
	}


	public function buildSearch($search){
		//apparently the ending / is required
		$search_string = "/usearch/".urlencode($search)."/";

		//some ISPs areawsome and dont block :)
		if($this->ISPBlocked()){
			return $this->getProxy().$search_string;
		}
		else{
			return $this->domain.$search_string;
		}
	}

	public function ISPBlocked(){
		$ip = $_SERVER['REMOTE_ADDR'];

		$isp = gethostbyaddr($ip);
		$array = explode('.', $isp);
		foreach($array as $part){
			if(is_numeric($part)){
				continue;
			}

			$isp = $part;
			break;
		}

		if(in_array($isp, $this->unblocked_isps)){
			return false;
		}
		else{
			return true;
		}
	}


}
