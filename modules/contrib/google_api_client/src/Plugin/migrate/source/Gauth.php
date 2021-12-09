<?php

namespace Drupal\google_api_client\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;
use Drupal\Core\Database\Connection;

/**
 * Drupal 7 user source from database.
 *
 * @MigrateSource(
 *   id = "gauth",
 *   source_module = "google_api_client"
 * )
 */
class Gauth extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('gauth_accounts', 'ga')
      ->fields('ga')
      ->condition('ga.id', 0, '>')
      ->condition('ga.name', Connection::escapeLike('Gauth Login ') . '%', 'NOT LIKE')
      ->condition('ga.name', '%' . Connection::escapeLike('|') . '%', 'NOT LIKE');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'id' => $this->t('Gauth ID'),
      'name' => $this->t('Name'),
      'developer_key' => $this->t('Developer Key'),
      'client_id' => $this->t('Client ID'),
      'client_secret' => $this->t('Client Secret'),
      'services' => $this->t('Services'),
      'access_token' => $this->t('Access Token'),
      'access_type' => $this->t('Access Type'),
      'is_authenticated' => $this->t('Is Authenticated'),
      'uid' => $this->t('User'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $services = $row->getSourceProperty('services');
    $services = explode(',', $services);
    $row->setSourceProperty('services', $services);

    $all_scopes = google_api_client_google_services_scopes($services);
    $merged_scopes = [];
    foreach ($all_scopes as $scopes) {
      $merged_scopes = array_merge($merged_scopes, array_keys($scopes));
    }
    $row->setSourceProperty('service_scopes', $merged_scopes);

    $access_type = $row->getSourceProperty('access_type');
    $row->setSourceProperty('access_type', $access_type == 'offline');

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'id' => [
        'type' => 'integer',
        'alias' => 'ga',
      ],
    ];
  }

}
