<?php

namespace Drupal\google_api_client\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Serialization\Json;

/**
 * Form controller for the google_api_service_client entity edit forms.
 *
 * @ingroup google_api_service_client
 */
class GoogleApiServiceClientForm extends EntityForm {

  /**
   * Constructs an ExampleForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entityTypeManager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $google_api_service_client = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $google_api_service_client->label(),
      '#description' => $this->t("Label for the Example."),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $google_api_service_client->id(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
      ],
      '#disabled' => !$google_api_service_client->isNew(),
    ];
    $form['auth_config'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Credentials'),
      '#default_value' => $google_api_service_client->isNew() ? '' : Json::encode($google_api_service_client->getAuthConfig()),
      '#description' => $this->t("Credential json file downloaded from google console."),
      '#required' => TRUE,
      '#placeholder' => $this->t('Example: {
  "type": "service_account",
  "project_id": "SERVICEACCOUNT",
  "private_key_id": "PRIVATE_KEY_ID",
  "private_key": "-----BEGIN PRIVATE KEY-----\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890ab\nadaksdfaskdfjaskdf\n-----END PRIVATE KEY-----\n",
  "client_email": "google-service-account@servicesaccount.iam.gserviceaccount.com",
  "client_id": "CLIEND_ID",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/google-service-account%40servicesaccount.iam.gserviceaccount.com"
}'),
    ];

    $services = _google_api_client_google_services_names();
    $form['services'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => $this->t('Services'),
      '#default_value' => $google_api_service_client->isNew() ? '' : $google_api_service_client->getServices(),
      '#options' => $services,
    ];
    $services = array_keys($services);
    $scopes = google_api_client_google_services_scopes($services);
    $form['scopes'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => $this->t('Scopes'),
      '#default_value' => $google_api_service_client->isNew() ? '' : $google_api_service_client->getScopes(),
      '#options' => $scopes,
    ];
    $form['#attached']['library'][] = 'google_api_client/google_api_client.add_client';

    // You will need additional form elements for your custom properties.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $google_api_service_client = $this->entity;
    $status = $google_api_service_client->save();

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('The %label Google Api Service Account created.', [
        '%label' => $google_api_service_client->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('The %label Google Api Service Account updated.', [
        '%label' => $google_api_service_client->label(),
      ]));
    }

    $form_state->setRedirect('entity.google_api_service_client.collection');
  }

  /**
   * Helper function to check whether an Example configuration entity exists.
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('google_api_service_client')->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $auth_config = $form_state->getValue('auth_config');
    if ($auth_config_obj = Json::decode($auth_config)) {
      if (!isset($auth_config_obj['type']) || $auth_config_obj['type'] != 'service_account') {
        $form_state->setErrorByName('auth_config', $this->t('Invalid key file, this configuration needs a service account key, if you are using oauth goto Google api client listing.'));
      }
    }
    else {
      // Json decode failed so json is invalid.
      $form_state->setErrorByName('auth_config', $this->t('Invalid Json, paste complete file contents as downloaded.'));
    }
    parent::validateForm($form, $form_state);
  }

}
