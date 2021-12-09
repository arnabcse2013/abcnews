<?php

namespace Drupal\google_api_client;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface defining a GoogleApiClient entity.
 *
 * @ingroup google_api_client
 */
interface GoogleApiClientInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Function returns the Developer key saved for the account.
   *
   * @return string
   *   Returns Developer key.
   */
  public function getDeveloperKey();

  /**
   * Function returns Client Id of the account.
   *
   * @return string
   *   Returns Client Id.
   */
  public function getClientId();

  /**
   * Function returns Client Secret.
   *
   * @return string
   *   Returns Client Secret.
   */
  public function getClientSecret();

  /**
   * Function returns the Scopes for the account.
   *
   * @return array
   *   Returns array of scopes.
   */
  public function getScopes();

  /**
   * Function returns the Services for the account.
   *
   * @return array
   *   Returns array of services.
   */
  public function getServices();

  /**
   * Function returns Access Token for the account.
   *
   * @return string
   *   Returns JSON Access Token.
   */
  public function getAccessToken();

  /**
   * Function returns whether the account is Authenticated.
   *
   * @return bool
   *   Returns TRUE if authenticated else FALSE.
   */
  public function getAuthenticated();

  /**
   * Function returns Owner Id of the account.
   *
   * @return \Drupal\user\Entity\User
   *   Returns Owner object.
   */
  public function getOwner();

  /**
   * Function returns Owner Id of the account.
   *
   * @return int
   *   Returns Owner id.
   */
  public function getOwnerId();

  /**
   * Function returns Access Type.
   *
   * @return bool
   *   Returns TRUE if offline.
   */
  public function getAccessType();

  /**
   * Function set Developer Key.
   *
   * @param string $key
   *   Pass Developer Key for the account.
   */
  public function setDeveloperKey($key);

  /**
   * Function set Client Id.
   *
   * @param string $client_id
   *   Pass Client Id for the account.
   */
  public function setClientId($client_id);

  /**
   * Function set Client Secret.
   *
   * @param string $secret
   *   Pass Client Secret for the account.
   */
  public function setClientSecret($secret);

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

  /**
   * Function set Access Token.
   *
   * @param string $token
   *   Pass JSON of Access Token for the account.
   */
  public function setAccessToken($token);

  /**
   * Function set whether the account is authenticated.
   *
   * @param bool $authentication
   *   Pass TRUE if authenticated else FALSE.
   */
  public function setAuthenticated($authentication);

  /**
   * Function set whether the account is offline.
   *
   * @param bool $type
   *   Pass TRUE if offline else FALSE.
   */
  public function setAccessType($type);

  /**
   * Function set whether the account is authenticated.
   *
   * @param int $uid
   *   Pass User id of the owner.
   */
  public function setOwnerId($uid);

  /**
   * Function set whether the account is authenticated.
   *
   * @param \Drupal\user\UserInterface $account
   *   Pass User object for the owner.
   */
  public function setOwner(UserInterface $account);

}
