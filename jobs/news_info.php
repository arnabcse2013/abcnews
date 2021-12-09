<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once 'autoload.php';

$kernel = new DrupalKernel('prod', $autoloader);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);


function curl_get_contents($url){
	$ch = curl_init();
	$timeout = 10;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}



//$url3 = 'https://newsapi.org/v2/top-headlines?country=in&category=business&apiKey=4272b788991c4ff69975dafc57c56c4b';
$url3 = 'http://api.mediastack.com/v1/news?access_key=f8d322a50181340b7109ae80437dabaa&languages=en';
$contents3 = curl_get_contents($url3);
$news = json_decode($contents3);


echo "<pre>";
print_r($news);

?>