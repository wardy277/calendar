<?php
include(dirname(__FILE__)."/../settings.php");

$full    = isset($_GET['full'])? true: false;
$current = isset($_GET['current'])? true: false;

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
	$sql   = $db->build("SELECT s.id AS show_id, s.title AS title, a.api_ref AS api_id
							FROM api_shows a
							JOIN `tv_shows` s ON s.id = a.show_id
							JOIN users_shows u ON u.show_id = s.id
							JOIN episode_list e ON e.show_id = s.id
							WHERE a.api_id = '?'
							AND a.api_id > 0
							GROUP BY s.id,  a.api_ref
							ORDER BY max(case when e.aired_date < date('now') then e.aired_date else 0 end) desc",
		$api_id
	);
	
	
	foreach($db->getArray($sql) as $row){
		$shows[] = new Entity($row);
	}
}

$total           = count($shows);
$last_percentage = 0;
$i               = 0;

foreach($shows as $api_show){
	
	$i++;
	
	$api_id = $api_show->getShowId();
	
	//see if show already exists
	$show = Show::loadFromApi($api_id);
	$data = $api_show->getArray();
	
	if(!$show || !$show->getId()){
		//this show is new
		echo "creating show ".$data['title']."<br />\n";
		//$show = Show::create($data);
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
