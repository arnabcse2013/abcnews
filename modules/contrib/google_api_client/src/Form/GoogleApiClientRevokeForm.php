<?php

namespace Drupal\google_api_client\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for revoking a google_api_client entity.
 *
 * @ingroup google_api_client
 */
class GoogleApiClientRevokeForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to revoke access token of this account');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t("This account can't be used for api call until authenticated again");
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t("Revoke");
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.google_api_client.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $google_api_client = $this->entity;
    $service = \Drupal::service('google_api_client.client');
    $service->setGoogleApiClient($google_api_client);
    $service->googleClient->revokeToken();
    $google_api_client->setAccessToken('');
    $google_api_client->setAuthenticated(FALSE);
    $google_api_client->save();
    parent::submitForm($form, $form_state);
    $this->messenger()->addMessage($this->t('GoogleApiClient account revoked successfully'));
    $this->redirect('entity.google_api_client.collection')->send();
  }

}
