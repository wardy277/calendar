<?php
//process get vars from args
if(substr($_SERVER['SHELL'], 0, 4) == '/bin'){
	$requests = $argv;
	array_shift($requests);

	foreach($requests as $request){
		$request                 = explode("=", $request);
		$_REQUEST[ $request[0] ] = $request[1];
		$_GET[ $request[0] ]     = $request[1];
	}

	if(isset($_REQUEST['debug'])){
		$_GET['debug'] = 1;
	}
	$cronning = true;
}

if(isset($_GET['debug'])){
	ini_set("display_errors", 1);
	error_reporting(E_ALL ^ E_NOTICE);
}


function pre_r($v){
	echo "\n<pre>";
	print_r($v);
	echo "</pre>\n";
}

function pre($v){
	pre_r($v);
}

function my_autoloader($class){

	$path = dirname(__FILE__)."/";

	//echo "\n\nlooking for $class in $path\n";

	if(file_exists($path.$class.".php")){
		//echo "found ".$path.$class.".php";
		include($path.$class.".php");
	}
	else if(file_exists($path.$class."/".$class.".php")){
		//echo "found ".$path.$class."/".$class.".php";
		include($path.$class."/".$class.".php");
	}
	else if(file_exists($path.$class."/index.php")){
		//echo "found ".$path.$class."/index.php";
		include($path.$class."/index.php");
	}
	else{
		echo "Class not found $class";
		exit;
	}

}

spl_autoload_register('my_autoloader');

