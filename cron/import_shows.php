<?php
include(dirname(__FILE__)."/../settings.php");
//run once
exit;

$sql = "SELECT s.id AS show_id, s.title AS title, a.api_ref AS api_id
	FROM api_shows a
	JOIN `tv_shows` s ON s.id = a.show_id
	JOIN users_shows u ON u.show_id = s.id
	JOIN episode_list e ON e.show_id = s.id
	
	LEFT JOIN api_shows a2 ON a2.api_id = 3 AND a2.show_id = s.id
	
	WHERE a2.api_id IS NULL
	AND a.api_id = '2'
	GROUP BY s.id, a.api_ref
	ORDER BY MAX(IF(e.aired_date < NOW(), e.aired_date, 0)) DESC";

foreach($db->getArray($sql) as $row){
	
	$title = "\nProcessing ".$row['title'];
	
	$shows = $tv_api->searchShows($row['title']);
	$num_results = count($shows);
	$show_id = false;
	
	switch ($num_results){
		case 0:
			echo $title." none found";
			break;
		case 1:
			$show_id = $shows[0]->getShowId();
			break;
		default:
			$found = false;
			
			foreach($shows as $show){
				if($show->getName() == $row['title']){
					$found = true;
					$show_id = $show->getShowId();
					break;
				}
			}
			
			if(!$found){
				echo $title." multiple not found";
			}
			break;
	}
	
	if($show_id){
		$data = $tv_api->getShow($show_id);
		
		echo "\ncreating show ".$row['title'];
		$show = Show::create($data);
	}
	
}