<?php

namespace Drupal\data_permissions;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides dynamic permissions for nodes of different types.
 */
class DynamicPermissions {

  use StringTranslationTrait;

  /**
   * Returns an array of node type permissions.
   *
   * @return array
   *   The node type permissions.
   *   @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  public function getPermissions() {
    $permissions = [];
    $count = 1;
    while ($count <= 5) {
      $permissions += [
        "dynamic permission $count" => [
          'title' => $this->t('Data dynamic permission @number', ['@number' => $count]),
          'description' => $this->t('This is a Data permission generated dynamically.'),
        ],
      ];
      $count++;
    }

    return $permissions;
  }

}
