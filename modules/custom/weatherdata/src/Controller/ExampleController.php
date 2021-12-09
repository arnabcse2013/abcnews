<?php
/**
 * @file
 * @author Rakesh James
 * Contains \Drupal\example\Controller\ExampleController.
 * Please place this file under your example(module_root_folder)/src/Controller/
 */
namespace Drupal\weatherdata\Controller;

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * Provides route responses for the Example module.
 */
class ExampleController {
  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function myPage() {
	$user = \Drupal::currentUser();
	//$user = User::load(\Drupal::currentUser()->id());
	$roles = $user->getRoles();
	//print_r($roles); exit;
	// Check for permission
	if($roles[1] == 'secured_access' || $roles[2] == 'secured_access'){
		$element = array(
		  '#markup' => 'Hello world!',
		);
	}else{
		//return new RedirectResponse(\Drupal::url('user.login', [], ['absolute' => TRUE]));
		$response = new RedirectResponse("user/login");
		$response->send();
		return;
	}
    
    return $element;
  }
  
	public function dailystockupdate(){
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

		$mystock = array(
			'0' => 'Stock: '.$last_val.'||Timestamp: '.$timestamp_val.'',
		);
		return new JsonResponse($mystock);
		
	}
	
	public function dailyweatherupdate(){
		$ch = curl_init();
		$timeout = 10;
		$url = 'https://api.openweathermap.org/data/2.5/weather?q=Kalyani&appid=e77c2b1bea2f60ace71f4eb49c9944de&units=metric';
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$contents = curl_exec($ch);
		curl_close($ch);
		$weather = json_decode($contents);
		
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
			'mytime' => date('Y-m-d H:i:s')
		);

		$connection = \Drupal\Core\Database\Database::getConnection() ;
		$connection->insert('weather_info')->fields($data) ->execute();

		$mystock = array(
			'0' => 'ok',
		);
		return new JsonResponse($mystock);
	}

}
?>