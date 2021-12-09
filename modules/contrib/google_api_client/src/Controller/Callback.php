<?php

namespace Drupal\google_api_client\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\google_api_client\Service\GoogleApiClientService;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Google Client Callback Controller.
 *
 * @package Drupal\google_api_client\Controller
 */
class Callback extends ControllerBase {

  /**
   * Google API Client.
   *
   * @var \Drupal\google_api_client\Service\GoogleApiClientService
   */
  private $googleApiClientService;

  /**
   * The tempstore factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Callback constructor.
   *
   * @param \Drupal\google_api_client\Service\GoogleApiClientService $googleApiClient
   *   Google API Client.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(GoogleApiClientService $googleApiClient, ModuleHandlerInterface $module_handler, PrivateTempStoreFactory $temp_store_factory, RequestStack $request_stack) {
    $this->googleApiClientService = $googleApiClient;
    $this->moduleHandler = $module_handler;
    $this->tempStoreFactory = $temp_store_factory;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('google_api_client.client'),
      $container->get('module_handler'),
      $container->get('tempstore.private'),
      $container->get('request_stack')
    );
  }

  /**
   * Callback URL for Google API Auth.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return array
   *   Return markup for the page.
   */
  public function callbackUrl(Request $request) {
    if ($state = $request->get('state')) {
      $state = Json::decode($state);
      if (isset($state['src']) && !in_array('google_api_client', $state['src'])) {
        // Handle response only if the request was from google_api_client.
        // Here some other module has set that we don't process standard
        // google_api_client so we invoke the webhook and return.
        $this->moduleHandler->invokeAll('google_api_client_google_response', [$request]);
        // We return to home page if not redirected in the webhook.
        return $this->redirect('<front>');
      }
    }
    $tempStore = $this->tempStoreFactory->get('google_api_client');
    if ($request->get('error')) {
      if ($request->get('error') == 'access_denied') {
        $this->messenger()->addError($this->t('You denied access so account is not authenticated'));
      }
      else {
        $this->messenger()->addError($this->t('Something caused error in authentication.'));
      }
      $tempStore->delete('account_id');
      $tempStore->delete('account_type');

      if ($tempStore->get('state_destination')) {
        $destination = $tempStore->get('state_destination');
        $tempStore->delete('state_destination');
        return new RedirectResponse(Url::fromUserInput($destination)->toString());
      }
      $tempStore->delete('state_src');
      $tempStore->delete('state_hash');
      return $this->redirect('<front>');
    }
    $account_id = $request->get('id');
    $entity_type = $request->get('type');
    if ($entity_type) {
      $tempStore->set('account_type', $entity_type);
    }
    else {
      if ($tempStore->get('account_type')) {
        $entity_type = $tempStore->get('account_type');
      }
      else {
        $entity_type = 'google_api_client';
        $tempStore->set('account_type', $entity_type);
      }
    }
    if (!google_api_client_load_library()) {
      // We don't have library installed notify admin and abort.
      $status_report_link = Link::createFromRoute($this->t('Status Report'), 'system.status')->toString();
      $this->messenger()->addError($this->t("Can't authenticate with google as library is missing check %status_report for more details", [
        '%status_report' => $status_report_link,
      ]));
      return $this->redirect('entity.google_api_client.collection');
    }
    if ($account_id == NULL && $tempStore->get('account_id')) {
      $account_id = $tempStore->get('account_id');
    }
    elseif ($account_id) {
      $tempStore->set('account_id', $account_id);
    }
    if ($account_id) {
      $google_api_client = $this->entityTypeManager()->getStorage($entity_type)->load($account_id);
      $this->googleApiClientService->setGoogleApiClient($google_api_client);
      $this->googleApiClientService->googleClient->setApplicationName("Google OAuth2");

      if ($request->get('code')) {
        $this->googleApiClientService->googleClient->fetchAccessTokenWithAuthCode($request->get('code'));
        $google_api_client->setAccessToken(Json::encode($this->googleApiClientService->googleClient->getAccessToken()));
        $google_api_client->setAuthenticated(TRUE);
        $google_api_client->save();
        $destination = FALSE;
        if ($tempStore->get('state_destination')) {
          $destination = $tempStore->get('state_destination');
        }
        $tempStore->delete('state_destination');
        $tempStore->delete('state_src');
        $tempStore->delete('state_hash');
        $tempStore->delete('account_id');
        $tempStore->delete('account_type');
        $this->messenger()->addMessage($this->t('Api Account saved'));
        // Let other modules act of google response.
        $this->moduleHandler->invokeAll('google_api_client_google_response', [$request]);
        if ($destination) {
          return new RedirectResponse(Url::fromUserInput($destination)->toString());
        }
        return $this->redirect('entity.google_api_client.collection');
      }
      if ($this->googleApiClientService->googleClient) {
        if ($tempStore->get('state_src')) {
          $state = [
            'src' => $tempStore->get('state_src'),
            'hash' => $tempStore->get('state_hash'),
          ];
        }
        else {
          $state = [
            'src' => ['google_api_client'],
            'hash' => md5(rand()),
          ];
          if ($destination = $request->get('destination')) {
            $tempStore->set('state_destination', $destination);
            $this->requestStack->getCurrentRequest()->query->remove('destination');
          }
        }
        // Allow other modules to alter the state param.
        $this->moduleHandler->alter('google_api_client_state', $state, $google_api_client);
        $tempStore->set('state_src', $state['src']);
        $tempStore->set('state_hash', $state['hash']);
        $state = Json::encode($state);
        $this->googleApiClientService->googleClient->setState($state);
        $auth_url = $this->googleApiClientService->googleClient->createAuthUrl();
        $request->getSession()->save();
        $response = new TrustedRedirectResponse($auth_url);
        $response->send();
        exit;
      }
    }
    return $this->redirect('entity.google_api_client.collection');
  }

  /**
   * Checks access for authenticate url.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function authenticateAccess(AccountInterface $account) {
    $request = $this->requestStack->getCurrentRequest();
    if ($account->hasPermission('administer google api settings')) {
      return AccessResult::allowed();
    }
    if ($state = $request->get('state')) {
      $state = Json::decode($state);
      $tempStore = $this->tempStoreFactory->get('google_api_client');
      /* We implement an additional hash check so that if the callback
       * is opened for public access like it will be done for google login
       * In that case we rely on the has for verifying that no one is hacking.
       */
      if (!isset($state['hash']) || $state['hash'] != $tempStore->get('state_hash')) {
        $this->messenger()->addError($this->t('Invalid state parameter'), 'error');
        return AccessResult::forbidden();
      }
      else {
        return AccessResult::allowed();
      }
    }
    $account_id = $request->get('id');
    $account_type = $request->get('type', 'google_api_client');
    $access = $this->moduleHandler->invokeAll('google_api_client_authenticate_account_access', [
      $account_id,
      $account_type,
      $account,
    ]);
    // If any module returns forbidden then we don't allow authenticate.
    if (in_array(AccessResult::forbidden(), $access)) {
      return AccessResult::forbidden();
    }
    elseif (in_array(AccessResult::allowed(), $access)) {
      return AccessResult::allowed();
    }
    return AccessResult::neutral();
  }

}
