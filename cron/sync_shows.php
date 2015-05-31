<?php
include(dirname(__FILE__)."/../settings.php");

/*
$url = "http://services.tvrage.com/myfeeds/currentshows.php?key=".$settings['tvrage_api_key'];
$file_contents = file_get_contents($url);
$file_contents = file_put_contents('/tmp/shows.xml', $file_contents);
*/

$file_contents = file_get_contents('/tmp/shows.xml');

$xml = simplexml_load_string($file_contents);

foreach($xml->children() as $country){
	foreach($country->children() as $show){

		$show_details = xml2array($show);

		echo "\nadding show ".$show_details['showid']." - ".$show_details['showname'];

		$sql = "INSERT INTO tv_shows
                        SET tvrage_id = '".$db->escape($show_details['showid'])."',
                        title = '".$db->escape($show_details['showname'])."',
                        url = '".$db->escape($show_details['showlink'])."'
                        ON DUPLICATE KEY UPDATE title = '".$db->escape($show_details['showname'])."',
                        url = '".$db->escape($show_details['showlink'])."'
                        ";
		$db->query($sql);
		#echo $sql;exit;
	}
}



