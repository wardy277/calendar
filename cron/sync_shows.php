<?php
include(dirname(__FILE__)."/../settings.php");

$full    = $_GET['full']? true: false;
$current = $_GET['current']? true: false;

$tv_api = ApiWrapper::load($data);

if($full){
	$shows = $tv_api->getAllShows();
}
else if($current){
	//current only
	$shows = $tv_api->getCurrentShows();
}
else{
	//lets just get those which we have displayed
	$shows = array();
	$sql   = "SELECT s.tvrage_id AS showid, s.title AS showname, url AS showlink
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

foreach($shows as $api_show){

	$i ++;

	$api_id = $api_show->getShowId();

	//see if show already exists
	$show = Show::loadFromApi($api_id);
	$data = $api_show->getArray();

	if(!$show || !$show->getId()){
		//this show is new
		echo "creating show ".$data['title']."<br />\n";
		$show = Show::create($data);
	}
	else{
		/*
		 * todo - update this maybe?
		echo "updating show";
		exit;
		$show->update($data);
		$show_id = $show->getId();
		echo ".";
		*/
	}

	$percentage = round($i / $total);
}
