<?php

namespace Drupal\pages_restriction\Event;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Config\ConfigFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\pages_restriction\Service\PagesRestrictionHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Url;

/**
 * Default class for Subscriber.
 */
class PagesRestrictionSubscriber implements EventSubscriberInterface {

  /**
   * PagesRestrictionHelper.
   *
   * @var \Drupal\pages_restriction\Service\PagesRestrictionHelper
   */
  protected $pagesRestrictionHelper;

  /**
   * Path Matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * Symfony session handler.
   *
   * @var \Symfony\Component\HttpFoundation\Session\Session
   */
  private $session;

  /**
   * Current Path Stack.
   *
   * @var Drupal\Core\Path\CurrentPathStack
   */
  private $currentPath;

  /**
   * Alias Manager.
   *
   * @var \Drupal\Core\Path\AliasManager
   */
  protected $aliasManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactory $config_factory, RequestStack $request_stack, PathMatcherInterface $path_matcher, PagesRestrictionHelper $pages_restriction_helper, AccountProxyInterface $account, Session $session, CurrentPathStack $current_path, AliasManagerInterface $alias_manager) {
    $this->pathMatcher = $path_matcher;
    $this->request = $request_stack->getCurrentRequest();
    $this->configFactory = $config_factory;
    $this->pagesRestrictionHelper = $pages_restriction_helper;
    $this->account = $account;
    $this->session = $session;
    $this->currentPath = $current_path;
    $this->aliasManager = $alias_manager;
  }

  /**
   * On Request Check Restricted Pages.
   */
  public function onRequestCheckRestrictedPages(GetResponseEvent $event) {

    $config = $this->configFactory->get('pages_restriction.settings');

    $roles = $this->account->getRoles();
    $bypass_role = $config->get('bypass_role');

    // Check bypass only for logged user.
    if (!$this->account->isAnonymous()) {

      // Check roles.
      foreach ($roles as $rid) {
        if (!empty($bypass_role[$rid])) {
          return FALSE;
        }
      }
    }

    $restrictedPages = $config->get('pages_restriction');

    if (empty($restrictedPages)) {
      return FALSE;
    }

    $restrictedPages = explode(PHP_EOL, $restrictedPages);

    if (empty($this->request->getRequestUri())) {
      return FALSE;
    }

    // Get current path.
    $currentPath = $this->currentPath->getPath();
    $currentPath = $this->aliasManager->getAliasByPath($currentPath);

    // Check for bypass on session.
    $pages_restriction_bypass = $this->session->get('pages_restriction_bypass');

    // If user has bypass skip.
    if (!empty($pages_restriction_bypass) && in_array($currentPath, $pages_restriction_bypass)) {
      return FALSE;
    }

    $restrictedPaths = (array) $this->pagesRestrictionHelper->getRestrictedPagesByConfig($restrictedPages);

    if (empty($restrictedPaths) || !in_array($currentPath, $restrictedPaths)) {
      return FALSE;
    }

    foreach ($restrictedPages as $restrictedPage) {

      if (empty($restrictedPage[0]) || empty($restrictedPage[1])) {
        continue;
      }

      $restrictedPage = explode('|', $restrictedPage);

      $restrictedPath = Xss::filter($restrictedPage[0]);
      $restrictedPath = trim($restrictedPath);

      if ($restrictedPath != $currentPath) {
        continue;
      }

      $targetPage = Xss::filter($restrictedPage[1]);
      $targetPage = trim($targetPage);

      if (!empty($config->get('keep_parameters'))) {
        $queryParameters = $this->request->query->all();
        $queryParameters = ['query' => $queryParameters];
        $targetPage = Url::fromUserInput($targetPage, $queryParameters)->toString();
      }

      $response = new RedirectResponse($targetPage);
      $response->send();
      $event->stopPropagation();
      exit;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequestCheckRestrictedPages', 215];
    return $events;
  }

}
