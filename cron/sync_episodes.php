<?php
include(dirname(__FILE__)."/../settings.php");

$tv_api = ApiWrapper::load();
$type = isset($_GET['type'])?'all':'latest';

//only shows from users
$sql = "SELECT s.id as show_id
			FROM tv_shows s
			JOIN users_shows u ON u.show_id = s.id
			JOIN episode_list e ON e.show_id = s.id
			WHERE s.id > 0
			#WHERE s.id = 5
		";

if(isset($_GET['s'])){
	$search = $_GET['s'];
	$sql .= $db->build("AND s.title like '%?%'", $search);
}

$sql .= "
			GROUP BY s.id
			ORDER BY MAX(IF(e.aired_date < NOW(), e.aired_date, 0)) DESC";

foreach($db->getArray($sql) as $row){
	/** @var Show $show */
	$show = Show::load($row['show_id']);

	//show not found - create ie
	if(!$show){
		//todo - get show from api - or not?
		$show = false;

		//we have title, thats all, need to get api id from that
	}

	if($show){
		echo "Syncing ".$show->getTitle()."<br />\n";
		$show->syncEpisodes($type);
	}
}

