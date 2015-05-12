<?php

function pre_r($v){
	echo "\n<pre>";
	print_r($v);
	echo "</pre>\n";
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

