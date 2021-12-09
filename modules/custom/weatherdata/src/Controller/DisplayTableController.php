<?php

namespace Drupal\weatherdata\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;

/**
 * Class DisplayTableController.
 *
 * @package Drupal\weatherdata\Controller
 */
class DisplayTableController extends ControllerBase {
	
    public function getContent() {  
		$build = [
		  'description' => [
			'#theme' => 'weatherdata_description',
			'#description' => 'Weather Description',
			'#attributes' => [],
		  ],
		];
		return $build;
	}

  /**
   * Display.
   *
   * @return string
   *   Return Hello string.
   */
	public function display() {
		// Call API
		
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
		
		
		//create table header
		$header_table = array(
			'id'=>    t('SrNo'),
			'inserted_on' => t('Date'),
			'temp' => t('Temperature'),
			'feels_like' => t('Feels Like'),
			'temp_min' => t('Min Temperature'),
			'temp_max' => t('Max Temperature'),
			'pressure' => t('Pressure'),
			'humidity' => t('Humidity'),
			'sea_level' => t('Sea Level'),
			'grnd_level' => t('Ground Level'),
		);

		$query = \Drupal::database()->select('weather_info', 'm');
		$query->fields('m', ['id','temp','feels_like','temp_min','temp_max','pressure','humidity','sea_level','grnd_level','inserted_on']);
		$pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
		$results = $pager->execute()->fetchAll();
		//print_r($results); exit;
		$rows=array();
		foreach($results as $key => $data){
			//echo $data->id; exit;
			//print the data from table
			$rows[$data->id] = array(
				'id' =>	$data->id,
				'inserted_on' => $data->inserted_on,
				'temp' => $data->temp,
				'feels_like' => $data->feels_like,
				'temp_min' => $data->temp_min,
				'temp_max' => $data->temp_max,
				'pressure' => $data->pressure,
				'humidity' => $data->humidity,
				'sea_level' => $data->sea_level,
				'grnd_level' => $data->grnd_level,
			);
		}
		//display data in site
		$form['table'] = [
			'#type' => 'table',
			'#header' => $header_table,
			'#rows' => $rows,
			'#empty' => t('No Data found'),
		];
		$form['pager'] = array(
			'#type' => 'pager'
		);
		return $form;
	}
}