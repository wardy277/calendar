<?php
if($_GET['debug'] == 1){
	ini_set("display_errors", 1);
	error_reporting(E_ALL ^ E_NOTICE);
}


//want this as early as possible for debugging
require_once(dirname(__FILE__)."/classes/common.php");

//process get vars from args
if(substr($_SERVER['SHELL'], 0, 4) == '/bin'){
	$requests = $argv;
	array_shift($requests);

	foreach($requests as $request){
		$request                 = explode("=", $request);
		$_REQUEST[ $request[0] ] = $request[1];
		$_GET[ $request[0] ]     = $request[1];
	}

	if($_REQUEST['debug'] == 1){
		$_GET['debug'] = 1;
	}
	$cronning = true;
}

//$db = new Database($server, $username, $password, $database);

//include(dirname(__FILE__)."/functions.php");


register_shutdown_function('shutdown_function');
function shutdown_function(){
	$output = ob_get_clean();

	include(dirname(__FILE__)."/templates/header.html");
	echo $output;
	include(dirname(__FILE__)."/templates/footer.html");
}
