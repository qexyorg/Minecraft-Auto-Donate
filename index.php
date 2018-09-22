<?php

use Alonity\Alonity;

ini_set('display_errors', true);
error_reporting(E_ALL);

//$start = microtime(true);

ini_set('session.cookie_lifetime', 3600*24*365);

ini_set("upload_max_filesize", "8M");
ini_set("post_max_size", "8M");
ini_set("upload_tmp_dir", __DIR__."/Uploads/tmp/");

if(function_exists('date_default_timezone_set')){
	date_default_timezone_set('Europe/Moscow');
}

header('Content-Type: text/html; charset=UTF-8');

if(!file_exists(__DIR__.'/Uploads/tmp/index.html')){
	if(!file_exists(__DIR__.'/Uploads/tmp')){
		mkdir(__DIR__.'/Uploads/tmp', 0755, true);
	}

	copy(__DIR__.'/Uploads/index.html', __DIR__.'/Uploads/tmp/index.html');
}

if(!file_exists(__DIR__.'/Uploads/cache/index.html')){
	if(!file_exists(__DIR__.'/Uploads/cache')){
		mkdir(__DIR__.'/Uploads/cache', 0755, true);
	}

	copy(__DIR__.'/Uploads/index.html', __DIR__.'/Uploads/cache/index.html');
}

/*if(!file_exists(__DIR__.'/Uploads/cache/sessions/index.html')){
	if(!file_exists(__DIR__.'/Uploads/cache/sessions')){
		mkdir(__DIR__.'/Uploads/cache/sessions', 0755, true);
	}

	copy(__DIR__.'/Uploads/index.html', __DIR__.'/Uploads/cache/sessions/index.html');
}

session_save_path(__DIR__.'/Uploads/cache/sessions');*/

if(!isset($_SESSION)){ session_start(); }

require_once(__DIR__.'/Alonity/Alonity.php');

$alonity = new Alonity();

$alonity->RunApp('MyApp');

//echo microtime(true)-$start;

?>