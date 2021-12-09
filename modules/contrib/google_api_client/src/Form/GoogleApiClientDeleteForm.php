<?php

namespace Drupal\google_api_client\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting a google_api_client entity.
 *
 * @ingroup google_api_client
 */
class GoogleApiClientDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete this account');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t("This account will be deleted from the system and won't be available");
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t("Delete");
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
    $google_api_client->delete();
    parent::submitForm($form, $form_state);
    $this->messenger()->addMessage($this->t('GoogleApiClient account deleted successfully'));
    $this->redirect('entity.google_api_client.collection')->send();
  }

}
