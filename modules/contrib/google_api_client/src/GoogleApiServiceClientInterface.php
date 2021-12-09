<?php

namespace Drupal\google_api_client;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a GoogleApiServiceClient entity.
 *
 * @ingroup google_api_service_client
 */
interface GoogleApiServiceClientInterface extends ConfigEntityInterface {

  /**
   * Function returns Json file of the account.
   *
   * @return string
   *   Returns the JSON.
   */
  public function getAuthConfig();

  /**
   * Function returns Json of access_token.
   *
   * @return array
   *   Returns the access token.
   */
  public function getAccessToken();

  /**
   * Function returns the Scopes for the account.
   *
   * @param bool $url
   *   TRUE if should return scope urls.
   *
   * @return array
   *   Returns array of scopes.
   */
  public function getScopes($url = FALSE);

  /**
   * Function returns the Services for the account.
   *
   * @return array
   *   Returns array of services.
   */
  public function getServices();

  /**
   * Function set Json File.
   *
   * @param string $config
   *   Pass Json contents of the file.
   */
  public function setAuthConfig($config);

  /**
   * Function set Access Token.
   *
   * @param array $token
   *   Pass array of Access Token for the account.
   */
  public function setAccessToken(array $token);

  /**
   * Function set Scopes.
   *
   * @param array $scopes
   *   Pass array of scopes for the account.
   */
  public function setScopes(array $scopes);

  /**
   * Function set Services.
   *
   * @param array $services
   *   Pass array of services for the account.
   */
  public function setServices(array $services);

}
