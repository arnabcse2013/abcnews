<?php

namespace Drupal\googlelogin\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form for Social API Google.
 */
class GoogleOAuthCredentialsForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new SiteConfigureForm.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'google_oauth_login_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['googlelogin.settings'];
  }

  /**
   * Build Admin Settings Form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('googlelogin.settings');

    $form['google_oauth_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Google OAuth Settings'),
      '#open' => TRUE,
    ];
    $form['google_oauth_settings']['google_api_client_id'] = [
      '#type' => 'hidden',
      '#default_value' => $config->get('google_api_client_id'),
    ];
    if ($google_api_client_id = $config->get('google_api_client_id')) {
      $google_api_client = $this->entityTypeManager->getStorage('google_api_client')->load($google_api_client_id);
    }

    $form['google_oauth_settings']['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google OAuth ClientID'),
      '#required' => TRUE,
      '#default_value' => is_object($google_api_client) ? $google_api_client->getClientId() : '',
    ];
    $form['google_oauth_settings']['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google OAuth Client Secret'),
      '#required' => TRUE,
      '#default_value' => is_object($google_api_client) ? $google_api_client->getClientSecret() : '',
    ];
    $form['google_oauth_settings']['access_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Access Type'),
      '#default_value' => is_object($google_api_client) ? $google_api_client->getAccessType() : FALSE,
      '#options' => [$this->t('Online'), $this->t('Offline')],
      '#required' => TRUE,
      '#description' => $this->t('Access type defines when can this authentication be used,
       if online then it can be used only for login i.e. when the user is 
       logged in on google and authenticates, once authentication expires
       usually in 1 hour it can not be used,
       if offline then can be used even when user is logged out (this is only for developers
        who want to perform some operations on user data).'),
    ];

    $form['google_oauth_settings']['redirect_url'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Google OAuth Redirect URL') . '<br/><code>' . google_api_client_callback_url() . '</code><br/>' . $this->t('Redirect URL to be pasted in the google api console.'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * Build Admin Submit.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if ($values['google_api_client_id']) {
      $google_api_client = $this->entityTypeManager->getStorage('google_api_client')->load($values['google_api_client_id']);
      $google_api_client->setClientId($values['client_id']);
      $google_api_client->setClientSecret($values['client_secret']);
      $google_api_client->setAccessType($values['access_type']);
    }
    else {
      $account = [
        'name' => 'Google OAuth Login Client',
        'client_id' => $values['client_id'],
        'client_secret' => $values['client_secret'],
        'access_token' => '',
        'services' => ['oauth2'],
        'is_authenticated' => FALSE,
        'scopes' => ['USERINFO_PROFILE', 'USERINFO_EMAIL', 'OPENID'],
        'access_type' => $values['access_type'],
      ];
      $google_api_client = $this->entityTypeManager->getStorage('google_api_client')->create($account);
    }
    $google_api_client->save();

    if ($google_api_client->getId()) {
      $this->config('googlelogin.settings')
        ->set('google_api_client_id', $google_api_client->getId())
        ->save();
    }
    parent::submitForm($form, $form_state);
  }

}
