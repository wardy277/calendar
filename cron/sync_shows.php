<?php
include(dirname(__FILE__)."/../settings.php");

$full    = $_GET['full']? true: false;
$current = $_GET['current']? true: false;

$data   = array('api_key' => $settings['tvrage_api_key']);
$tvrage = new TvRage($data);

if($full){
	$shows = $tvrage->getAllShows();
}
else if($current){
	//current only
	$shows = $tvrage->getCurrentShows();
}
else{
	//lets just get those which we have displayed
	$shows = array();
	$sql = "SELECT s.tvrage_id AS showid, s.title AS showname, url AS showlink
			FROM tv_shows s
			JOIN users_shows u ON u.show_id = s.id
			JOIN episode_list e ON e.show_id = s.id
			GROUP BY s.id
			ORDER BY MAX(IF(e.aired_date < NOW(), e.aired_date, 0)) DESC";

	foreach($db->getArray($sql) as $row){
		$shows[] = new Entity($row);
	}
}

$total           = count($shows);
$last_percentage = 0;

foreach($shows as $tvrage_show){
	$i ++;

	$tvrage_id = $tvrage_show->getShowid();

	//see if show already exists
	$show = Show::loadFromTvrage($tvrage_id);
	$data = $tvrage->getShow($tvrage_id);

	if(!$show || !$show->getId()){
		//this show is new
		$show = Show::create($data);
		echo "+";
	}
	else{
		$show->update($data);
		$show_id = $show->getId();
		echo ".";
	}

	$percentage = round($i / $total);
}
