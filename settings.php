<?php
//setup globals
$cronning = false;
global $settings, $cronning, $session, $calendar;

//want this as early as possible for debugging
require_once(dirname(__FILE__)."/classes/common.php");
require_once(dirname(__FILE__)."/connection_settings.php");

#$db=  new Database($db_server, $db_username, $db_password, $db_name);
$db=  new DatabaseSQLite('database/database.sqlite3');

//maybe this can be a class
$sql = "SELECT * FROM settings";
foreach($db->getArray($sql) as $setting){
	$settings[ $setting['setting'] ] = $setting['value'];
}

include(dirname(__FILE__)."/functions.php");

session_start();
$session = Session::loadSession();

//todo - add login handeling
$session->login(1);

if(!$cronning){
	ob_start();
	register_shutdown_function('shutdown_function');
}

$tv_api = ApiWrapper::load();
$api_id = $tv_api->getApiId();

function shutdown_function(){
	global $calendar;

	$output = ob_get_clean();

	include(dirname(__FILE__)."/templates/header.html");

	if(!$calendar){
		include(dirname(__FILE__)."/templates/nav.phtml");
	}

	echo $output;
	include(dirname(__FILE__)."/templates/footer.html");
}

