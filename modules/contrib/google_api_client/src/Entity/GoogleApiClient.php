<?php

namespace Drupal\google_api_client\Entity;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\google_api_client\GoogleApiClientInterface;
use Drupal\user\UserInterface;
use Drupal\Component\Serialization\Json;

/**
 * Defines the GoogleApiClient entity.
 *
 * @ingroup google_api_client
 *
 * This is the main definition of the entity type. From it, an entityType is
 * derived. The most important properties in this example are listed below.
 *
 * id: The unique identifier of this entityType. It follows the pattern
 * 'moduleName_xyz' to avoid naming conflicts.
 *
 * label: Human readable name of the entity type.
 *
 * handlers: Handler classes are used for different tasks. You can use
 * standard handlers provided by D8 or build your own, most probably derived
 * from the standard class. In detail:
 *
 * - view_builder: we use the standard controller to view an instance. It is
 *   called when a route lists an '_entity_view' default for the entityType
 *   (see routing.yml for details. The view can be manipulated by using the
 *   standard drupal tools in the settings.
 *
 * - list_builder: We derive our own list builder class from the
 *   entityListBuilder to control the presentation.
 *   If there is a view available for this entity from the views module, it
 *   overrides the list builder. @todo: any view? naming convention?
 *
 * - form: We derive our own forms to add functionality like additional fields,
 *   redirects etc. These forms are called when the routing list an
 *   '_entity_form' default for the entityType. Depending on the suffix
 *   (.add/.edit/.delete) in the route, the correct form is called.
 *
 * - access: Our own accessController where we determine access rights based on
 *   permissions.
 *
 * More properties:
 *
 *  - base_table: Define the name of the table used to store the data. Make sure
 *    it is unique. The schema is automatically determined from the
 *    BaseFieldDefinitions below. The table is automatically created during
 *    installation.
 *
 *  - fieldable: Can additional fields be added to the entity via the GUI?
 *    Analog to content types.
 *
 *  - entity_keys: How to access the fields. Analog to 'nid' or 'uid'.
 *
 *  - links: Provide links to do standard tasks. The 'edit-form' and
 *    'delete-form' links are added to the list built by the
 *    entityListController. They will show up as action buttons in an additional
 *    column.
 *
 * There are many more properties to be used in an entity type definition. For
 * a complete overview, please refer to the '\Drupal\Core\Entity\EntityType'
 * class definition.
 *
 * The following construct is the actual definition of the entity type which
 * is read and cached. Don't forget to clear cache after changes.
 *
 * @ContentEntityType(
 *   id = "google_api_client",
 *   label = @Translation("GoogleApiClient entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\google_api_client\Entity\Controller\GoogleApiClientListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\google_api_client\Form\GoogleApiClientForm",
 *       "edit" = "Drupal\google_api_client\Form\GoogleApiClientForm",
 *       "delete" = "Drupal\google_api_client\Form\GoogleApiClientDeleteForm",
 *       "revoke" = "Drupal\google_api_client\Form\GoogleApiClientRevokeForm",
 *     },
 *   },
 *   base_table = "google_api_client",
 *   admin_permission = "administer site configuration",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "name",
 *     "owner" = "uid"
 *   },
 *   links = {
 *     "canonical" = "/google_api_client/{google_api_client}",
 *     "edit-form" = "/admin/config/services/google_api_client/{google_api_client}/edit",
 *     "delete-form" = "/admin/config/services/google_api_client/{google_api_client}/delete",
 *     "revoke-form" = "/admin/config/services/google_api_client/revoke",
 *     "collection" = "/google_api_client/list"
 *   },
 *   field_ui_base_route = "google_api_client.google_api_client_settings",
 * )
 *
 * The 'links' above are defined by their path. For core to find the
 * corresponding route, the route name must follow the correct pattern:
 *
 * entity.<entity-name>.<link-name> (replace dashes with underscores)
 * Example: 'entity.google_api_client.canonical'
 *
 * See routing file above for the corresponding implementation
 *
 * The 'GoogleApiClient' class defines methods and
 * fields for the google api client entity.
 *
 * Being derived from the ContentEntityBase class, we can override the methods
 * we want. In our case we want to provide access to the standard fields about
 * creation and changed time stamps.
 *
 * Our interface (see GoogleApiClientInterface)
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
class GoogleApiClient extends ContentEntityBase implements GoogleApiClientInterface {

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->get('id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getDeveloperKey() {
    return $this->get('developer_key')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getClientId() {
    return $this->get('client_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getClientSecret() {
    return $this->get('client_secret')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getScopes() {
    $scopes = $this->get('scopes')->getValue();
    $merged_scopes = [];
    foreach ($scopes as $key => $scope) {
      $merged_scopes[$key] = $scope['value'];
    }
    $services = $this->getServices();
    $all_scopes = google_api_client_google_services_scopes($services);
    $return = [];
    foreach ($merged_scopes as $scope) {
      foreach ($all_scopes as $scopes) {
        if (isset($scopes[$scope])) {
          $return[] = $scopes[$scope];
          break;
        }
      }
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function getServices() {
    $services = $this->get('services')->getValue();
    $return = [];
    foreach ($services as $key => $service) {
      $return[$key] = $service['value'];
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessToken() {
    return $this->get('access_token')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthenticated() {
    return $this->get('is_authenticated')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessType() {
    return $this->get('access_type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    return $this->set('name', $name);
  }

  /**
   * {@inheritdoc}
   */
  public function setDeveloperKey($key) {
    return $this->set('developer_key', $key);
  }

  /**
   * {@inheritdoc}
   */
  public function setClientId($client_id) {
    return $this->set('client_id', $client_id);
  }

  /**
   * {@inheritdoc}
   */
  public function setClientSecret($secret) {
    return $this->set('client_secret', $secret);
  }

  /**
   * {@inheritdoc}
   */
  public function setScopes(array $scopes) {
    $services = $this->getServices();
    $all_scopes = google_api_client_google_services_scopes($services);
    $merged_scopes = [];
    foreach ($scopes as $scope) {
      foreach ($all_scopes as $scopes) {
        if (UrlHelper::isValid($scope, TRUE)) {
          if (in_array($scope, $scopes)) {
            $merged_scopes[] = array_search($scope, $scopes);
            break;
          }
        }
        else {
          if (in_array($scope, array_keys($scopes))) {
            $merged_scopes[] = $scope;
            break;
          }
        }
      }
    }
    return $this->set('scopes', $merged_scopes);
  }

  /**
   * {@inheritdoc}
   */
  public function setServices(array $services) {
    return $this->set('services', $services);
  }

  /**
   * {@inheritdoc}
   */
  public function setAccessToken($token) {
    if (is_array($token)) {
      $token = (object) $token;
      $token = Json::encode($token);
    }
    return $this->set('access_token', $token);
  }

  /**
   * {@inheritdoc}
   */
  public function setAuthenticated($authentication) {
    return $this->set('is_authenticated', $authentication);
  }

  /**
   * {@inheritdoc}
   */
  public function setAccessType($type) {
    return $this->set('access_type', $type);
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * Define the field properties here.
   *
   * Field name, type and size determine the table structure.
   *
   * In addition, we can define how the field and its content can be manipulated
   * in the GUI. The behaviour of the widgets used can be determined here.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('GoogleApiClient ID'))
      ->setDescription(t('The ID of the GoogleApiClient entity.'))
      ->setReadOnly(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -1,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The Google Api Client UUID.'))
      ->setReadOnly(TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the GoogleApiClient entity.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 1,
      ])
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['developer_key'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Developer Key'))
      ->setDescription(t('The developer key of the GoogleApiClient entity.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['client_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Client Id'))
      ->setDescription(t('The client id of the GoogleApiClient entity.'))
      ->setRequired(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 2,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['client_secret'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Client Secret'))
      ->setDescription(t('The client secret of the GoogleApiClient entity.'))
      ->setRequired(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 3,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['services'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Services'))
      ->setDescription(t('services of the GoogleApiClient entity.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 4,
        'multiple' => TRUE,
      ])
      ->setSetting('allowed_values_function', 'google_api_client_google_services_names')
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['scopes'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Scopes'))
      ->setDescription(t('scopes of the GoogleApiClient entity.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 5,
        'multiple' => TRUE,
      ])
      ->setSetting('allowed_values_function', 'google_api_client_google_scopes_names')
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['access_token'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Access Token'))
      ->setDescription(t('Access token from google'))
      ->setReadOnly(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 7,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['is_authenticated'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Is Authenticated'))
      ->setDescription(t('Is google_api_client account authenticated'))
      ->setDefaultValue(FALSE)
      ->setReadOnly(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 8,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    // Owner field of the google_api_client.
    // Entity reference field, holds the reference to the user object.
    // The view shows the user name field of the user.
    // The form presents a auto complete field for the user name.
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User Name'))
      ->setDescription(t('The Name of the associated user.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 9,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['access_type'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Is Access Type Offline'))
      ->setDescription(t('Access type of the GoogleApiClient entity.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 6,
      ])
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 11,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    return $fields;
  }

  /**
   * {@inheritdoc}
   *
   * When a new entity instance is added, set the uid entity reference to
   * the current user as the creator of the instance.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    if (!isset($values['uid']) || $values['uid'] == NULL) {
      $values += [
        'uid' => \Drupal::currentUser()->id(),
      ];
    }
  }

  /**
   * {@inheritdoc}
   *
   * @return int
   *   Id for the GoogleApiClient being saved.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function save() {
    // Skip for new entity.
    if ($this->isNew()) {
      if ($this->getAccessToken() == '' || $this->getAccessToken() == NULL) {
        $this->setAccessToken('');
        $this->setAuthenticated(FALSE);
      }
      return parent::save();
    }
    $original = $this->original ? $this->original : NULL;

    if (!$original) {
      // Usually this should exist in save but still keeping it safe.
      $id = $this->getOriginalId() !== NULL ? $this->getOriginalId() : $this->id();
      $original = $this->entityTypeManager()->getStorage($this->getEntityTypeId())->loadUnchanged($id);
    }

    if ($original &&($original->getServices() != $this->getServices() ||
        $original->getClientId() != $this->getClientId() ||
        $original->getClientSecret() != $this->getClientSecret() ||
        $original->getScopes() != $this->getScopes() ||
        $original->getDeveloperKey() != $this->getDeveloperKey())) {
      // If the google_api_client isi modified it needs to be re-authenticated.
      $this->setAccessToken('');
      $this->setAuthenticated(FALSE);
    }
    return parent::save();
  }

}
