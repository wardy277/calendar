<?php
include(dirname(__FILE__)."/../settings.php");

//only shows from users
$sql = "SELECT s.id as show_id
		FROM tv_shows s
        JOIN users_shows u ON u.show_id = s.id
		";

if($_GET['s']){
	$search = $_GET['s'];
	$sql .= $db->build("AND s.title like '%?%'", $search);
}

foreach($db->getArray($sql) as $row){
	/** @var Show $show */
	$show = Show::load($row['show_id']);

	if($show){
		$show->syncEpisodes();
	}
}

