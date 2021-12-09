<?php

namespace Drupal\pages_restriction\Service;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Pages Restriction Session Service.
 */
class PagesRestrictionSessionService {

  /**
   * Symfony session handler.
   *
   * @var \Symfony\Component\HttpFoundation\Session\Session
   */
  private $session;

  /**
   * {@inheritdoc}
   */
  public function __construct(Session $session) {
    $this->session = $session;
  }

  /**
   * Set Bypass.
   */
  public function setBypass($path) {

    // Get current bypass values.
    $pages_restriction_bypass = $this->session->get('pages_restriction_bypass');

    // Set next URL on bypass session.
    $pages_restriction_bypass[] = $path;

    // Update Bypass Session.
    $this->session->set('pages_restriction_bypass', $pages_restriction_bypass);

    // Return Bypass Session.
    return $pages_restriction_bypass;
  }

}
