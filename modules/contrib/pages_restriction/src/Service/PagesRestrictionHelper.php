<?php

namespace Drupal\pages_restriction\Service;

use Drupal\Component\Utility\Xss;

/**
 * Helper for Pages Restriction.
 */
class PagesRestrictionHelper {

  /**
   * Get Restricted Pages by Config.
   */
  public function getRestrictedPagesByConfig($configRestrictedPages) {

    if (empty($configRestrictedPages)) {
      return FALSE;
    }

    $restrictedPages = [];

    foreach ($configRestrictedPages as $restrictedPage) {

      $restrictedPage = explode('|', $restrictedPage);

      if (empty($restrictedPage)) {
        continue;
      }

      $restrictedPath = Xss::filter($restrictedPage[0]);
      $restrictedPath = trim($restrictedPath);
      $restrictedPages[] = $restrictedPath;
    }

    return $restrictedPages;
  }

}
