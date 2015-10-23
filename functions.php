<?php

function xml2array($xml){
	//conver the xml to a json
	$json = json_encode($xml);

	//remove {} from creatign empty array as a value
	$json = str_replace("{}", '""', $json);

	//convert json to an array
	$array = json_decode($json, true);

	return $array;

}

function json2array($json){

	//remove {} from creatign empty array as a value
	$json = str_replace("{}", '""', $json);

	//convert json to an array
	$array = json_decode($json, true);

	foreach($array as $field => $value){
		if(!is_array($value) && !is_array($field)){
			$array[ $field ] = trim($value);
		}
	}

	return $array;
}


function get_timezone($timezone){

        $timezone = strtolower(trim($timezone));

        if(substr($timezone, 0, 3) == "gmt"){
                $timezone = trim(substr($timezone, 3));
                $timezone = timezoneNamed($timezone);
        }

	$timezone = new DateTimeZone($timezone);

	return $timezone;
}



function timezoneNamed($id = 0){
	if(empty($id)){
		$id = 0;
	}
	else{
		//use first numbers where avaliable
		$id = 0+$id;
	}

	$timezones = array(
		- 12 => 'Kwajalein',
		- 11 => 'Pacific/Midway',
		- 10 => 'Pacific/Honolulu',
		- 9  => 'America/Anchorage',
		- 8  => 'America/Los_Angeles',
		- 7  => 'America/Denver',
		- 6  => 'America/Tegucigalpa',
		- 5  => 'America/New_York',
		- 4  => 'America/Halifax',
		- 3  => 'America/Argentina/Buenos_Aires',
		- 3  => 'America/Sao_Paulo',
		- 2  => 'Atlantic/South_Georgia',
		- 1  => 'Atlantic/Azores',
		0    => 'Europe/Dublin',
		1    => 'Europe/Belgrade',
		2    => 'Europe/Minsk',
		3    => 'Asia/Kuwait',
		4    => 'Asia/Muscat',
		5    => 'Asia/Yekaterinburg',
		6    => 'Asia/Dhaka',
		7    => 'Asia/Krasnoyarsk',
		8    => 'Asia/Brunei',
		9    => 'Asi/Seoul',
		10   => 'Australia/Canberra',
		11   => 'Asia/Magadan',
		12   => 'Pacific/Fiji',
		13   => 'Pacific/Tongatapu',
	);

	return $timezones[ $id ];
}
