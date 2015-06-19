<?php
include(dirname(__FILE__)."/../settings.php");

$full = $_GET['full']?true:false;

$data   = array('api_key' => $settings['tvrage_api_key']);
$tvrage = new TvRage($data);

$shows = $tvrage->getAllShows();
$total = count($shows);
$last_percentage = 0;

foreach($shows as $tvrage_show){
	$i++;

	$tvrage_id = $tvrage_show->getShowid();

	//see if show already exists
	$show = Show::loadFromTvrage($tvrage_id);


	if(!$show || !$show->getId()){
		//this show is new
		$data = $tvrage->getShow($tvrage_id);
		$show = Show::create($data);
		echo "+";
	}

	$percentage = round($i/$total);
}
