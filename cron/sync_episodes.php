<?php
include(dirname(__FILE__)."/../settings.php");

//only shows from users
$sql = "SELECT t.* FROM tv_shows t, users_shows u WHERE u.show_id = t.tvrage_id ORDER BY date_added DESC ";

if($_GET['s']){
	$search = $_GET['s'];
	//$sql = "SELECT * FROM tv_shows WHERE title like '".mysql_real_escape_string($search)."%'";
	$sql .= "AND title like '".mysql_real_escape_string($search)."%'";
}

echo "<pre>";

foreach($db->query($sql) as $row){
	$id      = $row['id'];
	$show_id = $row['tvrage_id'];

	echo "\n".$row['title'].": ".$id;

	$url           = "http://services.tvrage.com/myfeeds/episode_list.php?key=".$settings['tvrage_api_key']."&sid=$show_id";
	$file_contents = file_get_contents($url);

	if(empty($file_contents)){
		echo "\n\tRetrieving XML failed";
		sleep(5);

		$file_contents = file_get_contents($url);

		if(empty($file_contents)){
			echo " - No joy";
			continue;
		}

	}

	$xml = simplexml_load_string($file_contents);

	foreach($xml->children() as $details){
		if($details->getName() == "Episodelist"){
			foreach($details->children() as $season){
				$season_num = $season->attributes();
				$season_num = $season_num['no'];

				foreach($season->children() as $episode){
					$details = xml2array($episode);

					$details['airdate_time'] = $details['airdate']." ".current(explode(" ", $details['alternatetime']));

					$sql = "INSERT INTO episode_list SET
                                                show_id = '$show_id',
                                                title = '".$db->escape($details['title'])."',
                                                aired_date = '".$db->escape($details['airdate'])."',
                                                season = '".$db->escape($season_num)."',
                                                episode = '".$db->escape($details['seasonnum'])."'
                                                ON DUPLICATE KEY UPDATE title = '".$db->escape($details['title'])."',
                                                aired_date = '".$db->escape($details['airdate'])."'
                                                ";
					echo "\n\tAdding s{$season_num}.e{$details['epnum']} {$details['airdate']} {$details['title']}";
					$db->query($sql);
					echo mysql_error();
				}
			}
		}
	}


}
