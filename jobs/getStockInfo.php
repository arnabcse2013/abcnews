<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

$autoloader = require_once 'autoload.php';

$kernel = new DrupalKernel('prod', $autoloader);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);

curl_get_contents();
function curl_get_contents(){
	$ch = curl_init();
	$timeout = 10;
	$url = 'https://assets.tekfacts.com/stocks.php';
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$contents = curl_exec($ch);
	curl_close($ch);
	$stocks = json_decode($contents);
	$last_val = $stocks->data[3]->last;
	$timestamp_val = $stocks->data[3]->timestamp;

	$data = array(
		'last' => $last_val,
		'timestamp'  => $timestamp_val,
		'updated_at' => date('Y-m-d H:i:s'),
	);

	$connection = \Drupal\Core\Database\Database::getConnection() ;
	$connection->insert('stock_info')->fields($data) ->execute();

	$mystock = 'Stock: '.$last_val.'||Timestamp: '.$timestamp_val.'';

	echo new JsonResponse($mystock);
	
}




?>