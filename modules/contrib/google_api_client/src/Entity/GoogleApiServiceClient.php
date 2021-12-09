<?php

namespace Drupal\google_api_client\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\google_api_client\GoogleApiServiceClientInterface;
use Drupal\Component\Serialization\Json;

/**
 * Defines the GoogleApiServiceClient entity.
 *
 * @ingroup google_api_service_client
 *
 * This is the main definition of the entity type. From it, an entityType is
 * derived. The most important properties in this example are listed below.
 *
 * @ConfigEntityType(
 *   id = "google_api_service_client",
 *   label = @Translation("Google Api Service Client"),
 *   handlers = {
 *     "list_builder" = "Drupal\google_api_client\Entity\Controller\GoogleApiServiceClientListBuilder",
 *     "form" = {
 *       "add" = "Drupal\google_api_client\Form\GoogleApiServiceClientForm",
 *       "edit" = "Drupal\google_api_client\Form\GoogleApiServiceClientForm",
 *       "delete" = "Drupal\google_api_client\Form\GoogleApiServiceClientDeleteForm",
 *     },
 *   },
 *   config_prefix = "google_api_service_client",
 *   admin_permission = "administer google api settings",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "auth_config",
 *     "access_token",
 *     "services",
 *     "scopes"
 *   },
 *   links = {
 *     "canonical" = "/google_api_service_client/{google_api_service_client}",
 *     "edit-form" = "/admin/config/services/google_api_service_client/{google_api_service_client}/edit",
 *     "delete-form" = "/admin/config/services/google_api_service_client/{google_api_service_client}/delete",
 *     "collection" = "/admin/config/services/google_api_service_client"
 *   },
 * )
 *
 * The 'links' above are defined by their path. For core to find the
 * corresponding route, the route name must follow the correct pattern:
 *
 * entity.<entity-name>.<link-name> (replace dashes with underscores)
 * Example: 'entity.google_api_service_client.canonical'
 *
 * See routing file above for the corresponding implementation
 *
 * The 'GoogleApiServiceClient' class defines methods and
 * fields for the google_api_service_client entity.
 *
 * Being derived from the ContentEntityBase class, we can override the methods
 * we want. In our case we want to provide access to the standard fields about
 * creation and changed time stamps.
 *
 * Our interface (see GoogleApiServiceClientInterface)
 * also exposes the EntityOwnerInterface.
 * This allows us to provide methods for setting and providing ownership
 * information.
 *
 * The most important part is the definitions of the field properties for this
 * entity type. These are of the same type as fields added through the GUI, but
 * they can by changed in code. In the definition we can define if the user with
 * the rights privileges can influence the presentation (view, edit) of each
 * field.
 */
class GoogleApiServiceClient extends ConfigEntityBase implements GoogleApiServiceClientInterface {

  /**
   * The machine_name.
   *
   * @var string
   */
  protected $id;

  /**
   * The Name for this account.
   *
   * @var string
   */
  protected $label;

  /**
   * The credentials for account.
   *
   * @var string
   */
  protected $auth_config;

  /**
   * The access_token for account.
   *
   * @var string
   */
  protected $access_token;

  /**
   * The services for account.
   *
   * @var array
   */
  protected $services;

  /**
   * The scopes for account.
   *
   * @var array
   */
  protected $scopes;

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthConfig() {
    return Json::decode($this->auth_config);
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessToken() {
    return Json::decode($this->access_token);
  }

  /**
   * {@inheritdoc}
   */
  public function getScopes($url = FALSE) {
    if ($url) {
      $services = $this->getServices();
      $all_scopes = google_api_client_google_services_scopes($services);
      $return = [];
      foreach ($this->scopes as $scope) {
        foreach ($all_scopes as $scopes) {
          if (isset($scopes[$scope])) {
            $return[] = $scopes[$scope];
            break;
          }
        }
      }
      return $return;
    }
    else {
      return $this->scopes;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getServices() {
    return $this->services;
  }

  /**
   * Function sets id of the service account.
   *
   * @param string $id
   *   Pass id of the service account.
   *
   * @return bool
   *   Returns true if the operation is successful.
   */
  public function setId($id) {
    return $this->id = $id;
  }

  /**
   * Function sets Name of the service account.
   *
   * @param string $name
   *   Pass the account name.
   *
   * @return bool
   *   Returns true if the operation is successful.
   */
  public function setName($name) {
    return $this->label = $name;
  }

  /**
   * {@inheritdoc}
   */
  public function setAuthConfig($config) {
    if (is_array($config)) {
      $config = Json::encode($config);
    }
    return $this->auth_config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public function setAccessToken(array $access_token) {
    return $this->access_token = Json::encode($access_token);
  }

  /**
   * {@inheritdoc}
   */
  public function setScopes(array $scopes) {
    return $this->scopes = $scopes;
  }

  /**
   * {@inheritdoc}
   */
  public function setServices(array $services) {
    return $this->services = $services;
  }

}
