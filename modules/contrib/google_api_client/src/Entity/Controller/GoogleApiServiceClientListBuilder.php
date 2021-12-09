<?php

namespace Drupal\google_api_client\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;

/**
 * Provides a list controller for google_api_client entity.
 *
 * @ingroup google_api_client
 */
class GoogleApiServiceClientListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the entity list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {
    $header = [
      'id' => $this->t('Machine Name'),
      'label' => $this->t('Name'),
      'services' => $this->t('Services'),
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [
      'id' => $entity->id(),
      'label' => $entity->label(),
      'services' => implode(', ', $entity->getServices()),
    ];
    return $row + parent::buildRow($entity);
  }

}
