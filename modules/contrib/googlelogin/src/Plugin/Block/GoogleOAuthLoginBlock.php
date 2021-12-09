<?php

namespace Drupal\googlelogin\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\google_api_client\Service\GoogleApiClientService;

/**
 * Provides a Google OAuth Login Block.
 *
 * @Block(
 *   id = "google_oauth_login_block",
 *   admin_label = @Translation("Google OAuth Login"),
 *   category = @Translation("Blocks")
 * )
 */
class GoogleOAuthLoginBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The GoogleApiClient Service.
   *
   * @var \Drupal\google_api_client\Service\GoogleApiClientService
   */
  protected $googleApiClientService;

  /**
   * Overrides \Drupal\Core\Block::__construct().
   *
   * Overrides the construction of context aware plugins to allow for
   * unvalidated constructor based injection of contexts.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\google_api_client\Service\GoogleApiClientService $googleApiClientService
   *   The google api client service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entityTypeManager, GoogleApiClientService $googleApiClientService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entityTypeManager;
    $this->googleApiClientService = $googleApiClientService;
  }

  /**
   * Create function.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container object.
   * @param array $configuration
   *   The config array.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('google_api_client.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->configFactory->get('googlelogin.settings');
    // Google client ID.
    $google_api_client_id = $config->get('google_api_client_id');
    if (isset($_SESSION['google_oauth_token'])) {
      $google_api_client = $this->entityTypeManager->getStorage('google_api_client')->load($google_api_client_id);
      $this->googleApiClientService->setGoogleApiClient($google_api_client);
      $this->googleApiClientService->googleClient->setAccessToken($_SESSION['google_oauth_token']);
      $google_oauthV2 = new \Google_Service_Oauth2($this->googleApiClientService->googleClient);
      // Get user profile data from google.
      $userInfo = $google_oauthV2->userinfo->get();
      $output = $userInfo['name'];
    }
    else {
      $output = googlelogin_login_button_code($google_api_client_id);
    }

    return [
      '#markup' => $output,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
