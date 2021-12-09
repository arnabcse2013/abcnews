<?php

namespace Drupal\google_api_client\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

/**
 * Form controller for the google_api_client entity edit forms.
 *
 * @ingroup google_api_client
 */
class GoogleApiClientForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if (!google_api_client_load_library()) {
      // We don't have library installed notify admin and abort.
      $status_report_link = Link::createFromRoute($this->t('Status Report'), 'system.status')->toString();
      $this->messenger()->addError($this->t('Google Api PHP Client library is not installed, please check %status_report for more details.', [
        '%status_report' => $status_report_link,
      ]));
      return $this->redirect('entity.google_api_client.collection');
    }
    $form = parent::buildForm($form, $form_state);
    $form['scopes']['widget']['#prefix'] = '<div id="scopes-wrapper">';
    $form['scopes']['widget']['#suffix'] = '</div>';
    if ($form_state->getValue('services')) {

      $services = [];
      foreach ($form_state->getValue('services') as $values) {
        $services[] = $values['value'];
      }
      $scopes = google_api_client_google_services_scopes($services);
      $form['scopes']['widget']['#options'] = $scopes;
    }
    $form['#attached']['library'][] = 'google_api_client/google_api_client.add_client';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);

    $entity = $this->entity;
    if ($status == SAVED_UPDATED) {
      $this->messenger()->addMessage($this->t('The google_api_client %feed has been updated.', ['%feed' => $entity->toLink()->toString()]));
    }
    else {
      $this->messenger()->addMessage($this->t('The google_api_client %feed has been added. You now need to authenticate this new account.', ['%feed' => $entity->toLink()->toString()]));
    }

    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $status;
  }

}
