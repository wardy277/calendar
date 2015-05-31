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
