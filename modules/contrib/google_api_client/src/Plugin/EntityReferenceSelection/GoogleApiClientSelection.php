<?php

namespace Drupal\google_api_client\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;

/**
 * Provides property filter for google client entity type.
 *
 * @EntityReferenceSelection(
 *   id = "default:google_api_client",
 *   label = @Translation("Google Api Client Selection"),
 *   entity_types = {"google_api_client"},
 *   group = "default",
 *   weight = 1
 * )
 */
class GoogleApiClientSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = parent::buildEntityQuery($match, $match_operator);
    $configuration = $this->getConfiguration();
    if (is_array($configuration['property']) && !empty($configuration['property'])) {
      $fields = array_keys($configuration['property']);
      foreach ($fields as $field) {
        if (is_array($configuration['property'][$field])) {
          $query->condition($field, $configuration['property'][$field], 'IN');
        }
        else {
          $query->condition($field, $configuration['property'][$field]);
        }
      }
    }
    return $query;
  }

}
