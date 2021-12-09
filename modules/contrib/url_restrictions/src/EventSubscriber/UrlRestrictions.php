<?php

namespace Drupal\url_restrictions\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Path\PathMatcher;
use Drupal\Core\Url;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Event Subscriber for Url Restrictions.
 */
class UrlRestrictions implements EventSubscriberInterface {

  /**
   * Request object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Configuration object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * User object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Language object.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcher
   */
  protected $pathmatcher;

  /**
   * Route matcher.
   *
   * @var \Drupal\Core\Path\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Path\PathMatcher $pathmatcher
   *   Provides a path matcher.
   * @param \Drupal\Core\Path\RouteMatchInterface $route_match
   *   Provides a Route matcher.
   */
  public function __construct(AccountProxyInterface $currentUser,
                              ConfigFactoryInterface $config,
                              RequestStack $request,
                              LanguageManagerInterface $language_manager,
                              PathMatcher $pathmatcher,
                              RouteMatchInterface $route_match) {
    $this->currentUser = $currentUser;
    $this->config = $config;
    $this->request = $request;
    $this->languageManager = $language_manager;
    $this->pathmatcher = $pathmatcher;
    $this->routeMatch = $route_match;
  }

  /**
   * Code that should be triggered on event specified .
   */
  public function onRequest(GetResponseEvent $event) {
    // Get the configuration variables.
    $arr_entity_type = $this->config->get('url_restrictions.settings')->get('entity_type');
    $redirect_path = $this->config->get('url_restrictions.settings')->get('url');
    $pages = $this->config->get('url_restrictions.settings')->get('pages');
    $pages = explode(PHP_EOL, $pages);
    $hasPermission = $this->currentUser->hasPermission('allow_all_url');
    $request_uri = $this->request->getCurrentRequest()->server->get('REQUEST_URI');
    $request_uri = $this->getCurrentUrl($request_uri);
    $node = $this->pathmatcher->matchPath($request_uri, '/node/*');
    $taxonomy = $this->pathmatcher->matchPath($request_uri, '/taxonomy/*');
    $user = $this->pathmatcher->matchPath($request_uri, '/user/*');
    if (!$hasPermission && ($node || $taxonomy || $user) && $request_uri != $redirect_path) {
      $route_name = $this->routeMatch->getRouteName();
      $current_url = Url::fromRoute('<current>')->toString();
      $current_url = $this->getCurrentUrl($current_url);
      // Check current url belongs to custom pages defined in configuration.
      $flag_page = in_array($current_url, $pages);
      $node = ($route_name === 'entity.node.canonical') ? 1 : 0;
      $taxonomy = ($route_name === 'entity.taxonomy_term.canonical') ? 1 : 0;
      $user = ($route_name === 'entity.user.canonical') ? 1 : 0;
      $flag_node = 0;
      $flag_taxonomy = 0;
      $flag_user = 0;
      if (!empty($arr_entity_type)) {
        if ($node && $arr_entity_type['node'] === 'node') {
          $flag_node = 1;
        }
        if ($taxonomy && $arr_entity_type['taxonomy'] === 'taxonomy') {
          $flag_taxonomy = 1;
        }
        if ($user && $arr_entity_type['user'] === 'user') {
          $flag_user = 1;
        }
      }
      // Check for redirection.
      if (($flag_node || $flag_taxonomy || $flag_user) && !$flag_page) {
        $response = new TrustedRedirectResponse($redirect_path);
        $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([])->addCacheTags(['rendered']));
        $event->setResponse($response);
      }
    }
  }

  /**
   * Code that should be return current url without language code.
   */
  public function getCurrentUrl($url) {
    $languagecode = $this->languageManager->getCurrentLanguage()->getId();
    $url = preg_replace('/\?.*/', '', $url);
    $path = explode("/", $url);
    if (isset($path[1]) && $path[1] == $languagecode) {
      array_splice($path, 1, 1);
      $url = implode("/", $path);
    }
    return $url;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest'];
    return $events;
  }

}
