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

$url2 = 'https://api.openweathermap.org/data/2.5/weather?q=Kalyani&appid=e77c2b1bea2f60ace71f4eb49c9944de&units=metric';
$contents2 = curl_get_contents($url2);
$weather = json_decode($contents2);

$temperature = $weather->main->temp;
$feels_like = $weather->main->feels_like;
$temp_min = $weather->main->temp_min;
$temp_max = $weather->main->temp_max;
$pressure = $weather->main->pressure;
$humidity = $weather->main->humidity;
$sea_level = $weather->main->sea_level;
$grnd_level = $weather->main->grnd_level;

$data = array(
	'temp' => $temperature,
	'feels_like'  => $feels_like,
	'temp_min' => $temp_min,
	'temp_max'  => $temp_max,
	'pressure' => $pressure,
	'humidity'  => $humidity,
	'sea_level' => $sea_level,
	'grnd_level'  => $grnd_level,
	'inserted_on' => date('Y-m-d'),
);

$connection = \Drupal\Core\Database\Database::getConnection() ;
$connection->insert('weather_info')->fields($data) ->execute();

?>