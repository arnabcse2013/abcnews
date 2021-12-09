<?php

namespace Drupal\google_api_client\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\google_api_client\GoogleApiClientInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class Google API Client Service.
 *
 * @package Drupal\google_api_client\Service
 */
class GoogleApiClientService {

  use StringTranslationTrait;

  /**
   * The GoogleClient object.
   *
   * @var \Google_Client
   */
  public $googleClient;

  /**
   * The GoogleApiClient Entity Object.
   *
   * @var \Drupal\google_api_client\GoogleApiClientInterface
   */
  public $googleApiClient;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cacheBackend;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The system theme config object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Callback Controller constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   LoggerChannelFactoryInterface.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   Cache Backend.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(LoggerChannelFactoryInterface $loggerFactory, CacheBackendInterface $cacheBackend, MessengerInterface $messenger, TranslationInterface $string_translation, ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    $this->loggerFactory = $loggerFactory;
    $this->cacheBackend = $cacheBackend;
    $this->messenger = $messenger;
    $this->stringTranslation = $string_translation;
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Function to set the GoogleApiClient account for the service.
   *
   * @param \Drupal\google_api_client\GoogleApiClientInterface $google_api_client
   *   Pass completely loaded GoogleApiClient object.
   * @param \Google_Client|null $googleClient
   *   Optionally parameter for developers who want to set initial
   *   google client object.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function setGoogleApiClient(GoogleApiClientInterface $google_api_client, \Google_Client $googleClient = NULL) {
    $this->googleApiClient = $google_api_client;
    // Add the client.
    $this->getClient($googleClient);
  }

  /**
   * Function to retrieve the google client for different operations.
   *
   * Developers can pass the google_api_client object to setGoogleApiClient
   * and get the api client ready for operations.
   *
   * @param \Google_Client|null $client
   *   Optionally parameter for developers who want to set initial
   *   google client object.
   *
   * @return \Google_Client|bool
   *   Google_Client object with all params from the account or false.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function getClient(\Google_Client $client = NULL) {
    if (!google_api_client_load_library()) {
      // We don't have library installed notify admin and abort.
      $status_report_link = Link::createFromRoute($this->t('Status Report'), 'system.status')->toString();

      $this->messenger->addError($this->t("Can't get the google client as library is missing check %status_report for more details. Report this to site administrator.", [
        '%status_report' => $status_report_link,
      ]));
      $response = new RedirectResponse('<front>');
      $response->send();
      return FALSE;
    }
    if ($client == NULL) {
      $client = new \Google_Client();
    }
    $client->setRedirectUri(google_api_client_callback_url());
    if ($this->googleApiClient == NULL) {
      return $client;
    }
    $google_api_client = $this->googleApiClient;
    $client->setClientId($google_api_client->getClientId());
    if ($google_api_client->getAccessType()) {
      $client->setAccessType('offline');
      $client->setApprovalPrompt('force');
    }
    $client->setClientSecret($google_api_client->getClientSecret());
    $client->setDeveloperKey($google_api_client->getDeveloperKey());
    $client->setRedirectUri(google_api_client_callback_url());
    $client->setApplicationName($google_api_client->getName());
    $scopes = $google_api_client->getScopes();

    // Let other modules change scopes.
    $google_api_client_id = $google_api_client->getId();
    $this->moduleHandler->alter('google_api_client_account_scopes', $scopes, $google_api_client_id);
    $client->addScope($scopes);
    $this->googleClient = $client;
    if ($google_api_client->getAuthenticated()) {
      $this->googleClient->setAccessToken($google_api_client->getAccessToken());
      $this->setAccessToken();
    }
    return $this->googleClient;
  }

  /**
   * Wrapper for Google_Client::setAccessToken.
   *
   * @return bool
   *   Was the token added or not?
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function setAccessToken() {
    // If there was something in cache.
    if ($access_token = $this->googleApiClient->getAccessToken()) {
      // Check if the current token is expired?
      if ($this->googleClient->isAccessTokenExpired()) {
        // Refresh the access token using refresh token if it is set.
        if ($refresh_token = $this->googleClient->getRefreshToken()) {
          if ($tokenUpdated = $this->googleClient->fetchAccessTokenWithRefreshToken($refresh_token)) {
            $this->googleApiClient->setAccessToken($tokenUpdated);
            $this->googleApiClient->save();
            // There should be a new unexpired token.
            return TRUE;
          }
        }
        // Else the token fetch from refresh token failed.
        $this->googleClient->revokeToken();
        $this->googleApiClient->setAuthenticated(FALSE);
        $this->googleApiClient->setAccessToken('');
        $this->googleApiClient->save();
        // Unable to update token.
        return FALSE;
      }
      $this->googleClient->setAccessToken($access_token);
      // Token is set and is valid.
      return TRUE;
    }
    // There is no token in db.
    return FALSE;
  }

  /**
   * This function is designed to return objects of services classes.
   *
   * So if the account is authenticated for say Google calendar then
   * this function will return Google_Service_Calendar class object.
   *
   * @param bool $blank_client
   *   If we should use a blank client object.
   * @param bool $return_object
   *   True if we want objects else classes returned.
   *
   * @return array
   *   Array of Google_Service classes with servicename as index.
   */
  public function getServiceObjects($blank_client = FALSE, $return_object = TRUE) {
    $google_api_client = $this->googleApiClient;
    $services = $google_api_client->getServices();
    if (!is_array($services)) {
      $services = [$services];
    }
    $classes = $this->configFactory->get('google_api_client.google_api_classes')->get('google_api_client_google_api_classes');
    $return = [];
    foreach ($services as $service) {
      $return[$service] = new $classes[$service]($this->googleClient);
    }
    return $return;
  }

}
